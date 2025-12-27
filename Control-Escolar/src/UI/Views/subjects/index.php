<?php
$basePath = rtrim($basePath ?? '', '/');
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/login');
    exit();
}
?>

<div class="container-xxl app-content">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-journal-bookmark me-2"></i> Gestión de Materias</h1>
            <p class="page-subtitle">Administra las materias y asignaturas del sistema educativo</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSubjectModal">
                <i class="bi bi-plus-circle me-1"></i> Nueva Materia
            </button>
            <button class="btn btn-outline-success" id="exportSubjects">
                <i class="bi bi-download me-1"></i> Exportar Lista
            </button>
            <button class="btn btn-outline-info" id="bulkActions">
                <i class="bi bi-list-check me-1"></i> Acciones Masivas
            </button>
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

    <!-- Búsqueda rápida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card filter-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h6 class="mb-1 text-muted">Búsqueda rápida</h6>
                            <p class="mb-0 text-muted">Encuentra materias por nombre, código o módulo.</p>
                        </div>
                        <div class="input-group" style="width: 320px;">
                            <input type="text" class="form-control" id="globalSearch" placeholder="Buscar materias...">
                            <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y búsqueda avanzada -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card filter-card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <button class="btn btn-link p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                            <i class="bi bi-funnel me-1"></i> Filtros Avanzados
                            <i class="bi bi-chevron-down ms-1"></i>
                        </button>
                    </h6>
                </div>
                <div class="collapse" id="advancedFilters">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="statusFilter" class="form-label">Estado</label>
                                <select class="form-select select2" id="statusFilter" data-enhance="select">
                                    <option value="">Todos los estados</option>
                                    <option value="active">Activa</option>
                                    <option value="inactive">Inactiva</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="categoryFilter" class="form-label">Categoría</label>
                                <select class="form-select select2" id="categoryFilter" data-enhance="select">
                                    <option value="">Todas las categorías</option>
                                    <option value="mathematics">Matemáticas</option>
                                    <option value="language">Lenguaje</option>
                                    <option value="science">Ciencias</option>
                                    <option value="social">Ciencias Sociales</option>
                                    <option value="religious">Educación Religiosa</option>
                                    <option value="physical">Educación Física</option>
                                    <option value="arts">Artes</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="gradeLevelFilter" class="form-label">Nivel</label>
                                <select class="form-select select2" id="gradeLevelFilter" data-enhance="select">
                                    <option value="">Todos los niveles</option>
                                    <option value="1">1° Grado</option>
                                    <option value="2">2° Grado</option>
                                    <option value="3">3° Grado</option>
                                    <option value="4">4° Grado</option>
                                    <option value="5">5° Grado</option>
                                    <option value="6">6° Grado</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-outline-primary" id="applyAdvancedFilters">
                                    <i class="bi bi-check-lg me-1"></i> Aplicar Filtros
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="clearAdvancedFilters">
                                    <i class="bi bi-x-lg me-1"></i> Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vista de materias -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i> Lista de Materias
                        <span class="badge bg-secondary ms-2" id="totalSubjects"><?= count($subjects ?? []) ?></span>
                    </h5>
                    <div class="d-flex align-items-center">
                        <span class="me-2">Vista:</span>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="viewMode" id="tableView" value="table" checked>
                            <label class="btn btn-outline-primary btn-sm" for="tableView">
                                <i class="bi bi-table me-1"></i> Tabla
                            </label>
                            <input type="radio" class="btn-check" name="viewMode" id="cardView" value="card">
                            <label class="btn btn-outline-primary btn-sm" for="cardView">
                                <i class="bi bi-grid-3x3-gap me-1"></i> Tarjetas
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Vista de tabla -->
                    <div class="table-responsive" id="tableViewContainer">
                        <table class="table table-striped table-hover" id="subjectsTable">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllSubjects"></th>
                                    <th>Código</th>
                                    <th>Nombre de la Materia</th>
                                    <th>Módulo</th>
                                    <th>Descripción</th>
                                    <th>Cursos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($subjects)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No hay materias registradas.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td><input type="checkbox" class="subject-checkbox" value="<?= (int) $subject['id'] ?>"></td>
                                            <td><span class="badge bg-primary"><?= htmlspecialchars($subject['code'] ?? 'N/A') ?></span></td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($subject['name'] ?? '') ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($subject['module_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($subject['description'] ?? 'Sin descripción') ?></td>
                                            <td><?= (int) ($subject['course_count'] ?? 0) ?> cursos</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-info"
                                                        data-subject-edit
                                                        data-id="<?= (int) $subject['id'] ?>"
                                                        data-name="<?= htmlspecialchars($subject['name'] ?? '', ENT_QUOTES) ?>"
                                                        data-code="<?= htmlspecialchars($subject['code'] ?? '', ENT_QUOTES) ?>"
                                                        data-module-id="<?= (int) ($subject['module_id'] ?? 0) ?>"
                                                        data-description="<?= htmlspecialchars($subject['description'] ?? '', ENT_QUOTES) ?>"
                                                    >
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        data-subject-delete
                                                        data-id="<?= (int) $subject['id'] ?>"
                                                        data-name="<?= htmlspecialchars($subject['name'] ?? '', ENT_QUOTES) ?>"
                                                    >
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Vista de tarjetas -->
                    <div class="row" id="cardViewContainer" style="display: none;">
                        <?php if (empty($subjects)): ?>
                            <div class="col-12 text-center text-muted">No hay materias registradas.</div>
                        <?php else: ?>
                            <?php foreach ($subjects as $subject): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 subject-card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($subject['code'] ?? 'N/A') ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($subject['module_name'] ?? 'Sin módulo') ?></small>
                                            </div>
                                            <input type="checkbox" class="subject-checkbox" value="<?= (int) $subject['id'] ?>">
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($subject['name'] ?? '') ?></h5>
                                            <p class="card-text"><?= htmlspecialchars($subject['description'] ?? 'Sin descripción.') ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-info"><?= htmlspecialchars($subject['module_name'] ?? 'N/A') ?></span>
                                                <span class="badge bg-secondary"><?= (int) ($subject['course_count'] ?? 0) ?> cursos</span>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="btn-group w-100" role="group">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-info"
                                                    data-subject-edit
                                                    data-id="<?= (int) $subject['id'] ?>"
                                                    data-name="<?= htmlspecialchars($subject['name'] ?? '', ENT_QUOTES) ?>"
                                                    data-code="<?= htmlspecialchars($subject['code'] ?? '', ENT_QUOTES) ?>"
                                                    data-module-id="<?= (int) ($subject['module_id'] ?? 0) ?>"
                                                    data-description="<?= htmlspecialchars($subject['description'] ?? '', ENT_QUOTES) ?>"
                                                >
                                                    <i class="bi bi-pencil me-1"></i> Editar
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-subject-delete
                                                    data-id="<?= (int) $subject['id'] ?>"
                                                    data-name="<?= htmlspecialchars($subject['name'] ?? '', ENT_QUOTES) ?>"
                                                >
                                                    <i class="bi bi-trash me-1"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Paginación -->
                    <nav aria-label="Paginación de materias">
                        <ul class="pagination justify-content-center" id="subjectsPagination">
                            <!-- La paginación se generará dinámicamente -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear materia -->
<div class="modal fade" id="createSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i> Crear Nueva Materia
                    </h5>
                    <p class="text-muted mb-0 small">Registra una materia con su código y módulo.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createSubjectForm" method="POST" action="<?= htmlspecialchars($basePath . '/subjects') ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_subject">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Información Básica</h6>
                            <div class="mb-3">
                                <label for="subjectName" class="form-label">Nombre de la Materia *</label>
                                <input type="text" class="form-control" id="subjectName" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="subjectCode" class="form-label">Código de la Materia *</label>
                                <input type="text" class="form-control" id="subjectCode" name="code" required>
                                <div class="form-text">Código único de identificación (ej: MAT-001)</div>
                            </div>
                            <div class="mb-3">
                                <label for="subjectModule" class="form-label">Módulo</label>
                                <select class="form-select select2" id="subjectModule" name="module_id" data-enhance="select">
                                    <option value="">Seleccionar módulo</option>
                                    <?php foreach ($modules as $module): ?>
                                        <option value="<?= (int) $module['id'] ?>"><?= htmlspecialchars($module['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="subjectDescription" class="form-label">Descripción</label>
                                <textarea class="form-control" id="subjectDescription" name="description" rows="3" placeholder="Descripción detallada de la materia..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Configuración Académica</h6>
                            <p class="text-muted mb-0">Completa los datos básicos para crear la materia.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Crear Materia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar materia -->
<div class="modal fade" id="editSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2"></i> Editar Materia
                    </h5>
                    <p class="text-muted mb-0 small">Modifica la información académica de la materia.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSubjectForm" method="POST" action="<?= htmlspecialchars($basePath . '/subjects') ?>">
                <div class="modal-body">
                    <input type="hidden" id="editSubjectId" name="id">
                    <input type="hidden" name="action" value="update_subject">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Información Básica</h6>
                            <div class="mb-3">
                                <label for="editSubjectName" class="form-label">Nombre de la Materia *</label>
                                <input type="text" class="form-control" id="editSubjectName" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="editSubjectCode" class="form-label">Código de la Materia *</label>
                                <input type="text" class="form-control" id="editSubjectCode" name="code" required readonly>
                            </div>
                            <div class="mb-3">
                                <label for="editSubjectModule" class="form-label">Módulo</label>
                                <select class="form-select select2" id="editSubjectModule" name="module_id" data-enhance="select">
                                    <option value="">Seleccionar módulo</option>
                                    <?php foreach ($modules as $module): ?>
                                        <option value="<?= (int) $module['id'] ?>"><?= htmlspecialchars($module['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editSubjectDescription" class="form-label">Descripción</label>
                                <textarea class="form-control" id="editSubjectDescription" name="description" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Configuración Académica</h6>
                            <p class="text-muted mb-0">Actualiza el módulo o descripción según sea necesario.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Actualizar Materia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver detalles de materia -->
<div class="modal fade" id="viewSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">
                        <i class="bi bi-eye me-2"></i> Detalles de la Materia
                    </h5>
                    <p class="text-muted mb-0 small">Consulta la información completa de la materia.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="subjectDetails">
                    <!-- Los detalles se cargarán dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="editSubjectFromView">
                    <i class="bi bi-pencil me-1"></i> Editar
                </button>
                <button type="button" class="btn btn-success" id="printSubject">
                    <i class="bi bi-printer me-1"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="deleteSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i> Confirmar Eliminación
                    </h5>
                    <p class="text-muted mb-0 small">Esta acción puede afectar cursos vinculados.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar esta materia?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Advertencia:</strong> Esta acción no se puede deshacer y puede afectar los cursos que utilizan esta materia.
                </div>
                <p><strong>Materia:</strong> <span id="deleteSubjectName"></span></p>
            </div>
            <form method="POST" action="<?= htmlspecialchars($basePath . '/subjects') ?>">
                <div class="modal-footer">
                    <input type="hidden" name="action" value="delete_subject">
                    <input type="hidden" id="deleteSubjectId" name="id">
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
