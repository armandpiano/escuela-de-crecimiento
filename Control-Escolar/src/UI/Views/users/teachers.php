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
$displayName = $_SESSION['user_name'] ?? 'Usuario';
?>

<div class="container-premium app-content admin-premium-page admin-page page-shell page-shell-dashboard">
    <div class="dash-header-card dash-card">
        <div class="dash-header-main">
            <div>
                <h1 class="page-title"><i class="bi bi-easel me-2"></i> Profesores</h1>
                <p class="page-subtitle">Consulta profesores registrados en el sistema.</p>
            </div>
            <div class="dash-header-actions">
                <div class="dash-header-meta">
                    <span class="badge badge-premium"><?= htmlspecialchars(ucfirst($userRole)) ?></span>
                    <span class="dash-user-name"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($displayName) ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger admin-alert" data-toast-message="<?= htmlspecialchars($errorMessage) ?>" data-toast-type="error">
            <i class="bi bi-exclamation-circle me-1"></i>
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

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

    <div class="card dash-card dash-table-card table-card">
        <div class="card-body premium-card-body">
            <?php if (empty($teachers)): ?>
                <p class="text-muted">No hay profesores registrados.</p>
            <?php else: ?>
                <div class="table-responsive premium-table-wrapper datatable-premium">
                    <table class="table table-striped align-middle premium-table" data-datatable data-order-column="0" data-order-direction="asc">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Cursos que imparte</th>
                                <th>Estado</th>
                                <th class="text-end" data-orderable="false">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teachers as $teacher): ?>
                                <?php $coursesList = array_filter(explode('||', $teacher['course_names'] ?? '')); ?>
                                <tr>
                                    <td><?= htmlspecialchars($teacher['name']) ?></td>
                                    <td><?= htmlspecialchars($teacher['email']) ?></td>
                                    <td>
                                        <?php if (empty($coursesList)): ?>
                                            <span class="badge bg-light text-dark">Sin cursos asignados</span>
                                        <?php else: ?>
                                            <div class="table-badges">
                                                <?php foreach ($coursesList as $courseLabel): ?>
                                                    <span class="badge badge-soft-primary"><?= htmlspecialchars($courseLabel) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $teacher['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars($teacher['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end table-actions">
                                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#teacherModal<?= (int) $teacher['id'] ?>">
                                            <i class="bi bi-eye me-1"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php foreach ($teachers as $teacher): ?>
                    <div class="modal fade" id="teacherModal<?= (int) $teacher['id'] ?>" tabindex="-1" aria-labelledby="teacherModalLabel<?= (int) $teacher['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content premium-modal">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="teacherModalLabel<?= (int) $teacher['id'] ?>">Detalle del profesor</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <dl class="row mb-0">
                                        <dt class="col-4">Nombre</dt>
                                        <dd class="col-8"><?= htmlspecialchars($teacher['name']) ?></dd>
                                        <dt class="col-4">Correo</dt>
                                        <dd class="col-8"><?= htmlspecialchars($teacher['email']) ?></dd>
                                        <dt class="col-4">Estado</dt>
                                        <dd class="col-8"><?= htmlspecialchars($teacher['status']) ?></dd>
                                        <dt class="col-4">Registro</dt>
                                        <dd class="col-8"><?= htmlspecialchars($teacher['created_at'] ?? 'N/D') ?></dd>
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
