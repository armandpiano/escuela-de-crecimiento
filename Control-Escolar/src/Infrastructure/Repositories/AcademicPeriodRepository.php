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
            $periodArray['updated_at'] = date('Y-m-d H:i:s');
            
            if ($this->existsById($period->getId())) {
                // Actualizar periodo existente
                $this->update($period);
            } else {
                // Crear nuevo periodo
                $periodArray['created_at'] = date('Y-m-d H:i:s');
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
            $sql = "SELECT * FROM {$this->tableName} WHERE id = :id AND deleted_at IS NULL LIMIT 1";
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
            $sql = "SELECT * FROM {$this->tableName} WHERE code = :code AND deleted_at IS NULL LIMIT 1";
            $params = ['code' => $code];
            
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
            $sql = "SELECT * FROM {$this->tableName} WHERE deleted_at IS NULL ORDER BY academic_year DESC, period_number ASC";
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
                    WHERE is_active = 1 AND deleted_at IS NULL 
                    ORDER BY academic_year DESC, period_number ASC";
            
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
                    WHERE is_current = 1 AND deleted_at IS NULL 
                    ORDER BY academic_year DESC, period_number DESC 
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
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE type = :type AND deleted_at IS NULL 
                    ORDER BY academic_year DESC, period_number ASC";
            $params = ['type' => $type->getValue()];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodos por tipo: ' . $e->getMessage());
        }
    }

    /**
     * Buscar periodos por año académico
     */
    public function findByAcademicYear(int $year): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE academic_year = :year AND deleted_at IS NULL 
                    ORDER BY period_number ASC";
            $params = ['year' => $year];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodos por año académico: ' . $e->getMessage());
        }
    }

    /**
     * Buscar periodos futuros
     */
    public function findUpcoming(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE start_date > CURDATE() AND deleted_at IS NULL 
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
                    AND deleted_at IS NULL 
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
                    WHERE end_date < CURDATE() AND deleted_at IS NULL 
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
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE period_number = :period_number AND deleted_at IS NULL 
                    ORDER BY academic_year DESC";
            $params = ['period_number' => $periodNumber];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodos por número: ' . $e->getMessage());
        }
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
        try {
            $sql = "UPDATE {$this->tableName} 
                    SET deleted_at = :deleted_at 
                    WHERE id = :id AND deleted_at IS NULL";
            $params = [
                'id' => $id->getValue(),
                'deleted_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->connectionManager->execute($sql, $params) > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al eliminar periodo académico suavemente: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si existe un periodo académico con el ID dado
     */
    public function existsById(AcademicPeriodId $id): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE id = :id AND deleted_at IS NULL";
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE code = :code AND deleted_at IS NULL";
            $params = ['code' => $code];
            
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE deleted_at IS NULL";
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
                    WHERE is_active = 1 AND deleted_at IS NULL";
            
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
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE type = :type AND deleted_at IS NULL";
            $params = ['type' => $type->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar periodos por tipo: ' . $e->getMessage());
        }
    }

    /**
     * Contar periodos por año académico
     */
    public function countByAcademicYear(int $year): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE academic_year = :year AND deleted_at IS NULL";
            $params = ['year' => $year];
            
            $result = $this->connectionManager->query($sql, $params);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar periodos por año académico: ' . $e->getMessage());
        }
    }

    /**
     * Obtener periodos académicos paginados
     */
    public function findPaginated(int $page = 1, int $perPage = 20): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE deleted_at IS NULL 
                    ORDER BY academic_year DESC, period_number ASC 
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
            $whereConditions = ['deleted_at IS NULL'];
            $params = [];
            
            // Aplicar criterios de búsqueda
            if (isset($criteria['is_active'])) {
                $whereConditions[] = 'is_active = :is_active';
                $params['is_active'] = $criteria['is_active'] ? 1 : 0;
            }
            
            if (isset($criteria['is_current'])) {
                $whereConditions[] = 'is_current = :is_current';
                $params['is_current'] = $criteria['is_current'] ? 1 : 0;
            }
            
            if (isset($criteria['type'])) {
                $whereConditions[] = 'type = :type';
                $params['type'] = $criteria['type'];
            }
            
            if (isset($criteria['academic_year'])) {
                $whereConditions[] = 'academic_year = :academic_year';
                $params['academic_year'] = $criteria['academic_year'];
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
                    ORDER BY academic_year DESC, period_number ASC 
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
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY) 
                    AND deleted_at IS NULL 
                    ORDER BY created_at DESC";
            $params = ['days' => $days];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateAcademicPeriod'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar periodos académicos recientes: ' . $e->getMessage());
        }
    }

    /**
     * Buscar periodos académicos por nombre (búsqueda parcial)
     */
    public function findByName(string $name): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE (name LIKE :name OR code LIKE :code) 
                    AND deleted_at IS NULL 
                    ORDER BY academic_year DESC, period_number ASC";
            $params = [
                'name' => '%' . $name . '%',
                'code' => '%' . $name . '%'
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
    public function findOrdered(string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        try {
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE deleted_at IS NULL 
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
        try {
            $sql = "SELECT DISTINCT academic_year FROM {$this->tableName} 
                    WHERE deleted_at IS NULL 
                    ORDER BY academic_year DESC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_column($results, 'academic_year');
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener años académicos: ' . $e->getMessage());
        }
    }

    /**
     * Obtener tipos de periodo únicos
     */
    public function findTypes(): array
    {
        try {
            $sql = "SELECT DISTINCT type FROM {$this->tableName} 
                    WHERE deleted_at IS NULL 
                    ORDER BY type ASC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_column($results, 'type');
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener tipos de periodo: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas de periodos académicos
     */
    public function getStatistics(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN is_current = 1 THEN 1 ELSE 0 END) as current,
                        SUM(CASE WHEN start_date > CURDATE() THEN 1 ELSE 0 END) as upcoming,
                        SUM(CASE WHEN start_date <= CURDATE() AND end_date >= CURDATE() THEN 1 ELSE 0 END) as in_progress,
                        SUM(CASE WHEN end_date < CURDATE() THEN 1 ELSE 0 END) as ended,
                        AVG(max_students_per_course) as avg_max_students
                    FROM {$this->tableName} 
                    WHERE deleted_at IS NULL";
            
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
                    WHERE registration_start IS NOT NULL 
                    AND registration_end IS NOT NULL 
                    AND registration_start <= CURDATE() 
                    AND registration_end >= CURDATE()
                    AND deleted_at IS NULL 
                    ORDER BY registration_start ASC";
            
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
                    WHERE is_active = 1 AND deleted_at IS NULL 
                    ORDER BY academic_year DESC, period_number ASC";
            
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
                    AND deleted_at IS NULL 
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
                    WHERE start_date > CURDATE() AND deleted_at IS NULL 
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
                    WHERE end_date < CURDATE() AND deleted_at IS NULL 
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
            
            // Verificar códigos duplicados
            $sql = "SELECT code, COUNT(*) as count FROM {$this->tableName} 
                    WHERE deleted_at IS NULL 
                    GROUP BY code HAVING count > 1";
            $results = $this->connectionManager->query($sql);
            if (!empty($results)) {
                $issues[] = 'Encontrados códigos de periodo duplicados';
            }
            
            // Verificar fechas inválidas
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE end_date <= start_date AND deleted_at IS NULL";
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
        $periodArray = $period->toArray();
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
        $periodArray = $period->toArray();
        $updateFields = [];
        
        foreach ($periodArray as $column => $value) {
            if ($column !== 'id' && $column !== 'created_at') {
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
        $period = new AcademicPeriod(
            new AcademicPeriodId($data['id']),
            $data['name'],
            $data['code'],
            new AcademicPeriodType($data['type']),
            $data['start_date'],
            $data['end_date'],
            $data['academic_year'],
            $data['period_number']
        );

        $period->setRegistrationStart($data['registration_start']);
        $period->setRegistrationEnd($data['registration_end']);
        $period->setIsActive($data['is_active']);
        $period->setIsCurrent($data['is_current']);
        $period->setMaxStudentsPerCourse($data['max_students_per_course']);
        $period->setGradingDeadline($data['grading_deadline']);
        $period->setTranscriptReleaseDate($data['transcript_release_date']);
        $period->setNotes($data['notes']);
        $period->setMetadata(json_decode($data['metadata'], true) ?? []);

        return $period;
    }
}
