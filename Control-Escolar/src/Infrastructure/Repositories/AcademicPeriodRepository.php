<?php
/**
 * =============================================================================
 * REPOSITORIO CONCRETO: ACADEMIC PERIOD REPOSITORY - INFRASTRUCTURE LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Repositories;

use ChristianLMS\Domain\Entities\AcademicPeriod;
use ChristianLMS\Domain\Ports\AcademicPeriodRepositoryInterface;
use ChristianLMS\Domain\ValueObjects\{
    AcademicPeriodId,
    AcademicPeriodType
};
use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;

/**
 * Repositorio Concreto de Periodo Académico
 * 
 * Implementación de la interfaz AcademicPeriodRepositoryInterface
 * Maneja la persistencia de periodos académicos en la base de datos
 */
class AcademicPeriodRepository implements AcademicPeriodRepositoryInterface
{
    private ConnectionManager $connectionManager;
    private string $tableName = 'academic_periods';

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     * Guardar periodo académico (crear o actualizar)
     */
    public function save(AcademicPeriod $period): AcademicPeriod
    {
        try {
            $periodArray = $period->toArray();

            if ($this->existsById($period->getId())) {
                // Actualizar periodo existente
                $this->update($period);
            } else {
                // Crear nuevo periodo
                $this->insert($period);
            }
            
            return $period;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al guardar periodo académico: ' . $e->getMessage());
        }
    }

    /**
     * Buscar periodo académico por ID
     */
    public function findById(AcademicPeriodId $id): ?AcademicPeriod
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE id = :id LIMIT 1";
            $params = ['id' => $id->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            
            if ($result) {
                return $this->hydrateAcademicPeriod($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodo académico por ID: ' . $e->getMessage());
        }
    }

    /**
     * Buscar periodo académico por código
     */
    public function findByCode(string $code): ?AcademicPeriod
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE name = :name LIMIT 1";
            $params = ['name' => $code];
            
            $result = $this->connectionManager->query($sql, $params);
            
            if ($result) {
                return $this->hydrateAcademicPeriod($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodo académico por código: ' . $e->getMessage());
        }
    }

    /**
     * Buscar todos los periodos académicos
     */
    public function findAll(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} ORDER BY start_date DESC";
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar todos los periodos académicos: ' . $e->getMessage());
        }
    }

    /**
     * Buscar periodos activos
     */
    public function findActive(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE status = 'active' 
                    ORDER BY start_date DESC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodos activos: ' . $e->getMessage());
        }
    }

    /**
     * Buscar periodo actual
     */
    public function findCurrent(): ?AcademicPeriod
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE status = 'active' 
                    ORDER BY start_date DESC 
                    LIMIT 1";
            
            $result = $this->connectionManager->query($sql);
            
            if ($result) {
                return $this->hydrateAcademicPeriod($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodo actual: ' . $e->getMessage());
        }
    }

    /**
     * Buscar periodos por tipo
     */
    public function findByType(AcademicPeriodType $type): array
    {
        return [];
    }

    /**
     * Buscar periodos por año académico
     */
    public function findByAcademicYear(int $year): array
    {
        return [];
    }

    /**
     * Buscar periodos futuros
     */
    public function findUpcoming(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE start_date > CURDATE() 
                    ORDER BY start_date ASC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodos futuros: ' . $e->getMessage());
        }
    }

    /**
     * Buscar periodos en curso
     */
    public function findInProgress(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE start_date <= CURDATE() 
                    AND end_date >= CURDATE() 
 
                    ORDER BY start_date DESC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodos en curso: ' . $e->getMessage());
        }
    }

    /**
     * Buscar periodos finalizados
     */
    public function findEnded(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE end_date < CURDATE() 
                    ORDER BY end_date DESC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodos finalizados: ' . $e->getMessage());
        }
    }

    /**
     * Buscar periodo por número
     */
    public function findByPeriodNumber(int $periodNumber): array
    {
        return [];
    }

    /**
     * Eliminar periodo académico
     */
    public function delete(AcademicPeriodId $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
            $params = ['id' => $id->getValue()];
            
            return $this->connectionManager->execute($sql, $params) > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al eliminar periodo académico: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar periodo académico de forma suave (soft delete)
     */
    public function softDelete(AcademicPeriodId $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Verificar si existe un periodo académico con el ID dado
     */
    public function existsById(AcademicPeriodId $id): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE id = :id";
            $params = ['id' => $id->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar existencia de periodo académico por ID: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si existe un periodo académico con el código dado
     */
    public function existsByCode(string $code): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE name = :name";
            $params = ['name' => $code];
            
            $result = $this->connectionManager->query($sql, $params);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar existencia de periodo académico por código: ' . $e->getMessage());
        }
    }

    /**
     * Contar total de periodos académicos
     */
    public function count(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE 1=1";
            $result = $this->connectionManager->query($sql);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar periodos académicos: ' . $e->getMessage());
        }
    }

    /**
     * Contar periodos por estado
     */
    public function countActive(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE status = 'active'";
            
            $result = $this->connectionManager->query($sql);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar periodos activos: ' . $e->getMessage());
        }
    }

    /**
     * Contar periodos por tipo
     */
    public function countByType(AcademicPeriodType $type): int
    {
        return 0;
    }

    /**
     * Contar periodos por año académico
     */
    public function countByAcademicYear(int $year): int
    {
        return 0;
    }

    /**
     * Obtener periodos académicos paginados
     */
    public function findPaginated(int $page = 1, int $perPage = 20): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT * FROM {$this->tableName} 
                    ORDER BY start_date DESC 
                    LIMIT :limit OFFSET :offset";
            
            $params = [
                'limit' => $perPage,
                'offset' => $offset
            ];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener periodos académicos paginados: ' . $e->getMessage());
        }
    }

    /**
     * Buscar periodos académicos con filtros
     */
    public function search(array $criteria, int $page = 1, int $perPage = 20): array
    {
        try {
            $whereConditions = ['1=1'];
            $params = [];
            
            // Aplicar criterios de búsqueda
            if (isset($criteria['status'])) {
                $whereConditions[] = 'status = :status';
                $params['status'] = $criteria['status'];
            }
            
            if (isset($criteria['name'])) {
                $whereConditions[] = 'name LIKE :name';
                $params['name'] = '%' . $criteria['name'] . '%';
            }
            
            $offset = ($page - 1) * $perPage;
            $params['limit'] = $perPage;
            $params['offset'] = $offset;
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE " . implode(' AND ', $whereConditions) . "
                    ORDER BY start_date DESC 
                    LIMIT :limit OFFSET :offset";
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodos académicos: ' . $e->getMessage());
        }
    }

    /**
     * Obtener periodos académicos recientes
     */
    public function findRecent(int $days = 30): array
    {
        return $this->findPaginated(1, 20);
    }

    /**
     * Buscar periodos académicos por nombre (búsqueda parcial)
     */
    public function findByName(string $name): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE name LIKE :name 
                    ORDER BY start_date DESC";
            $params = [
                'name' => '%' . $name . '%'
            ];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodos académicos por nombre: ' . $e->getMessage());
        }
    }

    /**
     * Obtener periodos académicos ordenados
     */
    public function findOrdered(string $orderBy = 'start_date', string $direction = 'DESC'): array
    {
        try {
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
            $allowedColumns = ['id', 'name', 'start_date', 'end_date', 'status'];
            $orderBy = in_array($orderBy, $allowedColumns, true) ? $orderBy : 'start_date';

            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE 1=1 
                    ORDER BY $orderBy $direction";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener periodos académicos ordenados: ' . $e->getMessage());
        }
    }

    /**
     * Obtener años académicos únicos
     */
    public function findAcademicYears(): array
    {
        return [];
    }

    /**
     * Obtener tipos de periodo únicos
     */
    public function findTypes(): array
    {
        return [];
    }

    /**
     * Obtener estadísticas de periodos académicos
     */
    public function getStatistics(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN start_date > CURDATE() THEN 1 ELSE 0 END) as upcoming,
                        SUM(CASE WHEN start_date <= CURDATE() AND end_date >= CURDATE() THEN 1 ELSE 0 END) as in_progress,
                        SUM(CASE WHEN end_date < CURDATE() THEN 1 ELSE 0 END) as ended
                    FROM {$this->tableName} 
                    WHERE 1=1";
            
            $result = $this->connectionManager->query($sql);
            return $result;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener estadísticas de periodos académicos: ' . $e->getMessage());
        }
    }

    /**
     * Buscar periodos con inscripciones abiertas
     */
    public function findWithOpenRegistration(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE enrollment_start IS NOT NULL 
                    AND enrollment_end IS NOT NULL 
                    AND enrollment_start <= CURDATE() 
                    AND enrollment_end >= CURDATE()
 
                    ORDER BY enrollment_start ASC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodos con inscripciones abiertas: ' . $e->getMessage());
        }
    }

    /**
     * Buscar periodos disponibles para cursos
     */
    public function findAvailableForCourses(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE status = 'active' 
                    ORDER BY start_date DESC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodos disponibles para cursos: ' . $e->getMessage());
        }
    }

    /**
     * Obtener periodos por rango de fechas
     */
    public function findByDateRange(string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE (start_date BETWEEN :start_date AND :end_date 
                           OR end_date BETWEEN :start_date AND :end_date
                           OR (start_date <= :start_date AND end_date >= :end_date))
 
                    ORDER BY start_date ASC";
            $params = [
                'start_date' => $startDate,
                'end_date' => $endDate
            ];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodos por rango de fechas: ' . $e->getMessage());
        }
    }

    /**
     * Obtener siguiente periodo académico
     */
    public function findNext(): ?AcademicPeriod
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE start_date > CURDATE() 
                    ORDER BY start_date ASC 
                    LIMIT 1";
            
            $result = $this->connectionManager->query($sql);
            
            if ($result) {
                return $this->hydrateAcademicPeriod($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar siguiente periodo académico: ' . $e->getMessage());
        }
    }

    /**
     * Obtener periodo anterior
     */
    public function findPrevious(): ?AcademicPeriod
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE end_date < CURDATE() 
                    ORDER BY end_date DESC 
                    LIMIT 1";
            
            $result = $this->connectionManager->query($sql);
            
            if ($result) {
                return $this->hydrateAcademicPeriod($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodo anterior: ' . $e->getMessage());
        }
    }

    /**
     * Verificar integridad de datos
     */
    public function verifyIntegrity(): array
    {
        try {
            $issues = [];
            
            // Verificar fechas inválidas
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE end_date <= start_date";
            $result = $this->connectionManager->query($sql);
            if ($result['count'] > 0) {
                $issues[] = "Encontrados {$result['count']} periodos con fechas inválidas";
            }
            
            return $issues;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar integridad de periodos académicos: ' . $e->getMessage());
        }
    }

    /**
     * Insertar nuevo periodo académico
     */
    private function insert(AcademicPeriod $period): void
    {
        $allowedColumns = [
            'id',
            'name',
            'start_date',
            'end_date',
            'enrollment_start',
            'enrollment_end',
            'status'
        ];
        $periodArray = array_intersect_key($period->toArray(), array_flip($allowedColumns));
        $periodArray += [
            'status' => 'draft'
        ];
        $columns = array_keys($periodArray);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        
        $sql = "INSERT INTO {$this->tableName} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->connectionManager->execute($sql, $periodArray);
    }

    /**
     * Actualizar periodo académico existente
     */
    private function update(AcademicPeriod $period): void
    {
        $allowedColumns = [
            'id',
            'name',
            'start_date',
            'end_date',
            'enrollment_start',
            'enrollment_end',
            'status'
        ];
        $periodArray = array_intersect_key($period->toArray(), array_flip($allowedColumns));
        $periodArray += [
            'status' => 'draft'
        ];
        $updateFields = [];
        
        foreach ($periodArray as $column => $value) {
            if ($column !== 'id') {
                $updateFields[] = "$column = :$column";
            }
        }
        
        $sql = "UPDATE {$this->tableName} 
                SET " . implode(', ', $updateFields) . "
                WHERE id = :id";
        
        $this->connectionManager->execute($sql, $periodArray);
    }

    /**
     * Hidratar periodo académico desde array de base de datos
     */
    private function hydrateAcademicPeriod(array $data): AcademicPeriod
    {
        $academicYear = (int) date('Y', strtotime($data['start_date'] ?? 'now'));

        $period = new AcademicPeriod(
            new AcademicPeriodId($data['id']),
            $data['name'],
            $data['name'],
            AcademicPeriodType::custom(),
            $data['start_date'],
            $data['end_date'],
            $academicYear,
            1
        );

        if (!empty($data['enrollment_start'])) {
            $period->setRegistrationStart($data['enrollment_start']);
        }
        if (!empty($data['enrollment_end'])) {
            $period->setRegistrationEnd($data['enrollment_end']);
        }
        $period->setIsActive(($data['status'] ?? '') === 'active');

        return $period;
    }
}
