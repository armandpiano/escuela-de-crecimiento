<?php
/**
 * =============================================================================
 * VALUE OBJECT: COURSE STATUS
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Value Object CourseStatus
 * 
 * Estados posibles de un curso
 */
class CourseStatus
{
    public const DRAFT = 'draft';
    public const ACTIVE = 'active';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';
    public const ARCHIVED = 'archived';

    /** @var string */
    private $value;

    /** @var array */

    private static $validStatuses = [
        self::DRAFT,
        self::ACTIVE,
        self::COMPLETED,
        self::CANCELLED,
        self::ARCHIVED
    ];

    public function __construct(string $value)
    {
        $value = trim($value);
        
        if (!in_array($value, self::$validStatuses)) {
            throw new \InvalidArgumentException(
                sprintf('Estado de curso inválido: %s. Estados válidos: %s', 
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
     * Estado borrador
     */
    public static function draft(): self
    {
        return new self(self::DRAFT);
    }

    /**
     * Estado completado
     */
    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    /**
     * Estado cancelado
     */
    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    /**
     * Estado archivado
     */
    public static function archived(): self
    {
        return new self(self::ARCHIVED);
    }

    /**
     * Verificar si el curso está activo
     */
    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    /**
     * Verificar si el curso está en borrador
     */
    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    /**
     * Verificar si el curso está completado
     */
    public function isCompleted(): bool
    {
        return $this->value === self::COMPLETED;
    }

    /**
     * Verificar si el curso está cancelado
     */
    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }

    /**
     * Verificar si el curso está archivado
     */
    public function isArchived(): bool
    {
        return $this->value === self::ARCHIVED;
    }

    /**
     * Verificar si el curso está disponible para inscripciones
     */
    public function isAvailableForEnrollment(): bool
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
