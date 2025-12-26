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
                <h1><i class="fas fa-calendar-alt"></i> Periodos Académicos</h1>
                <p class="lead">Da de alta, edita y administra los periodos académicos del sistema</p>
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

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPeriodModal">
                        <i class="fas fa-plus-circle"></i> Nuevo Periodo
                    </button>
                    <span class="text-muted">Total: <?= count($periods ?? []) ?> periodos</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Lista de Periodos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Código</th>
                                    <th>Inscripciones</th>
                                    <th>Fechas</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($periods)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No hay periodos registrados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($periods as $period): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($period['name'] ?? '') ?></div>
                                            </td>
                                            <td><span class="badge bg-primary"><?= htmlspecialchars($period['code'] ?? 'N/A') ?></span></td>
                                            <td>
                                                <?= htmlspecialchars($period['enrollment_start'] ?? 'N/A') ?>
                                                -
                                                <?= htmlspecialchars($period['enrollment_end'] ?? 'N/A') ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($period['start_date'] ?? 'N/A') ?>
                                                -
                                                <?= htmlspecialchars($period['end_date'] ?? 'N/A') ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= ($period['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>">
                                                    <?= htmlspecialchars($period['status'] ?? 'inactive') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-period-edit
                                                    data-id="<?= (int) $period['id'] ?>"
                                                    data-name="<?= htmlspecialchars($period['name'] ?? '', ENT_QUOTES) ?>"
                                                    data-code="<?= htmlspecialchars($period['code'] ?? '', ENT_QUOTES) ?>"
                                                    data-enrollment-start="<?= htmlspecialchars($period['enrollment_start'] ?? '', ENT_QUOTES) ?>"
                                                    data-enrollment-end="<?= htmlspecialchars($period['enrollment_end'] ?? '', ENT_QUOTES) ?>"
                                                    data-start-date="<?= htmlspecialchars($period['start_date'] ?? '', ENT_QUOTES) ?>"
                                                    data-end-date="<?= htmlspecialchars($period['end_date'] ?? '', ENT_QUOTES) ?>"
                                                    data-status="<?= htmlspecialchars($period['status'] ?? '', ENT_QUOTES) ?>"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-period-delete
                                                    data-id="<?= (int) $period['id'] ?>"
                                                    data-name="<?= htmlspecialchars($period['name'] ?? '', ENT_QUOTES) ?>"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createPeriodModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Crear Periodo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= htmlspecialchars($basePath . '/periods') ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_period">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Código *</label>
                                <input type="text" class="form-control" name="code" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Inicio de inscripciones</label>
                                <input type="date" class="form-control" name="enrollment_start">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Fin de inscripciones</label>
                                <input type="date" class="form-control" name="enrollment_end">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Fecha inicio</label>
                                <input type="date" class="form-control" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Fecha fin</label>
                                <input type="date" class="form-control" name="end_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estado *</label>
                                <select class="form-select" name="status" required>
                                    <option value="inactive">Inactivo</option>
                                    <option value="active">Activo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Periodo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editPeriodModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Periodo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= htmlspecialchars($basePath . '/periods') ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_period">
                    <input type="hidden" id="editPeriodId" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="editPeriodName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Código</label>
                                <input type="text" class="form-control" id="editPeriodCode" name="code" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Inicio de inscripciones</label>
                                <input type="date" class="form-control" id="editInscriptionStart" name="enrollment_start">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Fin de inscripciones</label>
                                <input type="date" class="form-control" id="editInscriptionEnd" name="enrollment_end">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Fecha inicio</label>
                                <input type="date" class="form-control" id="editTermStart" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Fecha fin</label>
                                <input type="date" class="form-control" id="editTermEnd" name="end_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estado *</label>
                                <select class="form-select" id="editPeriodStatus" name="status" required>
                                    <option value="inactive">Inactivo</option>
                                    <option value="active">Activo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deletePeriodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-warning"></i> Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar este periodo?</p>
                <p><strong>Periodo:</strong> <span id="deletePeriodName"></span></p>
            </div>
            <form method="POST" action="<?= htmlspecialchars($basePath . '/periods') ?>">
                <div class="modal-footer">
                    <input type="hidden" name="action" value="delete_period">
                    <input type="hidden" id="deletePeriodId" name="id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-period-edit]').forEach((button) => {
        button.addEventListener('click', () => {
            const dataset = button.dataset;
            document.getElementById('editPeriodId').value = dataset.id || '';
            document.getElementById('editPeriodName').value = dataset.name || '';
            document.getElementById('editPeriodCode').value = dataset.code || '';
            document.getElementById('editInscriptionStart').value = dataset.enrollmentStart || '';
            document.getElementById('editInscriptionEnd').value = dataset.enrollmentEnd || '';
            document.getElementById('editTermStart').value = dataset.startDate || '';
            document.getElementById('editTermEnd').value = dataset.endDate || '';
            document.getElementById('editPeriodStatus').value = dataset.status || 'inactive';
            const modal = new bootstrap.Modal(document.getElementById('editPeriodModal'));
            modal.show();
        });
    });

    document.querySelectorAll('[data-period-delete]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('deletePeriodId').value = button.dataset.id || '';
            document.getElementById('deletePeriodName').textContent = button.dataset.name || '';
            const modal = new bootstrap.Modal(document.getElementById('deletePeriodModal'));
            modal.show();
        });
    });
});
</script>

<style>
.page-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}
</style>
