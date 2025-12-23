<?php
/**
 * =============================================================================
 * USE CASE: USER LOGIN
 * Christian LMS System - Application Layer
 * =============================================================================
 */

namespace ChristianLMS\Application\UseCases\Auth;

use ChristianLMS\Application\Services\ApplicationServices;
use ChristianLMS\Infrastructure\Mail\EmailService;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;
use ChristianLMS\Domain\ValueObjects\Email;

/**
 * Use Case: User Login
 * 
 * Caso de uso para autenticación de usuarios
 */
class LoginUserUseCase
{
    private ApplicationServices $applicationServices;
    private EmailService $emailService;

    public function __construct(
        ApplicationServices $applicationServices,
        EmailService $emailService
    ) {
        $this->applicationServices = $applicationServices;
        $this->emailService = $emailService;
    }

    /**
     * Ejecutar caso de uso
     */
    public function execute(LoginUserRequest $request): LoginUserResponse
    {
        try {
            // Validar datos de entrada
            $this->validateRequest($request);

            // Obtener repositorio de usuarios
            $userRepository = $this->applicationServices->getUserRepository();

            // Buscar usuario por email
            $email = new Email($request->getEmail());
            $user = $userRepository->findByEmailCaseInsensitive($email);

            if (!$user) {
                return new LoginUserResponse(false, 'Credenciales inválidas');
            }

            // Verificar si el usuario está bloqueado
            if ($user->isLocked()) {
                return new LoginUserResponse(false, 'Usuario bloqueado temporalmente');
            }

            // Verificar si el usuario está activo
            if (!$user->isActive()) {
                return new LoginUserResponse(false, 'Cuenta desactivada');
            }

            // Verificar contraseña
            if (!$user->hasPassword() || !$user->verifyPassword($request->getPassword())) {
                $user->recordLoginAttempt(false);
                $userRepository->save($user);
                
                return new LoginUserResponse(false, 'Credenciales inválidas');
            }

            // Login exitoso
            $user->recordLoginAttempt(true);
            $userRepository->save($user);

            // Enviar notificación de login (opcional)
            try {
                $this->sendLoginNotification($user, $request->getIpAddress());
            } catch (\Exception $e) {
                // Log error but don't fail the operation
                error_log('Error sending login notification: ' . $e->getMessage());
            }

            return new LoginUserResponse(true, 'Login exitoso', $user);

        } catch (\InvalidArgumentException $e) {
            return new LoginUserResponse(false, 'Datos inválidos: ' . $e->getMessage());
        } catch (DatabaseException $e) {
            return new LoginUserResponse(false, 'Error de base de datos: ' . $e->getMessage());
        } catch (\Exception $e) {
            return new LoginUserResponse(false, 'Error interno del servidor');
        }
    }

    /**
     * Validar datos de la petición
     */
    private function validateRequest(LoginUserRequest $request): void
    {
        if (empty(trim($request->getEmail()))) {
            throw new \InvalidArgumentException('El email es requerido');
        }

        if (!filter_var($request->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('El formato del email no es válido');
        }

        if (empty(trim($request->getPassword()))) {
            throw new \InvalidArgumentException('La contraseña es requerida');
        }

        if (strlen($request->getPassword()) < 6) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos 6 caracteres');
        }
    }

    /**
     * Enviar notificación de login
     */
    private function sendLoginNotification($user, ?string $ipAddress): void
    {
        $subject = 'Nuevo inicio de sesión';
        $message = sprintf(
            "Hola %s,\n\n" .
            "Se ha iniciado sesión en tu cuenta:\n\n" .
            "Fecha: %s\n" .
            "IP: %s\n\n" .
            "Si no fuiste tú, por favor contacta con el soporte.",
            $user->getFirstName(),
            date('d/m/Y H:i:s'),
            $ipAddress ?? 'Desconocida'
        );

        $this->emailService->sendEmail(
            $user->getEmailString(),
            $subject,
            $message
        );
    }
}

/**
 * Request DTO para LoginUserUseCase
 */
class LoginUserRequest
{
    private string $email;
    private string $password;
    private bool $remember;
    private ?string $ipAddress;

    public function __construct(string $email, string $password, bool $remember = false, ?string $ipAddress = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->remember = $remember;
        $this->ipAddress = $ipAddress;
    }

    // Getters
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function isRemember(): bool { return $this->remember; }
    public function getIpAddress(): ?string { return $this->ipAddress; }

    // Setters
    public function setEmail(string $email): self { $this->email = $email; return $this; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }
    public function setRemember(bool $remember): self { $this->remember = $remember; return $this; }
    public function setIpAddress(?string $ipAddress): self { $this->ipAddress = $ipAddress; return $this; }
}

/**
 * Response DTO para LoginUserUseCase
 */
class LoginUserResponse
{
    private bool $success;
    private string $message;
    private ?object $user;
    private ?string $redirectUrl;

    public function __construct(bool $success, string $message, ?object $user = null, ?string $redirectUrl = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->user = $user;
        $this->redirectUrl = $redirectUrl;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getUser(): ?object
    {
        return $this->user;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'user' => $this->user ? $this->user->toArray() : null,
            'redirect_url' => $this->redirectUrl
        ];
    }
}
