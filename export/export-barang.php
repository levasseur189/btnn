<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

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
} else {
    // Excel via PhpSpreadsheet
    require_once __DIR__ . '/../vendor/autoload.php';
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Data Barang');
    $headers = ['Kode', 'Nama', 'Kategori', 'Supplier', 'Lokasi', 'Satuan', 'Stok', 'Min Stok', 'Tanggal Dibuat'];
    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue($col . '1', $h);
        $sheet->getStyle($col . '1')->getFont()->setBold(true);
        $sheet->getStyle($col . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('0066B3');
        $sheet->getStyle($col . '1')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $col++;
    }
    $row = 2;
    foreach ($rows as $r) {
        $sheet->fromArray([
            $r['kode'], $r['nama'], $r['kategori'], $r['supplier'] ?? '-', $r['lokasi_rak'] ?? '-',
            $r['satuan'], $r['stok'], $r['minimal_stok'], date('d/m/Y', strtotime($r['created_at']))
        ], null, 'A' . $row);
        $row++;
    }
    foreach (range('A', 'I') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="data-barang-' . date('Ymd') . '.xlsx"');
    $writer->save('php://output');
    exit;
}
