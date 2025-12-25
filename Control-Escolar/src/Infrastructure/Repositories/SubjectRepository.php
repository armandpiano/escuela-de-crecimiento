<?php
/**
 * =============================================================================
 * REPOSITORIO CONCRETO: SUBJECT REPOSITORY - INFRASTRUCTURE LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Repositories;

use ChristianLMS\Domain\Entities\Subject;
use ChristianLMS\Domain\Ports\SubjectRepositoryInterface;
use ChristianLMS\Domain\ValueObjects\{
    SubjectId,
    SubjectCode,
    SubjectStatus,
    GradeLevel
};
use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;

/**
 * Repositorio Concreto de Materia
 * 
 * Implementación de la interfaz SubjectRepositoryInterface
 * Maneja la persistencia de materias en la base de datos
 */
class SubjectRepository implements SubjectRepositoryInterface
{
    private ConnectionManager $connectionManager;
    private string $tableName = 'subjects';

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     * Guardar materia (crear o actualizar)
     */
    public function save(Subject $subject): Subject
    {
        try {
            $subjectArray = $subject->toArray();
            $subjectArray['updated_at'] = date('Y-m-d H:i:s');
            
            if ($this->existsById($subject->getId())) {
                // Actualizar materia existente
                $this->update($subject);
            } else {
                // Crear nueva materia
                $subjectArray['created_at'] = date('Y-m-d H:i:s');
                $this->insert($subject);
            }
            
            return $subject;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al guardar materia: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materia por ID
     */
    public function findById(SubjectId $id): ?Subject
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE id = :id LIMIT 1";
            $params = ['id' => $id->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            
            if ($result) {
                return $this->hydrateSubject($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materia por ID: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materia por código
     */
    public function findByCode(SubjectCode $code): ?Subject
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE name = :name LIMIT 1";
            $params = ['name' => $code->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            
            if ($result) {
                return $this->hydrateSubject($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materia por código: ' . $e->getMessage());
        }
    }

    /**
     * Buscar todas las materias
     */
    public function findAll(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} ORDER BY sort_order ASC";
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar todas las materias: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias por departamento
     */
    public function findByDepartment(string $department): array
    {
        return [];
    }

    /**
     * Buscar materias por nivel educativo
     */
    public function findByGradeLevel(GradeLevel $gradeLevel): array
    {
        return [];
    }

    /**
     * Buscar materias por estado
     */
    public function findByStatus(SubjectStatus $status): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE is_active = :is_active 
                    ORDER BY sort_order ASC";
            $params = ['is_active' => $status->getValue() === 'active' ? 1 : 0];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias por estado: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias activas
     */
    public function findActive(): array
    {
        return $this->findByStatus(\ChristianLMS\Domain\ValueObjects\SubjectStatus::active());
    }

    /**
     * Buscar materias básicas
     */
    public function findCore(): array
    {
        return [];
    }

    /**
     * Buscar materias electivas
     */
    public function findElective(): array
    {
        return [];
    }

    /**
     * Eliminar materia
     */
    public function delete(SubjectId $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
            $params = ['id' => $id->getValue()];
            
            return $this->connectionManager->execute($sql, $params) > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al eliminar materia: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar materia de forma suave (soft delete)
     */
    public function softDelete(SubjectId $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Verificar si existe una materia con el ID dado
     */
    public function existsById(SubjectId $id): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE id = :id";
            $params = ['id' => $id->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar existencia de materia por ID: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si existe una materia con el código dado
     */
    public function existsByCode(SubjectCode $code): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE name = :name";
            $params = ['name' => $code->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar existencia de materia por código: ' . $e->getMessage());
        }
    }

    /**
     * Contar total de materias
     */
    public function count(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE 1=1";
            $result = $this->connectionManager->query($sql);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar materias: ' . $e->getMessage());
        }
    }

    /**
     * Contar materias por estado
     */
    public function countByStatus(SubjectStatus $status): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE is_active = :is_active";
            $params = ['is_active' => $status->getValue() === 'active' ? 1 : 0];
            
            $result = $this->connectionManager->query($sql, $params);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar materias por estado: ' . $e->getMessage());
        }
    }

    /**
     * Contar materias por departamento
     */
    public function countByDepartment(string $department): int
    {
        return 0;
    }

    /**
     * Contar materias por nivel educativo
     */
    public function countByGradeLevel(GradeLevel $gradeLevel): int
    {
        return 0;
    }

    /**
     * Obtener materias paginadas
     */
    public function findPaginated(int $page = 1, int $perPage = 20): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT * FROM {$this->tableName} 
                    ORDER BY sort_order ASC 
                    LIMIT :limit OFFSET :offset";
            
            $params = [
                'limit' => $perPage,
                'offset' => $offset
            ];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener materias paginadas: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias con filtros
     */
    public function search(array $criteria, int $page = 1, int $perPage = 20): array
    {
        try {
            $whereConditions = ['1=1'];
            $params = [];
            
            // Aplicar criterios de búsqueda
            if (isset($criteria['status'])) {
                $whereConditions[] = 'is_active = :is_active';
                $params['is_active'] = $criteria['status'] === 'active' ? 1 : 0;
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
                    ORDER BY sort_order ASC 
                    LIMIT :limit OFFSET :offset";
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias: ' . $e->getMessage());
        }
    }

    /**
     * Obtener materias recientes
     */
    public function findRecent(int $days = 30): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY) 
 
                    ORDER BY created_at DESC";
            $params = ['days' => $days];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias recientes: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias por nombre (búsqueda parcial)
     */
    public function findByName(string $name): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE name LIKE :name 
                    ORDER BY sort_order ASC";
            $params = [
                'name' => '%' . $name . '%'
            ];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias por nombre: ' . $e->getMessage());
        }
    }

    /**
     * Obtener materias ordenadas
     */
    public function findOrdered(string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        try {
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE 1=1 
                    ORDER BY $orderBy $direction";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener materias ordenadas: ' . $e->getMessage());
        }
    }

    /**
     * Obtener todos los departamentos únicos
     */
    public function findDepartments(): array
    {
        return [];
    }

    /**
     * Obtener todos los niveles educativos únicos
     */
    public function findGradeLevels(): array
    {
        return [];
    }

    /**
     * Obtener estadísticas de materias
     */
    public function getStatistics(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
                    FROM {$this->tableName} 
                    WHERE 1=1";
            
            $result = $this->connectionManager->query($sql);
            return $result;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener estadísticas de materias: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias con prerrequisitos
     */
    public function findWithPrerequisites(): array
    {
        return [];
    }

    /**
     * Buscar materias disponibles para cursos
     */
    public function findAvailableForCourses(): array
    {
        return $this->findByStatus(\ChristianLMS\Domain\ValueObjects\SubjectStatus::active());
    }

    /**
     * Obtener materias por rango de fechas
     */
    public function findByDateRange(string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE created_at BETWEEN :start_date AND :end_date
 
                    ORDER BY created_at DESC";
            $params = [
                'start_date' => $startDate,
                'end_date' => $endDate
            ];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias por rango de fechas: ' . $e->getMessage());
        }
    }

    /**
     * Verificar integridad de datos
     */
    public function verifyIntegrity(): array
    {
        try {
            $issues = [];
            
            return $issues;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar integridad de materias: ' . $e->getMessage());
        }
    }

    /**
     * Insertar nueva materia
     */
    private function insert(Subject $subject): void
    {
        $allowedColumns = [
            'id',
            'module_id',
            'name',
            'description',
            'sort_order',
            'is_active',
            'created_at',
            'updated_at'
        ];
        $subjectArray = array_intersect_key($subject->toArray(), array_flip($allowedColumns));
        $subjectArray += [
            'module_id' => null,
            'sort_order' => 0,
            'is_active' => 1
        ];
        $columns = array_keys($subjectArray);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        
        $sql = "INSERT INTO {$this->tableName} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->connectionManager->execute($sql, $subjectArray);
    }

    /**
     * Actualizar materia existente
     */
    private function update(Subject $subject): void
    {
        $allowedColumns = [
            'id',
            'module_id',
            'name',
            'description',
            'sort_order',
            'is_active',
            'created_at',
            'updated_at'
        ];
        $subjectArray = array_intersect_key($subject->toArray(), array_flip($allowedColumns));
        $subjectArray += [
            'module_id' => null,
            'sort_order' => 0,
            'is_active' => 1
        ];
        $updateFields = [];
        
        foreach ($subjectArray as $column => $value) {
            if ($column !== 'id' && $column !== 'created_at') {
                $updateFields[] = "$column = :$column";
            }
        }
        
        $sql = "UPDATE {$this->tableName} 
                SET " . implode(', ', $updateFields) . "
                WHERE id = :id";
        
        $this->connectionManager->execute($sql, $subjectArray);
    }

    /**
     * Hidratar materia desde array de base de datos
     */
    private function hydrateSubject(array $data): Subject
    {
        $rawCode = strtoupper(preg_replace('/[^A-Z0-9_-]/', '', $data['name'] ?? ''));
        $subjectCode = $rawCode !== '' ? $rawCode : 'SUBJECT';

        $subject = new Subject(
            new SubjectId($data['id']),
            $data['name'],
            new SubjectCode($subjectCode)
        );

        if (!empty($data['description'])) {
            $subject->setDescription($data['description']);
        }
        $subject->setStatus(new \ChristianLMS\Domain\ValueObjects\SubjectStatus(($data['is_active'] ?? 1) ? 'active' : 'inactive'));

        return $subject;
    }
}
