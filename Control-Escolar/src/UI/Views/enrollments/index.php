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
                <h1><i class="fas fa-user-graduate"></i> Gestión de Inscripciones</h1>
                <p class="lead">Administra las inscripciones de estudiantes en los cursos</p>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 id="totalEnrollments">0</h4>
                            <p class="mb-0">Total Inscripciones</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 id="activeEnrollments">0</h4>
                            <p class="mb-0">Inscripciones Activas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 id="pendingEnrollments">0</h4>
                            <p class="mb-0">Pendientes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 id="paidEnrollments">0</h4>
                            <p class="mb-0">Pagadas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de acciones -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enrollStudentModal">
                                <i class="fas fa-user-plus"></i> Inscribir Estudiante
                            </button>
                            <button class="btn btn-success" id="exportEnrollments">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                            <button class="btn btn-info" id="generateReport">
                                <i class="fas fa-chart-bar"></i> Reporte
                            </button>
                        </div>
                        <div>
                            <select class="form-select" id="statusQuickFilter" style="width: auto;">
                                <option value="">Todos los estados</option>
                                <option value="active">Activas</option>
                                <option value="pending">Pendientes</option>
                                <option value="cancelled">Canceladas</option>
                                <option value="completed">Completadas</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros avanzados -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="studentFilter" class="form-label">Estudiante</label>
                            <select class="form-select" id="studentFilter">
                                <option value="">Todos los estudiantes</option>
                                <!-- Opciones se cargarán dinámicamente -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="courseFilter" class="form-label">Curso</label>
                            <select class="form-select" id="courseFilter">
                                <option value="">Todos los cursos</option>
                                <!-- Opciones se cargarán dinámicamente -->
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="paymentStatusFilter" class="form-label">Estado de Pago</label>
                            <select class="form-select" id="paymentStatusFilter">
                                <option value="">Todos</option>
                                <option value="pending">Pendiente</option>
                                <option value="paid">Pagado</option>
                                <option value="partial">Parcial</option>
                                <option value="overdue">Vencido</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="dateFromFilter" class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control" id="dateFromFilter">
                        </div>
                        <div class="col-md-2">
                            <label for="dateToFilter" class="form-label">Fecha Hasta</label>
                            <input type="date" class="form-control" id="dateToFilter">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-outline-primary" id="applyFilters">
                                <i class="fas fa-filter"></i> Aplicar Filtros
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="clearFilters">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de inscripciones -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Lista de Inscripciones</h5>
                    <div class="d-flex align-items-center">
                        <span class="me-3">Mostrar:</span>
                        <select class="form-select form-select-sm" id="recordsPerPage" style="width: auto;">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="enrollmentsTable">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllEnrollments"></th>
                                    <th>ID</th>
                                    <th>Estudiante</th>
                                    <th>Curso</th>
                                    <th>Fecha Inscripción</th>
                                    <th>Estado</th>
                                    <th>Pago</th>
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
                                        <p class="mt-2">Cargando inscripciones...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <nav aria-label="Paginación de inscripciones">
                        <ul class="pagination justify-content-center" id="enrollmentsPagination">
                            <!-- La paginación se generará dinámicamente -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para inscribir estudiante -->
<div class="modal fade" id="enrollStudentModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Inscribir Estudiante
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="enrollStudentForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Información del Estudiante</h6>
                            <div class="mb-3">
                                <label for="studentSearch" class="form-label">Buscar Estudiante</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="studentSearch" placeholder="Buscar por nombre, documento o email...">
                                    <button class="btn btn-outline-secondary" type="button" id="searchStudent">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="studentSelect" class="form-label">Estudiante Seleccionado *</label>
                                <select class="form-select" id="studentSelect" name="student_id" required>
                                    <option value="">Seleccionar estudiante</option>
                                    <!-- Opciones se cargarán dinámicamente -->
                                </select>
                            </div>
                            <div id="studentInfo" class="alert alert-info" style="display: none;">
                                <!-- Información del estudiante seleccionado -->
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Información del Curso</h6>
                            <div class="mb-3">
                                <label for="courseSelect" class="form-label">Curso *</label>
                                <select class="form-select" id="courseSelect" name="course_id" required>
                                    <option value="">Seleccionar curso</option>
                                    <!-- Opciones se cargarán dinámicamente -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="academicPeriodSelect" class="form-label">Período Académico *</label>
                                <select class="form-select" id="academicPeriodSelect" name="academic_period_id" required>
                                    <option value="">Seleccionar período</option>
                                    <!-- Opciones se cargarán dinámicamente -->
                                </select>
                            </div>
                            <div id="courseInfo" class="alert alert-success" style="display: none;">
                                <!-- Información del curso seleccionado -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Detalles de Inscripción</h6>
                            <div class="mb-3">
                                <label for="enrollmentStatus" class="form-label">Estado *</label>
                                <select class="form-select" id="enrollmentStatus" name="status" required>
                                    <option value="pending">Pendiente</option>
                                    <option value="active">Activa</option>
                                    <option value="cancelled">Cancelada</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="enrollmentDate" class="form-label">Fecha de Inscripción *</label>
                                <input type="date" class="form-control" id="enrollmentDate" name="enrollment_date" required>
                            </div>
                            <div class="mb-3">
                                <label for="enrollmentNotes" class="form-label">Notas</label>
                                <textarea class="form-control" id="enrollmentNotes" name="notes" rows="3" placeholder="Observaciones adicionales..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">Información de Pago</h6>
                            <div class="mb-3">
                                <label for="paymentStatus" class="form-label">Estado de Pago *</label>
                                <select class="form-select" id="paymentStatus" name="payment_status" required>
                                    <option value="pending">Pendiente</option>
                                    <option value="paid">Pagado</option>
                                    <option value="partial">Parcial</option>
                                    <option value="overdue">Vencido</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="totalAmount" class="form-label">Monto Total</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="totalAmount" name="total_amount" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="paidAmount" class="form-label">Monto Pagado</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="paidAmount" name="paid_amount" step="0.01" min="0" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Inscribir Estudiante
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver detalles de inscripción -->
<div class="modal fade" id="viewEnrollmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Detalles de Inscripción
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="enrollmentDetails">
                    <!-- Los detalles se cargarán dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="editEnrollment">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button type="button" class="btn btn-success" id="printEnrollment">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar cancelación -->
<div class="modal fade" id="cancelEnrollmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Cancelar Inscripción
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea cancelar esta inscripción?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-warning"></i>
                    <strong>Advertencia:</strong> Esta acción puede afectar los pagos y registros académicos del estudiante.
                </div>
                <p><strong>Estudiante:</strong> <span id="cancelStudentName"></span></p>
                <p><strong>Curso:</strong> <span id="cancelCourseName"></span></p>
                <div class="mb-3">
                    <label for="cancellationReason" class="form-label">Motivo de cancelación</label>
                    <textarea class="form-control" id="cancellationReason" rows="3" placeholder="Indique el motivo de la cancelación..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> No, Mantener
                </button>
                <button type="button" class="btn btn-warning" id="confirmCancelEnrollment">
                    <i class="fas fa-ban"></i> Sí, Cancelar
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
    
    // Inicializar fecha de inscripción con la fecha actual
    document.getElementById('enrollmentDate').valueAsDate = new Date();
    
    // Cargar datos iniciales
    loadEnrollments();
    loadStudents();
    loadCourses();
    loadAcademicPeriods();
    updateStatistics();
    
    // Event listeners para filtros
    document.getElementById('applyFilters').addEventListener('click', function() {
        applyFilters();
    });
    
    document.getElementById('clearFilters').addEventListener('click', function() {
        clearFilters();
    });
    
    document.getElementById('statusQuickFilter').addEventListener('change', function() {
        filters.status = this.value;
        currentPage = 1;
        loadEnrollments();
    });
    
    document.getElementById('recordsPerPage').addEventListener('change', function() {
        recordsPerPage = parseInt(this.value);
        currentPage = 1;
        loadEnrollments();
    });
    
    // Event listeners para modal de inscripción
    document.getElementById('enrollStudentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        enrollStudent();
    });
    
    document.getElementById('studentSearch').addEventListener('input', function() {
        if (this.value.length >= 3) {
            searchStudents(this.value);
        }
    });
    
    document.getElementById('courseSelect').addEventListener('change', function() {
        if (this.value) {
            loadCourseInfo(this.value);
        }
    });
    
    document.getElementById('studentSelect').addEventListener('change', function() {
        if (this.value) {
            loadStudentInfo(this.value);
        }
    });
    
    // Event listeners para botones de acción
    document.getElementById('confirmCancelEnrollment').addEventListener('click', function() {
        cancelEnrollment();
    });
    
    // Event listener para seleccionar todos
    document.getElementById('selectAllEnrollments').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.enrollment-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // Función para cargar inscripciones
    function loadEnrollments() {
        const tbody = document.querySelector('#enrollmentsTable tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando inscripciones...</p>
                </td>
            </tr>
        `;
        
        // Simulación de carga de datos (en implementación real, hacer petición AJAX)
        setTimeout(() => {
            tbody.innerHTML = `
                <tr>
                    <td><input type="checkbox" class="enrollment-checkbox" value="1"></td>
                    <td><span class="badge bg-secondary">ENR-001</span></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary text-white rounded-circle me-2">
                                JM
                            </div>
                            <div>
                                <div class="fw-bold">Juan Pérez</div>
                                <small class="text-muted">ID: 12345</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>
                            <div class="fw-bold">Matemáticas Básicas</div>
                            <small class="text-muted">CUR-001</small>
                        </div>
                    </td>
                    <td>15/01/2024</td>
                    <td><span class="badge bg-success">Activa</span></td>
                    <td>
                        <span class="badge bg-success">Pagado</span>
                        <br><small class="text-muted">$150.00</small>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-primary" onclick="viewEnrollment(1)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="editEnrollment(1)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="cancelEnrollmentConfirm(1, 'Juan Pérez', 'Matemáticas Básicas')">
                                <i class="fas fa-ban"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="enrollment-checkbox" value="2"></td>
                    <td><span class="badge bg-secondary">ENR-002</span></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-success text-white rounded-circle me-2">
                                MR
                            </div>
                            <div>
                                <div class="fw-bold">María Rodríguez</div>
                                <small class="text-muted">ID: 12346</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>
                            <div class="fw-bold">Estudios Bíblicos</div>
                            <small class="text-muted">CUR-002</small>
                        </div>
                    </td>
                    <td>16/01/2024</td>
                    <td><span class="badge bg-warning">Pendiente</span></td>
                    <td>
                        <span class="badge bg-warning">Pendiente</span>
                        <br><small class="text-muted">$120.00</small>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-primary" onclick="viewEnrollment(2)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="editEnrollment(2)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="cancelEnrollmentConfirm(2, 'María Rodríguez', 'Estudios Bíblicos')">
                                <i class="fas fa-ban"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }, 1000);
    }
    
    // Función para cargar estudiantes
    function loadStudents() {
        // Simulación de carga de estudiantes (en implementación real, hacer petición AJAX)
        const students = [
            { id: 1, name: 'Juan Pérez', document: '12345', email: 'juan@email.com' },
            { id: 2, name: 'María Rodríguez', document: '12346', email: 'maria@email.com' },
            { id: 3, name: 'Carlos López', document: '12347', email: 'carlos@email.com' }
        ];
        
        const select = document.getElementById('studentSelect');
        students.forEach(student => {
            const option = document.createElement('option');
            option.value = student.id;
            option.textContent = `${student.name} (${student.document})`;
            option.dataset.email = student.email;
            select.appendChild(option);
        });
    }
    
    // Función para cargar cursos
    function loadCourses() {
        // Simulación de carga de cursos (en implementación real, hacer petición AJAX)
        const courses = [
            { id: 1, name: 'Matemáticas Básicas', code: 'CUR-001', price: 150 },
            { id: 2, name: 'Estudios Bíblicos', code: 'CUR-002', price: 120 },
            { id: 3, name: 'Ciencias Naturales', code: 'CUR-003', price: 180 }
        ];
        
        const select = document.getElementById('courseSelect');
        courses.forEach(course => {
            const option = document.createElement('option');
            option.value = course.id;
            option.textContent = `${course.name} (${course.code})`;
            option.dataset.price = course.price;
            option.dataset.code = course.code;
            select.appendChild(option);
        });
    }
    
    // Función para cargar períodos académicos
    function loadAcademicPeriods() {
        // Simulación de carga de períodos (en implementación real, hacer petición AJAX)
        const periods = [
            { id: 1, name: '2024-S1 (Primer Semestre)' },
            { id: 2, name: '2024-S2 (Segundo Semestre)' },
            { id: 3, name: '2024-T1 (Primer Trimestre)' }
        ];
        
        const select = document.getElementById('academicPeriodSelect');
        periods.forEach(period => {
            const option = document.createElement('option');
            option.value = period.id;
            option.textContent = period.name;
            select.appendChild(option);
        });
    }
    
    // Función para actualizar estadísticas
    function updateStatistics() {
        // Simulación de actualización de estadísticas
        document.getElementById('totalEnrollments').textContent = '156';
        document.getElementById('activeEnrollments').textContent = '142';
        document.getElementById('pendingEnrollments').textContent = '8';
        document.getElementById('paidEnrollments').textContent = '134';
    }
    
    // Función para inscribir estudiante
    function enrollStudent() {
        const formData = new FormData(document.getElementById('enrollStudentForm'));
        
        // Validación básica
        if (!formData.get('student_id') || !formData.get('course_id') || !formData.get('academic_period_id')) {
            showAlert('Por favor complete todos los campos requeridos', 'warning');
            return;
        }
        
        // Simulación de inscripción (en implementación real, hacer petición AJAX)
        showAlert('Estudiante inscrito exitosamente', 'success');
        bootstrap.Modal.getInstance(document.getElementById('enrollStudentModal')).hide();
        document.getElementById('enrollStudentForm').reset();
        document.getElementById('enrollmentDate').valueAsDate = new Date();
        loadEnrollments();
        updateStatistics();
    }
    
    // Función para cargar información del estudiante
    function loadStudentInfo(studentId) {
        const studentSelect = document.getElementById('studentSelect');
        const selectedOption = studentSelect.options[studentSelect.selectedIndex];
        
        if (selectedOption && selectedOption.dataset.email) {
            const studentInfo = document.getElementById('studentInfo');
            studentInfo.innerHTML = `
                <strong>Email:</strong> ${selectedOption.dataset.email}
                <br><strong>ID:</strong> ${selectedOption.value}
            `;
            studentInfo.style.display = 'block';
        }
    }
    
    // Función para cargar información del curso
    function loadCourseInfo(courseId) {
        const courseSelect = document.getElementById('courseSelect');
        const selectedOption = courseSelect.options[courseSelect.selectedIndex];
        
        if (selectedOption && selectedOption.dataset.price) {
            const courseInfo = document.getElementById('courseInfo');
            courseInfo.innerHTML = `
                <strong>Precio:</strong> $${selectedOption.dataset.price}
                <br><strong>Código:</strong> ${selectedOption.dataset.code}
            `;
            courseInfo.style.display = 'block';
            
            // Actualizar monto total automáticamente
            document.getElementById('totalAmount').value = selectedOption.dataset.price;
        }
    }
    
    // Función para buscar estudiantes
    function searchStudents(query) {
        // Simulación de búsqueda (en implementación real, hacer petición AJAX)
        console.log('Buscando estudiantes:', query);
    }
    
    // Función para aplicar filtros
    function applyFilters() {
        filters = {
            student_id: document.getElementById('studentFilter').value,
            course_id: document.getElementById('courseFilter').value,
            payment_status: document.getElementById('paymentStatusFilter').value,
            date_from: document.getElementById('dateFromFilter').value,
            date_to: document.getElementById('dateToFilter').value
        };
        
        currentPage = 1;
        loadEnrollments();
        showAlert('Filtros aplicados correctamente', 'success');
    }
    
    // Función para limpiar filtros
    function clearFilters() {
        document.getElementById('studentFilter').value = '';
        document.getElementById('courseFilter').value = '';
        document.getElementById('paymentStatusFilter').value = '';
        document.getElementById('dateFromFilter').value = '';
        document.getElementById('dateToFilter').value = '';
        document.getElementById('statusQuickFilter').value = '';
        
        filters = {};
        currentPage = 1;
        loadEnrollments();
        showAlert('Filtros limpiados', 'info');
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
function viewEnrollment(enrollmentId) {
    // Cargar detalles de la inscripción
    const detailsContainer = document.getElementById('enrollmentDetails');
    detailsContainer.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Información General</h6>
                <table class="table table-sm">
                    <tr><td><strong>ID:</strong></td><td>ENR-00${enrollmentId}</td></tr>
                    <tr><td><strong>Estudiante:</strong></td><td>Juan Pérez</td></tr>
                    <tr><td><strong>Curso:</strong></td><td>Matemáticas Básicas</td></tr>
                    <tr><td><strong>Fecha Inscripción:</strong></td><td>15/01/2024</td></tr>
                    <tr><td><strong>Estado:</strong></td><td><span class="badge bg-success">Activa</span></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Información de Pago</h6>
                <table class="table table-sm">
                    <tr><td><strong>Estado Pago:</strong></td><td><span class="badge bg-success">Pagado</span></td></tr>
                    <tr><td><strong>Monto Total:</strong></td><td>$150.00</td></tr>
                    <tr><td><strong>Monto Pagado:</strong></td><td>$150.00</td></tr>
                    <tr><td><strong>Fecha Pago:</strong></td><td>16/01/2024</td></tr>
                </table>
            </div>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('viewEnrollmentModal'));
    modal.show();
}

function editEnrollment(enrollmentId) {
    showAlert('Funcionalidad de edición próximamente', 'info');
}

function cancelEnrollmentConfirm(enrollmentId, studentName, courseName) {
    document.getElementById('cancelStudentName').textContent = studentName;
    document.getElementById('cancelCourseName').textContent = courseName;
    document.getElementById('cancelEnrollmentModal').dataset.enrollmentId = enrollmentId;
    
    const modal = new bootstrap.Modal(document.getElementById('cancelEnrollmentModal'));
    modal.show();
}

function cancelEnrollment() {
    const enrollmentId = document.getElementById('cancelEnrollmentModal').dataset.enrollmentId;
    const reason = document.getElementById('cancellationReason').value;
    
    if (!reason.trim()) {
        showAlert('Por favor indique el motivo de la cancelación', 'warning');
        return;
    }
    
    // Simulación de cancelación (en implementación real, hacer petición AJAX)
    showAlert('Inscripción cancelada exitosamente', 'success');
    bootstrap.Modal.getInstance(document.getElementById('cancelEnrollmentModal')).hide();
    document.getElementById('cancellationReason').value = '';
    loadEnrollments();
    updateStatistics();
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

.avatar-sm {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 600;
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

.input-group-text {
    background-color: #e9ecef;
    border-color: #ced4da;
    color: #495057;
}
</style>
