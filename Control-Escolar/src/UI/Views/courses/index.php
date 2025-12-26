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
                <h1><i class="fas fa-book"></i> Gestión de Cursos</h1>
                <p class="lead">Administra los cursos del sistema educativo cristiano</p>
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

    <!-- Barra de acciones -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal">
                        <i class="fas fa-plus"></i> Nuevo Curso
                    </button>
                    <button class="btn btn-success" id="exportCourses">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form class="row g-3" id="courseFilters" method="GET" action="<?= htmlspecialchars($basePath . '/courses') ?>">
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">Estado</label>
                            <select class="form-select" id="statusFilter" name="status">
                                <option value="">Todos los estados</option>
                                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Activo</option>
                                <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publicado</option>
                                <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                                <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Borrador</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="searchFilter" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="searchFilter" name="search" placeholder="Buscar por grupo o materia..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de cursos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Lista de Cursos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="coursesTable">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>Grupo</th>
                                    <th>Materia</th>
                                    <th>Periodo</th>
                                    <th>Horario</th>
                                    <th>Modalidad</th>
                                    <th>Estado</th>
                                    <th>Cupo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($courses)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No hay cursos registrados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($courses as $course): ?>
                                        <?php
                                        $capacity = $course['capacity'] ?? null;
                                        $enrollmentCount = (int) ($course['enrollment_count'] ?? 0);
                                        ?>
                                        <tr>
                                            <td><input type="checkbox" class="course-checkbox" value="<?= (int) $course['id'] ?>"></td>
                                            <td><span class="badge bg-primary"><?= htmlspecialchars($course['group_name'] ?? 'N/A') ?></span></td>
                                            <td><?= htmlspecialchars($course['subject_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($course['term_name'] ?? 'Sin periodo') ?></td>
                                            <td><?= htmlspecialchars($course['schedule_label'] ?? 'Por definir') ?></td>
                                            <td><?= htmlspecialchars($course['modality'] ?? 'N/A') ?></td>
                                            <td><span class="badge bg-<?= in_array(($course['status'] ?? ''), ['active', 'published'], true) ? 'success' : (($course['status'] ?? '') === 'draft' ? 'warning' : 'secondary') ?>">
                                                <?= htmlspecialchars($course['status'] ?? 'N/A') ?>
                                            </span></td>
                                            <td><?= $capacity ? sprintf('%d / %d', $enrollmentCount, $capacity) : $enrollmentCount ?></td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-course-edit
                                                    data-id="<?= (int) $course['id'] ?>"
                                                    data-group-name="<?= htmlspecialchars($course['group_name'] ?? '', ENT_QUOTES) ?>"
                                                    data-subject-id="<?= (int) ($course['subject_id'] ?? 0) ?>"
                                                    data-term-id="<?= (int) ($course['term_id'] ?? 0) ?>"
                                                    data-status="<?= htmlspecialchars($course['status'] ?? '', ENT_QUOTES) ?>"
                                                    data-schedule-label="<?= htmlspecialchars($course['schedule_label'] ?? '', ENT_QUOTES) ?>"
                                                    data-modality="<?= htmlspecialchars($course['modality'] ?? '', ENT_QUOTES) ?>"
                                                    data-zoom-url="<?= htmlspecialchars($course['zoom_url'] ?? '', ENT_QUOTES) ?>"
                                                    data-pdf-path="<?= htmlspecialchars($course['pdf_path'] ?? '', ENT_QUOTES) ?>"
                                                    data-capacity="<?= (int) ($course['capacity'] ?? 0) ?>"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-course-delete
                                                    data-id="<?= (int) $course['id'] ?>"
                                                    data-name="<?= htmlspecialchars($course['name'] ?? '', ENT_QUOTES) ?>"
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
                    
                    <!-- Paginación -->
                    <nav aria-label="Paginación de cursos">
                        <ul class="pagination justify-content-center" id="coursesPagination">
                            <!-- La paginación se generará dinámicamente -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear curso -->
<div class="modal fade" id="createCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Crear Nuevo Curso
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createCourseForm" method="POST" action="<?= htmlspecialchars($basePath . '/courses') ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_course">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="courseGroupName" class="form-label">Nombre del Grupo *</label>
                                <input type="text" class="form-control" id="courseGroupName" name="group_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="courseStatus" class="form-label">Estado *</label>
                                <select class="form-select" id="courseStatus" name="status" required>
                                    <option value="draft">Borrador</option>
                                    <option value="active">Activo</option>
                                    <option value="published">Publicado</option>
                                    <option value="inactive">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="courseSubject" class="form-label">Materia *</label>
                                <select class="form-select" id="courseSubject" name="subject_id" required>
                                    <option value="">Seleccionar materia</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?= (int) $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="courseTerm" class="form-label">Periodo Académico *</label>
                                <select class="form-select" id="courseTerm" name="term_id" required>
                                    <option value="">Seleccionar periodo</option>
                                    <?php foreach ($terms as $term): ?>
                                        <option value="<?= (int) $term['id'] ?>"><?= htmlspecialchars($term['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="courseSchedule" class="form-label">Horario</label>
                                <input type="text" class="form-control" id="courseSchedule" name="schedule_label" placeholder="Ej: Sábados 9:00-11:00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="courseCapacity" class="form-label">Cupo máximo</label>
                                <input type="number" class="form-control" id="courseCapacity" name="capacity" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="courseModality" class="form-label">Modalidad</label>
                                <input type="text" class="form-control" id="courseModality" name="modality" placeholder="Ej: Presencial">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="courseZoomUrl" class="form-label">Zoom URL</label>
                                <input type="url" class="form-control" id="courseZoomUrl" name="zoom_url" placeholder="https://">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="coursePdfPath" class="form-label">Ruta PDF</label>
                                <input type="text" class="form-control" id="coursePdfPath" name="pdf_path" placeholder="/archivos/curso.pdf">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Curso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar curso -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Curso
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCourseForm" method="POST" action="<?= htmlspecialchars($basePath . '/courses') ?>">
                <div class="modal-body">
                    <input type="hidden" id="editCourseId" name="id">
                    <input type="hidden" name="action" value="update_course">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCourseGroupName" class="form-label">Nombre del Grupo *</label>
                                <input type="text" class="form-control" id="editCourseGroupName" name="group_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCourseStatus" class="form-label">Estado *</label>
                                <select class="form-select" id="editCourseStatus" name="status" required>
                                    <option value="draft">Borrador</option>
                                    <option value="active">Activo</option>
                                    <option value="published">Publicado</option>
                                    <option value="inactive">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCourseSubject" class="form-label">Materia *</label>
                                <select class="form-select" id="editCourseSubject" name="subject_id" required>
                                    <option value="">Seleccionar materia</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?= (int) $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCourseTerm" class="form-label">Periodo Académico *</label>
                                <select class="form-select" id="editCourseTerm" name="term_id" required>
                                    <option value="">Seleccionar periodo</option>
                                    <?php foreach ($terms as $term): ?>
                                        <option value="<?= (int) $term['id'] ?>"><?= htmlspecialchars($term['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCourseSchedule" class="form-label">Horario</label>
                                <input type="text" class="form-control" id="editCourseSchedule" name="schedule_label">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCourseCapacity" class="form-label">Cupo máximo</label>
                                <input type="number" class="form-control" id="editCourseCapacity" name="capacity" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="editCourseModality" class="form-label">Modalidad</label>
                                <input type="text" class="form-control" id="editCourseModality" name="modality">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="editCourseZoomUrl" class="form-label">Zoom URL</label>
                                <input type="url" class="form-control" id="editCourseZoomUrl" name="zoom_url">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="editCoursePdfPath" class="form-label">Ruta PDF</label>
                                <input type="text" class="form-control" id="editCoursePdfPath" name="pdf_path">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Curso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="deleteCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar este curso?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-warning"></i>
                    <strong>Advertencia:</strong> Esta acción no se puede deshacer y eliminará todas las inscripciones asociadas.
                </div>
                <p><strong>Curso:</strong> <span id="deleteCourseName"></span></p>
            </div>
            <form method="POST" action="<?= htmlspecialchars($basePath . '/courses') ?>">
                <div class="modal-footer">
                    <input type="hidden" name="action" value="delete_course">
                    <input type="hidden" id="deleteCourseId" name="id">
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
    // Event listener para seleccionar todos
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.course-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    document.querySelectorAll('[data-course-edit]').forEach((button) => {
        button.addEventListener('click', () => {
            const dataset = button.dataset;
            document.getElementById('editCourseId').value = dataset.id || '';
            document.getElementById('editCourseGroupName').value = dataset.groupName || '';
            document.getElementById('editCourseSubject').value = dataset.subjectId || '';
            document.getElementById('editCourseTerm').value = dataset.termId || '';
            document.getElementById('editCourseStatus').value = dataset.status || 'draft';
            document.getElementById('editCourseSchedule').value = dataset.scheduleLabel || '';
            document.getElementById('editCourseCapacity').value = dataset.capacity || '';
            document.getElementById('editCourseModality').value = dataset.modality || '';
            document.getElementById('editCourseZoomUrl').value = dataset.zoomUrl || '';
            document.getElementById('editCoursePdfPath').value = dataset.pdfPath || '';

            const modal = new bootstrap.Modal(document.getElementById('editCourseModal'));
            modal.show();
        });
    });

    document.querySelectorAll('[data-course-delete]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('deleteCourseId').value = button.dataset.id || '';
            document.getElementById('deleteCourseName').textContent = button.dataset.name || '';

            const modal = new bootstrap.Modal(document.getElementById('deleteCourseModal'));
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

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.alert {
    border: none;
    border-radius: 0.375rem;
}

.spinner-border {
    width: 2rem;
    height: 2rem;
}

.pagination .page-link {
    color: #007bff;
    border-color: #dee2e6;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}
</style>
