<?php
/**
 * =============================================================================
 * INTERFACE SUBJECT REPOSITORY - DOMAIN LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\Ports;

use ChristianLMS\Domain\Entities\Subject;
use ChristianLMS\Domain\ValueObjects\{
    SubjectId,
    SubjectCode,
    SubjectStatus,
    GradeLevel
};

/**
 * Interface SubjectRepository
 * 
 * Puerto del dominio para persistencia de materias.
 * Define las operaciones de lectura y escritura sin exponer
 * detalles de implementación de infraestructura.
 */
interface SubjectRepositoryInterface
{
    /**
     * Guardar materia (crear o actualizar)
     */
    public function save(Subject $subject): Subject;

    /**
     * Buscar materia por ID
     */
    public function findById(SubjectId $id): ?Subject;

    /**
     * Buscar materia por código
     */
    public function findByCode(SubjectCode $code): ?Subject;

    /**
     * Buscar todas las materias
     */
    public function findAll(): array;

    /**
     * Buscar materias por departamento
     */
    public function findByDepartment(string $department): array;

    /**
     * Buscar materias por nivel educativo
     */
    public function findByGradeLevel(GradeLevel $gradeLevel): array;

    /**
     * Buscar materias por estado
     */
    public function findByStatus(SubjectStatus $status): array;

    /**
     * Buscar materias activas
     */
    public function findActive(): array;

    /**
     * Buscar materias básicas
     */
    public function findCore(): array;

    /**
     * Buscar materias electivas
     */
    public function findElective(): array;

    /**
     * Eliminar materia
     */
    public function delete(SubjectId $id): bool;

    /**
     * Eliminar materia de forma suave (soft delete)
     */
    public function softDelete(SubjectId $id): bool;

    /**
     * Verificar si existe una materia con el ID dado
     */
    public function existsById(SubjectId $id): bool;

    /**
     * Verificar si existe una materia con el código dado
     */
    public function existsByCode(SubjectCode $code): bool;

    /**
     * Contar total de materias
     */
    public function count(): int;

    /**
     * Contar materias por estado
     */
    public function countByStatus(SubjectStatus $status): int;

    /**
     * Contar materias por departamento
     */
    public function countByDepartment(string $department): int;

    /**
     * Contar materias por nivel educativo
     */
    public function countByGradeLevel(GradeLevel $gradeLevel): int;

    /**
     * Obtener materias paginadas
     */
    public function findPaginated(int $page = 1, int $perPage = 20): array;

    /**
     * Buscar materias con filtros
     */
    public function search(array $criteria, int $page = 1, int $perPage = 20): array;

    /**
     * Obtener materias recientes
     */
    public function findRecent(int $days = 30): array;

    /**
     * Buscar materias por nombre (búsqueda parcial)
     */
    public function findByName(string $name): array;

    /**
     * Obtener materias ordenadas
     */
    public function findOrdered(string $orderBy = 'created_at', string $direction = 'DESC'): array;

    /**
     * Obtener todos los departamentos únicos
     */
    public function findDepartments(): array;

    /**
     * Obtener todos los niveles educativos únicos
     */
    public function findGradeLevels(): array;

    /**
     * Obtener estadísticas de materias
     */
    public function getStatistics(): array;

    /**
     * Buscar materias con prerrequisitos
     */
    public function findWithPrerequisites(): array;

    /**
     * Buscar materias disponibles para cursos
     */
    public function findAvailableForCourses(): array;

    /**
     * Obtener materias por rango de fechas
     */
    public function findByDateRange(string $startDate, string $endDate): array;

    /**
     * Verificar integridad de datos
     */
    public function verifyIntegrity(): array;
}

/**
 * Interface SubjectRepositoryCriteria
 * 
 * Criterios de búsqueda para el repositorio de materias
 */
interface SubjectRepositoryCriteria
{
    // Criterios de estado
    public function withStatus(SubjectStatus $status): self;
    public function withStatuses(array $statuses): self;
    public function onlyActive(): self;
    public function onlyInactive(): self;
    public function onlyDeprecated(): self;

    // Criterios de departamento
    public function withDepartment(string $department): self;
    public function withDepartments(array $departments): self;
    public function withAnyDepartment(array $departments): self;

    // Criterios de nivel educativo
    public function withGradeLevel(GradeLevel $gradeLevel): self;
    public function withGradeLevels(array $gradeLevels): self;
    public function withBasicLevel(): self;
    public function withMiddleLevel(): self;
    public function withHigherLevel(): self;

    // Criterios de tipo
    public function onlyCore(): self;
    public function onlyElective(): self;

    // Criterios de nombre/código
    public function withName(string $name): self;
    public function withCode(string $code): self;
    public function withPartialName(string $name): self;

    // Criterios de créditos
    public function withMinCredits(float $min): self;
    public function withMaxCredits(float $max): self;
    public function withCreditsRange(float $min, float $max): self;

    // Criterios de horas
    public function withMinHoursPerWeek(float $min): self;
    public function withMaxHoursPerWeek(float $max): self;
    public function withHoursRange(float $min, float $max): self;

    // Criterios de prerrequisitos
    public function withPrerequisites(): self;
    public function withoutPrerequisites(): self;

    // Criterios de recursos
    public function withResources(): self;
    public function withoutResources(): self;

    // Criterios de paginación
    public function withPagination(int $page, int $perPage): self;
    public function withLimit(int $limit): self;

    // Criterios de ordenamiento
    public function orderBy(string $field, string $direction = 'ASC'): self;
    public function orderByCreatedAt(string $direction = 'DESC'): self;
    public function orderByName(string $direction = 'ASC'): self;
    public function orderByCode(string $direction = 'ASC'): self;
    public function orderByDepartment(string $direction = 'ASC'): self;

    // Criterios de exclusión
    public function excludeIds(array $ids): self;
    public function excludeStatuses(array $statuses): self;
    public function excludeDepartments(array $departments): self;
    public function excludeGradeLevels(array $gradeLevels): self;

    // Construir y ejecutar criterios
    public function build(): array;
    public function execute(): array;
}
