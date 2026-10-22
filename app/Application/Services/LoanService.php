<?php

namespace App\Application\Services;

use App\Domain\Repositories\LoanRepositoryInterface;
use App\Domain\Repositories\SettingRepositoryInterface;
use App\Domain\Repositories\UnitRepositoryInterface;
use App\Enums\LoanStatus;
use App\Enums\UnitStatus;
use App\Enums\UserRole;
use App\Models\Loan;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class LoanService
{
    /**
     * Create a new LoanService instance using Clean Architecture dependency injection.
     */
    public function __construct(
        protected LoanRepositoryInterface $loanRepository,
        protected UnitRepositoryInterface $unitRepository,
        protected SettingRepositoryInterface $settingRepository,
    ) {}

    /**
     * Request a loan for a member and a physical unit.
     *
     * @throws DomainException
     */
    public function request(User $member, Unit $unit): Loan
    {
        if ($member->role !== UserRole::Anggota || ! $member->is_active) {
            throw new DomainException('Hanya anggota aktif yang dapat mengajukan pinjaman.');
        }

        return DB::transaction(function () use ($member, $unit): Loan {
            $lockedUnit = $this->unitRepository->findById($unit->id, lock: true);

            if (! $lockedUnit) {
                throw new DomainException('Unit tidak ditemukan.');
            }

            if ($lockedUnit->status !== UnitStatus::Tersedia) {
                throw new DomainException('Unit tidak berstatus tersedia.');
            }

            $maxActiveLoans = (int) config('ghostgear.max_active_loans', 2);
            $activeCount = $this->loanRepository->countActiveOrPendingByMember($member->id, lock: true);

            if ($activeCount >= $maxActiveLoans) {
                throw new DomainException("Anggota sudah mencapai batas maksimal pinjaman aktif ({$maxActiveLoans}).");
            }

            $loan = $this->loanRepository->create([
                'member_id' => $member->id,
                'unit_id' => $lockedUnit->id,
                'requested_at' => Carbon::now(),
                'status' => LoanStatus::Menunggu,
            ]);

            $this->unitRepository->update($lockedUnit, [
                'status' => UnitStatus::Ditahan,
            ]);

            return $loan;
        });
    }

    /**
     * Approve a pending loan.
     *
     * @throws DomainException
     */
    public function approve(Loan $loan, User $admin): Loan
    {
        if ($admin->role !== UserRole::Admin) {
            throw new DomainException('Hanya admin yang dapat menyetujui pinjaman.');
        }

        return DB::transaction(function () use ($loan, $admin): Loan {
            $lockedUnit = $this->unitRepository->findById($loan->unit_id, lock: true);

            if (! $lockedUnit) {
                throw new DomainException('Unit terkait tidak ditemukan.');
            }

            if ($loan->status !== LoanStatus::Menunggu) {
                throw new DomainException('Hanya pinjaman berstatus menunggu yang dapat disetujui.');
            }

            $maxLoanDays = (int) config('ghostgear.max_loan_days', 5);
            $borrowDate = Carbon::now()->startOfDay();
            $dueDate = (clone $borrowDate)->addDays($maxLoanDays);

            $updatedLoan = $this->loanRepository->update($loan, [
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now(),
                'borrow_date' => $borrowDate,
                'due_date' => $dueDate,
                'status' => LoanStatus::Aktif,
            ]);

            $this->unitRepository->update($lockedUnit, [
                'status' => UnitStatus::Dipinjam,
            ]);

            return $updatedLoan;
        });
    }

    /**
     * Reject a pending loan.
     *
     * @throws DomainException
     */
    public function reject(Loan $loan, User $admin): Loan
    {
        if ($admin->role !== UserRole::Admin) {
            throw new DomainException('Hanya admin yang dapat menolak pinjaman.');
        }

        return DB::transaction(function () use ($loan): Loan {
            $lockedUnit = $this->unitRepository->findById($loan->unit_id, lock: true);

            if (! $lockedUnit) {
                throw new DomainException('Unit terkait tidak ditemukan.');
            }

            if ($loan->status !== LoanStatus::Menunggu) {
                throw new DomainException('Hanya pinjaman berstatus menunggu yang dapat ditolak.');
            }

            $updatedLoan = $this->loanRepository->update($loan, [
                'status' => LoanStatus::Ditolak,
            ]);

            $this->unitRepository->update($lockedUnit, [
                'status' => UnitStatus::Tersedia,
            ]);

            return $updatedLoan;
        });
    }

    /**
     * Process return of an active loan and calculate fines.
     *
     * @throws DomainException
     */
    public function returnLoan(Loan $loan, User $admin, ?Carbon $returnDate = null): Loan
    {
        if ($admin->role !== UserRole::Admin) {
            throw new DomainException('Hanya admin yang dapat mencatat pengembalian pinjaman.');
        }

        return DB::transaction(function () use ($loan, $admin, $returnDate): Loan {
            $lockedUnit = $this->unitRepository->findById($loan->unit_id, lock: true);

            if (! $lockedUnit) {
                throw new DomainException('Unit terkait tidak ditemukan.');
            }

            if ($loan->status !== LoanStatus::Aktif) {
                throw new DomainException('Hanya pinjaman berstatus aktif yang dapat dikembalikan.');
            }

            $actualReturnDate = ($returnDate ?? Carbon::now())->copy()->startOfDay();
            $dueDate = $loan->due_date ? Carbon::parse($loan->due_date)->startOfDay() : $actualReturnDate;

            $lateDays = 0;
            if ($actualReturnDate->gt($dueDate)) {
                $lateDays = (int) $dueDate->diffInDays($actualReturnDate, false);
            }

            $settingValue = $this->settingRepository->get('fine_per_day', '10000000');
            $finePerDay = (float) $settingValue;
            $fineAmount = $lateDays * $finePerDay;

            $updatedLoan = $this->loanRepository->update($loan, [
                'returned_by' => $admin->id,
                'return_date' => $actualReturnDate,
                'late_days' => $lateDays,
                'fine_per_day' => $finePerDay,
                'fine_amount' => $fineAmount,
                'status' => LoanStatus::Dikembalikan,
            ]);

            $this->unitRepository->update($lockedUnit, [
                'status' => UnitStatus::Tersedia,
            ]);

            return $updatedLoan;
        });
    }

    /**
     * Mark fine of a loan as paid.
     *
     * @throws DomainException
     */
    public function markFinePaid(Loan $loan, User $admin): Loan
    {
        if ($admin->role !== UserRole::Admin) {
            throw new DomainException('Hanya admin yang dapat menandai denda lunas.');
        }

        return DB::transaction(function () use ($loan): Loan {
            $this->unitRepository->findById($loan->unit_id, lock: true);

            return $this->loanRepository->update($loan, [
                'fine_paid' => true,
            ]);
        });
    }
}
