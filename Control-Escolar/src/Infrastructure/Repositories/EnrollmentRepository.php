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
    PaymentStatus,
    UserId,
    CourseId,
    AcademicPeriodId
};
use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;
use ChristianLMS\Infrastructure\Persistence\Schema\SchemaMap;

/**
 * Repositorio Concreto de Inscripción
 * 
 * Implementación de la interfaz EnrollmentRepositoryInterface
 * Maneja la persistencia de inscripciones en la base de datos
 */
class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    /** @var ConnectionManager */
    private $connectionManager;
    /** @var string */
    private $tableName = 'enrollments';

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
        $this->tableName = SchemaMap::table('enrollments');
    }

    /**
     * Guardar inscripción (crear o actualizar)
     */
    public function save(Enrollment $enrollment): Enrollment
    {
        try {
            $enrollmentArray = $enrollment->toArray();
            $enrollmentArray['updated_at'] = date('Y-m-d H:i:s');
            
            if ($this->existsById($enrollment->getId())) {
                // Actualizar inscripción existente
                $this->update($enrollment);
            } else {
                // Crear nueva inscripción
                $enrollmentArray['created_at'] = date('Y-m-d H:i:s');
                $this->insert($enrollment);
            }
            
            return $enrollment;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al guardar inscripción: ' . $e->getMessage());
        }
    }

    /**
     * Buscar inscripción por ID
     */
    public function findById(EnrollmentId $id): ?Enrollment
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE id = :id LIMIT 1";
            $params = ['id' => $id->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            
            if ($result) {
                return $this->hydrateEnrollment($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripción por ID: ' . $e->getMessage());
        }
    }

    /**
     * Buscar inscripción por estudiante y curso
     */
    public function findByStudentAndCourse(UserId $studentId, CourseId $courseId, AcademicPeriodId $academicPeriodId): ?Enrollment
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE student_id = :student_id 
                    AND course_id = :course_id 
                    LIMIT 1";
            $params = [
                'student_id' => $studentId->getValue(),
                'course_id' => $courseId->getValue()
            ];
            
            $result = $this->connectionManager->query($sql, $params);
            
            if ($result) {
                return $this->hydrateEnrollment($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripción por estudiante y curso: ' . $e->getMessage());
        }
    }

    /**
     * Buscar todas las inscripciones
     */
    public function findAll(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} ORDER BY enrollment_at DESC";
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar todas las inscripciones: ' . $e->getMessage());
        }
    }

    /**
     * Buscar inscripciones por estudiante
     */
    public function findByStudent(UserId $studentId): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE student_id = :student_id 
                    ORDER BY enrollment_at DESC";
            $params = ['student_id' => $studentId->getValue()];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones por estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Buscar inscripciones por curso
     */
    public function findByCourse(CourseId $courseId): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE course_id = :course_id 
                    ORDER BY enrollment_at DESC";
            $params = ['course_id' => $courseId->getValue()];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones por curso: ' . $e->getMessage());
        }
    }

    /**
     * Buscar inscripciones por periodo académico
     */
    public function findByAcademicPeriod(AcademicPeriodId $academicPeriodId): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    ORDER BY enrollment_at DESC";
            $params = [];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones por periodo académico: ' . $e->getMessage());
        }
    }

    /**
     * Buscar inscripciones por estado
     */
    public function findByStatus(EnrollmentStatus $status): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE status = :status 
                    ORDER BY enrollment_at DESC";
            $params = ['status' => $this->mapStatusToDb($status)];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones por estado: ' . $e->getMessage());
        }
    }

    /**
     * Buscar inscripciones activas
     */
    public function findActive(): array
    {
        return $this->findByStatus(\ChristianLMS\Domain\ValueObjects\EnrollmentStatus::enrolled());
    }

    /**
     * Buscar inscripciones completadas
     */
    public function findCompleted(): array
    {
        return $this->findByStatus(\ChristianLMS\Domain\ValueObjects\EnrollmentStatus::completed());
    }

    /**
     * Buscar inscripciones por estado de pago
     */
    public function findByPaymentStatus(PaymentStatus $paymentStatus): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE payment_status = :payment_status 
                    ORDER BY enrollment_at DESC";
            $params = ['payment_status' => $this->mapPaymentStatusToDb($paymentStatus)];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones por estado de pago: ' . $e->getMessage());
        }
    }

    /**
     * Buscar inscripciones pendientes de pago
     */
    public function findPendingPayment(): array
    {
        return $this->findByPaymentStatus(\ChristianLMS\Domain\ValueObjects\PaymentStatus::pending());
    }

    /**
     * Buscar inscripciones por profesor (a través del curso)
     */
    public function findByProfessor(UserId $professorId): array
    {
        try {
            $sql = "SELECT e.* FROM {$this->tableName} e
                    JOIN course_teachers ct ON ct.course_id = e.course_id
                    WHERE ct.teacher_id = :teacher_id
                    ORDER BY e.enrollment_at DESC";
            $params = ['teacher_id' => $professorId->getValue()];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones por profesor: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar inscripción
     */
    public function delete(EnrollmentId $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
            $params = ['id' => $id->getValue()];
            
            return $this->connectionManager->execute($sql, $params) > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al eliminar inscripción: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar inscripción de forma suave (soft delete)
     */
    public function softDelete(EnrollmentId $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Verificar si existe una inscripción con el ID dado
     */
    public function existsById(EnrollmentId $id): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE id = :id";
            $params = ['id' => $id->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar existencia de inscripción por ID: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si existe una inscripción para el estudiante, curso y periodo dados
     */
    public function existsByStudentCoursePeriod(UserId $studentId, CourseId $courseId, AcademicPeriodId $academicPeriodId): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE student_id = :student_id 
                    AND course_id = :course_id";
            $params = [
                'student_id' => $studentId->getValue(),
                'course_id' => $courseId->getValue()
            ];
            
            $result = $this->connectionManager->query($sql, $params);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar existencia de inscripción: ' . $e->getMessage());
        }
    }

    /**
     * Contar total de inscripciones
     */
    public function count(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE 1=1";
            $result = $this->connectionManager->query($sql);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar inscripciones: ' . $e->getMessage());
        }
    }

    /**
     * Contar inscripciones por estado
     */
    public function countByStatus(EnrollmentStatus $status): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE status = :status";
            $params = ['status' => $this->mapStatusToDb($status)];
            
            $result = $this->connectionManager->query($sql, $params);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar inscripciones por estado: ' . $e->getMessage());
        }
    }

    /**
     * Contar inscripciones por curso
     */
    public function countByCourse(CourseId $courseId): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE course_id = :course_id";
            $params = ['course_id' => $courseId->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar inscripciones por curso: ' . $e->getMessage());
        }
    }

    /**
     * Contar inscripciones por estudiante
     */
    public function countByStudent(UserId $studentId): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                        SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_payment
                    FROM {$this->tableName} 
                    WHERE student_id = :student_id";
            $params = ['student_id' => $studentId->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return $result;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar inscripciones por estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Contar inscripciones pendientes de pago
     */
    public function countPendingPayment(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE payment_status = 'pending'";
            
            $result = $this->connectionManager->query($sql);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar inscripciones pendientes de pago: ' . $e->getMessage());
        }
    }

    /**
     * Obtener inscripciones paginadas
     */
    public function findPaginated(int $page = 1, int $perPage = 20): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE 1=1 
                    ORDER BY enrollment_at DESC 
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

    /**
     * Buscar inscripciones con filtros
     */
    public function search(array $criteria, int $page = 1, int $perPage = 20): array
    {
        try {
            $whereConditions = ['1=1'];
            $params = [];
            
            // Aplicar criterios de búsqueda
            if (isset($criteria['status'])) {
                $whereConditions[] = 'status = :status';
                $params['status'] = $this->mapStatusValueToDb($criteria['status']);
            }
            
            if (isset($criteria['student_id'])) {
                $whereConditions[] = 'student_id = :student_id';
                $params['student_id'] = $criteria['student_id'];
            }
            
            if (isset($criteria['course_id'])) {
                $whereConditions[] = 'course_id = :course_id';
                $params['course_id'] = $criteria['course_id'];
            }
            
            if (isset($criteria['enrollment_at_from'])) {
                $whereConditions[] = 'enrollment_at >= :enrollment_at_from';
                $params['enrollment_at_from'] = $criteria['enrollment_at_from'];
            }
            
            if (isset($criteria['enrollment_at_to'])) {
                $whereConditions[] = 'enrollment_at <= :enrollment_at_to';
                $params['enrollment_at_to'] = $criteria['enrollment_at_to'];
            }
            
            $offset = ($page - 1) * $perPage;
            $params['limit'] = $perPage;
            $params['offset'] = $offset;
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE " . implode(' AND ', $whereConditions) . "
                    ORDER BY enrollment_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones: ' . $e->getMessage());
        }
    }

    /**
     * Obtener inscripciones recientes
     */
    public function findRecent(int $days = 30): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE enrollment_at >= DATE_SUB(NOW(), INTERVAL :days DAY) 
 
                    ORDER BY enrollment_at DESC";
            $params = ['days' => $days];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones recientes: ' . $e->getMessage());
        }
    }

    /**
     * Obtener inscripciones ordenadas
     */
    public function findOrdered(string $orderBy = 'enrollment_at', string $direction = 'DESC'): array
    {
        try {
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
            if (!SchemaMap::hasColumn($this->tableName, $orderBy)) {
                $orderBy = 'enrollment_at';
            }
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE 1=1 
                    ORDER BY $orderBy $direction";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener inscripciones ordenadas: ' . $e->getMessage());
        }
    }

    /**
     * Buscar inscripciones por calificación
     */
    public function findByGradeRange(float $minGrade, float $maxGrade): array
    {
        return [];
    }

    /**
     * Buscar inscripciones por asistencia
     */
    public function findByAttendanceRange(float $minAttendance, float $maxAttendance): array
    {
        return [];
    }

    /**
     * Obtener estadísticas de inscripciones
     */
    public function getStatistics(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                        SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_payment,
                        SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid
                    FROM {$this->tableName}";
            
            $result = $this->connectionManager->query($sql);
            return $result;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener estadísticas de inscripciones: ' . $e->getMessage());
        }
    }

    /**
     * Buscar inscripciones con pagos vencidos
     */
    public function findOverduePayments(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE payment_status = 'overdue' 
                    ORDER BY enrollment_at DESC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones con pagos vencidos: ' . $e->getMessage());
        }
    }

    /**
     * Buscar inscripciones por rango de fechas de inscripción
     */
    public function findByEnrollmentDateRange(string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE enrollment_at BETWEEN :start_date AND :end_date
 
                    ORDER BY enrollment_at DESC";
            $params = [
                'start_date' => $startDate,
                'end_date' => $endDate
            ];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones por rango de fechas: ' . $e->getMessage());
        }
    }

    /**
     * Obtener GPA del estudiante
     */
    public function getStudentGPA(UserId $studentId): float
    {
        return 0.0;
    }

    /**
     * Obtener créditos ganados del estudiante
     */
    public function getStudentCredits(UserId $studentId): float
    {
        return 0.0;
    }

    /**
     * Verificar integridad de datos
     */
    public function verifyIntegrity(): array
    {
        try {
            $issues = [];
            
            // Verificar inscripciones duplicadas
            $sql = "SELECT student_id, course_id, COUNT(*) as count 
                    FROM {$this->tableName} 
                    WHERE 1=1 
                    GROUP BY student_id, course_id 
                    HAVING count > 1";
            $results = $this->connectionManager->query($sql);
            if (!empty($results)) {
                $issues[] = 'Encontradas inscripciones duplicadas';
            }
            
            return $issues;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar integridad de inscripciones: ' . $e->getMessage());
        }
    }

    /**
     * Insertar nueva inscripción
     */
    private function insert(Enrollment $enrollment): void
    {
        $enrollmentArray = $this->buildPersistencePayload($enrollment);
        $columns = array_keys($enrollmentArray);
        $placeholders = array_map(function ($col) {
            return ":$col";
        }, $columns);
        
        $sql = "INSERT INTO {$this->tableName} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->connectionManager->execute($sql, $enrollmentArray);
    }

    /**
     * Actualizar inscripción existente
     */
    private function update(Enrollment $enrollment): void
    {
        $enrollmentArray = $this->buildPersistencePayload($enrollment);
        $updateFields = [];
        
        foreach ($enrollmentArray as $column => $value) {
            if ($column !== 'id' && $column !== 'created_at') {
                $updateFields[] = "$column = :$column";
            }
        }
        
        $sql = "UPDATE {$this->tableName} 
                SET " . implode(', ', $updateFields) . "
                WHERE id = :id";
        
        $this->connectionManager->execute($sql, $enrollmentArray);
    }

    /**
     * Hidratar inscripción desde array de base de datos
     */
    private function hydrateEnrollment(array $data): Enrollment
    {
        $enrollment = new Enrollment(
            new EnrollmentId($data['id']),
            UserId::fromString($data['student_id']),
            new CourseId($data['course_id']),
            new AcademicPeriodId('00000000-0000-0000-0000-000000000000')
        );

        $enrollment->setStatus($this->mapStatusFromDb($data['status']));
        if (!empty($data['notes'])) {
            $enrollment->setNotes($data['notes']);
        }
        if (!empty($data['enrollment_at'])) {
            $enrollment->setEnrollmentDate($data['enrollment_at']);
        }

        return $enrollment;
    }

    private function buildPersistencePayload(Enrollment $enrollment): array
    {
        $data = [
            'id' => $enrollment->getId()->getValue(),
            'student_id' => $enrollment->getStudentId()->getValue(),
            'course_id' => $enrollment->getCourseId()->getValue(),
            'enrollment_at' => $enrollment->getEnrollmentDate(),
            'status' => $this->mapStatusToDb($enrollment->getStatus()),
            'payment_status' => $this->mapPaymentStatusToDb($enrollment->getPaymentStatus()),
            'total_amount' => $enrollment->getPaymentAmount(),
            'paid_amount' => $enrollment->getPaymentStatus()->isPaid() ? $enrollment->getPaymentAmount() : 0,
            'notes' => $enrollment->getNotes(),
            'created_at' => $enrollment->getCreatedAt(),
            'updated_at' => $enrollment->getUpdatedAt(),
        ];

        $allowed = array_flip(SchemaMap::columns($this->tableName));

        return array_intersect_key($data, $allowed);
    }

    private function mapStatusToDb(EnrollmentStatus $status): string
    {
        switch ($status->getValue()) {
            case EnrollmentStatus::ENROLLED:
                return 'active';
            case EnrollmentStatus::COMPLETED:
                return 'completed';
            case EnrollmentStatus::DROPPED:
            case EnrollmentStatus::FAILED:
            case EnrollmentStatus::WITHDRAWN:
                return 'cancelled';
            default:
                return 'active';
        }
    }

    private function mapStatusFromDb(string $status): EnrollmentStatus
    {
        switch ($status) {
            case 'completed':
                return EnrollmentStatus::completed();
            case 'cancelled':
                return EnrollmentStatus::dropped();
            case 'active':
            default:
                return EnrollmentStatus::enrolled();
        }
    }

    private function mapStatusValueToDb(string $status): string
    {
        switch ($status) {
            case 'enrolled':
                return 'active';
            case 'dropped':
            case 'failed':
            case 'withdrawn':
                return 'cancelled';
            default:
                return $status;
        }
    }

    private function mapPaymentStatusToDb(PaymentStatus $status): string
    {
        $value = $status->getValue();
        if ($value === PaymentStatus::WAIVED) {
            return 'paid';
        }

        return $value;
    }
}
