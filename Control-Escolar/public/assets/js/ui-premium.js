(function () {
    const $ = window.jQuery;

    function showLoading() {
        if ($) {
            $('.loading').addClass('is-active');
        } else {
            document.querySelectorAll('.loading').forEach((el) => el.classList.add('is-active'));
        }
    }

    function hideLoading() {
        if ($) {
            $('.loading').removeClass('is-active');
        } else {
            document.querySelectorAll('.loading').forEach((el) => el.classList.remove('is-active'));
        }
    }

    function showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        if ($) {
            $('.alert').remove();
            $('main').prepend(alertHtml);
            setTimeout(function () {
                $('.alert').fadeOut();
            }, 5000);
        } else {
            const main = document.querySelector('main');
            if (main) {
                main.insertAdjacentHTML('afterbegin', alertHtml);
                setTimeout(() => {
                    document.querySelectorAll('.alert').forEach((alert) => alert.classList.remove('show'));
                }, 5000);
            }
        }
    }

    function confirmDelete(message = '¿Estás seguro de que quieres eliminar este elemento?') {
        return window.confirm(message);
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES');
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    }

    function formatNumber(number) {
        return new Intl.NumberFormat('es-ES').format(number);
    }

    function initTooltips() {
        if (!window.bootstrap) return;
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    function initSelect2(context) {
        if (!$ || !$.fn.select2) return;
        const scope = context ? $(context) : $(document);
        scope.find('select.select2, select[data-enhance="select"]').each(function () {
            const $select = $(this);
            if ($select.data('select2')) {
                return;
            }
            const $modal = $select.closest('.modal');
            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $modal.length ? $modal : $(document.body)
            });
        });
    }

    function initFormValidation() {
        if (!$) return;
        $('form').on('submit', function (event) {
            const form = $(this);
            if (form[0].checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.addClass('was-validated');
        });
    }

    function initAjaxForms() {
        if (!$) return;
        $('.ajax-form').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = form.find('[type="submit"]');
            const originalText = submitBtn.html();

            submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Procesando...');
            submitBtn.prop('disabled', true);
            showLoading();

            const formData = new FormData(this);

            $.ajax({
                url: form.attr('action') || window.location.href,
                method: form.attr('method') || 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        showAlert(response.message || 'Operación exitosa', 'success');
                        if (response.redirect) {
                            setTimeout(function () {
                                window.location.href = response.redirect;
                            }, 1500);
                        }
                    } else {
                        showAlert(response.message || 'Ha ocurrido un error', 'danger');
                    }
                },
                error: function (xhr) {
                    const response = xhr.responseJSON;
                    const message = response?.message || 'Ha ocurrido un error inesperado';
                    showAlert(message, 'danger');
                },
                complete: function () {
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                    hideLoading();
                }
            });
        });
    }

    function initDeleteButtons() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach((button) => {
            button.addEventListener('click', function (e) {
                if (!confirmDelete()) {
                    e.preventDefault();
                }
            });
        });
    }

    function initSearchInputs() {
        if ($) {
            $('.search-input').on('keyup', function () {
                const value = $(this).val().toLowerCase();
                const target = $(this).data('target');
                if (target) {
                    $(target + ' tr').filter(function () {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                    });
                }
            });
        }
    }

    function initModalFocus() {
        if ($) {
            $('.modal').on('shown.bs.modal', function () {
                const firstInput = $(this).find('input:first');
                if (firstInput.length) {
                    firstInput.trigger('focus');
                }
                initSelect2(this);
            });
        }
    }

    function initPasswordToggle() {
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        if (!togglePassword || !password) return;

        togglePassword.addEventListener('click', function () {
            const icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    }

    function initCoursesPage() {
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                const checkboxes = document.querySelectorAll('.course-checkbox');
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = this.checked;
                });
            });
        }

        document.querySelectorAll('[data-course-edit]').forEach((button) => {
            button.addEventListener('click', () => {
                const dataset = button.dataset;
                document.getElementById('editCourseId').value = dataset.id || '';
                document.getElementById('editCourseGroupName').value = dataset.groupName || '';
                document.getElementById('editCourseSubject').value = dataset.subjectId || '';
                document.getElementById('editCourseTerm').value = dataset.termId || '';
                document.getElementById('editCourseStatus').value = dataset.status || 'draft';
                document.getElementById('editCourseSchedule').value = dataset.scheduleLabel || '';
                document.getElementById('editCourseCapacity').value = dataset.capacity || '';
                document.getElementById('editCourseModality').value = dataset.modality || '';

                const modalElement = document.getElementById('editCourseModal');
                if (modalElement && window.bootstrap) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });
        });

        document.querySelectorAll('[data-course-delete]').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById('deleteCourseId').value = button.dataset.id || '';
                document.getElementById('deleteCourseName').textContent = button.dataset.name || '';

                const modalElement = document.getElementById('deleteCourseModal');
                if (modalElement && window.bootstrap) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });
        });
    }

    function initSubjectsPage() {
        const tableView = document.getElementById('tableViewContainer');
        const cardView = document.getElementById('cardViewContainer');
        const tableViewToggle = document.getElementById('tableView');
        const cardViewToggle = document.getElementById('cardView');

        if (tableViewToggle && cardViewToggle && tableView && cardView) {
            tableViewToggle.addEventListener('change', function () {
                if (this.checked) {
                    tableView.style.display = 'block';
                    cardView.style.display = 'none';
                }
            });

            cardViewToggle.addEventListener('change', function () {
                if (this.checked) {
                    tableView.style.display = 'none';
                    cardView.style.display = 'block';
                }
            });
        }

        const selectAllSubjects = document.getElementById('selectAllSubjects');
        if (selectAllSubjects) {
            selectAllSubjects.addEventListener('change', function () {
                const checkboxes = document.querySelectorAll('.subject-checkbox');
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = this.checked;
                });
            });
        }

        document.querySelectorAll('[data-subject-edit]').forEach((button) => {
            button.addEventListener('click', () => {
                const dataset = button.dataset;
                document.getElementById('editSubjectId').value = dataset.id || '';
                document.getElementById('editSubjectName').value = dataset.name || '';
                document.getElementById('editSubjectCode').value = dataset.code || '';
                document.getElementById('editSubjectModule').value = dataset.moduleId || '';
                document.getElementById('editSubjectDescription').value = dataset.description || '';

                const modalElement = document.getElementById('editSubjectModal');
                if (modalElement && window.bootstrap) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });
        });

        document.querySelectorAll('[data-subject-delete]').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById('deleteSubjectId').value = button.dataset.id || '';
                document.getElementById('deleteSubjectName').textContent = button.dataset.name || '';
                const modalElement = document.getElementById('deleteSubjectModal');
                if (modalElement && window.bootstrap) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });
        });
    }

    function initPeriodsPage() {
        document.querySelectorAll('[data-period-edit]').forEach((button) => {
            button.addEventListener('click', () => {
                const dataset = button.dataset;
                document.getElementById('editPeriodId').value = dataset.id || '';
                document.getElementById('editPeriodName').value = dataset.name || '';
                document.getElementById('editPeriodCode').value = dataset.code || '';
                document.getElementById('editInscriptionStart').value = dataset.enrollmentStart || '';
                document.getElementById('editInscriptionEnd').value = dataset.enrollmentEnd || '';
                document.getElementById('editTermStart').value = dataset.startDate || '';
                document.getElementById('editTermEnd').value = dataset.endDate || '';
                document.getElementById('editPeriodStatus').value = dataset.status || 'inactive';
                const modalElement = document.getElementById('editPeriodModal');
                if (modalElement && window.bootstrap) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });
        });

        document.querySelectorAll('[data-period-delete]').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById('deletePeriodId').value = button.dataset.id || '';
                document.getElementById('deletePeriodName').textContent = button.dataset.name || '';
                const modalElement = document.getElementById('deletePeriodModal');
                if (modalElement && window.bootstrap) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });
        });
    }

    function initDashboardSidebar() {
        const toggleSidebar = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        if (toggleSidebar && sidebar && mainContent) {
            toggleSidebar.addEventListener('click', function () {
                sidebar.classList.toggle('show');
                mainContent.classList.toggle('expanded');
            });

            document.addEventListener('click', function (event) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(event.target) && !toggleSidebar.contains(event.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('show');
                    mainContent.classList.remove('expanded');
                }
            });

            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach((link) => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        }
    }

    function initAlertsAutoHide() {
        setTimeout(function () {
            document.querySelectorAll('.alert').forEach((alert) => {
                if (alert.classList.contains('show')) {
                    alert.classList.remove('show');
                }
            });
        }, 5000);
    }

    document.addEventListener('DOMContentLoaded', function () {
        hideLoading();
        initTooltips();
        initFormValidation();
        initAjaxForms();
        initDeleteButtons();
        initSearchInputs();
        initModalFocus();
        initPasswordToggle();
        initCoursesPage();
        initSubjectsPage();
        initPeriodsPage();
        initDashboardSidebar();
        initAlertsAutoHide();
        initSelect2();
    });

    window.ChristianLMS = {
        showLoading,
        hideLoading,
        showAlert,
        confirmDelete,
        formatDate,
        formatCurrency,
        formatNumber,
        initSelect2
    };
})();
