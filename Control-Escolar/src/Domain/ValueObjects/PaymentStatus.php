<?php
/**
 * =============================================================================
 * VALUE OBJECT: PAYMENT STATUS
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Value Object PaymentStatus
 * 
 * Estados de pago de inscripciones
 */
class PaymentStatus
{
    public const PENDING = 'pending';
    public const PARTIAL = 'partial';
    public const PAID = 'paid';
    public const OVERDUE = 'overdue';
    public const WAIVED = 'waived';

    private string $value;

    private static array $validStatuses = [
        self::PENDING,
        self::PARTIAL,
        self::PAID,
        self::OVERDUE,
        self::WAIVED
    ];

    public function __construct(string $value)
    {
        $value = trim($value);
        
        if (!in_array($value, self::$validStatuses)) {
            throw new \InvalidArgumentException(
                sprintf('Estado de pago inválido: %s. Estados válidos: %s', 
                    $value, 
                    implode(', ', self::$validStatuses)
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
     * Estado pendiente
     */
    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    /**
     * Estado parcial
     */
    public static function partial(): self
    {
        return new self(self::PARTIAL);
    }

    /**
     * Estado pagado
     */
    public static function paid(): self
    {
        return new self(self::PAID);
    }

    /**
     * Estado vencido
     */
    public static function overdue(): self
    {
        return new self(self::OVERDUE);
    }

    /**
     * Estado exento
     */
    public static function waived(): self
    {
        return new self(self::WAIVED);
    }

    /**
     * Verificar si está pendiente
     */
    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    /**
     * Verificar si está pagado
     */
    public function isPaid(): bool
    {
        return $this->value === self::PAID;
    }

    /**
     * Verificar si está vencido
     */
    public function isOverdue(): bool
    {
        return $this->value === self::OVERDUE;
    }

    /**
     * Verificar si está exento
     */
    public function isWaived(): bool
    {
        return $this->value === self::WAIVED;
    }

    /**
     * Verificar si necesita pago
     */
    public function needsPayment(): bool
    {
        return in_array($this->value, [self::PENDING, self::PARTIAL, self::OVERDUE]);
    }

    /**
     * Obtener todos los estados válidos
     */
    public static function getValidStatuses(): array
    {
        return self::$validStatuses;
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
        return match($this->value) {
            self::PENDING => 'Pendiente',
            self::PARTIAL => 'Parcial',
            self::PAID => 'Pagado',
            self::OVERDUE => 'Vencido',
            self::WAIVED => 'Exento',
            default => $this->value
        };
    }
}
