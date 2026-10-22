<?php

namespace App\Application\Services;

use App\Domain\Repositories\EquipmentRepositoryInterface;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Collection;

class EquipmentCatalogService
{
    /**
     * Create a new EquipmentCatalogService instance.
     */
    public function __construct(
        protected EquipmentRepositoryInterface $equipmentRepository,
    ) {}

    /**
     * Get all catalog equipments with optional filters.
     *
     * @return Collection<int, Equipment>
     */
    public function getCatalogEquipments(?string $search = null, ?int $categoryId = null): Collection
    {
        return $this->equipmentRepository->getAllFiltered($search, $categoryId);
    }

    /**
     * Get single equipment detail with relations.
     */
    public function getEquipmentDetail(int $id): ?Equipment
    {
        return $this->equipmentRepository->findWithUnitsAndCategories($id);
    }
}
