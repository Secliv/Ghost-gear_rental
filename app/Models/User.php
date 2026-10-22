<?php

namespace App\Models;

use App\Enums\LoanStatus;
use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Determine if the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === UserRole::Admin && $this->is_active;
    }

    /**
     * Display name for Filament.
     */
    public function getFilamentName(): string
    {
        return $this->profile?->full_name ?? $this->username;
    }

    /**
     * Check if user has admin role.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Check if user has anggota role.
     */
    public function isAnggota(): bool
    {
        return $this->role === UserRole::Anggota;
    }

    /**
     * Profile relation (1 user 1 profile).
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Loans requested by this member.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'member_id');
    }

    /**
     * Loans approved by this user (admin).
     */
    public function approvedLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'approved_by');
    }

    /**
     * Loans returned and processed by this user (admin).
     */
    public function returnedLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'returned_by');
    }

    /**
     * Check if user has active or pending loans.
     */
    public function hasActiveOrPendingLoans(): bool
    {
        return $this->loans()
            ->whereIn('status', [LoanStatus::Menunggu->value, LoanStatus::Aktif->value])
            ->exists();
    }
}
