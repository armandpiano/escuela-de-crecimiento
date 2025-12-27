<?php
/**
 * =============================================================================
 * VALUE OBJECT USER GENDER
 * Christian LMS System - Domain Layer
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Value Object UserGender
 *
 * Representa los géneros de usuario en el sistema.
 */
class UserGender
{
    public const MALE = 'male';
    public const FEMALE = 'female';
    public const NON_BINARY = 'non_binary';
    public const PREFER_NOT_TO_SAY = 'prefer_not_to_say';
    public const UNSPECIFIED = 'unspecified';

    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        if (!self::isValid($value)) {
            throw new \InvalidArgumentException('Género inválido');
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
            case self::MALE:
                return 'Masculino';
            case self::FEMALE:
                return 'Femenino';
            case self::NON_BINARY:
                return 'No binario';
            case self::PREFER_NOT_TO_SAY:
                return 'Prefiero no decir';
            case self::UNSPECIFIED:
            default:
                return 'No especificado';
        }
    }

    /**
     * Obtener descripción
     */
    public function getDescription(): string
    {
        switch ($this->value) {
            case self::MALE:
                return 'Usuario identificado como masculino';
            case self::FEMALE:
                return 'Usuario identificada como femenina';
            case self::NON_BINARY:
                return 'Usuario que no se identifica como masculino ni femenino';
            case self::PREFER_NOT_TO_SAY:
                return 'Usuario que prefiere no especificar su género';
            case self::UNSPECIFIED:
            default:
                return 'Género no especificado en el sistema';
        }
    }

    /**
     * Obtener pronombre personal
     */
    public function getPersonalPronoun(): string
    {
        switch ($this->value) {
            case self::MALE:
                return 'él';
            case self::FEMALE:
                return 'ella';
            case self::NON_BINARY:
            case self::PREFER_NOT_TO_SAY:
            case self::UNSPECIFIED:
            default:
                return 'elle';
        }
    }

    /**
     * Obtener pronombre posesivo
     */
    public function getPossessivePronoun(): string
    {
        switch ($this->value) {
            case self::NON_BINARY:
            case self::PREFER_NOT_TO_SAY:
            case self::UNSPECIFIED:
                return 'suy';
            case self::MALE:
            case self::FEMALE:
            default:
                return 'su';
        }
    }

    /**
     * Obtener pronombre reflexivo
     */
    public function getReflexivePronoun(): string
    {
        return 'se';
    }

    /**
     * Obtener icono para UI
     */
    public function getIcon(): string
    {
        switch ($this->value) {
            case self::MALE:
                return 'bi bi-gender-male';
            case self::FEMALE:
                return 'bi bi-gender-female';
            case self::NON_BINARY:
                return 'bi bi-gender-trans';
            case self::PREFER_NOT_TO_SAY:
                return 'bi bi-question-circle';
            case self::UNSPECIFIED:
            default:
                return 'bi bi-person';
        }
    }

    /**
     * Obtener color para UI
     */
    public function getColor(): string
    {
        switch ($this->value) {
            case self::MALE:
                return '#007bff';
            case self::FEMALE:
                return '#e91e63';
            case self::NON_BINARY:
                return '#9c27b0';
            case self::PREFER_NOT_TO_SAY:
            case self::UNSPECIFIED:
            default:
                return '#6c757d';
        }
    }

    /**
     * Verificar si es específico
     */
    public function isSpecific(): bool
    {
        return in_array($this->value, [self::MALE, self::FEMALE, self::NON_BINARY], true);
    }

    /**
     * Verificar si es una preferencia
     */
    public function isPreference(): bool
    {
        return $this->value === self::PREFER_NOT_TO_SAY;
    }

    /**
     * Verificar si está sin especificar
     */
    public function isUnspecified(): bool
    {
        return in_array($this->value, [self::UNSPECIFIED, self::PREFER_NOT_TO_SAY], true);
    }

    /**
     * Obtener género por defecto
     */
    public static function getDefault(): self
    {
        return new self(self::UNSPECIFIED);
    }

    /**
     * Crear desde string
     */
    public static function fromString(string $value): ?self
    {
        $value = strtolower(trim($value));

        $mapping = [
            'm' => self::MALE,
            'masculino' => self::MALE,
            'man' => self::MALE,
            'male' => self::MALE,
            'f' => self::FEMALE,
            'femenino' => self::FEMALE,
            'woman' => self::FEMALE,
            'female' => self::FEMALE,
            'nb' => self::NON_BINARY,
            'non-binary' => self::NON_BINARY,
            'no-binario' => self::NON_BINARY,
            'genderqueer' => self::NON_BINARY,
            'unspecified' => self::UNSPECIFIED,
            'no-especificado' => self::UNSPECIFIED,
            'unknown' => self::UNSPECIFIED,
            'none' => self::UNSPECIFIED,
            'prefer-not-to-say' => self::PREFER_NOT_TO_SAY,
            'prefiero-no-decir' => self::PREFER_NOT_TO_SAY,
            'pnts' => self::PREFER_NOT_TO_SAY,
        ];

        if (isset($mapping[$value])) {
            return new self($mapping[$value]);
        }

        return null;
    }

    /**
     * Validar si un valor es un género válido
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, [
            self::MALE,
            self::FEMALE,
            self::NON_BINARY,
            self::PREFER_NOT_TO_SAY,
            self::UNSPECIFIED
        ], true);
    }

    /**
     * Obtener géneros específicos (no incluyendo preferencias)
     */
    public static function getSpecificGenders(): array
    {
        return [
            new self(self::MALE),
            new self(self::FEMALE),
            new self(self::NON_BINARY)
        ];
    }

    /**
     * Obtener todos los géneros para formularios
     */
    public static function getFormOptions(): array
    {
        return [
            self::UNSPECIFIED => (new self(self::UNSPECIFIED))->getLabel(),
            self::MALE => (new self(self::MALE))->getLabel(),
            self::FEMALE => (new self(self::FEMALE))->getLabel(),
            self::NON_BINARY => (new self(self::NON_BINARY))->getLabel(),
            self::PREFER_NOT_TO_SAY => (new self(self::PREFER_NOT_TO_SAY))->getLabel(),
        ];
    }

    /**
     * Obtener estadísticas de género (para reportes)
     */
    public function getStatistics(): array
    {
        return [
            'specific' => $this->isSpecific(),
            'preference' => $this->isPreference(),
            'unspecified' => $this->isUnspecified(),
            'has_pronouns' => $this->isSpecific() || $this->isPreference(),
        ];
    }

    /**
     * Verificar si requiere validación adicional
     */
    public function requiresValidation(): bool
    {
        return $this->value === self::PREFER_NOT_TO_SAY;
    }

    /**
     * Obtener mensaje de ayuda para formularios
     */
    public function getHelpText(): string
    {
        switch ($this->value) {
            case self::MALE:
                return 'Usuario identificado como masculino';
            case self::FEMALE:
                return 'Usuario identificada como femenina';
            case self::NON_BINARY:
                return 'Usuario que no se identifica como masculino ni femenino';
            case self::PREFER_NOT_TO_SAY:
                return 'Información confidencial - se respeta la privacidad';
            case self::UNSPECIFIED:
            default:
                return 'No especificado en el registro';
        }
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
