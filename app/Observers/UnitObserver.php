<?php

namespace App\Observers;

use App\Models\Unit;
use DomainException;

class UnitObserver
{
    /**
     * Handle the Unit "deleting" event.
     */
    public function deleting(Unit $unit): void
    {
        if ($unit->hasActiveOrPendingLoans()) {
            throw new DomainException('Unit tidak dapat dihapus karena masih memiliki pinjaman berstatus menunggu atau aktif.');
        }
    }
}
