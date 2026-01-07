<?php $basePath = $basePath ?? '/Control-Escolar'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Christian LMS - Sistema de Gestión Educativa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= htmlspecialchars($basePath) ?>/assets/css/ui-premium.css" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-card-body">
                <div class="auth-info">
                    <div class="auth-logo">
                        <i class="bi bi-mortarboard brand-icon"></i>
                        <h2 class="fw-bold mb-1">Christian LMS</h2>
                        <p class="mb-0 text-white-50">Sistema de Gestión Educativa</p>
                    </div>
                    <div>
                        <h4 class="fw-semibold">Acceso seguro para tu comunidad académica</h4>
                        <p class="text-white-50 mb-0">
                            Administra cursos, inscripciones y materias desde un entorno confiable y moderno.
                        </p>
                    </div>
                    <ul class="auth-info-list">
                        <li><i class="bi bi-shield-check"></i><span>Protección de datos y control de acceso por roles.</span></li>
                        <li><i class="bi bi-clipboard-check"></i><span>Gestión centralizada de cursos y periodos.</span></li>
                        <li><i class="bi bi-graph-up"></i><span>Seguimiento claro del progreso académico.</span></li>
                    </ul>
                </div>

                <div class="auth-form-section">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            <?= htmlspecialchars($_SESSION['success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h3 class="fw-bold mb-1">Iniciar sesión</h3>
                        <p class="text-muted mb-0">Ingresa tus credenciales para continuar.</p>
                    </div>

                    <form method="POST" action="<?= htmlspecialchars($basePath) ?>/auth/login">
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-2"></i>Correo Electrónico
                            </label>
                            <input type="email"
                                   class="form-control"
                                   id="email"
                                   name="email"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   required
                                   placeholder="tu@email.com">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-2"></i>Contraseña
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control"
                                       id="password"
                                       name="password"
                                       required
                                       placeholder="Tu contraseña">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Recordar sesión
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 text-white fw-bold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="<?= htmlspecialchars($basePath) ?>/auth/forgot-password" class="text-decoration-none text-muted">
                            <i class="bi bi-key me-1"></i>¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <div class="auth-form-footer text-center mt-3">
                        <small class="text-muted">
                            ¿No tienes cuenta?
                            <a href="<?= htmlspecialchars($basePath) ?>/auth/register" class="text-decoration-none">Regístrate aquí</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?= htmlspecialchars($basePath) ?>/assets/js/ui-premium.js"></script>
</body>
</html>
