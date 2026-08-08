        </main>
        <footer class="page-footer">
            <div>&copy; <?= date('Y') ?> Bank BTN - <?= APP_NAME ?> v<?= APP_VERSION ?></div>
            <div class="text-muted small">Dibuat untuk Pegawai Gudang Bank BTN</div>
        </footer>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<!-- Custom JS -->
<script src="<?= BASE_URL ?>/js/app.js"></script>
<?php if (!empty($extra_js)): ?>
    <script><?= $extra_js ?></script>
<?php endif; ?>
</body>
</html>
