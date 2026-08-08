<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
header('Content-Type: text/html; charset=utf-8');

$type = $_GET['type'] ?? '';

if ($type === 'aktivitas') {
    $aktivitas = db()->query("
        SELECT a.*, ad.nama AS admin_nama
        FROM aktivitas a JOIN admin ad ON ad.id = a.admin_id
        ORDER BY a.created_at DESC LIMIT 6
    ")->fetchAll();
    if (empty($aktivitas)): ?>
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
    <?php endif;
} elseif ($type === 'masuk') {
    $rows = db()->query("
        SELECT bm.*, b.nama AS barang_nama, b.kode AS barang_kode, b.satuan, s.nama AS supplier_nama, ad.nama AS admin_nama
        FROM barang_masuk bm
        JOIN barang b ON b.id = bm.barang_id
        LEFT JOIN supplier s ON s.id = bm.supplier_id
        JOIN admin ad ON ad.id = bm.admin_id
        ORDER BY bm.tanggal DESC, bm.id DESC
    ")->fetchAll();
    if (empty($rows)): ?>
        <div class="empty-state"><?= empty_state_svg('box') ?><h5>Belum ada transaksi masuk</h5></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover datatable w-100">
                <thead><tr><th>Tanggal</th><th>Kode</th><th>Barang</th><th>Jumlah</th><th>Supplier</th><th>Invoice</th><th>Admin</th><th class="no-print">Bukti</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= format_tgl_singkat($r['tanggal']) ?></td>
                        <td><?= e($r['barang_kode']) ?></td>
                        <td><?= e($r['barang_nama']) ?></td>
                        <td><span class="text-success fw-semibold">+<?= format_angka($r['jumlah']) ?></span> <?= e($r['satuan']) ?></td>
                        <td><?= e($r['supplier_nama'] ?? '-') ?></td>
                        <td><?= e($r['nomor_invoice'] ?? '-') ?></td>
                        <td><?= e($r['admin_nama']) ?></td>
                        <td class="no-print">
                            <?php if (!empty($r['bukti'])): ?>
                                <a href="<?= UPLOAD_URL ?>/<?= e($r['bukti']) ?>" target="_blank" class="btn-icon-sm"><i class="bi bi-paperclip"></i></a>
                            <?php else: ?>-<?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif;
} elseif ($type === 'keluar') {
    $rows = db()->query("
        SELECT bk.*, b.nama AS barang_nama, b.kode AS barang_kode, b.satuan, ad.nama AS admin_nama
        FROM barang_keluar bk
        JOIN barang b ON b.id = bk.barang_id
        JOIN admin ad ON ad.id = bk.admin_id
        ORDER BY bk.tanggal DESC, bk.id DESC
    ")->fetchAll();
    if (empty($rows)): ?>
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
    <?php endif;
}
