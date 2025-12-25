<?php
/**
 * =============================================================================
 * SERVICIOS DE APLICACIÓN
 * Christian LMS System - Application Layer
 * =============================================================================
 */

namespace ChristianLMS\Application\Services;

use ChristianLMS\Domain\ValueObjects\PasswordHash;
use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Repositories\{
    UserRepository,
    CourseRepository,
    SubjectRepository,
    AcademicPeriodRepository,
    EnrollmentRepository,
    ModuleRepository,
    SubjectPrerequisiteRepository,
    CourseTeacherRepository
};

/**
 * Servicio de Contraseñas
 */
class PasswordService
{
    /**
     * Verificar fortaleza de contraseña
     */
    public function checkStrength(string $password): array
    {
        return PasswordHash::getPasswordStrength($password);
    }

    /**
     * Generar hash de contraseña
     */
    public function hashPassword(string $password): PasswordHash
    {
        return PasswordHash::fromPlainPassword($password);
    }

    /**
     * Verificar contraseña contra hash
     */
    public function verifyPassword(string $password, PasswordHash $hash): bool
    {
        return $hash->verify($password);
    }

    /**
     * Generar contraseña temporal segura
     */
    public function generateTemporaryPassword(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }

    /**
     * Generar token de reset de contraseña
     */
    public function generateResetToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Validar token de reset
     */
    public function validateResetToken(string $token): bool
    {
        return strlen($token) === 64 && ctype_xdigit($token);
    }
}

/**
 * Servicio de Validación
 */
class ValidationService
{
    /**
     * Validar formato de teléfono mexicano
     */
    public function isValidPhone(string $phone): bool
    {
        // Remover caracteres no numéricos
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Validar longitud (10 dígitos para números mexicanos)
        if (strlen($cleanPhone) !== 10) {
            return false;
        }
        
        // Validar que empiece con dígito válido
        return preg_match('/^[2-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]$/', $cleanPhone) === 1;
    }

    /**
     * Formatear teléfono mexicano
     */
    public function formatPhone(string $phone): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($cleanPhone) === 10) {
            return preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '$1 $2 $3', $cleanPhone);
        }
        
        return $phone;
    }

    /**
     * Validar formato de CURP
     */
    public function isValidCURP(string $curp): bool
    {
        return preg_match('/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z][0-9]$/', $curp) === 1;
    }

    /**
     * Validar formato de RFC
     */
    public function isValidRFC(string $rfc): bool
    {
        // RFC persona física
        $rfcFisica = '/^[A-ZÑ&]{4}[0-9]{6}[A-Z0-9]{3}$/';
        // RFC persona moral
        $rfcMoral = '/^[A-ZÑ&]{3}[0-9]{6}[A-Z0-9]{3}$/';
        
        return preg_match($rfcFisica, $rfc) === 1 || preg_match($rfcMoral, $rfc) === 1;
    }

    /**
     * Validar código postal mexicano
     */
    public function isValidPostalCode(string $postalCode): bool
    {
        return preg_match('/^[0-9]{5}$/', $postalCode) === 1;
    }

    /**
     * Sanitizar input de usuario
     */
    public function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validar edad
     */
    public function isValidAge(string $birthDate): bool
    {
        $birth = new \DateTime($birthDate);
        $today = new \DateTime();
        $age = $today->diff($birth)->y;
        
        return $age >= 3 && $age <= 100; // Entre 3 y 100 años
    }

    /**
     * Validar fecha en formato válido
     */
    public function isValidDate(string $date, string $format = 'Y-m-d'): bool
    {
        $dateTime = \DateTime::createFromFormat($format, $date);
        return $dateTime && $dateTime->format($format) === $date;
    }

    /**
     * Validar matrícula de estudiante
     */
    public function isValidMatricula(string $matricula): bool
    {
        // Formato: CHLMS240001 (CHLMS + YY + 6 dígitos)
        return preg_match('/^CHLMS[0-9]{2}[0-9]{6}$/', $matricula) === 1;
    }

    /**
     * Generar matrícula de estudiante
     */
    public function generateMatricula(int $sequence): string
    {
        $year = date('y'); // Últimos 2 dígitos del año
        return sprintf('CHLMS%06d', $sequence);
    }
}

/**
 * Servicio de Generación de IDs
 */
class IdGenerationService
{
    /**
     * Generar UUID v4
     */
    public function generateUUID(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // versión 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Generar slug desde texto
     */
    public function generateSlug(string $text): string
    {
        // Convertir a minúsculas
        $text = mb_strtolower($text, 'UTF-8');
        
        // Remover acentos
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        
        // Reemplazar caracteres especiales
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        
        // Remover guiones al inicio y final
        $text = trim($text, '-');
        
        return $text;
    }

    /**
     * Generar código único
     */
    public function generateUniqueCode(int $length = 8): string
    {
        return substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', $length)), 0, $length);
    }

    /**
     * Generar número secuencial
     */
    public function generateSequentialNumber(int $min = 100000, int $max = 999999): int
    {
        return random_int($min, $max);
    }
}

/**
 * Servicio de Normalización de Datos
 */
class DataNormalizationService
{
    /**
     * Normalizar nombre
     */
    public function normalizeName(string $name): string
    {
        // Capitalizar palabras y remover espacios extras
        $name = preg_replace('/\s+/', ' ', trim($name));
        return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Normalizar email
     */
    public function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    /**
     * Normalizar teléfono
     */
    public function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Normalizar texto para búsqueda
     */
    public function normalizeForSearch(string $text): string
    {
        // Remover acentos y convertir a minúsculas
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = mb_strtolower($text, 'UTF-8');
        
        // Remover caracteres especiales
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        
        // Normalizar espacios
        $text = preg_replace('/\s+/', ' ', trim($text));
        
        return $text;
    }
}

/**
 * Clase principal de servicios de aplicación
 * Centraliza el acceso a repositorios y servicios
 */
class ApplicationServices
{
    private static ?ApplicationServices $instance = null;
    private ?ConnectionManager $connectionManager = null;
    private ?UserRepository $userRepository = null;
    private ?CourseRepository $courseRepository = null;
    private ?SubjectRepository $subjectRepository = null;
    private ?AcademicPeriodRepository $academicPeriodRepository = null;
    private ?EnrollmentRepository $enrollmentRepository = null;
    private ?ModuleRepository $moduleRepository = null;
    private ?SubjectPrerequisiteRepository $subjectPrerequisiteRepository = null;
    private ?CourseTeacherRepository $courseTeacherRepository = null;

    /**
     * Singleton pattern
     */
    public static function getInstance(): ApplicationServices
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor privado
     */
    private function __construct()
    {
        // Inicializar conexiones lazily
    }

    /**
     * Obtener ConnectionManager
     */
    public function getConnectionManager(): ConnectionManager
    {
        if ($this->connectionManager === null) {
            $this->connectionManager = new ConnectionManager();
        }
        return $this->connectionManager;
    }

    /**
     * Obtener UserRepository
     */
    public function getUserRepository(): UserRepository
    {
        if ($this->userRepository === null) {
            $this->userRepository = new UserRepository($this->getConnectionManager());
        }
        return $this->userRepository;
    }

    /**
     * Obtener CourseRepository
     */
    public function getCourseRepository(): CourseRepository
    {
        if ($this->courseRepository === null) {
            $this->courseRepository = new CourseRepository($this->getConnectionManager());
        }
        return $this->courseRepository;
    }

    /**
     * Obtener SubjectRepository
     */
    public function getSubjectRepository(): SubjectRepository
    {
        if ($this->subjectRepository === null) {
            $this->subjectRepository = new SubjectRepository($this->getConnectionManager());
        }
        return $this->subjectRepository;
    }

    /**
     * Obtener AcademicPeriodRepository
     */
    public function getAcademicPeriodRepository(): AcademicPeriodRepository
    {
        if ($this->academicPeriodRepository === null) {
            $this->academicPeriodRepository = new AcademicPeriodRepository($this->getConnectionManager());
        }
        return $this->academicPeriodRepository;
    }

    /**
     * Obtener EnrollmentRepository
     */
    public function getEnrollmentRepository(): EnrollmentRepository
    {
        if ($this->enrollmentRepository === null) {
            $this->enrollmentRepository = new EnrollmentRepository($this->getConnectionManager());
        }
        return $this->enrollmentRepository;
    }

    public function getModuleRepository(): ModuleRepository
    {
        if ($this->moduleRepository === null) {
            $this->moduleRepository = new ModuleRepository($this->getConnectionManager());
        }
        return $this->moduleRepository;
    }

    public function getSubjectPrerequisiteRepository(): SubjectPrerequisiteRepository
    {
        if ($this->subjectPrerequisiteRepository === null) {
            $this->subjectPrerequisiteRepository = new SubjectPrerequisiteRepository($this->getConnectionManager());
        }
        return $this->subjectPrerequisiteRepository;
    }

    public function getCourseTeacherRepository(): CourseTeacherRepository
    {
        if ($this->courseTeacherRepository === null) {
            $this->courseTeacherRepository = new CourseTeacherRepository($this->getConnectionManager());
        }
        return $this->courseTeacherRepository;
    }

    /**
     * Servicio de Utilidades Generales
     */
    public function getUtilityService(): UtilityService
    {
        static $utilityService = null;
        if ($utilityService === null) {
            $utilityService = new UtilityService();
        }
        return $utilityService;
    }

    /**
     * Servicio de validación
     */
    public function getValidationService(): ValidationService
    {
        static $validationService = null;
        if ($validationService === null) {
            $validationService = new ValidationService();
        }
        return $validationService;
    }

    /**
     * Servicio de contraseñas
     */
    public function getPasswordService(): PasswordService
    {
        static $passwordService = null;
        if ($passwordService === null) {
            $passwordService = new PasswordService();
        }
        return $passwordService;
    }

    /**
     * Servicio de generación de IDs
     */
    public function getIdGenerationService(): IdGenerationService
    {
        static $idGenerationService = null;
        if ($idGenerationService === null) {
            $idGenerationService = new IdGenerationService();
        }
        return $idGenerationService;
    }

    /**
     * Servicio de normalización de datos
     */
    public function getDataNormalizationService(): DataNormalizationService
    {
        static $dataNormalizationService = null;
        if ($dataNormalizationService === null) {
            $dataNormalizationService = new DataNormalizationService();
        }
        return $dataNormalizationService;
    }
}

/**
 * Servicio de Utilidades Generales
 */
class UtilityService
{
    /**
     * Formatear nombre completo
     */
    public function formatFullName(string $firstName, string $lastName): string
    {
        return trim($this->normalizeName($firstName) . ' ' . $this->normalizeName($lastName));
    }

    /**
     * Calcular edad desde fecha de nacimiento
     */
    public function calculateAge(string $birthDate): int
    {
        $birth = new \DateTime($birthDate);
        $today = new \DateTime();
        return $today->diff($birth)->y;
    }

    /**
     * Verificar si es día laboral
     */
    public function isBusinessDay(\DateTime $date): bool
    {
        $dayOfWeek = $date->format('N');
        return $dayOfWeek >= 1 && $dayOfWeek <= 5; // Lunes a viernes
    }

    /**
     * Obtener próximo día laboral
     */
    public function getNextBusinessDay(\DateTime $date = null): \DateTime
    {
        if ($date === null) {
            $date = new \DateTime();
        }
        
        do {
            $date->modify('+1 day');
        } while (!$this->isBusinessDay($date));
        
        return $date;
    }

    /**
     * Formatear tamaño de archivo
     */
    public function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Generar color aleatorio
     */
    public function generateRandomColor(): string
    {
        return sprintf('#%06x', random_int(0, 0xFFFFFF));
    }
}
