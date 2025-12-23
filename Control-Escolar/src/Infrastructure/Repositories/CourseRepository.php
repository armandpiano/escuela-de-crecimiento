<?php
/**
 * =============================================================================
 * REPOSITORIO CONCRETO: COURSE REPOSITORY - INFRASTRUCTURE LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Repositories;

use ChristianLMS\Domain\Entities\Course;
use ChristianLMS\Domain\Ports\CourseRepositoryInterface;
use ChristianLMS\Domain\ValueObjects\{
    CourseId,
    CourseCode,
    CourseStatus,
    UserId,
    SubjectId
};
use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;

/**
 * Repositorio Concreto de Curso
 * 
 * Implementación de la interfaz CourseRepositoryInterface
 * Maneja la persistencia de cursos en la base de datos
 */
class CourseRepository implements CourseRepositoryInterface
{
    private ConnectionManager $connectionManager;
    private string $tableName = 'courses';

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     * Guardar curso (crear o actualizar)
     */
    public function save(Course $course): Course
    {
        try {
            $courseArray = $course->toArray();
            $courseArray['updated_at'] = date('Y-m-d H:i:s');
            
            if ($this->existsById($course->getId())) {
                // Actualizar curso existente
                $this->update($course);
            } else {
                // Crear nuevo curso
                $courseArray['created_at'] = date('Y-m-d H:i:s');
                $this->insert($course);
            }
            
            return $course;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al guardar curso: ' . $e->getMessage());
        }
    }

    /**
     * Buscar curso por ID
     */
    public function findById(CourseId $id): ?Course
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE id = :id AND deleted_at IS NULL LIMIT 1";
            $params = ['id' => $id->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            
            if ($result) {
                return $this->hydrateCourse($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar curso por ID: ' . $e->getMessage());
        }
    }

    /**
     * Buscar curso por código
     */
    public function findByCode(CourseCode $code): ?Course
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE code = :code AND deleted_at IS NULL LIMIT 1";
            $params = ['code' => $code->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            
            if ($result) {
                return $this->hydrateCourse($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar curso por código: ' . $e->getMessage());
        }
    }

    /**
     * Buscar todos los cursos
     */
    public function findAll(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE deleted_at IS NULL ORDER BY created_at DESC";
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar todos los cursos: ' . $e->getMessage());
        }
    }

    /**
     * Buscar cursos por profesor
     */
    public function findByProfessor(UserId $professorId): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE professor_id = :professor_id AND deleted_at IS NULL 
                    ORDER BY created_at DESC";
            $params = ['professor_id' => $professorId->getValue()];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar cursos por profesor: ' . $e->getMessage());
        }
    }

    /**
     * Buscar cursos por materia
     */
    public function findBySubject(SubjectId $subjectId): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE subject_id = :subject_id AND deleted_at IS NULL 
                    ORDER BY created_at DESC";
            $params = ['subject_id' => $subjectId->getValue()];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar cursos por materia: ' . $e->getMessage());
        }
    }

    /**
     * Buscar cursos por estado
     */
    public function findByStatus(CourseStatus $status): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE status = :status AND deleted_at IS NULL 
                    ORDER BY created_at DESC";
            $params = ['status' => $status->getValue()];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar cursos por estado: ' . $e->getMessage());
        }
    }

    /**
     * Buscar cursos activos
     */
    public function findActive(): array
    {
        return $this->findByStatus(\ChristianLMS\Domain\ValueObjects\CourseStatus::active());
    }

    /**
     * Buscar cursos con cupo disponible
     */
    public function findWithAvailableSpots(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE current_students < max_students 
                    AND status = 'active' 
                    AND deleted_at IS NULL 
                    ORDER BY created_at DESC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar cursos con cupo disponible: ' . $e->getMessage());
        }
    }

    /**
     * Buscar cursos virtuales
     */
    public function findVirtual(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE is_virtual = 1 AND deleted_at IS NULL 
                    ORDER BY created_at DESC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar cursos virtuales: ' . $e->getMessage());
        }
    }

    /**
     * Buscar cursos presenciales
     */
    public function findInPerson(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE is_virtual = 0 AND deleted_at IS NULL 
                    ORDER BY created_at DESC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar cursos presenciales: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar curso
     */
    public function delete(CourseId $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
            $params = ['id' => $id->getValue()];
            
            return $this->connectionManager->execute($sql, $params) > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al eliminar curso: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar curso de forma suave (soft delete)
     */
    public function softDelete(CourseId $id): bool
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
            throw new DatabaseException('Error al eliminar curso suavemente: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si existe un curso con el ID dado
     */
    public function existsById(CourseId $id): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE id = :id AND deleted_at IS NULL";
            $params = ['id' => $id->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar existencia de curso por ID: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si existe un curso con el código dado
     */
    public function existsByCode(CourseCode $code): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE code = :code AND deleted_at IS NULL";
            $params = ['code' => $code->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar existencia de curso por código: ' . $e->getMessage());
        }
    }

    /**
     * Contar total de cursos
     */
    public function count(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE deleted_at IS NULL";
            $result = $this->connectionManager->query($sql);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar cursos: ' . $e->getMessage());
        }
    }

    /**
     * Contar cursos por estado
     */
    public function countByStatus(CourseStatus $status): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = :status AND deleted_at IS NULL";
            $params = ['status' => $status->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar cursos por estado: ' . $e->getMessage());
        }
    }

    /**
     * Contar cursos por profesor
     */
    public function countByProfessor(UserId $professorId): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE professor_id = :professor_id AND deleted_at IS NULL";
            $params = ['professor_id' => $professorId->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar cursos por profesor: ' . $e->getMessage());
        }
    }

    /**
     * Obtener cursos paginados
     */
    public function findPaginated(int $page = 1, int $perPage = 20): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE deleted_at IS NULL 
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $params = [
                'limit' => $perPage,
                'offset' => $offset
            ];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener cursos paginados: ' . $e->getMessage());
        }
    }

    /**
     * Buscar cursos con filtros
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
            
            if (isset($criteria['professor_id'])) {
                $whereConditions[] = 'professor_id = :professor_id';
                $params['professor_id'] = $criteria['professor_id'];
            }
            
            if (isset($criteria['subject_id'])) {
                $whereConditions[] = 'subject_id = :subject_id';
                $params['subject_id'] = $criteria['subject_id'];
            }
            
            if (isset($criteria['name'])) {
                $whereConditions[] = 'name LIKE :name';
                $params['name'] = '%' . $criteria['name'] . '%';
            }
            
            if (isset($criteria['code'])) {
                $whereConditions[] = 'code LIKE :code';
                $params['code'] = '%' . $criteria['code'] . '%';
            }
            
            if (isset($criteria['is_virtual'])) {
                $whereConditions[] = 'is_virtual = :is_virtual';
                $params['is_virtual'] = $criteria['is_virtual'] ? 1 : 0;
            }
            
            if (isset($criteria['available_spots_only']) && $criteria['available_spots_only']) {
                $whereConditions[] = 'current_students < max_students';
            }
            
            $offset = ($page - 1) * $perPage;
            $params['limit'] = $perPage;
            $params['offset'] = $offset;
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE " . implode(' AND ', $whereConditions) . "
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar cursos: ' . $e->getMessage());
        }
    }

    /**
     * Obtener cursos recientes
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
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar cursos recientes: ' . $e->getMessage());
        }
    }

    /**
     * Buscar cursos por nombre (búsqueda parcial)
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
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar cursos por nombre: ' . $e->getMessage());
        }
    }

    /**
     * Obtener cursos ordenados
     */
    public function findOrdered(string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        try {
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE deleted_at IS NULL 
                    ORDER BY $orderBy $direction";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener cursos ordenados: ' . $e->getMessage());
        }
    }

    /**
     * Buscar cursos por periodo académico
     */
    public function findByAcademicPeriod(string $academicPeriodId): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE academic_period_id = :academic_period_id AND deleted_at IS NULL 
                    ORDER BY created_at DESC";
            $params = ['academic_period_id' => $academicPeriodId];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar cursos por periodo académico: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas de cursos
     */
    public function getStatistics(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                        SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived,
                        SUM(CASE WHEN is_virtual = 1 THEN 1 ELSE 0 END) as virtual,
                        SUM(CASE WHEN is_virtual = 0 THEN 1 ELSE 0 END) as in_person,
                        SUM(current_students) as total_enrolled_students,
                        SUM(max_students) as total_capacity,
                        AVG(current_students) as avg_enrollment,
                        AVG(max_students) as avg_capacity,
                        AVG((current_students / NULLIF(max_students, 0)) * 100) as avg_occupancy_percent
                    FROM {$this->tableName} 
                    WHERE deleted_at IS NULL";
            
            $result = $this->connectionManager->query($sql);
            return $result;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener estadísticas de cursos: ' . $e->getMessage());
        }
    }

    /**
     * Buscar cursos que requieren inscripción
     */
    public function findAvailableForEnrollment(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE status = 'active' 
                    AND current_students < max_students 
                    AND deleted_at IS NULL 
                    ORDER BY created_at DESC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar cursos disponibles para inscripción: ' . $e->getMessage());
        }
    }

    /**
     * Obtener cursos por rango de fechas
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
            
            return array_map([$this, 'hydrateCourse'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar cursos por rango de fechas: ' . $e->getMessage());
        }
    }

    /**
     * Verificar integridad de datos
     */
    public function verifyIntegrity(): array
    {
        try {
            $issues = [];
            
            // Verificar cursos con más estudiantes inscritos que capacidad
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE current_students > max_students AND deleted_at IS NULL";
            $result = $this->connectionManager->query($sql);
            if ($result['count'] > 0) {
                $issues[] = "Encontrados {$result['count']} cursos con sobrepoblación";
            }
            
            // Verificar códigos duplicados
            $sql = "SELECT code, COUNT(*) as count FROM {$this->tableName} 
                    WHERE deleted_at IS NULL 
                    GROUP BY code HAVING count > 1";
            $results = $this->connectionManager->query($sql);
            if (!empty($results)) {
                $issues[] = 'Encontrados códigos de curso duplicados';
            }
            
            return $issues;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar integridad de cursos: ' . $e->getMessage());
        }
    }

    /**
     * Insertar nuevo curso
     */
    private function insert(Course $course): void
    {
        $courseArray = $course->toArray();
        $columns = array_keys($courseArray);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        
        $sql = "INSERT INTO {$this->tableName} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->connectionManager->execute($sql, $courseArray);
    }

    /**
     * Actualizar curso existente
     */
    private function update(Course $course): void
    {
        $courseArray = $course->toArray();
        $updateFields = [];
        
        foreach ($courseArray as $column => $value) {
            if ($column !== 'id' && $column !== 'created_at') {
                $updateFields[] = "$column = :$column";
            }
        }
        
        $sql = "UPDATE {$this->tableName} 
                SET " . implode(', ', $updateFields) . "
                WHERE id = :id";
        
        $this->connectionManager->execute($sql, $courseArray);
    }

    /**
     * Hidratar curso desde array de base de datos
     */
    private function hydrateCourse(array $data): Course
    {
        $course = new Course(
            new CourseId($data['id']),
            $data['name'],
            new CourseCode($data['code']),
            new UserId($data['professor_id']),
            $data['subject_id'] ? new SubjectId($data['subject_id']) : null
        );

        $course->setDescription($data['description']);
        $course->setAcademicPeriodId($data['academic_period_id']);
        $course->setMaxStudents($data['max_students']);
        $course->setStartDate($data['start_date']);
        $course->setEndDate($data['end_date']);
        $course->setSchedule(json_decode($data['schedule'], true));
        $course->setCredits($data['credits']);
        $course->setHoursPerWeek($data['hours_per_week']);
        $course->setStatus(new \ChristianLMS\Domain\ValueObjects\CourseStatus($data['status']));
        $course->setVirtual($data['is_virtual']);
        $course->setVirtualPlatform($data['virtual_platform']);
        $course->setVirtualLink($data['virtual_link']);
        $course->setPrerequisites(json_decode($data['prerequisites'], true));
        $course->setLearningObjectives($data['learning_objectives']);
        $course->setSyllabus($data['syllabus']);
        $course->setMaterials(json_decode($data['materials'], true));
        $course->setAssessmentMethods($data['assessment_methods']);
        $course->setGradingScale(json_decode($data['grading_scale'], true));
        $course->setMetadata(json_decode($data['metadata'], true) ?? []);

        return $course;
    }
}
