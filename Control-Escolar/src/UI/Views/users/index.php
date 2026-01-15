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

<div class="container-xxl app-content admin-premium-page">
    <div class="page-header admin-premium-header">
        <div>
            <h1 class="page-title"><i class="bi bi-people me-2"></i> Usuarios</h1>
            <p class="page-subtitle">Consulta profesores y alumnos registrados en el sistema.</p>
        </div>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger admin-alert">
            <i class="bi bi-exclamation-circle me-1"></i>
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success admin-alert">
            <i class="bi bi-check-circle me-1"></i>
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <div class="row admin-section">
        <div class="col-lg-6 mb-4">
            <div class="card h-100 premium-card">
                <div class="card-header premium-card-header">
                    <h5 class="mb-0"><i class="bi bi-easel me-2"></i> Profesores</h5>
                </div>
                <div class="card-body premium-card-body">
                    <form method="GET" action="<?= htmlspecialchars($basePath . '/users') ?>" class="row g-2 align-items-center mb-3 admin-filter-form">
                        <div class="col-12 col-md-8">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="teacher_search" class="form-control" placeholder="Buscar profesor..." value="<?= htmlspecialchars($teacherSearch ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-4 d-flex gap-2">
                            <input type="hidden" name="student_search" value="<?= htmlspecialchars($studentSearch ?? '') ?>">
                            <input type="hidden" name="student_page" value="<?= (int) ($studentPage ?? 1) ?>">
                            <button type="submit" class="btn btn-primary w-100">Buscar</button>
                            <a href="<?= htmlspecialchars($basePath . '/users?student_search=' . urlencode($studentSearch ?? '') . '&student_page=' . (int) ($studentPage ?? 1)) ?>" class="btn btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>
                    <?php if (empty($teachers)): ?>
                        <p class="text-muted">No hay profesores registrados.</p>
                    <?php else: ?>
                        <div class="table-responsive premium-table-wrapper">
                            <table class="table table-striped align-middle premium-table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($teacher['name']) ?></td>
                                            <td><?= htmlspecialchars($teacher['email']) ?></td>
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
                        <?php if (($teacherTotalPages ?? 1) > 1): ?>
                            <nav aria-label="Paginación de profesores">
                                <ul class="pagination justify-content-end mb-0">
                                    <?php
                                    $teacherPageCurrent = (int) ($teacherPage ?? 1);
                                    $teacherQueryBase = [
                                        'teacher_search' => $teacherSearch ?? '',
                                        'student_search' => $studentSearch ?? '',
                                        'student_page' => (int) ($studentPage ?? 1)
                                    ];
                                    ?>
                                    <li class="page-item <?= $teacherPageCurrent <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= htmlspecialchars($basePath . '/users?' . http_build_query(array_merge($teacherQueryBase, ['teacher_page' => max(1, $teacherPageCurrent - 1)]))) ?>">Anterior</a>
                                    </li>
                                    <?php for ($page = 1; $page <= ($teacherTotalPages ?? 1); $page++): ?>
                                        <li class="page-item <?= $page === $teacherPageCurrent ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= htmlspecialchars($basePath . '/users?' . http_build_query(array_merge($teacherQueryBase, ['teacher_page' => $page]))) ?>"><?= $page ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $teacherPageCurrent >= ($teacherTotalPages ?? 1) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= htmlspecialchars($basePath . '/users?' . http_build_query(array_merge($teacherQueryBase, ['teacher_page' => min(($teacherTotalPages ?? 1), $teacherPageCurrent + 1)]))) ?>">Siguiente</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card h-100 premium-card">
                <div class="card-header premium-card-header">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i> Alumnos</h5>
                </div>
                <div class="card-body premium-card-body">
                    <form method="GET" action="<?= htmlspecialchars($basePath . '/users') ?>" class="row g-2 align-items-center mb-3 admin-filter-form">
                        <div class="col-12 col-md-8">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" name="student_search" class="form-control" placeholder="Buscar alumno..." value="<?= htmlspecialchars($studentSearch ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-4 d-flex gap-2">
                            <input type="hidden" name="teacher_search" value="<?= htmlspecialchars($teacherSearch ?? '') ?>">
                            <input type="hidden" name="teacher_page" value="<?= (int) ($teacherPage ?? 1) ?>">
                            <button type="submit" class="btn btn-primary w-100">Buscar</button>
                            <a href="<?= htmlspecialchars($basePath . '/users?teacher_search=' . urlencode($teacherSearch ?? '') . '&teacher_page=' . (int) ($teacherPage ?? 1)) ?>" class="btn btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>
                    <?php if (empty($students)): ?>
                        <p class="text-muted">No hay alumnos registrados.</p>
                    <?php else: ?>
                        <div class="table-responsive premium-table-wrapper">
                            <table class="table table-striped align-middle premium-table">
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
                                            <td class="text-end table-actions">
                                                <button type="button" class="btn btn-sm btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#studentModal<?= (int) $student['id'] ?>">
                                                    <i class="bi bi-eye me-1"></i> Ver
                                                </button>
                                                <form method="POST" action="<?= htmlspecialchars($basePath . '/users') ?>" class="d-inline">
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
                        <?php if (($studentTotalPages ?? 1) > 1): ?>
                            <nav aria-label="Paginación de alumnos">
                                <ul class="pagination justify-content-end mb-0">
                                    <?php
                                    $studentPageCurrent = (int) ($studentPage ?? 1);
                                    $studentQueryBase = [
                                        'student_search' => $studentSearch ?? '',
                                        'teacher_search' => $teacherSearch ?? '',
                                        'teacher_page' => (int) ($teacherPage ?? 1)
                                    ];
                                    ?>
                                    <li class="page-item <?= $studentPageCurrent <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= htmlspecialchars($basePath . '/users?' . http_build_query(array_merge($studentQueryBase, ['student_page' => max(1, $studentPageCurrent - 1)]))) ?>">Anterior</a>
                                    </li>
                                    <?php for ($page = 1; $page <= ($studentTotalPages ?? 1); $page++): ?>
                                        <li class="page-item <?= $page === $studentPageCurrent ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= htmlspecialchars($basePath . '/users?' . http_build_query(array_merge($studentQueryBase, ['student_page' => $page]))) ?>"><?= $page ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $studentPageCurrent >= ($studentTotalPages ?? 1) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= htmlspecialchars($basePath . '/users?' . http_build_query(array_merge($studentQueryBase, ['student_page' => min(($studentTotalPages ?? 1), $studentPageCurrent + 1)]))) ?>">Siguiente</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
