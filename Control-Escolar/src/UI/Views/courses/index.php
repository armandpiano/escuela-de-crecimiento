<?php
$basePath = rtrim($basePath ?? '', '/');
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/login');
    exit();
}
?>

<div class="container-xxl app-content admin-premium-page admin-page page-shell">
    <div class="page-header admin-premium-header">
        <div>
            <h1 class="page-title"><i class="bi bi-book me-2"></i> Gestión de Cursos</h1>
            <p class="page-subtitle">Administra los cursos del sistema educativo cristiano</p>
        </div>
        <div class="page-header-actions admin-premium-actions">
            <button class="btn btn-primary btn-premium" data-bs-toggle="modal" data-bs-target="#createCourseModal">
                <i class="bi bi-plus-circle me-1"></i> Nuevo Curso
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

    <!-- Filtros de búsqueda -->
    <div class="row mb-4 admin-section">
        <div class="col-12">
            <div class="card filter-card premium-card premium-filter-card page-card">
                <div class="card-body premium-card-body">
                    <form class="row g-3 admin-filter-form" id="courseFilters" method="GET" action="<?= htmlspecialchars($basePath . '/courses') ?>">
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">Estado</label>
                            <select class="form-select select2" id="statusFilter" name="status" data-enhance="select">
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
                                    <i class="bi bi-search me-1"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de cursos -->
    <div class="row admin-section">
        <div class="col-12">
            <div class="card premium-card page-card table-card">
                <div class="card-header premium-card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i> Lista de Cursos</h5>
                </div>
                <div class="card-body premium-card-body">
                    <div class="table-responsive premium-table-wrapper datatable-premium">
                        <table class="table table-striped table-hover premium-table" id="coursesTable" data-datatable data-order-column="1" data-order-direction="asc">
                            <thead>
                                <tr>
                                    <th data-orderable="false"><input type="checkbox" id="selectAll"></th>
                                    <th>Grupo</th>
                                    <th>Materia</th>
                                    <th>Periodo</th>
                                    <th>Horario</th>
                                    <th>Modalidad</th>
                                    <th>Estado</th>
                                    <th>Cupo</th>
                                    <th data-orderable="false">Acciones</th>
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
                                        $statusValue = $course['status'] ?? 'N/A';
                                        $statusKey = strtolower((string) $statusValue);
                                        $statusLabel = in_array($statusKey, ['open', 'abierto'], true)
                                            ? 'Abierto'
                                            : $statusValue;
                                        ?>
                                        <tr>
                                            <td><input type="checkbox" class="course-checkbox" value="<?= (int) $course['id'] ?>"></td>
                                            <td><span class="badge bg-primary"><?= htmlspecialchars($course['group_name'] ?? 'N/A') ?></span></td>
                                            <td><?= htmlspecialchars($course['subject_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($course['term_name'] ?? 'Sin periodo') ?></td>
                                            <td><?= htmlspecialchars($course['schedule_label'] ?? 'Por definir') ?></td>
                                            <td><?= htmlspecialchars($course['modality'] ?? 'N/A') ?></td>
                                            <td><span class="badge bg-<?= in_array($statusKey, ['active', 'published', 'open'], true) ? 'success' : ($statusKey === 'draft' ? 'warning' : 'secondary') ?>">
                                                <?= htmlspecialchars($statusLabel) ?>
                                            </span></td>
                                            <td><?= $capacity ? sprintf('%d / %d', $enrollmentCount, $capacity) : $enrollmentCount ?></td>
                                            <td class="table-actions">
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
                                                    data-capacity="<?= (int) ($course['capacity'] ?? 0) ?>"
                                                >
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-course-delete
                                                    data-id="<?= (int) $course['id'] ?>"
                                                    data-name="<?= htmlspecialchars($course['group_name'] ?? '', ENT_QUOTES) ?>"
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

<!-- Modal para crear curso -->
<div class="modal fade" id="createCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content premium-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i> Crear Nuevo Curso
                    </h5>
                    <p class="text-muted mb-0 small">Completa la información para abrir un nuevo grupo.</p>
                </div>
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
                                <select class="form-select select2" id="courseStatus" name="status" required data-enhance="select">
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
                                <select class="form-select select2" id="courseSubject" name="subject_id" required data-enhance="select">
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
                                <select class="form-select select2" id="courseTerm" name="term_id" required data-enhance="select">
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="courseModality" class="form-label">Modalidad</label>
                                <input type="text" class="form-control" id="courseModality" name="modality" placeholder="Ej: Presencial">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Crear Curso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar curso -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content premium-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2"></i> Editar Curso
                    </h5>
                    <p class="text-muted mb-0 small">Actualiza los detalles del curso seleccionado.</p>
                </div>
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
                                <select class="form-select select2" id="editCourseStatus" name="status" required data-enhance="select">
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
                                <select class="form-select select2" id="editCourseSubject" name="subject_id" required data-enhance="select">
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
                                <select class="form-select select2" id="editCourseTerm" name="term_id" required data-enhance="select">
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCourseModality" class="form-label">Modalidad</label>
                                <input type="text" class="form-control" id="editCourseModality" name="modality">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Actualizar Curso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="deleteCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content premium-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i> Confirmar Eliminación
                    </h5>
                    <p class="text-muted mb-0 small">Esta acción eliminará el curso y sus inscripciones.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar este curso?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Advertencia:</strong> Esta acción no se puede deshacer y eliminará todas las inscripciones asociadas.
                </div>
                <p><strong>Curso:</strong> <span id="deleteCourseName"></span></p>
            </div>
            <form method="POST" action="<?= htmlspecialchars($basePath . '/courses') ?>">
                <div class="modal-footer">
                    <input type="hidden" name="action" value="delete_course">
                    <input type="hidden" id="deleteCourseId" name="id">
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
