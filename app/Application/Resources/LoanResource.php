<?php

namespace App\Application\Resources;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Loan
 */
class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'unit_id' => $this->unit_id,
            'unit_code' => $this->unit?->unit_code,
            'equipment_name' => $this->unit?->equipment?->name,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'requested_at' => $this->requested_at?->toISOString(),
            'borrow_date' => $this->borrow_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'return_date' => $this->return_date?->toDateString(),
            'late_days' => $this->late_days,
            'fine_per_day' => (float) $this->fine_per_day,
            'fine_amount' => (float) $this->fine_amount,
            'fine_paid' => $this->fine_paid,
            'is_late' => $this->isLate(),
        ];
    }
}
