<?php
/**
 * =============================================================================
 * VALUE OBJECT: GRADE LEVEL
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Value Object GradeLevel
 * 
 * Niveles educativos posibles
 */
class GradeLevel
{
    public const PREESCOLAR = 'preescolar';
    public const PRIMARIA = 'primaria';
    public const SECUNDARIA = 'secundaria';
    public const BACHILLERATO = 'bachillerato';
    public const UNIVERSIDAD = 'universidad';
    public const POSGRADO = 'posgrado';

    private string $value;

    private static array $validLevels = [
        self::PREESCOLAR,
        self::PRIMARIA,
        self::SECUNDARIA,
        self::BACHILLERATO,
        self::UNIVERSIDAD,
        self::POSGRADO
    ];

    public function __construct(string $value)
    {
        $value = trim($value);
        
        if (!in_array($value, self::$validLevels)) {
            throw new \InvalidArgumentException(
                sprintf('Nivel educativo inválido: %s. Niveles válidos: %s', 
                    $value, 
                    implode(', ', self::$validLevels)
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
     * Nivel preescolar
     */
    public static function preescolar(): self
    {
        return new self(self::PREESCOLAR);
    }

    /**
     * Nivel primaria
     */
    public static function primaria(): self
    {
        return new self(self::PRIMARIA);
    }

    /**
     * Nivel secundaria
     */
    public static function secundaria(): self
    {
        return new self(self::SECUNDARIA);
    }

    /**
     * Nivel bachillerato
     */
    public static function bachillerato(): self
    {
        return new self(self::BACHILLERATO);
    }

    /**
     * Nivel universidad
     */
    public static function universidad(): self
    {
        return new self(self::UNIVERSIDAD);
    }

    /**
     * Nivel posgrado
     */
    public static function posgrado(): self
    {
        return new self(self::POSGRADO);
    }

    /**
     * Verificar si es nivel básico
     */
    public function isBasic(): bool
    {
        return in_array($this->value, [self::PREESCOLAR, self::PRIMARIA]);
    }

    /**
     * Verificar si es nivel medio
     */
    public function isMiddle(): bool
    {
        return in_array($this->value, [self::SECUNDARIA, self::BACHILLERATO]);
    }

    /**
     * Verificar si es nivel superior
     */
    public function isHigher(): bool
    {
        return in_array($this->value, [self::UNIVERSIDAD, self::POSGRADO]);
    }

    /**
     * Verificar si requiere universidad previa
     */
    public function requiresUniversity(): bool
    {
        return $this->value === self::POSGRADO;
    }

    /**
     * Verificar si requiere bachillerato
     */
    public function requiresHighSchool(): bool
    {
        return in_array($this->value, [self::UNIVERSIDAD, self::POSGRADO]);
    }

    /**
     * Obtener nombre en español
     */
    public function getDisplayName(): string
    {
        return match($this->value) {
            self::PREESCOLAR => 'Preescolar',
            self::PRIMARIA => 'Primaria',
            self::SECUNDARIA => 'Secundaria',
            self::BACHILLERATO => 'Bachillerato',
            self::UNIVERSIDAD => 'Universidad',
            self::POSGRADO => 'Posgrado',
            default => $this->value
        };
    }

    /**
     * Obtener todos los niveles válidos
     */
    public static function getValidLevels(): array
    {
        return self::$validLevels;
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
        return $this->getDisplayName();
    }
}
