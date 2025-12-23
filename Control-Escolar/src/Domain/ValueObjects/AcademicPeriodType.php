<?php
/**
 * =============================================================================
 * VALUE OBJECT: ACADEMIC PERIOD TYPE
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Value Object AcademicPeriodType
 * 
 * Tipos de periodos académicos
 */
class AcademicPeriodType
{
    public const SEMESTRE = 'semestre';
    public const CUATRIMESTRE = 'cuatrimestre';
    public const TRIMESTRE = 'trimestre';
    public const BIMESTRE = 'bimestre';
    public const MONTHLY = 'monthly';
    public const CUSTOM = 'custom';

    private string $value;

    private static array $validTypes = [
        self::SEMESTRE,
        self::CUATRIMESTRE,
        self::TRIMESTRE,
        self::BIMESTRE,
        self::MONTHLY,
        self::CUSTOM
    ];

    public function __construct(string $value)
    {
        $value = trim($value);
        
        if (!in_array($value, self::$validTypes)) {
            throw new \InvalidArgumentException(
                sprintf('Tipo de periodo académico inválido: %s. Tipos válidos: %s', 
                    $value, 
                    implode(', ', self::$validTypes)
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
     * Tipo semestre
     */
    public static function semestre(): self
    {
        return new self(self::SEMESTRE);
    }

    /**
     * Tipo cuatrimestre
     */
    public static function cuatrimestre(): self
    {
        return new self(self::CUATRIMESTRE);
    }

    /**
     * Tipo trimestre
     */
    public static function trimestre(): self
    {
        return new self(self::TRIMESTRE);
    }

    /**
     * Tipo bimestre
     */
    public static function bimestre(): self
    {
        return new self(self::BIMESTRE);
    }

    /**
     * Tipo mensual
     */
    public static function monthly(): self
    {
        return new self(self::MONTHLY);
    }

    /**
     * Tipo personalizado
     */
    public static function custom(): self
    {
        return new self(self::CUSTOM);
    }

    /**
     * Verificar si es un tipo estándar
     */
    public function isStandard(): bool
    {
        return in_array($this->value, [self::SEMESTRE, self::CUATRIMESTRE, self::TRIMESTRE]);
    }

    /**
     * Verificar si es un tipo personalizado
     */
    public function isCustom(): bool
    {
        return $this->value === self::CUSTOM;
    }

    /**
     * Obtener duración en semanas típica
     */
    public function getTypicalWeeks(): int
    {
        return match($this->value) {
            self::SEMESTRE => 18,
            self::CUATRIMESTRE => 16,
            self::TRIMESTRE => 12,
            self::BIMESTRE => 8,
            self::MONTHLY => 4,
            self::CUSTOM => 0,
            default => 0
        };
    }

    /**
     * Obtener nombre en español
     */
    public function getDisplayName(): string
    {
        return match($this->value) {
            self::SEMESTRE => 'Semestre',
            self::CUATRIMESTRE => 'Cuatrimestre',
            self::TRIMESTRE => 'Trimestre',
            self::BIMESTRE => 'Bimestre',
            self::MONTHLY => 'Mensual',
            self::CUSTOM => 'Personalizado',
            default => $this->value
        };
    }

    /**
     * Obtener todos los tipos válidos
     */
    public static function getValidTypes(): array
    {
        return self::$validTypes;
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
