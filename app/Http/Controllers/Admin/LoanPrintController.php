<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Repositories\LoanRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanPrintController extends Controller
{
    /**
     * Create a new LoanPrintController instance with repository injection.
     */
    public function __construct(
        protected LoanRepositoryInterface $loanRepository,
    ) {}

    /**
     * Display printable loans history page.
     */
    public function __invoke(Request $request): View
    {
        $loans = $this->loanRepository->getAllForReport();

        return view('admin.loans.print', [
            'loans' => $loans,
            'printedAt' => now(),
        ]);
    }
}
