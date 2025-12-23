<?php
/**
 * =============================================================================
 * VALUE OBJECT PASSWORD HASH
 * Christian LMS System - Domain Layer
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Value Object PasswordHash
 * 
 * Representa un hash de contraseña de forma segura.
 * Es un value object inmutable.
 */
class PasswordHash
{
    private string $value;
    private ?string $algorithm = null;
    private ?int $cost = null;

    /**
     * Constructor
     */
    private function __construct(string $hash, ?string $algorithm = null, ?int $cost = null)
    {
        $this->value = $hash;
        $this->algorithm = $algorithm;
        $this->cost = $cost;
    }

    /**
     * Crear hash de contraseña nueva
     */
    public static function fromPlainPassword(string $password): self
    {
        if (empty(trim($password))) {
            throw new \InvalidArgumentException('La contraseña no puede estar vacía');
        }

        if (strlen($password) < 8) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos 8 caracteres');
        }

        if (strlen($password) > 255) {
            throw new \InvalidArgumentException('La contraseña es demasiado larga');
        }

        // Verificar complejidad básica
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
            throw new \InvalidArgumentException('La contraseña debe contener al menos una minúscula, una mayúscula y un número');
        }

        $hash = password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3,         // 3 threads
        ]);

        if ($hash === false) {
            throw new \RuntimeException('Error al generar el hash de la contraseña');
        }

        return new self($hash, 'argon2id', 4);
    }

    /**
     * Crear desde hash existente
     */
    public static function fromHash(string $hash): self
    {
        if (empty(trim($hash))) {
            throw new \InvalidArgumentException('El hash no puede estar vacío');
        }

        if (strlen($hash) < 30) {
            throw new \InvalidArgumentException('Formato de hash inválido');
        }

        return new self($hash);
    }

    /**
     * Obtener valor del hash
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Verificar contraseña contra el hash
     */
    public function verify(string $password): bool
    {
        return password_verify($password, $this->value);
    }

    /**
     * Verificar si necesita rehash (algoritmo obsoleto)
     */
    public function needsRehash(): bool
    {
        return password_needs_rehash($this->value, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3,
        ]);
    }

    /**
     * Obtener información del hash
     */
    public function getInfo(): array
    {
        $info = password_get_info($this->value);
        
        return [
            'algo' => $info['algo'],
            'algo_name' => $info['algoName'],
            'options' => $info['options'] ?? [],
            'is_valid' => $info['algo'] !== PASSWORD_UNKNOWN,
            'is_legacy' => $info['algo'] === PASSWORD_BCRYPT,
        ];
    }

    /**
     * Obtener algoritmo utilizado
     */
    public function getAlgorithm(): ?string
    {
        return $this->algorithm;
    }

    /**
     * Obtener costo (para bcrypt)
     */
    public function getCost(): ?int
    {
        return $this->cost;
    }

    /**
     * Verificar si es un hash válido
     */
    public function isValid(): bool
    {
        $info = $this->getInfo();
        return $info['is_valid'];
    }

    /**
     * Verificar si es un hash legacy (inseguro)
     */
    public function isLegacy(): bool
    {
        $info = $this->getInfo();
        return $info['is_legacy'];
    }

    /**
     * Generar hash temporal para reset de contraseña
     */
    public static function generateTemporary(): self
    {
        $temporary = bin2hex(random_bytes(32));
        return new self($temporary, 'temporary', null);
    }

    /**
     * Verificar si es temporal
     */
    public function isTemporary(): bool
    {
        return $this->algorithm === 'temporary';
    }

    /**
     * Verificar fuerza de contraseña
     */
    public static function getPasswordStrength(string $password): array
    {
        $score = 0;
        $feedback = [];

        // Longitud
        if (strlen($password) >= 8) {
            $score += 1;
        } else {
            $feedback[] = 'Debe tener al menos 8 caracteres';
        }

        if (strlen($password) >= 12) {
            $score += 1;
        }

        // Caracteres minúsculas
        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Debe contener al menos una letra minúscula';
        }

        // Caracteres mayúsculas
        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Debe contener al menos una letra mayúscula';
        }

        // Números
        if (preg_match('/\d/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Debe contener al menos un número';
        }

        // Caracteres especiales
        if (preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Debe contener al menos un carácter especial';
        }

        // Verificar patrones comunes
        $commonPatterns = [
            '123', 'abc', 'password', 'qwerty', 'admin'
        ];
        
        foreach ($commonPatterns as $pattern) {
            if (stripos($password, $pattern) !== false) {
                $score -= 1;
                $feedback[] = 'Evite patrones comunes como "' . $pattern . '"';
                break;
            }
        }

        // Determinar nivel
        if ($score <= 2) {
            $level = 'weak';
        } elseif ($score <= 4) {
            $level = 'medium';
        } else {
            $level = 'strong';
        }

        return [
            'score' => max(0, $score),
            'level' => $level,
            'feedback' => $feedback,
            'max_score' => 6
        ];
    }

    /**
     * Comparar con otro hash
     */
    public function equals(self $other): bool
    {
        return hash_equals($this->value, $other->value);
    }

    /**
     * Representación string (no mostrar hash completo)
     */
    public function __toString(): string
    {
        return '[PROTECTED_HASH]';
    }

    /**
     * Serialización segura para JSON
     */
    public function jsonSerialize(): string
    {
        return '[PROTECTED_HASH]';
    }
}
