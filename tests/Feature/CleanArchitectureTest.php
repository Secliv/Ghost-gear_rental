<?php

namespace Tests\Feature;

use App\Application\Requests\RequestLoanRequest;
use App\Application\Requests\ReturnLoanRequest;
use App\Application\Requests\UpdateProfileRequest;
use App\Application\Resources\EquipmentResource as EquipmentDataResource;
use App\Application\Resources\LoanResource as LoanDataResource;
use App\Application\Services\EquipmentCatalogService;
use App\Application\Services\LoanService;
use App\Domain\Repositories\EquipmentRepositoryInterface;
use App\Domain\Repositories\LoanRepositoryInterface;
use App\Domain\Repositories\SettingRepositoryInterface;
use App\Domain\Repositories\UnitRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Enums\LoanStatus;
use App\Enums\UnitStatus;
use App\Infrastructure\Repositories\EloquentEquipmentRepository;
use App\Infrastructure\Repositories\EloquentLoanRepository;
use App\Infrastructure\Repositories\EloquentSettingRepository;
use App\Infrastructure\Repositories\EloquentUnitRepository;
use App\Infrastructure\Repositories\EloquentUserRepository;
use App\Models\Equipment;
use App\Models\Loan;
use App\Models\Profile;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CleanArchitectureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test container resolves all Domain repository interfaces to Infrastructure Eloquent implementations.
     */
    public function test_domain_interfaces_bound_to_infrastructure_implementations(): void
    {
        $this->assertInstanceOf(EloquentLoanRepository::class, app(LoanRepositoryInterface::class));
        $this->assertInstanceOf(EloquentUnitRepository::class, app(UnitRepositoryInterface::class));
        $this->assertInstanceOf(EloquentUserRepository::class, app(UserRepositoryInterface::class));
        $this->assertInstanceOf(EloquentEquipmentRepository::class, app(EquipmentRepositoryInterface::class));
        $this->assertInstanceOf(EloquentSettingRepository::class, app(SettingRepositoryInterface::class));
    }

    /**
     * Test Application Layer Services coordinate use case flows via repositories.
     */
    public function test_application_service_orchestrates_flow_via_repositories(): void
    {
        $admin = User::factory()->admin()->create(['username' => 'clean_admin']);
        Profile::factory()->create(['user_id' => $admin->id]);

        $member = User::factory()->anggota()->create(['username' => 'clean_member']);
        Profile::factory()->create(['user_id' => $member->id]);

        $equipment = Equipment::factory()->create(['name' => 'Quantum Spirit Box']);
        $unit = Unit::factory()->create([
            'equipment_id' => $equipment->id,
            'unit_code' => 'QSB-001',
            'status' => UnitStatus::Tersedia,
        ]);

        $loanService = app(LoanService::class);

        // 1. Request
        $loan = $loanService->request($member, $unit);
        $this->assertSame(LoanStatus::Menunggu, $loan->status);

        // 2. Approve
        $approved = $loanService->approve($loan, $admin);
        $this->assertSame(LoanStatus::Aktif, $approved->status);

        // 3. EquipmentCatalogService test
        $catalogService = app(EquipmentCatalogService::class);
        $catalogItems = $catalogService->getCatalogEquipments('Quantum');
        $this->assertCount(1, $catalogItems);
        $this->assertSame('Quantum Spirit Box', $catalogItems->first()->name);
    }

    /**
     * Test Application Form Requests validate incoming inputs.
     */
    public function test_application_requests_validate_input(): void
    {
        $requestLoanRules = (new RequestLoanRequest)->rules();
        $v1 = Validator::make(['unit_id' => null], $requestLoanRules);
        $this->assertTrue($v1->fails());
        $this->assertArrayHasKey('unit_id', $v1->errors()->toArray());

        $returnLoanRules = (new ReturnLoanRequest)->rules();
        $v2 = Validator::make(['return_date' => 'invalid-date'], $returnLoanRules);
        $this->assertTrue($v2->fails());

        $updateProfileRules = (new UpdateProfileRequest)->rules();
        $v3 = Validator::make(['full_name' => ''], $updateProfileRules);
        $this->assertTrue($v3->fails());
        $this->assertArrayHasKey('full_name', $v3->errors()->toArray());
    }

    /**
     * Test Application Resources format output data (Image 3 & 4).
     */
    public function test_application_resources_format_output_data(): void
    {
        $member = User::factory()->anggota()->create(['username' => 'res_user']);
        $equipment = Equipment::factory()->create(['name' => 'Ovilus V']);
        $unit = Unit::factory()->create(['equipment_id' => $equipment->id, 'unit_code' => 'OV5-001']);

        $loan = Loan::factory()->create([
            'member_id' => $member->id,
            'unit_id' => $unit->id,
            'status' => LoanStatus::Aktif,
        ]);

        $request = Request::create('/api/loans/'.$loan->id, 'GET');

        // Test LoanResource
        $loanResource = new LoanDataResource($loan);
        $loanData = $loanResource->toArray($request);

        $this->assertSame($loan->id, $loanData['id']);
        $this->assertSame('OV5-001', $loanData['unit_code']);
        $this->assertSame('Ovilus V', $loanData['equipment_name']);
        $this->assertSame('aktif', $loanData['status']);
        $this->assertSame('Aktif', $loanData['status_label']);

        // Test EquipmentResource
        $equipmentResource = new EquipmentDataResource($equipment);
        $equipmentData = $equipmentResource->toArray($request);

        $this->assertSame('Ovilus V', $equipmentData['name']);
        $this->assertArrayHasKey('specs', $equipmentData);
        $this->assertArrayHasKey('available_units', $equipmentData);
    }
}
