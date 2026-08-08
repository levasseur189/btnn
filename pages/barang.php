<?php
$page_title = 'Data Barang';
$active_menu = 'barang';
require_once __DIR__ . '/../includes/header.php';

$filter = $_GET['filter'] ?? '';
$where = '';
$params = [];
if ($filter === 'menipis') {
    $where = 'WHERE b.stok <= b.minimal_stok';
} elseif ($filter === 'habis') {
    $where = 'WHERE b.stok <= 0';
}

$rows = db()->query("
    SELECT b.*, k.nama AS kategori_nama, s.nama AS supplier_nama
    FROM barang b
    LEFT JOIN kategori k ON k.id = b.kategori_id
    LEFT JOIN supplier s ON s.id = b.supplier_id
    $where
    ORDER BY b.kode ASC
")->fetchAll();

$kategoris = db()->query('SELECT id, nama FROM kategori ORDER BY nama ASC')->fetchAll();
$suppliers = db()->query('SELECT id, nama FROM supplier ORDER BY nama ASC')->fetchAll();
?>

<div class="page-title-bar fade-in">
    <h4>Data Barang</h4>
    <nav class="breadcrumb">
        <a class="breadcrumb-item" href="<?= BASE_URL ?>/pages/dashboard.php">Dashboard</a>
        <span class="breadcrumb-item active">Data Barang</span>
    </nav>
</div>

<div class="card">
    <div class="card-header">
        <span><i class="bi bi-box-seam text-primary me-2"></i>Daftar Barang</span>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/pages/barang-form.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Tambah Barang
            </a>
            <a href="<?= BASE_URL ?>/export/export-barang.php?type=excel" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            <a href="<?= BASE_URL ?>/export/export-barang.php?type=pdf" class="btn btn-danger btn-sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($rows)): ?>
            <div class="empty-state">
                <?= empty_state_svg('box') ?>
                <h5>Belum ada data barang</h5>
                <p>Silakan tambahkan barang baru.</p>
            </div>
        <?php else: ?>
        <!-- Advanced Filter Bar -->
        <div class="row g-2 mb-3 filter-bar no-print">
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1">Kategori</label>
                <select id="filterKategori" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategoris as $k): ?>
                        <option value="<?= e($k['nama']) ?>"><?= e($k['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1">Supplier</label>
                <select id="filterSupplier" class="form-select form-select-sm">
                    <option value="">Semua Supplier</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= e($s['nama']) ?>"><?= e($s['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1">Status Stok</label>
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="aman">Aman</option>
                    <option value="menipis">Menipis</option>
                    <option value="habis">Habis</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex align-items-end">
                <button id="resetFilter" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover w-100" id="tableBarang">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Supplier</th>
                        <th>Lokasi</th>
                        <th>Satuan</th>
                        <th>Stok</th>
                        <th>Min</th>
                        <th>Status</th>
                        <th class="no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $b): $st = status_stok($b['stok'], $b['minimal_stok']); ?>
                    <tr data-status="<?= $st ?>">
                        <td>
                            <?php if (!empty($b['foto'])): ?>
                                <img src="<?= UPLOAD_URL ?>/<?= e($b['foto']) ?>" class="barang-thumb" alt="">
                            <?php else: ?>
                                <div class="barang-thumb d-flex align-items-center justify-content-center"><i class="bi bi-image text-muted"></i></div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= e($b['kode']) ?></strong></td>
                        <td><?= e($b['nama']) ?></td>
                        <td><?= e($b['kategori_nama']) ?></td>
                        <td><?= e($b['supplier_nama'] ?? '-') ?></td>
                        <td><?= e($b['lokasi_rak'] ?? '-') ?></td>
                        <td><?= e($b['satuan']) ?></td>
                        <td><?= format_angka($b['stok']) ?></td>
                        <td><?= format_angka($b['minimal_stok']) ?></td>
                        <td><?= status_badge($st) ?></td>
                        <td class="no-print">
                            <a href="<?= BASE_URL ?>/pages/detail-barang.php?kode=<?= urlencode($b['kode']) ?>" class="btn-icon-sm" title="Detail"><i class="bi bi-eye"></i></a>
                            <a href="<?= BASE_URL ?>/pages/barang-form.php?id=<?= $b['id'] ?>" class="btn-icon-sm success" title="Edit"><i class="bi bi-pencil"></i></a>
                            <a href="<?= BASE_URL ?>/actions/barang-delete.php?id=<?= $b['id'] ?>" class="btn-icon-sm danger" title="Hapus" onclick="return confirmDelete(this.href, {text:'Barang: <?= e($b['nama']) ?>'})"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$extra_js = "
var dtBarang = $('#tableBarang').DataTable({
    columnDefs: [
        { orderable: false, targets: [0, 10] },
        { type: 'num', targets: [7, 8] }
    ],
    order: [[1, 'asc']]
});

$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    var row = dtBarang.row(dataIndex).node();
    var kat = document.getElementById('filterKategori').value;
    var sup = document.getElementById('filterSupplier').value;
    var sts = document.getElementById('filterStatus').value;
    var rowKat = data[3];
    var rowSup = data[4];
    var rowSts = row.getAttribute('data-status');
    var katOk = !kat || rowKat === kat;
    var supOk = !sup || rowSup === sup;
    var stsOk = !sts || rowSts === sts;
    return katOk && supOk && stsOk;
});

function applyBarangFilter() { dtBarang.draw(); }
document.getElementById('filterKategori').addEventListener('change', applyBarangFilter);
document.getElementById('filterSupplier').addEventListener('change', applyBarangFilter);
document.getElementById('filterStatus').addEventListener('change', applyBarangFilter);
document.getElementById('resetFilter').addEventListener('click', function() {
    document.getElementById('filterKategori').value = '';
    document.getElementById('filterSupplier').value = '';
    document.getElementById('filterStatus').value = '';
    dtBarang.search('').draw();
});
";
require_once __DIR__ . '/../includes/footer.php';
?>
