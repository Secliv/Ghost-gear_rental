<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\EquipmentRepositoryInterface;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Collection;

class EloquentEquipmentRepository implements EquipmentRepositoryInterface
{
    public function findById(int $id): ?Equipment
    {
        return Equipment::query()->find($id);
    }

    public function findWithUnitsAndCategories(int $id): ?Equipment
    {
        return Equipment::query()
            ->with(['categories', 'units'])
            ->find($id);
    }

    public function getAllFiltered(?string $search = null, ?int $categoryId = null): Collection
    {
        return Equipment::query()
            ->with(['categories', 'units'])
            ->withCount(['units', 'availableUnits'])
            ->when($search, function ($query, $term) {
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->when($categoryId, function ($query, $catId) {
                $query->whereHas('categories', function ($catQuery) use ($catId) {
                    $catQuery->where('categories.id', $catId);
                });
            })
            ->latest()
            ->get();
    }

    public function create(array $data): Equipment
    {
        return Equipment::query()->create($data);
    }

    public function update(Equipment $equipment, array $data): Equipment
    {
        $equipment->update($data);

        return $equipment->fresh();
    }
}
