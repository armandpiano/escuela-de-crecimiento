    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-graduation-cap me-2"></i>Christian LMS</h5>
                    <p class="text-muted mb-0">
                        Sistema de Gestión Educativa moderno y eficiente.
                        Diseñado para mejorar la experiencia educativa.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="mb-2">
                        <a href="#" class="text-light text-decoration-none me-3">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-light text-decoration-none me-3">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-light text-decoration-none me-3">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-light text-decoration-none">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                    <p class="text-muted mb-0">
                        © <?= date('Y') ?> Christian LMS. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Global JavaScript functions
        
        // Show/hide loading overlay
        function showLoading() {
            $('.loading').show();
        }
        
        function hideLoading() {
            $('.loading').hide();
        }
        
        // Show alert message
        function showAlert(message, type = 'info') {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Remove existing alerts
            $('.alert').remove();
            
            // Add new alert
            $('main').prepend(alertHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        }
        
        // Confirm delete action
        function confirmDelete(message = '¿Estás seguro de que quieres eliminar este elemento?') {
            return confirm(message);
        }
        
        // Format date
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES');
        }
        
        // Format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount);
        }
        
        // Format number
        function formatNumber(number) {
            return new Intl.NumberFormat('es-ES').format(number);
        }
        
        // Initialize tooltips
        function initTooltips() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        
        // Initialize page
        $(document).ready(function() {
            // Hide loading overlay
            hideLoading();
            
            // Initialize tooltips
            initTooltips();
            
            // Auto-hide alerts
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
            
            // Form validation
            $('form').on('submit', function() {
                const form = $(this);
                if (form[0].checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.addClass('was-validated');
            });
            
            // AJAX form submission
            $('.ajax-form').on('submit', function(e) {
                e.preventDefault();
                
                const form = $(this);
                const submitBtn = form.find('[type="submit"]');
                const originalText = submitBtn.html();
                
                // Show loading
                submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Procesando...');
                submitBtn.prop('disabled', true);
                showLoading();
                
                // Get form data
                const formData = new FormData(this);
                
                // Send AJAX request
                $.ajax({
                    url: form.attr('action') || window.location.href,
                    method: form.attr('method') || 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showAlert(response.message || 'Operación exitosa', 'success');
                            if (response.redirect) {
                                setTimeout(function() {
                                    window.location.href = response.redirect;
                                }, 1500);
                            }
                        } else {
                            showAlert(response.message || 'Ha ocurrido un error', 'danger');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        const message = response?.message || 'Ha ocurrido un error inesperado';
                        showAlert(message, 'danger');
                    },
                    complete: function() {
                        // Reset button
                        submitBtn.html(originalText);
                        submitBtn.prop('disabled', false);
                        hideLoading();
                    }
                });
            });
            
            // Delete confirmation
            $('.delete-btn').on('click', function(e) {
                if (!confirmDelete()) {
                    e.preventDefault();
                }
            });
            
            // Search functionality
            $('.search-input').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                const target = $(this).data('target');
                
                if (target) {
                    $(target + ' tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                    });
                }
            });
            
            // Modal handling
            $('.modal').on('shown.bs.modal', function() {
                $(this).find('input:first').focus();
            });
        });
        
        // Utility functions
        window.ChristianLMS = {
            showLoading,
            hideLoading,
            showAlert,
            confirmDelete,
            formatDate,
            formatCurrency,
            formatNumber
        };
    </script>
</body>
</html>
