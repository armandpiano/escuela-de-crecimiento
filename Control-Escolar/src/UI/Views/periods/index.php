<?php
$basePath = rtrim($basePath ?? '', '/');
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/login');
    exit();
}
?>

<div class="container-xxl app-content admin-premium-page">
    <div class="page-header admin-premium-header">
        <div>
            <h1 class="page-title"><i class="bi bi-calendar-event me-2"></i> Periodos Académicos</h1>
            <p class="page-subtitle">Da de alta, edita y administra los periodos académicos del sistema</p>
        </div>
        <div class="page-header-actions admin-premium-actions">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPeriodModal">
                <i class="bi bi-plus-circle me-1"></i> Nuevo Periodo
            </button>
        </div>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger admin-alert">
            <i class="bi bi-exclamation-circle me-1"></i>
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success admin-alert">
            <i class="bi bi-check-circle me-1"></i>
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <div class="row mb-4 admin-section">
        <div class="col-12">
            <div class="card filter-card premium-card premium-filter-card">
                <div class="card-body premium-card-body d-flex justify-content-between align-items-center flex-wrap">
                    <span class="text-muted">Total: <?= count($periods ?? []) ?> periodos</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row admin-section">
        <div class="col-12">
            <div class="card premium-card">
                <div class="card-header premium-card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i> Lista de Periodos</h5>
                </div>
                <div class="card-body premium-card-body">
                    <div class="table-responsive premium-table-wrapper">
                        <table class="table table-striped table-hover premium-table">
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
                                            <td class="table-actions">
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
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-period-delete
                                                    data-id="<?= (int) $period['id'] ?>"
                                                    data-name="<?= htmlspecialchars($period['name'] ?? '', ENT_QUOTES) ?>"
                                                >
                                                    <i class="bi bi-trash"></i>
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
        <div class="modal-content premium-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i> Crear Periodo</h5>
                    <p class="text-muted mb-0 small">Define fechas clave y activa el periodo académico.</p>
                </div>
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
                                <select class="form-select select2" name="status" required data-enhance="select">
                                    <option value="inactive">Inactivo</option>
                                    <option value="active">Activo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Crear Periodo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editPeriodModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content premium-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i> Editar Periodo</h5>
                    <p class="text-muted mb-0 small">Actualiza fechas o estado del periodo.</p>
                </div>
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
                                <select class="form-select select2" id="editPeriodStatus" name="status" required data-enhance="select">
                                    <option value="inactive">Inactivo</option>
                                    <option value="active">Activo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deletePeriodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content premium-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-warning me-2"></i> Confirmar Eliminación</h5>
                    <p class="text-muted mb-0 small">Eliminar este periodo puede afectar inscripciones.</p>
                </div>
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
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
