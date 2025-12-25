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
                <p class="lead">Crea cursos por cuatrimestre, asigna materias y profesores.</p>
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
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Nuevo curso</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= htmlspecialchars($basePath . '/courses') ?>">
                        <div class="mb-3">
                            <label class="form-label">Cuatrimestre</label>
                            <select name="academic_period_id" class="form-select" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($periods as $period): ?>
                                    <option value="<?= (int) $period['id'] ?>"><?= htmlspecialchars($period['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Materia</label>
                            <select name="subject_id" class="form-select" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= (int) $subject['id'] ?>">
                                        <?= htmlspecialchars($subject['module_name'] ?? '') ?> - <?= htmlspecialchars($subject['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Horario</label>
                            <div class="row g-2">
                                <div class="col-5">
                                    <select name="day_of_week" class="form-select">
                                        <option value="">Día</option>
                                        <option value="monday">Lunes</option>
                                        <option value="tuesday">Martes</option>
                                        <option value="wednesday">Miércoles</option>
                                        <option value="thursday">Jueves</option>
                                        <option value="friday">Viernes</option>
                                        <option value="saturday">Sábado</option>
                                        <option value="sunday">Domingo</option>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <input type="time" name="start_time" class="form-control">
                                </div>
                                <div class="col-3">
                                    <input type="time" name="end_time" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ubicación</label>
                            <input type="text" name="location" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cupo máximo</label>
                            <input type="number" name="max_students" class="form-control" min="1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Profesores</label>
                            <select name="teacher_ids[]" class="form-select" multiple>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= (int) $teacher['id'] ?>"><?= htmlspecialchars($teacher['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">El primer profesor será principal.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select name="status" class="form-select">
                                <option value="draft">Borrador</option>
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Crear curso
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Cursos registrados</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($courses)): ?>
                        <p class="text-muted">No hay cursos registrados.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Cuatrimestre</th>
                                        <th>Materia</th>
                                        <th>Horario</th>
                                        <th>Profesores</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($course['period_name']) ?></td>
                                            <td><?= htmlspecialchars($course['subject_name']) ?></td>
                                            <td>
                                                <?= htmlspecialchars($course['day_of_week'] ?? 'N/D') ?>
                                                <?= htmlspecialchars($course['start_time'] ?? '') ?>
                                                <?= htmlspecialchars($course['end_time'] ? ' - ' . $course['end_time'] : '') ?>
                                            </td>
                                            <td><?= htmlspecialchars($course['teacher_names'] ?? 'Por asignar') ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($course['status']) ?></span></td>
                                            <td class="text-end">
                                                <form method="POST" action="<?= htmlspecialchars($basePath . '/courses') ?>" class="d-inline">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar curso?')">
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
