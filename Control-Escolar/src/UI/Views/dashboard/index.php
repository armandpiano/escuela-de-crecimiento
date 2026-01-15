<?php
$userName = $userName ?? 'Usuario';
$userRole = $userRole ?? 'Estudiante';
$dashboardStats = $dashboardStats ?? [];
$recentActivity = $recentActivity ?? [];
$basePath = rtrim($basePath ?? '/Control-Escolar', '/');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Escuela de Crecimiento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= htmlspecialchars($basePath) ?>/assets/css/ui-premium.css" rel="stylesheet">
</head>
<body class="app-body admin-premium-page dashboard-page">
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="/dashboard" class="logo">
                <i class="bi bi-mortarboard"></i>
                Escuela de Crecimiento
            </a>
            <div class="user-info">
                <div class="d-flex align-items-center">
                    <div class="user-avatar">
                        <i class="bi bi-person"></i>
                    </div>
                    <div>
                        <div class="fw-bold"><?php echo htmlspecialchars($userName); ?></div>
                        <small class="opacity-75"><?php echo htmlspecialchars($userRole); ?></small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="nav-menu">
            <div class="nav-item">
                <a href="/dashboard" class="nav-link active">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="/dashboard/courses" class="nav-link">
                    <i class="bi bi-book"></i>
                    Cursos
                </a>
            </div>
            <div class="nav-item">
                <a href="/dashboard/enrollments" class="nav-link">
                    <i class="bi bi-person-graduate"></i>
                    Inscripciones
                </a>
            </div>
            <div class="nav-item">
                <a href="/dashboard/subjects" class="nav-link">
                    <i class="bi bi-book-open"></i>
                    Materias
                </a>
            </div>
            <div class="nav-item">
                <a href="/dashboard/students" class="nav-link">
                    <i class="bi bi-people"></i>
                    Estudiantes
                </a>
            </div>
            <div class="nav-item">
                <a href="/dashboard/teachers" class="nav-link">
                    <i class="bi bi-easel"></i>
                    Profesores
                </a>
            </div>
            <div class="nav-item">
                <a href="/dashboard/reports" class="nav-link">
                    <i class="bi bi-bar-chart"></i>
                    Reportes
                </a>
            </div>
            <div class="nav-item">
                <a href="/dashboard/settings" class="nav-link">
                    <i class="bi bi-gear"></i>
                    Configuración
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Topbar -->
        <header class="topbar admin-premium-header dashboard-glass-panel">
            <div class="d-flex align-items-center">
                <button class="btn-toggle-sidebar" id="toggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h1 class="page-title mb-0">Dashboard</h1>
                    <p class="page-subtitle mb-0">Panel de Control de Escuela de Crecimiento</p>
                </div>
            </div>
            <div class="user-menu">
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-2 text-primary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="/settings"><i class="bi bi-gear me-2"></i>Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="content app-content dashboard-content">
            <!-- Quick Actions -->
            <section class="dashboard-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Acciones Rápidas</h2>
                        <p class="section-subtitle">Atajos para gestionar las áreas más usadas del sistema.</p>
                    </div>
                </div>
                <div class="quick-actions quick-actions-premium">
                    <a href="/dashboard/courses" class="quick-action quick-action-pill quick-action-teal premium-card">
                        <div class="quick-action-icon">
                            <i class="bi bi-plus-circle"></i>
                        </div>
                        <div class="quick-action-content">
                            <h6 class="quick-action-title">Nuevo Curso</h6>
                            <p class="quick-action-text">Crear un nuevo curso</p>
                        </div>
                    </a>
                    <a href="/dashboard/enrollments" class="quick-action quick-action-pill quick-action-green premium-card">
                        <div class="quick-action-icon">
                            <i class="bi bi-person-plus"></i>
                        </div>
                        <div class="quick-action-content">
                            <h6 class="quick-action-title">Inscribir Estudiante</h6>
                            <p class="quick-action-text">Registrar inscripción</p>
                        </div>
                    </a>
                    <a href="/dashboard/subjects" class="quick-action quick-action-pill quick-action-amber premium-card">
                        <div class="quick-action-icon">
                            <i class="bi bi-book-open"></i>
                        </div>
                        <div class="quick-action-content">
                            <h6 class="quick-action-title">Nueva Materia</h6>
                            <p class="quick-action-text">Agregar materia</p>
                        </div>
                    </a>
                    <a href="/dashboard/reports" class="quick-action quick-action-pill quick-action-blue premium-card">
                        <div class="quick-action-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="quick-action-content">
                            <h6 class="quick-action-title">Ver Reportes</h6>
                            <p class="quick-action-text">Análisis del sistema</p>
                        </div>
                    </a>
                </div>
            </section>

            <!-- Stats Grid -->
            <section class="dashboard-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Indicadores Clave</h2>
                        <p class="section-subtitle">Resumen rápido del desempeño de la institución.</p>
                    </div>
                </div>
                <div class="stats-grid">
                    <div class="stat-card premium-card dashboard-kpi-card">
                        <div class="stat-icon stat-icon-teal">
                            <i class="bi bi-book"></i>
                        </div>
                        <div>
                            <div class="stat-label">Total Cursos</div>
                            <div class="stat-value"><?php echo $dashboardStats['total_courses'] ?? 0; ?></div>
                        </div>
                        <div class="stat-change text-success">
                            <i class="bi bi-arrow-up"></i>
                            <?php echo $dashboardStats['active_courses'] ?? 0; ?> activos
                        </div>
                    </div>
                    
                    <div class="stat-card premium-card dashboard-kpi-card">
                        <div class="stat-icon stat-icon-blue">
                            <i class="bi bi-person-graduate"></i>
                        </div>
                        <div>
                            <div class="stat-label">Inscripciones</div>
                            <div class="stat-value"><?php echo $dashboardStats['total_enrollments'] ?? 0; ?></div>
                        </div>
                        <div class="stat-change text-info">
                            <i class="bi bi-clock"></i>
                            <?php echo $dashboardStats['pending_enrollments'] ?? 0; ?> pendientes
                        </div>
                    </div>
                    
                    <div class="stat-card premium-card dashboard-kpi-card">
                        <div class="stat-icon stat-icon-green">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <div class="stat-label">Estudiantes</div>
                            <div class="stat-value"><?php echo $dashboardStats['total_students'] ?? 0; ?></div>
                        </div>
                        <div class="stat-change text-success">
                            <i class="bi bi-person-plus"></i> Este mes
                        </div>
                    </div>
                    
                    <div class="stat-card premium-card dashboard-kpi-card">
                        <div class="stat-icon stat-icon-amber">
                            <i class="bi bi-book-open"></i>
                        </div>
                        <div>
                            <div class="stat-label">Materias</div>
                            <div class="stat-value"><?php echo $dashboardStats['total_subjects'] ?? 0; ?></div>
                        </div>
                        <div class="stat-change text-primary">
                            <i class="bi bi-star"></i>
                            <?php echo $dashboardStats['average_grade'] ?? 0; ?> promedio
                        </div>
                    </div>
                    
                    <div class="stat-card premium-card dashboard-kpi-card">
                        <div class="stat-icon stat-icon-teal">
                            <i class="bi bi-easel"></i>
                        </div>
                        <div>
                            <div class="stat-label">Profesores</div>
                            <div class="stat-value"><?php echo $dashboardStats['total_teachers'] ?? 0; ?></div>
                        </div>
                        <div class="stat-change text-info">
                            <i class="bi bi-clock"></i> Activos
                        </div>
                    </div>
                    
                    <div class="stat-card premium-card dashboard-kpi-card">
                        <div class="stat-icon stat-icon-blue">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div>
                            <div class="stat-label">Ingresos Mensuales</div>
                            <div class="stat-value">$<?php echo number_format($dashboardStats['monthly_revenue'] ?? 0, 2); ?></div>
                        </div>
                        <div class="stat-change text-success">
                            <i class="bi bi-percent"></i>
                            <?php echo $dashboardStats['completion_rate'] ?? 0; ?>% completación
                        </div>
                    </div>
                </div>
            </section>

            <!-- Charts and Activity Row -->
            <section class="dashboard-section">
                <div class="row g-4">
                <div class="col-xl-8">
                    <div class="chart-card premium-card dashboard-glass-panel">
                        <h5 class="mb-3">
                            <i class="bi bi-graph-up text-primary"></i>
                            Resumen de Inscripciones
                        </h5>
                        <div class="chart-placeholder chart-placeholder-lg">
                            <div class="text-center">
                                <i class="bi bi-graph-up fs-1 mb-2"></i>
                                <p class="mb-0">Gráfico de Inscripciones por Mes</p>
                                <small class="text-muted">Integración con Chart.js próximamente</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-4">
                    <div class="activity-card premium-card dashboard-glass-panel">
                        <h5 class="mb-3">
                            <i class="bi bi-clock-history text-primary"></i>
                            Actividad Reciente
                        </h5>
                        
                        <?php if (empty($recentActivity)): ?>
                            <div class="empty-state empty-state-elevated">
                                <div class="empty-state-icon">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <div class="empty-state-title">Sin actividad reciente</div>
                                <div class="empty-state-description">Cuando existan movimientos se mostrarán aquí.</div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentActivity as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon <?php echo $this->getActivityIconClass($activity['type']); ?>">
                                        <i class="<?php echo $this->getActivityIcon($activity['type']); ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title"><?php echo htmlspecialchars($activity['description']); ?></div>
                                        <div class="activity-description">
                                            <i class="bi bi-person me-1"></i>
                                            <?php echo htmlspecialchars($activity['user']); ?>
                                        </div>
                                        <div class="activity-time">
                                            <i class="bi bi-clock me-1"></i>
                                            <?php echo $this->formatTimeAgo($activity['timestamp']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
            </section>

            <!-- Additional Stats Row -->
            <section class="dashboard-section">
                <div class="row g-4">
                <div class="col-md-6">
                    <div class="chart-card premium-card dashboard-glass-panel">
                        <h5 class="mb-3">
                            <i class="bi bi-pie-chart text-success"></i>
                            Distribución por Nivel
                        </h5>
                        <div class="chart-placeholder chart-placeholder-md">
                            <div class="text-center">
                                <i class="bi bi-pie-chart fs-2 mb-2"></i>
                                <p class="mb-0">Gráfico de Distribución</p>
                                <small class="text-muted">Datos por nivel académico</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="chart-card premium-card dashboard-glass-panel">
                        <h5 class="mb-3">
                            <i class="bi bi-bar-chart text-warning"></i>
                            Rendimiento Académico
                        </h5>
                        <div class="chart-placeholder chart-placeholder-md">
                            <div class="text-center">
                                <i class="bi bi-bar-chart fs-2 mb-2"></i>
                                <p class="mb-0">Gráfico de Rendimiento</p>
                                <small class="text-muted">Promedio por materia</small>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?= htmlspecialchars($basePath) ?>/assets/js/ui-premium.js"></script>
</body>
</html>

<?php
// Helper methods for the view
class DashboardViewHelper {
    public function getActivityIcon($type) {
        switch ($type) {
            case 'enrollment': return 'bi bi-person-plus';
            case 'course': return 'bi bi-book';
            case 'payment': return 'bi bi-currency-dollar';
            case 'subject': return 'bi bi-journal-bookmark';
            case 'teacher': return 'bi bi-easel';
            case 'student': return 'bi bi-person';
            default: return 'bi bi-info-circle';
        }
    }
    
    public function getActivityIconClass($type) {
        switch ($type) {
            case 'enrollment': return 'bg-success text-white';
            case 'course': return 'bg-primary text-white';
            case 'payment': return 'bg-success text-white';
            case 'subject': return 'bg-info text-white';
            case 'teacher': return 'bg-warning text-white';
            case 'student': return 'bg-secondary text-white';
            default: return 'bg-secondary text-white';
        }
    }
    
    public function formatTimeAgo($timestamp) {
        $time = strtotime($timestamp);
        $diff = time() - $time;
        
        if ($diff < 60) {
            return 'Hace un momento';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return 'Hace ' . $mins . ' minuto' . ($mins > 1 ? 's' : '');
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return 'Hace ' . $hours . ' hora' . ($hours > 1 ? 's' : '');
        } else {
            $days = floor($diff / 86400);
            return 'Hace ' . $days . ' día' . ($days > 1 ? 's' : '');
        }
    }
}

$helper = new DashboardViewHelper();
?>
