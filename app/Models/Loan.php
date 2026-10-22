<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'member_id',
        'unit_id',
        'approved_by',
        'returned_by',
        'requested_at',
        'approved_at',
        'borrow_date',
        'due_date',
        'return_date',
        'late_days',
        'fine_per_day',
        'fine_amount',
        'fine_paid',
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
            'status' => LoanStatus::class,
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'borrow_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
            'late_days' => 'integer',
            'fine_per_day' => 'decimal:2',
            'fine_amount' => 'decimal:2',
            'fine_paid' => 'boolean',
        ];
    }

    /**
     * Member who requested this loan.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Unit being loaned.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Admin who approved this loan.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Admin who accepted the return of this loan.
     */
    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    /**
     * Check if loan is currently overdue.
     */
    public function isLate(): bool
    {
        if ($this->status === LoanStatus::Aktif && $this->due_date) {
            return Carbon::now()->startOfDay()->gt($this->due_date->startOfDay());
        }

        return $this->late_days > 0;
    }
}
