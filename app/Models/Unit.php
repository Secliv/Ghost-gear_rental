<?php

namespace App\Models;

use App\Enums\LoanStatus;
use App\Enums\UnitStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'equipment_id',
        'unit_code',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => UnitStatus::class,
        ];
    }

    /**
     * Equipment relation.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    /**
     * Loans of this unit.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'unit_id');
    }

    /**
     * Check if unit has active or pending loans.
     */
    public function hasActiveOrPendingLoans(): bool
    {
        return $this->loans()
            ->whereIn('status', [LoanStatus::Menunggu->value, LoanStatus::Aktif->value])
            ->exists();
    }
}
