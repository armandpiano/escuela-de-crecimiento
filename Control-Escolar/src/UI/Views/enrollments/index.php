<?php
$basePath = rtrim($basePath ?? '', '/');
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/login');
    exit();
}

$userRole = $_SESSION['user_role'] ?? '';
if ($userRole !== 'student') {
    $_SESSION['error'] = 'Esta sección es solo para estudiantes activos.';
    header('Location: ' . $basePath . '/dashboard');
    exit();
}

$studentName = $_SESSION['user_name'] ?? 'Estudiante';
$studentEmail = $_SESSION['user_email'] ?? 'correo@ejemplo.com';

$enrolledSubjects = [
    [
        'code' => 'MAT-101',
        'name' => 'Matemáticas Básicas',
        'status' => 'Inscrita',
        'period' => '2024-S1',
        'teacher' => 'Prof. Ana López'
    ],
    [
        'code' => 'BIB-201',
        'name' => 'Estudios Bíblicos',
        'status' => 'Inscrita',
        'period' => '2024-S1',
        'teacher' => 'Prof. Carlos Ruiz'
    ]
];

$historySubjects = [
    [
        'code' => 'ESP-101',
        'name' => 'Lengua Española',
        'status' => 'Aprobada',
        'period' => '2023-S2',
        'grade' => '9.1'
    ],
    [
        'code' => 'CIE-102',
        'name' => 'Ciencias Naturales',
        'status' => 'Aprobada',
        'period' => '2023-S2',
        'grade' => '8.7'
    ]
];
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

    <div class="row mb-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-id-badge"></i> Información del estudiante</h5>
                    <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($studentName) ?></p>
                    <p class="mb-1"><strong>Correo:</strong> <?= htmlspecialchars($studentEmail) ?></p>
                    <p class="mb-0"><strong>Rol:</strong> Estudiante</p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-book"></i> Materias inscritas</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Materia</th>
                                    <th>Periodo</th>
                                    <th>Docente</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrolledSubjects as $subject): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($subject['code']) ?></td>
                                        <td><?= htmlspecialchars($subject['name']) ?></td>
                                        <td><?= htmlspecialchars($subject['period']) ?></td>
                                        <td><?= htmlspecialchars($subject['teacher']) ?></td>
                                        <td><span class="badge bg-success"><?= htmlspecialchars($subject['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Historial de materias</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Materia</th>
                                    <th>Periodo</th>
                                    <th>Calificación</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historySubjects as $subject): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($subject['code']) ?></td>
                                        <td><?= htmlspecialchars($subject['name']) ?></td>
                                        <td><?= htmlspecialchars($subject['period']) ?></td>
                                        <td><?= htmlspecialchars($subject['grade']) ?></td>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($subject['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
