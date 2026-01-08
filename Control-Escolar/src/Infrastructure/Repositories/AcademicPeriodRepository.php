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
use ChristianLMS\Infrastructure\Persistence\Schema\SchemaMap;

/**
 * Repositorio Concreto de Periodo Académico
 * 
 * Implementación de la interfaz AcademicPeriodRepositoryInterface
 * Maneja la persistencia de periodos académicos en la base de datos
 */
class AcademicPeriodRepository implements AcademicPeriodRepositoryInterface
{
    /** @var ConnectionManager */
    private $connectionManager;
    /** @var string */
    private $tableName = 'academic_periods';

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
        $this->tableName = SchemaMap::table('academic_periods');
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
            $sql = "SELECT * FROM {$this->tableName} WHERE code = :code LIMIT 1";
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
            $sql = "SELECT * FROM {$this->tableName} WHERE 1=1 ORDER BY term_start DESC";
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
                    ORDER BY term_start DESC";
            
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
                    ORDER BY term_start DESC 
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
        return $this->findAll();
    }

    /**
     * Buscar periodos por año académico
     */
    public function findByAcademicYear(int $year): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE YEAR(term_start) = :year 
                    ORDER BY term_start ASC";
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
                    WHERE term_start > CURDATE() 
                    ORDER BY term_start ASC";
            
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
                    WHERE term_start <= CURDATE() 
                    AND term_end >= CURDATE() 
                    
                    ORDER BY term_start DESC";
            
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
                    WHERE term_end < CURDATE() 
                    ORDER BY term_end DESC";
            
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
        return $this->findAll();
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
                    SET status = 'closed' 
                    WHERE id = :id";
            $params = [
                'id' => $id->getValue()
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE code = :code";
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
        return $this->count();
    }

    /**
     * Contar periodos por año académico
     */
    public function countByAcademicYear(int $year): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE YEAR(term_start) = :year";
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
                    WHERE 1=1 
                    ORDER BY term_start DESC 
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
            if (isset($criteria['is_active'])) {
                $whereConditions[] = 'status = :status';
                $params['status'] = $criteria['is_active'] ? 'active' : 'draft';
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
                    ORDER BY term_start DESC 
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
                    
                    ORDER BY term_start DESC";
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
        try {
            $sql = "SELECT DISTINCT YEAR(term_start) as academic_year FROM {$this->tableName} 
                    WHERE term_start IS NOT NULL 
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
        return AcademicPeriodType::getValidTypes();
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
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as current,
                        SUM(CASE WHEN term_start > CURDATE() THEN 1 ELSE 0 END) as upcoming,
                        SUM(CASE WHEN term_start <= CURDATE() AND term_end >= CURDATE() THEN 1 ELSE 0 END) as in_progress,
                        SUM(CASE WHEN term_end < CURDATE() THEN 1 ELSE 0 END) as ended
                    FROM {$this->tableName}";
            
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
                    WHERE inscriptions_start IS NOT NULL 
                    AND inscriptions_end IS NOT NULL 
                    AND inscriptions_start <= CURDATE() 
                    AND inscriptions_end >= CURDATE()
                    
                    ORDER BY inscriptions_start ASC";
            
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
                    ORDER BY term_start DESC";
            
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
                    WHERE (term_start BETWEEN :term_start AND :term_end 
                           OR term_end BETWEEN :term_start AND :term_end
                           OR (term_start <= :term_start AND term_end >= :term_end))
                    
                    ORDER BY term_start ASC";
            $params = [
                'term_start' => $startDate,
                'term_end' => $endDate
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
                    WHERE term_start > CURDATE() 
                    ORDER BY term_start ASC 
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
                    WHERE term_end < CURDATE() 
                    ORDER BY term_end DESC 
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
                    WHERE 1=1 
                    GROUP BY code HAVING count > 1";
            $results = $this->connectionManager->query($sql);
            if (!empty($results)) {
                $issues[] = 'Encontrados códigos de periodo duplicados';
            }
            
            // Verificar fechas inválidas
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE term_end <= term_start";
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
        $periodArray = $this->buildPersistencePayload($period);
        $columns = array_keys($periodArray);
        $placeholders = array_map(function ($col) {
            return ":$col";
        }, $columns);
        
        $sql = "INSERT INTO {$this->tableName} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->connectionManager->execute($sql, $periodArray);
    }

    /**
     * Actualizar periodo académico existente
     */
    private function update(AcademicPeriod $period): void
    {
        $periodArray = $this->buildPersistencePayload($period);
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
        $termStart = $data['term_start'] ?? $data['start_date'] ?? null;
        $termEnd = $data['term_end'] ?? $data['end_date'] ?? null;
        $academicYear = $termStart ? (int) date('Y', strtotime($termStart)) : (int) date('Y');

        $period = new AcademicPeriod(
            new AcademicPeriodId($data['id']),
            $data['name'],
            $data['code'],
            AcademicPeriodType::custom(),
            $termStart,
            $termEnd,
            $academicYear,
            1
        );

        $period->setRegistrationStart($data['inscriptions_start']);
        $period->setRegistrationEnd($data['inscriptions_end']);
        $period->setIsActive($data['status'] === 'active');
        $period->setIsCurrent($data['status'] === 'active');
        $period->setMaxStudentsPerCourse(0);
        $period->setGradingDeadline(null);
        $period->setTranscriptReleaseDate(null);
        $period->setNotes(null);
        $period->setMetadata([]);

        return $period;
    }

    private function buildPersistencePayload(AcademicPeriod $period): array
    {
        $data = [
            'id' => $period->getId()->getValue(),
            'code' => $period->getCode(),
            'name' => $period->getName(),
            'term_start' => $period->getStartDate(),
            'term_end' => $period->getEndDate(),
            'inscriptions_start' => $period->getRegistrationStart(),
            'inscriptions_end' => $period->getRegistrationEnd(),
            'status' => $period->isActive() ? 'active' : 'inactive',
            'created_at' => $period->getCreatedAt(),
            'updated_at' => $period->getUpdatedAt(),
            'enrollment_start' => null,
            'enrollment_end' => null
        ];

        $allowed = array_flip(SchemaMap::columns($this->tableName));

        return array_intersect_key($data, $allowed);
    }
}
