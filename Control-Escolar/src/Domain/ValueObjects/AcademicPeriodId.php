<?php
/**
 * =============================================================================
 * VALUE OBJECT: ACADEMIC PERIOD ID
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Value Object AcademicPeriodId
 * 
 * Identificador único para periodos académicos
 */
class AcademicPeriodId
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('El ID del periodo académico no puede estar vacío');
        }
        
        if (!preg_match('/^[a-f0-9\-]{36}$/', $value)) {
            throw new \InvalidArgumentException('El ID del periodo académico debe ser un UUID válido');
        }
        
        $this->value = $value;
    }

    /**
     * Generar nuevo AcademicPeriodId
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
     * Representación string del objeto
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
