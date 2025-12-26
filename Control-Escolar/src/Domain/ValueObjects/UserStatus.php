<?php
/**
 * =============================================================================
 * VALUE OBJECT USER STATUS
 * Christian LMS System - Domain Layer
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Value Object UserStatus
 *
 * Representa los posibles estados de un usuario en el sistema.
 */
class UserStatus
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';
    public const SUSPENDED = 'suspended';
    public const PENDING = 'pending';
    public const BANNED = 'banned';
    public const DELETED = 'deleted';

    /** @var string */
    private $value;

    /** @var array */
    private static $validStatuses = [
        self::ACTIVE,
        self::INACTIVE,
        self::SUSPENDED,
        self::PENDING,
        self::BANNED,
        self::DELETED,
    ];

    public function __construct(string $value)
    {
        if (!in_array($value, self::$validStatuses, true)) {
            throw new \InvalidArgumentException('Estado de usuario inválido');
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Obtener etiqueta legible
     */
    public function getLabel(): string
    {
        switch ($this->value) {
            case self::ACTIVE:
                return 'Activo';
            case self::INACTIVE:
                return 'Inactivo';
            case self::SUSPENDED:
                return 'Suspendido';
            case self::PENDING:
                return 'Pendiente';
            case self::BANNED:
                return 'Baneado';
            case self::DELETED:
                return 'Eliminado';
            default:
                return 'Desconocido';
        }
    }

    /**
     * Obtener descripción
     */
    public function getDescription(): string
    {
        switch ($this->value) {
            case self::ACTIVE:
                return 'Usuario activo que puede acceder al sistema';
            case self::INACTIVE:
                return 'Usuario inactivo temporalmente';
            case self::SUSPENDED:
                return 'Usuario suspendido por violaciones';
            case self::PENDING:
                return 'Usuario pendiente de activación';
            case self::BANNED:
                return 'Usuario permanentemente baneado';
            case self::DELETED:
                return 'Usuario eliminado del sistema';
            default:
                return 'Estado no reconocido';
        }
    }

    /**
     * Obtener color para UI
     */
    public function getColor(): string
    {
        switch ($this->value) {
            case self::ACTIVE:
                return '#28a745';
            case self::INACTIVE:
                return '#6c757d';
            case self::SUSPENDED:
                return '#ffc107';
            case self::PENDING:
                return '#17a2b8';
            case self::BANNED:
                return '#dc3545';
            case self::DELETED:
                return '#343a40';
            default:
                return '#6c757d';
        }
    }

    /**
     * Obtener icono para UI
     */
    public function getIcon(): string
    {
        switch ($this->value) {
            case self::ACTIVE:
                return 'fas fa-check-circle';
            case self::INACTIVE:
                return 'fas fa-pause-circle';
            case self::SUSPENDED:
                return 'fas fa-exclamation-triangle';
            case self::PENDING:
                return 'fas fa-clock';
            case self::BANNED:
                return 'fas fa-ban';
            case self::DELETED:
                return 'fas fa-trash';
            default:
                return 'fas fa-user';
        }
    }

    /**
     * Verificar si el usuario puede acceder al sistema
     */
    public function canAccess(): bool
    {
        return in_array($this->value, [self::ACTIVE, self::PENDING], true);
    }

    /**
     * Verificar si el usuario puede iniciar sesión
     */
    public function canLogin(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->value === self::INACTIVE;
    }

    /**
     * Verificar si el usuario está bloqueado
     */
    public function isBlocked(): bool
    {
        return in_array($this->value, [self::SUSPENDED, self::BANNED, self::DELETED], true);
    }

    /**
     * Verificar si el estado es temporal
     */
    public function isTemporary(): bool
    {
        return in_array($this->value, [self::INACTIVE, self::SUSPENDED, self::PENDING], true);
    }

    /**
     * Obtener transiciones permitidas
     */
    public function getAllowedTransitions(): array
    {
        switch ($this->value) {
            case self::PENDING:
                return [self::ACTIVE, self::INACTIVE, self::BANNED];
            case self::ACTIVE:
                return [self::INACTIVE, self::SUSPENDED, self::BANNED];
            case self::INACTIVE:
                return [self::ACTIVE, self::SUSPENDED, self::BANNED];
            case self::SUSPENDED:
                return [self::ACTIVE, self::INACTIVE, self::BANNED];
            case self::BANNED:
            case self::DELETED:
            default:
                return [];
        }
    }

    /**
     * Verificar si puede transicionar a otro estado
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target->getValue(), $this->getAllowedTransitions(), true);
    }

    /**
     * Obtener prioridad del estado (para ordenamiento)
     */
    public function getPriority(): int
    {
        switch ($this->value) {
            case self::ACTIVE:
                return 1;
            case self::PENDING:
                return 2;
            case self::INACTIVE:
                return 3;
            case self::SUSPENDED:
                return 4;
            case self::BANNED:
                return 5;
            case self::DELETED:
            default:
                return 6;
        }
    }

    /**
     * Crear desde string
     */
    public static function fromString(string $value): ?self
    {
        if (!in_array($value, self::$validStatuses, true)) {
            return null;
        }

        return new self($value);
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function inactive(): self
    {
        return new self(self::INACTIVE);
    }

    public static function suspended(): self
    {
        return new self(self::SUSPENDED);
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function banned(): self
    {
        return new self(self::BANNED);
    }

    public static function deleted(): self
    {
        return new self(self::DELETED);
    }

    /**
     * Obtener todos los casos activos
     */
    public static function getActiveStatuses(): array
    {
        return [self::active(), self::pending()];
    }

    /**
     * Obtener todos los casos bloqueados
     */
    public static function getBlockedStatuses(): array
    {
        return [self::suspended(), self::banned(), self::deleted()];
    }

    /**
     * Validar si un valor es un estado válido
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::$validStatuses, true);
    }

    /**
     * Obtener estado por defecto
     */
    public static function getDefault(): self
    {
        return self::pending();
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
