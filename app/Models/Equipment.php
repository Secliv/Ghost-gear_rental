<?php

namespace App\Models;

use App\Enums\DeviceType;
use App\Enums\UnitStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'equipments';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'device_type',
        'description',
        'emf_range',
        'thermal_temp_range_c',
        'night_vision_resolution',
        'audio_record_hours',
        'battery_hours',
        'motion_sensor_type',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'device_type' => DeviceType::class,
            'audio_record_hours' => 'decimal:1',
            'battery_hours' => 'decimal:1',
        ];
    }

    /**
     * Categories of this equipment.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'equipment_categories');
    }

    /**
     * Units of this equipment.
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'equipment_id');
    }

    /**
     * Available units of this equipment.
     */
    public function availableUnits(): HasMany
    {
        return $this->units()->where('status', UnitStatus::Tersedia->value);
    }
}
