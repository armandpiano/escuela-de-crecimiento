<?php
$basePath = rtrim($basePath ?? '', '/');
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/login');
    exit();
}

$userRole = $_SESSION['user_role'] ?? '';
if ($userRole === 'student') {
    header('Location: ' . $basePath . '/enrollments');
    exit();
}

$activePeriodName = $activePeriod['name'] ?? 'Sin periodo activo';
?>

<div class="container-xxl app-content">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-person-plus me-2"></i> Gestión de Inscripciones</h1>
            <p class="page-subtitle">Registra inscripciones manuales con validaciones y overrides.</p>
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

    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-calendar-event me-2"></i> Periodo activo: <?= htmlspecialchars($activePeriodName) ?></h5>
                    <?php if (!$activePeriod): ?>
                        <p class="text-muted">No hay un periodo académico activo.</p>
                    <?php elseif (empty($adminCourses)): ?>
                        <p class="text-muted">No hay cursos visibles disponibles.</p>
                    <?php else: ?>
                        <form method="POST" action="<?= htmlspecialchars($basePath . '/enrollments') ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="studentId" class="form-label">Estudiante activo</label>
                                    <select class="form-select select2" id="studentId" name="student_id" required data-enhance="select">
                                        <option value="">Selecciona un estudiante</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?= (int) $student['id'] ?>">
                                                <?= htmlspecialchars($student['name'] . ' - ' . $student['email']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="courseId" class="form-label">Curso</label>
                                    <select class="form-select select2" id="courseId" name="course_id" required data-enhance="select">
                                        <option value="">Selecciona un curso</option>
                                        <?php foreach ($adminCourses as $course): ?>
                                            <option value="<?= (int) $course['id'] ?>">
                                                <?= htmlspecialchars($course['module_name'] . ' - ' . $course['subject_name'] . ' (' . ($course['group_name'] ?? 'Grupo') . ', ' . ($course['schedule_label'] ?? 'Horario') . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="overrideSeriation" name="override_seriation" value="1">
                                <label class="form-check-label" for="overrideSeriation">
                                    Ignorar seriación
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="overrideSchedule" name="override_schedule" value="1">
                                <label class="form-check-label" for="overrideSchedule">
                                    Ignorar choque de horario
                                </label>
                            </div>
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-save me-1"></i> Registrar inscripción
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted">Ventana de inscripción</h6>
                    <?php if (!$activePeriod): ?>
                        <p class="text-muted">Sin periodo activo.</p>
                    <?php elseif ($enrollmentWindowOpen): ?>
                        <span class="badge bg-success">Abierta</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Cerrada</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
