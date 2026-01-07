<?php
$basePath = rtrim($basePath ?? '', '/');
$homePath = $basePath !== '' ? $basePath . '/' : '/';
$isAuthenticated = isset($_SESSION['user_id']);
$displayName = $_SESSION['user_name'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Christian LMS' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($basePath) ?>/assets/css/ui-premium.css" rel="stylesheet">
</head>
<body class="app-body">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark app-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= htmlspecialchars($homePath) ?>">
                <i class="bi bi-mortarboard me-2"></i>
                Christian LMS
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($homePath) ?>">Inicio</a>
                    </li>
                    <?php if (!$isAuthenticated): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/courses') ?>">Cursos</a>
                        </li>
                    <?php elseif (($_SESSION['user_role'] ?? '') === 'student'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/enrollments') ?>">Mis Inscripciones</a>
                        </li>
                    <?php elseif (($_SESSION['user_role'] ?? '') === 'teacher'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/dashboard') ?>">Mis Cursos</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/courses') ?>">Cursos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/teachers') ?>">Profesores</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/students') ?>">Alumnos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/subjects') ?>">Materias</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/periods') ?>">Periodos</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($basePath . '/about') ?>">Acerca de</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($isAuthenticated): ?>
                        <li class="nav-item">
                            <span class="nav-link text-white-50">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= htmlspecialchars($displayName) ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/logout') ?>">
                                <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/auth/login') ?>">Iniciar Sesión</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/auth/register') ?>">Registrarse</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Loading Overlay -->
    <div class="loading is-active position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index: 9999;">
        <div class="loading-content">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <span class="loading-text">Cargando datos...</span>
        </div>
    </div>

    <!-- Main Content -->
    <main class="app-main">
