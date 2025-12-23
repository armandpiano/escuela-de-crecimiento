<?php
/**
 * =============================================================================
 * INTERFACE ENROLLMENT REPOSITORY - DOMAIN LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\Ports;

use ChristianLMS\Domain\Entities\Enrollment;
use ChristianLMS\Domain\ValueObjects\{
    EnrollmentId,
    EnrollmentStatus,
    PaymentStatus,
    UserId,
    CourseId,
    AcademicPeriodId
};

/**
 * Interface EnrollmentRepository
 * 
 * Puerto del dominio para persistencia de inscripciones.
 * Define las operaciones de lectura y escritura sin exponer
 * detalles de implementación de infraestructura.
 */
interface EnrollmentRepositoryInterface
{
    /**
     * Guardar inscripción (crear o actualizar)
     */
    public function save(Enrollment $enrollment): Enrollment;

    /**
     * Buscar inscripción por ID
     */
    public function findById(EnrollmentId $id): ?Enrollment;

    /**
     * Buscar inscripción por estudiante y curso
     */
    public function findByStudentAndCourse(UserId $studentId, CourseId $courseId, AcademicPeriodId $academicPeriodId): ?Enrollment;

    /**
     * Buscar todas las inscripciones
     */
    public function findAll(): array;

    /**
     * Buscar inscripciones por estudiante
     */
    public function findByStudent(UserId $studentId): array;

    /**
     * Buscar inscripciones por curso
     */
    public function findByCourse(CourseId $courseId): array;

    /**
     * Buscar inscripciones por periodo académico
     */
    public function findByAcademicPeriod(AcademicPeriodId $academicPeriodId): array;

    /**
     * Buscar inscripciones por estado
     */
    public function findByStatus(EnrollmentStatus $status): array;

    /**
     * Buscar inscripciones activas
     */
    public function findActive(): array;

    /**
     * Buscar inscripciones completadas
     */
    public function findCompleted(): array;

    /**
     * Buscar inscripciones por estado de pago
     */
    public function findByPaymentStatus(PaymentStatus $paymentStatus): array;

    /**
     * Buscar inscripciones pendientes de pago
     */
    public function findPendingPayment(): array;

    /**
     * Buscar inscripciones por profesor (a través del curso)
     */
    public function findByProfessor(UserId $professorId): array;

    /**
     * Eliminar inscripción
     */
    public function delete(EnrollmentId $id): bool;

    /**
     * Eliminar inscripción de forma suave (soft delete)
     */
    public function softDelete(EnrollmentId $id): bool;

    /**
     * Verificar si existe una inscripción con el ID dado
     */
    public function existsById(EnrollmentId $id): bool;

    /**
     * Verificar si existe una inscripción para el estudiante, curso y periodo dados
     */
    public function existsByStudentCoursePeriod(UserId $studentId, CourseId $courseId, AcademicPeriodId $academicPeriodId): bool;

    /**
     * Contar total de inscripciones
     */
    public function count(): int;

    /**
     * Contar inscripciones por estado
     */
    public function countByStatus(EnrollmentStatus $status): int;

    /**
     * Contar inscripciones por curso
     */
    public function countByCourse(CourseId $courseId): int;

    /**
     * Contar inscripciones por estudiante
     */
    public function countByStudent(UserId $studentId): array;

    /**
     * Contar inscripciones pendientes de pago
     */
    public function countPendingPayment(): int;

    /**
     * Obtener inscripciones paginadas
     */
    public function findPaginated(int $page = 1, int $perPage = 20): array;

    /**
     * Buscar inscripciones con filtros
     */
    public function search(array $criteria, int $page = 1, int $perPage = 20): array;

    /**
     * Obtener inscripciones recientes
     */
    public function findRecent(int $days = 30): array;

    /**
     * Obtener inscripciones ordenadas
     */
    public function findOrdered(string $orderBy = 'created_at', string $direction = 'DESC'): array;

    /**
     * Buscar inscripciones por calificación
     */
    public function findByGradeRange(float $minGrade, float $maxGrade): array;

    /**
     * Buscar inscripciones por asistencia
     */
    public function findByAttendanceRange(float $minAttendance, float $maxAttendance): array;

    /**
     * Obtener estadísticas de inscripciones
     */
    public function getStatistics(): array;

    /**
     * Buscar inscripciones con pagos vencidos
     */
    public function findOverduePayments(): array;

    /**
     * Buscar inscripciones por rango de fechas de inscripción
     */
    public function findByEnrollmentDateRange(string $startDate, string $endDate): array;

    /**
     * Obtener GPA del estudiante
     */
    public function getStudentGPA(UserId $studentId): float;

    /**
     * Obtener créditos ganados del estudiante
     */
    public function getStudentCredits(UserId $studentId): float;

    /**
     * Verificar integridad de datos
     */
    public function verifyIntegrity(): array;
}

/**
 * Interface EnrollmentRepositoryCriteria
 * 
 * Criterios de búsqueda para el repositorio de inscripciones
 */
interface EnrollmentRepositoryCriteria
{
    // Criterios de estudiante
    public function withStudent(UserId $studentId): self;
    public function withStudents(array $studentIds): self;

    // Criterios de curso
    public function withCourse(CourseId $courseId): self;
    public function withCourses(array $courseIds): self;

    // Criterios de periodo académico
    public function withAcademicPeriod(AcademicPeriodId $academicPeriodId): self;
    public function withAcademicPeriods(array $academicPeriodIds): self;

    // Criterios de estado
    public function withStatus(EnrollmentStatus $status): self;
    public function withStatuses(array $statuses): self;
    public function onlyActive(): self;
    public function onlyCompleted(): self;
    public function onlyDropped(): self;

    // Criterios de pago
    public function withPaymentStatus(PaymentStatus $paymentStatus): self;
    public function withPaymentStatuses(array $paymentStatuses): self;
    public function onlyPendingPayment(): self;
    public function onlyOverduePayment(): self;
    public function onlyPaid(): self;

    // Criterios de calificación
    public function withFinalGrade(): self;
    public function withoutFinalGrade(): self;
    public function withGradeRange(float $min, float $max): self;
    public function passingGrades(): self;
    public function failingGrades(): self;

    // Criterios de asistencia
    public function withAttendanceRange(float $min, float $max): self;
    public function withGoodAttendance(): self;
    public function withPoorAttendance(): self;

    // Criterios de fecha
    public function enrollmentAfter(string $date): self;
    public function enrollmentBefore(string $date): self;
    public function enrollmentBetween(string $startDate, string $endDate): self;
    public function completionAfter(string $date): self;
    public function completionBefore(string $date): self;

    // Criterios de créditos
    public function withCreditsRange(float $min, float $max): self;
    public function withCreditsEarned(): self;
    public function withoutCredits(): self;

    // Criterios de notas
    public function withNotes(): self;
    public function withoutNotes(): self;

    // Criterios de paginación
    public function withPagination(int $page, int $perPage): self;
    public function withLimit(int $limit): self;

    // Criterios de ordenamiento
    public function orderBy(string $field, string $direction = 'ASC'): self;
    public function orderByEnrollmentDate(string $direction = 'DESC'): self;
    public function orderByFinalGrade(string $direction = 'DESC'): self;
    public function orderByStudent(string $direction = 'ASC'): self;
    public function orderByCourse(string $direction = 'ASC'): self;

    // Criterios de exclusión
    public function excludeIds(array $ids): self;
    public function excludeStatuses(array $statuses): self;
    public function excludeStudentIds(array $studentIds): self;
    public function excludeCourseIds(array $courseIds): self;

    // Construir y ejecutar criterios
    public function build(): array;
    public function execute(): array;
}
