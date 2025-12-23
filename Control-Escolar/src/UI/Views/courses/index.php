<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-book"></i> Gestión de Cursos</h1>
                <p class="lead">Administra los cursos del sistema educativo cristiano</p>
            </div>
        </div>
    </div>

    <!-- Barra de acciones -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal">
                        <i class="fas fa-plus"></i> Nuevo Curso
                    </button>
                    <button class="btn btn-success" id="exportCourses">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form class="row g-3" id="courseFilters">
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">Estado</label>
                            <select class="form-select" id="statusFilter" name="status">
                                <option value="">Todos los estados</option>
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                                <option value="draft">Borrador</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="gradeLevelFilter" class="form-label">Nivel</label>
                            <select class="form-select" id="gradeLevelFilter" name="grade_level">
                                <option value="">Todos los niveles</option>
                                <option value="1">1° Grado</option>
                                <option value="2">2° Grado</option>
                                <option value="3">3° Grado</option>
                                <option value="4">4° Grado</option>
                                <option value="5">5° Grado</option>
                                <option value="6">6° Grado</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="searchFilter" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="searchFilter" name="search" placeholder="Buscar por nombre o código...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de cursos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Lista de Cursos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="coursesTable">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>Código</th>
                                    <th>Nombre del Curso</th>
                                    <th>Nivel</th>
                                    <th>Período</th>
                                    <th>Estado</th>
                                    <th>Inscripciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargarán dinámicamente -->
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="spinner-border" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                        <p class="mt-2">Cargando cursos...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <nav aria-label="Paginación de cursos">
                        <ul class="pagination justify-content-center" id="coursesPagination">
                            <!-- La paginación se generará dinámicamente -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear curso -->
<div class="modal fade" id="createCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Crear Nuevo Curso
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createCourseForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="courseName" class="form-label">Nombre del Curso *</label>
                                <input type="text" class="form-control" id="courseName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="courseCode" class="form-label">Código del Curso *</label>
                                <input type="text" class="form-control" id="courseCode" name="code" required>
                                <div class="form-text">Código único de identificación</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="gradeLevel" class="form-label">Nivel *</label>
                                <select class="form-select" id="gradeLevel" name="grade_level" required>
                                    <option value="">Seleccionar nivel</option>
                                    <option value="1">1° Grado</option>
                                    <option value="2">2° Grado</option>
                                    <option value="3">3° Grado</option>
                                    <option value="4">4° Grado</option>
                                    <option value="5">5° Grado</option>
                                    <option value="6">6° Grado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="courseStatus" class="form-label">Estado *</label>
                                <select class="form-select" id="courseStatus" name="status" required>
                                    <option value="draft">Borrador</option>
                                    <option value="active">Activo</option>
                                    <option value="inactive">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="courseDescription" class="form-label">Descripción</label>
                        <textarea class="form-control" id="courseDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="academicPeriod" class="form-label">Período Académico</label>
                        <select class="form-select" id="academicPeriod" name="academic_period_id">
                            <option value="">Seleccionar período</option>
                            <!-- Opciones se cargarán dinámicamente -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Curso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar curso -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Curso
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCourseForm">
                <div class="modal-body">
                    <input type="hidden" id="editCourseId" name="id">
                    <!-- Los mismos campos que en crear curso -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCourseName" class="form-label">Nombre del Curso *</label>
                                <input type="text" class="form-control" id="editCourseName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCourseCode" class="form-label">Código del Curso *</label>
                                <input type="text" class="form-control" id="editCourseCode" name="code" required readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editGradeLevel" class="form-label">Nivel *</label>
                                <select class="form-select" id="editGradeLevel" name="grade_level" required>
                                    <option value="">Seleccionar nivel</option>
                                    <option value="1">1° Grado</option>
                                    <option value="2">2° Grado</option>
                                    <option value="3">3° Grado</option>
                                    <option value="4">4° Grado</option>
                                    <option value="5">5° Grado</option>
                                    <option value="6">6° Grado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCourseStatus" class="form-label">Estado *</label>
                                <select class="form-select" id="editCourseStatus" name="status" required>
                                    <option value="draft">Borrador</option>
                                    <option value="active">Activo</option>
                                    <option value="inactive">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editCourseDescription" class="form-label">Descripción</label>
                        <textarea class="form-control" id="editCourseDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editAcademicPeriod" class="form-label">Período Académico</label>
                        <select class="form-select" id="editAcademicPeriod" name="academic_period_id">
                            <option value="">Seleccionar período</option>
                            <!-- Opciones se cargarán dinámicamente -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Curso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="deleteCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar este curso?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-warning"></i>
                    <strong>Advertencia:</strong> Esta acción no se puede deshacer y eliminará todas las inscripciones asociadas.
                </div>
                <p><strong>Curso:</strong> <span id="deleteCourseName"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteCourse">
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
    let filters = {};
    
    // Cargar cursos iniciales
    loadCourses();
    
    // Cargar períodos académicos para los selects
    loadAcademicPeriods();
    
    // Event listeners para filtros
    document.getElementById('courseFilters').addEventListener('submit', function(e) {
        e.preventDefault();
        filters = {
            status: document.getElementById('statusFilter').value,
            grade_level: document.getElementById('gradeLevelFilter').value,
            search: document.getElementById('searchFilter').value
        };
        currentPage = 1;
        loadCourses();
    });
    
    // Event listener para crear curso
    document.getElementById('createCourseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        createCourse();
    });
    
    // Event listener para editar curso
    document.getElementById('editCourseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateCourse();
    });
    
    // Event listener para eliminar curso
    document.getElementById('confirmDeleteCourse').addEventListener('click', function() {
        deleteCourse();
    });
    
    // Event listener para seleccionar todos
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.course-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // Función para cargar cursos
    function loadCourses() {
        const tbody = document.querySelector('#coursesTable tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando cursos...</p>
                </td>
            </tr>
        `;
        
        // Simulación de carga de datos (en implementación real, hacer petición AJAX)
        setTimeout(() => {
            tbody.innerHTML = `
                <tr>
                    <td><input type="checkbox" class="course-checkbox" value="1"></td>
                    <td><span class="badge bg-primary">CUR-001</span></td>
                    <td>Matemáticas Básicas</td>
                    <td>1° Grado</td>
                    <td>2024-S1</td>
                    <td><span class="badge bg-success">Activo</span></td>
                    <td>25/30</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="editCourse(1)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="viewCourse(1)">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCourseConfirm(1, 'Matemáticas Básicas')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="course-checkbox" value="2"></td>
                    <td><span class="badge bg-primary">CUR-002</span></td>
                    <td>Estudios Bíblicos</td>
                    <td>2° Grado</td>
                    <td>2024-S1</td>
                    <td><span class="badge bg-warning">Borrador</span></td>
                    <td>0/25</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="editCourse(2)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="viewCourse(2)">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCourseConfirm(2, 'Estudios Bíblicos')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }, 1000);
    }
    
    // Función para cargar períodos académicos
    function loadAcademicPeriods() {
        // Simulación de carga de períodos (en implementación real, hacer petición AJAX)
        const periods = [
            { id: 1, name: '2024-S1 (Primer Semestre)' },
            { id: 2, name: '2024-S2 (Segundo Semestre)' },
            { id: 3, name: '2024-T1 (Primer Trimestre)' }
        ];
        
        const selects = ['academicPeriod', 'editAcademicPeriod'];
        selects.forEach(selectId => {
            const select = document.getElementById(selectId);
            periods.forEach(period => {
                const option = document.createElement('option');
                option.value = period.id;
                option.textContent = period.name;
                select.appendChild(option);
            });
        });
    }
    
    // Función para crear curso
    function createCourse() {
        const formData = new FormData(document.getElementById('createCourseForm'));
        
        // Validación básica
        if (!formData.get('name') || !formData.get('code') || !formData.get('grade_level')) {
            showAlert('Por favor complete todos los campos requeridos', 'warning');
            return;
        }
        
        // Simulación de creación (en implementación real, hacer petición AJAX)
        showAlert('Curso creado exitosamente', 'success');
        bootstrap.Modal.getInstance(document.getElementById('createCourseModal')).hide();
        document.getElementById('createCourseForm').reset();
        loadCourses();
    }
    
    // Función para actualizar curso
    function updateCourse() {
        const formData = new FormData(document.getElementById('editCourseForm'));
        
        // Validación básica
        if (!formData.get('name') || !formData.get('code') || !formData.get('grade_level')) {
            showAlert('Por favor complete todos los campos requeridos', 'warning');
            return;
        }
        
        // Simulación de actualización (en implementación real, hacer petición AJAX)
        showAlert('Curso actualizado exitosamente', 'success');
        bootstrap.Modal.getInstance(document.getElementById('editCourseModal')).hide();
        loadCourses();
    }
    
    // Función para eliminar curso
    function deleteCourse() {
        const courseId = document.getElementById('deleteCourseModal').dataset.courseId;
        
        // Simulación de eliminación (en implementación real, hacer petición AJAX)
        showAlert('Curso eliminado exitosamente', 'success');
        bootstrap.Modal.getInstance(document.getElementById('deleteCourseModal')).hide();
        loadCourses();
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
        
        // Auto-ocultar después de 5 segundos
        setTimeout(() => {
            bootstrap.Alert.getOrCreateInstance(alertDiv).close();
        }, 5000);
    }
});

// Funciones globales para botones de acción
function editCourse(courseId) {
    // Cargar datos del curso y abrir modal de edición
    document.getElementById('editCourseId').value = courseId;
    document.getElementById('editCourseCode').value = 'CUR-00' + courseId;
    document.getElementById('editCourseName').value = courseId == 1 ? 'Matemáticas Básicas' : 'Estudios Bíblicos';
    
    const modal = new bootstrap.Modal(document.getElementById('editCourseModal'));
    modal.show();
}

function viewCourse(courseId) {
    // Implementar vista de detalles del curso
    showAlert('Funcionalidad de vista detallada próximamente', 'info');
}

function deleteCourseConfirm(courseId, courseName) {
    document.getElementById('deleteCourseModal').dataset.courseId = courseId;
    document.getElementById('deleteCourseName').textContent = courseName;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteCourseModal'));
    modal.show();
}
</script>

<style>
.page-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
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

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.alert {
    border: none;
    border-radius: 0.375rem;
}

.spinner-border {
    width: 2rem;
    height: 2rem;
}

.pagination .page-link {
    color: #007bff;
    border-color: #dee2e6;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}
</style>