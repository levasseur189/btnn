<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
}

require_once __DIR__ . '/../vendor/autoload.php';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan');

$thin = ['borderStyle' => Border::BORDER_THIN, 'color' => ['RGB' => 'D1D5DB']];
$borderAll = ['borders' => ['top' => $thin, 'bottom' => $thin, 'left' => $thin, 'right' => $thin]];

// Title row
$lastCol = ($tipe === 'keluar') ? 'E' : 'F';
$sheet->mergeCells('A1:' . $lastCol . '1');
$sheet->setCellValue('A1', 'LAPORAN TRANSAKSI IMS BTN - Periode ' . strtoupper($judul_periode[$periode]));
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new Color('0066B3'));
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension('1')->setRowHeight(28);

// Subtitle
$sheet->mergeCells('A2:' . $lastCol . '2');
$sheet->setCellValue('A2', 'Dicetak: ' . date('d/m/Y H:i:s'));
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new Color('6B7280'));
$sheet->getRowDimension('2')->setRowHeight(20);

$row = 4;

if ($tipe === 'all' || $tipe === 'masuk') {
    // Section header
    $sheet->mergeCells('A' . $row . ':F' . $row);
    $sheet->setCellValue('A' . $row, 'BARANG MASUK');
    $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12)->setColor(new Color('22C55E'));
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getRowDimension($row)->setRowHeight(22);
    $row++;

    // Column headers
    $headers = ['Tanggal', 'Kode', 'Nama Barang', 'Jumlah', 'Supplier', 'Invoice'];
    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue($col . $row, $h);
        $sheet->getStyle($col . $row)->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
        $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('22C55E');
        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($col . $row)->applyFromArray($borderAll);
        $col++;
    }
    $sheet->getRowDimension($row)->setRowHeight(22);
    $row++;

    $dataStart = $row;
    $no = 1;
    foreach ($masuk as $r) {
        $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($r['tanggal'])));
        $sheet->setCellValue('B' . $row, $r['kode']);
        $sheet->setCellValue('C' . $row, $r['nama']);
        $sheet->setCellValue('D' . $row, (int)$r['jumlah']);
        $sheet->setCellValue('E' . $row, $r['supplier_nama'] ?? '-');
        $sheet->setCellValue('F' . $row, $r['nomor_invoice'] ?? '-');

        foreach (range('A', 'F') as $c) $sheet->getStyle($c . $row)->applyFromArray($borderAll);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        if ($no % 2 === 0) {
            foreach (range('A', 'F') as $c)
                $sheet->getStyle($c . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDF4');
        }
        $row++; $no++;
    }

    // Total row
    $sheet->setCellValue('C' . $row, 'TOTAL MASUK');
    $sheet->getStyle('C' . $row)->getFont()->setBold(true);
    $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('D' . $row, array_sum(array_column($masuk, 'jumlah')));
    $sheet->getStyle('D' . $row)->getFont()->setBold(true);
    $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    foreach (range('A', 'F') as $c) {
        $sheet->getStyle($c . $row)->applyFromArray($borderAll);
        $sheet->getStyle($c . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');
    }
    $row += 2;
}

if ($tipe === 'all' || $tipe === 'keluar') {
    $sheet->mergeCells('A' . $row . ':E' . $row);
    $sheet->setCellValue('A' . $row, 'BARANG KELUAR');
    $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12)->setColor(new Color('EF4444'));
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getRowDimension($row)->setRowHeight(22);
    $row++;

    $headers = ['Tanggal', 'Kode', 'Nama Barang', 'Jumlah', 'Tujuan'];
    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue($col . $row, $h);
        $sheet->getStyle($col . $row)->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
        $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EF4444');
        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($col . $row)->applyFromArray($borderAll);
        $col++;
    }
    $sheet->getRowDimension($row)->setRowHeight(22);
    $row++;

    $no = 1;
    foreach ($keluar as $r) {
        $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($r['tanggal'])));
        $sheet->setCellValue('B' . $row, $r['kode']);
        $sheet->setCellValue('C' . $row, $r['nama']);
        $sheet->setCellValue('D' . $row, (int)$r['jumlah']);
        $sheet->setCellValue('E' . $row, $r['tujuan'] ?? '-');

        foreach (range('A', 'E') as $c) $sheet->getStyle($c . $row)->applyFromArray($borderAll);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        if ($no % 2 === 0) {
            foreach (range('A', 'E') as $c)
                $sheet->getStyle($c . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF2F2');
        }
        $row++; $no++;
    }

    // Total row
    $sheet->setCellValue('C' . $row, 'TOTAL KELUAR');
    $sheet->getStyle('C' . $row)->getFont()->setBold(true);
    $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('D' . $row, array_sum(array_column($keluar, 'jumlah')));
    $sheet->getStyle('D' . $row)->getFont()->setBold(true);
    $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    foreach (range('A', 'E') as $c) {
        $sheet->getStyle($c . $row)->applyFromArray($borderAll);
        $sheet->getStyle($c . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2');
    }
}

// Column widths
$widths = ['A' => 14, 'B' => 15, 'C' => 30, 'D' => 12, 'E' => 20, 'F' => 18];
foreach ($widths as $c => $w) $sheet->getColumnDimension($c)->setWidth($w);

$sheet->freezePane('A4');

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="laporan-' . $periode . '-' . date('Ymd') . '.xlsx"');
$writer->save('php://output');
exit;
