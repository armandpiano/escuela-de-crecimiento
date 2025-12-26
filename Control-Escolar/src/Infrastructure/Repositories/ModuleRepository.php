<?php
/**
 * =============================================================================
 * REPOSITORIO CONCRETO: MODULE REPOSITORY - INFRASTRUCTURE LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Repositories;

use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;
use ChristianLMS\Infrastructure\Persistence\Schema\SchemaMap;

class ModuleRepository
{
    /** @var ConnectionManager */
    private $connectionManager;
    /** @var string */
    private $tableName = 'modules';

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
        $this->tableName = SchemaMap::table('modules');
    }

    public function findAll(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} ORDER BY sort_order ASC";
            return $this->connectionManager->select($sql);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener módulos: ' . $e->getMessage());
        }
    }

    public function findActive(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE is_active = 1 ORDER BY sort_order ASC";
            return $this->connectionManager->select($sql);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener módulos activos: ' . $e->getMessage());
        }
    }

    public function create(array $data): string
    {
        try {
            $sql = "INSERT INTO {$this->tableName} (code, name, sort_order, is_active, created_at, updated_at)
                    VALUES (:code, :name, :sort_order, :is_active, NOW(), NOW())";
            return $this->connectionManager->insert($sql, [
                'code' => $data['code'],
                'name' => $data['name'],
                'sort_order' => $data['sort_order'] ?? 1,
                'is_active' => $data['is_active'] ?? 1
            ]);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al crear módulo: ' . $e->getMessage());
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $sql = "UPDATE {$this->tableName}
                    SET code = :code,
                        name = :name,
                        sort_order = :sort_order,
                        is_active = :is_active,
                        updated_at = NOW()
                    WHERE id = :id";
            return $this->connectionManager->execute($sql, [
                'id' => $id,
                'code' => $data['code'],
                'name' => $data['name'],
                'sort_order' => $data['sort_order'] ?? 1,
                'is_active' => $data['is_active'] ?? 1
            ]);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al actualizar módulo: ' . $e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
            return $this->connectionManager->execute($sql, ['id' => $id]);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al eliminar módulo: ' . $e->getMessage());
        }
    }
}
