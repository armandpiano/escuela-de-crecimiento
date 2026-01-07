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
            <h1 class="page-title"><i class="bi bi-person-badge me-2"></i> Alumnos</h1>
            <p class="page-subtitle">Consulta alumnos registrados en el sistema.</p>
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

    <div class="card">
        <div class="card-body">
            <form method="GET" action="<?= htmlspecialchars($basePath . '/students') ?>" class="row g-2 align-items-center mb-3">
                <div class="col-12 col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="student_search" class="form-control" placeholder="Buscar alumno..." value="<?= htmlspecialchars($studentSearch ?? '') ?>">
                    </div>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Buscar</button>
                    <a href="<?= htmlspecialchars($basePath . '/students') ?>" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>

            <?php if (empty($students)): ?>
                <p class="text-muted">No hay alumnos registrados.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['name']) ?></td>
                                    <td><?= htmlspecialchars($student['email']) ?></td>
                                    <td>
                                        <span class="badge <?= $student['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars($student['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
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
                            <div class="modal-content">
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
                <?php if (($studentTotalPages ?? 1) > 1): ?>
                    <nav aria-label="Paginación de alumnos">
                        <ul class="pagination justify-content-end mb-0">
                            <?php $studentPageCurrent = (int) ($studentPage ?? 1); ?>
                            <li class="page-item <?= $studentPageCurrent <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars($basePath . '/students?' . http_build_query(['student_search' => $studentSearch ?? '', 'student_page' => max(1, $studentPageCurrent - 1)])) ?>">Anterior</a>
                            </li>
                            <?php for ($page = 1; $page <= ($studentTotalPages ?? 1); $page++): ?>
                                <li class="page-item <?= $page === $studentPageCurrent ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= htmlspecialchars($basePath . '/students?' . http_build_query(['student_search' => $studentSearch ?? '', 'student_page' => $page])) ?>"><?= $page ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $studentPageCurrent >= ($studentTotalPages ?? 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars($basePath . '/students?' . http_build_query(['student_search' => $studentSearch ?? '', 'student_page' => min(($studentTotalPages ?? 1), $studentPageCurrent + 1)])) ?>">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
