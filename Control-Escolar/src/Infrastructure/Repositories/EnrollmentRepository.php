<?php
/**
 * =============================================================================
 * REPOSITORIO CONCRETO: ENROLLMENT REPOSITORY - INFRASTRUCTURE LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Repositories;

use ChristianLMS\Domain\Entities\Enrollment;
use ChristianLMS\Domain\Ports\EnrollmentRepositoryInterface;
use ChristianLMS\Domain\ValueObjects\{
    EnrollmentId,
    EnrollmentStatus,
    UserId,
    CourseId,
    AcademicPeriodId
};
use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;

class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    private ConnectionManager $connectionManager;
    private string $tableName = 'enrollments';

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    public function save(Enrollment $enrollment): Enrollment
    {
        try {
            if ($this->existsById($enrollment->getId())) {
                $this->update($enrollment);
            } else {
                $this->insert($enrollment);
            }

            return $enrollment;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al guardar inscripción: ' . $e->getMessage());
        }
    }

    public function findById(EnrollmentId $id): ?Enrollment
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE id = :id LIMIT 1";
            $result = $this->connectionManager->query($sql, ['id' => $id->getValue()]);

            return $result ? $this->hydrateEnrollment($result) : null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripción por ID: ' . $e->getMessage());
        }
    }

    public function findByUserAndCourse(UserId $userId, CourseId $courseId): ?Enrollment
    {
        try {
            $sql = "SELECT * FROM {$this->tableName}
                    WHERE user_id = :user_id AND course_id = :course_id
                    LIMIT 1";
            $params = [
                'user_id' => $userId->getValue(),
                'course_id' => $courseId->getValue()
            ];
            $result = $this->connectionManager->query($sql, $params);

            return $result ? $this->hydrateEnrollment($result) : null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripción por usuario y curso: ' . $e->getMessage());
        }
    }

    public function findAll(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} ORDER BY id DESC";
            $results = $this->connectionManager->query($sql);

            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar todas las inscripciones: ' . $e->getMessage());
        }
    }

    public function findByUser(UserId $userId): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE user_id = :user_id ORDER BY id DESC";
            $results = $this->connectionManager->query($sql, ['user_id' => $userId->getValue()]);

            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones por usuario: ' . $e->getMessage());
        }
    }

    public function findByCourse(CourseId $courseId): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE course_id = :course_id ORDER BY id DESC";
            $results = $this->connectionManager->query($sql, ['course_id' => $courseId->getValue()]);

            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones por curso: ' . $e->getMessage());
        }
    }

    public function findByAcademicPeriod(AcademicPeriodId $academicPeriodId): array
    {
        try {
            $sql = "SELECT e.* FROM {$this->tableName} e
                    INNER JOIN courses c ON c.id = e.course_id
                    WHERE c.academic_period_id = :academic_period_id
                    ORDER BY e.id DESC";
            $params = ['academic_period_id' => $academicPeriodId->getValue()];
            $results = $this->connectionManager->query($sql, $params);

            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones por periodo académico: ' . $e->getMessage());
        }
    }

    public function findByStatus(EnrollmentStatus $status): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE status = :status ORDER BY id DESC";
            $results = $this->connectionManager->query($sql, ['status' => $status->getValue()]);

            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones por estado: ' . $e->getMessage());
        }
    }

    public function findActive(): array
    {
        return $this->findByStatus(EnrollmentStatus::enrolled());
    }

    public function findCompleted(): array
    {
        return $this->findByStatus(EnrollmentStatus::completed());
    }

    public function findByProfessor(UserId $professorId): array
    {
        try {
            $sql = "SELECT e.* FROM {$this->tableName} e
                    INNER JOIN course_teachers ct ON ct.course_id = e.course_id
                    WHERE ct.teacher_id = :teacher_id
                    ORDER BY e.id DESC";
            $params = ['teacher_id' => $professorId->getValue()];
            $results = $this->connectionManager->query($sql, $params);

            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones por profesor: ' . $e->getMessage());
        }
    }

    public function delete(EnrollmentId $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
            return $this->connectionManager->execute($sql, ['id' => $id->getValue()]) > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al eliminar inscripción: ' . $e->getMessage());
        }
    }

    public function softDelete(EnrollmentId $id): bool
    {
        return $this->delete($id);
    }

    public function existsById(EnrollmentId $id): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE id = :id";
            $result = $this->connectionManager->query($sql, ['id' => $id->getValue()]);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar existencia de inscripción: ' . $e->getMessage());
        }
    }

    public function existsByUserCourse(UserId $userId, CourseId $courseId): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName}
                    WHERE user_id = :user_id AND course_id = :course_id";
            $params = [
                'user_id' => $userId->getValue(),
                'course_id' => $courseId->getValue()
            ];
            $result = $this->connectionManager->query($sql, $params);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar inscripción por usuario y curso: ' . $e->getMessage());
        }
    }

    public function count(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName}";
            $result = $this->connectionManager->query($sql);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar inscripciones: ' . $e->getMessage());
        }
    }

    public function countByStatus(EnrollmentStatus $status): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = :status";
            $result = $this->connectionManager->query($sql, ['status' => $status->getValue()]);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar inscripciones por estado: ' . $e->getMessage());
        }
    }

    public function countByCourse(CourseId $courseId): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE course_id = :course_id";
            $result = $this->connectionManager->query($sql, ['course_id' => $courseId->getValue()]);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar inscripciones por curso: ' . $e->getMessage());
        }
    }

    public function countByUser(UserId $userId): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE user_id = :user_id";
            $result = $this->connectionManager->query($sql, ['user_id' => $userId->getValue()]);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar inscripciones por usuario: ' . $e->getMessage());
        }
    }

    public function findPaginated(int $page = 1, int $perPage = 20): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT * FROM {$this->tableName}
                    ORDER BY id DESC
                    LIMIT :limit OFFSET :offset";
            $params = [
                'limit' => $perPage,
                'offset' => $offset
            ];
            $results = $this->connectionManager->query($sql, $params);
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener inscripciones paginadas: ' . $e->getMessage());
        }
    }

    public function search(array $criteria, int $page = 1, int $perPage = 20): array
    {
        try {
            $whereConditions = ['1=1'];
            $params = [];

            if (isset($criteria['status'])) {
                $whereConditions[] = 'status = :status';
                $params['status'] = $criteria['status'];
            }

            if (isset($criteria['user_id'])) {
                $whereConditions[] = 'user_id = :user_id';
                $params['user_id'] = $criteria['user_id'];
            }

            if (isset($criteria['course_id'])) {
                $whereConditions[] = 'course_id = :course_id';
                $params['course_id'] = $criteria['course_id'];
            }

            $offset = ($page - 1) * $perPage;
            $params['limit'] = $perPage;
            $params['offset'] = $offset;

            $sql = "SELECT * FROM {$this->tableName}
                    WHERE " . implode(' AND ', $whereConditions) . "
                    ORDER BY id DESC
                    LIMIT :limit OFFSET :offset";

            $results = $this->connectionManager->query($sql, $params);
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones: ' . $e->getMessage());
        }
    }

    public function findRecent(int $days = 30): array
    {
        return $this->findPaginated(1, 20);
    }

    public function findOrdered(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        try {
            $allowedColumns = ['id', 'status', 'user_id', 'course_id'];
            $orderBy = in_array($orderBy, $allowedColumns, true) ? $orderBy : 'id';
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

            $sql = "SELECT * FROM {$this->tableName} ORDER BY {$orderBy} {$direction}";
            $results = $this->connectionManager->query($sql);
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener inscripciones ordenadas: ' . $e->getMessage());
        }
    }

    public function getStatistics(): array
    {
        try {
            $sql = "SELECT
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'enrolled' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'dropped' THEN 1 ELSE 0 END) as dropped
                    FROM {$this->tableName}";
            return $this->connectionManager->query($sql);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener estadísticas de inscripciones: ' . $e->getMessage());
        }
    }

    public function verifyIntegrity(): array
    {
        return [];
    }

    private function insert(Enrollment $enrollment): void
    {
        $allowedColumns = [
            'id',
            'user_id',
            'course_id',
            'status',
            'enrolled_by',
            'override_seriation',
            'override_schedule'
        ];
        $enrollmentArray = array_intersect_key($enrollment->toArray(), array_flip($allowedColumns));
        $columns = array_keys($enrollmentArray);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = "INSERT INTO {$this->tableName} (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")";
        $this->connectionManager->execute($sql, $enrollmentArray);
    }

    private function update(Enrollment $enrollment): void
    {
        $allowedColumns = [
            'id',
            'user_id',
            'course_id',
            'status',
            'enrolled_by',
            'override_seriation',
            'override_schedule'
        ];
        $enrollmentArray = array_intersect_key($enrollment->toArray(), array_flip($allowedColumns));
        $updateFields = [];

        foreach ($enrollmentArray as $column => $value) {
            if ($column !== 'id') {
                $updateFields[] = "{$column} = :{$column}";
            }
        }

        $sql = "UPDATE {$this->tableName}
                SET " . implode(', ', $updateFields) . "
                WHERE id = :id";
        $this->connectionManager->execute($sql, $enrollmentArray);
    }

    private function hydrateEnrollment(array $data): Enrollment
    {
        $enrollment = new Enrollment(
            new EnrollmentId($data['id']),
            new UserId($data['user_id']),
            new CourseId($data['course_id'])
        );

        if (!empty($data['status'])) {
            $enrollment->setStatus(new EnrollmentStatus($data['status']));
        }

        if (!empty($data['enrolled_by'])) {
            $enrollment->setEnrolledBy(new UserId($data['enrolled_by']));
        }

        $enrollment->setOverrideSeriation(!empty($data['override_seriation']));
        $enrollment->setOverrideSchedule(!empty($data['override_schedule']));

        return $enrollment;
    }
}
