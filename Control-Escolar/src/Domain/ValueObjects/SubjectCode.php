<?php
/**
 * =============================================================================
 * VALUE OBJECT: SUBJECT CODE
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Value Object SubjectCode
 * 
 * Código único para materias
 */
class SubjectCode
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        
        if (empty($value)) {
            throw new \InvalidArgumentException('El código de la materia no puede estar vacío');
        }
        
        if (strlen($value) > 20) {
            throw new \InvalidArgumentException('El código de la materia no puede exceder 20 caracteres');
        }
        
        if (!preg_match('/^[A-Z0-9_\-]+$/', $value)) {
            throw new \InvalidArgumentException('El código de la materia solo puede contener letras mayúsculas, números, guiones y guiones bajos');
        }
        
        $this->value = strtoupper($value);
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
