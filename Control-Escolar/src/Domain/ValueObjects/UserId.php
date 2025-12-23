<?php
/**
 * =============================================================================
 * VALUE OBJECT USER ID
 * Christian LMS System - Domain Layer
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Value Object UserId
 * 
 * Representa el identificador único de un usuario.
 * Es un value object inmutable.
 */
class UserId
{
    private string $value;

    /**
     * Constructor
     */
    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Generar nuevo ID
     */
    public static function generate(): self
    {
        return new self(uniqid('usr_', true));
    }

    /**
     * Crear desde string
     */
    public static function fromString(string $value): self
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('El ID de usuario no puede estar vacío');
        }

        if (strlen($value) > 255) {
            throw new \InvalidArgumentException('El ID de usuario es demasiado largo');
        }

        return new self($value);
    }

    /**
     * Crear desde UUID
     */
    public static function fromUuid(string $uuid): self
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid)) {
            throw new \InvalidArgumentException('Formato de UUID inválido');
        }

        return new self($uuid);
    }

    /**
     * Obtener valor
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Verificar si es igual a otro UserId
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Verificar si es un UUID válido
     */
    public function isUuid(): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $this->value) === 1;
    }

    /**
     * Obtener versión del UUID
     */
    public function getUuidVersion(): ?int
    {
        if (!$this->isUuid()) {
            return null;
        }

        return hexdec(substr($this->value, 12, 1)) & 0xf;
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
