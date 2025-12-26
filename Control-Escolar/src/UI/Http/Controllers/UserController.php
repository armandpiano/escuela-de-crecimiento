<?php
/**
 * =============================================================================
 * CONTROLADOR DE USUARIOS - UI LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\UI\Http\Controllers;

use ChristianLMS\Application\UseCases\Auth\RegisterUserUseCase;
use ChristianLMS\Application\UseCases\Auth\AuthenticateUserUseCase;
use ChristianLMS\Application\DTOs\{
    CreateUserDTO,
    UserResponseDTO
};
use ChristianLMS\Infrastructure\Ports\UserRepositoryInterface;

/**
 * Controlador de Usuarios
 * 
 * Maneja las peticiones HTTP relacionadas con usuarios
 * actúa como una capa delgada entre la web y la aplicación.
 */
class UserController
{
    /** @var RegisterUserUseCase */
    private $registerUserUseCase;
    /** @var AuthenticateUserUseCase */
    private $authenticateUserUseCase;
    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        RegisterUserUseCase $registerUserUseCase,
        AuthenticateUserUseCase $authenticateUserUseCase,
        UserRepositoryInterface $userRepository
    ) {
        $this->registerUserUseCase = $registerUserUseCase;
        $this->authenticateUserUseCase = $authenticateUserUseCase;
        $this->userRepository = $userRepository;
    }

    /**
     * Mostrar formulario de registro
     */
    public function showRegistrationForm(): string
    {
        $roles = [
            'student' => 'Estudiante',
            'teacher' => 'Profesor',
            'admin' => 'Administrador'
        ];

        $genders = [
            'male' => 'Masculino',
            'female' => 'Femenino',
            'non_binary' => 'No binario',
            'prefer_not_to_say' => 'Prefiero no decir'
        ];

        return $this->renderView('auth.register', [
            'roles' => $roles,
            'genders' => $genders,
            'title' => 'Registro de Usuario'
        ]);
    }

    /**
     * Procesar registro de usuario
     */
    public function register(): string
    {
        try {
            // Obtener datos del formulario
            $userData = CreateUserDTO::fromArray($_POST);

            // Validar DTO
            $validationErrors = $userData->validate();
            if (!empty($validationErrors)) {
                return $this->renderView('auth.register', [
                    'errors' => $validationErrors,
                    'old' => $_POST,
                    'roles' => ['student' => 'Estudiante', 'teacher' => 'Profesor'],
                    'genders' => ['male' => 'Masculino', 'female' => 'Femenino'],
                    'title' => 'Registro de Usuario'
                ]);
            }

            // Ejecutar caso de uso
            $userResponse = $this->registerUserUseCase->execute($userData);

            // Establecer sesión si es necesario
            $_SESSION['user_id'] = $userResponse->id->getValue();
            $_SESSION['user_email'] = $userResponse->email;

            // Redirigir o mostrar mensaje de éxito
            return $this->renderView('auth.register_success', [
                'user' => $userResponse,
                'message' => 'Usuario registrado exitosamente. Revisa tu email para activar tu cuenta.'
            ]);

        } catch (\InvalidArgumentException $e) {
            return $this->renderView('auth.register', [
                'errors' => ['general' => $e->getMessage()],
                'old' => $_POST,
                'roles' => ['student' => 'Estudiante', 'teacher' => 'Profesor'],
                'genders' => ['male' => 'Masculino', 'female' => 'Femenino'],
                'title' => 'Registro de Usuario'
            ]);
        } catch (\Exception $e) {
            error_log("Error en registro de usuario: " . $e->getMessage());
            
            return $this->renderView('auth.register', [
                'errors' => ['general' => 'Error interno del servidor. Intenta nuevamente.'],
                'old' => $_POST,
                'roles' => ['student' => 'Estudiante', 'teacher' => 'Profesor'],
                'genders' => ['male' => 'Masculino', 'female' => 'Femenino'],
                'title' => 'Registro de Usuario'
            ]);
        }
    }

    /**
     * Mostrar formulario de login
     */
    public function showLoginForm(): string
    {
        return $this->renderView('auth.login', [
            'title' => 'Iniciar Sesión'
        ]);
    }

    /**
     * Procesar login de usuario
     */
    public function login(): string
    {
        try {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                return $this->renderView('auth.login', [
                    'errors' => ['general' => 'Email y contraseña son requeridos'],
                    'old' => $_POST,
                    'title' => 'Iniciar Sesión'
                ]);
            }

            // Autenticar usuario
            $userResponse = $this->authenticateUserUseCase->execute($email, $password);

            // Establecer sesión
            $_SESSION['user_id'] = $userResponse->id->getValue();
            $_SESSION['user_email'] = $userResponse->email;
            $_SESSION['user_name'] = $userResponse->fullName;
            $_SESSION['user_roles'] = $userResponse->roles;

            // Redirigir según rol
            if (in_array('admin', $userResponse->roles)) {
                return $this->redirect('/admin/dashboard');
            } elseif (in_array('teacher', $userResponse->roles) || in_array('professor', $userResponse->roles)) {
                return $this->redirect('/teacher/dashboard');
            } else {
                return $this->redirect('/student/dashboard');
            }

        } catch (\InvalidArgumentException $e) {
            return $this->renderView('auth.login', [
                'errors' => ['general' => $e->getMessage()],
                'old' => $_POST,
                'title' => 'Iniciar Sesión'
            ]);
        } catch (\Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            
            return $this->renderView('auth.login', [
                'errors' => ['general' => 'Error interno del servidor. Intenta nuevamente.'],
                'old' => $_POST,
                'title' => 'Iniciar Sesión'
            ]);
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout(): string
    {
        // Limpiar sesión
        session_destroy();
        
        // Redirigir a login
        return $this->redirect('/login');
    }

    /**
     * Mostrar perfil de usuario
     */
    public function showProfile(): string
    {
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            return $this->redirect('/login');
        }

        try {
            // Obtener usuario desde repositorio
            $userId = \ChristianLMS\Domain\ValueObjects\UserId::fromString($_SESSION['user_id']);
            $user = $this->userRepository->findById($userId);

            if (!$user) {
                return $this->redirect('/login');
            }

            $userResponse = UserResponseDTO::fromUser($user);

            return $this->renderView('user.profile', [
                'user' => $userResponse,
                'title' => 'Mi Perfil'
            ]);

        } catch (\Exception $e) {
            error_log("Error mostrando perfil: " . $e->getMessage());
            
            return $this->renderView('user.profile', [
                'error' => 'Error cargando perfil de usuario',
                'title' => 'Mi Perfil'
            ]);
        }
    }

    /**
     * API: Obtener usuarios (JSON)
     */
    public function apiIndex(): string
    {
        try {
            // Verificar autenticación y permisos
            if (!$this->isAuthenticated() || !$this->hasRole('admin')) {
                http_response_code(403);
                return json_encode(['error' => 'No autorizado']);
            }

            // Obtener parámetros de paginación
            $page = (int) ($_GET['page'] ?? 1);
            $perPage = (int) ($_GET['per_page'] ?? 20);
            $search = $_GET['search'] ?? '';

            // Buscar usuarios
            $criteria = [];
            if (!empty($search)) {
                $criteria['search'] = $search;
            }

            $users = $this->userRepository->search($criteria, $page, $perPage);

            // Convertir a DTOs
            $userResponses = array_map(function($user) {
                return UserResponseDTO::fromUser($user)->toPublicArray();
            }, $users);

            return json_encode([
                'data' => $userResponses,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => count($users)
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Error en API users index: " . $e->getMessage());
            http_response_code(500);
            return json_encode(['error' => 'Error interno del servidor']);
        }
    }

    /**
     * API: Obtener usuario por ID (JSON)
     */
    public function apiShow(string $id): string
    {
        try {
            // Verificar autenticación
            if (!$this->isAuthenticated()) {
                http_response_code(401);
                return json_encode(['error' => 'No autenticado']);
            }

            // Obtener usuario
            $userId = \ChristianLMS\Domain\ValueObjects\UserId::fromString($id);
            $user = $this->userRepository->findById($userId);

            if (!$user) {
                http_response_code(404);
                return json_encode(['error' => 'Usuario no encontrado']);
            }

            // Verificar permisos (solo admin o el propio usuario)
            $currentUserId = $_SESSION['user_id'];
            $userResponse = UserResponseDTO::fromUser($user);

            if ($userResponse->id->getValue() !== $currentUserId && !$this->hasRole('admin')) {
                http_response_code(403);
                return json_encode(['error' => 'No autorizado']);
            }

            return json_encode($userResponse->toArray());

        } catch (\Exception $e) {
            error_log("Error en API user show: " . $e->getMessage());
            http_response_code(500);
            return json_encode(['error' => 'Error interno del servidor']);
        }
    }

    /**
     * Verificar si el usuario está autenticado
     */
    private function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    private function hasRole(string $role): bool
    {
        $userRoles = $_SESSION['user_roles'] ?? [];
        return in_array($role, $userRoles);
    }

    /**
     * Renderizar vista
     */
    private function renderView(string $viewName, array $data = []): string
    {
        // Extraer variables para la vista
        extract($data);
        
        // Incluir header
        ob_start();
        include __DIR__ . "/../../Views/layouts/header.php";
        $header = ob_get_clean();

        // Incluir contenido
        ob_start();
        include __DIR__ . "/../../Views/{$viewName}.php";
        $content = ob_get_clean();

        // Incluir footer
        ob_start();
        include __DIR__ . "/../../Views/layouts/footer.php";
        $footer = ob_get_clean();

        return $header . $content . $footer;
    }

    /**
     * Redirigir
     */
    private function redirect(string $url): string
    {
        header("Location: {$url}");
        exit;
    }
}

/**
 * Funciones helper para el controlador
 */

/**
 * Obtener controlador de usuarios
 */
function getUserController(): UserController
{
    // Obtener servicios de la aplicación
    $app = app();
    
    // Crear repositorio (esto sería inyectado en una aplicación real)
    // Por simplicidad, asumimos que ya está configurado
    
    // Crear casos de uso
    $registerUseCase = new RegisterUserUseCase(
        // $userRepository, // Se inyectaría
        $app->getService('password'),
        $app->getService('email'),
        $app->getService('validation')
    );

    $authenticateUseCase = new AuthenticateUserUseCase(
        // $userRepository, // Se inyectaría
        $app->getService('password')
    );

    return new UserController(
        $registerUseCase,
        $authenticateUseCase,
        // $userRepository // Se inyectaría
    );
}
