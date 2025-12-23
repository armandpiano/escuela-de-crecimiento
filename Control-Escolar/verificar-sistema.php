<?php
/**
 * Archivo de verificación del Sistema Christian LMS - Control-Escolar
 * Accede a este archivo para verificar que todo funciona correctamente
 */

// Verificar versión de PHP
$phpVersion = phpversion();
$phpOk = version_compare($phpVersion, '8.0', '>=');

// Verificar extensiones necesarias
$extensions = ['pdo', 'pdo_mysql', 'json', 'openssl'];
$extensionsOk = true;
$missingExtensions = [];

foreach ($extensions as $ext) {
    if (!extension_loaded($ext)) {
        $extensionsOk = false;
        $missingExtensions[] = $ext;
    }
}

// Verificar archivo de configuración
$configExists = file_exists(__DIR__ . '/config/database.php');

// Verificar estructura de archivos
$requiredFiles = [
    'public/index.php',
    'src/UI/Controllers/DashboardController.php',
    'src/UI/Views/dashboard/index.php'
];
$filesOk = true;
$missingFiles = [];

foreach ($requiredFiles as $file) {
    if (!file_exists(__DIR__ . '/' . $file)) {
        $filesOk = false;
        $missingFiles[] = $file;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación - Christian LMS Control-Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .check-item { margin-bottom: 1rem; padding: 1rem; border-radius: 0.5rem; }
        .check-ok { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .check-error { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .check-warning { background-color: #fff3cd; border: 1px solid #ffeaa7; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h1 class="mb-0">
                            <i class="fas fa-check-circle"></i>
                            Verificación del Sistema Christian LMS
                        </h1>
                        <p class="mb-0">Control-Escolar - Configuración y Estado</p>
                    </div>
                    <div class="card-body">
                        
                        <!-- Estado General -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h3><i class="fas fa-server"></i> Estado del Servidor</h3>
                            </div>
                        </div>
                        
                        <!-- PHP Version -->
                        <div class="check-item <?php echo $phpOk ? 'check-ok' : 'check-error'; ?>">
                            <div class="d-flex align-items-center">
                                <i class="<?php echo $phpOk ? 'fas fa-check-circle status-ok' : 'fas fa-times-circle status-error'; ?> fa-2x me-3"></i>
                                <div>
                                    <h5 class="mb-1">Versión de PHP</h5>
                                    <p class="mb-0">
                                        Versión actual: <strong><?php echo $phpVersion; ?></strong>
                                        <?php if ($phpOk): ?>
                                            <span class="status-ok">✓ Compatible</span>
                                        <?php else: ?>
                                            <span class="status-error">✗ Se requiere PHP 8.0 o superior</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Extensiones PHP -->
                        <div class="check-item <?php echo $extensionsOk ? 'check-ok' : 'check-error'; ?>">
                            <div class="d-flex align-items-center">
                                <i class="<?php echo $extensionsOk ? 'fas fa-check-circle status-ok' : 'fas fa-times-circle status-error'; ?> fa-2x me-3"></i>
                                <div>
                                    <h5 class="mb-1">Extensiones PHP</h5>
                                    <p class="mb-0">
                                        <?php if ($extensionsOk): ?>
                                            <span class="status-ok">✓ Todas las extensiones requeridas están disponibles</span>
                                        <?php else: ?>
                                            <span class="status-error">✗ Faltan extensiones: <?php echo implode(', ', $missingExtensions); ?></span>
                                        <?php endif; ?>
                                    </p>
                                    <small class="text-muted">Requeridas: PDO, PDO_MySQL, JSON, OpenSSL</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Archivos del Sistema -->
                        <div class="row mb-4 mt-4">
                            <div class="col-12">
                                <h3><i class="fas fa-folder-open"></i> Archivos del Sistema</h3>
                            </div>
                        </div>
                        
                        <div class="check-item <?php echo $filesOk ? 'check-ok' : 'check-error'; ?>">
                            <div class="d-flex align-items-center">
                                <i class="<?php echo $filesOk ? 'fas fa-check-circle status-ok' : 'fas fa-times-circle status-error'; ?> fa-2x me-3"></i>
                                <div>
                                    <h5 class="mb-1">Estructura de Archivos</h5>
                                    <p class="mb-0">
                                        <?php if ($filesOk): ?>
                                            <span class="status-ok">✓ Todos los archivos principales están presentes</span>
                                        <?php else: ?>
                                            <span class="status-error">✗ Faltan archivos: <?php echo implode(', ', $missingFiles); ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Configuración -->
                        <div class="check-item <?php echo $configExists ? 'check-ok' : 'check-warning'; ?>">
                            <div class="d-flex align-items-center">
                                <i class="<?php echo $configExists ? 'fas fa-check-circle status-ok' : 'fas fa-exclamation-triangle status-warning'; ?> fa-2x me-3"></i>
                                <div>
                                    <h5 class="mb-1">Configuración de Base de Datos</h5>
                                    <p class="mb-0">
                                        <?php if ($configExists): ?>
                                            <span class="status-ok">✓ Archivo de configuración encontrado</span>
                                        <?php else: ?>
                                            <span class="status-warning">⚠ Archivo de configuración no encontrado</span>
                                        <?php endif; ?>
                                    </p>
                                    <small class="text-muted">Archivo: config/database.php</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información del Sistema -->
                        <div class="row mb-4 mt-4">
                            <div class="col-12">
                                <h3><i class="fas fa-info-circle"></i> Información del Sistema</h3>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6><i class="fas fa-folder"></i> Directorio Actual</h6>
                                        <code><?php echo __DIR__; ?></code>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6><i class="fas fa-globe"></i> URL Actual</h6>
                                        <code><?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?></code>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Próximos Pasos -->
                        <div class="row mb-4 mt-4">
                            <div class="col-12">
                                <h3><i class="fas fa-rocket"></i> Próximos Pasos</h3>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-lightbulb"></i> Para completar la instalación:</h6>
                            <ol class="mb-0">
                                <li><strong>Configurar Base de Datos:</strong> Editar <code>config/database.php</code> con tus credenciales</li>
                                <li><strong>Importar Base de Datos:</strong> Ejecutar <code>database.sql</code> en phpMyAdmin</li>
                                <li><strong>Acceder al Sistema:</strong> Ir a <a href="public/" class="alert-link">public/</a> para usar el control escolar</li>
                                <li><strong>Integrar con Landing:</strong> Agregar enlace en tu página principal</li>
                            </ol>
                        </div>
                        
                        <!-- Enlaces Útiles -->
                        <div class="text-center mt-4">
                            <a href="public/" class="btn btn-primary btn-lg me-2">
                                <i class="fas fa-graduation-cap"></i> Acceder al Control Escolar
                            </a>
                            <a href="INTEGRACION-XAMPP.md" class="btn btn-outline-info">
                                <i class="fas fa-book"></i> Ver Instrucciones
                            </a>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>