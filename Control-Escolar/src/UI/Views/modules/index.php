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
                <h1><i class="fas fa-layer-group"></i> Gestión de Módulos</h1>
                <p class="lead">Crea, edita y ordena los módulos académicos</p>
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
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Nuevo módulo</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= htmlspecialchars($basePath . '/modules') ?>">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Orden</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Activo</label>
                            <select name="is_active" class="form-select">
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Crear módulo
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Módulos registrados</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($modules)): ?>
                        <p class="text-muted">No hay módulos registrados.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Orden</th>
                                        <th>Activo</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modules as $module): ?>
                                        <tr>
                                            <td>
                                                <form method="POST" action="<?= htmlspecialchars($basePath . '/modules') ?>">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="module_id" value="<?= (int) $module['id'] ?>">
                                                    <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($module['name']) ?>" required>
                                            </td>
                                            <td>
                                                    <input type="number" name="sort_order" class="form-control form-control-sm" value="<?= (int) $module['sort_order'] ?>" min="0">
                                            </td>
                                            <td>
                                                    <select name="is_active" class="form-select form-select-sm">
                                                        <option value="1" <?= !empty($module['is_active']) ? 'selected' : '' ?>>Sí</option>
                                                        <option value="0" <?= empty($module['is_active']) ? 'selected' : '' ?>>No</option>
                                                    </select>
                                            </td>
                                            <td class="text-end">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="<?= htmlspecialchars($basePath . '/modules') ?>" class="d-inline">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="module_id" value="<?= (int) $module['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar módulo?')">
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
