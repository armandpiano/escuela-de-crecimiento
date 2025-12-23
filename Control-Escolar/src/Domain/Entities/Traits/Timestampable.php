<?php
/**
 * =============================================================================
 * TRAIT TIMESTAMPABLE
 * Christian LMS System - Domain Layer
 * =============================================================================
 */

namespace ChristianLMS\Domain\Entities\Traits;

/**
 * Trait Timestampable
 * 
 * Proporciona funcionalidad para manejar timestamps de creación y actualización
 * en entidades del dominio.
 */
trait Timestampable
{
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    /**
     * Establecer fecha de creación
     */
    public function setCreatedAt(string $timestamp = null): self
    {
        $this->createdAt = $timestamp ?? date('Y-m-d H:i:s');
        return $this;
    }

    /**
     * Establecer fecha de actualización
     */
    public function setUpdatedAt(string $timestamp = null): self
    {
        $this->updatedAt = $timestamp ?? date('Y-m-d H:i:s');
        return $this;
    }

    /**
     * Obtener fecha de creación
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Obtener fecha de actualización
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Verificar si la entidad es nueva (no guardada)
     */
    public function isNew(): bool
    {
        return $this->createdAt === null;
    }

    /**
     * Actualizar timestamp de modificación
     */
    public function touch(): self
    {
        $this->updatedAt = date('Y-m-d H:i:s');
        return $this;
    }

    /**
     * Obtener antigüedad en segundos
     */
    public function getAgeInSeconds(): int
    {
        if ($this->createdAt === null) {
            return 0;
        }
        
        return time() - strtotime($this->createdAt);
    }

    /**
     * Obtener antigüedad en formato legible
     */
    public function getAgeString(): string
    {
        $seconds = $this->getAgeInSeconds();
        
        if ($seconds < 60) {
            return "{$seconds} segundos";
        }
        
        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return "{$minutes} minutos";
        }
        
        $hours = floor($minutes / 60);
        if ($hours < 24) {
            return "{$hours} horas";
        }
        
        $days = floor($hours / 24);
        if ($days < 30) {
            return "{$days} días";
        }
        
        $months = floor($days / 30);
        if ($months < 12) {
            return "{$months} meses";
        }
        
        $years = floor($months / 12);
        return "{$years} años";
    }

    /**
     * Verificar si fue actualizado recientemente
     */
    public function wasRecentlyUpdated(int $seconds = 300): bool
    {
        if ($this->updatedAt === null) {
            return false;
        }
        
        return (time() - strtotime($this->updatedAt)) <= $seconds;
    }

    /**
     * Verificar si fue creado recientemente
     */
    public function wasRecentlyCreated(int $seconds = 300): bool
    {
        if ($this->createdAt === null) {
            return false;
        }
        
        return (time() - strtotime($this->createdAt)) <= $seconds;
    }
}
