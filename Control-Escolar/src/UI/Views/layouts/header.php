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
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .btn {
            border-radius: 25px;
            font-weight: 500;
            padding: 8px 20px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        
        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
        }
        
        .sidebar .nav-link {
            color: #ecf0f1;
            border-radius: 8px;
            margin: 2px 8px;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateX(5px);
        }
        
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        
        .stats-card .card-body {
            padding: 1.5rem;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: #f8f9fa;
            border: none;
            font-weight: 600;
            color: #495057;
        }
        
        .alert {
            border: none;
            border-radius: 10px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 6px 10px;
        }
        
        .modal-content {
            border: none;
            border-radius: 15px;
        }
        
        .loading {
            display: none;
            align-items: center;
            justify-content: center;
        }

        .loading.is-active {
            display: flex;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= htmlspecialchars($homePath) ?>">
                <i class="fas fa-graduation-cap me-2"></i>
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
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($basePath . '/courses') ?>">Cursos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($basePath . '/about') ?>">Acerca de</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($isAuthenticated): ?>
                        <li class="nav-item">
                            <span class="nav-link text-white-50">
                                <i class="fas fa-user-circle me-1"></i>
                                <?= htmlspecialchars($displayName) ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/logout') ?>">
                                <i class="fas fa-sign-out-alt me-1"></i>Cerrar Sesión
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
    <div class="loading position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index: 9999;">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <!-- Main Content -->
    <main>
