<?php
/**
 * =============================================================================
 * VALUE OBJECT: COURSE ID
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Value Object CourseId
 * 
 * Identificador único para cursos
 */
class CourseId
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('El ID del curso no puede estar vacío');
        }
        
        if (!preg_match('/^[a-f0-9\-]{36}$/', $value)) {
            throw new \InvalidArgumentException('El ID del curso debe ser un UUID válido');
        }
        
        $this->value = $value;
    }

    /**
     * Generar nuevo CourseId
     */
    public static function generate(): self
    {
        return new self(\Ramsey\Uuid\Uuid::uuid4()->toString());
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Comparar igualdad
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Verificar si es nulo
     */
    public function isNull(): bool
    {
        return empty($this->value);
    }

    /**
     * Representación string del objeto
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
