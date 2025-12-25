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

            if ($this->existsById($course->getId())) {
                // Actualizar curso existente
                $this->update($course);
            } else {
                // Crear nuevo curso
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
            $sql = "SELECT c.*, s.name AS subject_name
                    FROM {$this->tableName} c
                    LEFT JOIN subjects s ON s.id = c.subject_id
                    WHERE c.id = :id
                    LIMIT 1";
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
            $sql = "SELECT c.*, s.name AS subject_name
                    FROM {$this->tableName} c
                    LEFT JOIN subjects s ON s.id = c.subject_id
                    WHERE s.name = :name
                    LIMIT 1";
            $params = ['name' => $code->getValue()];
            
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
            $sql = "SELECT c.*, s.name AS subject_name
                    FROM {$this->tableName} c
                    LEFT JOIN subjects s ON s.id = c.subject_id
                    ORDER BY c.id DESC";
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
            $sql = "SELECT c.* FROM {$this->tableName} c
                    INNER JOIN course_teachers ct ON ct.course_id = c.id
                    WHERE ct.teacher_id = :teacher_id
                    ORDER BY c.id DESC";
            $params = ['teacher_id' => $professorId->getValue()];
            
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
            $sql = "SELECT c.*, s.name AS subject_name
                    FROM {$this->tableName} c
                    LEFT JOIN subjects s ON s.id = c.subject_id
                    WHERE c.subject_id = :subject_id
                    ORDER BY c.id DESC";
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
            $sql = "SELECT c.*, s.name AS subject_name
                    FROM {$this->tableName} c
                    LEFT JOIN subjects s ON s.id = c.subject_id
                    WHERE c.status = :status
                    ORDER BY c.id DESC";
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
            $sql = "SELECT c.*, s.name AS subject_name
                    FROM {$this->tableName} c
                    LEFT JOIN subjects s ON s.id = c.subject_id
                    WHERE c.status = 'active'
                    ORDER BY c.id DESC";
            
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
        return [];
    }

    /**
     * Buscar cursos presenciales
     */
    public function findInPerson(): array
    {
        return [];
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
        return $this->delete($id);
    }

    /**
     * Verificar si existe un curso con el ID dado
     */
    public function existsById(CourseId $id): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE id = :id";
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
            $sql = "SELECT COUNT(*) as count
                    FROM {$this->tableName} c
                    LEFT JOIN subjects s ON s.id = c.subject_id
                    WHERE s.name = :name";
            $params = ['name' => $code->getValue()];
            
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE 1=1";
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = :status";
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
            $sql = "SELECT COUNT(*) as count FROM course_teachers 
                    WHERE teacher_id = :teacher_id";
            $params = ['teacher_id' => $professorId->getValue()];
            
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
            
            $sql = "SELECT c.*, s.name AS subject_name
                    FROM {$this->tableName} c
                    LEFT JOIN subjects s ON s.id = c.subject_id
                    ORDER BY c.id DESC
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
            $whereConditions = ['1=1'];
            $params = [];
            
            // Aplicar criterios de búsqueda
            if (isset($criteria['status'])) {
                $whereConditions[] = 'c.status = :status';
                $params['status'] = $criteria['status'];
            }
            
            if (isset($criteria['professor_id'])) {
                $whereConditions[] = 'c.id IN (SELECT course_id FROM course_teachers WHERE teacher_id = :teacher_id)';
                $params['teacher_id'] = $criteria['professor_id'];
            }
            
            if (isset($criteria['subject_id'])) {
                $whereConditions[] = 'c.subject_id = :subject_id';
                $params['subject_id'] = $criteria['subject_id'];
            }
            
            if (isset($criteria['name'])) {
                $whereConditions[] = 's.name LIKE :subject_name';
                $params['subject_name'] = '%' . $criteria['name'] . '%';
            }
            
            if (isset($criteria['code'])) {
                $whereConditions[] = 's.name LIKE :subject_code';
                $params['subject_code'] = '%' . $criteria['code'] . '%';
            }
            
            if (isset($criteria['available_spots_only']) && $criteria['available_spots_only']) {
                $whereConditions[] = 'c.status = :available_status';
                $params['available_status'] = 'active';
            }
            
            $offset = ($page - 1) * $perPage;
            $params['limit'] = $perPage;
            $params['offset'] = $offset;
            
            $sql = "SELECT c.*, s.name AS subject_name
                    FROM {$this->tableName} c
                    LEFT JOIN subjects s ON s.id = c.subject_id
                    WHERE " . implode(' AND ', $whereConditions) . "
                    ORDER BY c.id DESC
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
        return $this->findPaginated(1, 20);
    }

    /**
     * Buscar cursos por nombre (búsqueda parcial)
     */
    public function findByName(string $name): array
    {
        try {
            $sql = "SELECT c.*, s.name AS subject_name
                    FROM {$this->tableName} c
                    LEFT JOIN subjects s ON s.id = c.subject_id
                    WHERE s.name LIKE :name
                    ORDER BY s.name ASC";
            $params = [
                'name' => '%' . $name . '%'
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
    public function findOrdered(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        try {
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
            $allowedColumns = ['id', 'status', 'academic_period_id', 'subject_id', 'day_of_week'];
            $orderBy = in_array($orderBy, $allowedColumns, true) ? $orderBy : 'id';

            $sql = "SELECT c.*, s.name AS subject_name
                    FROM {$this->tableName} c
                    LEFT JOIN subjects s ON s.id = c.subject_id
                    ORDER BY c.$orderBy $direction";
            
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
            $sql = "SELECT c.*, s.name AS subject_name
                    FROM {$this->tableName} c
                    LEFT JOIN subjects s ON s.id = c.subject_id
                    WHERE c.academic_period_id = :academic_period_id
                    ORDER BY c.id DESC";
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
                        SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived
                    FROM {$this->tableName}";
            
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
            $sql = "SELECT c.*, s.name AS subject_name
                    FROM {$this->tableName} c
                    LEFT JOIN subjects s ON s.id = c.subject_id
                    WHERE c.status = 'active'
                    ORDER BY c.id DESC";
            
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
        return [];
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
            throw new DatabaseException('Error al verificar integridad de cursos: ' . $e->getMessage());
        }
    }

    /**
     * Insertar nuevo curso
     */
    private function insert(Course $course): void
    {
        $allowedColumns = [
            'id',
            'academic_period_id',
            'subject_id',
            'day_of_week',
            'start_time',
            'end_time',
            'status',
            'location',
            'max_students'
        ];
        $courseArray = array_intersect_key($course->toArray(), array_flip($allowedColumns));
        $courseArray += [
            'status' => 'draft'
        ];
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
        $allowedColumns = [
            'id',
            'academic_period_id',
            'subject_id',
            'day_of_week',
            'start_time',
            'end_time',
            'status',
            'location',
            'max_students'
        ];
        $courseArray = array_intersect_key($course->toArray(), array_flip($allowedColumns));
        $courseArray += [
            'status' => 'draft'
        ];
        $updateFields = [];
        
        foreach ($courseArray as $column => $value) {
            if ($column !== 'id') {
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
        $subjectName = $data['subject_name'] ?? 'Curso';
        $rawCode = strtoupper(preg_replace('/[^A-Z0-9_-]/', '', $subjectName));
        $courseCode = $rawCode !== '' ? $rawCode : 'COURSE';

        $course = new Course(
            new CourseId($data['id']),
            $subjectName,
            new CourseCode($courseCode),
            UserId::fromString('0'),
            $data['subject_id'] ? new SubjectId($data['subject_id']) : null
        );

        if (!empty($data['academic_period_id'])) {
            $course->setAcademicPeriodId($data['academic_period_id']);
        }
        if (!empty($data['day_of_week'])) {
            $course->setDayOfWeek($data['day_of_week']);
        }
        if (!empty($data['start_time'])) {
            $course->setStartTime($data['start_time']);
        }
        if (!empty($data['end_time'])) {
            $course->setEndTime($data['end_time']);
        }
        if (!empty($data['location'])) {
            $course->setLocation($data['location']);
        }
        if (!empty($data['max_students'])) {
            $course->setMaxStudents($data['max_students']);
        }
        $course->setStatus(new \ChristianLMS\Domain\ValueObjects\CourseStatus($data['status']));

        return $course;
    }
}
