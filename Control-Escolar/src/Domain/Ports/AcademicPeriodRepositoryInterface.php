<?php
/**
 * =============================================================================
 * INTERFACE ACADEMIC PERIOD REPOSITORY - DOMAIN LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\Ports;

use ChristianLMS\Domain\Entities\AcademicPeriod;
use ChristianLMS\Domain\ValueObjects\{
    AcademicPeriodId,
    AcademicPeriodType
};

/**
 * Interface AcademicPeriodRepository
 * 
 * Puerto del dominio para persistencia de periodos académicos.
 * Define las operaciones de lectura y escritura sin exponer
 * detalles de implementación de infraestructura.
 */
interface AcademicPeriodRepositoryInterface
{
    /**
     * Guardar periodo académico (crear o actualizar)
     */
    public function save(AcademicPeriod $period): AcademicPeriod;

    /**
     * Buscar periodo académico por ID
     */
    public function findById(AcademicPeriodId $id): ?AcademicPeriod;

    /**
     * Buscar periodo académico por código
     */
    public function findByCode(string $code): ?AcademicPeriod;

    /**
     * Buscar todos los periodos académicos
     */
    public function findAll(): array;

    /**
     * Buscar periodos activos
     */
    public function findActive(): array;

    /**
     * Buscar periodo actual
     */
    public function findCurrent(): ?AcademicPeriod;

    /**
     * Buscar periodos por tipo
     */
    public function findByType(AcademicPeriodType $type): array;

    /**
     * Buscar periodos por año académico
     */
    public function findByAcademicYear(int $year): array;

    /**
     * Buscar periodos futuros
     */
    public function findUpcoming(): array;

    /**
     * Buscar periodos en curso
     */
    public function findInProgress(): array;

    /**
     * Buscar periodos finalizados
     */
    public function findEnded(): array;

    /**
     * Buscar periodo por número
     */
    public function findByPeriodNumber(int $periodNumber): array;

    /**
     * Eliminar periodo académico
     */
    public function delete(AcademicPeriodId $id): bool;

    /**
     * Eliminar periodo académico de forma suave (soft delete)
     */
    public function softDelete(AcademicPeriodId $id): bool;

    /**
     * Verificar si existe un periodo académico con el ID dado
     */
    public function existsById(AcademicPeriodId $id): bool;

    /**
     * Verificar si existe un periodo académico con el código dado
     */
    public function existsByCode(string $code): bool;

    /**
     * Contar total de periodos académicos
     */
    public function count(): int;

    /**
     * Contar periodos por estado
     */
    public function countActive(): int;

    /**
     * Contar periodos por tipo
     */
    public function countByType(AcademicPeriodType $type): int;

    /**
     * Contar periodos por año académico
     */
    public function countByAcademicYear(int $year): int;

    /**
     * Obtener periodos académicos paginados
     */
    public function findPaginated(int $page = 1, int $perPage = 20): array;

    /**
     * Buscar periodos académicos con filtros
     */
    public function search(array $criteria, int $page = 1, int $perPage = 20): array;

    /**
     * Obtener periodos académicos recientes
     */
    public function findRecent(int $days = 30): array;

    /**
     * Buscar periodos académicos por nombre (búsqueda parcial)
     */
    public function findByName(string $name): array;

    /**
     * Obtener periodos académicos ordenados
     */
    public function findOrdered(string $orderBy = 'created_at', string $direction = 'DESC'): array;

    /**
     * Obtener años académicos únicos
     */
    public function findAcademicYears(): array;

    /**
     * Obtener tipos de periodo únicos
     */
    public function findTypes(): array;

    /**
     * Obtener estadísticas de periodos académicos
     */
    public function getStatistics(): array;

    /**
     * Buscar periodos con inscripciones abiertas
     */
    public function findWithOpenRegistration(): array;

    /**
     * Buscar periodos disponibles para cursos
     */
    public function findAvailableForCourses(): array;

    /**
     * Obtener periodos por rango de fechas
     */
    public function findByDateRange(string $startDate, string $endDate): array;

    /**
     * Obtener siguiente periodo académico
     */
    public function findNext(): ?AcademicPeriod;

    /**
     * Obtener periodo anterior
     */
    public function findPrevious(): ?AcademicPeriod;

    /**
     * Verificar integridad de datos
     */
    public function verifyIntegrity(): array;
}

/**
 * Interface AcademicPeriodRepositoryCriteria
 * 
 * Criterios de búsqueda para el repositorio de periodos académicos
 */
interface AcademicPeriodRepositoryCriteria
{
    // Criterios de estado
    public function onlyActive(): self;
    public function onlyInactive(): self;
    public function onlyCurrent(): self;

    // Criterios de tipo
    public function withType(AcademicPeriodType $type): self;
    public function withTypes(array $types): self;
    public function withStandardTypes(): self;
    public function withCustomType(): self;

    // Criterios de año académico
    public function withAcademicYear(int $year): self;
    public function withAcademicYears(array $years): self;
    public function withCurrentYear(): self;

    // Criterios de número de periodo
    public function withPeriodNumber(int $number): self;
    public function withPeriodNumbers(array $numbers): self;

    // Criterios de fecha
    public function startAfter(string $date): self;
    public function startBefore(string $date): self;
    public function startBetween(string $startDate, string $endDate): self;
    public function endAfter(string $date): self;
    public function endBefore(string $date): self;
    public function endBetween(string $startDate, string $endDate): self;

    // Criterios de registro
    public function withOpenRegistration(): self;
    public function withRegistrationStartAfter(string $date): self;
    public function withRegistrationEndBefore(string $date): self;

    // Criterios de nombre/código
    public function withName(string $name): self;
    public function withCode(string $code): self;
    public function withPartialName(string $name): self;

    // Criterios de duración
    public function withMinDuration(int $days): self;
    public function withMaxDuration(int $days): self;
    public function withDurationRange(int $minDays, int $maxDays): self;

    // Criterios de estudiantes
    public function withMinStudentsPerCourse(int $min): self;
    public function withMaxStudentsPerCourse(int $max): self;

    // Criterios temporales
    public function onlyUpcoming(): self;
    public function onlyInProgress(): self;
    public function onlyEnded(): self;

    // Criterios de paginación
    public function withPagination(int $page, int $perPage): self;
    public function withLimit(int $limit): self;

    // Criterios de ordenamiento
    public function orderBy(string $field, string $direction = 'ASC'): self;
    public function orderByCreatedAt(string $direction = 'DESC'): self;
    public function orderByStartDate(string $direction = 'ASC'): self;
    public function orderByAcademicYear(string $direction = 'DESC'): self;
    public function orderByPeriodNumber(string $direction = 'ASC'): self;

    // Criterios de exclusión
    public function excludeIds(array $ids): self;
    public function excludeTypes(array $types): self;
    public function excludeAcademicYears(array $years): self;

    // Construir y ejecutar criterios
    public function build(): array;
    public function execute(): array;
}
