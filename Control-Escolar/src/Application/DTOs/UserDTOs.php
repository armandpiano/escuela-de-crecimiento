<?php
/**
 * =============================================================================
 * DTOS - DATA TRANSFER OBJECTS
 * Christian LMS System - Application Layer
 * =============================================================================
 */

namespace ChristianLMS\Application\DTOs;

use ChristianLMS\Domain\Entities\User;
use ChristianLMS\Domain\ValueObjects\{
    Email,
    UserId,
    UserStatus,
    UserGender
};

/**
 * DTO para crear usuario
 */
class CreateUserDTO
{
    /** @var string */
    public $firstName;
    /** @var string */
    public $lastName;
    /** @var string */
    public $email;
    /** @var string|null */
    public $password= null;
    /** @var string|null */
    public $gender= null;
    /** @var string|null */
    public $phone= null;
    /** @var string|null */
    public $address= null;
    /** @var string|null */
    public $profilePhoto= null;
    /** @var array */
    public $roles= [];
    /** @var string|null */
    public $status= null;
    /** @var array */
    public $metadata= [];
    /** @var bool */
    public $sendVerificationEmail= true;
    /** @var bool */
    public $autoActivate= false;

    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        ?string $password = null,
        array $options = []
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;

        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Crear DTO desde array
     */
    public static function fromArray(array $data): self
    {
        $required = ['firstName', 'lastName', 'email'];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException("Campo requerido faltante: {$field}");
            }
        }

        return new self(
            $data['firstName'],
            $data['lastName'],
            $data['email'],
            $data['password'] ?? null,
            $data
        );
    }

    /**
     * Validar DTO
     */
    public function validate(): array
    {
        $errors = [];

        // Validar nombre
        if (empty(trim($this->firstName))) {
            $errors['firstName'] = 'El nombre es requerido';
        } elseif (strlen($this->firstName) > 50) {
            $errors['firstName'] = 'El nombre no puede exceder 50 caracteres';
        }

        // Validar apellido
        if (empty(trim($this->lastName))) {
            $errors['lastName'] = 'El apellido es requerido';
        } elseif (strlen($this->lastName) > 50) {
            $errors['lastName'] = 'El apellido no puede exceder 50 caracteres';
        }

        // Validar email
        try {
            new Email($this->email);
        } catch (\InvalidArgumentException $e) {
            $errors['email'] = 'Email inválido';
        }

        // Validar contraseña si se proporciona
        if ($this->password) {
            if (strlen($this->password) < 8) {
                $errors['password'] = 'La contraseña debe tener al menos 8 caracteres';
            }
        }

        // Validar género
        if ($this->gender && !UserGender::isValid($this->gender)) {
            $errors['gender'] = 'Género inválido';
        }

        // Validar estado
        if ($this->status && !UserStatus::isValid($this->status)) {
            $errors['status'] = 'Estado inválido';
        }

        // Validar roles
        if ($this->roles) {
            $validRoles = ['admin', 'teacher', 'student', 'professor', 'alumno'];
            foreach ($this->roles as $role) {
                if (!in_array($role, $validRoles)) {
                    $errors['roles'][] = "Rol inválido: {$role}";
                }
            }
        }

        return $errors;
    }

    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'password' => $this->password,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'address' => $this->address,
            'profilePhoto' => $this->profilePhoto,
            'roles' => $this->roles,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'sendVerificationEmail' => $this->sendVerificationEmail,
            'autoActivate' => $this->autoActivate,
        ];
    }
}

/**
 * DTO para respuesta de usuario
 */
class UserResponseDTO
{
    /** @var UserId */
    public $id;
    /** @var string */
    public $firstName;
    /** @var string */
    public $lastName;
    /** @var string */
    public $fullName;
    /** @var string */
    public $email;
    /** @var UserStatus */
    public $status;
    /** @var UserGender */
    public $gender;
    /** @var string|null */
    public $phone= null;
    /** @var string|null */
    public $address= null;
    /** @var string|null */
    public $profilePhoto= null;
    /** @var array */
    public $roles= [];
    /** @var array */
    public $metadata= [];
    /** @var string|null */
    public $lastLoginAt= null;
    /** @var int */
    public $loginAttempts= 0;
    /** @var string|null */
    public $lockedUntil= null;
    /** @var string|null */
    public $createdAt= null;
    /** @var string|null */
    public $updatedAt= null;

    /**
     * Crear DTO desde entidad User
     */
    public static function fromUser(User $user): self
    {
        $dto = new self();
        $dto->id = $user->getId();
        $dto->firstName = $user->getFirstName();
        $dto->lastName = $user->getLastName();
        $dto->fullName = $user->getFullName();
        $dto->email = $user->getEmailString();
        $dto->status = $user->getStatus();
        $dto->gender = $user->getGender();
        $dto->phone = $user->getPhone();
        $dto->address = $user->getAddress();
        $dto->profilePhoto = $user->getProfilePhoto();
        $dto->roles = $user->getRoles();
        $dto->metadata = $user->getMetadata();
        $dto->lastLoginAt = $user->getLastLoginAt();
        $dto->loginAttempts = $user->getLoginAttempts();
        $dto->lockedUntil = $user->getLockedUntil();
        $dto->createdAt = $user->getCreatedAt();
        $dto->updatedAt = $user->getUpdatedAt();

        return $dto;
    }

    /**
     * Crear DTO desde array
     */
    public static function fromArray(array $data): self
    {
        $dto = new self();
        
        if (isset($data['id'])) {
            $dto->id = UserId::fromString($data['id']);
        }
        
        $dto->firstName = $data['firstName'] ?? '';
        $dto->lastName = $data['lastName'] ?? '';
        $dto->fullName = trim(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? ''));
        $dto->email = $data['email'] ?? '';
        
        if (isset($data['status'])) {
            $dto->status = UserStatus::fromString($data['status']);
        }
        
        if (isset($data['gender'])) {
            $dto->gender = UserGender::fromString($data['gender']);
        }
        
        $dto->phone = $data['phone'] ?? null;
        $dto->address = $data['address'] ?? null;
        $dto->profilePhoto = $data['profilePhoto'] ?? null;
        $dto->roles = $data['roles'] ?? [];
        $dto->metadata = $data['metadata'] ?? [];
        $dto->lastLoginAt = $data['lastLoginAt'] ?? null;
        $dto->loginAttempts = $data['loginAttempts'] ?? 0;
        $dto->lockedUntil = $data['lockedUntil'] ?? null;
        $dto->createdAt = $data['createdAt'] ?? null;
        $dto->updatedAt = $data['updatedAt'] ?? null;

        return $dto;
    }

    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'fullName' => $this->fullName,
            'email' => $this->email,
            'status' => $this->status->getValue(),
            'gender' => $this->gender->getValue(),
            'phone' => $this->phone,
            'address' => $this->address,
            'profilePhoto' => $this->profilePhoto,
            'roles' => $this->roles,
            'metadata' => $this->metadata,
            'lastLoginAt' => $this->lastLoginAt,
            'loginAttempts' => $this->loginAttempts,
            'lockedUntil' => $this->lockedUntil,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    /**
     * Obtener información pública (sin datos sensibles)
     */
    public function toPublicArray(): array
    {
        $publicData = $this->toArray();
        
        // Remover datos sensibles
        unset(
            $publicData['loginAttempts'],
            $publicData['lockedUntil'],
            $publicData['metadata']
        );

        // Agregar información calculada
        $publicData['isActive'] = $this->status->isActive();
        $publicData['isBlocked'] = $this->status->isBlocked();
        $publicData['isAdmin'] = in_array('admin', $this->roles);
        $publicData['isTeacher'] = in_array('teacher', $this->roles) || in_array('professor', $this->roles);
        $publicData['isStudent'] = in_array('student', $this->roles) || in_array('alumno', $this->roles);
        $publicData['canAccessControlEscolar'] = $this->status->canAccess();

        return $publicData;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    /**
     * Obtener roles principales
     */
    public function getPrimaryRole(): ?string
    {
        $priorityRoles = ['admin', 'teacher', 'professor', 'student', 'alumno'];
        
        foreach ($priorityRoles as $role) {
            if ($this->hasRole($role)) {
                return $role;
            }
        }
        
        return null;
    }

    /**
     * Serialización para JSON
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

/**
 * DTO para actualizar usuario
 */
class UpdateUserDTO
{
    /** @var string|null */
    public $firstName= null;
    /** @var string|null */
    public $lastName= null;
    /** @var string|null */
    public $email= null;
    /** @var string|null */
    public $gender= null;
    /** @var string|null */
    public $phone= null;
    /** @var string|null */
    public $address= null;
    /** @var string|null */
    public $profilePhoto= null;
    /** @var array */
    public $roles= [];
    /** @var string|null */
    public $status= null;
    /** @var array */
    public $metadata= [];

    /**
     * Crear DTO desde array
     */
    public static function fromArray(array $data): self
    {
        $dto = new self();
        
        $allowedFields = [
            'firstName', 'lastName', 'email', 'gender', 'phone', 'address',
            'profilePhoto', 'roles', 'status', 'metadata'
        ];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $dto->$field = $data[$field];
            }
        }

        return $dto;
    }

    /**
     * Validar DTO
     */
    public function validate(): array
    {
        $errors = [];

        // Validar nombre si se proporciona
        if ($this->firstName !== null) {
            if (empty(trim($this->firstName))) {
                $errors['firstName'] = 'El nombre no puede estar vacío';
            } elseif (strlen($this->firstName) > 50) {
                $errors['firstName'] = 'El nombre no puede exceder 50 caracteres';
            }
        }

        // Validar apellido si se proporciona
        if ($this->lastName !== null) {
            if (empty(trim($this->lastName))) {
                $errors['lastName'] = 'El apellido no puede estar vacío';
            } elseif (strlen($this->lastName) > 50) {
                $errors['lastName'] = 'El apellido no puede exceder 50 caracteres';
            }
        }

        // Validar email si se proporciona
        if ($this->email !== null) {
            try {
                new Email($this->email);
            } catch (\InvalidArgumentException $e) {
                $errors['email'] = 'Email inválido';
            }
        }

        // Validar género si se proporciona
        if ($this->gender !== null && !UserGender::isValid($this->gender)) {
            $errors['gender'] = 'Género inválido';
        }

        // Validar estado si se proporciona
        if ($this->status !== null && !UserStatus::isValid($this->status)) {
            $errors['status'] = 'Estado inválido';
        }

        // Validar roles si se proporcionan
        if ($this->roles) {
            $validRoles = ['admin', 'teacher', 'student', 'professor', 'alumno'];
            foreach ($this->roles as $role) {
                if (!in_array($role, $validRoles)) {
                    $errors['roles'][] = "Rol inválido: {$role}";
                }
            }
        }

        return $errors;
    }

    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        $data = [];
        
        foreach (get_object_vars($this) as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
