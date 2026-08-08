<?php
$page_title = 'Kategori';
$active_menu = 'kategori';
require_once __DIR__ . '/../includes/header.php';

$rows = db()->query("
    SELECT k.*, (SELECT COUNT(*) FROM barang b WHERE b.kategori_id = k.id) AS jml_barang
    FROM kategori k ORDER BY k.nama ASC
")->fetchAll();
?>

<div class="page-title-bar fade-in">
    <h4>Kategori Barang</h4>
    <nav class="breadcrumb">
        <a class="breadcrumb-item" href="<?= BASE_URL ?>/pages/dashboard.php">Dashboard</a>
        <span class="breadcrumb-item active">Kategori</span>
    </nav>
</div>

<div class="card">
    <div class="card-header">
        <span><i class="bi bi-tags text-primary me-2"></i>Daftar Kategori</span>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalKategori" onclick="resetForm()">
            <i class="bi bi-plus-lg"></i> Tambah Kategori
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($rows)): ?>
            <div class="empty-state"><?= empty_state_svg('default') ?><h5>Belum ada kategori</h5></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover datatable w-100">
                <thead><tr><th>Kode</th><th>Nama</th><th>Deskripsi</th><th>Jumlah Barang</th><th class="no-print">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><span class="badge bg-light text-dark"><?= e($r['kode']) ?></span></td>
                        <td><?= e($r['nama']) ?></td>
                        <td><?= e($r['deskripsi'] ?? '-') ?></td>
                        <td><?= format_angka($r['jml_barang']) ?></td>
                        <td class="no-print">
                            <button class="btn-icon-sm success" onclick="editKategori(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)"><i class="bi bi-pencil"></i></button>
                            <a href="<?= BASE_URL ?>/actions/kategori-delete.php?id=<?= $r['id'] ?>" class="btn-icon-sm danger" onclick="return confirmDelete(this.href, {text:'Kategori: <?= e($r['nama']) ?>'})"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalKategori" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= BASE_URL ?>/actions/kategori-save.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="fId">
                    <div class="mb-3">
                        <label class="form-label">Kode <span class="required">*</span></label>
                        <input type="text" name="kode" id="fKode" class="form-control" value="<?= e(generate_kode('KAT-', 'kategori', 'kode')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="required">*</span></label>
                        <input type="text" name="nama" id="fNama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="fDeskripsi" class="form-control" rows="2"></textarea>
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
    document.getElementById('modalTitle').textContent = 'Tambah Kategori';
    document.getElementById('fId').value = '';
    document.getElementById('fNama').value = '';
    document.getElementById('fDeskripsi').value = '';
}
function editKategori(data) {
    document.getElementById('modalTitle').textContent = 'Edit Kategori';
    document.getElementById('fId').value = data.id;
    document.getElementById('fKode').value = data.kode;
    document.getElementById('fNama').value = data.nama;
    document.getElementById('fDeskripsi').value = data.deskripsi || '';
    new bootstrap.Modal(document.getElementById('modalKategori')).show();
}
";
require_once __DIR__ . '/../includes/footer.php';
?>
