<?php
/**
 * =============================================================================
 * CONFIGURACIÓN PRINCIPAL DE LA APLICACIÓN
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Persistence\Database\SchemaManager;
use ChristianLMS\Infrastructure\Mail\EmailService;
use ChristianLMS\Application\Services\PasswordService;
use ChristianLMS\Application\Services\ValidationService;
use ChristianLMS\Application\Services\IdGenerationService;
use ChristianLMS\Application\Services\DataNormalizationService;
use ChristianLMS\Application\Services\UtilityService;

// Cargar bootstrap
require_once __DIR__ . '/bootstrap.php';

use ChristianLMS\Infrastructure\Ports\UserRepositoryInterface;

/**
 * Clase de Configuración Principal de la Aplicación
 */
class Application
{
    private static ?self $instance = null;
    private array $config;
    private ConnectionManager $connectionManager;
    private SchemaManager $schemaManager;
    private EmailService $emailService;
    private array $services = [];

    private function __construct()
    {
        global $application;
        $this->config = $application->getConfig();
        $this->connectionManager = $application->getConnectionManager();
        $this->schemaManager = $application->getSchemaManager();
        
        $this->initializeServices();
    }

    /**
     * Obtener instancia singleton
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Inicializar servicios de la aplicación
     */
    private function initializeServices(): void
    {
        // Servicios de aplicación
        $this->services['password'] = new PasswordService();
        $this->services['validation'] = new ValidationService();
        $this->services['idGeneration'] = new IdGenerationService();
        $this->services['normalization'] = new DataNormalizationService();
        $this->services['utility'] = new UtilityService();

        // Servicio de email
        $emailConfig = $this->config['mail'] ?? [];
        $this->emailService = new EmailService($emailConfig);
        $this->services['email'] = $this->emailService;
    }

    /**
     * Obtener servicio por nombre
     */
    public function getService(string $serviceName)
    {
        return $this->services[$serviceName] ?? null;
    }

    /**
     * Obtener administrador de conexiones
     */
    public function getConnectionManager(): ConnectionManager
    {
        return $this->connectionManager;
    }

    /**
     * Obtener administrador de esquema
     */
    public function getSchemaManager(): SchemaManager
    {
        return $this->schemaManager;
    }

    /**
     * Obtener configuración
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Obtener variable de entorno
     */
    public function env(string $key, $default = null)
    {
        return getenv($key) ?: $default;
    }

    /**
     * Verificar si estamos en modo desarrollo
     */
    public function isDevelopment(): bool
    {
        return $this->env('APP_ENV') === 'development';
    }

    /**
     * Verificar si estamos en modo producción
     */
    public function isProduction(): bool
    {
        return $this->env('APP_ENV') === 'production';
    }

    /**
     * Verificar si el debug está habilitado
     */
    public function isDebugEnabled(): bool
    {
        return $this->env('APP_DEBUG') === 'true';
    }

    /**
     * Obtener URL base de la aplicación
     */
    public function getBaseUrl(): string
    {
        return $this->env('APP_URL', 'http://localhost');
    }

    /**
     * Obtener configuración de base de datos
     */
    public function getDatabaseConfig(): array
    {
        return $this->config['connections'] ?? [];
    }

    /**
     * Ejecutar migraciones pendientes
     */
    public function runMigrations(): void
    {
        $migrationPath = database_path('migrations');
        $this->schemaManager->runPendingMigrations($migrationPath);
    }

    /**
     * Verificar integridad del esquema
     */
    public function verifySchema(): array
    {
        $expectedTables = [
            'users' => [
                'columns' => ['id', 'matricula', 'name', 'email', 'password', 'role', 'status']
            ],
            'courses' => [
                'columns' => ['id', 'academic_period_id', 'subject_id', 'day_of_week', 'start_time', 'end_time', 'location', 'max_students', 'status']
            ],
            'subjects' => [
                'columns' => ['id', 'module_id', 'name', 'sort_order', 'is_active']
            ],
            'academic_periods' => [
                'columns' => ['id', 'name', 'start_date', 'end_date', 'enrollment_start', 'enrollment_end', 'status']
            ],
            'enrollments' => [
                'columns' => ['id', 'user_id', 'course_id', 'status', 'enrolled_by', 'override_seriation', 'override_schedule']
            ]
        ];

        return $this->schemaManager->verifySchemaIntegrity($expectedTables);
    }

    /**
     * Obtener estadísticas del sistema
     */
    public function getSystemStats(): array
    {
        try {
            $stats = [
                'database' => [
                    'connected' => true,
                    'status' => $this->connectionManager->getConnectionStatus()
                ],
                'application' => [
                    'environment' => $this->env('APP_ENV'),
                    'debug_enabled' => $this->isDebugEnabled(),
                    'base_url' => $this->getBaseUrl(),
                    'version' => '2.0.0'
                ],
                'services' => []
            ];

            // Verificar servicios
            foreach ($this->services as $name => $service) {
                $stats['services'][$name] = 'available';
            }

            return $stats;

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'database' => ['connected' => false],
                'application' => [
                    'environment' => 'unknown',
                    'debug_enabled' => false
                ],
                'services' => []
            ];
        }
    }

    /**
     * Inicializar datos de prueba
     */
    public function seedTestData(): void
    {
        if (!$this->isDevelopment()) {
            throw new \Exception('Solo se puede sembrar datos de prueba en modo desarrollo');
        }

        // Implementar lógica de seeding
        // Por ahora, crear usuario administrador por defecto
        try {
            $this->createDefaultAdmin();
        } catch (\Exception $e) {
            error_log("Error creando administrador por defecto: " . $e->getMessage());
        }
    }

    /**
     * Crear usuario administrador por defecto
     */
    private function createDefaultAdmin(): void
    {
        // Verificar si ya existe un admin
        $existingAdmin = $this->connectionManager->fetch(
            "SELECT id FROM users WHERE role = 'admin' LIMIT 1"
        );

        if ($existingAdmin) {
            return; // Ya existe un admin
        }

        // Crear usuario admin por defecto
        $adminData = [
            'id' => \ChristianLMS\Domain\ValueObjects\UserId::generate()->getValue(),
            'matricula' => sprintf('ECAFC%s%03d', date('Y'), random_int(1, 999)),
            'name' => 'Administrador Sistema',
            'email' => 'admin@churchlms.com',
            'password' => password_hash('admin123', PASSWORD_BCRYPT),
            'role' => 'admin',
            'status' => 'active'
        ];

        $this->connectionManager->execute(
            "INSERT INTO users (id, matricula, name, email, password, role, status) 
             VALUES (:id, :matricula, :name, :email, :password, :role, :status)",
            $adminData
        );
    }

    /**
     * Cerrar conexiones y limpiar recursos
     */
    public function shutdown(): void
    {
        $this->connectionManager->closeConnections();
    }

    /**
     * Obtener información de ayuda
     */
    public function getHelpInfo(): array
    {
        return [
            'system' => [
                'name' => 'Christian LMS System',
                'version' => '2.0.0',
                'architecture' => 'Hexagonal Architecture',
                'php_version' => PHP_VERSION,
                'database' => 'MySQL/MariaDB'
            ],
            'urls' => [
                'home' => $this->getBaseUrl(),
                'admin' => $this->getBaseUrl() . '/admin',
                'api' => $this->getBaseUrl() . '/api'
            ],
            'default_credentials' => [
                'admin' => [
                    'email' => 'admin@churchlms.com',
                    'password' => 'admin123'
                ]
            ],
            'commands' => [
                'run_migrations' => 'Ejecutar migraciones pendientes',
                'verify_schema' => 'Verificar integridad del esquema',
                'seed_test_data' => 'Sembrar datos de prueba',
                'system_stats' => 'Obtener estadísticas del sistema'
            ]
        ];
    }
}

/**
 * Funciones helper globales
 */

/**
 * Obtener instancia de la aplicación
 */
function app(): Application
{
    return Application::getInstance();
}

/**
 * Obtener servicio
 */
function service(string $serviceName)
{
    return app()->getService($serviceName);
}

/**
 * Obtener configuración
 */
function config(string $key = null, $default = null)
{
    if ($key === null) {
        return app()->getConfig();
    }
    
    $keys = explode('.', $key);
    $value = app()->getConfig();
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    
    return $value;
}

/**
 * Obtener base de datos
 */
function db(): ConnectionManager
{
    return app()->getConnectionManager();
}

/**
 * Obtener esquema
 */
function schema(): SchemaManager
{
    return app()->getSchemaManager();
}

/**
 * Obtener variable de entorno
 */
function env(string $key, $default = null)
{
    return getenv($key) ?: $default;
}

// Registrar función de cierre
register_shutdown_function(function() {
    app()->shutdown();
});
