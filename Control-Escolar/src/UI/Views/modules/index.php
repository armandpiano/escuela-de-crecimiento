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

<div class="container-xxl app-content admin-premium-page">
    <div class="page-header admin-premium-header">
        <div>
            <h1 class="page-title"><i class="bi bi-grid-1x2 me-2"></i> Módulos</h1>
            <p class="page-subtitle">Crea, organiza y asigna módulos a las materias del plan académico.</p>
        </div>
        <div class="page-header-actions admin-premium-actions">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModuleModal">
                <i class="bi bi-plus-circle me-1"></i> Nuevo Módulo
            </button>
        </div>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger" data-toast-message="<?= htmlspecialchars($errorMessage) ?>" data-toast-type="error">
            <i class="bi bi-exclamation-circle me-1"></i>
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success" data-toast-message="<?= htmlspecialchars($successMessage) ?>" data-toast-type="success">
            <i class="bi bi-check-circle me-1"></i>
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4 premium-card premium-filter-card">
        <div class="card-body premium-card-body">
            <form method="GET" action="<?= htmlspecialchars($basePath . '/modules') ?>" class="row g-2 align-items-center">
                <div class="col-12 col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="module_search" class="form-control" placeholder="Buscar módulo..." value="<?= htmlspecialchars($moduleSearch ?? '') ?>">
                    </div>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Buscar</button>
                    <a href="<?= htmlspecialchars($basePath . '/modules') ?>" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card premium-card">
        <div class="card-header premium-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i> Lista de Módulos
                <span class="badge bg-secondary ms-2"><?= (int) ($moduleTotal ?? count($modules ?? [])) ?></span>
            </h5>
            <span class="text-muted small">Asignaciones de materias por módulo</span>
        </div>
        <div class="card-body premium-card-body">
            <?php if (empty($modules)): ?>
                <div class="empty-state">
                    <i class="bi bi-grid"></i>
                    <div class="empty-state-title">Sin módulos registrados</div>
                    <p class="text-muted mb-0">Crea un nuevo módulo para organizar las materias.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive table-scroll">
                    <table class="table table-striped align-middle sortable-table premium-table">
                        <thead>
                            <tr>
                                <th data-sortable="true">Nombre <span class="sort-indicator"></span></th>
                                <th data-sortable="false">Descripción</th>
                                <th data-sortable="true">Orden <span class="sort-indicator"></span></th>
                                <th data-sortable="true">Estado <span class="sort-indicator"></span></th>
                                <th data-sortable="false">Materias asignadas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $module): ?>
                                <?php $subjectNames = array_filter(explode('||', $module['subject_names'] ?? '')); ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($module['name'] ?? '') ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($module['description'] ?? 'Sin descripción') ?></td>
                                    <td><?= (int) ($module['sort_order'] ?? 1) ?></td>
                                    <td>
                                        <span class="badge <?= (int) ($module['is_active'] ?? 0) === 1 ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= (int) ($module['is_active'] ?? 0) === 1 ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (empty($subjectNames)): ?>
                                            <span class="badge bg-light text-dark">Sin materias</span>
                                        <?php else: ?>
                                            <div class="table-badges">
                                                <?php foreach ($subjectNames as $subjectName): ?>
                                                    <span class="badge badge-soft-info"><?= htmlspecialchars($subjectName) ?></span>
                                                <?php endforeach; ?>
                                                <span class="badge bg-secondary"><?= (int) ($module['subject_count'] ?? 0) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-info"
                                                data-module-edit
                                                data-id="<?= (int) $module['id'] ?>"
                                                data-name="<?= htmlspecialchars($module['name'] ?? '', ENT_QUOTES) ?>"
                                                data-description="<?= htmlspecialchars($module['description'] ?? '', ENT_QUOTES) ?>"
                                                data-sort-order="<?= (int) ($module['sort_order'] ?? 1) ?>"
                                                data-is-active="<?= (int) ($module['is_active'] ?? 0) ?>"
                                                data-subject-ids="<?= htmlspecialchars($module['subject_ids'] ?? '') ?>"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="<?= htmlspecialchars($basePath . '/modules') ?>" class="d-inline">
                                                <input type="hidden" name="action" value="delete_module">
                                                <input type="hidden" name="id" value="<?= (int) $module['id'] ?>">
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-confirm-delete
                                                    data-confirm-message="¿Seguro que deseas eliminar el módulo <?= htmlspecialchars($module['name'] ?? '', ENT_QUOTES) ?>?"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($moduleTotalPages ?? 1) > 1): ?>
                    <nav aria-label="Paginación de módulos">
                        <ul class="pagination justify-content-end mb-0">
                            <?php $modulePageCurrent = (int) ($modulePage ?? 1); ?>
                            <li class="page-item <?= $modulePageCurrent <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars($basePath . '/modules?' . http_build_query(['module_search' => $moduleSearch ?? '', 'module_page' => max(1, $modulePageCurrent - 1)])) ?>">Anterior</a>
                            </li>
                            <?php for ($page = 1; $page <= ($moduleTotalPages ?? 1); $page++): ?>
                                <li class="page-item <?= $page === $modulePageCurrent ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= htmlspecialchars($basePath . '/modules?' . http_build_query(['module_search' => $moduleSearch ?? '', 'module_page' => $page])) ?>"><?= $page ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $modulePageCurrent >= ($moduleTotalPages ?? 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars($basePath . '/modules?' . http_build_query(['module_search' => $moduleSearch ?? '', 'module_page' => min(($moduleTotalPages ?? 1), $modulePageCurrent + 1)])) ?>">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="createModuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content premium-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i> Crear módulo</h5>
                    <p class="text-muted mb-0 small">Define el módulo y asigna materias si aplica.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= htmlspecialchars($basePath . '/modules') ?>" data-loading="true">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_module">
                    <div class="mb-3">
                        <label for="moduleName" class="form-label">Nombre del módulo *</label>
                        <input type="text" class="form-control" id="moduleName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="moduleDescription" class="form-label">Descripción</label>
                        <textarea class="form-control" id="moduleDescription" name="description" rows="3" placeholder="Descripción del módulo..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="moduleOrder" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="moduleOrder" name="sort_order" value="1" min="1">
                        </div>
                        <div class="col-md-8">
                            <label for="moduleSubjects" class="form-label">Materias asignadas</label>
                            <select class="form-select select2" id="moduleSubjects" name="subject_ids[]" data-enhance="select" multiple>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= (int) $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="moduleActive" name="is_active" value="1" checked>
                        <label class="form-check-label" for="moduleActive">Módulo activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Guardar módulo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content premium-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i> Editar módulo</h5>
                    <p class="text-muted mb-0 small">Actualiza el contenido y las asignaciones.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= htmlspecialchars($basePath . '/modules') ?>" data-loading="true">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_module">
                    <input type="hidden" id="editModuleId" name="id">
                    <div class="mb-3">
                        <label for="editModuleName" class="form-label">Nombre del módulo *</label>
                        <input type="text" class="form-control" id="editModuleName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editModuleDescription" class="form-label">Descripción</label>
                        <textarea class="form-control" id="editModuleDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="editModuleOrder" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="editModuleOrder" name="sort_order" min="1">
                        </div>
                        <div class="col-md-8">
                            <label for="editModuleSubjects" class="form-label">Materias asignadas</label>
                            <select class="form-select select2" id="editModuleSubjects" name="subject_ids[]" data-enhance="select" multiple>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= (int) $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="editModuleActive" name="is_active" value="1">
                        <label class="form-check-label" for="editModuleActive">Módulo activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Actualizar módulo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
