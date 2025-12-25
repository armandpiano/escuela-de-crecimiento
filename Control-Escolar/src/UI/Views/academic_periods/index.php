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
                <h1><i class="fas fa-calendar-alt"></i> Gestión de Cuatrimestres</h1>
                <p class="lead">Crea y administra los cuatrimestres del sistema</p>
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
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Nuevo cuatrimestre</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= htmlspecialchars($basePath . '/academic-periods') ?>">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha de inicio</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha de fin</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Inicio de inscripciones</label>
                            <input type="date" name="enrollment_start" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fin de inscripciones</label>
                            <input type="date" name="enrollment_end" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select name="status" class="form-select">
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Crear cuatrimestre
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Cuatrimestres registrados</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($academicPeriods)): ?>
                        <p class="text-muted">No hay cuatrimestres registrados.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Fechas</th>
                                        <th>Inscripción</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($academicPeriods as $period): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($period['name']) ?></td>
                                            <td><?= htmlspecialchars($period['start_date']) ?> - <?= htmlspecialchars($period['end_date']) ?></td>
                                            <td>
                                                <?= htmlspecialchars($period['enrollment_start'] ?? 'N/D') ?>
                                                <br>
                                                <?= htmlspecialchars($period['enrollment_end'] ?? 'N/D') ?>
                                            </td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($period['status']) ?></span></td>
                                            <td>
                                                <form method="POST" action="<?= htmlspecialchars($basePath . '/academic-periods') ?>" class="d-inline">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="period_id" value="<?= (int) $period['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar cuatrimestre?')">
                                                        <i class="fas fa-trash"></i>
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
