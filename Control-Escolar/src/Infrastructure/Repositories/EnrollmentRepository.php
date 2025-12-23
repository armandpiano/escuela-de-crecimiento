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

/**
 * Repositorio Concreto de Inscripción
 * 
 * Implementación de la interfaz EnrollmentRepositoryInterface
 * Maneja la persistencia de inscripciones en la base de datos
 */
class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    private ConnectionManager $connectionManager;
    private string $tableName = 'enrollments';

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
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
            $sql = "SELECT * FROM {$this->tableName} WHERE id = :id AND deleted_at IS NULL LIMIT 1";
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
                    AND academic_period_id = :academic_period_id 
                    AND deleted_at IS NULL 
                    LIMIT 1";
            $params = [
                'student_id' => $studentId->getValue(),
                'course_id' => $courseId->getValue(),
                'academic_period_id' => $academicPeriodId->getValue()
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
            $sql = "SELECT * FROM {$this->tableName} WHERE deleted_at IS NULL ORDER BY enrollment_date DESC";
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
                    WHERE student_id = :student_id AND deleted_at IS NULL 
                    ORDER BY enrollment_date DESC";
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
                    WHERE course_id = :course_id AND deleted_at IS NULL 
                    ORDER BY enrollment_date DESC";
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
                    WHERE academic_period_id = :academic_period_id AND deleted_at IS NULL 
                    ORDER BY enrollment_date DESC";
            $params = ['academic_period_id' => $academicPeriodId->getValue()];
            
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
                    WHERE status = :status AND deleted_at IS NULL 
                    ORDER BY enrollment_date DESC";
            $params = ['status' => $status->getValue()];
            
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
                    WHERE payment_status = :payment_status AND deleted_at IS NULL 
                    ORDER BY enrollment_date DESC";
            $params = ['payment_status' => $paymentStatus->getValue()];
            
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
                    JOIN courses c ON e.course_id = c.id
                    WHERE c.professor_id = :professor_id 
                    AND e.deleted_at IS NULL 
                    AND c.deleted_at IS NULL
                    ORDER BY e.enrollment_date DESC";
            $params = ['professor_id' => $professorId->getValue()];
            
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
            throw new DatabaseException('Error al eliminar inscripción suavemente: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si existe una inscripción con el ID dado
     */
    public function existsById(EnrollmentId $id): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE id = :id AND deleted_at IS NULL";
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
                    AND course_id = :course_id 
                    AND academic_period_id = :academic_period_id 
                    AND deleted_at IS NULL";
            $params = [
                'student_id' => $studentId->getValue(),
                'course_id' => $courseId->getValue(),
                'academic_period_id' => $academicPeriodId->getValue()
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE deleted_at IS NULL";
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
                    WHERE status = :status AND deleted_at IS NULL";
            $params = ['status' => $status->getValue()];
            
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
                    WHERE course_id = :course_id AND deleted_at IS NULL";
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
                        SUM(CASE WHEN status = 'enrolled' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                        SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_payment
                    FROM {$this->tableName} 
                    WHERE student_id = :student_id AND deleted_at IS NULL";
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
                    WHERE payment_status = 'pending' AND deleted_at IS NULL";
            
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
                    WHERE deleted_at IS NULL 
                    ORDER BY enrollment_date DESC 
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
            $whereConditions = ['deleted_at IS NULL'];
            $params = [];
            
            // Aplicar criterios de búsqueda
            if (isset($criteria['status'])) {
                $whereConditions[] = 'status = :status';
                $params['status'] = $criteria['status'];
            }
            
            if (isset($criteria['student_id'])) {
                $whereConditions[] = 'student_id = :student_id';
                $params['student_id'] = $criteria['student_id'];
            }
            
            if (isset($criteria['course_id'])) {
                $whereConditions[] = 'course_id = :course_id';
                $params['course_id'] = $criteria['course_id'];
            }
            
            if (isset($criteria['academic_period_id'])) {
                $whereConditions[] = 'academic_period_id = :academic_period_id';
                $params['academic_period_id'] = $criteria['academic_period_id'];
            }
            
            if (isset($criteria['payment_status'])) {
                $whereConditions[] = 'payment_status = :payment_status';
                $params['payment_status'] = $criteria['payment_status'];
            }
            
            if (isset($criteria['enrollment_date_from'])) {
                $whereConditions[] = 'enrollment_date >= :enrollment_date_from';
                $params['enrollment_date_from'] = $criteria['enrollment_date_from'];
            }
            
            if (isset($criteria['enrollment_date_to'])) {
                $whereConditions[] = 'enrollment_date <= :enrollment_date_to';
                $params['enrollment_date_to'] = $criteria['enrollment_date_to'];
            }
            
            $offset = ($page - 1) * $perPage;
            $params['limit'] = $perPage;
            $params['offset'] = $offset;
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE " . implode(' AND ', $whereConditions) . "
                    ORDER BY enrollment_date DESC 
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
                    WHERE enrollment_date >= DATE_SUB(NOW(), INTERVAL :days DAY) 
                    AND deleted_at IS NULL 
                    ORDER BY enrollment_date DESC";
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
    public function findOrdered(string $orderBy = 'enrollment_date', string $direction = 'DESC'): array
    {
        try {
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE deleted_at IS NULL 
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
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE final_grade BETWEEN :min_grade AND :max_grade 
                    AND deleted_at IS NULL 
                    ORDER BY final_grade DESC";
            $params = [
                'min_grade' => $minGrade,
                'max_grade' => $maxGrade
            ];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones por calificación: ' . $e->getMessage());
        }
    }

    /**
     * Buscar inscripciones por asistencia
     */
    public function findByAttendanceRange(float $minAttendance, float $maxAttendance): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE attendance_percentage BETWEEN :min_attendance AND :max_attendance 
                    AND deleted_at IS NULL 
                    ORDER BY attendance_percentage DESC";
            $params = [
                'min_attendance' => $minAttendance,
                'max_attendance' => $maxAttendance
            ];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateEnrollment'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar inscripciones por asistencia: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas de inscripciones
     */
    public function getStatistics(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'enrolled' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'dropped' THEN 1 ELSE 0 END) as dropped,
                        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                        SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_payment,
                        SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid,
                        AVG(final_grade) as avg_grade,
                        AVG(attendance_percentage) as avg_attendance,
                        SUM(credits_earned) as total_credits_earned
                    FROM {$this->tableName} 
                    WHERE deleted_at IS NULL";
            
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
                    WHERE payment_status = 'overdue' AND deleted_at IS NULL 
                    ORDER BY enrollment_date DESC";
            
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
                    WHERE enrollment_date BETWEEN :start_date AND :end_date
                    AND deleted_at IS NULL 
                    ORDER BY enrollment_date DESC";
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
        try {
            $sql = "SELECT 
                        SUM(CASE 
                            WHEN letter_grade = 'A' THEN 4.0 * credits_earned
                            WHEN letter_grade = 'B' THEN 3.0 * credits_earned
                            WHEN letter_grade = 'C' THEN 2.0 * credits_earned
                            WHEN letter_grade = 'D' THEN 1.0 * credits_earned
                            ELSE 0.0
                        END) as total_points,
                        SUM(credits_earned) as total_credits
                    FROM {$this->tableName} 
                    WHERE student_id = :student_id 
                    AND status = 'completed' 
                    AND deleted_at IS NULL";
            $params = ['student_id' => $studentId->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            
            if ($result['total_credits'] > 0) {
                return round($result['total_points'] / $result['total_credits'], 2);
            }
            
            return 0.0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener GPA del estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Obtener créditos ganados del estudiante
     */
    public function getStudentCredits(UserId $studentId): float
    {
        try {
            $sql = "SELECT SUM(credits_earned) as total_credits 
                    FROM {$this->tableName} 
                    WHERE student_id = :student_id 
                    AND status = 'completed' 
                    AND deleted_at IS NULL";
            $params = ['student_id' => $studentId->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            
            return (float) ($result['total_credits'] ?? 0.0);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener créditos del estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Verificar integridad de datos
     */
    public function verifyIntegrity(): array
    {
        try {
            $issues = [];
            
            // Verificar inscripciones duplicadas
            $sql = "SELECT student_id, course_id, academic_period_id, COUNT(*) as count 
                    FROM {$this->tableName} 
                    WHERE deleted_at IS NULL 
                    GROUP BY student_id, course_id, academic_period_id 
                    HAVING count > 1";
            $results = $this->connectionManager->query($sql);
            if (!empty($results)) {
                $issues[] = 'Encontradas inscripciones duplicadas';
            }
            
            // Verificar calificaciones fuera de rango
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE (final_grade < 0 OR final_grade > 100) AND deleted_at IS NULL";
            $result = $this->connectionManager->query($sql);
            if ($result['count'] > 0) {
                $issues[] = "Encontradas {$result['count']} calificaciones fuera de rango";
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
        $enrollmentArray = $enrollment->toArray();
        $columns = array_keys($enrollmentArray);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        
        $sql = "INSERT INTO {$this->tableName} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->connectionManager->execute($sql, $enrollmentArray);
    }

    /**
     * Actualizar inscripción existente
     */
    private function update(Enrollment $enrollment): void
    {
        $enrollmentArray = $enrollment->toArray();
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
            new UserId($data['student_id']),
            new CourseId($data['course_id']),
            new AcademicPeriodId($data['academic_period_id'])
        );

        $enrollment->setStatus(new \ChristianLMS\Domain\ValueObjects\EnrollmentStatus($data['status']));
        $enrollment->setFinalGrade($data['final_grade']);
        $enrollment->setLetterGrade($data['letter_grade']);
        $enrollment->setCreditsEarned($data['credits_earned']);
        $enrollment->setAttendancePercentage($data['attendance_percentage']);
        $enrollment->setPaymentStatus(new \ChristianLMS\Domain\ValueObjects\PaymentStatus($data['payment_status']));
        $enrollment->setPaymentAmount($data['payment_amount']);
        $enrollment->setPaymentDate($data['payment_date']);
        $enrollment->setDropDate($data['drop_date']);
        $enrollment->setCompletionDate($data['completion_date']);
        $enrollment->setNotes($data['notes']);
        $enrollment->setMetadata(json_decode($data['metadata'], true) ?? []);

        return $enrollment;
    }
}
