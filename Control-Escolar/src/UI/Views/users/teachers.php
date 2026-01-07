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

<div class="container-xxl app-content">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-easel me-2"></i> Profesores</h1>
            <p class="page-subtitle">Consulta profesores registrados en el sistema.</p>
        </div>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger" data-toast-message="<?= htmlspecialchars($errorMessage) ?>" data-toast-type="error">
            <i class="bi bi-exclamation-circle me-1"></i>
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="<?= htmlspecialchars($basePath . '/teachers') ?>" class="row g-2 align-items-center mb-3">
                <div class="col-12 col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="teacher_search" class="form-control" placeholder="Buscar profesor..." value="<?= htmlspecialchars($teacherSearch ?? '') ?>">
                    </div>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Buscar</button>
                    <a href="<?= htmlspecialchars($basePath . '/teachers') ?>" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>

            <?php if (empty($teachers)): ?>
                <p class="text-muted">No hay profesores registrados.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle sortable-table">
                        <thead>
                            <tr>
                                <th data-sortable="true">Nombre <span class="sort-indicator"></span></th>
                                <th data-sortable="true">Correo <span class="sort-indicator"></span></th>
                                <th data-sortable="false">Cursos que imparte</th>
                                <th data-sortable="true">Estado <span class="sort-indicator"></span></th>
                                <th class="text-end">Acciones</th>
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
                                    <td class="text-end">
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
                            <div class="modal-content">
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
                <?php if (($teacherTotalPages ?? 1) > 1): ?>
                    <nav aria-label="Paginación de profesores">
                        <ul class="pagination justify-content-end mb-0">
                            <?php $teacherPageCurrent = (int) ($teacherPage ?? 1); ?>
                            <li class="page-item <?= $teacherPageCurrent <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars($basePath . '/teachers?' . http_build_query(['teacher_search' => $teacherSearch ?? '', 'teacher_page' => max(1, $teacherPageCurrent - 1)])) ?>">Anterior</a>
                            </li>
                            <?php for ($page = 1; $page <= ($teacherTotalPages ?? 1); $page++): ?>
                                <li class="page-item <?= $page === $teacherPageCurrent ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= htmlspecialchars($basePath . '/teachers?' . http_build_query(['teacher_search' => $teacherSearch ?? '', 'teacher_page' => $page])) ?>"><?= $page ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $teacherPageCurrent >= ($teacherTotalPages ?? 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars($basePath . '/teachers?' . http_build_query(['teacher_search' => $teacherSearch ?? '', 'teacher_page' => min(($teacherTotalPages ?? 1), $teacherPageCurrent + 1)])) ?>">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
