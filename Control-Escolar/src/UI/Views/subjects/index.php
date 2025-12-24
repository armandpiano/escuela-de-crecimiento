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
                <p class="lead">Administra las materias y asignaturas del sistema educativo</p>
            </div>
        </div>
    </div>

    <!-- Barra de acciones principal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-2 mb-md-0">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSubjectModal">
                                <i class="fas fa-plus-circle"></i> Nueva Materia
                            </button>
                            <button class="btn btn-success" id="exportSubjects">
                                <i class="fas fa-download"></i> Exportar Lista
                            </button>
                            <button class="btn btn-info" id="bulkActions">
                                <i class="fas fa-tasks"></i> Acciones Masivas
                            </button>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="input-group" style="width: 300px;">
                                <input type="text" class="form-control" id="globalSearch" placeholder="Buscar materias...">
                                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y búsqueda avanzada -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <button class="btn btn-link p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                            <i class="fas fa-filter"></i> Filtros Avanzados
                            <i class="fas fa-chevron-down ms-1"></i>
                        </button>
                    </h6>
                </div>
                <div class="collapse" id="advancedFilters">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="statusFilter" class="form-label">Estado</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">Todos los estados</option>
                                    <option value="active">Activa</option>
                                    <option value="inactive">Inactiva</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="categoryFilter" class="form-label">Categoría</label>
                                <select class="form-select" id="categoryFilter">
                                    <option value="">Todas las categorías</option>
                                    <option value="mathematics">Matemáticas</option>
                                    <option value="language">Lenguaje</option>
                                    <option value="science">Ciencias</option>
                                    <option value="social">Ciencias Sociales</option>
                                    <option value="religious">Educación Religiosa</option>
                                    <option value="physical">Educación Física</option>
                                    <option value="arts">Artes</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="gradeLevelFilter" class="form-label">Nivel</label>
                                <select class="form-select" id="gradeLevelFilter">
                                    <option value="">Todos los niveles</option>
                                    <option value="1">1° Grado</option>
                                    <option value="2">2° Grado</option>
                                    <option value="3">3° Grado</option>
                                    <option value="4">4° Grado</option>
                                    <option value="5">5° Grado</option>
                                    <option value="6">6° Grado</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-outline-primary" id="applyAdvancedFilters">
                                    <i class="fas fa-check"></i> Aplicar Filtros
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="clearAdvancedFilters">
                                    <i class="fas fa-times"></i> Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vista de materias -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Lista de Materias
                        <span class="badge bg-secondary ms-2" id="totalSubjects">0</span>
                    </h5>
                    <div class="d-flex align-items-center">
                        <span class="me-2">Vista:</span>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="viewMode" id="tableView" value="table" checked>
                            <label class="btn btn-outline-primary btn-sm" for="tableView">
                                <i class="fas fa-table"></i> Tabla
                            </label>
                            <input type="radio" class="btn-check" name="viewMode" id="cardView" value="card">
                            <label class="btn btn-outline-primary btn-sm" for="cardView">
                                <i class="fas fa-th-large"></i> Tarjetas
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Vista de tabla -->
                    <div class="table-responsive" id="tableViewContainer">
                        <table class="table table-striped table-hover" id="subjectsTable">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllSubjects"></th>
                                    <th>Código</th>
                                    <th>Nombre de la Materia</th>
                                    <th>Categoría</th>
                                    <th>Nivel</th>
                                    <th>Creditos</th>
                                    <th>Estado</th>
                                    <th>Cursos Activos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargarán dinámicamente -->
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="spinner-border" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                        <p class="mt-2">Cargando materias...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Vista de tarjetas -->
                    <div class="row" id="cardViewContainer" style="display: none;">
                        <!-- Las tarjetas se cargarán dinámicamente -->
                    </div>
                    
                    <!-- Paginación -->
                    <nav aria-label="Paginación de materias">
                        <ul class="pagination justify-content-center" id="subjectsPagination">
                            <!-- La paginación se generará dinámicamente -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear materia -->
<div class="modal fade" id="createSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Crear Nueva Materia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createSubjectForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Información Básica</h6>
                            <div class="mb-3">
                                <label for="subjectName" class="form-label">Nombre de la Materia *</label>
                                <input type="text" class="form-control" id="subjectName" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="subjectCode" class="form-label">Código de la Materia *</label>
                                <input type="text" class="form-control" id="subjectCode" name="code" required>
                                <div class="form-text">Código único de identificación (ej: MAT-001)</div>
                            </div>
                            <div class="mb-3">
                                <label for="subjectCategory" class="form-label">Categoría *</label>
                                <select class="form-select" id="subjectCategory" name="category" required>
                                    <option value="">Seleccionar categoría</option>
                                    <option value="mathematics">Matemáticas</option>
                                    <option value="language">Lenguaje y Literatura</option>
                                    <option value="science">Ciencias Naturales</option>
                                    <option value="social">Ciencias Sociales</option>
                                    <option value="religious">Educación Religiosa</option>
                                    <option value="physical">Educación Física</option>
                                    <option value="arts">Artes</option>
                                    <option value="technology">Tecnología</option>
                                    <option value="foreign_language">Idiomas Extranjeros</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="subjectDescription" class="form-label">Descripción</label>
                                <textarea class="form-control" id="subjectDescription" name="description" rows="3" placeholder="Descripción detallada de la materia..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Configuración Académica</h6>
                            <div class="mb-3">
                                <label for="subjectGradeLevel" class="form-label">Nivel *</label>
                                <select class="form-select" id="subjectGradeLevel" name="grade_level" required>
                                    <option value="">Seleccionar nivel</option>
                                    <option value="1">1° Grado</option>
                                    <option value="2">2° Grado</option>
                                    <option value="3">3° Grado</option>
                                    <option value="4">4° Grado</option>
                                    <option value="5">5° Grado</option>
                                    <option value="6">6° Grado</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="subjectCredits" class="form-label">Créditos</label>
                                <input type="number" class="form-control" id="subjectCredits" name="credits" min="1" max="10" value="3">
                                <div class="form-text">Número de créditos académicos (1-10)</div>
                            </div>
                            <div class="mb-3">
                                <label for="subjectHours" class="form-label">Horas Semanales</label>
                                <input type="number" class="form-control" id="subjectHours" name="weekly_hours" min="1" max="40" value="5">
                                <div class="form-text">Horas de clase por semana</div>
                            </div>
                            <div class="mb-3">
                                <label for="subjectStatus" class="form-label">Estado *</label>
                                <select class="form-select" id="subjectStatus" name="status" required>
                                    <option value="active">Activa</option>
                                    <option value="inactive">Inactiva</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Objetivos y Competencias</h6>
                            <div class="mb-3">
                                <label for="subjectObjectives" class="form-label">Objetivos de Aprendizaje</label>
                                <textarea class="form-control" id="subjectObjectives" name="objectives" rows="4" placeholder="Describe los objetivos principales de la materia..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="subjectCompetencies" class="form-label">Competencias a Desarrollar</label>
                                <textarea class="form-control" id="subjectCompetencies" name="competencies" rows="3" placeholder="Lista las competencias que se desarrollarán..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Materia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar materia -->
<div class="modal fade" id="editSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Materia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSubjectForm">
                <div class="modal-body">
                    <input type="hidden" id="editSubjectId" name="id">
                    <!-- Los mismos campos que en crear materia -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Información Básica</h6>
                            <div class="mb-3">
                                <label for="editSubjectName" class="form-label">Nombre de la Materia *</label>
                                <input type="text" class="form-control" id="editSubjectName" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="editSubjectCode" class="form-label">Código de la Materia *</label>
                                <input type="text" class="form-control" id="editSubjectCode" name="code" required readonly>
                            </div>
                            <div class="mb-3">
                                <label for="editSubjectCategory" class="form-label">Categoría *</label>
                                <select class="form-select" id="editSubjectCategory" name="category" required>
                                    <option value="">Seleccionar categoría</option>
                                    <option value="mathematics">Matemáticas</option>
                                    <option value="language">Lenguaje y Literatura</option>
                                    <option value="science">Ciencias Naturales</option>
                                    <option value="social">Ciencias Sociales</option>
                                    <option value="religious">Educación Religiosa</option>
                                    <option value="physical">Educación Física</option>
                                    <option value="arts">Artes</option>
                                    <option value="technology">Tecnología</option>
                                    <option value="foreign_language">Idiomas Extranjeros</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editSubjectDescription" class="form-label">Descripción</label>
                                <textarea class="form-control" id="editSubjectDescription" name="description" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Configuración Académica</h6>
                            <div class="mb-3">
                                <label for="editSubjectGradeLevel" class="form-label">Nivel *</label>
                                <select class="form-select" id="editSubjectGradeLevel" name="grade_level" required>
                                    <option value="">Seleccionar nivel</option>
                                    <option value="1">1° Grado</option>
                                    <option value="2">2° Grado</option>
                                    <option value="3">3° Grado</option>
                                    <option value="4">4° Grado</option>
                                    <option value="5">5° Grado</option>
                                    <option value="6">6° Grado</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editSubjectCredits" class="form-label">Créditos</label>
                                <input type="number" class="form-control" id="editSubjectCredits" name="credits" min="1" max="10">
                            </div>
                            <div class="mb-3">
                                <label for="editSubjectHours" class="form-label">Horas Semanales</label>
                                <input type="number" class="form-control" id="editSubjectHours" name="weekly_hours" min="1" max="40">
                            </div>
                            <div class="mb-3">
                                <label for="editSubjectStatus" class="form-label">Estado *</label>
                                <select class="form-select" id="editSubjectStatus" name="status" required>
                                    <option value="active">Activa</option>
                                    <option value="inactive">Inactiva</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Objetivos y Competencias</h6>
                            <div class="mb-3">
                                <label for="editSubjectObjectives" class="form-label">Objetivos de Aprendizaje</label>
                                <textarea class="form-control" id="editSubjectObjectives" name="objectives" rows="4"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="editSubjectCompetencies" class="form-label">Competencias a Desarrollar</label>
                                <textarea class="form-control" id="editSubjectCompetencies" name="competencies" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Materia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver detalles de materia -->
<div class="modal fade" id="viewSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Detalles de la Materia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="subjectDetails">
                    <!-- Los detalles se cargarán dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="editSubjectFromView">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button type="button" class="btn btn-success" id="printSubject">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="deleteSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar esta materia?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-warning"></i>
                    <strong>Advertencia:</strong> Esta acción no se puede deshacer y puede afectar los cursos que utilizan esta materia.
                </div>
                <p><strong>Materia:</strong> <span id="deleteSubjectName"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSubject">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentPage = 1;
    let recordsPerPage = 25;
    let filters = {};
    let viewMode = 'table';
    
    // Cargar datos iniciales
    loadSubjects();
    updateSubjectCount();
    
    // Event listeners para búsqueda
    document.getElementById('globalSearch').addEventListener('input', function() {
        filters.search = this.value;
        currentPage = 1;
        loadSubjects();
    });
    
    document.getElementById('searchBtn').addEventListener('click', function() {
        filters.search = document.getElementById('globalSearch').value;
        currentPage = 1;
        loadSubjects();
    });
    
    // Event listeners para filtros avanzados
    document.getElementById('applyAdvancedFilters').addEventListener('click', function() {
        applyAdvancedFilters();
    });
    
    document.getElementById('clearAdvancedFilters').addEventListener('click', function() {
        clearAdvancedFilters();
    });
    
    // Event listeners para cambio de vista
    document.getElementById('tableView').addEventListener('change', function() {
        if (this.checked) {
            switchView('table');
        }
    });
    
    document.getElementById('cardView').addEventListener('change', function() {
        if (this.checked) {
            switchView('card');
        }
    });
    
    // Event listeners para formularios
    document.getElementById('createSubjectForm').addEventListener('submit', function(e) {
        e.preventDefault();
        createSubject();
    });
    
    document.getElementById('editSubjectForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateSubject();
    });
    
    // Event listener para eliminar materia
    document.getElementById('confirmDeleteSubject').addEventListener('click', function() {
        deleteSubject();
    });
    
    // Event listener para seleccionar todos
    document.getElementById('selectAllSubjects').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.subject-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // Función para cargar materias
    function loadSubjects() {
        const tbody = document.querySelector('#subjectsTable tbody');
        const cardContainer = document.getElementById('cardViewContainer');
        
        // Mostrar loading
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando materias...</p>
                </td>
            </tr>
        `;
        
        cardContainer.innerHTML = `
            <div class="col-12 text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Cargando materias...</p>
            </div>
        `;
        
        // Simulación de carga de datos (en implementación real, hacer petición AJAX)
        setTimeout(() => {
            if (viewMode === 'table') {
                loadTableView();
            } else {
                loadCardView();
            }
        }, 1000);
    }
    
    // Función para vista de tabla
    function loadTableView() {
        const tbody = document.querySelector('#subjectsTable tbody');
        tbody.innerHTML = `
            <tr>
                <td><input type="checkbox" class="subject-checkbox" value="1"></td>
                <td><span class="badge bg-primary">MAT-001</span></td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="subject-icon me-2">
                            <i class="fas fa-calculator text-primary"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Matemáticas Básicas</div>
                            <small class="text-muted">Fundamentos matemáticos</small>
                        </div>
                    </div>
                </td>
                <td><span class="badge bg-info">Matemáticas</span></td>
                <td>1°-3° Grado</td>
                <td>4 créditos</td>
                <td><span class="badge bg-success">Activa</span></td>
                <td>3 cursos</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewSubject(1)">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="editSubject(1)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSubjectConfirm(1, 'Matemáticas Básicas', 'MAT-001')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td><input type="checkbox" class="subject-checkbox" value="2"></td>
                <td><span class="badge bg-primary">LEN-001</span></td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="subject-icon me-2">
                            <i class="fas fa-book text-success"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Lenguaje y Literatura</div>
                            <small class="text-muted">Desarrollo comunicativo</small>
                        </div>
                    </div>
                </td>
                <td><span class="badge bg-warning">Lenguaje</span></td>
                <td>1°-6° Grado</td>
                <td>5 créditos</td>
                <td><span class="badge bg-success">Activa</span></td>
                <td>6 cursos</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewSubject(2)">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="editSubject(2)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSubjectConfirm(2, 'Lenguaje y Literatura', 'LEN-001')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td><input type="checkbox" class="subject-checkbox" value="3"></td>
                <td><span class="badge bg-primary">REL-001</span></td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="subject-icon me-2">
                            <i class="fas fa-church text-warning"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Educación Religiosa</div>
                            <small class="text-muted">Formación cristiana</small>
                        </div>
                    </div>
                </td>
                <td><span class="badge bg-danger">Religión</span></td>
                <td>1°-6° Grado</td>
                <td>3 créditos</td>
                <td><span class="badge bg-success">Activa</span></td>
                <td>6 cursos</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewSubject(3)">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="editSubject(3)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSubjectConfirm(3, 'Educación Religiosa', 'REL-001')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }
    
    // Función para vista de tarjetas
    function loadCardView() {
        const cardContainer = document.getElementById('cardViewContainer');
        cardContainer.innerHTML = `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 subject-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="subject-icon-lg me-2">
                                <i class="fas fa-calculator text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">MAT-001</h6>
                                <small class="text-muted">Matemáticas</small>
                            </div>
                        </div>
                        <input type="checkbox" class="subject-checkbox" value="1">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Matemáticas Básicas</h5>
                        <p class="card-text">Fundamentos matemáticos para el desarrollo del pensamiento lógico.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-info">1°-3° Grado</span>
                            <span class="badge bg-success">Activa</span>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-star"></i> 4 créditos
                                <i class="fas fa-clock ms-2"></i> 5h/semana
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-sm btn-outline-primary" onclick="viewSubject(1)">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="editSubject(1)">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSubjectConfirm(1, 'Matemáticas Básicas', 'MAT-001')">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 subject-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="subject-icon-lg me-2">
                                <i class="fas fa-book text-success"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">LEN-001</h6>
                                <small class="text-muted">Lenguaje</small>
                            </div>
                        </div>
                        <input type="checkbox" class="subject-checkbox" value="2">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Lenguaje y Literatura</h5>
                        <p class="card-text">Desarrollo de competencias comunicativas y literarias.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-warning">1°-6° Grado</span>
                            <span class="badge bg-success">Activa</span>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-star"></i> 5 créditos
                                <i class="fas fa-clock ms-2"></i> 6h/semana
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-sm btn-outline-primary" onclick="viewSubject(2)">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="editSubject(2)">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSubjectConfirm(2, 'Lenguaje y Literatura', 'LEN-001')">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 subject-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="subject-icon-lg me-2">
                                <i class="fas fa-church text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">REL-001</h6>
                                <small class="text-muted">Religión</small>
                            </div>
                        </div>
                        <input type="checkbox" class="subject-checkbox" value="3">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Educación Religiosa</h5>
                        <p class="card-text">Formación cristiana integral y valores bíblicos.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-danger">1°-6° Grado</span>
                            <span class="badge bg-success">Activa</span>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-star"></i> 3 créditos
                                <i class="fas fa-clock ms-2"></i> 3h/semana
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-sm btn-outline-primary" onclick="viewSubject(3)">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="editSubject(3)">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSubjectConfirm(3, 'Educación Religiosa', 'REL-001')">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Función para actualizar contador de materias
    function updateSubjectCount() {
        document.getElementById('totalSubjects').textContent = '15';
    }
    
    // Función para cambiar vista
    function switchView(mode) {
        viewMode = mode;
        const tableView = document.getElementById('tableViewContainer');
        const cardView = document.getElementById('cardViewContainer');
        
        if (mode === 'table') {
            tableView.style.display = 'block';
            cardView.style.display = 'none';
        } else {
            tableView.style.display = 'none';
            cardView.style.display = 'block';
        }
        
        loadSubjects();
    }
    
    // Función para aplicar filtros avanzados
    function applyAdvancedFilters() {
        filters = {
            status: document.getElementById('statusFilter').value,
            category: document.getElementById('categoryFilter').value,
            grade_level: document.getElementById('gradeLevelFilter').value
        };
        
        currentPage = 1;
        loadSubjects();
        showAlert('Filtros aplicados correctamente', 'success');
    }
    
    // Función para limpiar filtros avanzados
    function clearAdvancedFilters() {
        document.getElementById('statusFilter').value = '';
        document.getElementById('categoryFilter').value = '';
        document.getElementById('gradeLevelFilter').value = '';
        document.getElementById('globalSearch').value = '';
        
        filters = {};
        currentPage = 1;
        loadSubjects();
        showAlert('Filtros limpiados', 'info');
    }
    
    // Función para crear materia
    function createSubject() {
        const formData = new FormData(document.getElementById('createSubjectForm'));
        
        // Validación básica
        if (!formData.get('name') || !formData.get('code') || !formData.get('category') || !formData.get('grade_level')) {
            showAlert('Por favor complete todos los campos requeridos', 'warning');
            return;
        }
        
        // Simulación de creación (en implementación real, hacer petición AJAX)
        showAlert('Materia creada exitosamente', 'success');
        bootstrap.Modal.getInstance(document.getElementById('createSubjectModal')).hide();
        document.getElementById('createSubjectForm').reset();
        loadSubjects();
        updateSubjectCount();
    }
    
    // Función para actualizar materia
    function updateSubject() {
        const formData = new FormData(document.getElementById('editSubjectForm'));
        
        // Validación básica
        if (!formData.get('name') || !formData.get('code') || !formData.get('category') || !formData.get('grade_level')) {
            showAlert('Por favor complete todos los campos requeridos', 'warning');
            return;
        }
        
        // Simulación de actualización (en implementación real, hacer petición AJAX)
        showAlert('Materia actualizada exitosamente', 'success');
        bootstrap.Modal.getInstance(document.getElementById('editSubjectModal')).hide();
        loadSubjects();
    }
    
    // Función para eliminar materia
    function deleteSubject() {
        const subjectId = document.getElementById('deleteSubjectModal').dataset.subjectId;
        
        // Simulación de eliminación (en implementación real, hacer petición AJAX)
        showAlert('Materia eliminada exitosamente', 'success');
        bootstrap.Modal.getInstance(document.getElementById('deleteSubjectModal')).hide();
        loadSubjects();
        updateSubjectCount();
    }
    
    // Función para mostrar alerta
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.container-fluid').firstChild);
        
        setTimeout(() => {
            bootstrap.Alert.getOrCreateInstance(alertDiv).close();
        }, 5000);
    }
});

// Funciones globales para botones de acción
function viewSubject(subjectId) {
    // Cargar detalles de la materia
    const detailsContainer = document.getElementById('subjectDetails');
    detailsContainer.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Información General</h6>
                <table class="table table-sm">
                    <tr><td><strong>Código:</strong></td><td>MAT-00${subjectId}</td></tr>
                    <tr><td><strong>Nombre:</strong></td><td>Matemáticas Básicas</td></tr>
                    <tr><td><strong>Categoría:</strong></td><td><span class="badge bg-info">Matemáticas</span></td></tr>
                    <tr><td><strong>Nivel:</strong></td><td>1°-3° Grado</td></tr>
                    <tr><td><strong>Estado:</strong></td><td><span class="badge bg-success">Activa</span></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Configuración Académica</h6>
                <table class="table table-sm">
                    <tr><td><strong>Créditos:</strong></td><td>4</td></tr>
                    <tr><td><strong>Horas Semanales:</strong></td><td>5 horas</td></tr>
                    <tr><td><strong>Cursos Activos:</strong></td><td>3</td></tr>
                    <tr><td><strong>Profesor:</strong></td><td>Prof. Ana García</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Descripción</h6>
                <p>Fundamentos matemáticos para el desarrollo del pensamiento lógico y la resolución de problemas en los primeros grados.</p>
            </div>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('viewSubjectModal'));
    modal.show();
}

function editSubject(subjectId) {
    // Cargar datos de la materia y abrir modal de edición
    document.getElementById('editSubjectId').value = subjectId;
    document.getElementById('editSubjectCode').value = 'MAT-00' + subjectId;
    document.getElementById('editSubjectName').value = subjectId == 1 ? 'Matemáticas Básicas' : 
                                                     subjectId == 2 ? 'Lenguaje y Literatura' : 
                                                     'Educación Religiosa';
    
    const modal = new bootstrap.Modal(document.getElementById('editSubjectModal'));
    modal.show();
}

function deleteSubjectConfirm(subjectId, subjectName, subjectCode) {
    document.getElementById('deleteSubjectName').textContent = subjectName;
    document.getElementById('deleteSubjectModal').dataset.subjectId = subjectId;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteSubjectModal'));
    modal.show();
}
</script>

<style>
.page-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
    margin-bottom: 1.5rem;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.75em;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.subject-icon {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
}

.subject-icon-lg {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    font-size: 1.25rem;
}

.subject-card {
    transition: transform 0.2s ease-in-out;
}

.subject-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.spinner-border {
    width: 2rem;
    height: 2rem;
}

.alert {
    border: none;
    border-radius: 0.375rem;
}

.table-sm td, .table-sm th {
    padding: 0.3rem;
}

.btn-check:checked + .btn {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.collapse {
    transition: all 0.3s ease;
}

.bg-primary {
    background-color: #0d6efd !important;
}

.bg-success {
    background-color: #198754 !important;
}

.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.bg-info {
    background-color: #0dcaf0 !important;
    color: #000 !important;
}

.bg-danger {
    background-color: #dc3545 !important;
}
</style>
