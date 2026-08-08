<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$periode = $_GET['periode'] ?? 'bulanan';
$tipe = $_GET['tipe'] ?? 'all';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$format = $_GET['format'] ?? 'excel';

$where_masuk = ['1=1']; $where_keluar = ['1=1']; $params_m = []; $params_k = [];
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

$masuk = []; $keluar = [];
if ($tipe === 'all' || $tipe === 'masuk') {
    $sql = "SELECT bm.tanggal, b.kode, b.nama, bm.jumlah, s.nama AS supplier_nama, bm.nomor_invoice
            FROM barang_masuk bm JOIN barang b ON b.id=bm.barang_id
            LEFT JOIN supplier s ON s.id=bm.supplier_id WHERE " . implode(' AND ', $where_masuk) . " ORDER BY bm.tanggal DESC";
    $stmt = db()->prepare($sql); $stmt->execute($params_m); $masuk = $stmt->fetchAll();
}
if ($tipe === 'all' || $tipe === 'keluar') {
    $sql = "SELECT bk.tanggal, b.kode, b.nama, bk.jumlah, bk.tujuan
            FROM barang_keluar bk JOIN barang b ON b.id=bk.barang_id WHERE " . implode(' AND ', $where_keluar) . " ORDER BY bk.tanggal DESC";
    $stmt = db()->prepare($sql); $stmt->execute($params_k); $keluar = $stmt->fetchAll();
}

$judul_periode = ['harian'=>'Harian','mingguan'=>'Mingguan','bulanan'=>'Bulanan','tahunan'=>'Tahunan','custom'=>'Custom'];

if ($format === 'pdf') {
    require_once __DIR__ . '/../vendor/autoload.php';
    $dompdf = new \Dompdf\Dompdf();
    ob_start();
    ?>
    <h2 style="text-align:center;color:#0066B3">Inventory Management System - Bank BTN</h2>
    <h4 style="text-align:center">Laporan Transaksi - <?= $judul_periode[$periode] ?></h4>
    <p style="text-align:center">Dicetak: <?= date('d/m/Y H:i') ?></p>
    <?php if ($tipe === 'all' || $tipe === 'masuk'): ?>
    <h3 style="color:#22C55E">Barang Masuk</h3>
    <table border="1" cellspacing="0" cellpadding="5" width="100%" style="font-size:11px">
        <thead style="background:#22C55E;color:#fff"><tr><th>Tanggal</th><th>Kode</th><th>Barang</th><th>Jumlah</th><th>Supplier</th><th>Invoice</th></tr></thead>
        <tbody>
        <?php foreach ($masuk as $r): ?><tr><td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td><td><?= e($r['kode']) ?></td><td><?= e($r['nama']) ?></td><td><?= $r['jumlah'] ?></td><td><?= e($r['supplier_nama'] ?? '-') ?></td><td><?= e($r['nomor_invoice'] ?? '-') ?></td></tr><?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <?php if ($tipe === 'all' || $tipe === 'keluar'): ?>
    <h3 style="color:#EF4444">Barang Keluar</h3>
    <table border="1" cellspacing="0" cellpadding="5" width="100%" style="font-size:11px">
        <thead style="background:#EF4444;color:#fff"><tr><th>Tanggal</th><th>Kode</th><th>Barang</th><th>Jumlah</th><th>Tujuan</th></tr></thead>
        <tbody>
        <?php foreach ($keluar as $r): ?><tr><td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td><td><?= e($r['kode']) ?></td><td><?= e($r['nama']) ?></td><td><?= $r['jumlah'] ?></td><td><?= e($r['tujuan'] ?? '-') ?></td></tr><?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <?php
    $dompdf->loadHtml(ob_get_clean());
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream('laporan-' . $periode . '-' . date('Ymd') . '.pdf', ['Attachment' => false]);
    exit;
} else {
    require_once __DIR__ . '/../vendor/autoload.php';
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Laporan');
    $sheet->mergeCells('A1:F1');
    $sheet->setCellValue('A1', 'Laporan Transaksi IMS BTN - Periode ' . $judul_periode[$periode] . ' - ' . date('d/m/Y'));
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
    $row = 3;
    if ($tipe === 'all' || $tipe === 'masuk') {
        $sheet->setCellValue('A' . $row, 'BARANG MASUK'); $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('22C55E')); $row++;
        $headers = ['Tanggal', 'Kode', 'Barang', 'Jumlah', 'Supplier', 'Invoice'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $row, $h);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('22C55E');
            $sheet->getStyle($col . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $col++;
        }
        $row++;
        foreach ($masuk as $r) {
            $sheet->fromArray([date('d/m/Y', strtotime($r['tanggal'])), $r['kode'], $r['nama'], $r['jumlah'], $r['supplier_nama'] ?? '-', $r['nomor_invoice'] ?? '-'], null, 'A' . $row);
            $row++;
        }
        $row += 2;
    }
    if ($tipe === 'all' || $tipe === 'keluar') {
        $sheet->setCellValue('A' . $row, 'BARANG KELUAR'); $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('EF4444')); $row++;
        $headers = ['Tanggal', 'Kode', 'Barang', 'Jumlah', 'Tujuan'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $row, $h);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('EF4444');
            $sheet->getStyle($col . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $col++;
        }
        $row++;
        foreach ($keluar as $r) {
            $sheet->fromArray([date('d/m/Y', strtotime($r['tanggal'])), $r['kode'], $r['nama'], $r['jumlah'], $r['tujuan'] ?? '-'], null, 'A' . $row);
            $row++;
        }
    }
    foreach (range('A', 'F') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="laporan-' . $periode . '-' . date('Ymd') . '.xlsx"');
    $writer->save('php://output');
    exit;
}
