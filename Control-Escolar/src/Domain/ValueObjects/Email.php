<?php
/**
 * =============================================================================
 * VALUE OBJECT EMAIL
 * Christian LMS System - Domain Layer
 * =============================================================================
 */

namespace ChristianLMS\Domain\ValueObjects;

/**
 * Value Object Email
 * 
 * Representa una dirección de correo electrónico válida.
 * Es un value object inmutable.
 */
class Email
{
    private string $value;
    private string $localPart;
    private string $domain;
    private ?string $displayName = null;

    /**
     * Constructor
     */
    private function __construct(string $value, ?string $displayName = null)
    {
        $this->value = $value;
        $this->displayName = $displayName;
        
        // Extraer local part y domain
        $parts = explode('@', $value);
        $this->localPart = $parts[0];
        $this->domain = $parts[1] ?? '';
    }

    /**
     * Crear desde string
     */
    public static function fromString(string $email): self
    {
        $email = trim($email);
        
        if (empty($email)) {
            throw new \InvalidArgumentException('El email no puede estar vacío');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Formato de email inválido');
        }

        if (strlen($email) > 254) {
            throw new \InvalidArgumentException('El email es demasiado largo');
        }

        // Verificar longitud de local part (antes del @)
        $parts = explode('@', $email);
        if (strlen($parts[0]) > 64) {
            throw new \InvalidArgumentException('La parte local del email es demasiado larga');
        }

        return new self($email);
    }

    /**
     * Crear con nombre para mostrar
     */
    public static function withDisplayName(string $email, string $displayName): self
    {
        return new self($email, $displayName);
    }

    /**
     * Obtener valor completo
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Obtener parte local (antes del @)
     */
    public function getLocalPart(): string
    {
        return $this->localPart;
    }

    /**
     * Obtener dominio (después del @)
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Obtener nombre para mostrar
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * Obtener email formateado para mostrar
     */
    public function getFormatted(): string
    {
        if ($this->displayName) {
            return "{$this->displayName} <{$this->value}>";
        }
        
        return $this->value;
    }

    /**
     * Verificar si el dominio es válido
     */
    public function isValidDomain(): bool
    {
        return checkdnsrr($this->domain, 'MX') || checkdnsrr($this->domain, 'A');
    }

    /**
     * Verificar si es un email corporativo (no gratuito)
     */
    public function isCorporate(): bool
    {
        $freeDomains = [
            'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com',
            'live.com', 'msn.com', 'aol.com', 'icloud.com',
            'yandex.com', 'mail.ru', 'protonmail.com'
        ];
        
        return !in_array(strtolower($this->domain), $freeDomains);
    }

    /**
     * Obtener proveedor de email
     */
    public function getProvider(): string
    {
        $domain = strtolower($this->domain);
        
        if (strpos($domain, 'gmail') !== false) {
            return 'Gmail';
        }
        
        if (strpos($domain, 'yahoo') !== false) {
            return 'Yahoo';
        }
        
        if (strpos($domain, 'outlook') !== false || strpos($domain, 'hotmail') !== false || strpos($domain, 'live') !== false) {
            return 'Outlook/Hotmail';
        }
        
        if (strpos($domain, 'outlook') !== false) {
            return 'Outlook';
        }
        
        if (strpos($domain, 'icloud') !== false) {
            return 'iCloud';
        }
        
        return 'Otro';
    }

    /**
     * Verificar si es igual a otro email
     */
    public function equals(self $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }

    /**
     * Verificar si contiene el mismo dominio
     */
    public function sameDomain(self $other): bool
    {
        return strtolower($this->domain) === strtolower($other->domain);
    }

    /**
     * Verificar si es una cuenta de estudiante (dominio educativo)
     */
    public function isEducational(): bool
    {
        $educationalDomains = ['.edu', '.ac.', '.sch.', 'universidad', 'colegio', 'escuela'];
        
        foreach ($educationalDomains as $eduDomain) {
            if (strpos(strtolower($this->domain), $eduDomain) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtener hash para búsquedas
     */
    public function getHash(): string
    {
        return hash('sha256', strtolower($this->value));
    }

    /**
     * Verificar si es un email temporal
     */
    public function isTemporary(): bool
    {
        $temporaryDomains = [
            '10minutemail.com', 'guerrillamail.com', 'mailinator.com',
            'temp-mail.org', 'throwaway.email', 'guerrillamailblock.com'
        ];
        
        return in_array(strtolower($this->domain), $temporaryDomains);
    }

    /**
     * Obtener información de validación
     */
    public function getValidationInfo(): array
    {
        return [
            'is_valid' => filter_var($this->value, FILTER_VALIDATE_EMAIL) !== false,
            'domain_exists' => $this->isValidDomain(),
            'is_corporate' => $this->isCorporate(),
            'is_educational' => $this->isEducational(),
            'is_temporary' => $this->isTemporary(),
            'provider' => $this->getProvider(),
            'local_part_length' => strlen($this->localPart),
            'domain_length' => strlen($this->domain)
        ];
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
