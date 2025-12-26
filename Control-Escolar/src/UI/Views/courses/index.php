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
                        <div class="col-md-3">
                            <label for="gradeLevelFilter" class="form-label">Nivel</label>
                            <select class="form-select" id="gradeLevelFilter" name="grade_level">
                                <option value="">Todos los niveles</option>
                                <option value="1" <?= ($filters['grade_level'] ?? '') === '1' ? 'selected' : '' ?>>1° Grado</option>
                                <option value="2" <?= ($filters['grade_level'] ?? '') === '2' ? 'selected' : '' ?>>2° Grado</option>
                                <option value="3" <?= ($filters['grade_level'] ?? '') === '3' ? 'selected' : '' ?>>3° Grado</option>
                                <option value="4" <?= ($filters['grade_level'] ?? '') === '4' ? 'selected' : '' ?>>4° Grado</option>
                                <option value="5" <?= ($filters['grade_level'] ?? '') === '5' ? 'selected' : '' ?>>5° Grado</option>
                                <option value="6" <?= ($filters['grade_level'] ?? '') === '6' ? 'selected' : '' ?>>6° Grado</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="searchFilter" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="searchFilter" name="search" placeholder="Buscar por nombre o código..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
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
                                    <th>Código</th>
                                    <th>Nombre del Curso</th>
                                    <th>Nivel</th>
                                    <th>Período</th>
                                    <th>Estado</th>
                                    <th>Inscripciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($courses)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No hay cursos registrados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($courses as $course): ?>
                                        <?php
                                        $schedule = json_decode($course['schedule'] ?? '[]', true) ?? [];
                                        $dayOfWeek = $schedule['day_of_week'] ?? '';
                                        $startTime = $schedule['start_time'] ?? '';
                                        $endTime = $schedule['end_time'] ?? '';
                                        $maxStudents = $course['max_students'] ?? null;
                                        $enrollmentCount = (int) ($course['enrollment_count'] ?? 0);
                                        $courseTeacherIds = $courseTeachers[$course['id']] ?? [];
                                        ?>
                                        <tr>
                                            <td><input type="checkbox" class="course-checkbox" value="<?= (int) $course['id'] ?>"></td>
                                            <td><span class="badge bg-primary"><?= htmlspecialchars($course['code'] ?? 'N/A') ?></span></td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($course['name'] ?? '') ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($course['subject_name'] ?? '') ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($course['subject_grade_level'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($course['period_name'] ?? 'Sin periodo') ?></td>
                                            <td><span class="badge bg-<?= in_array(($course['status'] ?? ''), ['active', 'published'], true) ? 'success' : (($course['status'] ?? '') === 'draft' ? 'warning' : 'secondary') ?>">
                                                <?= htmlspecialchars($course['status'] ?? 'N/A') ?>
                                            </span></td>
                                            <td><?= $maxStudents ? sprintf('%d / %d', $enrollmentCount, $maxStudents) : $enrollmentCount ?></td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-course-edit
                                                    data-id="<?= (int) $course['id'] ?>"
                                                    data-name="<?= htmlspecialchars($course['name'] ?? '', ENT_QUOTES) ?>"
                                                    data-code="<?= htmlspecialchars($course['code'] ?? '', ENT_QUOTES) ?>"
                                                    data-subject-id="<?= (int) ($course['subject_id'] ?? 0) ?>"
                                                    data-period-id="<?= (int) ($course['academic_period_id'] ?? 0) ?>"
                                                    data-status="<?= htmlspecialchars($course['status'] ?? '', ENT_QUOTES) ?>"
                                                    data-description="<?= htmlspecialchars($course['description'] ?? '', ENT_QUOTES) ?>"
                                                    data-max-students="<?= (int) ($course['max_students'] ?? 0) ?>"
                                                    data-start-date="<?= htmlspecialchars($course['start_date'] ?? '', ENT_QUOTES) ?>"
                                                    data-end-date="<?= htmlspecialchars($course['end_date'] ?? '', ENT_QUOTES) ?>"
                                                    data-day-of-week="<?= htmlspecialchars($dayOfWeek, ENT_QUOTES) ?>"
                                                    data-start-time="<?= htmlspecialchars($startTime, ENT_QUOTES) ?>"
                                                    data-end-time="<?= htmlspecialchars($endTime, ENT_QUOTES) ?>"
                                                    data-teacher-ids="<?= htmlspecialchars(implode(',', $courseTeacherIds), ENT_QUOTES) ?>"
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
                                <label for="courseName" class="form-label">Nombre del Curso *</label>
                                <input type="text" class="form-control" id="courseName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="courseCode" class="form-label">Código del Curso *</label>
                                <input type="text" class="form-control" id="courseCode" name="code" required>
                                <div class="form-text">Código único de identificación</div>
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
                                <label for="coursePeriod" class="form-label">Período Académico *</label>
                                <select class="form-select" id="coursePeriod" name="academic_period_id" required>
                                    <option value="">Seleccionar período</option>
                                    <?php foreach ($periods as $period): ?>
                                        <option value="<?= (int) $period['id'] ?>"><?= htmlspecialchars($period['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="courseMaxStudents" class="form-label">Cupo máximo</label>
                                <input type="number" class="form-control" id="courseMaxStudents" name="max_students" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="courseDay" class="form-label">Día de clase</label>
                                <input type="text" class="form-control" id="courseDay" name="day_of_week" placeholder="Ej: Sábado">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="courseStartTime" class="form-label">Hora inicio</label>
                                <input type="time" class="form-control" id="courseStartTime" name="start_time">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="courseEndTime" class="form-label">Hora fin</label>
                                <input type="time" class="form-control" id="courseEndTime" name="end_time">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="courseStartDate" class="form-label">Fecha de inicio</label>
                                <input type="date" class="form-control" id="courseStartDate" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="courseEndDate" class="form-label">Fecha de fin</label>
                                <input type="date" class="form-control" id="courseEndDate" name="end_date">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="courseDescription" class="form-label">Descripción</label>
                        <textarea class="form-control" id="courseDescription" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="courseTeachers" class="form-label">Profesores asignados</label>
                        <select class="form-select" id="courseTeachers" name="teacher_ids[]" multiple>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= (int) $teacher['id'] ?>"><?= htmlspecialchars($teacher['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Selecciona uno o varios profesores para este curso.</div>
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
                                <label for="editCourseName" class="form-label">Nombre del Curso *</label>
                                <input type="text" class="form-control" id="editCourseName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCourseCode" class="form-label">Código del Curso *</label>
                                <input type="text" class="form-control" id="editCourseCode" name="code" required readonly>
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
                                <label for="editCoursePeriod" class="form-label">Período Académico *</label>
                                <select class="form-select" id="editCoursePeriod" name="academic_period_id" required>
                                    <option value="">Seleccionar período</option>
                                    <?php foreach ($periods as $period): ?>
                                        <option value="<?= (int) $period['id'] ?>"><?= htmlspecialchars($period['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCourseMaxStudents" class="form-label">Cupo máximo</label>
                                <input type="number" class="form-control" id="editCourseMaxStudents" name="max_students" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="editCourseDay" class="form-label">Día de clase</label>
                                <input type="text" class="form-control" id="editCourseDay" name="day_of_week">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="editCourseStartTime" class="form-label">Hora inicio</label>
                                <input type="time" class="form-control" id="editCourseStartTime" name="start_time">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="editCourseEndTime" class="form-label">Hora fin</label>
                                <input type="time" class="form-control" id="editCourseEndTime" name="end_time">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCourseStartDate" class="form-label">Fecha de inicio</label>
                                <input type="date" class="form-control" id="editCourseStartDate" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCourseEndDate" class="form-label">Fecha de fin</label>
                                <input type="date" class="form-control" id="editCourseEndDate" name="end_date">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editCourseDescription" class="form-label">Descripción</label>
                        <textarea class="form-control" id="editCourseDescription" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="editCourseTeachers" class="form-label">Profesores asignados</label>
                        <select class="form-select" id="editCourseTeachers" name="teacher_ids[]" multiple>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= (int) $teacher['id'] ?>"><?= htmlspecialchars($teacher['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Selecciona uno o varios profesores para este curso.</div>
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
            document.getElementById('editCourseName').value = dataset.name || '';
            document.getElementById('editCourseCode').value = dataset.code || '';
            document.getElementById('editCourseSubject').value = dataset.subjectId || '';
            document.getElementById('editCoursePeriod').value = dataset.periodId || '';
            document.getElementById('editCourseStatus').value = dataset.status || 'draft';
            document.getElementById('editCourseDescription').value = dataset.description || '';
            document.getElementById('editCourseMaxStudents').value = dataset.maxStudents || '';
            document.getElementById('editCourseStartDate').value = dataset.startDate || '';
            document.getElementById('editCourseEndDate').value = dataset.endDate || '';
            document.getElementById('editCourseDay').value = dataset.dayOfWeek || '';
            document.getElementById('editCourseStartTime').value = dataset.startTime || '';
            document.getElementById('editCourseEndTime').value = dataset.endTime || '';

            const selectedTeachers = (dataset.teacherIds || '').split(',').filter(Boolean);
            const teacherSelect = document.getElementById('editCourseTeachers');
            Array.from(teacherSelect.options).forEach((option) => {
                option.selected = selectedTeachers.includes(option.value);
            });

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
