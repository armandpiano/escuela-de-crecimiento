<?php
$basePath = rtrim($basePath ?? '', '/');
$homePath = $basePath !== '' ? $basePath . '/' : '/';
$isAuthenticated = isset($_SESSION['user_id']);
$userRole = $_SESSION['user_role'] ?? '';
$displayName = $_SESSION['user_name'] ?? 'Usuario';
$breadcrumbs = $breadcrumbs ?? [];
$pageTitle = $pageTitle ?? 'Escuela de Crecimiento';
$pageSubtitle = $pageSubtitle ?? '';
$displayTitle = strpos($pageTitle, ' - ') !== false ? explode(' - ', $pageTitle)[0] : $pageTitle;
$pageShellClass = $pageShellClass ?? '';
$topbarClass = $topbarClass ?? '';
$faviconPath = $basePath !== '' ? $basePath . '/public/uploads/logo-afc.png' : '/public/uploads/logo-afc.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($faviconPath) ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($faviconPath) ?>">
    <link href="<?= htmlspecialchars($basePath) ?>/assets/css/ui-premium.css" rel="stylesheet">
</head>
<body class="app-body">
    <div class="loading loading-overlay position-fixed top-0 start-0 w-100 h-100">
        <div class="loading-content">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <span class="loading-text">Cargando datos...</span>
        </div>
    </div>

    <?php if ($isAuthenticated): ?>
        <div class="app-shell admin-page<?= $pageShellClass ? ' ' . htmlspecialchars($pageShellClass) : '' ?>">
            <nav class="sidebar" id="sidebar">
                <div class="sidebar-header">
                    <a href="<?= htmlspecialchars($homePath) ?>" class="logo">
                        <i class="bi bi-mortarboard"></i>
                        Escuela de Crecimiento
                    </a>
                    <div class="user-info">
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($displayName) ?></div>
                                <small class="opacity-75"><?= htmlspecialchars(ucfirst($userRole) ?: 'Usuario') ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="nav-menu">
                    <?php if ($userRole === 'admin'): ?>
                        <div class="sidebar-section-title">Control Escolar</div>
                        <div class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/modules') ?>">
                                <i class="bi bi-grid-1x2"></i>
                                Módulos
                            </a>
                        </div>
                        <div class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/subjects') ?>">
                                <i class="bi bi-journal-bookmark"></i>
                                Materias
                            </a>
                        </div>
                        <div class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/students') ?>">
                                <i class="bi bi-people"></i>
                                Alumnos
                            </a>
                        </div>
                        <div class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/teachers') ?>">
                                <i class="bi bi-easel"></i>
                                Profesores
                            </a>
                        </div>
                        <div class="sidebar-section-title">Operaciones</div>
                        <div class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/courses') ?>">
                                <i class="bi bi-book"></i>
                                Cursos
                            </a>
                        </div>
                        <div class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/periods') ?>">
                                <i class="bi bi-calendar3"></i>
                                Periodos
                            </a>
                        </div>
                    <?php elseif ($userRole === 'teacher'): ?>
                        <div class="sidebar-section-title">Panel Docente</div>
                        <div class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/dashboard') ?>">
                                <i class="bi bi-speedometer2"></i>
                                Mis Cursos
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="sidebar-section-title">Panel Estudiante</div>
                        <div class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/enrollments') ?>">
                                <i class="bi bi-person-graduate"></i>
                                Mis Inscripciones
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="sidebar-section-title">General</div>
                    <div class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($basePath . '/about') ?>">
                            <i class="bi bi-info-circle"></i>
                            Acerca de
                        </a>
                    </div>
                </div>
            </nav>

            <div class="main-content" id="mainContent">
                <header class="topbar<?= $topbarClass ? ' ' . htmlspecialchars($topbarClass) : '' ?>">
                    <div class="container-premium topbar-inner">
                        <div class="d-flex align-items-center">
                            <button class="btn-toggle-sidebar" id="toggleSidebar" type="button">
                                <i class="bi bi-list"></i>
                            </button>
                            <div>
                                <div class="fw-semibold text-uppercase text-muted small">Control Escolar</div>
                                <div class="fw-bold"><?= htmlspecialchars($displayTitle) ?></div>
                            </div>
                        </div>
                        <div class="topbar-actions">
                            <div class="user-chip">
                                <i class="bi bi-person-circle text-primary"></i>
                                <?= htmlspecialchars($displayName) ?>
                            </div>
                            <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($basePath . '/logout') ?>">
                                <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </header>

                <?php if (!empty($breadcrumbs)): ?>
                    <nav class="breadcrumb-nav" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                                <?php
                                $label = $breadcrumb['label'] ?? '';
                                $url = $breadcrumb['url'] ?? null;
                                $isLast = $index === array_key_last($breadcrumbs);
                                ?>
                                <?php if ($url && !$isLast): ?>
                                    <li class="breadcrumb-item"><a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($label) ?></a></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($label) ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                <?php endif; ?>

                <main class="content app-content container-premium">
    <?php else: ?>
        <nav class="navbar navbar-expand-lg navbar-dark app-navbar">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?= htmlspecialchars($homePath) ?>">
                    <i class="bi bi-mortarboard me-2"></i>
                    Escuela de Crecimiento
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($homePath) ?>">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/courses') ?>">Cursos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/about') ?>">Acerca de</a>
                        </li>
                    </ul>

                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/auth/login') ?>">Iniciar Sesión</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/auth/register') ?>">Registrarse</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="app-main">
    <?php endif; ?>
