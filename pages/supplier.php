<?php
$page_title = 'Supplier';
$active_menu = 'supplier';
require_once __DIR__ . '/../includes/header.php';

$rows = db()->query("
    SELECT s.*, (SELECT COUNT(*) FROM barang b WHERE b.supplier_id = s.id) AS jml_barang
    FROM supplier s ORDER BY s.nama ASC
")->fetchAll();
?>

<div class="page-title-bar fade-in">
    <h4>Supplier</h4>
    <nav class="breadcrumb">
        <a class="breadcrumb-item" href="<?= BASE_URL ?>/pages/dashboard.php">Dashboard</a>
        <span class="breadcrumb-item active">Supplier</span>
    </nav>
</div>

<div class="card">
    <div class="card-header">
        <span><i class="bi bi-truck text-primary me-2"></i>Daftar Supplier</span>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSupplier" onclick="resetForm()">
            <i class="bi bi-plus-lg"></i> Tambah Supplier
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($rows)): ?>
            <div class="empty-state"><?= empty_state_svg('default') ?><h5>Belum ada supplier</h5></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover datatable w-100">
                <thead><tr><th>Nama</th><th>Alamat</th><th>No. Telepon</th><th>Email</th><th>Kontak</th><th>Barang</th><th class="no-print">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><strong><?= e($r['nama']) ?></strong></td>
                        <td><?= e($r['alamat'] ?? '-') ?></td>
                        <td><?= e($r['no_telepon'] ?? '-') ?></td>
                        <td><?= e($r['email'] ?? '-') ?></td>
                        <td><?= e($r['kontak_person'] ?? '-') ?></td>
                        <td><?= format_angka($r['jml_barang']) ?></td>
                        <td class="no-print">
                            <button class="btn-icon-sm success" onclick='editSupplier(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)'><i class="bi bi-pencil"></i></button>
                            <a href="<?= BASE_URL ?>/actions/supplier-delete.php?id=<?= $r['id'] ?>" class="btn-icon-sm danger" onclick="return confirmDelete(this.href, {text:'Supplier: <?= e($r['nama']) ?>'})"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalSupplier" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= BASE_URL ?>/actions/supplier-save.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="fId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama <span class="required">*</span></label>
                            <input type="text" name="nama" id="fNama" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kontak Person</label>
                            <input type="text" name="kontak_person" id="fKontak" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" id="fAlamat" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="no_telepon" id="fTelp" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="fEmail" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extra_js = "
function resetForm() {
    document.getElementById('modalTitle').textContent = 'Tambah Supplier';
    ['fId','fNama','fKontak','fAlamat','fTelp','fEmail'].forEach(function(i){ document.getElementById(i).value=''; });
}
function editSupplier(data) {
    document.getElementById('modalTitle').textContent = 'Edit Supplier';
    document.getElementById('fId').value = data.id;
    document.getElementById('fNama').value = data.nama;
    document.getElementById('fKontak').value = data.kontak_person || '';
    document.getElementById('fAlamat').value = data.alamat || '';
    document.getElementById('fTelp').value = data.no_telepon || '';
    document.getElementById('fEmail').value = data.email || '';
    new bootstrap.Modal(document.getElementById('modalSupplier')).show();
}
";
require_once __DIR__ . '/../includes/footer.php';
?>
