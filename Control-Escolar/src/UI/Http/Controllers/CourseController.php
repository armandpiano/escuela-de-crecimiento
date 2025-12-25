<?php
/**
 * =============================================================================
 * CONTROLADOR: COURSE CONTROLLER
 * Christian LMS System - UI Layer
 * =============================================================================
 */

namespace ChristianLMS\UI\Http\Controllers;

use ChristianLMS\Application\UseCases\Course\CreateCourseUseCase;
use ChristianLMS\Application\Services\ApplicationServices;
use ChristianLMS\Infrastructure\Mail\EmailService;
use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Repositories\CourseRepository;
use ChristianLMS\Infrastructure\Repositories\UserRepository;

/**
 * CourseController
 * 
 * Controlador para gestión de cursos
 */
class CourseController
{
    private CreateCourseUseCase $createCourseUseCase;
    private ApplicationServices $applicationServices;

    public function __construct()
    {
        $this->applicationServices = new ApplicationServices();
        
        // Inicializar dependencias
        $connectionManager = new ConnectionManager();
        $courseRepository = new CourseRepository($connectionManager);
        $userRepository = new UserRepository($connectionManager);
        $emailService = new EmailService();
        
        $this->createCourseUseCase = new CreateCourseUseCase(
            $courseRepository,
            $this->applicationServices,
            $emailService
        );
    }

    /**
     * Mostrar lista de cursos
     */
    public function index(): void
    {
        try {
            // Verificar autenticación
            $this->requireAuth();

            $courseRepository = $this->applicationServices->getCourseRepository();
            
            // Obtener parámetros de paginación
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = min(50, max(1, intval($_GET['per_page'] ?? 20)));
            
            // Aplicar filtros si existen
            $criteria = [];
            if (!empty($_GET['status'])) {
                $criteria['status'] = $_GET['status'];
            }
            if (!empty($_GET['search'])) {
                $criteria['name'] = $_GET['search'];
            }

            // Obtener cursos
            if (!empty($criteria)) {
                $courses = $courseRepository->search($criteria, $page, $perPage);
            } else {
                $courses = $courseRepository->findPaginated($page, $perPage);
            }

            // Incluir vista
            $pageTitle = 'Gestión de Cursos';
            include __DIR__ . '/../../UI/Views/pages/courses/index.php';

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al cargar cursos: ' . $e->getMessage();
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Mostrar formulario de creación de curso
     */
    public function create(): void
    {
        try {
            // Verificar autenticación y permisos
            $this->requireAuth();
            $this->requireTeacherOrAdmin();

            $applicationServices = $this->applicationServices;
            $professors = $applicationServices->getUserRepository()->findTeachers();
            $subjects = $applicationServices->getSubjectRepository()->findActive();

            $pageTitle = 'Crear Nuevo Curso';
            include __DIR__ . '/../../UI/Views/pages/courses/create.php';

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al cargar formulario: ' . $e->getMessage();
            header('Location: /courses');
            exit;
        }
    }

    /**
     * Procesar creación de curso
     */
    public function store(): void
    {
        try {
            // Verificar autenticación y permisos
            $this->requireAuth();
            $this->requireTeacherOrAdmin();

            // Validar método POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Método no permitido');
            }

            // Crear request
            $request = new \ChristianLMS\Application\UseCases\Course\CreateCourseRequest();
            
            // Llenar datos del request
            $request->setName($_POST['name'] ?? '');
            $request->setSubjectId($_POST['subject_id'] ?: null);
            $request->setDescription($_POST['description'] ?? null);
            $request->setMaxStudents(intval($_POST['max_students'] ?? 50));

            // Ejecutar caso de uso
            $response = $this->createCourseUseCase->execute($request);

            if ($response->isSuccess()) {
                $_SESSION['success'] = 'Curso creado exitosamente';
                header('Location: /courses/' . $response->getCourse()->getId()->getValue());
            } else {
                $_SESSION['error'] = $response->getMessage();
                header('Location: /courses/create');
            }
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al crear curso: ' . $e->getMessage();
            header('Location: /courses/create');
            exit;
        }
    }

    /**
     * Mostrar detalle de curso
     */
    public function show(string $id): void
    {
        try {
            // Verificar autenticación
            $this->requireAuth();

            $courseRepository = $this->applicationServices->getCourseRepository();
            $course = $courseRepository->findById(new \ChristianLMS\Domain\ValueObjects\CourseId($id));

            if (!$course) {
                throw new \Exception('Curso no encontrado');
            }

            // Verificar permisos para ver detalles
            $currentUser = $_SESSION['user'];
            $canView = $currentUser->isAdmin() || 
                      $course->getProfessorId()->equals($currentUser->getId()) ||
                      $currentUser->isTeacher();

            if (!$canView) {
                throw new \Exception('No tienes permisos para ver este curso');
            }

            // Obtener inscripciones del curso
            $enrollmentRepository = $this->applicationServices->getEnrollmentRepository();
            $enrollments = $enrollmentRepository->findByCourse($course->getId());

            $pageTitle = 'Detalle del Curso';
            include __DIR__ . '/../../UI/Views/pages/courses/show.php';

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /courses');
            exit;
        }
    }

    /**
     * Mostrar formulario de edición de curso
     */
    public function edit(string $id): void
    {
        try {
            // Verificar autenticación y permisos
            $this->requireAuth();
            $this->requireTeacherOrAdmin();

            $courseRepository = $this->applicationServices->getCourseRepository();
            $course = $courseRepository->findById(new \ChristianLMS\Domain\ValueObjects\CourseId($id));

            if (!$course) {
                throw new \Exception('Curso no encontrado');
            }

            // Verificar permisos de edición
            $currentUser = $_SESSION['user'];
            if (!$currentUser->isAdmin() && !$course->getProfessorId()->equals($currentUser->getId())) {
                throw new \Exception('No tienes permisos para editar este curso');
            }

            $professors = $this->applicationServices->getUserRepository()->findTeachers();
            $subjects = $this->applicationServices->getSubjectRepository()->findActive();

            $pageTitle = 'Editar Curso';
            include __DIR__ . '/../../UI/Views/pages/courses/edit.php';

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /courses');
            exit;
        }
    }

    /**
     * Procesar actualización de curso
     */
    public function update(string $id): void
    {
        try {
            // Verificar autenticación y permisos
            $this->requireAuth();
            $this->requireTeacherOrAdmin();

            // Validar método POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Método no permitido');
            }

            // TODO: Implementar UpdateCourseUseCase
            throw new \Exception('Funcionalidad en desarrollo');

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al actualizar curso: ' . $e->getMessage();
            header('Location: /courses/' . $id . '/edit');
            exit;
        }
    }

    /**
     * Eliminar curso
     */
    public function destroy(string $id): void
    {
        try {
            // Verificar autenticación y permisos
            $this->requireAuth();
            $this->requireAdmin(); // Solo admin puede eliminar

            // Validar método POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Método no permitido');
            }

            $courseRepository = $this->applicationServices->getCourseRepository();
            $course = $courseRepository->findById(new \ChristianLMS\Domain\ValueObjects\CourseId($id));

            if (!$course) {
                throw new \Exception('Curso no encontrado');
            }

            // Verificar que no tenga inscripciones activas
            $enrollmentRepository = $this->applicationServices->getEnrollmentRepository();
            $activeEnrollments = $enrollmentRepository->findByCourse($course->getId());
            
            $hasActiveEnrollments = array_filter($activeEnrollments, function($enrollment) {
                return $enrollment->isActive();
            });

            if (!empty($hasActiveEnrollments)) {
                throw new \Exception('No se puede eliminar un curso con inscripciones activas');
            }

            // Soft delete
            $courseRepository->softDelete($course->getId());

            $_SESSION['success'] = 'Curso eliminado exitosamente';
            header('Location: /courses');
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al eliminar curso: ' . $e->getMessage();
            header('Location: /courses');
            exit;
        }
    }

    /**
     * Buscar cursos disponibles para inscripción
     */
    public function available(): void
    {
        try {
            // Verificar autenticación
            $this->requireAuth();

            $courseRepository = $this->applicationServices->getCourseRepository();
            $courses = $courseRepository->findAvailableForEnrollment();

            $pageTitle = 'Cursos Disponibles';
            include __DIR__ . '/../../UI/Views/pages/courses/available.php';

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al cargar cursos disponibles: ' . $e->getMessage();
            header('Location: /dashboard');
            exit;
        }
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

    /**
     * Requiere rol de admin
     */
    private function requireAdmin(): void
    {
        $user = $_SESSION['user'];
        if (!$user->isAdmin()) {
            throw new \Exception('No tienes permisos para realizar esta acción');
        }
    }
}
