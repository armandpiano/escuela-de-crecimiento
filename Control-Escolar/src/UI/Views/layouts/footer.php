    </main>

    <?php if ($isAuthenticated ?? false): ?>
            </div>
        </div>
    <?php else: ?>
        <footer class="app-footer py-4 mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="bi bi-mortarboard me-2"></i>Escuela de Crecimiento</h5>
                        <p class="text-muted mb-0">
                            Sistema de Gestión Educativa moderno y eficiente.
                            Diseñado para mejorar la experiencia educativa.
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="mb-2">
                            <a href="#" class="text-light text-decoration-none me-3">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="text-light text-decoration-none me-3">
                                <i class="bi bi-twitter-x"></i>
                            </a>
                            <a href="#" class="text-light text-decoration-none me-3">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="#" class="text-light text-decoration-none">
                                <i class="bi bi-linkedin"></i>
                            </a>
                        </div>
                        <p class="text-muted mb-0">
                            © <?= date('Y') ?> Escuela de Crecimiento. Todos los derechos reservados.
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= htmlspecialchars($basePath) ?>/assets/js/ui-premium.js"></script>
</body>
</html>
