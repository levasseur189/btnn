<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Template Import Barang');

$thin = ['borderStyle' => Border::BORDER_THIN, 'color' => ['RGB' => 'D1D5DB']];
$borderAll = ['borders' => ['top' => $thin, 'bottom' => $thin, 'left' => $thin, 'right' => $thin]];

// Title
$sheet->mergeCells('A1:I1');
$sheet->setCellValue('A1', 'TEMPLATE IMPORT DATA BARANG - IMS BTN');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new Color('0066B3'));
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension('1')->setRowHeight(28);

// Instructions
$sheet->mergeCells('A2:I2');
$sheet->setCellValue('A2', 'Isi data mulai dari baris 4. Kode & Nama wajib diisi. Kategori & Supplier harus sesuai data di sistem.');
$sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new Color('6B7280'));
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension('2')->setRowHeight(20);

// Headers (row 3)
$headers = ['Kode', 'Nama', 'Kategori', 'Supplier', 'Lokasi Rak', 'Satuan', 'Stok', 'Min Stok', 'Deskripsi'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '3', $h);
    $sheet->getStyle($col . '3')->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
    $sheet->getStyle($col . '3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0066B3');
    $sheet->getStyle($col . '3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle($col . '3')->applyFromArray($borderAll);
    $col++;
}
$sheet->getRowDimension('3')->setRowHeight(24);

// Sample rows
$sample = [
    ['BRG-0001', 'Kertas A4 70 GSM', 'ATK', 'PT Office Supply', 'Rak A-01', 'rim', 120, 20, 'Kertas HVS A4 70 GSM'],
    ['BRG-0002', 'Pulpen Standard AE7', 'ATK', 'PT Office Supply', 'Rak A-02', 'pcs', 15, 30, 'Pulpen tinta biru'],
    ['BRG-0003', 'Tinta Printer HP 680', 'Elektronik', 'CV Mitra Elektronik', 'Rak B-01', 'pcs', 0, 5, 'Cartridge printer HP'],
];
$row = 4;
foreach ($sample as $s) {
    $col = 'A';
    foreach ($s as $val) {
        $sheet->setCellValue($col . $row, $val);
        $sheet->getStyle($col . $row)->applyFromArray($borderAll);
        $col++;
    }
    foreach (range('A', 'I') as $c) {
        $sheet->getStyle($c . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');
    }
    $row++;
}

// Column widths
$widths = ['A' => 15, 'B' => 30, 'C' => 18, 'D' => 20, 'E' => 12, 'F' => 10, 'G' => 10, 'H' => 10, 'I' => 30];
foreach ($widths as $c => $w) $sheet->getColumnDimension($c)->setWidth($w);

$sheet->freezePane('A4');

// Second sheet: available kategori & supplier reference
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Referensi');

$sheet2->setCellValue('A1', 'Daftar Kategori');
$sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(12)->setColor(new Color('0066B3'));
$sheet2->setCellValue('A2', 'Nama');
$sheet2->setCellValue('B2', 'Kode');
$sheet2->getStyle('A2:B2')->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
$sheet2->getStyle('A2:B2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0066B3');
$sheet2->getStyle('A2:B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$kategoris = db()->query('SELECT nama, kode FROM kategori ORDER BY nama ASC')->fetchAll();
$r = 3;
foreach ($kategoris as $k) {
    $sheet2->setCellValue('A' . $r, $k['nama']);
    $sheet2->setCellValue('B' . $r, $k['kode']);
    $r++;
}

$sheet2->setCellValue('D1', 'Daftar Supplier');
$sheet2->getStyle('D1')->getFont()->setBold(true)->setSize(12)->setColor(new Color('0066B3'));
$sheet2->setCellValue('D2', 'Nama');
$sheet2->getStyle('D2')->getFont()->setBold(true)->setColor(new Color('FFFFFF'));
$sheet2->getStyle('D2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('22C55E');
$sheet2->getStyle('D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$suppliers = db()->query('SELECT nama FROM supplier ORDER BY nama ASC')->fetchAll();
$r = 3;
foreach ($suppliers as $s) {
    $sheet2->setCellValue('D' . $r, $s['nama']);
    $r++;
}

$sheet2->getColumnDimension('A')->setWidth(25);
$sheet2->getColumnDimension('B')->setWidth(15);
$sheet2->getColumnDimension('D')->setWidth(30);

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="template-import-barang.xlsx"');
$writer->save('php://output');
exit;
