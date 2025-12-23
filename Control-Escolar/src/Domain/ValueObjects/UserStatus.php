<?php
/**
 * =============================================================================
 * ENUM USER STATUS
 * Christian LMS System - Domain Layer
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Enum UserStatus
 * 
 * Representa los posibles estados de un usuario en el sistema.
 */
enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';
    case BANNED = 'banned';
    case DELETED = 'deleted';

    /**
     * Obtener etiqueta legible
     */
    public function getLabel(): string
    {
        return match($this) {
            self::ACTIVE => 'Activo',
            self::INACTIVE => 'Inactivo',
            self::SUSPENDED => 'Suspendido',
            self::PENDING => 'Pendiente',
            self::BANNED => 'Baneado',
            self::DELETED => 'Eliminado',
        };
    }

    /**
     * Obtener descripción
     */
    public function getDescription(): string
    {
        return match($this) {
            self::ACTIVE => 'Usuario activo que puede acceder al sistema',
            self::INACTIVE => 'Usuario inactivo temporalmente',
            self::SUSPENDED => 'Usuario suspendido por violaciones',
            self::PENDING => 'Usuario pendiente de activación',
            self::BANNED => 'Usuario permanentemente baneado',
            self::DELETED => 'Usuario eliminado del sistema',
        };
    }

    /**
     * Obtener color para UI
     */
    public function getColor(): string
    {
        return match($this) {
            self::ACTIVE => '#28a745',
            self::INACTIVE => '#6c757d',
            self::SUSPENDED => '#ffc107',
            self::PENDING => '#17a2b8',
            self::BANNED => '#dc3545',
            self::DELETED => '#343a40',
        };
    }

    /**
     * Obtener icono para UI
     */
    public function getIcon(): string
    {
        return match($this) {
            self::ACTIVE => 'fas fa-check-circle',
            self::INACTIVE => 'fas fa-pause-circle',
            self::SUSPENDED => 'fas fa-exclamation-triangle',
            self::PENDING => 'fas fa-clock',
            self::BANNED => 'fas fa-ban',
            self::DELETED => 'fas fa-trash',
        };
    }

    /**
     * Verificar si el usuario puede acceder al sistema
     */
    public function canAccess(): bool
    {
        return in_array($this, [self::ACTIVE, self::PENDING]);
    }

    /**
     * Verificar si el usuario puede iniciar sesión
     */
    public function canLogin(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Verificar si el usuario está bloqueado
     */
    public function isBlocked(): bool
    {
        return in_array($this, [self::SUSPENDED, self::BANNED, self::DELETED]);
    }

    /**
     * Verificar si el estado es temporal
     */
    public function isTemporary(): bool
    {
        return in_array($this, [self::INACTIVE, self::SUSPENDED, self::PENDING]);
    }

    /**
     * Obtener transiciones permitidas
     */
    public function getAllowedTransitions(): array
    {
        return match($this) {
            self::PENDING => [self::ACTIVE, self::INACTIVE, self::BANNED],
            self::ACTIVE => [self::INACTIVE, self::SUSPENDED, self::BANNED],
            self::INACTIVE => [self::ACTIVE, self::SUSPENDED, self::BANNED],
            self::SUSPENDED => [self::ACTIVE, self::INACTIVE, self::BANNED],
            self::BANNED => [], // No se puede cambiar desde baneado
            self::DELETED => [], // No se puede cambiar desde eliminado
        };
    }

    /**
     * Verificar si puede transicionar a otro estado
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->getAllowedTransitions());
    }

    /**
     * Obtener prioridad del estado (para ordenamiento)
     */
    public function getPriority(): int
    {
        return match($this) {
            self::ACTIVE => 1,
            self::PENDING => 2,
            self::INACTIVE => 3,
            self::SUSPENDED => 4,
            self::BANNED => 5,
            self::DELETED => 6,
        };
    }

    /**
     * Crear desde string
     */
    public static function fromString(string $value): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }
        
        return null;
    }

    /**
     * Obtener todos los casos activos
     */
    public static function getActiveStatuses(): array
    {
        return [self::ACTIVE, self::PENDING];
    }

    /**
     * Obtener todos los casos bloqueados
     */
    public static function getBlockedStatuses(): array
    {
        return [self::SUSPENDED, self::BANNED, self::DELETED];
    }

    /**
     * Validar si un valor es un estado válido
     */
    public static function isValid(string $value): bool
    {
        return self::tryFrom($value) !== null;
    }

    /**
     * Obtener estado por defecto
     */
    public static function getDefault(): self
    {
        return self::PENDING;
    }

    /**
     * Representación string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Serialización para JSON
     */
    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
