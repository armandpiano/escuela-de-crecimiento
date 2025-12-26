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
use ChristianLMS\Infrastructure\Persistence\Schema\SchemaMap;

/**
 * Repositorio Concreto de Curso
 * 
 * Implementación de la interfaz CourseRepositoryInterface
 * Maneja la persistencia de cursos en la base de datos
 */
class CourseRepository implements CourseRepositoryInterface
{
    /** @var ConnectionManager */
    private $connectionManager;
    /** @var string */
    private $tableName = 'courses';

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
        $this->tableName = SchemaMap::table('courses');
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
            $sql = "SELECT * FROM {$this->tableName} WHERE id = :id LIMIT 1";
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
            $sql = "SELECT * FROM {$this->tableName} WHERE group_name = :group_name LIMIT 1";
            $params = ['group_name' => $code->getValue()];
            
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
            $sql = "SELECT * FROM {$this->tableName} ORDER BY created_at DESC";
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
                    ORDER BY c.created_at DESC";
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
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE subject_id = :subject_id 
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
                    WHERE status = :status 
                    ORDER BY created_at DESC";
            $params = ['status' => $this->mapStatusToDb($status)];
            
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
            $sql = "SELECT c.*, COUNT(e.id) as enrolled_count
                    FROM {$this->tableName} c
                    LEFT JOIN enrollments e ON e.course_id = c.id
                    WHERE c.status = 'open'
                    GROUP BY c.id
                    HAVING enrolled_count < c.capacity
                    ORDER BY c.created_at DESC";
            
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
                    WHERE modality IN ('zoom', 'mixto') 
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
                    WHERE modality = 'presencial' 
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
                    SET status = 'archived' 
                    WHERE id = :id";
            $params = [
                'id' => $id->getValue()
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE group_name = :group_name";
            $params = ['group_name' => $code->getValue()];
            
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName}";
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
            $params = ['status' => $this->mapStatusToDb($status)];
            
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
            $sql = "SELECT COUNT(DISTINCT c.id) as count FROM {$this->tableName} c
                    INNER JOIN course_teachers ct ON ct.course_id = c.id
                    WHERE ct.teacher_id = :teacher_id";
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
            
            $sql = "SELECT * FROM {$this->tableName} 
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
            $whereConditions = ['1=1'];
            $params = [];
            
            // Aplicar criterios de búsqueda
            if (isset($criteria['status'])) {
                $whereConditions[] = 'status = :status';
                $params['status'] = $criteria['status'] === 'active' ? 'open' : $criteria['status'];
            }
            
            if (isset($criteria['subject_id'])) {
                $whereConditions[] = 'subject_id = :subject_id';
                $params['subject_id'] = $criteria['subject_id'];
            }
            
            if (isset($criteria['name'])) {
                $whereConditions[] = 'group_name LIKE :name';
                $params['name'] = '%' . $criteria['name'] . '%';
            }
            
            if (isset($criteria['code'])) {
                $whereConditions[] = 'group_name LIKE :code';
                $params['code'] = '%' . $criteria['code'] . '%';
            }
            
            if (isset($criteria['is_virtual'])) {
                $whereConditions[] = 'modality = :modality';
                $params['modality'] = $criteria['is_virtual'] ? 'zoom' : 'presencial';
            }
            
            if (isset($criteria['available_spots_only']) && $criteria['available_spots_only']) {
                $whereConditions[] = 'capacity > 0';
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
                    WHERE group_name LIKE :name 
                    ORDER BY group_name ASC";
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
    public function findOrdered(string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        try {
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
            if (!SchemaMap::hasColumn($this->tableName, $orderBy)) {
                $orderBy = 'created_at';
            }
            
            $sql = "SELECT * FROM {$this->tableName} 
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
                    WHERE term_id = :term_id 
                    ORDER BY created_at DESC";
            $params = ['term_id' => $academicPeriodId];
            
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
                        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
                        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
                        SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived,
                        SUM(CASE WHEN modality IN ('zoom','mixto') THEN 1 ELSE 0 END) as virtual,
                        SUM(CASE WHEN modality = 'presencial' THEN 1 ELSE 0 END) as in_person,
                        SUM(capacity) as total_capacity,
                        AVG(capacity) as avg_capacity
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
            $sql = "SELECT c.*, COUNT(e.id) as enrolled_count
                    FROM {$this->tableName} c
                    LEFT JOIN enrollments e ON e.course_id = c.id
                    WHERE c.status = 'open'
                    GROUP BY c.id
                    HAVING enrolled_count < c.capacity
                    ORDER BY c.created_at DESC";
            
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
            $sql = "SELECT c.* FROM {$this->tableName} c
                    INNER JOIN terms t ON t.id = c.term_id
                    WHERE (t.term_start BETWEEN :start_date AND :end_date 
                           OR t.term_end BETWEEN :start_date AND :end_date
                           OR (t.term_start <= :start_date AND t.term_end >= :end_date))
                    ORDER BY t.term_start ASC";
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
            $sql = "SELECT c.id, c.capacity, COUNT(e.id) as enrolled_count
                    FROM {$this->tableName} c
                    LEFT JOIN enrollments e ON e.course_id = c.id
                    GROUP BY c.id
                    HAVING enrolled_count > c.capacity";
            $results = $this->connectionManager->query($sql);
            if (!empty($results)) {
                $issues[] = 'Encontrados cursos con sobrecupo de estudiantes';
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
        $courseArray = $this->buildPersistencePayload($course);
        $columns = array_keys($courseArray);
        $placeholders = array_map(function ($col) {
            return ":$col";
        }, $columns);
        
        $sql = "INSERT INTO {$this->tableName} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->connectionManager->execute($sql, $courseArray);
    }

    /**
     * Actualizar curso existente
     */
    private function update(Course $course): void
    {
        $courseArray = $this->buildPersistencePayload($course);
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
        $teacherId = isset($data['teacher_id']) ? (string) $data['teacher_id'] : '0';
        $course = new Course(
            new CourseId($data['id']),
            $data['group_name'],
            new CourseCode($data['group_name']),
            UserId::fromString($teacherId),
            $data['subject_id'] ? new SubjectId($data['subject_id']) : null
        );

        $course->setDescription(null);
        $course->setAcademicPeriodId($data['term_id']);
        $course->setMaxStudents($data['capacity']);
        $course->setStartDate(null);
        $course->setEndDate(null);
        $course->setSchedule($data['schedule_label'] ? [$data['schedule_label']] : null);
        $course->setCredits(0);
        $course->setHoursPerWeek(0);
        $course->setStatus($this->mapStatusFromDb($data['status']));
        $course->setVirtual(in_array($data['modality'], ['zoom', 'mixto'], true));
        $course->setVirtualPlatform($data['modality']);
        $course->setVirtualLink($data['zoom_url']);
        $course->setPrerequisites(null);
        $course->setLearningObjectives(null);
        $course->setSyllabus(null);
        $course->setMaterials(null);
        $course->setAssessmentMethods(null);
        $course->setGradingScale(null);
        $course->setMetadata([]);

        return $course;
    }

    private function buildPersistencePayload(Course $course): array
    {
        $data = [
            'id' => $course->getId()->getValue(),
            'term_id' => $course->getAcademicPeriodId(),
            'subject_id' => $course->getSubjectId() ? $course->getSubjectId()->getValue() : null,
            'group_name' => $course->getName(),
            'schedule_label' => $course->getSchedule() ? implode(' | ', $course->getSchedule()) : '',
            'modality' => $course->isVirtual() ? 'zoom' : 'presencial',
            'zoom_url' => $course->getVirtualLink(),
            'pdf_path' => null,
            'capacity' => $course->getMaxStudents(),
            'status' => $this->mapStatusToDb($course->getStatus()),
            'created_at' => $course->getCreatedAt(),
            'updated_at' => $course->getUpdatedAt(),
        ];

        $allowed = array_flip(SchemaMap::columns($this->tableName));

        return array_intersect_key($data, $allowed);
    }

    private function mapStatusToDb(CourseStatus $status): string
    {
        switch ($status->getValue()) {
            case CourseStatus::ACTIVE:
                return 'open';
            case CourseStatus::ARCHIVED:
                return 'archived';
            case CourseStatus::COMPLETED:
            case CourseStatus::CANCELLED:
                return 'closed';
            case CourseStatus::DRAFT:
            default:
                return 'open';
        }
    }

    private function mapStatusFromDb(string $status): CourseStatus
    {
        switch ($status) {
            case 'open':
                return CourseStatus::active();
            case 'archived':
                return CourseStatus::archived();
            case 'closed':
            default:
                return CourseStatus::completed();
        }
    }
}
