<?php
/**
 * =============================================================================
 * VALUE OBJECT: SUBJECT STATUS
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Value Object SubjectStatus
 * 
 * Estados posibles de una materia
 */
class SubjectStatus
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';
    public const DEPRECATED = 'deprecated';

    private string $value;

    private static array $validStatuses = [
        self::ACTIVE,
        self::INACTIVE,
        self::DEPRECATED
    ];

    public function __construct(string $value)
    {
        $value = trim($value);
        
        if (!in_array($value, self::$validStatuses)) {
            throw new \InvalidArgumentException(
                sprintf('Estado de materia inválido: %s. Estados válidos: %s', 
                    $value, 
                    implode(', ', self::$validStatuses)
                )
            );
        }
        
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Estado activo
     */
    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    /**
     * Estado inactivo
     */
    public static function inactive(): self
    {
        return new self(self::INACTIVE);
    }

    /**
     * Estado deprecado
     */
    public static function deprecated(): self
    {
        return new self(self::DEPRECATED);
    }

    /**
     * Verificar si está activa
     */
    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    /**
     * Verificar si está inactiva
     */
    public function isInactive(): bool
    {
        return $this->value === self::INACTIVE;
    }

    /**
     * Verificar si está deprecada
     */
    public function isDeprecated(): bool
    {
        return $this->value === self::DEPRECATED;
    }

    /**
     * Verificar si está disponible para cursos
     */
    public function isAvailableForCourses(): bool
    {
        return $this->isActive();
    }

    /**
     * Obtener todos los estados válidos
     */
    public static function getValidStatuses(): array
    {
        return self::$validStatuses;
    }

    /**
     * Comparar igualdad
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Representación string del objeto
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
