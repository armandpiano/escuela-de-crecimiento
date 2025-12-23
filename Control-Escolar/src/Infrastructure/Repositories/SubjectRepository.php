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
            $sql = "SELECT * FROM {$this->tableName} WHERE id = :id AND deleted_at IS NULL LIMIT 1";
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
            $sql = "SELECT * FROM {$this->tableName} WHERE code = :code AND deleted_at IS NULL LIMIT 1";
            $params = ['code' => $code->getValue()];
            
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
            $sql = "SELECT * FROM {$this->tableName} WHERE deleted_at IS NULL ORDER BY name ASC";
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
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE department = :department AND deleted_at IS NULL 
                    ORDER BY name ASC";
            $params = ['department' => $department];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias por departamento: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias por nivel educativo
     */
    public function findByGradeLevel(GradeLevel $gradeLevel): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE grade_level = :grade_level AND deleted_at IS NULL 
                    ORDER BY name ASC";
            $params = ['grade_level' => $gradeLevel->getValue()];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias por nivel educativo: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias por estado
     */
    public function findByStatus(SubjectStatus $status): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE status = :status AND deleted_at IS NULL 
                    ORDER BY name ASC";
            $params = ['status' => $status->getValue()];
            
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
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE is_core = 1 AND deleted_at IS NULL 
                    ORDER BY name ASC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias básicas: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias electivas
     */
    public function findElective(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE is_core = 0 AND deleted_at IS NULL 
                    ORDER BY name ASC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias electivas: ' . $e->getMessage());
        }
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
            throw new DatabaseException('Error al eliminar materia suavemente: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si existe una materia con el ID dado
     */
    public function existsById(SubjectId $id): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE id = :id AND deleted_at IS NULL";
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE code = :code AND deleted_at IS NULL";
            $params = ['code' => $code->getValue()];
            
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE deleted_at IS NULL";
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = :status AND deleted_at IS NULL";
            $params = ['status' => $status->getValue()];
            
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
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE department = :department AND deleted_at IS NULL";
            $params = ['department' => $department];
            
            $result = $this->connectionManager->query($sql, $params);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar materias por departamento: ' . $e->getMessage());
        }
    }

    /**
     * Contar materias por nivel educativo
     */
    public function countByGradeLevel(GradeLevel $gradeLevel): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE grade_level = :grade_level AND deleted_at IS NULL";
            $params = ['grade_level' => $gradeLevel->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar materias por nivel educativo: ' . $e->getMessage());
        }
    }

    /**
     * Obtener materias paginadas
     */
    public function findPaginated(int $page = 1, int $perPage = 20): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE deleted_at IS NULL 
                    ORDER BY name ASC 
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
            $whereConditions = ['deleted_at IS NULL'];
            $params = [];
            
            // Aplicar criterios de búsqueda
            if (isset($criteria['status'])) {
                $whereConditions[] = 'status = :status';
                $params['status'] = $criteria['status'];
            }
            
            if (isset($criteria['department'])) {
                $whereConditions[] = 'department = :department';
                $params['department'] = $criteria['department'];
            }
            
            if (isset($criteria['grade_level'])) {
                $whereConditions[] = 'grade_level = :grade_level';
                $params['grade_level'] = $criteria['grade_level'];
            }
            
            if (isset($criteria['is_core'])) {
                $whereConditions[] = 'is_core = :is_core';
                $params['is_core'] = $criteria['is_core'] ? 1 : 0;
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
                    ORDER BY name ASC 
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
                    AND deleted_at IS NULL 
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
                    WHERE (name LIKE :name OR code LIKE :code) 
                    AND deleted_at IS NULL 
                    ORDER BY name ASC";
            $params = [
                'name' => '%' . $name . '%',
                'code' => '%' . $name . '%'
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
                    WHERE deleted_at IS NULL 
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
        try {
            $sql = "SELECT DISTINCT department FROM {$this->tableName} 
                    WHERE department IS NOT NULL AND department != '' AND deleted_at IS NULL 
                    ORDER BY department ASC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_column($results, 'department');
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener departamentos: ' . $e->getMessage());
        }
    }

    /**
     * Obtener todos los niveles educativos únicos
     */
    public function findGradeLevels(): array
    {
        try {
            $sql = "SELECT DISTINCT grade_level FROM {$this->tableName} 
                    WHERE grade_level IS NOT NULL AND grade_level != '' AND deleted_at IS NULL 
                    ORDER BY grade_level ASC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_column($results, 'grade_level');
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener niveles educativos: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas de materias
     */
    public function getStatistics(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                        SUM(CASE WHEN status = 'deprecated' THEN 1 ELSE 0 END) as deprecated,
                        SUM(CASE WHEN is_core = 1 THEN 1 ELSE 0 END) as core,
                        SUM(CASE WHEN is_core = 0 THEN 1 ELSE 0 END) as elective,
                        AVG(credits) as avg_credits,
                        AVG(hours_per_week) as avg_hours_per_week
                    FROM {$this->tableName} 
                    WHERE deleted_at IS NULL";
            
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
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE prerequisites IS NOT NULL 
                    AND JSON_LENGTH(prerequisites) > 0 
                    AND deleted_at IS NULL 
                    ORDER BY name ASC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias con prerrequisitos: ' . $e->getMessage());
        }
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
                    AND deleted_at IS NULL 
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
            
            // Verificar códigos duplicados
            $sql = "SELECT code, COUNT(*) as count FROM {$this->tableName} 
                    WHERE deleted_at IS NULL 
                    GROUP BY code HAVING count > 1";
            $results = $this->connectionManager->query($sql);
            if (!empty($results)) {
                $issues[] = 'Encontrados códigos de materia duplicados';
            }
            
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
        $subjectArray = $subject->toArray();
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
        $subjectArray = $subject->toArray();
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
        $subject = new Subject(
            new SubjectId($data['id']),
            $data['name'],
            new SubjectCode($data['code'])
        );

        $subject->setDescription($data['description']);
        $subject->setDepartment($data['department']);
        $subject->setGradeLevel($data['grade_level'] ? new \ChristianLMS\Domain\ValueObjects\GradeLevel($data['grade_level']) : null);
        $subject->setIsCore($data['is_core']);
        $subject->setCredits($data['credits']);
        $subject->setHoursPerWeek($data['hours_per_week']);
        $subject->setPrerequisites(json_decode($data['prerequisites'], true));
        $subject->setLearningOutcomes($data['learning_outcomes']);
        $subject->setBibliography($data['bibliography']);
        $subject->setResources(json_decode($data['resources'], true));
        $subject->setStatus(new \ChristianLMS\Domain\ValueObjects\SubjectStatus($data['status']));
        $subject->setMetadata(json_decode($data['metadata'], true) ?? []);

        return $subject;
    }
}
