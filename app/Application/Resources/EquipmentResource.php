<?php

namespace App\Application\Resources;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Equipment
 */
class EquipmentResource extends JsonResource
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
            'name' => $this->name,
            'device_type' => $this->device_type->value,
            'device_type_label' => $this->device_type->label(),
            'deskripsi' => $this->deskripsi,
            'description' => $this->description,
            'categories' => $this->categories->pluck('name'),
            'total_units' => $this->units_count ?? $this->units->count(),
            'available_units' => $this->available_units_count ?? $this->availableUnits->count(),
            'specs' => $this->specs_map,
            'specifications' => $this->specifications,
            'units' => UnitResource::collection($this->whenLoaded('units')),
        ];
    }
}
