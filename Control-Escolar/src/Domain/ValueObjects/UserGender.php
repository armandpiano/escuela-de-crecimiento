<?php
/**
 * =============================================================================
 * ENUM USER GENDER
 * Christian LMS System - Domain Layer
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Enum UserGender
 * 
 * Representa los géneros de usuario en el sistema.
 */
enum UserGender: string
{
    case MALE = 'male';
    case FEMALE = 'female';
    case NON_BINARY = 'non_binary';
    case PREFER_NOT_TO_SAY = 'prefer_not_to_say';
    case UNSPECIFIED = 'unspecified';

    /**
     * Obtener etiqueta legible
     */
    public function getLabel(): string
    {
        return match($this) {
            self::MALE => 'Masculino',
            self::FEMALE => 'Femenino',
            self::NON_BINARY => 'No binario',
            self::PREFER_NOT_TO_SAY => 'Prefiero no decir',
            self::UNSPECIFIED => 'No especificado',
        };
    }

    /**
     * Obtener descripción
     */
    public function getDescription(): string
    {
        return match($this) {
            self::MALE => 'Usuario identificado como masculino',
            self::FEMALE => 'Usuario identificada como femenina',
            self::NON_BINARY => 'Usuario que no se identifica como masculino ni femenino',
            self::PREFER_NOT_TO_SAY => 'Usuario que prefiere no especificar su género',
            self::UNSPECIFIED => 'Género no especificado en el sistema',
        };
    }

    /**
     * Obtener pronombre personal
     */
    public function getPersonalPronoun(): string
    {
        return match($this) {
            self::MALE => 'él',
            self::FEMALE => 'ella',
            self::NON_BINARY => 'elle',
            self::PREFER_NOT_TO_SAY => 'elle',
            self::UNSPECIFIED => 'elle',
        };
    }

    /**
     * Obtener pronombre posesivo
     */
    public function getPossessivePronoun(): string
    {
        return match($this) {
            self::MALE => 'su',
            self::FEMALE => 'su',
            self::NON_BINARY => 'suy',
            self::PREFER_NOT_TO_SAY => 'suy',
            self::UNSPECIFIED => 'suy',
        };
    }

    /**
     * Obtener pronombre reflexivo
     */
    public function getReflexivePronoun(): string
    {
        return match($this) {
            self::MALE => 'se',
            self::FEMALE => 'se',
            self::NON_BINARY => 'se',
            self::PREFER_NOT_TO_SAY => 'se',
            self::UNSPECIFIED => 'se',
        };
    }

    /**
     * Obtener icono para UI
     */
    public function getIcon(): string
    {
        return match($this) {
            self::MALE => 'fas fa-mars',
            self::FEMALE => 'fas fa-venus',
            self::NON_BINARY => 'fas fa-genderless',
            self::PREFER_NOT_TO_SAY => 'fas fa-question',
            self::UNSPECIFIED => 'fas fa-user',
        };
    }

    /**
     * Obtener color para UI
     */
    public function getColor(): string
    {
        return match($this) {
            self::MALE => '#007bff',
            self::FEMALE => '#e91e63',
            self::NON_BINARY => '#9c27b0',
            self::PREFER_NOT_TO_SAY => '#6c757d',
            self::UNSPECIFIED => '#6c757d',
        };
    }

    /**
     * Verificar si es específico
     */
    public function isSpecific(): bool
    {
        return in_array($this, [self::MALE, self::FEMALE, self::NON_BINARY]);
    }

    /**
     * Verificar si es una preferencia
     */
    public function isPreference(): bool
    {
        return $this === self::PREFER_NOT_TO_SAY;
    }

    /**
     * Verificar si está sin especificar
     */
    public function isUnspecified(): bool
    {
        return in_array($this, [self::UNSPECIFIED, self::PREFER_NOT_TO_SAY]);
    }

    /**
     * Obtener género por defecto
     */
    public static function getDefault(): self
    {
        return self::UNSPECIFIED;
    }

    /**
     * Crear desde string
     */
    public static function fromString(string $value): ?self
    {
        $value = strtolower(trim($value));
        
        // Mapeo de valores alternativos
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
        
        return $mapping[$value] ?? null;
    }

    /**
     * Validar si un valor es un género válido
     */
    public static function isValid(string $value): bool
    {
        return self::fromString($value) !== null;
    }

    /**
     * Obtener géneros específicos (no incluyendo preferencias)
     */
    public static function getSpecificGenders(): array
    {
        return [self::MALE, self::FEMALE, self::NON_BINARY];
    }

    /**
     * Obtener todos los géneros para formularios
     */
    public static function getFormOptions(): array
    {
        return [
            self::UNSPECIFIED->value => self::UNSPECIFIED->getLabel(),
            self::MALE->value => self::MALE->getLabel(),
            self::FEMALE->value => self::FEMALE->getLabel(),
            self::NON_BINARY->value => self::NON_BINARY->getLabel(),
            self::PREFER_NOT_TO_SAY->value => self::PREFER_NOT_TO_SAY->getLabel(),
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
        return $this === self::PREFER_NOT_TO_SAY;
    }

    /**
     * Obtener mensaje de ayuda para formularios
     */
    public function getHelpText(): string
    {
        return match($this) {
            self::MALE => 'Usuario identificado como masculino',
            self::FEMALE => 'Usuario identificada como femenina',
            self::NON_BINARY => 'Usuario que no se identifica como masculino ni femenino',
            self::PREFER_NOT_TO_SAY => 'Información confidencial - se respeta la privacidad',
            self::UNSPECIFIED => 'No especificado en el registro',
        };
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
