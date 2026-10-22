<?php

namespace App\Domain\Repositories;

use App\Models\Equipment;
use Illuminate\Database\Eloquent\Collection;

interface EquipmentRepositoryInterface
{
    /**
     * Find equipment by ID.
     */
    public function findById(int $id): ?Equipment;

    /**
     * Find equipment with units and categories.
     */
    public function findWithUnitsAndCategories(int $id): ?Equipment;

    /**
     * Get all equipments with optional search and category filters.
     *
     * @return Collection<int, Equipment>
     */
    public function getAllFiltered(?string $search = null, ?int $categoryId = null): Collection;

    /**
     * Create equipment record.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Equipment;

    /**
     * Update equipment record.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Equipment $equipment, array $data): Equipment;
}
