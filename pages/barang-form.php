<?php
$page_title = 'Form Barang';
$active_menu = 'barang';
require_once __DIR__ . '/../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$barang = null;
if ($id) {
    $stmt = db()->prepare('SELECT * FROM barang WHERE id = ?');
    $stmt->execute([$id]);
    $barang = $stmt->fetch();
    if (!$barang) { set_flash('error', 'Barang tidak ditemukan.'); redirect('pages/barang.php'); }
}

$kategoris = db()->query('SELECT * FROM kategori ORDER BY nama ASC')->fetchAll();
$suppliers = db()->query('SELECT * FROM supplier ORDER BY nama ASC')->fetchAll();
$kode_otomatis = generate_kode('BRG-', 'barang', 'kode');
clear_old();
?>

<div class="page-title-bar fade-in">
    <h4><?= $id ? 'Edit Barang' : 'Tambah Barang' ?></h4>
    <nav class="breadcrumb">
        <a class="breadcrumb-item" href="<?= BASE_URL ?>/pages/dashboard.php">Dashboard</a>
        <a class="breadcrumb-item" href="<?= BASE_URL ?>/pages/barang.php">Data Barang</a>
        <span class="breadcrumb-item active"><?= $id ? 'Edit' : 'Tambah' ?></span>
    </nav>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><span><i class="bi bi-box text-primary me-2"></i><?= $id ? 'Edit Data Barang' : 'Tambah Data Barang' ?></span></div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/actions/barang-save.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode Barang <span class="required">*</span></label>
                            <input type="text" name="kode" class="form-control" value="<?= e($barang['kode'] ?? $kode_otomatis) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Barang <span class="required">*</span></label>
                            <input type="text" name="nama" class="form-control" value="<?= e($barang['nama'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori <span class="required">*</span></label>
                            <select name="kategori_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategoris as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= ($barang['kategori_id'] ?? '') == $k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" class="form-select">
                                <option value="">-- Pilih Supplier --</option>
                                <?php foreach ($suppliers as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= ($barang['supplier_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= e($s['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lokasi Rak</label>
                            <input type="text" name="lokasi_rak" class="form-control" value="<?= e($barang['lokasi_rak'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Satuan <span class="required">*</span></label>
                            <input type="text" name="satuan" class="form-control" value="<?= e($barang['satuan'] ?? 'pcs') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Minimal Stok <span class="required">*</span></label>
                            <input type="number" name="minimal_stok" class="form-control" value="<?= e($barang['minimal_stok'] ?? 5) ?>" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stok Awal</label>
                            <?php if ($id): ?>
                                <input type="number" name="stok" class="form-control" value="<?= e($barang['stok']) ?>" readonly>
                                <div class="form-text">Stok diubah melalui modul Barang Masuk/Keluar.</div>
                            <?php else: ?>
                                <input type="number" name="stok" class="form-control" value="0" min="0">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Foto Barang</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <?php if (!empty($barang['foto'])): ?>
                                <div class="mt-2"><img src="<?= UPLOAD_URL ?>/<?= e($barang['foto']) ?>" class="barang-thumb" alt=""></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= e($barang['deskripsi'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
                        <a href="<?= BASE_URL ?>/pages/barang.php" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header"><span><i class="bi bi-qr-code text-primary me-2"></i>QR Code</span></div>
            <div class="card-body text-center">
                <div id="qrcode" class="qr-print-area mx-auto"></div>
                <p class="text-muted small mt-2">QR otomatis dibuat dari kode barang. Pindai untuk membuka detail.</p>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = "
var kode = " . json_encode($barang['kode'] ?? $kode_otomatis) . ";
new QRCode(document.getElementById('qrcode'), {
    text: '" . BASE_URL . "/pages/detail-barang.php?kode=' + encodeURIComponent(kode),
    width: 160, height: 160
});
";
require_once __DIR__ . '/../includes/footer.php';
?>
