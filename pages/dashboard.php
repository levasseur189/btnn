<?php
$page_title = 'Dashboard';
$active_menu = 'dashboard';
require_once __DIR__ . '/../includes/header.php';

$total_barang = count_total_barang();
$hampir_habis = count_hampir_habis();
$masuk_hari_ini = count_masuk_hari_ini();
$keluar_hari_ini = count_keluar_hari_ini();

// Grafik 7 hari terakhir
$labels = [];
$masuk_data = [];
$keluar_data = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $labels[] = date('d/m', strtotime($d));
    $stmt = db()->prepare('SELECT COALESCE(SUM(jumlah),0) FROM barang_masuk WHERE tanggal = ?');
    $stmt->execute([$d]);
    $masuk_data[] = (int)$stmt->fetchColumn();
    $stmt = db()->prepare('SELECT COALESCE(SUM(jumlah),0) FROM barang_keluar WHERE tanggal = ?');
    $stmt->execute([$d]);
    $keluar_data[] = (int)$stmt->fetchColumn();
}

// Barang per kategori
$kat_labels = [];
$kat_data = [];
$kat_rows = db()->query("
    SELECT k.nama, COUNT(b.id) AS jml
    FROM kategori k LEFT JOIN barang b ON b.kategori_id = k.id
    GROUP BY k.id ORDER BY jml DESC
")->fetchAll();
foreach ($kat_rows as $r) {
    $kat_labels[] = $r['nama'];
    $kat_data[] = (int)$r['jml'];
}

// Aktivitas terbaru
$aktivitas = db()->query("
    SELECT a.*, ad.nama AS admin_nama
    FROM aktivitas a JOIN admin ad ON ad.id = a.admin_id
    ORDER BY a.created_at DESC LIMIT 6
")->fetchAll();

// Barang hampir habis
$menipis = db()->query("
    SELECT kode, nama, stok, minimal_stok, satuan
    FROM barang WHERE stok <= minimal_stok ORDER BY stok ASC LIMIT 6
")->fetchAll();
?>

<div class="page-title-bar fade-in">
    <h4>Selamat datang, <?= e($admin['nama']) ?>!</h4>
    <nav class="breadcrumb">
        <span class="breadcrumb-item active">Dashboard</span>
    </nav>
</div>

<div class="position-relative">
    <button class="btn btn-soft btn-sm dashboard-customize-btn" id="customizeDashboardBtn">
        <i class="bi bi-sliders me-1"></i> Atur Kartu
    </button>
    <div id="customizePanel" class="card mb-3 d-none">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <span class="small text-muted fw-semibold">Tampilkan Kartu:</span>
                <div class="form-check form-switch"><input class="form-check-input dash-card-toggle" type="checkbox" data-card="statTotal" id="cbStatTotal" checked><label class="form-check-label small" for="cbStatTotal">Total Barang</label></div>
                <div class="form-check form-switch"><input class="form-check-input dash-card-toggle" type="checkbox" data-card="statMenipis" id="cbStatMenipis" checked><label class="form-check-label small" for="cbStatMenipis">Hampir Habis</label></div>
                <div class="form-check form-switch"><input class="form-check-input dash-card-toggle" type="checkbox" data-card="statMasuk" id="cbStatMasuk" checked><label class="form-check-label small" for="cbStatMasuk">Masuk Hari Ini</label></div>
                <div class="form-check form-switch"><input class="form-check-input dash-card-toggle" type="checkbox" data-card="statKeluar" id="cbStatKeluar" checked><label class="form-check-label small" for="cbStatKeluar">Keluar Hari Ini</label></div>
                <div class="form-check form-switch"><input class="form-check-input dash-card-toggle" type="checkbox" data-card="chartMasuk" id="cbChartMasuk" checked><label class="form-check-label small" for="cbChartMasuk">Grafik Masuk</label></div>
                <div class="form-check form-switch"><input class="form-check-input dash-card-toggle" type="checkbox" data-card="chartKeluar" id="cbChartKeluar" checked><label class="form-check-label small" for="cbChartKeluar">Grafik Keluar</label></div>
                <div class="form-check form-switch"><input class="form-check-input dash-card-toggle" type="checkbox" data-card="chartKategori" id="cbChartKategori" checked><label class="form-check-label small" for="cbChartKategori">Grafik Kategori</label></div>
                <div class="form-check form-switch"><input class="form-check-input dash-card-toggle" type="checkbox" data-card="cardAktivitas" id="cbCardAktivitas" checked><label class="form-check-label small" for="cbCardAktivitas">Aktivitas Terbaru</label></div>
                <div class="form-check form-switch"><input class="form-check-input dash-card-toggle" type="checkbox" data-card="cardMenipis" id="cbCardMenipis" checked><label class="form-check-label small" for="cbCardMenipis">Barang Menipis</label></div>
            </div>
        </div>
    </div>

<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card" id="statTotal">
            <div class="stat-icon primary"><i class="bi bi-box-seam"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= format_angka($total_barang) ?></div>
                <div class="stat-label">Total Barang</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card" id="statMenipis">
            <div class="stat-icon warning"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= format_angka($hampir_habis) ?></div>
                <div class="stat-label">Barang Hampir Habis</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card" id="statMasuk">
            <div class="stat-icon success"><i class="bi bi-box-arrow-in-down"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= format_angka($masuk_hari_ini) ?></div>
                <div class="stat-label">Barang Masuk Hari Ini</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card" id="statKeluar">
            <div class="stat-icon danger"><i class="bi bi-box-arrow-right"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= format_angka($keluar_hari_ini) ?></div>
                <div class="stat-label">Barang Keluar Hari Ini</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card" id="chartMasuk">
            <div class="card-header">
                <span><i class="bi bi-graph-up text-primary me-2"></i>Grafik Barang Masuk (7 Hari)</span>
            </div>
            <div class="card-body">
                <div class="chart-container"><canvas id="chartMasukCanvas"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card" id="chartKeluar">
            <div class="card-header">
                <span><i class="bi bi-graph-down-arrow text-danger me-2"></i>Grafik Barang Keluar (7 Hari)</span>
            </div>
            <div class="card-body">
                <div class="chart-container"><canvas id="chartKeluarCanvas"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card" id="chartKategori">
            <div class="card-header">
                <span><i class="bi bi-pie-chart text-primary me-2"></i>Jumlah Barang per Kategori</span>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:300px"><canvas id="chartKategoriCanvas"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12 col-lg-6">
        <div class="card" id="cardAktivitas">
            <div class="card-header">
                <span><i class="bi bi-clock-history text-primary me-2"></i>Aktivitas Terbaru</span>
            </div>
            <div class="card-body">
                <?php if (empty($aktivitas)): ?>
                    <div class="empty-state"><?= empty_state_svg('clock') ?><h5>Belum ada aktivitas</h5></div>
                <?php else: ?>
                    <?php foreach ($aktivitas as $a): ?>
                        <div class="activity-item">
                            <div class="activity-dot bg-primary-light text-primary"><i class="bi bi-activity"></i></div>
                            <div class="activity-content">
                                <div class="activity-text"><strong><?= e($a['admin_nama']) ?></strong> - <?= e($a['aksi']) ?> <?= e($a['modul']) ?></div>
                                <div class="activity-time"><?= e($a['keterangan']) ?> - <?= format_datetime($a['created_at']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card" id="cardMenipis">
            <div class="card-header">
                <span><i class="bi bi-exclamation-triangle text-warning me-2"></i>Daftar Barang Hampir Habis</span>
                <a href="<?= BASE_URL ?>/pages/barang.php?filter=menipis" class="btn btn-sm btn-soft">Lihat Semua</a>
            </div>
            <div class="card-body">
                <?php if (empty($menipis)): ?>
                    <div class="empty-state"><?= empty_state_svg('success') ?><h5>Semua stok aman</h5></div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Kode</th><th>Nama</th><th>Stok</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($menipis as $b): $st = status_stok($b['stok'], $b['minimal_stok']); ?>
                                <tr>
                                    <td><?= e($b['kode']) ?></td>
                                    <td><?= e($b['nama']) ?></td>
                                    <td><?= format_angka($b['stok']) ?> <?= e($b['satuan']) ?></td>
                                    <td><?= status_badge($st) ?></td>
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
</div>

<?php
$extra_js = "
var labels = " . json_encode($labels) . ";
var masukData = " . json_encode($masuk_data) . ";
var keluarData = " . json_encode($keluar_data) . ";
var katLabels = " . json_encode($kat_labels) . ";
var katData = " . json_encode($kat_data) . ";

new Chart(document.getElementById('chartMasukCanvas'), {
    type: 'line',
    data: { labels: labels, datasets: [{ label: 'Masuk', data: masukData, borderColor: '#22C55E', backgroundColor: 'rgba(34,197,94,.15)', fill: true, tension: .35 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});
new Chart(document.getElementById('chartKeluarCanvas'), {
    type: 'line',
    data: { labels: labels, datasets: [{ label: 'Keluar', data: keluarData, borderColor: '#EF4444', backgroundColor: 'rgba(239,68,68,.15)', fill: true, tension: .35 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});
new Chart(document.getElementById('chartKategoriCanvas'), {
    type: 'bar',
    data: { labels: katLabels, datasets: [{ label: 'Jumlah Barang', data: katData, backgroundColor: '#0066B3', borderRadius: 8 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
";
require_once __DIR__ . '/../includes/footer.php';
?>
