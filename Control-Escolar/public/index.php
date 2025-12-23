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
function createDashboard($basePath = '/Control-Escolar') {
    $userName = $_SESSION['user_name'] ?? 'Usuario';
    $userRole = $_SESSION['user_role'] ?? 'student';
    
    ob_start();
    ?>
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-graduation-cap"></i> Control Escolar
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Usuario:</strong><br>
                            <?php echo htmlspecialchars($userName); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Rol:</strong><br>
                            <span class="badge bg-info"><?php echo ucfirst($userRole); ?></span>
                        </div>
                        <hr>
                        <div class="d-grid">
                            <a href="<?php echo $basePath; ?>/logout" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="page-header">
                    <h2><i class="fas fa-tachometer-alt"></i> Panel de Control</h2>
                    <p class="text-muted">Bienvenido al Sistema de Gestión Escolar Christian LMS</p>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-book fa-2x text-primary mb-2"></i>
                                <h5 class="card-title">Cursos</h5>
                                <p class="card-text display-6 text-primary">4</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x text-success mb-2"></i>
                                <h5 class="card-title">Estudiantes</h5>
                                <p class="card-text display-6 text-success">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-chalkboard-teacher fa-2x text-warning mb-2"></i>
                                <h5 class="card-title">Profesores</h5>
                                <p class="card-text display-6 text-warning">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-clipboard-list fa-2x text-info mb-2"></i>
                                <h5 class="card-title">Inscripciones</h5>
                                <p class="card-text display-6 text-info">0</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
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
            
            $stmt = $pdo->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
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
        
    case 'dashboard':
        requireAuth($route['base_path']);
        
        echo loadLayout(
            createDashboard($route['base_path']),
            'Dashboard - Control Escolar',
            $route['base_path']
        );
        break;
        
    case 'courses':
        requireAuth($route['base_path']);
        echo loadLayout('
            <div class="text-center">
                <h1><i class="fas fa-book text-primary"></i></h1>
                <h3>Gestión de Cursos</h1>
                <p class="text-muted">Esta funcionalidad estará disponible próximamente.</p>
                <a href="' . $route['base_path'] . '/dashboard" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        ', 'Cursos - Control Escolar', $route['base_path']);
        break;
        
    case 'enrollments':
        requireAuth($route['base_path']);
        echo loadLayout('
            <div class="text-center">
                <h1><i class="fas fa-user-plus text-success"></i></h1>
                <h3>Gestión de Inscripciones</h1>
                <p class="text-muted">Esta funcionalidad estará disponible próximamente.</p>
                <a href="' . $route['base_path'] . '/dashboard" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        ', 'Inscripciones - Control Escolar', $route['base_path']);
        break;
        
    case 'subjects':
        requireAuth($route['base_path']);
        echo loadLayout('
            <div class="text-center">
                <h1><i class="fas fa-list text-warning"></i></h1>
                <h3>Gestión de Materias</h1>
                <p class="text-muted">Esta funcionalidad estará disponible próximamente.</p>
                <a href="' . $route['base_path'] . '/dashboard" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        ', 'Materias - Control Escolar', $route['base_path']);
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