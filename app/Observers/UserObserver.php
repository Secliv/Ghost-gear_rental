<?php

namespace App\Observers;

use App\Models\User;
use DomainException;

class UserObserver
{
    /**
     * Handle the User "deleting" event.
     */
    public function deleting(User $user): void
    {
        if ($user->hasActiveOrPendingLoans()) {
            throw new DomainException('Anggota tidak dapat dihapus karena masih memiliki pinjaman berstatus menunggu atau aktif.');
        }
    }
}
