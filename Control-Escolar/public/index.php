<?php
/**
 * Punto de entrada principal del Sistema Christian LMS
 * Control-Escolar - Acceso directo sin /public/
 * Detecta automáticamente la ruta y el estado de autenticación
 */

// Iniciar sesión
session_start();

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Configurar errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader simple para las clases
spl_autoload_register(function ($className) {
    $directories = [
        __DIR__ . '/../src/Domain/',
        __DIR__ . '/../src/Application/',
        __DIR__ . '/../src/Infrastructure/',
        __DIR__ . '/../src/UI/Controllers/',
        __DIR__ . '/../src/UI/Views/',
        __DIR__ . '/../src/UI/Views/layouts/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Cargar configuración de base de datos
$dbConfig = require __DIR__ . '/../config/database.php';

// Función para detectar automáticamente la ruta base
function getBasePath() {
    $requestUri = $_SERVER['REQUEST_URI'];
    
    // Detectar si está en localhost/escuela-de-crecimiento/Control-Escolar/
    if (strpos($requestUri, '/escuela-de-crecimiento/Control-Escolar/') !== false) {
        return '/escuela-de-crecimiento/Control-Escolar';
    }
    
    // Detectar si está en /Control-Escolar/ (raíz del dominio)
    if (strpos($requestUri, '/Control-Escolar/') !== false) {
        return '/Control-Escolar';
    }
    
    // Fallback para desarrollo local
    return '/Control-Escolar';
}

// Función para obtener la ruta solicitada
function getCurrentRoute() {
    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH);
    $basePath = getBasePath();
    
    // Remover el directorio base si existe
    if (strpos($path, $basePath . '/') === 0) {
        $path = substr($path, strlen($basePath . '/'));
    } elseif ($path === $basePath) {
        $path = '';
    }
    
    // Dividir la ruta en segmentos
    $segments = explode('/', trim($path, '/'));
    
    return [
        'path' => $path,
        'segments' => $segments,
        'action' => $segments[0] ?? 'index',
        'id' => $segments[1] ?? null,
        'base_path' => $basePath
    ];
}

// Función para verificar autenticación
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

// Función para requerir autenticación
function requireAuth($basePath) {
    if (!isAuthenticated()) {
        header('Location: ' . $basePath . '/login');
        exit();
    }
}

function requireNonStudent($basePath) {
    $userRole = $_SESSION['user_role'] ?? '';
    if ($userRole === 'student') {
        header('Location: ' . $basePath . '/enrollments');
        exit();
    }
}

function requireAdmin($basePath) {
    $userRole = $_SESSION['user_role'] ?? '';
    if ($userRole !== 'admin') {
        header('Location: ' . $basePath . '/dashboard');
        exit();
    }
}

// Función para redirigir si ya está autenticado
function redirectIfAuthenticated($basePath) {
    if (isAuthenticated()) {
        header('Location: ' . $basePath . '/dashboard');
        exit();
    }
}

// Función para cargar el layout principal
function loadLayout($content, $title = 'Sistema Christian LMS', $basePath = '/Control-Escolar') {
    $userName = $_SESSION['user_name'] ?? null;
    $userRole = $_SESSION['user_role'] ?? null;
    
    // Si el contenido ya es HTML completo (como en dashboard), retornarlo directamente
    if (strpos($content, '<!DOCTYPE html>') !== false || strpos($content, '<html') !== false) {
        return $content;
    }
    
    // Para otros contenidos, envolver en el layout
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --primary-color: #0d6efd;
                --secondary-color: #6c757d;
                --success-color: #198754;
                --warning-color: #ffc107;
                --danger-color: #dc3545;
                --info-color: #0dcaf0;
            }
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Inter', sans-serif;
                background-color: #f8f9fa;
                color: #333;
            }
            
            .main-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem 1rem;
            }
            
            .auth-card {
                background: white;
                border-radius: 1rem;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                padding: 2rem;
                width: 100%;
                max-width: 400px;
            }
            
            .auth-logo {
                text-align: center;
                margin-bottom: 2rem;
            }
            
            .auth-logo i {
                font-size: 3rem;
                color: var(--primary-color);
                margin-bottom: 0.5rem;
            }
            
            .auth-logo h3 {
                color: var(--primary-color);
                font-weight: 700;
            }
            
            .form-floating > .form-control {
                border-radius: 0.5rem;
            }
            
            .btn-primary {
                border-radius: 0.5rem;
                padding: 0.75rem 1.5rem;
                font-weight: 600;
            }
            
            .alert {
                border: none;
                border-radius: 0.5rem;
            }
            
            .page-header {
                margin-bottom: 2rem;
                padding-bottom: 1rem;
                border-bottom: 2px solid #e9ecef;
            }
            
            .card {
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                border: 1px solid rgba(0, 0, 0, 0.125);
                border-radius: 0.75rem;
            }
            
            .table th {
                background-color: #f8f9fa;
                border-top: none;
                font-weight: 600;
                color: #495057;
            }
            
            .badge {
                font-size: 0.75em;
            }
            
            .modal-header {
                background-color: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
            }
            
            .form-label {
                font-weight: 500;
                color: #495057;
            }
        </style>
    </head>
    <body>
        <div class="main-container">
            <?php echo $content; ?>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// Renderizar vistas con layout principal
function renderPage($viewPath, $pageTitle, $basePath, array $data = []) {
    $basePath = rtrim($basePath, '/');
    extract($data, EXTR_SKIP);
    ob_start();
    include __DIR__ . '/../src/UI/Views/layouts/header.php';
    include $viewPath;
    include __DIR__ . '/../src/UI/Views/layouts/footer.php';
    return ob_get_clean();
}

function getPdoConnection(array $dbConfig): PDO
{
    return new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['options']
    );
}

function getActiveTerm(PDO $pdo): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM terms WHERE status = 'active' LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function isEnrollmentWindowOpen(array $term): bool
{
    $now = new DateTimeImmutable();
    if (empty($term['enrollment_start']) || empty($term['enrollment_end'])) {
        return true;
    }
    $start = new DateTimeImmutable($term['enrollment_start']);
    $end = new DateTimeImmutable($term['enrollment_end']);
    return $now >= $start && $now <= $end;
}

function getCompletedSubjectIds(PDO $pdo, int $studentId): array
{
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.subject_id
        FROM enrollments e
        INNER JOIN courses c ON c.id = e.course_id
        WHERE e.student_id = :student_id
          AND e.status = 'completed'
    ");
    $stmt->execute(['student_id' => $studentId]);
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'subject_id');
}

function getEligibleSubjectIds(PDO $pdo, int $studentId): array
{
    $stmt = $pdo->prepare("
        SELECT s.id
        FROM subjects s
        WHERE s.is_active = 1
          AND NOT EXISTS (
              SELECT 1
              FROM subject_prerequisites sp
              LEFT JOIN student_subject_history ssh
                ON ssh.subject_id = sp.prerequisite_subject_id
               AND ssh.student_id = :student_id
               AND ssh.passed = 1
              WHERE sp.subject_id = s.id
                AND ssh.id IS NULL
          )
        ORDER BY s.name ASC
    ");
    $stmt->execute(['student_id' => $studentId]);
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');
}

function getStudentEnrollments(PDO $pdo, int $studentId): array
{
    $stmt = $pdo->prepare("
        SELECT e.id,
               e.status,
               e.enrollment_at,
               c.schedule_label,
               s.name AS subject_name,
               t.name AS term_name
        FROM enrollments e
        INNER JOIN courses c ON c.id = e.course_id
        INNER JOIN subjects s ON s.id = c.subject_id
        INNER JOIN terms t ON t.id = c.term_id
        WHERE e.student_id = :student_id
        ORDER BY e.enrollment_at DESC
    ");
    $stmt->execute(['student_id' => $studentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStudentAvailableCourses(PDO $pdo, int $studentId, int $termId, array $eligibleSubjectIds): array
{
    if (!$eligibleSubjectIds) {
        return [];
    }

    $eligibleList = implode(',', array_map('intval', $eligibleSubjectIds));

    $stmt = $pdo->prepare("
        SELECT c.id,
               c.schedule_label,
               c.group_name,
               c.modality,
               s.name AS subject_name,
               m.name AS module_name
        FROM courses c
        INNER JOIN subjects s ON s.id = c.subject_id
        LEFT JOIN modules m ON m.id = s.module_id
        WHERE c.term_id = :term_id
          AND c.status = 'open'
          AND c.subject_id IN ({$eligibleList})
          AND c.id NOT IN (
              SELECT course_id FROM enrollments WHERE student_id = :student_id
          )
        GROUP BY c.id
        ORDER BY s.name ASC
    ");
    $stmt->execute([
        'term_id' => $termId,
        'student_id' => $studentId
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createEnrollment(PDO $pdo, int $studentId, int $courseId, ?int $enrolledBy, bool $overrideSeriation, bool $overrideSchedule): void
{
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = :course_id LIMIT 1");
    $stmt->execute(['course_id' => $courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        throw new Exception('Curso no encontrado.');
    }

    $termStmt = $pdo->prepare("SELECT * FROM terms WHERE id = :id LIMIT 1");
    $termStmt->execute(['id' => $course['term_id']]);
    $term = $termStmt->fetch(PDO::FETCH_ASSOC);

    if (!$term || $term['status'] !== 'active') {
        throw new Exception('No hay un periodo académico activo para este curso.');
    }

    if (!isEnrollmentWindowOpen($term)) {
        throw new Exception('La ventana de inscripción está cerrada.');
    }

    $existsStmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE student_id = :student_id AND course_id = :course_id LIMIT 1");
    $existsStmt->execute([
        'student_id' => $studentId,
        'course_id' => $courseId
    ]);
    if ($existsStmt->fetch()) {
        throw new Exception('Ya estás inscrito en este curso.');
    }

    if (!$overrideSeriation) {
        $eligibleSubjects = getEligibleSubjectIds($pdo, $studentId);
        if (!in_array($course['subject_id'], $eligibleSubjects, true)) {
            throw new Exception('No cumples con la seriación requerida para esta materia.');
        }
    }

    $insertStmt = $pdo->prepare("
        INSERT INTO enrollments (student_id, course_id, enrollment_at, status, payment_status, total_amount, paid_amount)
        VALUES (:student_id, :course_id, NOW(), 'active', 'pending', 0, 0)
    ");
    $insertStmt->execute([
        'student_id' => $studentId,
        'course_id' => $courseId,
    ]);
}

// Función para crear el formulario de login
function createLoginForm($error = null, $success = null, $basePath = '/Control-Escolar') {
    ob_start();
    ?>
    <div class="auth-card">
        <div class="auth-logo">
            <i class="fas fa-graduation-cap"></i>
            <h3>Control Escolar</h3>
            <p class="text-muted">Christian LMS</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo $basePath; ?>/auth/login">
            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>
        
        <div class="text-center mt-3">
            <small class="text-muted">
                Usuario por defecto: admin@christianlms.com<br>
                Contraseña: password
            </small>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Función para crear el dashboard principal
function createDashboard($basePath = '/Control-Escolar', array $dashboardData = []) {
    $userName = $_SESSION['user_name'] ?? 'Usuario';
    $userRole = $_SESSION['user_role'] ?? 'student';
    $stats = $dashboardData['stats'] ?? [
        'courses' => 0,
        'students' => 0,
        'teachers' => 0,
        'enrollments' => 0
    ];
    
    ob_start();
    ?>
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between bg-white p-3 rounded-3 shadow-sm mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-tachometer-alt"></i> Panel de Control</h2>
                <p class="text-muted mb-0">Bienvenido al Sistema de Gestión Escolar Christian LMS</p>
            </div>
            <div class="text-end">
                <div class="mb-2">
                    <strong><?php echo htmlspecialchars($userName); ?></strong>
                    <span class="badge bg-info ms-2"><?php echo ucfirst($userRole); ?></span>
                </div>
                <a href="<?php echo $basePath; ?>/logout" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-book fa-2x text-primary mb-2"></i>
                        <h5 class="card-title">Cursos</h5>
                        <p class="card-text display-6 text-primary"><?= (int) $stats['courses'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x text-success mb-2"></i>
                        <h5 class="card-title">Estudiantes</h5>
                        <p class="card-text display-6 text-success"><?= (int) $stats['students'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-chalkboard-teacher fa-2x text-warning mb-2"></i>
                        <h5 class="card-title">Profesores</h5>
                        <p class="card-text display-6 text-warning"><?= (int) $stats['teachers'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-clipboard-list fa-2x text-info mb-2"></i>
                        <h5 class="card-title">Inscripciones</h5>
                        <p class="card-text display-6 text-info"><?= (int) $stats['enrollments'] ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($userRole === 'teacher'): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chalkboard-teacher"></i> Mis cursos asignados</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($dashboardData['teacherCourses'])): ?>
                        <p class="text-muted">No tienes cursos asignados en el periodo activo.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Materia</th>
                                        <th>Horario</th>
                                        <th>Periodo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dashboardData['teacherCourses'] as $course): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($course['subject_name']); ?></td>
                                            <td><?php echo htmlspecialchars($course['schedule_label'] ?? 'Por definir'); ?></td>
                                            <td><?php echo htmlspecialchars($course['term_name'] ?? 'Sin periodo'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo $basePath; ?>/courses" class="btn btn-outline-primary w-100">
                                <i class="fas fa-book"></i><br>Gestionar Cursos
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo $basePath; ?>/enrollments" class="btn btn-outline-success w-100">
                                <i class="fas fa-user-plus"></i><br>Gestionar Inscripciones
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo $basePath; ?>/subjects" class="btn btn-outline-warning w-100">
                                <i class="fas fa-list"></i><br>Gestionar Materias
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Recent Activity -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock"></i> Actividad Reciente</h5>
            </div>
            <div class="card-body">
                <div class="text-center text-muted">
                    <i class="fas fa-info-circle"></i>
                    No hay actividad reciente para mostrar.
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .container-fluid {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .card-header {
            border-bottom: 1px solid #dee2e6;
        }
        
        .display-6 {
            font-size: 2.5rem;
            font-weight: 300;
        }
        
        .btn-outline-primary:hover,
        .btn-outline-success:hover,
        .btn-outline-warning:hover {
            color: white;
        }
    </style>
    <?php
    return ob_get_clean();
}

// Procesar formularios de autenticación
function processLogin($basePath, $dbConfig) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['email'] ?? '') && ($_POST['password'] ?? '')) {
        try {
            $pdo = new PDO(
                "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
                $dbConfig['username'],
                $dbConfig['password'],
                $dbConfig['options']
            );
            
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role, status FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login exitoso
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                header('Location: ' . $basePath . '/dashboard');
                exit();
            } else {
                $_SESSION['error'] = 'Credenciales incorrectas. Verifica tu email y contraseña.';
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error de conexión: ' . $e->getMessage();
        }
    }
}

// Obtener la ruta actual
$route = getCurrentRoute();

// Procesar login si se envió el formulario
if ($route['action'] === 'auth' && ($route['segments'][1] ?? '') === 'login') {
    processLogin($route['base_path'], $dbConfig);
}

// Manejar las rutas
switch ($route['action']) {
    case '':
    case 'index':
        if (isAuthenticated()) {
            header('Location: ' . $route['base_path'] . '/dashboard');
        } else {
            header('Location: ' . $route['base_path'] . '/login');
        }
        exit();
        break;
        
    case 'login':
        redirectIfAuthenticated($route['base_path']);
        
        $error = $_SESSION['error'] ?? null;
        $success = $_SESSION['success'] ?? null;
        
        // Limpiar mensajes de sesión
        unset($_SESSION['error']);
        unset($_SESSION['success']);
        
        // Mostrar formulario de login
        echo loadLayout(
            createLoginForm($error, $success, $route['base_path']),
            'Iniciar Sesión - Control Escolar',
            $route['base_path']
        );
        break;

    case 'auth':
        if (($route['segments'][1] ?? '') === 'login') {
            redirectIfAuthenticated($route['base_path']);

            $error = $_SESSION['error'] ?? null;
            $success = $_SESSION['success'] ?? null;

            unset($_SESSION['error']);
            unset($_SESSION['success']);

            echo loadLayout(
                createLoginForm($error, $success, $route['base_path']),
                'Iniciar Sesión - Control Escolar',
                $route['base_path']
            );
            break;
        }

        http_response_code(404);
        echo loadLayout('
            <div class="text-center">
                <h1><i class="fas fa-exclamation-triangle text-warning"></i></h1>
                <h3>Página No Encontrada</h3>
                <p class="text-muted">La página que busca no existe.</p>
                <a href="' . $route['base_path'] . '/dashboard" class="btn btn-primary">
                    <i class="fas fa-home"></i> Ir al Dashboard
                </a>
            </div>
        ', 'Página No Encontrada - Control Escolar', $route['base_path']);
        break;
        
    case 'dashboard':
        requireAuth($route['base_path']);
        if (($_SESSION['user_role'] ?? '') === 'student') {
            header('Location: ' . $route['base_path'] . '/enrollments');
            exit();
        }

        $dashboardData = [
            'stats' => [
                'courses' => 0,
                'students' => 0,
                'teachers' => 0,
                'enrollments' => 0
            ],
            'teacherCourses' => []
        ];

        $pdo = getPdoConnection($dbConfig);
        $activeTerm = getActiveTerm($pdo);

        $dashboardData['stats']['courses'] = (int) $pdo->query("SELECT COUNT(*) FROM courses WHERE status = 'open'")->fetchColumn();
        $dashboardData['stats']['students'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
        $dashboardData['stats']['teachers'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();

        if ($activeTerm) {
            $enrollmentStmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM enrollments e
                INNER JOIN courses c ON c.id = e.course_id
                WHERE c.term_id = :term_id
            ");
            $enrollmentStmt->execute(['term_id' => $activeTerm['id']]);
            $dashboardData['stats']['enrollments'] = (int) $enrollmentStmt->fetchColumn();
        }

        if (($_SESSION['user_role'] ?? '') === 'teacher') {
            $teacherId = (int) ($_SESSION['user_id'] ?? 0);
            $teacherCoursesStmt = $pdo->prepare("
                SELECT c.id,
                       c.schedule_label,
                       s.name AS subject_name,
                       t.name AS term_name
                FROM course_teachers ct
                INNER JOIN courses c ON c.id = ct.course_id
                INNER JOIN subjects s ON s.id = c.subject_id
                INNER JOIN terms t ON t.id = c.term_id
                WHERE ct.teacher_id = :teacher_id
                ORDER BY t.term_start DESC, s.name ASC
            ");
            $teacherCoursesStmt->execute(['teacher_id' => $teacherId]);
            $dashboardData['teacherCourses'] = $teacherCoursesStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        echo loadLayout(
            createDashboard($route['base_path'], $dashboardData),
            'Dashboard - Control Escolar',
            $route['base_path']
        );
        break;
        
    case 'courses':
        requireAuth($route['base_path']);
        requireAdmin($route['base_path']);
        $pdo = getPdoConnection($dbConfig);
        $errorMessage = null;
        $successMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            try {
                if ($action === 'create_course') {
                    $groupName = trim($_POST['group_name'] ?? '');
                    $subjectId = (int) ($_POST['subject_id'] ?? 0);
                    $termId = (int) ($_POST['term_id'] ?? 0);
                    $status = $_POST['status'] ?? 'draft';
                    $scheduleLabel = trim($_POST['schedule_label'] ?? '');
                    $modality = trim($_POST['modality'] ?? '');
                    $capacity = (int) ($_POST['capacity'] ?? 0);

                    if ($groupName === '' || !$subjectId || !$termId) {
                        throw new Exception('Completa los campos obligatorios del curso.');
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO courses
                            (term_id, subject_id, group_name, schedule_label, modality, capacity, status)
                        VALUES
                            (:term_id, :subject_id, :group_name, :schedule_label, :modality, :capacity, :status)
                    ");
                    $stmt->execute([
                        'term_id' => $termId,
                        'subject_id' => $subjectId,
                        'group_name' => $groupName,
                        'schedule_label' => $scheduleLabel ?: null,
                        'modality' => $modality ?: null,
                        'capacity' => $capacity ?: null,
                        'status' => $status
                    ]);

                    $successMessage = 'Curso creado correctamente.';
                } elseif ($action === 'update_course') {
                    $courseId = (int) ($_POST['id'] ?? 0);
                    $groupName = trim($_POST['group_name'] ?? '');
                    $subjectId = (int) ($_POST['subject_id'] ?? 0);
                    $termId = (int) ($_POST['term_id'] ?? 0);
                    $status = $_POST['status'] ?? 'draft';
                    $scheduleLabel = trim($_POST['schedule_label'] ?? '');
                    $modality = trim($_POST['modality'] ?? '');
                    $capacity = (int) ($_POST['capacity'] ?? 0);

                    if (!$courseId || $groupName === '' || !$subjectId || !$termId) {
                        throw new Exception('Completa los campos obligatorios del curso.');
                    }

                    $stmt = $pdo->prepare("
                        UPDATE courses
                        SET term_id = :term_id,
                            subject_id = :subject_id,
                            group_name = :group_name,
                            schedule_label = :schedule_label,
                            modality = :modality,
                            capacity = :capacity,
                            status = :status
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'id' => $courseId,
                        'term_id' => $termId,
                        'subject_id' => $subjectId,
                        'group_name' => $groupName,
                        'schedule_label' => $scheduleLabel ?: null,
                        'modality' => $modality ?: null,
                        'capacity' => $capacity ?: null,
                        'status' => $status
                    ]);

                    $successMessage = 'Curso actualizado correctamente.';
                } elseif ($action === 'delete_course') {
                    $courseId = (int) ($_POST['id'] ?? 0);
                    if (!$courseId) {
                        throw new Exception('Curso inválido.');
                    }
                    $pdo->prepare("DELETE FROM courses WHERE id = :id")->execute(['id' => $courseId]);
                    $successMessage = 'Curso eliminado correctamente.';
                }
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => trim($_GET['search'] ?? '')
        ];

        $conditions = [];
        $params = [];
        if ($filters['status'] !== '') {
            $conditions[] = 'c.status = :status';
            $params['status'] = $filters['status'];
        }
        if ($filters['search'] !== '') {
            $conditions[] = '(c.group_name LIKE :search OR s.name LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

        $coursesStmt = $pdo->prepare("
            SELECT c.*,
                   s.name AS subject_name,
                   t.name AS term_name,
                   COUNT(e.id) AS enrollment_count
            FROM courses c
            LEFT JOIN subjects s ON s.id = c.subject_id
            LEFT JOIN terms t ON t.id = c.term_id
            LEFT JOIN enrollments e ON e.course_id = c.id
            {$whereClause}
            GROUP BY c.id
            ORDER BY c.id DESC
        ");
        $coursesStmt->execute($params);
        $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

        $subjectsStmt = $pdo->query("SELECT id, name FROM subjects ORDER BY name ASC");
        $subjects = $subjectsStmt->fetchAll(PDO::FETCH_ASSOC);

        $termsStmt = $pdo->query("SELECT id, name FROM terms ORDER BY term_start DESC");
        $terms = $termsStmt->fetchAll(PDO::FETCH_ASSOC);

        echo renderPage(
            __DIR__ . '/../src/UI/Views/courses/index.php',
            'Cursos - Control Escolar',
            $route['base_path'],
            [
                'courses' => $courses,
                'subjects' => $subjects,
                'terms' => $terms,
                'filters' => $filters,
                'errorMessage' => $errorMessage,
                'successMessage' => $successMessage
            ]
        );
        break;
        
    case 'enrollments':
        requireAuth($route['base_path']);
        $pdo = getPdoConnection($dbConfig);
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $userRole = $_SESSION['user_role'] ?? '';
        $activeTerm = getActiveTerm($pdo);
        $enrollmentWindowOpen = $activeTerm ? isEnrollmentWindowOpen($activeTerm) : false;

        $errorMessage = null;
        $successMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $courseId = (int) ($_POST['course_id'] ?? 0);
                if (!$courseId) {
                    throw new Exception('Selecciona un curso válido.');
                }

                $enrolledBy = null;
                $targetStudentId = $userId;
                $overrideSeriation = false;
                $overrideSchedule = false;

                if ($userRole !== 'student') {
                    $enrolledBy = $userId;
                    $targetStudentId = (int) ($_POST['student_id'] ?? 0);
                    if (!$targetStudentId) {
                        throw new Exception('Selecciona un estudiante válido.');
                    }
                    $overrideSeriation = !empty($_POST['override_seriation']);
                    $overrideSchedule = !empty($_POST['override_schedule']);
                }

                createEnrollment($pdo, $targetStudentId, $courseId, $enrolledBy, $overrideSeriation, $overrideSchedule);
                $successMessage = 'Inscripción registrada correctamente.';
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        if ($userRole === 'student') {
            $eligibleSubjects = $activeTerm ? getEligibleSubjectIds($pdo, $userId) : [];
            $availableCourses = $activeTerm ? getStudentAvailableCourses($pdo, $userId, (int) $activeTerm['id'], $eligibleSubjects) : [];
            $studentEnrollments = getStudentEnrollments($pdo, $userId);

            echo renderPage(
                __DIR__ . '/../src/UI/Views/enrollments/index.php',
                'Mis Inscripciones - Control Escolar',
                $route['base_path'],
                [
                    'activePeriod' => $activeTerm,
                    'enrollmentWindowOpen' => $enrollmentWindowOpen,
                    'availableCourses' => $availableCourses,
                    'studentEnrollments' => $studentEnrollments,
                    'errorMessage' => $errorMessage,
                    'successMessage' => $successMessage
                ]
            );
            break;
        }

        if ($userRole !== 'admin') {
            header('Location: ' . $route['base_path'] . '/dashboard');
            exit();
        }

        $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE role = 'student' AND status = 'active' ORDER BY name ASC");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $coursesStmt = $pdo->prepare("
            SELECT c.id,
               s.name AS subject_name,
               m.name AS module_name,
               c.schedule_label,
               c.group_name
            FROM courses c
            INNER JOIN subjects s ON s.id = c.subject_id
            LEFT JOIN modules m ON m.id = s.module_id
            WHERE c.term_id = :term_id
              AND c.status = 'open'
            ORDER BY s.name ASC
        ");
        $coursesStmt->execute(['term_id' => $activeTerm['id'] ?? 0]);
        $adminCourses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

        echo renderPage(
            __DIR__ . '/../src/UI/Views/enrollments/admin.php',
            'Gestión de Inscripciones - Control Escolar',
            $route['base_path'],
            [
                'activePeriod' => $activeTerm,
                'enrollmentWindowOpen' => $enrollmentWindowOpen,
                'students' => $students,
                'adminCourses' => $adminCourses,
                'errorMessage' => $errorMessage,
                'successMessage' => $successMessage
            ]
        );
        break;
        
    case 'subjects':
        requireAuth($route['base_path']);
        requireAdmin($route['base_path']);
        $pdo = getPdoConnection($dbConfig);
        $errorMessage = null;
        $successMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            try {
                if ($action === 'create_subject') {
                    $name = trim($_POST['name'] ?? '');
                    $code = trim($_POST['code'] ?? '');
                    $moduleId = (int) ($_POST['module_id'] ?? 0);
                    $description = trim($_POST['description'] ?? '');

                    if ($name === '' || $code === '') {
                        throw new Exception('Completa los campos obligatorios de la materia.');
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO subjects
                            (code, name, module_id, description, created_at, updated_at)
                        VALUES
                            (:code, :name, :module_id, :description, NOW(), NOW())
                    ");
                    $stmt->execute([
                        'code' => $code,
                        'name' => $name,
                        'module_id' => $moduleId ?: null,
                        'description' => $description
                    ]);

                    $successMessage = 'Materia creada correctamente.';
                } elseif ($action === 'update_subject') {
                    $subjectId = (int) ($_POST['id'] ?? 0);
                    $name = trim($_POST['name'] ?? '');
                    $moduleId = (int) ($_POST['module_id'] ?? 0);
                    $description = trim($_POST['description'] ?? '');

                    if (!$subjectId || $name === '') {
                        throw new Exception('Completa los campos obligatorios de la materia.');
                    }

                    $stmt = $pdo->prepare("
                        UPDATE subjects
                        SET name = :name,
                            module_id = :module_id,
                            description = :description,
                            updated_at = NOW()
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'id' => $subjectId,
                        'name' => $name,
                        'module_id' => $moduleId ?: null,
                        'description' => $description
                    ]);

                    $successMessage = 'Materia actualizada correctamente.';
                } elseif ($action === 'delete_subject') {
                    $subjectId = (int) ($_POST['id'] ?? 0);
                    if (!$subjectId) {
                        throw new Exception('Materia inválida.');
                    }
                    $pdo->prepare("DELETE FROM subjects WHERE id = :id")->execute(['id' => $subjectId]);
                    $successMessage = 'Materia eliminada correctamente.';
                }
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        $subjectsStmt = $pdo->query("
            SELECT s.*,
                   m.name AS module_name,
                   COUNT(DISTINCT c.id) AS course_count
            FROM subjects s
            LEFT JOIN modules m ON m.id = s.module_id
            LEFT JOIN courses c ON c.subject_id = s.id
            GROUP BY s.id
            ORDER BY s.name ASC
        ");
        $subjects = $subjectsStmt->fetchAll(PDO::FETCH_ASSOC);

        $modulesStmt = $pdo->query("SELECT id, name FROM modules ORDER BY sort_order ASC, name ASC");
        $modules = $modulesStmt->fetchAll(PDO::FETCH_ASSOC);

        echo renderPage(
            __DIR__ . '/../src/UI/Views/subjects/index.php',
            'Materias - Control Escolar',
            $route['base_path'],
            [
                'subjects' => $subjects,
                'modules' => $modules,
                'errorMessage' => $errorMessage,
                'successMessage' => $successMessage
            ]
        );
        break;

    case 'periods':
        requireAuth($route['base_path']);
        requireAdmin($route['base_path']);
        $pdo = getPdoConnection($dbConfig);
        $errorMessage = null;
        $successMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            try {
                if ($action === 'create_period') {
                    $name = trim($_POST['name'] ?? '');
                    $code = trim($_POST['code'] ?? '');
                    $enrollmentStart = $_POST['enrollment_start'] ?? null;
                    $enrollmentEnd = $_POST['enrollment_end'] ?? null;
                    $startDate = $_POST['start_date'] ?? null;
                    $endDate = $_POST['end_date'] ?? null;
                    $status = $_POST['status'] ?? 'draft';

                    if ($name === '' || $code === '') {
                        throw new Exception('Completa los campos obligatorios del periodo.');
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO terms
                            (code, name, enrollment_start, enrollment_end, term_start, term_end, status)
                        VALUES
                            (:code, :name, :enrollment_start, :enrollment_end, :term_start, :term_end, :status)
                    ");
                    $stmt->execute([
                        'code' => $code,
                        'name' => $name,
                        'enrollment_start' => $enrollmentStart ?: null,
                        'enrollment_end' => $enrollmentEnd ?: null,
                        'term_start' => $startDate ?: null,
                        'term_end' => $endDate ?: null,
                        'status' => $status
                    ]);
                    $successMessage = 'Periodo académico creado correctamente.';
                } elseif ($action === 'update_period') {
                    $periodId = (int) ($_POST['id'] ?? 0);
                    $name = trim($_POST['name'] ?? '');
                    $enrollmentStart = $_POST['enrollment_start'] ?? null;
                    $enrollmentEnd = $_POST['enrollment_end'] ?? null;
                    $startDate = $_POST['start_date'] ?? null;
                    $endDate = $_POST['end_date'] ?? null;
                    $status = $_POST['status'] ?? 'draft';

                    if (!$periodId || $name === '') {
                        throw new Exception('Completa los campos obligatorios del periodo.');
                    }

                    $stmt = $pdo->prepare("
                        UPDATE terms
                        SET name = :name,
                            enrollment_start = :enrollment_start,
                            enrollment_end = :enrollment_end,
                            term_start = :term_start,
                            term_end = :term_end,
                            status = :status
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'id' => $periodId,
                        'name' => $name,
                        'enrollment_start' => $enrollmentStart ?: null,
                        'enrollment_end' => $enrollmentEnd ?: null,
                        'term_start' => $startDate ?: null,
                        'term_end' => $endDate ?: null,
                        'status' => $status
                    ]);
                    $successMessage = 'Periodo académico actualizado correctamente.';
                } elseif ($action === 'delete_period') {
                    $periodId = (int) ($_POST['id'] ?? 0);
                    if (!$periodId) {
                        throw new Exception('Periodo inválido.');
                    }
                    $pdo->prepare("DELETE FROM terms WHERE id = :id")->execute(['id' => $periodId]);
                    $successMessage = 'Periodo académico eliminado correctamente.';
                }
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        $periodsStmt = $pdo->query("SELECT * FROM terms ORDER BY term_start DESC");
        $periods = $periodsStmt->fetchAll(PDO::FETCH_ASSOC);

        echo renderPage(
            __DIR__ . '/../src/UI/Views/periods/index.php',
            'Periodos Académicos - Control Escolar',
            $route['base_path'],
            [
                'periods' => $periods,
                'errorMessage' => $errorMessage,
                'successMessage' => $successMessage
            ]
        );
        break;
        
    case 'logout':
        session_destroy();
        session_start();
        $_SESSION['success'] = 'Sesión cerrada exitosamente';
        header('Location: ' . $route['base_path'] . '/login');
        exit();
        break;
        
    default:
        // Página no encontrada
        http_response_code(404);
        echo loadLayout('
            <div class="text-center">
                <h1><i class="fas fa-exclamation-triangle text-warning"></i></h1>
                <h3>Página No Encontrada</h3>
                <p class="text-muted">La página que busca no existe.</p>
                <a href="' . $route['base_path'] . '/dashboard" class="btn btn-primary">
                    <i class="fas fa-home"></i> Ir al Dashboard
                </a>
            </div>
        ', 'Página No Encontrada - Control Escolar', $route['base_path']);
        break;
}
?>
