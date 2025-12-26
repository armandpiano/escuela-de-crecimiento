<?php
/**
 * =============================================================================
 * USE CASE: REGISTRAR USUARIO
 * Christian LMS System - Application Layer
 * =============================================================================
 */

namespace ChristianLMS\Application\UseCases;

use ChristianLMS\Application\DTOs\{
    CreateUserDTO,
    UserResponseDTO
};
use ChristianLMS\Application\Services\{
    PasswordService,
    EmailService,
    ValidationService
};
use ChristianLMS\Infrastructure\Ports\UserRepositoryInterface;
use ChristianLMS\Infrastructure\Events\UserRegisteredEvent;
use ChristianLMS\Domain\Entities\User;
use ChristianLMS\Domain\ValueObjects\{
    Email,
    PasswordHash,
    UserId,
    UserStatus,
    UserGender
};

/**
 * Use Case: Registrar Usuario
 * 
 * Maneja el proceso completo de registro de un nuevo usuario
 * en el sistema.
 */
class RegisterUserUseCase
{
    /** @var UserRepositoryInterface */
    private $userRepository;
    /** @var PasswordService */
    private $passwordService;
    /** @var EmailService */
    private $emailService;
    /** @var ValidationService */
    private $validationService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PasswordService $passwordService,
        EmailService $emailService,
        ValidationService $validationService
    ) {
        $this->userRepository = $userRepository;
        $this->passwordService = $passwordService;
        $this->emailService = $emailService;
        $this->validationService = $validationService;
    }

    /**
     * Ejecutar el caso de uso
     */
    public function execute(CreateUserDTO $userData): UserResponseDTO
    {
        // 1. Validar datos de entrada
        $this->validateInput($userData);

        // 2. Verificar que el email no exista
        $this->checkEmailNotExists($userData->email);

        // 3. Crear el usuario
        $user = $this->createUser($userData);

        // 4. Guardar en repositorio
        $savedUser = $this->userRepository->save($user);

        // 5. Enviar email de verificación si es necesario
        if ($userData->sendVerificationEmail) {
            $this->sendVerificationEmail($savedUser);
        }

        // 6. Disparar evento
        $this->dispatchEvent($savedUser);

        // 7. Retornar respuesta
        return UserResponseDTO::fromUser($savedUser);
    }

    /**
     * Validar datos de entrada
     */
    private function validateInput(CreateUserDTO $data): void
    {
        // Validar nombre
        if (empty(trim($data->firstName))) {
            throw new \InvalidArgumentException('El nombre es requerido');
        }

        if (strlen($data->firstName) > 50) {
            throw new \InvalidArgumentException('El nombre no puede exceder 50 caracteres');
        }

        // Validar apellido
        if (empty(trim($data->lastName))) {
            throw new \InvalidArgumentException('El apellido es requerido');
        }

        if (strlen($data->lastName) > 50) {
            throw new \InvalidArgumentException('El apellido no puede exceder 50 caracteres');
        }

        // Validar email
        try {
            new Email($data->email);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException('Email inválido: ' . $e->getMessage());
        }

        // Validar contraseña si se proporciona
        if ($data->password) {
            $passwordStrength = $this->passwordService->checkStrength($data->password);
            if ($passwordStrength['level'] !== 'strong') {
                throw new \InvalidArgumentException(
                    'La contraseña debe ser más fuerte. Errores: ' . 
                    implode(', ', $passwordStrength['feedback'])
                );
            }
        }

        // Validar género si se proporciona
        if ($data->gender) {
            UserGender::fromString($data->gender);
        }

        // Validar teléfono si se proporciona
        if ($data->phone && !$this->validationService->isValidPhone($data->phone)) {
            throw new \InvalidArgumentException('Formato de teléfono inválido');
        }

        // Validar roles si se proporcionan
        if ($data->roles) {
            $this->validateRoles($data->roles);
        }
    }

    /**
     * Verificar que el email no exista
     */
    private function checkEmailNotExists(string $email): void
    {
        $emailObject = new Email($email);
        
        if ($this->userRepository->existsByEmail($emailObject)) {
            throw new \InvalidArgumentException('Ya existe un usuario con este email');
        }
    }

    /**
     * Crear entidad de usuario
     */
    private function createUser(CreateUserDTO $data): User
    {
        // Crear usuario
        $user = User::create(
            $data->firstName,
            $data->lastName,
            $data->email,
            $data->password
        );

        // Configurar propiedades adicionales
        if ($data->gender) {
            $user->setGender(UserGender::fromString($data->gender));
        }

        if ($data->phone) {
            $user->setPhone($data->phone);
        }

        if ($data->address) {
            $user->setAddress($data->address);
        }

        // Asignar roles
        if ($data->roles) {
            foreach ($data->roles as $role) {
                $user->addRole($role);
            }
        }

        // Configurar estado inicial
        if ($data->status) {
            $user->setStatus(UserStatus::fromString($data->status));
        } else {
            // Por defecto, usuarios se crean como pendientes de activación
            $user->setStatus(UserStatus::pending());
        }

        // Agregar metadata adicional
        if ($data->metadata) {
            $user->setMetadata($data->metadata);
        }

        return $user;
    }

    /**
     * Enviar email de verificación
     */
    private function sendVerificationEmail(User $user): void
    {
        try {
            $this->emailService->sendVerificationEmail($user);
        } catch (\Exception $e) {
            // Log error but don't fail registration
            error_log("Error enviando email de verificación: " . $e->getMessage());
        }
    }

    /**
     * Disparar evento de registro
     */
    private function dispatchEvent(User $user): void
    {
        $event = new UserRegisteredEvent($user);
        $event->dispatch();
    }

    /**
     * Validar roles
     */
    private function validateRoles(array $roles): void
    {
        $validRoles = ['admin', 'teacher', 'student', 'professor', 'alumno'];
        
        foreach ($roles as $role) {
            if (!in_array($role, $validRoles)) {
                throw new \InvalidArgumentException("Rol inválido: {$role}");
            }
        }
    }
}

/**
 * Use Case: Autenticar Usuario
 */
class AuthenticateUserUseCase
{
    /** @var UserRepositoryInterface */
    private $userRepository;
    /** @var PasswordService */
    private $passwordService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PasswordService $passwordService
    ) {
        $this->userRepository = $userRepository;
        $this->passwordService = $passwordService;
    }

    /**
     * Autenticar usuario con email y contraseña
     */
    public function execute(string $email, string $password): UserResponseDTO
    {
        // Buscar usuario por email
        $emailObject = new Email($email);
        $user = $this->userRepository->findByEmail($emailObject);

        if (!$user) {
            throw new \InvalidArgumentException('Credenciales inválidas');
        }

        // Verificar estado del usuario
        if (!$user->canLogin()) {
            throw new \InvalidArgumentException('Usuario no puede iniciar sesión');
        }

        // Verificar si está bloqueado
        if ($user->isLocked()) {
            throw new \InvalidArgumentException('Usuario bloqueado temporalmente');
        }

        // Verificar contraseña
        if (!$user->verifyPassword($password)) {
            // Registrar intento fallido
            $user->recordLoginAttempt(false);
            $this->userRepository->save($user);
            
            throw new \InvalidArgumentException('Credenciales inválidas');
        }

        // Verificar si la contraseña necesita rehash
        if ($user->getPasswordHash()->needsRehash()) {
            $user->changePassword($password);
            $this->userRepository->save($user);
        }

        // Registrar login exitoso
        $user->recordLoginAttempt(true);
        $this->userRepository->save($user);

        return UserResponseDTO::fromUser($user);
    }

    /**
     * Autenticar usuario por ID y token
     */
    public function authenticateByToken(UserId $userId, string $token): UserResponseDTO
    {
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            throw new \InvalidArgumentException('Token inválido');
        }

        if (!$user->isActive()) {
            throw new \InvalidArgumentException('Usuario inactivo');
        }

        // Verificar token (implementar según necesidades)
        // if (!$this->validateToken($user, $token)) {
        //     throw new \InvalidArgumentException('Token inválido');
        // }

        // Registrar login
        $user->recordLoginAttempt(true);
        $this->userRepository->save($user);

        return UserResponseDTO::fromUser($user);
    }
}
