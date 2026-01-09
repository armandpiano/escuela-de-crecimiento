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

<div class="container-xxl app-content">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-person-badge me-2"></i> Mis Inscripciones</h1>
            <p class="page-subtitle">Consulta tus materias inscritas e historial académico</p>
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
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-person-vcard me-2"></i> Información del estudiante</h5>
                    <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($studentName) ?></p>
                    <p class="mb-1"><strong>Correo:</strong> <?= htmlspecialchars($studentEmail) ?></p>
                    <p class="mb-0"><strong>Periodo activo:</strong> <?= htmlspecialchars($activePeriodName) ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-book me-2"></i> Cursos disponibles</h5>
                    <?php if (!$activePeriod): ?>
                        <p class="text-muted">No hay un periodo académico activo.</p>
                    <?php elseif (!$enrollmentWindowOpen): ?>
                        <p class="text-warning">La ventana de inscripción está cerrada.</p>
                    <?php elseif (empty($availableCourses)): ?>
                        <p class="text-muted">No tienes cursos disponibles por seriación o visibilidad.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped" data-datatable data-order-column="1" data-order-direction="asc">
                                <thead>
                                    <tr>
                                        <th>Módulo</th>
                                        <th>Materia</th>
                                        <th>Grupo</th>
                                        <th>Horario</th>
                                        <th>Modalidad</th>
                                        <th data-orderable="false"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($availableCourses as $course): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($course['module_name'] ?? '') ?></td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($course['subject_name']) ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($course['group_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($course['schedule_label'] ?? 'Por definir') ?></td>
                                            <td><?= htmlspecialchars($course['modality'] ?? 'N/A') ?></td>
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
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Historial de inscripciones</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($studentEnrollments)): ?>
                        <p class="text-muted">Aún no tienes inscripciones registradas.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped" data-datatable data-order-column="0" data-order-direction="asc">
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
                                            </td>
                                            <td><?= htmlspecialchars($enrollment['term_name']) ?></td>
                                            <td><?= htmlspecialchars($enrollment['schedule_label'] ?? 'Por definir') ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($enrollment['status']) ?></span></td>
                                            <td><?= htmlspecialchars($enrollment['enrollment_at']) ?></td>
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
