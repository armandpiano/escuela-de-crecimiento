<?php
/**
 * =============================================================================
 * INTERFACE COURSE REPOSITORY - DOMAIN LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\Ports;

use ChristianLMS\Domain\Entities\Course;
use ChristianLMS\Domain\ValueObjects\{
    CourseId,
    CourseCode,
    CourseStatus,
    UserId,
    SubjectId
};

/**
 * Interface CourseRepository
 * 
 * Puerto del dominio para persistencia de cursos.
 * Define las operaciones de lectura y escritura sin exponer
 * detalles de implementación de infraestructura.
 */
interface CourseRepositoryInterface
{
    /**
     * Guardar curso (crear o actualizar)
     */
    public function save(Course $course): Course;

    /**
     * Buscar curso por ID
     */
    public function findById(CourseId $id): ?Course;

    /**
     * Buscar curso por código
     */
    public function findByCode(CourseCode $code): ?Course;

    /**
     * Buscar todos los cursos
     */
    public function findAll(): array;

    /**
     * Buscar cursos por profesor
     */
    public function findByProfessor(UserId $professorId): array;

    /**
     * Buscar cursos por materia
     */
    public function findBySubject(SubjectId $subjectId): array;

    /**
     * Buscar cursos por estado
     */
    public function findByStatus(CourseStatus $status): array;

    /**
     * Buscar cursos activos
     */
    public function findActive(): array;

    /**
     * Buscar cursos con cupo disponible
     */
    public function findWithAvailableSpots(): array;

    /**
     * Buscar cursos virtuales
     */
    public function findVirtual(): array;

    /**
     * Buscar cursos presenciales
     */
    public function findInPerson(): array;

    /**
     * Eliminar curso
     */
    public function delete(CourseId $id): bool;

    /**
     * Eliminar curso de forma suave (soft delete)
     */
    public function softDelete(CourseId $id): bool;

    /**
     * Verificar si existe un curso con el ID dado
     */
    public function existsById(CourseId $id): bool;

    /**
     * Verificar si existe un curso con el código dado
     */
    public function existsByCode(CourseCode $code): bool;

    /**
     * Contar total de cursos
     */
    public function count(): int;

    /**
     * Contar cursos por estado
     */
    public function countByStatus(CourseStatus $status): int;

    /**
     * Contar cursos por profesor
     */
    public function countByProfessor(UserId $professorId): int;

    /**
     * Obtener cursos paginados
     */
    public function findPaginated(int $page = 1, int $perPage = 20): array;

    /**
     * Buscar cursos con filtros
     */
    public function search(array $criteria, int $page = 1, int $perPage = 20): array;

    /**
     * Obtener cursos recientes
     */
    public function findRecent(int $days = 30): array;

    /**
     * Buscar cursos por nombre (búsqueda parcial)
     */
    public function findByName(string $name): array;

    /**
     * Obtener cursos ordenados
     */
    public function findOrdered(string $orderBy = 'created_at', string $direction = 'DESC'): array;

    /**
     * Buscar cursos por periodo académico
     */
    public function findByAcademicPeriod(string $academicPeriodId): array;

    /**
     * Obtener estadísticas de cursos
     */
    public function getStatistics(): array;

    /**
     * Buscar cursos que requieren inscripción
     */
    public function findAvailableForEnrollment(): array;

    /**
     * Obtener cursos por rango de fechas
     */
    public function findByDateRange(string $startDate, string $endDate): array;

    /**
     * Verificar integridad de datos
     */
    public function verifyIntegrity(): array;
}

/**
 * Interface CourseRepositoryCriteria
 * 
 * Criterios de búsqueda para el repositorio de cursos
 */
interface CourseRepositoryCriteria
{
    // Criterios de estado
    public function withStatus(CourseStatus $status): self;
    public function withStatuses(array $statuses): self;
    public function onlyActive(): self;
    public function onlyDraft(): self;

    // Criterios de profesor
    public function withProfessor(UserId $professorId): self;
    public function withAnyProfessor(array $professorIds): self;

    // Criterios de materia
    public function withSubject(SubjectId $subjectId): self;
    public function withAnySubject(array $subjectIds): self;

    // Criterios de fecha
    public function startAfter(string $date): self;
    public function startBefore(string $date): self;
    public function startBetween(string $startDate, string $endDate): self;
    public function endAfter(string $date): self;
    public function endBefore(string $date): self;

    // Criterios de nombre/código
    public function withName(string $name): self;
    public function withCode(string $code): self;
    public function withPartialName(string $name): self;

    // Criterios de capacidad
    public function withAvailableSpots(): self;
    public function withFullCapacity(): self;
    public function withMinStudents(int $min): self;
    public function withMaxStudents(int $max): self;

    // Criterios de modalidad
    public function onlyVirtual(): self;
    public function onlyInPerson(): self;

    // Criterios de paginación
    public function withPagination(int $page, int $perPage): self;
    public function withLimit(int $limit): self;

    // Criterios de ordenamiento
    public function orderBy(string $field, string $direction = 'ASC'): self;
    public function orderByCreatedAt(string $direction = 'DESC'): self;
    public function orderByName(string $direction = 'ASC'): self;
    public function orderByStartDate(string $direction = 'ASC'): self;

    // Criterios de exclusión
    public function excludeIds(array $ids): self;
    public function excludeStatuses(array $statuses): self;
    public function excludeProfessorIds(array $professorIds): self;

    // Construir y ejecutar criterios
    public function build(): array;
    public function execute(): array;
}
