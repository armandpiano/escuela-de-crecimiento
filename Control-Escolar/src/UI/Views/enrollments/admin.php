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

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-user-plus"></i> Gestión de Inscripciones</h1>
                <p class="lead">Registra inscripciones manuales con validaciones y overrides.</p>
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
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-calendar"></i> Periodo activo: <?= htmlspecialchars($activePeriodName) ?></h5>
                    <?php if (!$activePeriod): ?>
                        <p class="text-muted">No hay un periodo académico activo.</p>
                    <?php elseif (empty($adminCourses)): ?>
                        <p class="text-muted">No hay cursos visibles disponibles.</p>
                    <?php else: ?>
                        <form method="POST" action="<?= htmlspecialchars($basePath . '/enrollments') ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="studentId" class="form-label">Estudiante activo</label>
                                    <select class="form-select" id="studentId" name="student_id" required>
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
                                    <select class="form-select" id="courseId" name="course_id" required>
                                        <option value="">Selecciona un curso</option>
                                        <?php foreach ($adminCourses as $course): ?>
                                            <option value="<?= (int) $course['id'] ?>">
                                                <?= htmlspecialchars($course['module_name'] . ' - ' . $course['subject_name'] . ' (' . $course['day_of_week'] . ' ' . $course['start_time'] . '-' . $course['end_time'] . ')') ?>
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
                                <i class="fas fa-save"></i> Registrar inscripción
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
