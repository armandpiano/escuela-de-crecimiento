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
     * Buscar inscripción por usuario y curso
     */
    public function findByUserAndCourse(UserId $userId, CourseId $courseId): ?Enrollment;

    /**
     * Buscar todas las inscripciones
     */
    public function findAll(): array;

    /**
     * Buscar inscripciones por estudiante
     */
    public function findByUser(UserId $userId): array;

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
    public function existsByUserCourse(UserId $userId, CourseId $courseId): bool;

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
    public function countByUser(UserId $userId): int;

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
     * Obtener estadísticas de inscripciones
     */
    public function getStatistics(): array;

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
    public function withUser(UserId $userId): self;
    public function withUsers(array $userIds): self;
    public function withCourse(CourseId $courseId): self;
    public function withCourses(array $courseIds): self;
    public function withStatus(EnrollmentStatus $status): self;
    public function withStatuses(array $statuses): self;
    public function withAcademicPeriod(AcademicPeriodId $academicPeriodId): self;
    public function withAcademicPeriods(array $academicPeriodIds): self;
    public function withPagination(int $page, int $perPage): self;
    public function orderBy(string $field, string $direction = 'ASC'): self;
    public function build(): array;
}
