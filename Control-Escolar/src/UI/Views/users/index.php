<?php
$basePath = rtrim($basePath ?? '', '/');
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/login');
    exit();
}

$userRole = $_SESSION['user_role'] ?? '';
if ($userRole !== 'admin') {
    header('Location: ' . $basePath . '/dashboard');
    exit();
}
?>

<div class="container-xxl app-content">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-people me-2"></i> Usuarios</h1>
            <p class="page-subtitle">Consulta profesores y alumnos registrados en el sistema.</p>
        </div>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle me-1"></i>
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i>
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-easel me-2"></i> Profesores</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($teachers)): ?>
                        <p class="text-muted">No hay profesores registrados.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($teacher['name']) ?></td>
                                            <td><?= htmlspecialchars($teacher['email']) ?></td>
                                            <td>
                                                <span class="badge <?= $teacher['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= htmlspecialchars($teacher['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i> Alumnos</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($students)): ?>
                        <p class="text-muted">No hay alumnos registrados.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($student['name']) ?></td>
                                            <td><?= htmlspecialchars($student['email']) ?></td>
                                            <td>
                                                <span class="badge <?= $student['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= htmlspecialchars($student['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <form method="POST" action="<?= htmlspecialchars($basePath . '/users') ?>" class="d-inline">
                                                    <input type="hidden" name="action" value="reset_student_password">
                                                    <input type="hidden" name="student_id" value="<?= (int) $student['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-key me-1"></i> Generar contraseña
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
