<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Enums\UnitStatus;
use App\Models\Equipment;
use App\Models\Loan;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use App\Services\LoanService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class GhostGearRentalTest extends TestCase
{
    use RefreshDatabase;

    protected LoanService $loanService;

    protected User $admin;

    protected User $member;

    protected Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loanService = app(LoanService::class);
        Setting::set('fine_per_day', '10000000');

        $this->admin = User::factory()->admin()->create(['username' => 'admin_test']);
        Profile::factory()->create(['user_id' => $this->admin->id]);

        $this->member = User::factory()->anggota()->create(['username' => 'member_test']);
        Profile::factory()->create(['user_id' => $this->member->id]);

        $this->equipment = Equipment::factory()->create(['name' => 'Ghost Scanner 3000']);
    }

    /**
     * Test 1: Anggota tidak bisa mengajukan pinjaman ketiga (dua pinjaman menunggu atau aktif sudah penuh).
     */
    public function test_1_member_cannot_request_third_loan_when_two_active_or_pending(): void
    {
        $unit1 = Unit::factory()->create(['equipment_id' => $this->equipment->id, 'unit_code' => 'TEST-001', 'status' => UnitStatus::Tersedia]);
        $unit2 = Unit::factory()->create(['equipment_id' => $this->equipment->id, 'unit_code' => 'TEST-002', 'status' => UnitStatus::Tersedia]);
        $unit3 = Unit::factory()->create(['equipment_id' => $this->equipment->id, 'unit_code' => 'TEST-003', 'status' => UnitStatus::Tersedia]);

        // Pinjaman 1: berhasil
        $loan1 = $this->loanService->request($this->member, $unit1);
        $this->assertSame(LoanStatus::Menunggu, $loan1->status);
        $this->assertSame(UnitStatus::Ditahan, $unit1->fresh()->status);

        // Pinjaman 2: berhasil (disetujui menjadi aktif)
        $loan2 = $this->loanService->request($this->member, $unit2);
        $this->loanService->approve($loan2, $this->admin);
        $this->assertSame(LoanStatus::Aktif, $loan2->fresh()->status);
        $this->assertSame(UnitStatus::Dipinjam, $unit2->fresh()->status);

        // Pinjaman 3: harus ditolak
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Anggota sudah mencapai batas maksimal pinjaman aktif (2).');

        $this->loanService->request($this->member, $unit3);
    }

    /**
     * Test 2: Unit yang ditahan atau dipinjam tidak bisa diajukan anggota lain.
     */
    public function test_2_unit_that_is_held_or_borrowed_cannot_be_requested_by_other_member(): void
    {
        $otherMember = User::factory()->anggota()->create(['username' => 'other_investigator']);
        Profile::factory()->create(['user_id' => $otherMember->id]);

        $unitHeld = Unit::factory()->create(['equipment_id' => $this->equipment->id, 'unit_code' => 'HELD-001', 'status' => UnitStatus::Tersedia]);
        $unitBorrowed = Unit::factory()->create(['equipment_id' => $this->equipment->id, 'unit_code' => 'BORROW-001', 'status' => UnitStatus::Tersedia]);

        // Member pertama mengajukan unitHeld -> unit jadi ditahan
        $this->loanService->request($this->member, $unitHeld);
        $this->assertSame(UnitStatus::Ditahan, $unitHeld->fresh()->status);

        // Anggota lain mencoba mengajukan unit yang ditahan
        try {
            $this->loanService->request($otherMember, $unitHeld);
            $this->fail('Unit ditahan seharusnya tidak bisa diajukan.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Unit tidak berstatus tersedia', $e->getMessage());
        }

        // Ajukan dan setujui unitBorrowed -> unit jadi dipinjam
        $loanBorrowed = $this->loanService->request($otherMember, $unitBorrowed);
        $this->loanService->approve($loanBorrowed, $this->admin);
        $this->assertSame(UnitStatus::Dipinjam, $unitBorrowed->fresh()->status);

        // Member pertama mencoba mengajukan unit yang dipinjam
        try {
            $this->loanService->request($this->member, $unitBorrowed);
            $this->fail('Unit dipinjam seharusnya tidak bisa diajukan.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Unit tidak berstatus tersedia', $e->getMessage());
        }
    }

    /**
     * Test 3: Approve mengisi due_date = borrow_date + 5 hari.
     */
    public function test_3_approve_sets_due_date_to_borrow_date_plus_five_days(): void
    {
        $unit = Unit::factory()->create(['equipment_id' => $this->equipment->id, 'unit_code' => 'APPR-001', 'status' => UnitStatus::Tersedia]);
        $loan = $this->loanService->request($this->member, $unit);

        $approvedLoan = $this->loanService->approve($loan, $this->admin);

        $this->assertSame(LoanStatus::Aktif, $approvedLoan->status);
        $this->assertNotNull($approvedLoan->approved_at);
        $this->assertSame($this->admin->id, $approvedLoan->approved_by);
        $this->assertNotNull($approvedLoan->borrow_date);
        $this->assertNotNull($approvedLoan->due_date);

        $expectedDueDate = $approvedLoan->borrow_date->copy()->addDays(5)->format('Y-m-d');
        $this->assertSame($expectedDueDate, $approvedLoan->due_date->format('Y-m-d'));
        $this->assertSame(UnitStatus::Dipinjam, $unit->fresh()->status);
    }

    /**
     * Test 4: Dipinjam 1 November (due 6 November), dikembalikan 6 November: denda 0.
     *         Dikembalikan 8 November: late_days 2, fine_amount 20000000.
     */
    public function test_4_fine_calculation_on_return(): void
    {
        $unit1 = Unit::factory()->create(['equipment_id' => $this->equipment->id, 'unit_code' => 'FINE-001', 'status' => UnitStatus::Dipinjam]);
        $unit2 = Unit::factory()->create(['equipment_id' => $this->equipment->id, 'unit_code' => 'FINE-002', 'status' => UnitStatus::Dipinjam]);

        // Kasus A: Dipinjam 1 Nov (due 6 Nov), dikembalikan 6 Nov -> denda 0
        $loanA = Loan::factory()->create([
            'member_id' => $this->member->id,
            'unit_id' => $unit1->id,
            'approved_by' => $this->admin->id,
            'requested_at' => Carbon::parse('2026-11-01 08:00:00'),
            'approved_at' => Carbon::parse('2026-11-01 09:00:00'),
            'borrow_date' => Carbon::parse('2026-11-01'),
            'due_date' => Carbon::parse('2026-11-06'),
            'status' => LoanStatus::Aktif,
        ]);

        $returnedA = $this->loanService->returnLoan($loanA, $this->admin, Carbon::parse('2026-11-06'));

        $this->assertSame(LoanStatus::Dikembalikan, $returnedA->status);
        $this->assertSame(0, $returnedA->late_days);
        $this->assertEquals(0, (float) $returnedA->fine_amount);
        $this->assertSame(UnitStatus::Tersedia, $unit1->fresh()->status);

        // Kasus B: Dipinjam 1 Nov (due 6 Nov), dikembalikan 8 Nov -> late_days 2, fine_amount 20000000
        $loanB = Loan::factory()->create([
            'member_id' => $this->member->id,
            'unit_id' => $unit2->id,
            'approved_by' => $this->admin->id,
            'requested_at' => Carbon::parse('2026-11-01 08:00:00'),
            'approved_at' => Carbon::parse('2026-11-01 09:00:00'),
            'borrow_date' => Carbon::parse('2026-11-01'),
            'due_date' => Carbon::parse('2026-11-06'),
            'status' => LoanStatus::Aktif,
        ]);

        $returnedB = $this->loanService->returnLoan($loanB, $this->admin, Carbon::parse('2026-11-08'));

        $this->assertSame(LoanStatus::Dikembalikan, $returnedB->status);
        $this->assertSame(2, $returnedB->late_days);
        $this->assertEquals(20000000, (float) $returnedB->fine_amount);
        $this->assertSame(UnitStatus::Tersedia, $unit2->fresh()->status);
    }

    /**
     * Test 5: Anggota tidak bisa memanggil returnLoan, approve, atau reject.
     */
    public function test_5_member_cannot_call_approve_reject_or_return_loan(): void
    {
        $unit = Unit::factory()->create(['equipment_id' => $this->equipment->id, 'unit_code' => 'AUTH-001', 'status' => UnitStatus::Tersedia]);
        $loan = $this->loanService->request($this->member, $unit);

        // Anggota mencoba approve
        try {
            $this->loanService->approve($loan, $this->member);
            $this->fail('Anggota seharusnya tidak diizinkan memanggil approve.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Hanya admin', $e->getMessage());
        }

        // Anggota mencoba reject
        try {
            $this->loanService->reject($loan, $this->member);
            $this->fail('Anggota seharusnya tidak diizinkan memanggil reject.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Hanya admin', $e->getMessage());
        }

        // Setujui dengan admin agar status aktif
        $this->loanService->approve($loan, $this->admin);

        // Anggota mencoba returnLoan
        try {
            $this->loanService->returnLoan($loan, $this->member);
            $this->fail('Anggota seharusnya tidak diizinkan memanggil returnLoan.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Hanya admin', $e->getMessage());
        }
    }

    /**
     * Test 6: Anggota hanya melihat pinjaman miliknya sendiri.
     */
    public function test_6_member_only_sees_their_own_loans(): void
    {
        $otherMember = User::factory()->anggota()->create(['username' => 'other_user']);
        Profile::factory()->create(['user_id' => $otherMember->id]);

        $unitMy = Unit::factory()->create(['equipment_id' => $this->equipment->id, 'unit_code' => 'MY-UNIT-01', 'status' => UnitStatus::Tersedia]);
        $unitOther = Unit::factory()->create(['equipment_id' => $this->equipment->id, 'unit_code' => 'OTHER-UNIT-01', 'status' => UnitStatus::Tersedia]);

        $myLoan = $this->loanService->request($this->member, $unitMy);
        $otherLoan = $this->loanService->request($otherMember, $unitOther);

        // Bertindak sebagai $this->member
        $this->actingAs($this->member);

        $component = Volt::test('pages.my-loans');

        $component
            ->assertSee('MY-UNIT-01')
            ->assertDontSee('OTHER-UNIT-01');
    }

    /**
     * Test 7: Kode unit duplikat ditolak.
     */
    public function test_7_duplicate_unit_code_is_rejected(): void
    {
        Unit::create([
            'equipment_id' => $this->equipment->id,
            'unit_code' => 'DUP-001',
            'status' => UnitStatus::Tersedia,
        ]);

        $this->expectException(QueryException::class);

        Unit::create([
            'equipment_id' => $this->equipment->id,
            'unit_code' => 'DUP-001',
            'status' => UnitStatus::Tersedia,
        ]);
    }

    /**
     * Test 8: Admin tidak bisa menghapus unit atau anggota yang punya pinjaman menunggu atau aktif.
     */
    public function test_8_admin_cannot_delete_unit_or_member_with_pending_or_active_loan(): void
    {
        $unitPending = Unit::factory()->create(['equipment_id' => $this->equipment->id, 'unit_code' => 'DEL-P-001', 'status' => UnitStatus::Tersedia]);
        $loanPending = $this->loanService->request($this->member, $unitPending);

        // Anggota punya pinjaman menunggu -> tidak bisa dihapus
        try {
            $this->member->delete();
            $this->fail('Anggota dengan pinjaman menunggu seharusnya tidak bisa dihapus.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Anggota tidak dapat dihapus', $e->getMessage());
        }

        // Unit punya pinjaman menunggu -> tidak bisa dihapus
        try {
            $unitPending->delete();
            $this->fail('Unit dengan pinjaman menunggu seharusnya tidak bisa dihapus.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Unit tidak dapat dihapus', $e->getMessage());
        }

        // Setujui menjadi pinjaman aktif
        $this->loanService->approve($loanPending, $this->admin);

        // Anggota punya pinjaman aktif -> tidak bisa dihapus
        try {
            $this->member->delete();
            $this->fail('Anggota dengan pinjaman aktif seharusnya tidak bisa dihapus.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Anggota tidak dapat dihapus', $e->getMessage());
        }

        // Unit punya pinjaman aktif -> tidak bisa dihapus
        try {
            $unitPending->delete();
            $this->fail('Unit dengan pinjaman aktif seharusnya tidak bisa dihapus.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Unit tidak dapat dihapus', $e->getMessage());
        }

        // Kembalikan pinjaman sehingga selesai
        $this->loanService->returnLoan($loanPending, $this->admin);

        // Setelah selesai, anggota dan unit bisa di-soft-delete tanpa error
        $this->member->delete();
        $this->assertSoftDeleted('users', ['id' => $this->member->id]);

        $unitPending->delete();
        $this->assertSoftDeleted('units', ['id' => $unitPending->id]);
    }

    /**
     * Test ekstra: MySQL active_unit_id stored column mencegah 2 loan berstatus menunggu/aktif pada 1 unit.
     */
    public function test_extra_mysql_stored_column_prevents_concurrent_active_loans_on_same_unit(): void
    {
        $unit = Unit::factory()->create(['equipment_id' => $this->equipment->id, 'unit_code' => 'CONCURR-001', 'status' => UnitStatus::Tersedia]);

        Loan::create([
            'member_id' => $this->member->id,
            'unit_id' => $unit->id,
            'requested_at' => now(),
            'status' => LoanStatus::Menunggu,
        ]);

        $this->expectException(QueryException::class);

        // Bypass service langsung ke DB untuk menguji constraint kolom virtual/stored MySQL
        Loan::create([
            'member_id' => $this->member->id,
            'unit_id' => $unit->id,
            'requested_at' => now(),
            'status' => LoanStatus::Menunggu,
        ]);
    }
}
