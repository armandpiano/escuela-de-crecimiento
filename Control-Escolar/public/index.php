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

// Configuración opcional de debug del router (false por defecto)
if (!defined('ROUTER_DEBUG')) {
    define('ROUTER_DEBUG', false);
}

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
    if (!defined('BASE_PATH')) {
        $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $basePath = rtrim($basePath, '/');
        if ($basePath === '/' || $basePath === '.') {
            $basePath = '';
        }
        define('BASE_PATH', $basePath);
    }

    return BASE_PATH;
}

// Función para obtener la ruta solicitada
function getCurrentRoute() {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = getBasePath();

    // Remover el directorio base si existe al inicio
    if ($basePath !== '' && strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }

    $route = trim($path, '/');
    if ($route === '' || $route === 'index.php') {
        $route = isAuthenticated() ? 'dashboard' : 'login';
    }

    // Dividir la ruta en segmentos
    $segments = $route === '' ? [] : explode('/', $route);
    
    return [
        'path' => $route,
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
function loadLayout($content, $title = 'Sistema Escuela de Crecimiento', $basePath = null) {
    $basePath = $basePath ?? getBasePath();
    $faviconPath = $basePath !== '' ? $basePath . '/public/uploads/logo-afc.png' : '/public/uploads/logo-afc.png';
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
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
        <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($faviconPath); ?>">
        <link rel="apple-touch-icon" href="<?php echo htmlspecialchars($faviconPath); ?>">
        <link href="<?php echo htmlspecialchars($basePath); ?>/assets/css/ui-premium.css" rel="stylesheet">
    </head>
    <body class="app-body">
        <div class="main-container auth-page">
            <?php echo $content; ?>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="<?php echo htmlspecialchars($basePath); ?>/assets/js/ui-premium.js"></script>
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

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name = :table
        LIMIT 1
    ');
    $stmt->execute(['table' => $table]);
    return (bool) $stmt->fetchColumn();
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare('
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = :table
          AND column_name = :column
    ');
    $stmt->execute(['table' => $table, 'column' => $column]);
    return (int) $stmt->fetchColumn() > 0;
}

function generateModuleCode(PDO $pdo, string $name): string
{
    $base = strtoupper(preg_replace('/[^A-Z0-9]/', '', $name));
    $base = substr($base ?: 'MOD', 0, 6);
    $code = $base;
    $suffix = 1;

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM modules WHERE code = :code');
    while (true) {
        $stmt->execute(['code' => $code]);
        if ((int) $stmt->fetchColumn() === 0) {
            break;
        }
        $code = substr($base . $suffix, 0, 10);
        $suffix++;
    }

    return $code;
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
          AND s.is_active = 1
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

function generateTemporaryPassword(int $length = 12): string
{
    $lower = 'abcdefghijklmnopqrstuvwxyz';
    $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $digits = '0123456789';
    $all = $lower . $upper . $digits;

    $password = [
        $lower[random_int(0, strlen($lower) - 1)],
        $upper[random_int(0, strlen($upper) - 1)],
        $digits[random_int(0, strlen($digits) - 1)]
    ];

    while (count($password) < $length) {
        $password[] = $all[random_int(0, strlen($all) - 1)];
    }

    shuffle($password);
    return implode('', $password);
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


function createLoginForm($error = null, $success = null, $basePath = null)
{
    $basePath = $basePath ?? getBasePath();
    ob_start();

    // Evita duplicar estilos si renderizas el form más de una vez
    static $authCssInjected = false;
    ?>

    <?php if (!$authCssInjected): $authCssInjected = true; ?>
        <style>
            :root{
                --brand: #229499;
                --brand-2: #1b6f73;
                --ink: #0f172a;
                --muted: #64748b;
                --card: rgba(255,255,255,.92);
                --stroke: rgba(15,23,42,.08);
                --shadow: 0 25px 60px rgba(2,6,23,.18);
                --radius: 18px;
            }

            /* Contenedor externo sugerido (si tienes un wrapper, puedes usarlo) */
            .auth-wrap{
                min-height: calc(100vh - 80px);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 32px 16px;
                position: relative;
                overflow: hidden;
            }

            /* Fondo decorativo elegante */
            .auth-wrap::before{
                content:"";
                position:absolute;
                inset:-60px;
                background:
                    radial-gradient(600px 260px at 20% 20%, rgba(34,148,153,.25), transparent 60%),
                    radial-gradient(520px 240px at 85% 35%, rgba(27,111,115,.18), transparent 55%),
                    radial-gradient(520px 260px at 30% 90%, rgba(99,102,241,.14), transparent 55%),
                    linear-gradient(180deg, rgba(2,6,23,.04), rgba(2,6,23,.00));
                filter: blur(0px);
                z-index: 0;
            }

            .auth-card{
                width: 100%;
                max-width: 460px;
                border-radius: var(--radius);
                background: var(--card);
                border: 1px solid var(--stroke);
                box-shadow: var(--shadow);
                position: relative;
                z-index: 1;
                overflow: hidden;
                backdrop-filter: blur(10px);
            }

            .auth-card__top{
                padding: 22px 24px 16px;
                border-bottom: 1px solid var(--stroke);
                background:
                    linear-gradient(135deg, rgba(34,148,153,.10), rgba(34,148,153,.04));
            }

            .auth-brand{
                display: flex;
                align-items: center;
                gap: 14px;
            }

            .auth-brand__logo{
                width: 52px;
                height: 52px;
                border-radius: 14px;
                display: grid;
                place-items: center;
                background: rgba(34,148,153,.12);
                border: 1px solid rgba(34,148,153,.18);
                overflow: hidden;
                flex: 0 0 auto;
            }

            .auth-brand__logo img{
                width: 100%;
                height: 100%;
                object-fit: contain;
                padding: 8px;
            }

            .auth-brand__title{
                margin: 0;
                font-weight: 800;
                letter-spacing: .2px;
                color: var(--ink);
                line-height: 1.15;
                font-size: 1.05rem;
            }

            .auth-brand__subtitle{
                margin: 2px 0 0;
                color: var(--muted);
                font-size: .9rem;
            }

            .auth-card__body{
                padding: 22px 24px 24px;
            }

            .auth-alert{
                border-radius: 12px;
                border: 1px solid var(--stroke);
                padding: 12px 14px;
                display: flex;
                gap: 10px;
                align-items: flex-start;
                margin-bottom: 14px;
            }
            .auth-alert i{ margin-top: 1px; }

            .auth-alert--danger{
                background: rgba(239,68,68,.08);
                border-color: rgba(239,68,68,.18);
                color: #991b1b;
            }
            .auth-alert--success{
                background: rgba(34,197,94,.10);
                border-color: rgba(34,197,94,.18);
                color: #166534;
            }

            .auth-label{
                font-size: .9rem;
                font-weight: 700;
                color: #1f2937;
                margin-bottom: 6px;
            }

            /* Input con icono */
            .auth-input{
                position: relative;
            }
            .auth-input .form-control{
                padding-left: 42px;
                border-radius: 14px;
                border: 1px solid rgba(15,23,42,.12);
                background: rgba(255,255,255,.9);
                height: 46px;
                transition: all .18s ease;
            }
            .auth-input .form-control:focus{
                border-color: rgba(34,148,153,.55);
                box-shadow: 0 0 0 .2rem rgba(34,148,153,.16);
            }

            .auth-input__icon{
                position: absolute;
                left: 14px;
                top: 50%;
                transform: translateY(-50%);
                color: rgba(15,23,42,.55);
                font-size: 1.05rem;
                pointer-events: none;
            }

            /* Toggle password */
            .auth-input__toggle{
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                border: 0;
                background: transparent;
                width: 38px;
                height: 38px;
                border-radius: 12px;
                display: grid;
                place-items: center;
                color: rgba(15,23,42,.6);
                transition: background .18s ease, color .18s ease;
            }
            .auth-input__toggle:hover{
                background: rgba(15,23,42,.06);
                color: rgba(15,23,42,.85);
            }

            .auth-row{
                display:flex;
                align-items:center;
                justify-content: space-between;
                gap: 12px;
                margin: 10px 0 16px;
                flex-wrap: wrap;
            }

            .auth-link{
                text-decoration: none;
                font-weight: 700;
                color: var(--brand);
                font-size: .92rem;
            }
            .auth-link:hover{ color: var(--brand-2); text-decoration: underline; }

            .auth-btn{
                height: 48px;
                border-radius: 14px;
                border: 0;
                font-weight: 800;
                letter-spacing: .2px;
                background: linear-gradient(135deg, var(--brand), var(--brand-2));
                box-shadow: 0 12px 25px rgba(34,148,153,.22);
                transition: transform .12s ease, box-shadow .12s ease, filter .12s ease;
            }
            .auth-btn:hover{
                transform: translateY(-1px);
                filter: brightness(1.02);
                box-shadow: 0 16px 30px rgba(34,148,153,.26);
            }
            .auth-btn:active{
                transform: translateY(0px);
                box-shadow: 0 10px 22px rgba(34,148,153,.22);
            }

            .auth-footer{
                margin-top: 16px;
                font-size: .85rem;
                color: var(--muted);
                text-align: center;
            }

            @media (max-width: 420px){
                .auth-card__top, .auth-card__body{ padding-left: 18px; padding-right: 18px; }
            }
        </style>

        <script>
            // Toggle password (sin dependencias)
            document.addEventListener('click', function(e){
                const btn = e.target.closest('[data-toggle-password]');
                if(!btn) return;
                const inputId = btn.getAttribute('data-toggle-password');
                const input = document.getElementById(inputId);
                if(!input) return;

                const isPass = input.type === 'password';
                input.type = isPass ? 'text' : 'password';

                const icon = btn.querySelector('i');
                if(icon){
                    icon.classList.toggle('bi-eye', !isPass);
                    icon.classList.toggle('bi-eye-slash', isPass);
                }
            });
        </script>
    <?php endif; ?>

    <div class="auth-wrap">
        <div class="auth-card">

            <div class="auth-card__top">
                <div class="auth-brand">
                    <div class="auth-brand__logo" aria-hidden="true">
                        <img
                            src="<?= htmlspecialchars($basePath) ?>/public/uploads/logo-afc.png"
                            alt="Logo Escuela de Crecimiento"
                            loading="lazy"
                        >
                    </div>
                    <div>
                        <h1 class="auth-brand__title">CONTROL ESCOLAR</h1>
                        <p class="auth-brand__subtitle">Escuela de Crecimiento • Acceso seguro</p>
                    </div>
                </div>
            </div>

            <div class="auth-card__body">

                <?php if ($error): ?>
                    <div class="auth-alert auth-alert--danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="auth-alert auth-alert--success" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        <div><?= htmlspecialchars($success) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= htmlspecialchars($basePath) ?>/auth/login" autocomplete="on">

                    <div class="mb-3">
                        <label for="email" class="auth-label">Correo electrónico</label>
                        <div class="auth-input">
                            <i class="bi bi-envelope auth-input__icon"></i>
                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                required
                                autocomplete="username"
                                placeholder="Escribe tu correo"
                                inputmode="email"
                            >
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="password" class="auth-label">Contraseña</label>
                        <div class="auth-input">
                            <i class="bi bi-lock auth-input__icon"></i>
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Escribe tu contraseña"
                            >
                            <button
                                type="button"
                                class="auth-input__toggle"
                                data-toggle-password="password"
                                aria-label="Mostrar u ocultar contraseña"
                                title="Mostrar / Ocultar"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="auth-row">
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Recordarme</label>
                        </div>

                        <a class="auth-link" href="<?= htmlspecialchars($basePath) ?>/auth/forgot">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <button type="submit" class="btn auth-btn w-100 text-white">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
                    </button>

                    <div class="auth-footer">
                        © <?= date('Y') ?> Escuela de Crecimiento · Control Escolar
                    </div>
                </form>

            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}


// Función para crear el dashboard principal
function createDashboard($basePath = null, array $dashboardData = []) {
    $basePath = $basePath ?? getBasePath();
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
    <div class="container-fluid py-4 dashboard-premium">
        <div class="dashboard-hero premium-glass-card d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div>
                <h2 class="mb-2 dashboard-title"><i class="bi bi-speedometer2 me-2"></i> Panel de Control</h2>
                <p class="text-muted mb-0">Bienvenido al Sistema de Gestión Escolar Escuela de Crecimiento</p>
            </div>
            <div class="text-end dashboard-user">
                <div class="mb-2">
                    <span class="user-chip">
                        <i class="bi bi-person-circle"></i>
                        <?php echo htmlspecialchars($userName); ?>
                    </span>
                    <span class="badge badge-soft-info ms-2"><?php echo ucfirst($userRole); ?></span>
                </div>
                <a href="<?php echo $basePath; ?>/logout" class="btn btn-outline-danger btn-sm premium-pill-button">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6 col-12">
                <button type="button" class="dashboard-kpi-card premium-glass-card w-100 text-start" data-bs-toggle="modal" data-bs-target="#dashboardCoursesModal">
                    <div class="kpi-icon kpi-primary">
                        <i class="bi bi-book"></i>
                    </div>
                    <div>
                        <h5 class="kpi-label">Cursos</h5>
                        <p class="kpi-value text-primary mb-1"><?= (int) $stats['courses'] ?></p>
                        <small class="text-muted">Ver detalle y exportar</small>
                    </div>
                </button>
            </div>
            <div class="col-xl-3 col-md-6 col-12">
                <button type="button" class="dashboard-kpi-card premium-glass-card w-100 text-start" data-bs-toggle="modal" data-bs-target="#dashboardStudentsModal">
                    <div class="kpi-icon kpi-success">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h5 class="kpi-label">Estudiantes</h5>
                        <p class="kpi-value text-success mb-1"><?= (int) $stats['students'] ?></p>
                        <small class="text-muted">Ver detalle y exportar</small>
                    </div>
                </button>
            </div>
            <div class="col-xl-3 col-md-6 col-12">
                <button type="button" class="dashboard-kpi-card premium-glass-card w-100 text-start" data-bs-toggle="modal" data-bs-target="#dashboardTeachersModal">
                    <div class="kpi-icon kpi-warning">
                        <i class="bi bi-easel"></i>
                    </div>
                    <div>
                        <h5 class="kpi-label">Profesores</h5>
                        <p class="kpi-value text-warning mb-1"><?= (int) $stats['teachers'] ?></p>
                        <small class="text-muted">Ver detalle y exportar</small>
                    </div>
                </button>
            </div>
            <div class="col-xl-3 col-md-6 col-12">
                <button type="button" class="dashboard-kpi-card premium-glass-card w-100 text-start" data-bs-toggle="modal" data-bs-target="#dashboardEnrollmentsModal">
                    <div class="kpi-icon kpi-info">
                        <i class="bi bi-clipboard-data"></i>
                    </div>
                    <div>
                        <h5 class="kpi-label">Inscripciones</h5>
                        <p class="kpi-value text-info mb-1"><?= (int) $stats['enrollments'] ?></p>
                        <small class="text-muted">Ver detalle y exportar</small>
                    </div>
                </button>
            </div>
        </div>
        
        <?php if ($userRole === 'teacher'): ?>
            <div class="card premium-glass-card mb-4">
                <div class="card-header premium-card-header">
                    <h5 class="mb-0"><i class="bi bi-easel"></i> Mis cursos asignados</h5>
                </div>
                <div class="card-body premium-card-body">
                    <?php if (empty($dashboardData['teacherCourses'])): ?>
                        <div class="empty-state premium-empty">
                            <i class="bi bi-journal-x"></i>
                            <p class="empty-state-title">Aún no hay cursos asignados</p>
                            <p class="text-muted mb-0">Cuando tengas cursos activos, aparecerán listados aquí.</p>
                        </div>
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
            <div class="card premium-glass-card">
                <div class="card-header premium-card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Acciones Rápidas</h5>
                </div>
                <div class="card-body premium-card-body">
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <a href="<?php echo $basePath; ?>/courses" class="premium-pill-action">
                                <span class="premium-pill-icon"><i class="bi bi-book"></i></span>
                                <span>
                                    <span class="premium-pill-title">Gestionar Cursos</span>
                                    <span class="premium-pill-text">Administra grupos y contenido académico.</span>
                                </span>
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <a href="<?php echo $basePath; ?>/enrollments" class="premium-pill-action">
                                <span class="premium-pill-icon"><i class="bi bi-person-plus"></i></span>
                                <span>
                                    <span class="premium-pill-title">Gestionar Inscripciones</span>
                                    <span class="premium-pill-text">Mantén el control del alumnado activo.</span>
                                </span>
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <a href="<?php echo $basePath; ?>/subjects" class="premium-pill-action">
                                <span class="premium-pill-icon"><i class="bi bi-list-ul"></i></span>
                                <span>
                                    <span class="premium-pill-title">Gestionar Materias</span>
                                    <span class="premium-pill-text">Organiza el catálogo académico.</span>
                                </span>
                            </a>
                        </div>
                        <?php if ($userRole === 'admin'): ?>
                            <div class="col-lg-4 col-md-6">
                                <a href="<?php echo $basePath; ?>/teachers" class="premium-pill-action">
                                    <span class="premium-pill-icon"><i class="bi bi-easel"></i></span>
                                    <span>
                                        <span class="premium-pill-title">Gestionar Profesores</span>
                                        <span class="premium-pill-text">Asigna materias y horarios.</span>
                                    </span>
                                </a>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <a href="<?php echo $basePath; ?>/students" class="premium-pill-action">
                                    <span class="premium-pill-icon"><i class="bi bi-person-badge"></i></span>
                                    <span>
                                        <span class="premium-pill-title">Gestionar Alumnos</span>
                                        <span class="premium-pill-text">Actualiza perfiles estudiantiles.</span>
                                    </span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Recent Activity -->
        <div class="card premium-glass-card mt-4">
            <div class="card-header premium-card-header">
                <h5 class="mb-0"><i class="bi bi-clock"></i> Actividad Reciente</h5>
            </div>
            <div class="card-body premium-card-body">
                <div class="empty-state premium-empty">
                    <i class="bi bi-activity"></i>
                    <p class="empty-state-title">Sin novedades por ahora</p>
                    <p class="text-muted mb-0">Aquí verás inscripciones, cambios y alertas recientes.</p>
                </div>
            </div>
        </div>

        <div class="modal fade" id="dashboardCoursesModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title"><i class="bi bi-book me-2"></i>Detalle de cursos</h5>
                            <p class="text-muted mb-0 small">Consulta y exporta la información de cursos.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="dashboardCoursesTable" data-datatable data-order-column="0" data-order-direction="asc">
                                <thead>
                                    <tr>
                                        <th>Grupo</th>
                                        <th>Materia</th>
                                        <th>Periodo</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($dashboardData['courses'])): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Sin cursos registrados.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($dashboardData['courses'] as $course): ?>
                                            <?php
                                            $statusValue = $course['status'] ?? 'N/A';
                                            $statusLabel = in_array(strtolower((string) $statusValue), ['open', 'abierto'], true)
                                                ? 'Abierto'
                                                : $statusValue;
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($course['group_name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($course['subject_name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($course['term_name'] ?? 'Sin periodo') ?></td>
                                                <td><?= htmlspecialchars($statusLabel) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="dashboardStudentsModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title"><i class="bi bi-people me-2"></i>Detalle de estudiantes</h5>
                            <p class="text-muted mb-0 small">Listado de alumnos con contacto.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="dashboardStudentsTable" data-datatable data-order-column="0" data-order-direction="asc">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Teléfono</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($dashboardData['students'])): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Sin estudiantes registrados.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($dashboardData['students'] as $student): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($student['name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($student['email'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($student['phone'] ?? 'Sin teléfono') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="dashboardTeachersModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title"><i class="bi bi-easel me-2"></i>Detalle de profesores</h5>
                            <p class="text-muted mb-0 small">Listado actualizado de docentes.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="dashboardTeachersTable" data-datatable data-order-column="0" data-order-direction="asc">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($dashboardData['teachers'])): ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">Sin profesores registrados.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($dashboardData['teachers'] as $teacher): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($teacher['name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($teacher['email'] ?? 'N/A') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="dashboardEnrollmentsModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title"><i class="bi bi-clipboard-data me-2"></i>Detalle de inscripciones</h5>
                            <p class="text-muted mb-0 small">Incluye teléfono, correo, materia y manual físico.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="dashboardEnrollmentsTable" data-datatable data-export="excel" data-order-column="6" data-order-direction="desc">
                                <thead>
                                    <tr>
                                        <th>Alumno</th>
                                        <th>Correo</th>
                                        <th>Teléfono</th>
                                        <th>Materia</th>
                                        <th>Grupo</th>
                                        <th>Manual físico</th>
                                        <th>Fecha de inscripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($dashboardData['enrollments'])): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Sin inscripciones registradas.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($dashboardData['enrollments'] as $enrollment): ?>
                                            <?php
                                            $notesData = json_decode($enrollment['notes'] ?? '', true);
                                            $manualLabel = ((int) ($notesData['manual_fisico'] ?? ($enrollment['manual_fisico'] ?? 0)) === 1) ? 'Sí' : 'No';
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($enrollment['student_name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($enrollment['email'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($enrollment['phone'] ?? 'Sin teléfono') ?></td>
                                                <td><?= htmlspecialchars($enrollment['subject_name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($enrollment['group_name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($manualLabel) ?></td>
                                                <td><?= htmlspecialchars($enrollment['enrollment_at'] ?? '') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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

// Debug opcional de rutas (se activa con ?debug_route=1 o constante ROUTER_DEBUG)
$debugRoute = ROUTER_DEBUG || (($_GET['debug_route'] ?? '') === '1');
if ($debugRoute) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "BASE_PATH: " . ($route['base_path'] === '' ? '(empty)' : $route['base_path']) . PHP_EOL;
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . PHP_EOL;
    echo "ROUTE: " . ($route['path'] === '' ? '(empty)' : $route['path']) . PHP_EOL;
    echo "ACTION: " . ($route['action'] ?? '') . PHP_EOL;
    exit();
}

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
                <h1><i class="bi bi-exclamation-triangle text-warning"></i></h1>
                <h3>Página No Encontrada</h3>
                <p class="text-muted">La página que busca no existe.</p>
                <a href="' . $route['base_path'] . '/dashboard" class="btn btn-primary">
                    <i class="bi bi-house"></i> Ir al Dashboard
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
            'courses' => [],
            'students' => [],
            'teachers' => [],
            'enrollments' => [],
            'teacherCourses' => []
        ];

        $pdo = getPdoConnection($dbConfig);
        $activeTerm = getActiveTerm($pdo);

        $dashboardData['stats']['courses'] = (int) $pdo->query("SELECT COUNT(*) FROM courses WHERE status = 'open'")->fetchColumn();
        $dashboardData['stats']['students'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
        $dashboardData['stats']['teachers'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();

        $dashboardData['courses'] = $pdo->query("
            SELECT c.id,
                   c.group_name,
                   c.status,
                   s.name AS subject_name,
                   t.name AS term_name
            FROM courses c
            LEFT JOIN subjects s ON s.id = c.subject_id
            LEFT JOIN terms t ON t.id = c.term_id
            ORDER BY c.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $dashboardData['students'] = $pdo->query("
            SELECT u.id,
                   u.name,
                   u.email,
                   sp.phone
            FROM users u
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            WHERE u.role = 'student'
            ORDER BY u.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $dashboardData['teachers'] = $pdo->query("
            SELECT u.id,
                   u.name,
                   u.email
            FROM users u
            WHERE u.role = 'teacher'
            ORDER BY u.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $dashboardData['enrollments'] = $pdo->query("
            SELECT e.id,
                   u.name AS student_name,
                   u.email,
                   sp.phone,
                   s.name AS subject_name,
                   c.group_name,
                   e.enrollment_at,
                   e.notes
            FROM enrollments e
            INNER JOIN users u ON u.id = e.student_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            INNER JOIN courses c ON c.id = e.course_id
            INNER JOIN subjects s ON s.id = c.subject_id
            ORDER BY e.enrollment_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $enrollmentStmt = $pdo->query("SELECT COUNT(*) FROM enrollments");
        $dashboardData['stats']['enrollments'] = (int) $enrollmentStmt->fetchColumn();

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
        $hasModuloMateria = tableExists($pdo, 'modulo_materia');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            try {
                if ($action === 'create_course') {
                    $groupName = trim($_POST['group_name'] ?? '');
                    $subjectId = (int) ($_POST['subject_id'] ?? 0);
                    $termId = (int) ($_POST['term_id'] ?? 0);
                    $status = $_POST['status'] ?? 'inactive';
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
                    $status = $_POST['status'] ?? 'inactive';
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
                'successMessage' => $successMessage,
                'pageShellClass' => 'page-shell-dashboard',
                'topbarClass' => 'dash-topbar'
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
              AND s.is_active = 1
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
                'successMessage' => $successMessage,
                'pageShellClass' => 'page-shell-dashboard',
                'topbarClass' => 'dash-topbar'
            ]
        );
        break;

    case 'users':
        requireAuth($route['base_path']);
        requireAdmin($route['base_path']);
        header('Location: ' . $route['base_path'] . '/teachers');
        exit();

    case 'teachers':
        requireAuth($route['base_path']);
        requireAdmin($route['base_path']);
        $pdo = getPdoConnection($dbConfig);
        $errorMessage = null;

        $teachersStmt = $pdo->prepare("
            SELECT u.id,
                   u.name,
                   u.email,
                   u.status,
                   u.role,
                   u.created_at,
                   GROUP_CONCAT(
                       DISTINCT CONCAT(
                           s.name,
                           CASE WHEN c.group_name IS NULL OR c.group_name = '' THEN '' ELSE CONCAT(' - ', c.group_name) END,
                           CASE WHEN c.schedule_label IS NULL OR c.schedule_label = '' THEN '' ELSE CONCAT(' (', c.schedule_label, ')') END
                       )
                       ORDER BY s.name SEPARATOR '||'
                   ) AS course_names
            FROM users u
            LEFT JOIN course_teachers ct ON ct.teacher_id = u.id
            LEFT JOIN courses c ON c.id = ct.course_id
            LEFT JOIN subjects s ON s.id = c.subject_id
            WHERE u.role = 'teacher'
            GROUP BY u.id
            ORDER BY u.name ASC
        ");
        $teachersStmt->execute();
        $teachers = $teachersStmt->fetchAll(PDO::FETCH_ASSOC);

        echo renderPage(
            __DIR__ . '/../src/UI/Views/users/teachers.php',
            'Profesores - Control Escolar',
            $route['base_path'],
            [
                'teachers' => $teachers,
                'breadcrumbs' => [
                    ['label' => 'Control Escolar', 'url' => $route['base_path'] . '/dashboard'],
                    ['label' => 'Profesores']
                ],
                'errorMessage' => $errorMessage,
                'pageShellClass' => 'page-shell-dashboard',
                'topbarClass' => 'dash-topbar'
            ]
        );
        break;

    case 'students':
        requireAuth($route['base_path']);
        requireAdmin($route['base_path']);
        $pdo = getPdoConnection($dbConfig);
        $errorMessage = null;
        $successMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $action = $_POST['action'] ?? '';
                if ($action === 'reset_student_password') {
                    $studentId = (int) ($_POST['student_id'] ?? 0);
                    if (!$studentId) {
                        throw new Exception('Selecciona un estudiante válido.');
                    }

                    $studentStmt = $pdo->prepare("SELECT id, name, role FROM users WHERE id = :id LIMIT 1");
                    $studentStmt->execute(['id' => $studentId]);
                    $student = $studentStmt->fetch(PDO::FETCH_ASSOC);

                    if (!$student || $student['role'] !== 'student') {
                        throw new Exception('Solo puedes restablecer contraseñas de alumnos.');
                    }

                    $newPassword = generateTemporaryPassword();
                    $hash = password_hash($newPassword, PASSWORD_ARGON2ID);
                    $updateStmt = $pdo->prepare("UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :id");
                    $updateStmt->execute([
                        'hash' => $hash,
                        'id' => $studentId
                    ]);

                    $successMessage = 'Contraseña generada para ' . $student['name'] . ': ' . $newPassword;
                }
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        $studentsStmt = $pdo->prepare("
            SELECT u.id,
                   u.name,
                   u.email,
                   u.status,
                   u.role,
                   u.created_at,
                   GROUP_CONCAT(
                       DISTINCT CONCAT(
                           s.name,
                           CASE WHEN c.group_name IS NULL OR c.group_name = '' THEN '' ELSE CONCAT(' - ', c.group_name) END,
                           CASE WHEN c.schedule_label IS NULL OR c.schedule_label = '' THEN '' ELSE CONCAT(' (', c.schedule_label, ')') END
                       )
                       ORDER BY s.name SEPARATOR '||'
                   ) AS subject_names
            FROM users u
            LEFT JOIN enrollments e ON e.student_id = u.id
            LEFT JOIN courses c ON c.id = e.course_id
            LEFT JOIN subjects s ON s.id = c.subject_id
            WHERE u.role = 'student'
            GROUP BY u.id
            ORDER BY u.name ASC
        ");
        $studentsStmt->execute();
        $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

        echo renderPage(
            __DIR__ . '/../src/UI/Views/users/students.php',
            'Alumnos - Control Escolar',
            $route['base_path'],
            [
                'students' => $students,
                'breadcrumbs' => [
                    ['label' => 'Control Escolar', 'url' => $route['base_path'] . '/dashboard'],
                    ['label' => 'Alumnos']
                ],
                'errorMessage' => $errorMessage,
                'successMessage' => $successMessage
            ]
        );
        break;

    case 'modules':
        requireAuth($route['base_path']);
        requireAdmin($route['base_path']);
        $pdo = getPdoConnection($dbConfig);
        $errorMessage = null;
        $successMessage = null;
        $moduleSearch = trim($_GET['module_search'] ?? '');
        $modulePage = max(1, (int) ($_GET['module_page'] ?? 1));
        $perPage = 10;
        $hasModuloMateria = tableExists($pdo, 'modulo_materia');
        $hasModuleDescription = columnExists($pdo, 'modules', 'description');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            try {
                if ($action === 'create_module') {
                    $name = trim($_POST['name'] ?? '');
                    $description = trim($_POST['description'] ?? '');
                    $sortOrder = max(1, (int) ($_POST['sort_order'] ?? 1));
                    $isActive = isset($_POST['is_active']) ? 1 : 0;
                    $subjectIds = array_values(array_unique(array_filter(array_map('intval', $_POST['subject_ids'] ?? []))));

                    if ($name === '') {
                        throw new Exception('El nombre del módulo es obligatorio.');
                    }

                    $pdo->beginTransaction();

                    $code = generateModuleCode($pdo, $name);
                    if ($hasModuleDescription) {
                        $stmt = $pdo->prepare("
                            INSERT INTO modules (code, name, description, sort_order, is_active, created_at, updated_at)
                            VALUES (:code, :name, :description, :sort_order, :is_active, NOW(), NOW())
                        ");
                        $stmt->execute([
                            'code' => $code,
                            'name' => $name,
                            'description' => $description,
                            'sort_order' => $sortOrder,
                            'is_active' => $isActive
                        ]);
                    } else {
                        $stmt = $pdo->prepare("
                            INSERT INTO modules (code, name, sort_order, is_active, created_at, updated_at)
                            VALUES (:code, :name, :sort_order, :is_active, NOW(), NOW())
                        ");
                        $stmt->execute([
                            'code' => $code,
                            'name' => $name,
                            'sort_order' => $sortOrder,
                            'is_active' => $isActive
                        ]);
                    }

                    $moduleId = (int) $pdo->lastInsertId();
                    if (!empty($subjectIds)) {
                        if ($hasModuloMateria) {
                            $linkStmt = $pdo->prepare("
                                INSERT INTO modulo_materia (modulo_id, materia_id)
                                VALUES (:modulo_id, :materia_id)
                            ");
                            foreach ($subjectIds as $subjectId) {
                                $linkStmt->execute([
                                    'modulo_id' => $moduleId,
                                    'materia_id' => $subjectId
                                ]);
                            }
                        } else {
                            $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));
                            $assignStmt = $pdo->prepare("UPDATE subjects SET module_id = ? WHERE id IN ({$placeholders})");
                            $assignStmt->execute(array_merge([$moduleId], $subjectIds));
                        }
                    }

                    $pdo->commit();
                    $successMessage = 'Módulo creado correctamente.';
                } elseif ($action === 'update_module') {
                    $moduleId = (int) ($_POST['id'] ?? 0);
                    $name = trim($_POST['name'] ?? '');
                    $description = trim($_POST['description'] ?? '');
                    $sortOrder = max(1, (int) ($_POST['sort_order'] ?? 1));
                    $isActive = isset($_POST['is_active']) ? 1 : 0;
                    $subjectIds = array_values(array_unique(array_filter(array_map('intval', $_POST['subject_ids'] ?? []))));

                    if (!$moduleId || $name === '') {
                        throw new Exception('Completa los campos obligatorios del módulo.');
                    }

                    $pdo->beginTransaction();

                    if ($hasModuleDescription) {
                        $stmt = $pdo->prepare("
                            UPDATE modules
                            SET name = :name,
                                description = :description,
                                sort_order = :sort_order,
                                is_active = :is_active,
                                updated_at = NOW()
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            'id' => $moduleId,
                            'name' => $name,
                            'description' => $description,
                            'sort_order' => $sortOrder,
                            'is_active' => $isActive
                        ]);
                    } else {
                        $stmt = $pdo->prepare("
                            UPDATE modules
                            SET name = :name,
                                sort_order = :sort_order,
                                is_active = :is_active,
                                updated_at = NOW()
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            'id' => $moduleId,
                            'name' => $name,
                            'sort_order' => $sortOrder,
                            'is_active' => $isActive
                        ]);
                    }

                    if ($hasModuloMateria) {
                        $pdo->prepare("DELETE FROM modulo_materia WHERE modulo_id = :id")
                            ->execute(['id' => $moduleId]);

                        if (!empty($subjectIds)) {
                            $linkStmt = $pdo->prepare("
                                INSERT INTO modulo_materia (modulo_id, materia_id)
                                VALUES (:modulo_id, :materia_id)
                            ");
                            foreach ($subjectIds as $subjectId) {
                                $linkStmt->execute([
                                    'modulo_id' => $moduleId,
                                    'materia_id' => $subjectId
                                ]);
                            }
                        }
                    } else {
                        $pdo->prepare("UPDATE subjects SET module_id = NULL WHERE module_id = :id")
                            ->execute(['id' => $moduleId]);

                        if (!empty($subjectIds)) {
                            $placeholders = implode(',', array_fill(0, count($subjectIds), '?'));
                            $assignStmt = $pdo->prepare("UPDATE subjects SET module_id = ? WHERE id IN ({$placeholders})");
                            $assignStmt->execute(array_merge([$moduleId], $subjectIds));
                        }
                    }

                    $pdo->commit();
                    $successMessage = 'Módulo actualizado correctamente.';
                } elseif ($action === 'delete_module') {
                    $moduleId = (int) ($_POST['id'] ?? 0);
                    if (!$moduleId) {
                        throw new Exception('Módulo inválido.');
                    }

                    $pdo->beginTransaction();
                    if ($hasModuloMateria) {
                        $pdo->prepare("DELETE FROM modulo_materia WHERE modulo_id = :id")->execute(['id' => $moduleId]);
                    } else {
                        $pdo->prepare("UPDATE subjects SET module_id = NULL WHERE module_id = :id")->execute(['id' => $moduleId]);
                    }
                    $pdo->prepare("DELETE FROM modules WHERE id = :id")->execute(['id' => $moduleId]);
                    $pdo->commit();

                    $successMessage = 'Módulo eliminado correctamente.';
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errorMessage = $e->getMessage();
            }
        }

        $moduleSearchTerm = '%' . $moduleSearch . '%';
        $moduleCountStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM modules
            WHERE name LIKE :search_name
        ");
        $moduleCountStmt->execute(['search_name' => $moduleSearchTerm]);
        $moduleTotal = (int) $moduleCountStmt->fetchColumn();
        $moduleTotalPages = max(1, (int) ceil($moduleTotal / $perPage));
        $modulePage = min($modulePage, $moduleTotalPages);
        $moduleOffset = ($modulePage - 1) * $perPage;

        $modulesQuery = "
            SELECT m.id,
                   m.name,
                   " . ($hasModuleDescription ? 'm.description' : "'' AS description") . ",
                   m.sort_order,
                   m.is_active,
                   GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR '||') AS subject_names,
                   GROUP_CONCAT(DISTINCT s.id ORDER BY s.name SEPARATOR ',') AS subject_ids,
                   COUNT(DISTINCT s.id) AS subject_count
            FROM modules m
            LEFT JOIN subjects s ON s.module_id = m.id
            WHERE m.name LIKE :search_name
            GROUP BY m.id
            ORDER BY m.sort_order ASC, m.name ASC
            LIMIT :limit OFFSET :offset
        ";

        if ($hasModuloMateria) {
            $modulesQuery = "
                SELECT m.id,
                       m.name,
                       " . ($hasModuleDescription ? 'm.description' : "'' AS description") . ",
                       m.sort_order,
                       m.is_active,
                       GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR '||') AS subject_names,
                       GROUP_CONCAT(DISTINCT s.id ORDER BY s.name SEPARATOR ',') AS subject_ids,
                       COUNT(DISTINCT mm.materia_id) AS subject_count
                FROM modules m
                LEFT JOIN modulo_materia mm ON mm.modulo_id = m.id
                LEFT JOIN subjects s ON s.id = mm.materia_id
                WHERE m.name LIKE :search_name
                GROUP BY m.id
                ORDER BY m.sort_order ASC, m.name ASC
                LIMIT :limit OFFSET :offset
            ";
        }

        $modulesStmt = $pdo->prepare($modulesQuery);
        $modulesStmt->bindValue(':search_name', $moduleSearchTerm, PDO::PARAM_STR);
        $modulesStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $modulesStmt->bindValue(':offset', $moduleOffset, PDO::PARAM_INT);
        $modulesStmt->execute();
        $modules = $modulesStmt->fetchAll(PDO::FETCH_ASSOC);

        $subjectsStmt = $pdo->query("SELECT id, name FROM subjects WHERE is_active = 1 ORDER BY name ASC");
        $subjects = $subjectsStmt->fetchAll(PDO::FETCH_ASSOC);

        echo renderPage(
            __DIR__ . '/../src/UI/Views/modules/index.php',
            'Módulos - Control Escolar',
            $route['base_path'],
            [
                'modules' => $modules,
                'subjects' => $subjects,
                'moduleSearch' => $moduleSearch,
                'modulePage' => $modulePage,
                'moduleTotalPages' => $moduleTotalPages,
                'moduleTotal' => $moduleTotal,
                'breadcrumbs' => [
                    ['label' => 'Control Escolar', 'url' => $route['base_path'] . '/dashboard'],
                    ['label' => 'Módulos']
                ],
                'errorMessage' => $errorMessage,
                'successMessage' => $successMessage,
                'pageShellClass' => 'page-shell-dashboard',
                'topbarClass' => 'dash-topbar'
            ]
        );
        break;

    case 'subjects':
        requireAuth($route['base_path']);
        requireAdmin($route['base_path']);
        $pdo = getPdoConnection($dbConfig);
        $errorMessage = null;
        $successMessage = null;
        $hasModuloMateria = tableExists($pdo, 'modulo_materia');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            try {
                if ($action === 'create_subject') {
                    $name = trim($_POST['name'] ?? '');
                    $code = trim($_POST['code'] ?? '');
                    $moduleIds = array_values(array_filter(array_map('intval', $_POST['module_ids'] ?? [])));
                    $primaryModuleId = $moduleIds[0] ?? null;
                    $description = trim($_POST['description'] ?? '');

                    if ($name === '' || $code === '') {
                        throw new Exception('Completa los campos obligatorios de la materia.');
                    }

                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("
                        INSERT INTO subjects
                            (code, name, module_id, description, created_at, updated_at)
                        VALUES
                            (:code, :name, :module_id, :description, NOW(), NOW())
                    ");
                    $stmt->execute([
                        'code' => $code,
                        'name' => $name,
                        'module_id' => $primaryModuleId,
                        'description' => $description
                    ]);

                    $subjectId = (int) $pdo->lastInsertId();
                    if (!empty($moduleIds) && $hasModuloMateria) {
                        $linkStmt = $pdo->prepare("
                            INSERT INTO modulo_materia (modulo_id, materia_id)
                            VALUES (:modulo_id, :materia_id)
                        ");
                        foreach ($moduleIds as $moduleId) {
                            $linkStmt->execute([
                                'modulo_id' => $moduleId,
                                'materia_id' => $subjectId
                            ]);
                        }
                    }

                    $pdo->commit();

                    $successMessage = 'Materia creada correctamente.';
                } elseif ($action === 'update_subject') {
                    $subjectId = (int) ($_POST['id'] ?? 0);
                    $name = trim($_POST['name'] ?? '');
                    $moduleIds = array_values(array_filter(array_map('intval', $_POST['module_ids'] ?? [])));
                    $primaryModuleId = $moduleIds[0] ?? null;
                    $description = trim($_POST['description'] ?? '');

                    if (!$subjectId || $name === '') {
                        throw new Exception('Completa los campos obligatorios de la materia.');
                    }

                    $pdo->beginTransaction();

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
                        'module_id' => $primaryModuleId,
                        'description' => $description
                    ]);

                    if ($hasModuloMateria) {
                        $pdo->prepare("DELETE FROM modulo_materia WHERE materia_id = :id")
                            ->execute(['id' => $subjectId]);

                        if (!empty($moduleIds)) {
                            $linkStmt = $pdo->prepare("
                                INSERT INTO modulo_materia (modulo_id, materia_id)
                                VALUES (:modulo_id, :materia_id)
                            ");
                            foreach ($moduleIds as $moduleId) {
                                $linkStmt->execute([
                                    'modulo_id' => $moduleId,
                                    'materia_id' => $subjectId
                                ]);
                            }
                        }
                    }

                    $pdo->commit();

                    $successMessage = 'Materia actualizada correctamente.';
                } elseif ($action === 'delete_subject') {
                    $subjectId = (int) ($_POST['id'] ?? 0);
                    if (!$subjectId) {
                        throw new Exception('Materia inválida.');
                    }
                    $pdo->beginTransaction();
                    if ($hasModuloMateria) {
                        $pdo->prepare("DELETE FROM modulo_materia WHERE materia_id = :id")->execute(['id' => $subjectId]);
                    }
                    $pdo->prepare("DELETE FROM subjects WHERE id = :id")->execute(['id' => $subjectId]);
                    $pdo->commit();
                    $successMessage = 'Materia eliminada correctamente.';
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errorMessage = $e->getMessage();
            }
        }

        $subjectsQuery = "
            SELECT s.*,
                   GROUP_CONCAT(DISTINCT m.name ORDER BY m.sort_order SEPARATOR '||') AS module_names,
                   GROUP_CONCAT(DISTINCT m.id ORDER BY m.sort_order SEPARATOR ',') AS module_ids,
                   COUNT(DISTINCT c.id) AS course_count
            FROM subjects s
            LEFT JOIN modules m ON m.id = s.module_id
            LEFT JOIN courses c ON c.subject_id = s.id AND c.status = 'open'
            WHERE s.is_active = 1
            GROUP BY s.id
            ORDER BY s.name ASC
        ";

        if ($hasModuloMateria) {
            $subjectsQuery = "
                SELECT s.*,
                       GROUP_CONCAT(DISTINCT m.name ORDER BY m.sort_order SEPARATOR '||') AS module_names,
                       GROUP_CONCAT(DISTINCT m.id ORDER BY m.sort_order SEPARATOR ',') AS module_ids,
                       COUNT(DISTINCT c.id) AS course_count
                FROM subjects s
                LEFT JOIN modulo_materia mm ON mm.materia_id = s.id
                LEFT JOIN modules m ON m.id = mm.modulo_id
                LEFT JOIN courses c ON c.subject_id = s.id AND c.status = 'open'
                WHERE s.is_active = 1
                GROUP BY s.id
                ORDER BY s.name ASC
            ";
        }

        $subjectsStmt = $pdo->query($subjectsQuery);
        $subjects = $subjectsStmt->fetchAll(PDO::FETCH_ASSOC);

        $modulesStmt = $pdo->query("SELECT id, name FROM modules WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
        $modules = $modulesStmt->fetchAll(PDO::FETCH_ASSOC);

        echo renderPage(
            __DIR__ . '/../src/UI/Views/subjects/index.php',
            'Materias - Control Escolar',
            $route['base_path'],
            [
                'subjects' => $subjects,
                'modules' => $modules,
                'breadcrumbs' => [
                    ['label' => 'Control Escolar', 'url' => $route['base_path'] . '/dashboard'],
                    ['label' => 'Materias']
                ],
                'errorMessage' => $errorMessage,
                'successMessage' => $successMessage,
                'pageShellClass' => 'page-shell-dashboard',
                'topbarClass' => 'dash-topbar'
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
                    $status = $_POST['status'] ?? 'inactive';

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
                    $status = $_POST['status'] ?? 'inactive';

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
                'successMessage' => $successMessage,
                'pageShellClass' => 'page-shell-dashboard',
                'topbarClass' => 'dash-topbar'
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
                <h1><i class="bi bi-exclamation-triangle text-warning"></i></h1>
                <h3>Página No Encontrada</h3>
                <p class="text-muted">La página que busca no existe.</p>
                <a href="' . $route['base_path'] . '/dashboard" class="btn btn-primary">
                    <i class="bi bi-house"></i> Ir al Dashboard
                </a>
            </div>
        ', 'Página No Encontrada - Control Escolar', $route['base_path']);
        break;
}
?>
