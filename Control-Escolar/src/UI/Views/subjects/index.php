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
                <h1><i class="fas fa-book-open"></i> Gestión de Materias</h1>
                <p class="lead">Administra las materias y asignaturas del sistema educativo</p>
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

    <!-- Barra de acciones principal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-2 mb-md-0">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSubjectModal">
                                <i class="fas fa-plus-circle"></i> Nueva Materia
                            </button>
                            <button class="btn btn-success" id="exportSubjects">
                                <i class="fas fa-download"></i> Exportar Lista
                            </button>
                            <button class="btn btn-info" id="bulkActions">
                                <i class="fas fa-tasks"></i> Acciones Masivas
                            </button>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="input-group" style="width: 300px;">
                                <input type="text" class="form-control" id="globalSearch" placeholder="Buscar materias...">
                                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y búsqueda avanzada -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <button class="btn btn-link p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                            <i class="fas fa-filter"></i> Filtros Avanzados
                            <i class="fas fa-chevron-down ms-1"></i>
                        </button>
                    </h6>
                </div>
                <div class="collapse" id="advancedFilters">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="statusFilter" class="form-label">Estado</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">Todos los estados</option>
                                    <option value="active">Activa</option>
                                    <option value="inactive">Inactiva</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="categoryFilter" class="form-label">Categoría</label>
                                <select class="form-select" id="categoryFilter">
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
                                <select class="form-select" id="gradeLevelFilter">
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
                                    <i class="fas fa-check"></i> Aplicar Filtros
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="clearAdvancedFilters">
                                    <i class="fas fa-times"></i> Limpiar Filtros
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
                        <i class="fas fa-list"></i> Lista de Materias
                        <span class="badge bg-secondary ms-2" id="totalSubjects"><?= count($subjects ?? []) ?></span>
                    </h5>
                    <div class="d-flex align-items-center">
                        <span class="me-2">Vista:</span>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="viewMode" id="tableView" value="table" checked>
                            <label class="btn btn-outline-primary btn-sm" for="tableView">
                                <i class="fas fa-table"></i> Tabla
                            </label>
                            <input type="radio" class="btn-check" name="viewMode" id="cardView" value="card">
                            <label class="btn btn-outline-primary btn-sm" for="cardView">
                                <i class="fas fa-th-large"></i> Tarjetas
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
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        data-subject-delete
                                                        data-id="<?= (int) $subject['id'] ?>"
                                                        data-name="<?= htmlspecialchars($subject['name'] ?? '', ENT_QUOTES) ?>"
                                                    >
                                                        <i class="fas fa-trash"></i>
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
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-subject-delete
                                                    data-id="<?= (int) $subject['id'] ?>"
                                                    data-name="<?= htmlspecialchars($subject['name'] ?? '', ENT_QUOTES) ?>"
                                                >
                                                    <i class="fas fa-trash"></i> Eliminar
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
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Crear Nueva Materia
                </h5>
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
                                <select class="form-select" id="subjectModule" name="module_id">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Materia
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
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Materia
                </h5>
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
                                <select class="form-select" id="editSubjectModule" name="module_id">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Materia
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
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Detalles de la Materia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="subjectDetails">
                    <!-- Los detalles se cargarán dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="editSubjectFromView">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button type="button" class="btn btn-success" id="printSubject">
                    <i class="fas fa-print"></i> Imprimir
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
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar esta materia?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-warning"></i>
                    <strong>Advertencia:</strong> Esta acción no se puede deshacer y puede afectar los cursos que utilizan esta materia.
                </div>
                <p><strong>Materia:</strong> <span id="deleteSubjectName"></span></p>
            </div>
            <form method="POST" action="<?= htmlspecialchars($basePath . '/subjects') ?>">
                <div class="modal-footer">
                    <input type="hidden" name="action" value="delete_subject">
                    <input type="hidden" id="deleteSubjectId" name="id">
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
    const tableView = document.getElementById('tableViewContainer');
    const cardView = document.getElementById('cardViewContainer');

    document.getElementById('tableView').addEventListener('change', function() {
        if (this.checked) {
            tableView.style.display = 'block';
            cardView.style.display = 'none';
        }
    });

    document.getElementById('cardView').addEventListener('change', function() {
        if (this.checked) {
            tableView.style.display = 'none';
            cardView.style.display = 'block';
        }
    });

    document.getElementById('selectAllSubjects').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.subject-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    document.querySelectorAll('[data-subject-edit]').forEach((button) => {
        button.addEventListener('click', () => {
            const dataset = button.dataset;
            document.getElementById('editSubjectId').value = dataset.id || '';
            document.getElementById('editSubjectName').value = dataset.name || '';
            document.getElementById('editSubjectCode').value = dataset.code || '';
            document.getElementById('editSubjectModule').value = dataset.moduleId || '';
            document.getElementById('editSubjectDescription').value = dataset.description || '';

            const modal = new bootstrap.Modal(document.getElementById('editSubjectModal'));
            modal.show();
        });
    });

    document.querySelectorAll('[data-subject-delete]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('deleteSubjectId').value = button.dataset.id || '';
            document.getElementById('deleteSubjectName').textContent = button.dataset.name || '';
            const modal = new bootstrap.Modal(document.getElementById('deleteSubjectModal'));
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

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
    margin-bottom: 1.5rem;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.75em;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.subject-icon {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
}

.subject-icon-lg {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    font-size: 1.25rem;
}

.subject-card {
    transition: transform 0.2s ease-in-out;
}

.subject-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.spinner-border {
    width: 2rem;
    height: 2rem;
}

.alert {
    border: none;
    border-radius: 0.375rem;
}

.table-sm td, .table-sm th {
    padding: 0.3rem;
}

.btn-check:checked + .btn {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.collapse {
    transition: all 0.3s ease;
}

.bg-primary {
    background-color: #0d6efd !important;
}

.bg-success {
    background-color: #198754 !important;
}

.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.bg-info {
    background-color: #0dcaf0 !important;
    color: #000 !important;
}

.bg-danger {
    background-color: #dc3545 !important;
}
</style>
