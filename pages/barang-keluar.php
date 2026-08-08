<?php
$page_title = 'Barang Keluar';
$active_menu = 'keluar';
require_once __DIR__ . '/../includes/header.php';

$rows = db()->query("
    SELECT bk.*, b.nama AS barang_nama, b.kode AS barang_kode, b.satuan, ad.nama AS admin_nama
    FROM barang_keluar bk
    JOIN barang b ON b.id = bk.barang_id
    JOIN admin ad ON ad.id = bk.admin_id
    ORDER BY bk.tanggal DESC, bk.id DESC
")->fetchAll();

$barangs = db()->query('SELECT id, kode, nama, satuan, stok FROM barang ORDER BY nama ASC')->fetchAll();
clear_old();
?>

<div class="page-title-bar fade-in">
    <h4>Barang Keluar</h4>
    <nav class="breadcrumb">
        <a class="breadcrumb-item" href="<?= BASE_URL ?>/pages/dashboard.php">Dashboard</a>
        <span class="breadcrumb-item active">Barang Keluar</span>
    </nav>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header"><span><i class="bi bi-box-arrow-right text-danger me-2"></i>Form Barang Keluar</span></div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/actions/barang-keluar-save.php" method="post">
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="required">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Barang <span class="required">*</span></label>
                        <select name="barang_id" class="form-select" required id="barangSelect">
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach ($barangs as $b): ?>
                                <option value="<?= $b['id'] ?>" data-stok="<?= $b['stok'] ?>" data-satuan="<?= e($b['satuan']) ?>"><?= e($b['kode']) ?> - <?= e($b['nama']) ?> (Stok: <?= format_angka($b['stok']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text" id="stokInfo">Pilih barang untuk melihat stok tersedia.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah <span class="required">*</span></label>
                        <input type="number" name="jumlah" class="form-control" min="1" required id="jumlahInput">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tujuan Penggunaan <span class="required">*</span></label>
                        <input type="text" name="tujuan" class="form-control" required placeholder="Contoh: Divisi Operasional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100"><i class="bi bi-save me-1"></i> Simpan</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><span><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Barang Keluar</span></div>
            <div class="card-body">
                <?php if (empty($rows)): ?>
                    <div class="empty-state"><?= empty_state_svg('box') ?><h5>Belum ada transaksi keluar</h5></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable w-100">
                        <thead><tr><th>Tanggal</th><th>Kode</th><th>Barang</th><th>Jumlah</th><th>Tujuan</th><th>Admin</th></tr></thead>
                        <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= format_tgl_singkat($r['tanggal']) ?></td>
                                <td><?= e($r['barang_kode']) ?></td>
                                <td><?= e($r['barang_nama']) ?></td>
                                <td><span class="text-danger fw-semibold">-<?= format_angka($r['jumlah']) ?></span> <?= e($r['satuan']) ?></td>
                                <td><?= e($r['tujuan'] ?? '-') ?></td>
                                <td><?= e($r['admin_nama']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = "
var sel = document.getElementById('barangSelect');
var info = document.getElementById('stokInfo');
var jml = document.getElementById('jumlahInput');
sel.addEventListener('change', function() {
    var opt = sel.options[sel.selectedIndex];
    var stok = opt.getAttribute('data-stok');
    var sat = opt.getAttribute('data-satuan');
    if (stok !== null) {
        info.textContent = 'Stok tersedia: ' + stok + ' ' + sat;
        jml.max = stok;
    } else {
        info.textContent = 'Pilih barang untuk melihat stok tersedia.';
    }
});
";
require_once __DIR__ . '/../includes/footer.php';
?>
