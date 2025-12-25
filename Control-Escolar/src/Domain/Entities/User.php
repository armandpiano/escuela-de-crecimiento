<?php
/**
 * =============================================================================
 * ENTIDAD USER - DOMAIN LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\Entities;

use ChristianLMS\Domain\ValueObjects\{
    Email,
    PasswordHash,
    UserId,
    UserStatus,
    UserGender
};
use ChristianLMS\Domain\Entities\Traits\Timestampable;
use ChristianLMS\Domain\Entities\Traits\SoftDeleteable;

/**
 * Entidad User
 * 
 * Representa un usuario del sistema (administrador, profesor, alumno)
 * Cumple con los principios de DDD y arquitectura hexagonal.
 */
class User
{
    use Timestampable, SoftDeleteable;

    private UserId $id;
    private ?string $matricula = null;
    private string $firstName;
    private string $lastName;
    private Email $email;
    private ?PasswordHash $passwordHash;
    private UserStatus $status;
    private UserGender $gender;
    private ?string $phone;
    private ?string $address;
    private ?string $profilePhoto;
    private array $roles = [];
    private array $metadata = [];
    private ?string $lastLoginAt = null;
    private int $loginAttempts = 0;
    private ?string $lockedUntil = null;

    /**
     * Constructor
     */
    public function __construct(
        UserId $id,
        string $firstName,
        string $lastName,
        Email $email,
        ?PasswordHash $passwordHash = null
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->status = UserStatus::ACTIVE;
        $this->gender = UserGender::UNSPECIFIED;
    }

    /**
     * Crear nuevo usuario
     */
    public static function create(
        string $firstName,
        string $lastName,
        string $email,
        ?string $password = null
    ): self {
        $user = new self(
            UserId::generate(),
            $firstName,
            $lastName,
            new Email($email),
            $password ? new PasswordHash($password) : null
        );
        
        return $user;
    }

    // Getters

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getMatricula(): ?string
    {
        return $this->matricula;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getEmailString(): string
    {
        return $this->email->getValue();
    }

    public function getPasswordHash(): ?PasswordHash
    {
        return $this->passwordHash;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function getGender(): UserGender
    {
        return $this->gender;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getProfilePhoto(): ?string
    {
        return $this->profilePhoto;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getMetadataValue(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    public function getLastLoginAt(): ?string
    {
        return $this->lastLoginAt;
    }

    public function getLoginAttempts(): int
    {
        return $this->loginAttempts;
    }

    public function getLockedUntil(): ?string
    {
        return $this->lockedUntil;
    }

    // Setters y métodos de comportamiento

    public function setFirstName(string $firstName): self
    {
        if (empty(trim($firstName))) {
            throw new \InvalidArgumentException('El nombre no puede estar vacío');
        }
        $this->firstName = $firstName;
        return $this;
    }

    public function setMatricula(?string $matricula): self
    {
        $this->matricula = $matricula;
        return $this;
    }

    public function setLastName(string $lastName): self
    {
        if (empty(trim($lastName))) {
            throw new \InvalidArgumentException('El apellido no puede estar vacío');
        }
        $this->lastName = $lastName;
        return $this;
    }

    public function setEmail(Email $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setPassword(PasswordHash $passwordHash): self
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    public function changePassword(string $newPassword): self
    {
        $this->passwordHash = new PasswordHash($newPassword);
        return $this;
    }

    public function setStatus(UserStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setGender(UserGender $gender): self
    {
        $this->gender = $gender;
        return $this;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function setProfilePhoto(?string $profilePhoto): self
    {
        $this->profilePhoto = $profilePhoto;
        return $this;
    }

    public function addRole(string $role): self
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    public function removeRole(string $role): self
    {
        $this->roles = array_filter($this->roles, fn($r) => $r !== $role);
        return $this;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = array_unique($roles);
        return $this;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function setMetadataValue(string $key, $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    // Comportamientos del dominio

    public function activate(): self
    {
        $this->status = UserStatus::ACTIVE;
        return $this;
    }

    public function deactivate(): self
    {
        $this->status = UserStatus::INACTIVE;
        return $this;
    }

    public function suspend(): self
    {
        $this->status = UserStatus::SUSPENDED;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    public function isLocked(): bool
    {
        if ($this->lockedUntil === null) {
            return false;
        }
        
        return strtotime($this->lockedUntil) > time();
    }

    public function lock(int $minutes = 30): self
    {
        $this->lockedUntil = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));
        return $this;
    }

    public function unlock(): self
    {
        $this->lockedUntil = null;
        $this->loginAttempts = 0;
        return $this;
    }

    public function recordLoginAttempt(bool $successful = true): self
    {
        if ($successful) {
            $this->loginAttempts = 0;
            $this->lastLoginAt = date('Y-m-d H:i:s');
            $this->unlock();
        } else {
            $this->loginAttempts++;
            
            // Bloquear después de 5 intentos fallidos
            if ($this->loginAttempts >= 5) {
                $this->lock(30);
            }
        }
        
        return $this;
    }

    public function hasPassword(): bool
    {
        return $this->passwordHash !== null;
    }

    public function verifyPassword(string $password): bool
    {
        if (!$this->hasPassword()) {
            return false;
        }
        
        return $this->passwordHash->verify($password);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('teacher') || $this->hasRole('professor');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student') || $this->hasRole('alumno');
    }

    public function canAccessControlEscolar(): bool
    {
        return $this->isAdmin() || $this->isTeacher() || $this->isStudent();
    }

    /**
     * Convertir a array para transporte
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'matricula' => $this->matricula,
            'name' => $this->getFullName(),
            'email' => $this->email->getValue(),
            'password' => $this->passwordHash?->getValue(),
            'role' => $this->roles[0] ?? 'student',
            'status' => $this->status->getValue(),
        ];
    }

    /**
     * Comparar igualdad
     */
    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }

    /**
     * Representación string del objeto
     */
    public function __toString(): string
    {
        return $this->getFullName() . ' (' . $this->email->getValue() . ')';
    }
}
