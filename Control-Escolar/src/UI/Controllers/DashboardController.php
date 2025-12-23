<?php
// Controlador principal para el dashboard y navegación
require_once __DIR__ . '/../Application/Services/ApplicationServices.php';
require_once __DIR__ . '/../Infrastructure/Repositories/UserRepository.php';
require_once __DIR__ . '/../Infrastructure/Repositories/CourseRepository.php';
require_once __DIR__ . '/../Infrastructure/Repositories/EnrollmentRepository.php';
require_once __DIR__ . '/../Infrastructure/Repositories/SubjectRepository.php';

class DashboardController
{
    private $userRepository;
    private $courseRepository;
    private $enrollmentRepository;
    private $subjectRepository;
    private $applicationServices;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->courseRepository = new CourseRepository();
        $this->enrollmentRepository = new EnrollmentRepository();
        $this->subjectRepository = new SubjectRepository();
        $this->applicationServices = new ApplicationServices();
    }

    public function index()
    {
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        // Obtener estadísticas del dashboard
        $dashboardStats = $this->getDashboardStats();
        
        // Obtener actividad reciente
        $recentActivity = $this->getRecentActivity();
        
        // Cargar las vistas del dashboard
        $this->loadDashboardView($dashboardStats, $recentActivity);
    }

    public function navigate($section)
    {
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        switch ($section) {
            case 'courses':
                $this->loadSection('courses');
                break;
            case 'enrollments':
                $this->loadSection('enrollments');
                break;
            case 'subjects':
                $this->loadSection('subjects');
                break;
            case 'students':
                $this->loadSection('students');
                break;
            case 'teachers':
                $this->loadSection('teachers');
                break;
            case 'reports':
                $this->loadSection('reports');
                break;
            case 'settings':
                $this->loadSection('settings');
                break;
            default:
                // Redirigir al dashboard principal si la sección no existe
                $this->index();
                break;
        }
    }

    private function getDashboardStats()
    {
        try {
            // En implementación real, usar los repositorios para obtener estadísticas
            return [
                'total_courses' => 12,
                'active_courses' => 10,
                'total_enrollments' => 156,
                'active_enrollments' => 142,
                'pending_enrollments' => 8,
                'total_students' => 98,
                'total_subjects' => 15,
                'total_teachers' => 8,
                'monthly_revenue' => 23450.00,
                'completion_rate' => 85.5,
                'average_grade' => 4.2
            ];
        } catch (Exception $e) {
            // En caso de error, retornar estadísticas por defecto
            return [
                'total_courses' => 0,
                'active_courses' => 0,
                'total_enrollments' => 0,
                'active_enrollments' => 0,
                'pending_enrollments' => 0,
                'total_students' => 0,
                'total_subjects' => 0,
                'total_teachers' => 0,
                'monthly_revenue' => 0,
                'completion_rate' => 0,
                'average_grade' => 0
            ];
        }
    }

    private function getRecentActivity()
    {
        try {
            // En implementación real, obtener actividad reciente de la base de datos
            return [
                [
                    'type' => 'enrollment',
                    'description' => 'Nuevo estudiante inscrito en Matemáticas Básicas',
                    'user' => 'María Rodríguez',
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours'))
                ],
                [
                    'type' => 'course',
                    'description' => 'Curso "Estudios Bíblicos" actualizado',
                    'user' => 'Prof. Carlos López',
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-4 hours'))
                ],
                [
                    'type' => 'payment',
                    'description' => 'Pago registrado para Inscripción ENR-001',
                    'user' => 'Sistema',
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-6 hours'))
                ],
                [
                    'type' => 'subject',
                    'description' => 'Nueva materia "Ciencias Naturales" creada',
                    'user' => 'Admin',
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-1 day'))
                ]
            ];
        } catch (Exception $e) {
            return [];
        }
    }

    private function loadDashboardView($stats, $activity)
    {
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $userRole = $_SESSION['user_role'] ?? 'Estudiante';
        
        include __DIR__ . '/Views/dashboard/index.php';
    }

    private function loadSection($section)
    {
        switch ($section) {
            case 'courses':
                include __DIR__ . '/Views/courses/index.php';
                break;
            case 'enrollments':
                include __DIR__ . '/Views/enrollments/index.php';
                break;
            case 'subjects':
                include __DIR__ . '/Views/subjects/index.php';
                break;
            case 'students':
                include __DIR__ . '/Views/students/index.php';
                break;
            case 'teachers':
                include __DIR__ . '/Views/teachers/index.php';
                break;
            case 'reports':
                include __DIR__ . '/Views/reports/index.php';
                break;
            case 'settings':
                include __DIR__ . '/Views/settings/index.php';
                break;
            default:
                $this->index();
                break;
        }
    }

    public function getApiStats()
    {
        // API endpoint para obtener estadísticas vía AJAX
        header('Content-Type: application/json');
        
        try {
            $stats = $this->getDashboardStats();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ]);
        }
    }

    public function getApiActivity()
    {
        // API endpoint para obtener actividad reciente vía AJAX
        header('Content-Type: application/json');
        
        try {
            $activity = $this->getRecentActivity();
            echo json_encode([
                'success' => true,
                'data' => $activity
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener actividad'
            ]);
        }
    }
}
?>