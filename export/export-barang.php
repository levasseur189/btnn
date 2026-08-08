<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Border as Bdr;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$type = $_GET['type'] ?? 'excel';
$rows = db()->query("
    SELECT b.kode, b.nama, k.nama AS kategori, s.nama AS supplier, b.lokasi_rak, b.satuan, b.stok, b.minimal_stok, b.created_at
    FROM barang b
    LEFT JOIN kategori k ON k.id = b.kategori_id
    LEFT JOIN supplier s ON s.id = b.supplier_id
    ORDER BY b.kode ASC
")->fetchAll();

if ($type === 'pdf') {
    require_once __DIR__ . '/../vendor/autoload.php';
    $dompdf = new \Dompdf\Dompdf();
    ob_start();
    ?>
    <h2 style="text-align:center;color:#0066B3">Inventory Management System - Bank BTN</h2>
    <h4 style="text-align:center">Laporan Data Barang</h4>
    <p style="text-align:center">Dicetak: <?= date('d/m/Y H:i') ?></p>
    <table border="1" cellspacing="0" cellpadding="6" width="100%" style="font-size:11px">
        <thead style="background:#0066B3;color:#fff">
            <tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Supplier</th><th>Lokasi</th><th>Satuan</th><th>Stok</th><th>Min</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['kode']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['kategori']) ?></td>
                <td><?= e($r['supplier'] ?? '-') ?></td><td><?= e($r['lokasi_rak'] ?? '-') ?></td>
                <td><?= e($r['satuan']) ?></td><td><?= $r['stok'] ?></td><td><?= $r['minimal_stok'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    $html = ob_get_clean();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream('data-barang-' . date('Ymd') . '.pdf', ['Attachment' => false]);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data Barang');

$thin = ['borderStyle' => Border::BORDER_THIN, 'color' => ['RGB' => 'D1D5DB']];
$borderAll = ['borders' => ['top' => $thin, 'bottom' => $thin, 'left' => $thin, 'right' => $thin]];

// Title row
$sheet->mergeCells('A1:I1');
$sheet->setCellValue('A1', 'LAPORAN DATA BARANG - IMS BTN');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new Color('0066B3'));
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension('1')->setRowHeight(28);

// Subtitle row
$sheet->mergeCells('A2:I2');
$sheet->setCellValue('A2', 'Dicetak: ' . date('d/m/Y H:i:s') . '  |  Total: ' . count($rows) . ' barang');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new Color('6B7280'));
$sheet->getRowDimension('2')->setRowHeight(20);

// Header row (row 4)
$headers = ['No', 'Kode', 'Nama', 'Kategori', 'Supplier', 'Lokasi', 'Satuan', 'Stok', 'Min Stok'];
$headerRow = 4;
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . $headerRow, $h);
    $sheet->getStyle($col . $headerRow)->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
    $sheet->getStyle($col . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0066B3');
    $sheet->getStyle($col . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle($col . $headerRow)->applyFromArray($borderAll);
    $col++;
}
$sheet->getRowDimension($headerRow)->setRowHeight(24);

// Data rows
$row = $headerRow + 1;
$no = 1;
foreach ($rows as $r) {
    $sheet->setCellValue('A' . $row, $no);
    $sheet->setCellValue('B' . $row, $r['kode']);
    $sheet->setCellValue('C' . $row, $r['nama']);
    $sheet->setCellValue('D' . $row, $r['kategori'] ?? '-');
    $sheet->setCellValue('E' . $row, $r['supplier'] ?? '-');
    $sheet->setCellValue('F' . $row, $r['lokasi_rak'] ?? '-');
    $sheet->setCellValue('G' . $row, $r['satuan']);
    $sheet->setCellValue('H' . $row, (int)$r['stok']);
    $sheet->setCellValue('I' . $row, (int)$r['minimal_stok']);

    // Apply borders to entire row
    foreach (range('A', 'I') as $c) {
        $sheet->getStyle($c . $row)->applyFromArray($borderAll);
    }
    // Alignment
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(false);

    // Alternating row color
    if ($no % 2 === 0) {
        foreach (range('A', 'I') as $c) {
            $sheet->getStyle($c . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');
        }
    }
    $row++;
    $no++;
}

// Column widths
$widths = ['A' => 6, 'B' => 15, 'C' => 30, 'D' => 18, 'E' => 20, 'F' => 12, 'G' => 10, 'H' => 10, 'I' => 10];
foreach ($widths as $c => $w) $sheet->getColumnDimension($c)->setWidth($w);

// Freeze header
$sheet->freezePane('A5');

// Auto filter
$sheet->setAutoFilter('A4:I' . ($row - 1));

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="data-barang-' . date('Ymd') . '.xlsx"');
$writer->save('php://output');
exit;
