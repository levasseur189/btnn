<?php
$page_title = 'Laporan';
$active_menu = 'laporan';
require_once __DIR__ . '/../includes/header.php';

$periode = $_GET['periode'] ?? 'bulanan';
$tipe = $_GET['tipe'] ?? 'all';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

$where_masuk = ['1=1'];
$where_keluar = ['1=1'];
$params_m = [];
$params_k = [];

if ($periode === 'harian') {
    $where_masuk[] = 'tanggal = ?'; $params_m[] = $tanggal;
    $where_keluar[] = 'tanggal = ?'; $params_k[] = $tanggal;
} elseif ($periode === 'mingguan') {
    $where_masuk[] = 'tanggal >= ?'; $params_m[] = date('Y-m-d', strtotime('monday this week'));
    $where_masuk[] = 'tanggal <= ?'; $params_m[] = date('Y-m-d', strtotime('sunday this week'));
    $where_keluar[] = 'tanggal >= ?'; $params_k[] = date('Y-m-d', strtotime('monday this week'));
    $where_keluar[] = 'tanggal <= ?'; $params_k[] = date('Y-m-d', strtotime('sunday this week'));
} elseif ($periode === 'bulanan') {
    $where_masuk[] = 'MONTH(tanggal) = ?'; $params_m[] = date('m');
    $where_masuk[] = 'YEAR(tanggal) = ?'; $params_m[] = date('Y');
    $where_keluar[] = 'MONTH(tanggal) = ?'; $params_k[] = date('m');
    $where_keluar[] = 'YEAR(tanggal) = ?'; $params_k[] = date('Y');
} elseif ($periode === 'tahunan') {
    $where_masuk[] = 'YEAR(tanggal) = ?'; $params_m[] = date('Y');
    $where_keluar[] = 'YEAR(tanggal) = ?'; $params_k[] = date('Y');
} elseif ($periode === 'custom') {
    $where_masuk[] = 'tanggal >= ?'; $params_m[] = $dari;
    $where_masuk[] = 'tanggal <= ?'; $params_m[] = $sampai;
    $where_keluar[] = 'tanggal >= ?'; $params_k[] = $dari;
    $where_keluar[] = 'tanggal <= ?'; $params_k[] = $sampai;
}

$masuk = [];
$keluar = [];
if ($tipe === 'all' || $tipe === 'masuk') {
    $sql = "SELECT bm.*, b.kode, b.nama, b.satuan, s.nama AS supplier_nama, ad.nama AS admin_nama
            FROM barang_masuk bm JOIN barang b ON b.id=bm.barang_id
            LEFT JOIN supplier s ON s.id=bm.supplier_id JOIN admin ad ON ad.id=bm.admin_id
            WHERE " . implode(' AND ', $where_masuk) . " ORDER BY bm.tanggal DESC";
    $stmt = db()->prepare($sql); $stmt->execute($params_m); $masuk = $stmt->fetchAll();
}
if ($tipe === 'all' || $tipe === 'keluar') {
    $sql = "SELECT bk.*, b.kode, b.nama, b.satuan, ad.nama AS admin_nama
            FROM barang_keluar bk JOIN barang b ON b.id=bk.barang_id JOIN admin ad ON ad.id=bk.admin_id
            WHERE " . implode(' AND ', $where_keluar) . " ORDER BY bk.tanggal DESC";
    $stmt = db()->prepare($sql); $stmt->execute($params_k); $keluar = $stmt->fetchAll();
}

$total_masuk = array_sum(array_column($masuk, 'jumlah'));
$total_keluar = array_sum(array_column($keluar, 'jumlah'));
$qstring = http_build_query(['periode' => $periode, 'tipe' => $tipe, 'tanggal' => $tanggal, 'dari' => $dari, 'sampai' => $sampai]);
?>

<div class="page-title-bar fade-in">
    <h4>Laporan Transaksi</h4>
    <nav class="breadcrumb">
        <a class="breadcrumb-item" href="<?= BASE_URL ?>/pages/dashboard.php">Dashboard</a>
        <span class="breadcrumb-item active">Laporan</span>
    </nav>
</div>

<div class="card">
    <div class="card-header"><span><i class="bi bi-funnel text-primary me-2"></i>Filter Laporan</span></div>
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Periode</label>
                <select name="periode" class="form-select" id="periodeSelect">
                    <option value="harian" <?= $periode === 'harian' ? 'selected' : '' ?>>Harian</option>
                    <option value="mingguan" <?= $periode === 'mingguan' ? 'selected' : '' ?>>Mingguan</option>
                    <option value="bulanan" <?= $periode === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                    <option value="tahunan" <?= $periode === 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
                    <option value="custom" <?= $periode === 'custom' ? 'selected' : '' ?>>Custom</option>
                </select>
            </div>
            <div class="col-md-3" id="tglField" <?= $periode !== 'harian' ? 'style="display:none"' : '' ?>>
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="<?= e($tanggal) ?>">
            </div>
            <div class="col-md-3" id="dariField" <?= $periode !== 'custom' ? 'style="display:none"' : '' ?>>
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="dari" class="form-control" value="<?= e($dari) ?>">
            </div>
            <div class="col-md-3" id="sampaiField" <?= $periode !== 'custom' ? 'style="display:none"' : '' ?>>
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control" value="<?= e($sampai) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Jenis Transaksi</label>
                <select name="tipe" class="form-select">
                    <option value="all" <?= $tipe === 'all' ? 'selected' : '' ?>>Semua</option>
                    <option value="masuk" <?= $tipe === 'masuk' ? 'selected' : '' ?>>Barang Masuk</option>
                    <option value="keluar" <?= $tipe === 'keluar' ? 'selected' : '' ?>>Barang Keluar</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-box-arrow-in-down"></i></div>
            <div class="stat-info"><div class="stat-value"><?= format_angka($total_masuk) ?></div><div class="stat-label">Total Barang Masuk</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon danger"><i class="bi bi-box-arrow-right"></i></div>
            <div class="stat-info"><div class="stat-value"><?= format_angka($total_keluar) ?></div><div class="stat-label">Total Barang Keluar</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-database"></i></div>
            <div class="stat-info"><div class="stat-value"><?= format_angka($total_masuk - $total_keluar) ?></div><div class="stat-label">Net Transaksi</div></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span><i class="bi bi-bar-chart-line text-primary me-2"></i>Hasil Laporan</span>
        <div class="d-flex gap-2 no-print">
            <button class="btn btn-success btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print</button>
            <a href="<?= BASE_URL ?>/export/export-laporan.php?<?= $qstring ?>&format=excel" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i> Excel</a>
            <a href="<?= BASE_URL ?>/export/export-laporan.php?<?= $qstring ?>&format=pdf" target="_blank" class="btn btn-danger btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</a>
        </div>
    </div>
    <div class="card-body">
        <?php if ($tipe === 'all' || $tipe === 'masuk'): ?>
        <h6 class="text-success mb-3"><i class="bi bi-box-arrow-in-down me-1"></i>Barang Masuk</h6>
        <?php if (empty($masuk)): ?>
            <div class="empty-state"><i class="bi bi-inbox"></i><h5>Tidak ada data</h5></div>
        <?php else: ?>
        <div class="table-responsive mb-4">
            <table class="table table-hover w-100">
                <thead><tr><th>Tanggal</th><th>Kode</th><th>Barang</th><th>Jumlah</th><th>Supplier</th><th>Invoice</th></tr></thead>
                <tbody>
                <?php foreach ($masuk as $r): ?>
                    <tr><td><?= format_tgl_singkat($r['tanggal']) ?></td><td><?= e($r['kode']) ?></td><td><?= e($r['nama']) ?></td><td class="text-success">+<?= format_angka($r['jumlah']) ?></td><td><?= e($r['supplier_nama'] ?? '-') ?></td><td><?= e($r['nomor_invoice'] ?? '-') ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($tipe === 'all' || $tipe === 'keluar'): ?>
        <h6 class="text-danger mb-3"><i class="bi bi-box-arrow-right me-1"></i>Barang Keluar</h6>
        <?php if (empty($keluar)): ?>
            <div class="empty-state"><i class="bi bi-inbox"></i><h5>Tidak ada data</h5></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover w-100">
                <thead><tr><th>Tanggal</th><th>Kode</th><th>Barang</th><th>Jumlah</th><th>Tujuan</th></tr></thead>
                <tbody>
                <?php foreach ($keluar as $r): ?>
                    <tr><td><?= format_tgl_singkat($r['tanggal']) ?></td><td><?= e($r['kode']) ?></td><td><?= e($r['nama']) ?></td><td class="text-danger">-<?= format_angka($r['jumlah']) ?></td><td><?= e($r['tujuan'] ?? '-') ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$extra_js = "
var sel = document.getElementById('periodeSelect');
sel.addEventListener('change', function() {
    var v = sel.value;
    document.getElementById('tglField').style.display = v === 'harian' ? '' : 'none';
    document.getElementById('dariField').style.display = v === 'custom' ? '' : 'none';
    document.getElementById('sampaiField').style.display = v === 'custom' ? '' : 'none';
});
";
require_once __DIR__ . '/../includes/footer.php';
?>
