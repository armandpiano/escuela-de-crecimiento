<?php
$basePath = rtrim($basePath ?? '', '/');
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/login');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-book-open"></i> Gestión de Materias</h1>
                <p class="lead">Asigna materias a módulos y define seriación.</p>
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

    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Nueva materia</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= htmlspecialchars($basePath . '/subjects') ?>">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Módulo</label>
                            <select name="module_id" class="form-select">
                                <option value="">Sin módulo</option>
                                <?php foreach ($modules as $module): ?>
                                    <option value="<?= (int) $module['id'] ?>"><?= htmlspecialchars($module['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Orden</label>
                            <input type="number" name="sort_order" class="form-control" min="0" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Seriación (prerrequisitos)</label>
                            <select name="prerequisites[]" class="form-select" multiple>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= (int) $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Activa</label>
                            <select name="is_active" class="form-select">
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Crear materia
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Materias registradas</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($subjects)): ?>
                        <p class="text-muted">No hay materias registradas.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Materia</th>
                                        <th>Módulo</th>
                                        <th>Orden</th>
                                        <th>Seriación</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td>
                                                <form method="POST" action="<?= htmlspecialchars($basePath . '/subjects') ?>">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="subject_id" value="<?= (int) $subject['id'] ?>">
                                                    <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($subject['name']) ?>" required>
                                            </td>
                                            <td>
                                                    <select name="module_id" class="form-select form-select-sm">
                                                        <option value="">Sin módulo</option>
                                                        <?php foreach ($modules as $module): ?>
                                                            <option value="<?= (int) $module['id'] ?>" <?= (int) $module['id'] === (int) $subject['module_id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($module['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                            </td>
                                            <td>
                                                    <input type="number" name="sort_order" class="form-control form-control-sm" value="<?= (int) $subject['sort_order'] ?>" min="0">
                                            </td>
                                            <td>
                                                    <select name="prerequisites[]" class="form-select form-select-sm" multiple>
                                                        <?php foreach ($subjects as $possible): ?>
                                                            <?php if ((int) $possible['id'] === (int) $subject['id']) { continue; } ?>
                                                            <?php $selected = in_array((int) $possible['id'], $prereqMap[$subject['id']] ?? [], true); ?>
                                                            <option value="<?= (int) $possible['id'] ?>" <?= $selected ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($possible['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                            </td>
                                            <td>
                                                    <select name="is_active" class="form-select form-select-sm">
                                                        <option value="1" <?= !empty($subject['is_active']) ? 'selected' : '' ?>>Activa</option>
                                                        <option value="0" <?= empty($subject['is_active']) ? 'selected' : '' ?>>Inactiva</option>
                                                    </select>
                                            </td>
                                            <td class="text-end">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="<?= htmlspecialchars($basePath . '/subjects') ?>" class="d-inline">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="subject_id" value="<?= (int) $subject['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar materia?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
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
</div>
