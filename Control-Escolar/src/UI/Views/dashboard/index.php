<?php
$userName = $userName ?? 'Usuario';
$userRole = $userRole ?? 'Estudiante';
$dashboardStats = $dashboardStats ?? [];
$recentActivity = $recentActivity ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Christian LMS</title>
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
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --sidebar-width: 280px;
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

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        .logo i {
            margin-right: 0.75rem;
            font-size: 2rem;
            color: #ffd700;
        }

        .user-info {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 0.75rem;
            backdrop-filter: blur(10px);
        }

        .user-avatar {
            width: 3rem;
            height: 3rem;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-right: 1rem;
        }

        .nav-menu {
            padding: 1.5rem 0;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0;
            border-left: 3px solid transparent;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #ffd700;
            transform: translateX(5px);
        }

        .nav-link i {
            width: 1.5rem;
            margin-right: 0.75rem;
            text-align: center;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        .topbar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .content {
            padding: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            border: 1px solid #e9ecef;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .stat-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .stat-change {
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .chart-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            border: 1px solid #e9ecef;
        }

        .activity-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            border: 1px solid #e9ecef;
            max-height: 500px;
            overflow-y: auto;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f1f3f4;
            transition: background-color 0.2s ease;
        }

        .activity-item:hover {
            background-color: #f8f9fa;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 0.875rem;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .activity-description {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .activity-time {
            color: #adb5bd;
            font-size: 0.75rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .quick-action {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: #495057;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .quick-action:hover {
            color: #495057;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: var(--primary-color);
        }

        .quick-action i {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            color: var(--primary-color);
        }

        .quick-action h6 {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .quick-action p {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 0;
        }

        .btn-toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #6c757d;
            margin-right: 1rem;
            cursor: pointer;
        }

        .user-menu {
            display: flex;
            align-items: center;
        }

        .user-menu img {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            margin-right: 0.75rem;
        }

        .alert {
            border: none;
            border-radius: 0.75rem;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .loading i {
            font-size: 2rem;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .content {
                padding: 1rem;
            }
        }

        .bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-gradient-success { background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); }
        .bg-gradient-warning { background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%); }
        .bg-gradient-info { background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); }
        .bg-gradient-danger { background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%); }
        .bg-gradient-secondary { background: linear-gradient(135deg, #b2bec3 0%, #636e72 100%); }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="/dashboard" class="logo">
                <i class="fas fa-graduation-cap"></i>
                Christian LMS
            </a>
            <div class="user-info">
                <div class="d-flex align-items-center">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
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
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="/dashboard/courses" class="nav-link">
                    <i class="fas fa-book"></i>
                    Cursos
                </a>
            </div>
            <div class="nav-item">
                <a href="/dashboard/enrollments" class="nav-link">
                    <i class="fas fa-user-graduate"></i>
                    Inscripciones
                </a>
            </div>
            <div class="nav-item">
                <a href="/dashboard/subjects" class="nav-link">
                    <i class="fas fa-book-open"></i>
                    Materias
                </a>
            </div>
            <div class="nav-item">
                <a href="/dashboard/students" class="nav-link">
                    <i class="fas fa-users"></i>
                    Estudiantes
                </a>
            </div>
            <div class="nav-item">
                <a href="/dashboard/teachers" class="nav-link">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Profesores
                </a>
            </div>
            <div class="nav-item">
                <a href="/dashboard/reports" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    Reportes
                </a>
            </div>
            <div class="nav-item">
                <a href="/dashboard/settings" class="nav-link">
                    <i class="fas fa-cog"></i>
                    Configuración
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Topbar -->
        <header class="topbar">
            <div class="d-flex align-items-center">
                <button class="btn-toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h1 class="page-title mb-0">Dashboard</h1>
                    <p class="page-subtitle mb-0">Panel de Control del Sistema Christian LMS</p>
                </div>
            </div>
            <div class="user-menu">
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-2x text-primary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/profile"><i class="fas fa-user me-2"></i>Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="/settings"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="content">
            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="/dashboard/courses" class="quick-action">
                    <i class="fas fa-plus-circle"></i>
                    <h6>Nuevo Curso</h6>
                    <p>Crear un nuevo curso</p>
                </a>
                <a href="/dashboard/enrollments" class="quick-action">
                    <i class="fas fa-user-plus"></i>
                    <h6>Inscribir Estudiante</h6>
                    <p>Registrar inscripción</p>
                </a>
                <a href="/dashboard/subjects" class="quick-action">
                    <i class="fas fa-book-open"></i>
                    <h6>Nueva Materia</h6>
                    <p>Agregar materia</p>
                </a>
                <a href="/dashboard/reports" class="quick-action">
                    <i class="fas fa-chart-line"></i>
                    <h6>Ver Reportes</h6>
                    <p>Análisis del sistema</p>
                </a>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon bg-gradient-primary text-white">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-value text-primary"><?php echo $dashboardStats['total_courses'] ?? 0; ?></div>
                    <div class="stat-label">Total Cursos</div>
                    <div class="stat-change text-success">
                        <i class="fas fa-arrow-up"></i>
                        <?php echo $dashboardStats['active_courses'] ?? 0; ?> activos
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-gradient-success text-white">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-value text-success"><?php echo $dashboardStats['total_enrollments'] ?? 0; ?></div>
                    <div class="stat-label">Inscripciones</div>
                    <div class="stat-change text-info">
                        <i class="fas fa-clock"></i>
                        <?php echo $dashboardStats['pending_enrollments'] ?? 0; ?> pendientes
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-gradient-warning text-white">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value text-warning"><?php echo $dashboardStats['total_students'] ?? 0; ?></div>
                    <div class="stat-label">Estudiantes</div>
                    <div class="stat-change text-success">
                        <i class="fas fa-user-plus"></i> Este mes
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-gradient-info text-white">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-value text-info"><?php echo $dashboardStats['total_subjects'] ?? 0; ?></div>
                    <div class="stat-label">Materias</div>
                    <div class="stat-change text-primary">
                        <i class="fas fa-star"></i>
                        <?php echo $dashboardStats['average_grade'] ?? 0; ?> promedio
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-gradient-secondary text-white">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-value text-secondary"><?php echo $dashboardStats['total_teachers'] ?? 0; ?></div>
                    <div class="stat-label">Profesores</div>
                    <div class="stat-change text-info">
                        <i class="fas fa-clock"></i> Activos
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-gradient-danger text-white">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-value text-danger">$<?php echo number_format($dashboardStats['monthly_revenue'] ?? 0, 2); ?></div>
                    <div class="stat-label">Ingresos Mensuales</div>
                    <div class="stat-change text-success">
                        <i class="fas fa-percentage"></i>
                        <?php echo $dashboardStats['completion_rate'] ?? 0; ?>% completación
                    </div>
                </div>
            </div>

            <!-- Charts and Activity Row -->
            <div class="row">
                <div class="col-xl-8">
                    <div class="chart-card">
                        <h5 class="mb-3">
                            <i class="fas fa-chart-line text-primary"></i>
                            Resumen de Inscripciones
                        </h5>
                        <div class="chart-placeholder" style="height: 300px; background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%), linear-gradient(-45deg, #f8f9fa 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f8f9fa 75%), linear-gradient(-45deg, transparent 75%, #f8f9fa 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                            <div class="text-center">
                                <i class="fas fa-chart-area fa-3x mb-2"></i>
                                <p class="mb-0">Gráfico de Inscripciones por Mes</p>
                                <small class="text-muted">Integración con Chart.js próximamente</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-4">
                    <div class="activity-card">
                        <h5 class="mb-3">
                            <i class="fas fa-history text-primary"></i>
                            Actividad Reciente
                        </h5>
                        
                        <?php if (empty($recentActivity)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No hay actividad reciente</p>
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
                                            <i class="fas fa-user me-1"></i>
                                            <?php echo htmlspecialchars($activity['user']); ?>
                                        </div>
                                        <div class="activity-time">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo $this->formatTimeAgo($activity['timestamp']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Additional Stats Row -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="chart-card">
                        <h5 class="mb-3">
                            <i class="fas fa-pie-chart text-success"></i>
                            Distribución por Nivel
                        </h5>
                        <div class="chart-placeholder" style="height: 250px; background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%), linear-gradient(-45deg, #f8f9fa 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f8f9fa 75%), linear-gradient(-45deg, transparent 75%, #f8f9fa 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                            <div class="text-center">
                                <i class="fas fa-chart-pie fa-2x mb-2"></i>
                                <p class="mb-0">Gráfico de Distribución</p>
                                <small class="text-muted">Datos por nivel académico</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="chart-card">
                        <h5 class="mb-3">
                            <i class="fas fa-bar-chart text-warning"></i>
                            Rendimiento Académico
                        </h5>
                        <div class="chart-placeholder" style="height: 250px; background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%), linear-gradient(-45deg, #f8f9fa 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f8f9fa 75%), linear-gradient(-45deg, transparent 75%, #f8f9fa 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                            <div class="text-center">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                <p class="mb-0">Gráfico de Rendimiento</p>
                                <small class="text-muted">Promedio por materia</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            const toggleSidebar = document.getElementById('toggleSidebar');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            toggleSidebar.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                mainContent.classList.toggle('expanded');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(event.target) && !toggleSidebar.contains(event.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('show');
                    mainContent.classList.remove('expanded');
                }
            });
            
            // Set active nav item based on current URL
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
            
            // Auto-refresh stats every 5 minutes
            setInterval(function() {
                loadStats();
            }, 300000);
        });
        
        function loadStats() {
            // Implement AJAX call to refresh stats
            console.log('Refreshing stats...');
        }
    </script>
</body>
</html>

<?php
// Helper methods for the view
class DashboardViewHelper {
    public function getActivityIcon($type) {
        switch ($type) {
            case 'enrollment': return 'fas fa-user-plus';
            case 'course': return 'fas fa-book';
            case 'payment': return 'fas fa-dollar-sign';
            case 'subject': return 'fas fa-book-open';
            case 'teacher': return 'fas fa-chalkboard-teacher';
            case 'student': return 'fas fa-user';
            default: return 'fas fa-info-circle';
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