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
        'deskripsi',
        'description',
        'rental_price_per_day',
        'image',
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
            'rental_price_per_day' => 'decimal:2',
        ];
    }

    /**
     * Get parsed specifications as an array of ['label' => ..., 'value' => ...] pairs.
     *
     * @return list<array{label: string, value: string}>
     */
    public function getSpecificationsAttribute(): array
    {
        $raw = $this->attributes['deskripsi'] ?? $this->attributes['description'] ?? null;
        if (empty($raw)) {
            return [];
        }

        $decoded = json_decode((string) $raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $specs = [];
            foreach ($decoded as $key => $item) {
                if (is_array($item)) {
                    $label = $item['title'] ?? $item['label'] ?? $item['name'] ?? (string) $key;
                    $value = $item['value'] ?? $item['content'] ?? $item['isi'] ?? '';
                    if (trim((string) $label) !== '' || trim((string) $value) !== '') {
                        $specs[] = [
                            'label' => trim((string) $label),
                            'value' => trim((string) $value),
                        ];
                    }
                } elseif (is_string($item) || is_numeric($item)) {
                    $specs[] = [
                        'label' => trim((string) $key),
                        'value' => trim((string) $item),
                    ];
                }
            }

            if (! empty($specs)) {
                return $specs;
            }
        }

        // Fallback: parse multiline string (e.g. "Judul: Nilai")
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", (string) $raw));
        $specs = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_contains($line, ':')) {
                [$label, $value] = explode(':', $line, 2);
                $specs[] = [
                    'label' => trim($label),
                    'value' => trim($value),
                ];
            } else {
                $specs[] = [
                    'label' => 'Deskripsi',
                    'value' => $line,
                ];
            }
        }

        return $specs;
    }

    /**
     * Get specifications as an associative key-value map.
     *
     * @return array<string, string>
     */
    public function getSpecsMapAttribute(): array
    {
        $map = [];
        foreach ($this->specifications as $spec) {
            $map[$spec['label']] = $spec['value'];
        }

        return $map;
    }

    /**
     * Mutator/accessor alias for backwards compatibility with description.
     */
    public function getDescriptionAttribute(): ?string
    {
        $raw = $this->attributes['deskripsi'] ?? $this->attributes['description'] ?? null;
        if (empty($raw)) {
            return null;
        }

        $decoded = json_decode((string) $raw, true);
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                if (is_array($item) && strtolower($item['title'] ?? '') === 'deskripsi umum') {
                    return $item['value'];
                }
            }

            return null;
        }

        return (string) $raw;
    }

    public function setDescriptionAttribute(?string $value): void
    {
        $this->attributes['deskripsi'] = $value;
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
