<?php
/**
 * =============================================================================
 * INTERFACE USER REPOSITORY - DOMAIN LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\Ports;

use ChristianLMS\Domain\Entities\User;
use ChristianLMS\Domain\ValueObjects\{
    UserId,
    Email,
    UserStatus
};

/**
 * Interface UserRepository
 * 
 * Puerto del dominio para persistencia de usuarios.
 * Define las operaciones de lectura y escritura sin exponer
 * detalles de implementación de infraestructura.
 */
interface UserRepositoryInterface
{
    /**
     * Guardar usuario (crear o actualizar)
     */
    public function save(User $user): User;

    /**
     * Buscar usuario por ID
     */
    public function findById(UserId $id): ?User;

    /**
     * Buscar usuario por email
     */
    public function findByEmail(Email $email): ?User;

    /**
     * Buscar usuario por email de forma case-insensitive
     */
    public function findByEmailCaseInsensitive(Email $email): ?User;

    /**
     * Buscar todos los usuarios
     */
    public function findAll(): array;

    /**
     * Buscar usuarios por estado
     */
    public function findByStatus(UserStatus $status): array;

    /**
     * Buscar usuarios activos
     */
    public function findActive(): array;

    /**
     * Buscar usuarios inactivos
     */
    public function findInactive(): array;

    /**
     * Buscar usuarios por rol
     */
    public function findByRole(string $role): array;

    /**
     * Buscar usuarios con múltiples roles
     */
    public function findByRoles(array $roles): array;

    /**
     * Buscar administradores
     */
    public function findAdmins(): array;

    /**
     * Buscar profesores
     */
    public function findTeachers(): array;

    /**
     * Buscar estudiantes
     */
    public function findStudents(): array;

    /**
     * Eliminar usuario
     */
    public function delete(UserId $id): bool;

    /**
     * Eliminar usuario de forma suave (soft delete)
     */
    public function softDelete(UserId $id): bool;

    /**
     * Verificar si existe un usuario con el ID dado
     */
    public function existsById(UserId $id): bool;

    /**
     * Verificar si existe un usuario con el email dado
     */
    public function existsByEmail(Email $email): bool;

    /**
     * Contar total de usuarios
     */
    public function count(): int;

    /**
     * Contar usuarios por estado
     */
    public function countByStatus(UserStatus $status): int;

    /**
     * Contar usuarios por rol
     */
    public function countByRole(string $role): int;

    /**
     * Obtener usuarios paginados
     */
    public function findPaginated(int $page = 1, int $perPage = 20): array;

    /**
     * Buscar usuarios con filtros
     */
    public function search(array $criteria, int $page = 1, int $perPage = 20): array;

    /**
     * Obtener usuarios recientes (por fecha de creación)
     */
    public function findRecent(int $days = 30): array;

    /**
     * Obtener usuarios que no han iniciado sesión recientemente
     */
    public function findInactiveUsers(int $days = 90): array;

    /**
     * Buscar usuarios por nombre (búsqueda parcial)
     */
    public function findByName(string $name): array;

    /**
     * Obtener el siguiente número de matrícula/secuencia
     */
    public function getNextMatriculaNumber(): int;

    /**
     * Verificar si un email ya existe (excluyendo un ID específico)
     */
    public function emailExistsExcluding(Email $email, UserId $excludeId): bool;

    /**
     * Obtener estadísticas de usuarios
     */
    public function getStatistics(): array;

    /**
     * Buscar usuarios que necesitan activación
     */
    public function findPendingActivation(): array;

    /**
     * Buscar usuarios bloqueados
     */
    public function findBlocked(): array;

    /**
     * Obtener usuarios ordenados
     */
    public function findOrdered(string $orderBy = 'created_at', string $direction = 'DESC'): array;

    /**
     * Buscar usuario por token de verificación
     */
    public function findByVerificationToken(string $token): ?User;

    /**
     * Buscar usuario por token de reset de contraseña
     */
    public function findByPasswordResetToken(string $token): ?User;

    /**
     * Obtener usuarios por rango de fechas de creación
     */
    public function findByDateRange(string $startDate, string $endDate): array;

    /**
     * Limpiar tokens expirados
     */
    public function cleanExpiredTokens(): int;

    /**
     * Obtener último usuario creado
     */
    public function findLastCreated(): ?User;

    /**
     * Verificar integridad de datos
     */
    public function verifyIntegrity(): array;
}

/**
 * Interface UserRepositoryCriteria
 * 
 * Criterios de búsqueda para el repositorio de usuarios
 */
interface UserRepositoryCriteria
{
    // Criterios de estado
    public function withStatus(UserStatus $status): self;
    public function withStatuses(array $statuses): self;
    public function onlyActive(): self;
    public function onlyInactive(): self;

    // Criterios de roles
    public function withRole(string $role): self;
    public function withRoles(array $roles): self;
    public function withAnyRole(array $roles): self;

    // Criterios de fecha
    public function createdAfter(string $date): self;
    public function createdBefore(string $date): self;
    public function createdBetween(string $startDate, string $endDate): self;
    public function updatedAfter(string $date): self;

    // Criterios de nombre/email
    public function withName(string $name): self;
    public function withEmail(string $email): self;
    public function withPartialName(string $name): self;

    // Criterios de paginación
    public function withPagination(int $page, int $perPage): self;
    public function withLimit(int $limit): self;

    // Criterios de ordenamiento
    public function orderBy(string $field, string $direction = 'ASC'): self;
    public function orderByCreatedAt(string $direction = 'DESC'): self;
    public function orderByName(string $direction = 'ASC'): self;

    // Criterios de relaciones
    public function withEnrollments(): self;
    public function withCourses(): self;
    public function withProfile(): self;

    // Criterios de exclusión
    public function excludeIds(array $ids): self;
    public function excludeRoles(array $roles): self;

    // Construir y ejecutar criterios
    public function build(): array;
    public function execute(): array;
}
