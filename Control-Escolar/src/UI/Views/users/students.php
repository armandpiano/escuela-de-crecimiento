<?php
$basePath = rtrim($basePath ?? '', '/');
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/login');
    exit();
}

$userRole = $_SESSION['user_role'] ?? '';
if ($userRole !== 'admin') {
    header('Location: ' . $basePath . '/dashboard');
    exit();
}
?>

<div class="container-premium app-content admin-premium-page admin-page page-shell page-shell-dashboard">
    <div class="dash-header-card dash-card">
        <div class="dash-header-main">
            <div>
                <h1 class="page-title"><i class="bi bi-person-badge me-2"></i> Alumnos</h1>
                <p class="page-subtitle">Consulta alumnos registrados en el sistema.</p>
            </div>
        </div>
    </div>

    <div class="dash-actions-grid">
        <a class="dash-action-card dash-card" href="<?= htmlspecialchars($basePath . '/courses') ?>">
            <span class="dash-action-icon"><i class="bi bi-book"></i></span>
            <div>
                <div class="dash-action-title">Cursos</div>
                <div class="dash-action-subtitle">Gestiona grupos</div>
            </div>
        </a>
        <a class="dash-action-card dash-card" href="<?= htmlspecialchars($basePath . '/enrollments') ?>">
            <span class="dash-action-icon"><i class="bi bi-person-plus"></i></span>
            <div>
                <div class="dash-action-title">Inscripciones</div>
                <div class="dash-action-subtitle">Altas y seguimientos</div>
            </div>
        </a>
        <a class="dash-action-card dash-card" href="<?= htmlspecialchars($basePath . '/subjects') ?>">
            <span class="dash-action-icon"><i class="bi bi-journal-bookmark"></i></span>
            <div>
                <div class="dash-action-title">Materias</div>
                <div class="dash-action-subtitle">Catálogo académico</div>
            </div>
        </a>
        <a class="dash-action-card dash-card" href="<?= htmlspecialchars($basePath . '/teachers') ?>">
            <span class="dash-action-icon"><i class="bi bi-easel"></i></span>
            <div>
                <div class="dash-action-title">Profesores</div>
                <div class="dash-action-subtitle">Plantilla docente</div>
            </div>
        </a>
        <a class="dash-action-card dash-card" href="<?= htmlspecialchars($basePath . '/students') ?>">
            <span class="dash-action-icon"><i class="bi bi-people"></i></span>
            <div>
                <div class="dash-action-title">Alumnos</div>
                <div class="dash-action-subtitle">Directorio</div>
            </div>
        </a>
        <a class="dash-action-card dash-card" href="<?= htmlspecialchars($basePath . '/periods') ?>">
            <span class="dash-action-icon"><i class="bi bi-calendar3"></i></span>
            <div>
                <div class="dash-action-title">Períodos</div>
                <div class="dash-action-subtitle">Calendario</div>
            </div>
        </a>
        <a class="dash-action-card dash-card" href="<?= htmlspecialchars($basePath . '/modules') ?>">
            <span class="dash-action-icon"><i class="bi bi-grid-1x2"></i></span>
            <div>
                <div class="dash-action-title">Módulos</div>
                <div class="dash-action-subtitle">Estructura</div>
            </div>
        </a>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger admin-alert" data-toast-message="<?= htmlspecialchars($errorMessage) ?>" data-toast-type="error">
            <i class="bi bi-exclamation-circle me-1"></i>
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success admin-alert" data-toast-message="<?= htmlspecialchars($successMessage) ?>" data-toast-type="success">
            <i class="bi bi-check-circle me-1"></i>
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <div class="card premium-card admin-section page-card table-card">
        <div class="card-body premium-card-body">
            <?php if (empty($students)): ?>
                <p class="text-muted">No hay alumnos registrados.</p>
            <?php else: ?>
                <div class="table-responsive premium-table-wrapper datatable-premium">
                    <table class="table table-striped align-middle premium-table" data-datatable data-order-column="0" data-order-direction="asc">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Materias inscritas</th>
                                <th>Estado</th>
                                <th class="text-end" data-orderable="false">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <?php $subjectsList = array_filter(explode('||', $student['subject_names'] ?? '')); ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['name']) ?></td>
                                    <td><?= htmlspecialchars($student['email']) ?></td>
                                    <td>
                                        <?php if (empty($subjectsList)): ?>
                                            <span class="badge bg-light text-dark">Sin inscripción</span>
                                        <?php else: ?>
                                            <div class="table-badges">
                                                <?php foreach ($subjectsList as $subjectLabel): ?>
                                                    <span class="badge badge-soft-info"><?= htmlspecialchars($subjectLabel) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $student['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars($student['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end table-actions">
                                        <button type="button" class="btn btn-sm btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#studentModal<?= (int) $student['id'] ?>">
                                            <i class="bi bi-eye me-1"></i> Ver
                                        </button>
                                        <form method="POST" action="<?= htmlspecialchars($basePath . '/students') ?>" class="d-inline">
                                            <input type="hidden" name="action" value="reset_student_password">
                                            <input type="hidden" name="student_id" value="<?= (int) $student['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-key me-1"></i> Generar contraseña
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php foreach ($students as $student): ?>
                    <div class="modal fade" id="studentModal<?= (int) $student['id'] ?>" tabindex="-1" aria-labelledby="studentModalLabel<?= (int) $student['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content premium-modal">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="studentModalLabel<?= (int) $student['id'] ?>">Detalle del alumno</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <dl class="row mb-0">
                                        <dt class="col-4">Nombre</dt>
                                        <dd class="col-8"><?= htmlspecialchars($student['name']) ?></dd>
                                        <dt class="col-4">Correo</dt>
                                        <dd class="col-8"><?= htmlspecialchars($student['email']) ?></dd>
                                        <dt class="col-4">Estado</dt>
                                        <dd class="col-8"><?= htmlspecialchars($student['status']) ?></dd>
                                        <dt class="col-4">Registro</dt>
                                        <dd class="col-8"><?= htmlspecialchars($student['created_at'] ?? 'N/D') ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
