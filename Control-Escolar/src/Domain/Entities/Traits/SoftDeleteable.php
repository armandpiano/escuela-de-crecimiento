<?php
/**
 * =============================================================================
 * TRAIT SOFT DELETABLE
 * Christian LMS System - Domain Layer
 * =============================================================================
 */

namespace ChristianLMS\Domain\Entities\Traits;

/**
 * Trait SoftDeleteable
 * 
 * Proporciona funcionalidad para soft delete (eliminación lógica)
 * en entidades del dominio.
 */
trait SoftDeleteable
{
    /** @var string|null */
    private $deletedAt= null;

    /**
     * Marcar como eliminado (soft delete)
     */
    public function delete(): self
    {
        $this->deletedAt = date('Y-m-d H:i:s');
        $this->touch();
        return $this;
    }

    /**
     * Restaurar de eliminación lógica
     */
    public function restore(): self
    {
        $this->deletedAt = null;
        $this->touch();
        return $this;
    }

    /**
     * Verificar si está eliminado
     */
    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    /**
     * Verificar si está activo (no eliminado)
     */
    public function isActive(): bool
    {
        return !$this->isDeleted();
    }

    /**
     * Obtener fecha de eliminación
     */
    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }

    /**
     * Verificar si fue eliminado recientemente
     */
    public function wasRecentlyDeleted(int $seconds = 300): bool
    {
        if ($this->deletedAt === null) {
            return false;
        }
        
        return (time() - strtotime($this->deletedAt)) <= $seconds;
    }

    /**
     * Obtener tiempo transcurrido desde eliminación
     */
    public function getTimeSinceDeletion(): string
    {
        if ($this->deletedAt === null) {
            return 'No eliminado';
        }
        
        $seconds = time() - strtotime($this->deletedAt);
        
        if ($seconds < 60) {
            return "Eliminado hace {$seconds} segundos";
        }
        
        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return "Eliminado hace {$minutes} minutos";
        }
        
        $hours = floor($minutes / 60);
        if ($hours < 24) {
            return "Eliminado hace {$hours} horas";
        }
        
        $days = floor($hours / 24);
        return "Eliminado hace {$days} días";
    }

    /**
     * Verificar si puede ser eliminado permanentemente
     */
    public function canBeForceDeleted(): bool
    {
        // Permitir eliminación permanente si ha pasado más de 30 días
        if ($this->deletedAt === null) {
            return false;
        }
        
        $daysSinceDeletion = floor((time() - strtotime($this->deletedAt)) / 86400);
        return $daysSinceDeletion >= 30;
    }

    /**
     * Eliminar permanentemente (hard delete)
     */
    public function forceDelete(): void
    {
        $this->deletedAt = null;
    }

    /**
     * Scopes para filtrar por estado de eliminación
     */
    
    /**
     * Scope para obtener solo registros no eliminados
     */
    public static function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope para obtener solo registros eliminados
     */
    public static function scopeDeleted($query)
    {
        return $query->whereNotNull('deleted_at');
    }

    /**
     * Scope para obtener todos los registros (incluyendo eliminados)
     */
    public static function scopeWithTrashed($query)
    {
        return $query;
    }

    /**
     * Scope para obtener solo registros eliminados recientemente
     */
    public static function scopeRecentlyDeleted($query, int $hours = 24)
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        return $query->whereNotNull('deleted_at')
                     ->where('deleted_at', '>=', $cutoff);
    }

    /**
     * Obtener estado legible de la entidad
     */
    public function getStatus(): string
    {
        if ($this->isDeleted()) {
            return 'Eliminado';
        }
        
        return 'Activo';
    }

    /**
     * Verificar si la entidad puede ser modificada
     */
    public function canBeModified(): bool
    {
        return !$this->isDeleted();
    }

    /**
     * Verificar si la entidad puede ser eliminada
     */
    public function canBeDeleted(): bool
    {
        return !$this->isDeleted();
    }
}
