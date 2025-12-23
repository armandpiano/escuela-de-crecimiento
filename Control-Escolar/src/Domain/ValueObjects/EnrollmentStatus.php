<?php
/**
 * =============================================================================
 * VALUE OBJECT: ENROLLMENT STATUS
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Value Object EnrollmentStatus
 * 
 * Estados posibles de una inscripción
 */
class EnrollmentStatus
{
    public const ENROLLED = 'enrolled';
    public const DROPPED = 'dropped';
    public const COMPLETED = 'completed';
    public const FAILED = 'failed';
    public const WITHDRAWN = 'withdrawn';

    private string $value;

    private static array $validStatuses = [
        self::ENROLLED,
        self::DROPPED,
        self::COMPLETED,
        self::FAILED,
        self::WITHDRAWN
    ];

    public function __construct(string $value)
    {
        $value = trim($value);
        
        if (!in_array($value, self::$validStatuses)) {
            throw new \InvalidArgumentException(
                sprintf('Estado de inscripción inválido: %s. Estados válidos: %s', 
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
     * Estado inscrito
     */
    public static function enrolled(): self
    {
        return new self(self::ENROLLED);
    }

    /**
     * Estado retirado
     */
    public static function dropped(): self
    {
        return new self(self::DROPPED);
    }

    /**
     * Estado completado
     */
    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    /**
     * Estado reprobado
     */
    public static function failed(): self
    {
        return new self(self::FAILED);
    }

    /**
     * Estado withdrawn
     */
    public static function withdrawn(): self
    {
        return new self(self::WITHDRAWN);
    }

    /**
     * Verificar si está inscrito
     */
    public function isEnrolled(): bool
    {
        return $this->value === self::ENROLLED;
    }

    /**
     * Verificar si está activo
     */
    public function isActive(): bool
    {
        return $this->isEnrolled();
    }

    /**
     * Verificar si está completado
     */
    public function isCompleted(): bool
    {
        return $this->value === self::COMPLETED;
    }

    /**
     * Verificar si está reprobado
     */
    public function isFailed(): bool
    {
        return $this->value === self::FAILED;
    }

    /**
     * Verificar si está retirado
     */
    public function isDropped(): bool
    {
        return $this->value === self::DROPPED;
    }

    /**
     * Verificar si está withdrawn
     */
    public function isWithdrawn(): bool
    {
        return $this->value === self::WITHDRAWN;
    }

    /**
     * Verificar si tiene calificación final
     */
    public function hasFinalGrade(): bool
    {
        return $this->isCompleted() || $this->isFailed();
    }

    /**
     * Verificar si puede modificar calificación
     */
    public function canModifyGrade(): bool
    {
        return $this->isEnrolled();
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
        return match($this->value) {
            self::ENROLLED => 'Inscrito',
            self::DROPPED => 'Retirado',
            self::COMPLETED => 'Completado',
            self::FAILED => 'Reprobado',
            self::WITHDRAWN => 'Retirado',
            default => $this->value
        };
    }
}
