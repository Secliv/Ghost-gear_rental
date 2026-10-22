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
            'description' => $this->description,
            'categories' => $this->categories->pluck('name'),
            'total_units' => $this->units_count ?? $this->units->count(),
            'available_units' => $this->available_units_count ?? $this->availableUnits->count(),
            'specs' => array_filter([
                'emf_range' => $this->emf_range,
                'thermal_temp_range_c' => $this->thermal_temp_range_c,
                'night_vision_resolution' => $this->night_vision_resolution,
                'audio_record_hours' => $this->audio_record_hours ? (float) $this->audio_record_hours : null,
                'motion_sensor_type' => $this->motion_sensor_type,
                'battery_hours' => $this->battery_hours ? (float) $this->battery_hours : null,
            ]),
            'units' => UnitResource::collection($this->whenLoaded('units')),
        ];
    }
}
