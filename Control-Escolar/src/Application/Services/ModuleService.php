<?php
/**
 * =============================================================================
 * SERVICIO: MODULE SERVICE - APPLICATION LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Application\Services;

use ChristianLMS\Infrastructure\Repositories\ModuleRepository;

class ModuleService
{
    private ModuleRepository $moduleRepository;

    public function __construct(ModuleRepository $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }

    public function getAll(): array
    {
        return $this->moduleRepository->findAll();
    }

    public function getActive(): array
    {
        return $this->moduleRepository->findActive();
    }

    public function create(array $data): string
    {
        return $this->moduleRepository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->moduleRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->moduleRepository->delete($id);
    }
}
