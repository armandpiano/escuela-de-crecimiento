<?php 
$pageTitle = 'Dashboard - Christian LMS';
$currentUser = $_SESSION['user'] ?? null;

// Determinar el tipo de usuario y sus estadísticas
$userType = '';
$stats = [];
$recentActivity = [];

if ($currentUser) {
    if ($currentUser->isAdmin()) {
        $userType = 'Administrador';
        // TODO: Obtener estadísticas de admin
        $stats = [
            'total_users' => 150,
            'active_courses' => 25,
            'total_students' => 500,
            'total_teachers' => 30
        ];
    } elseif ($currentUser->isTeacher()) {
        $userType = 'Profesor';
        // TODO: Obtener estadísticas de profesor
        $stats = [
            'my_courses' => 5,
            'total_students' => 120,
            'active_enrollments' => 150,
            'pending_grade' => 8
        ];
    } elseif ($currentUser->isStudent()) {
        $userType = 'Estudiante';
        // TODO: Obtener estadísticas de estudiante
        $stats = [
            'enrolled_courses' => 6,
            'completed_courses' => 15,
            'current_gpa' => 3.8,
            'total_credits' => 45
        ];
    }
}

// Incluir el header
include __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar" style="width: 260px; min-height: 100vh;">
        <div class="p-3">
            <h5 class="mb-0">
                <i class="bi bi-mortarboard me-2"></i>Christian LMS
            </h5>
        </div>
        
        <nav class="mt-3">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white active" href="/dashboard">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                </li>
                
                <?php if ($currentUser->isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/admin/users">
                            <i class="bi bi-people me-2"></i>Usuarios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/admin/courses">
                            <i class="bi bi-book me-2"></i>Cursos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/admin/subjects">
                            <i class="bi bi-list-ul me-2"></i>Materias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/admin/reports">
                            <i class="bi bi-bar-chart me-2"></i>Reportes
                        </a>
                    </li>
                <?php elseif ($currentUser->isTeacher()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/teacher/courses">
                            <i class="bi bi-book me-2"></i>Mis Cursos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/teacher/students">
                            <i class="bi bi-person-badge me-2"></i>Mis Estudiantes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/teacher/grades">
                            <i class="bi bi-calculator me-2"></i>Calificaciones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/teacher/attendance">
                            <i class="bi bi-clipboard-check me-2"></i>Asistencia
                        </a>
                    </li>
                <?php elseif ($currentUser->isStudent()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/student/courses">
                            <i class="bi bi-book me-2"></i>Mis Cursos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/student/enrollments">
                            <i class="bi bi-person-plus me-2"></i>Inscripciones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/student/grades">
                            <i class="bi bi-graph-up me-2"></i>Mis Calificaciones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="/student/transcript">
                            <i class="bi bi-file-earmark-text me-2"></i>Historial Académico
                        </a>
                    </li>
                <?php endif; ?>
                
                <li class="nav-item mt-3">
                    <hr class="text-light">
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/profile">
                        <i class="bi bi-person me-2"></i>Mi Perfil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/settings">
                        <i class="bi bi-gear me-2"></i>Configuración
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="/auth/logout">
                        <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-fill">
        <!-- Header -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
            <div class="container-fluid">
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-2"></i>
                            <?= htmlspecialchars($currentUser->getFullName()) ?>
                            <span class="badge bg-primary ms-1"><?= htmlspecialchars($userType) ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                            <li><a class="dropdown-item" href="/settings"><i class="bi bi-gear me-2"></i>Configuración</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Dashboard Content -->
        <div class="container-xxl app-content">
            <div class="row mb-4">
                <div class="col">
                    <h1 class="h3 mb-0">Bienvenido, <?= htmlspecialchars($currentUser->getFirstName()) ?>!</h1>
                    <p class="text-muted">Panel de control - <?= htmlspecialchars($userType) ?></p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <?php foreach ($stats as $key => $value): ?>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title text-muted mb-1">
                                            <?php
                                            $labels = [
                                                'total_users' => 'Total Usuarios',
                                                'active_courses' => 'Cursos Activos',
                                                'total_students' => 'Total Estudiantes',
                                                'total_teachers' => 'Total Profesores',
                                                'my_courses' => 'Mis Cursos',
                                                'pending_grade' => 'Calificaciones Pendientes',
                                                'enrolled_courses' => 'Cursos Inscritos',
                                                'completed_courses' => 'Cursos Completados',
                                                'current_gpa' => 'GPA Actual',
                                                'total_credits' => 'Créditos Totales'
                                            ];
                                            echo $labels[$key] ?? $key;
                                            ?>
                                        </h6>
                                        <h3 class="mb-0 text-primary"><?= is_numeric($value) ? number_format($value) : $value ?></h3>
                                    </div>
                                    <div class="ms-3">
                                        <?php
                                        $icons = [
                                            'total_users' => 'bi-people',
                                            'active_courses' => 'bi-book',
                                            'total_students' => 'bi-person-badge',
                                            'total_teachers' => 'bi-easel',
                                            'my_courses' => 'bi-book',
                                            'pending_grade' => 'bi-calculator',
                                            'enrolled_courses' => 'bi-person-plus',
                                            'completed_courses' => 'bi-check-circle',
                                            'current_gpa' => 'bi-graph-up',
                                            'total_credits' => 'bi-star'
                                        ];
                                        $icon = $icons[$key] ?? 'bi-bar-chart';
                                        ?>
                                        <i class="bi <?= $icon ?> fs-2 text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-lightning me-2"></i>Acciones Rápidas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php if ($currentUser->isAdmin()): ?>
                                    <div class="col-md-4 mb-3">
                                        <a href="/admin/users/create" class="btn btn-outline-primary w-100">
                                            <i class="bi bi-person-plus mb-2 d-block"></i>
                                            Crear Usuario
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="/admin/courses/create" class="btn btn-outline-success w-100">
                                            <i class="bi bi-plus-circle mb-2 d-block"></i>
                                            Nuevo Curso
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="/admin/reports" class="btn btn-outline-info w-100">
                                            <i class="bi bi-bar-chart mb-2 d-block"></i>
                                            Ver Reportes
                                        </a>
                                    </div>
                                <?php elseif ($currentUser->isTeacher()): ?>
                                    <div class="col-md-4 mb-3">
                                        <a href="/teacher/courses/create" class="btn btn-outline-primary w-100">
                                            <i class="bi bi-plus-circle mb-2 d-block"></i>
                                            Crear Curso
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="/teacher/grades/manage" class="btn btn-outline-success w-100">
                                            <i class="bi bi-calculator mb-2 d-block"></i>
                                            Calificar
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="/teacher/attendance/take" class="btn btn-outline-info w-100">
                                            <i class="bi bi-clipboard-check mb-2 d-block"></i>
                                            Tomar Asistencia
                                        </a>
                                    </div>
                                <?php elseif ($currentUser->isStudent()): ?>
                                    <div class="col-md-4 mb-3">
                                        <a href="/student/courses/browse" class="btn btn-outline-primary w-100">
                                            <i class="bi bi-search mb-2 d-block"></i>
                                            Buscar Cursos
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="/student/enrollments" class="btn btn-outline-success w-100">
                                            <i class="bi bi-person-plus mb-2 d-block"></i>
                                            Inscribirse
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="/student/grades" class="btn btn-outline-info w-100">
                                            <i class="bi bi-graph-up mb-2 d-block"></i>
                                            Ver Calificaciones
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-bell me-2"></i>Notificaciones
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 mb-3"></i>
                                <p>No hay notificaciones nuevas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock me-2"></i>Actividad Reciente
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-clock-history fs-1 mb-3"></i>
                                <p>No hay actividad reciente</p>
                                <small>La actividad aparecerá aquí cuando empiece a usar el sistema</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
