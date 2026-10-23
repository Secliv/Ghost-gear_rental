<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\EquipmentRepositoryInterface;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

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
        $descColumn = Schema::hasColumn('equipments', 'deskripsi')
            ? 'deskripsi'
            : (Schema::hasColumn('equipments', 'description') ? 'description' : null);

        return Equipment::query()
            ->with(['categories', 'units'])
            ->withCount(['units', 'availableUnits'])
            ->when($search, function ($query, $term) use ($descColumn) {
                $query->where(function ($sub) use ($term, $descColumn) {
                    $sub->where('name', 'like', "%{$term}%");

                    if ($descColumn) {
                        $sub->orWhere($descColumn, 'like', "%{$term}%");
                    }

                    $sub->orWhereHas('categories', function ($catSub) use ($term) {
                        $catSub->where('name', 'like', "%{$term}%");
                    });
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
