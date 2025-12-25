<?php
$basePath = rtrim($basePath ?? '', '/');
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/login');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-chalkboard-teacher"></i> Gestión de Profesores</h1>
                <p class="lead">Crea profesores y visualiza su información principal</p>
            </div>
        </div>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-plus"></i> Nuevo profesor</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= htmlspecialchars($basePath . '/teachers') ?>">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select name="status" class="form-select">
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Crear profesor
                        </button>
                    </form>
                </div>
            </div>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Los profesores también pueden inscribirse como alumnos desde la vista de inscripciones.
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Profesores registrados</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($teachers)): ?>
                        <p class="text-muted">No hay profesores registrados.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Matrícula</th>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($teacher['matricula']) ?></td>
                                            <td><?= htmlspecialchars($teacher['name']) ?></td>
                                            <td><?= htmlspecialchars($teacher['email']) ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($teacher['status']) ?></span></td>
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
