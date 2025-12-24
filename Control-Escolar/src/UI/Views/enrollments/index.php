<?php
$basePath = rtrim($basePath ?? '', '/');
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/login');
    exit();
}

$userRole = $_SESSION['user_role'] ?? '';
if ($userRole !== 'student') {
    header('Location: ' . $basePath . '/dashboard');
    exit();
}

$studentName = $_SESSION['user_name'] ?? 'Estudiante';
$studentEmail = $_SESSION['user_email'] ?? 'correo@ejemplo.com';
$activePeriodName = $activePeriod['name'] ?? 'Sin periodo activo';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-user-graduate"></i> Mis Inscripciones</h1>
                <p class="lead">Consulta tus materias inscritas e historial académico</p>
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
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-id-badge"></i> Información del estudiante</h5>
                    <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($studentName) ?></p>
                    <p class="mb-1"><strong>Correo:</strong> <?= htmlspecialchars($studentEmail) ?></p>
                    <p class="mb-0"><strong>Periodo activo:</strong> <?= htmlspecialchars($activePeriodName) ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-book"></i> Cursos disponibles</h5>
                    <?php if (!$activePeriod): ?>
                        <p class="text-muted">No hay un periodo académico activo.</p>
                    <?php elseif (!$enrollmentWindowOpen): ?>
                        <p class="text-warning">La ventana de inscripción está cerrada.</p>
                    <?php elseif (empty($availableCourses)): ?>
                        <p class="text-muted">No tienes cursos disponibles por seriación o visibilidad.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Módulo</th>
                                        <th>Materia</th>
                                        <th>Horario</th>
                                        <th>Profesores</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($availableCourses as $course): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($course['module_name'] ?? '') ?></td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($course['subject_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($course['subject_code']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($course['day_of_week'] . ' ' . $course['start_time'] . '-' . $course['end_time']) ?></td>
                                            <td><?= htmlspecialchars($course['teachers'] ?: 'Por asignar') ?></td>
                                            <td>
                                                <form method="POST" action="<?= htmlspecialchars($basePath . '/enrollments') ?>">
                                                    <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-primary">Inscribirme</button>
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Historial de inscripciones</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($studentEnrollments)): ?>
                        <p class="text-muted">Aún no tienes inscripciones registradas.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Materia</th>
                                        <th>Periodo</th>
                                        <th>Horario</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($studentEnrollments as $enrollment): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($enrollment['subject_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($enrollment['subject_code']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($enrollment['period_name']) ?></td>
                                            <td><?= htmlspecialchars($enrollment['day_of_week'] . ' ' . $enrollment['start_time'] . '-' . $enrollment['end_time']) ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($enrollment['status']) ?></span></td>
                                            <td><?= htmlspecialchars($enrollment['created_at']) ?></td>
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
