<?php
/**
 * =============================================================================
 * CONTROLADOR: ENROLLMENT CONTROLLER
 * Christian LMS System - UI Layer
 * =============================================================================
 */

namespace ChristianLMS\UI\Http\Controllers;

use ChristianLMS\Application\UseCases\Enrollment\EnrollStudentUseCase;
use ChristianLMS\Application\Services\ApplicationServices;
use ChristianLMS\Infrastructure\Mail\EmailService;
use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Repositories\EnrollmentRepository;
use ChristianLMS\Infrastructure\Repositories\CourseRepository;
use ChristianLMS\Infrastructure\Repositories\UserRepository;

/**
 * EnrollmentController
 * 
 * Controlador para gestión de inscripciones
 */
class EnrollmentController
{
    private EnrollStudentUseCase $enrollStudentUseCase;
    private ApplicationServices $applicationServices;

    public function __construct()
    {
        $this->applicationServices = new ApplicationServices();
        
        // Inicializar dependencias
        $connectionManager = new ConnectionManager();
        $enrollmentRepository = new EnrollmentRepository($connectionManager);
        $courseRepository = new CourseRepository($connectionManager);
        $userRepository = new UserRepository($connectionManager);
        $emailService = new EmailService();
        
        $this->enrollStudentUseCase = new EnrollStudentUseCase(
            $this->applicationServices,
            $emailService
        );
    }

    /**
     * Mostrar lista de inscripciones
     */
    public function index(): void
    {
        try {
            // Verificar autenticación
            $this->requireAuth();

            $enrollmentRepository = $this->applicationServices->getEnrollmentRepository();
            
            // Obtener parámetros de paginación
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = min(50, max(1, intval($_GET['per_page'] ?? 20)));
            
            // Aplicar filtros
            $criteria = [];
            if (!empty($_GET['status'])) {
                $criteria['status'] = $_GET['status'];
            }
            if (!empty($_GET['student_id'])) {
                $criteria['student_id'] = $_GET['student_id'];
            }
            if (!empty($_GET['course_id'])) {
                $criteria['course_id'] = $_GET['course_id'];
            }

            $currentUser = $_SESSION['user'];
            
            // Filtrar por permisos de usuario
            if ($currentUser->isStudent()) {
                $criteria['student_id'] = $currentUser->getId()->getValue();
            } elseif ($currentUser->isTeacher()) {
                // TODO: Filtrar por cursos del profesor
            }
            // Admin puede ver todo

            // Obtener inscripciones
            $enrollments = $enrollmentRepository->findPaginated($page, $perPage);

            // Incluir vista
            $pageTitle = 'Mis Inscripciones';
            include __DIR__ . '/../../UI/Views/pages/enrollments/index.php';

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al cargar inscripciones: ' . $e->getMessage();
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Mostrar formulario de inscripción
     */
    public function create(string $courseId): void
    {
        try {
            // Verificar autenticación
            $this->requireAuth();

            // Verificar que sea estudiante
            $currentUser = $_SESSION['user'];
            if (!$currentUser->isStudent()) {
                throw new \Exception('Solo los estudiantes pueden inscribirse a cursos');
            }

            // Obtener curso
            $courseRepository = $this->applicationServices->getCourseRepository();
            $course = $courseRepository->findById(new \ChristianLMS\Domain\ValueObjects\CourseId($courseId));

            if (!$course) {
                throw new \Exception('Curso no encontrado');
            }

            // Verificar que el curso esté disponible para inscripción
            if (!$course->canEnrollStudent()) {
                throw new \Exception('El curso no está disponible para inscripción');
            }

            // Obtener periodo académico actual
            $academicPeriodRepository = $this->applicationServices->getAcademicPeriodRepository();
            $currentPeriod = $academicPeriodRepository->findCurrent();

            if (!$currentPeriod) {
                throw new \Exception('No hay un periodo académico activo');
            }

            // Verificar que no esté ya inscrito
            $enrollmentRepository = $this->applicationServices->getEnrollmentRepository();
            $existingEnrollment = $enrollmentRepository->findByStudentAndCourse(
                $currentUser->getId(),
                $course->getId(),
                $currentPeriod->getId()
            );

            if ($existingEnrollment) {
                $_SESSION['error'] = 'Ya estás inscrito en este curso';
                header('Location: /courses/' . $courseId);
                exit;
            }

            $pageTitle = 'Inscribirse al Curso';
            include __DIR__ . '/../../UI/Views/pages/enrollments/create.php';

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /courses');
            exit;
        }
    }

    /**
     * Procesar inscripción
     */
    public function store(string $courseId): void
    {
        try {
            // Verificar autenticación
            $this->requireAuth();

            // Verificar que sea estudiante
            $currentUser = $_SESSION['user'];
            if (!$currentUser->isStudent()) {
                throw new \Exception('Solo los estudiantes pueden inscribirse a cursos');
            }

            // Validar método POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Método no permitido');
            }

            // Obtener periodo académico actual
            $academicPeriodRepository = $this->applicationServices->getAcademicPeriodRepository();
            $currentPeriod = $academicPeriodRepository->findCurrent();

            if (!$currentPeriod) {
                throw new \Exception('No hay un periodo académico activo');
            }

            // Crear request
            $request = new \ChristianLMS\Application\UseCases\Enrollment\EnrollStudentRequest();
            $request->setStudentId($currentUser->getId()->getValue());
            $request->setCourseId($courseId);
            $request->setAcademicPeriodId($currentPeriod->getId()->getValue());
            $request->setNotes($_POST['notes'] ?? null);

            // Ejecutar caso de uso
            $response = $this->enrollStudentUseCase->execute($request);

            if ($response->isSuccess()) {
                $_SESSION['success'] = 'Inscripción realizada exitosamente';
                header('Location: /enrollments/' . $response->getEnrollment()->getId()->getValue());
            } else {
                $_SESSION['error'] = $response->getMessage();
                header('Location: /courses/' . $courseId . '/enroll');
            }
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al procesar inscripción: ' . $e->getMessage();
            header('Location: /courses/' . $courseId);
            exit;
        }
    }

    /**
     * Mostrar detalle de inscripción
     */
    public function show(string $id): void
    {
        try {
            // Verificar autenticación
            $this->requireAuth();

            $enrollmentRepository = $this->applicationServices->getEnrollmentRepository();
            $enrollment = $enrollmentRepository->findById(new \ChristianLMS\Domain\ValueObjects\EnrollmentId($id));

            if (!$enrollment) {
                throw new \Exception('Inscripción no encontrada');
            }

            // Verificar permisos
            $currentUser = $_SESSION['user'];
            $canView = $currentUser->isAdmin() || 
                      $enrollment->getStudentId()->equals($currentUser->getId()) ||
                      $this->canTeacherViewEnrollment($currentUser, $enrollment);

            if (!$canView) {
                throw new \Exception('No tienes permisos para ver esta inscripción');
            }

            // Obtener datos relacionados
            $courseRepository = $this->applicationServices->getCourseRepository();
            $course = $courseRepository->findById($enrollment->getCourseId());

            $userRepository = $this->applicationServices->getUserRepository();
            $student = $userRepository->findById($enrollment->getStudentId());

            $pageTitle = 'Detalle de Inscripción';
            include __DIR__ . '/../../UI/Views/pages/enrollments/show.php';

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /enrollments');
            exit;
        }
    }

    /**
     * Retirarse de un curso
     */
    public function withdraw(string $id): void
    {
        try {
            // Verificar autenticación
            $this->requireAuth();

            // Validar método POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Método no permitido');
            }

            $currentUser = $_SESSION['user'];
            $enrollmentRepository = $this->applicationServices->getEnrollmentRepository();
            $courseRepository = $this->applicationServices->getCourseRepository();

            $enrollment = $enrollmentRepository->findById(new \ChristianLMS\Domain\ValueObjects\EnrollmentId($id));

            if (!$enrollment) {
                throw new \Exception('Inscripción no encontrada');
            }

            // Verificar permisos
            $canWithdraw = $currentUser->isAdmin() || 
                          $enrollment->getStudentId()->equals($currentUser->getId());

            if (!$canWithdraw) {
                throw new \Exception('No tienes permisos para realizar esta acción');
            }

            // Verificar que esté activo
            if (!$enrollment->isActive()) {
                throw new \Exception('Solo se pueden retirar inscripciones activas');
            }

            // Realizar retiro
            $enrollment->withdraw();
            $enrollmentRepository->save($enrollment);

            // Actualizar contador del curso
            $course = $courseRepository->findById($enrollment->getCourseId());
            if ($course) {
                $course->unenrollStudent();
                $courseRepository->save($course);
            }

            $_SESSION['success'] = 'Te has retirado exitosamente del curso';
            header('Location: /enrollments');
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al retirarse del curso: ' . $e->getMessage();
            header('Location: /enrollments/' . $id);
            exit;
        }
    }

    /**
     * Completar curso (solo profesores/admin)
     */
    public function complete(string $id): void
    {
        try {
            // Verificar autenticación y permisos
            $this->requireAuth();
            $this->requireTeacherOrAdmin();

            // Validar método POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Método no permitido');
            }

            $enrollmentRepository = $this->applicationServices->getEnrollmentRepository();
            $enrollment = $enrollmentRepository->findById(new \ChristianLMS\Domain\ValueObjects\EnrollmentId($id));

            if (!$enrollment) {
                throw new \Exception('Inscripción no encontrada');
            }

            // Verificar que esté activo
            if (!$enrollment->isActive()) {
                throw new \Exception('Solo se pueden completar inscripciones activas');
            }

            // Obtener datos del formulario
            $finalGrade = floatval($_POST['final_grade'] ?? 0.0);
            $creditsEarned = floatval($_POST['credits_earned'] ?? 0.0);

            if ($finalGrade < 0 || $finalGrade > 100) {
                throw new \Exception('La calificación final debe estar entre 0 y 100');
            }

            // Completar inscripción
            if ($finalGrade >= 60) {
                $enrollment->complete($finalGrade, $creditsEarned);
            } else {
                $enrollment->fail($finalGrade);
            }

            $enrollmentRepository->save($enrollment);

            $_SESSION['success'] = 'Curso completado exitosamente';
            header('Location: /enrollments/' . $id);
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al completar curso: ' . $e->getMessage();
            header('Location: /enrollments/' . $id);
            exit;
        }
    }

    /**
     * Mostrar historial de inscripciones del estudiante
     */
    public function history(): void
    {
        try {
            // Verificar autenticación
            $this->requireAuth();

            $currentUser = $_SESSION['user'];
            if (!$currentUser->isStudent()) {
                throw new \Exception('Solo los estudiantes pueden ver su historial');
            }

            $enrollmentRepository = $this->applicationServices->getEnrollmentRepository();
            $enrollments = $enrollmentRepository->findByStudent($currentUser->getId());

            // Obtener estadísticas del estudiante
            $gpa = $enrollmentRepository->getStudentGPA($currentUser->getId());
            $totalCredits = $enrollmentRepository->getStudentCredits($currentUser->getId());

            $pageTitle = 'Mi Historial Académico';
            include __DIR__ . '/../../UI/Views/pages/enrollments/history.php';

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al cargar historial: ' . $e->getMessage();
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Verificar si un profesor puede ver una inscripción
     */
    private function canTeacherViewEnrollment($currentUser, $enrollment): bool
    {
        if (!$currentUser->isTeacher()) {
            return false;
        }

        // TODO: Implementar lógica para verificar si el profesor enseña el curso
        // Por ahora, permitir que todos los profesores vean todas las inscripciones
        return true;
    }

    /**
     * Requiere autenticación
     */
    private function requireAuth(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /auth/login');
            exit;
        }
    }

    /**
     * Requiere rol de profesor o admin
     */
    private function requireTeacherOrAdmin(): void
    {
        $user = $_SESSION['user'];
        if (!$user->isTeacher() && !$user->isAdmin()) {
            throw new \Exception('No tienes permisos para realizar esta acción');
        }
    }
}
