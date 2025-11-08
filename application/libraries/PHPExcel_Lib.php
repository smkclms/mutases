<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

// pastikan file PHPExcel utama tersedia di third_party
require_once APPPATH . "third_party/PHPExcel/Classes/PHPExcel.php";

class PHPExcel_lib
{
    public function __construct()
    {
        // biarkan kosong, cukup load otomatis PHPExcel di atas
    }

    public function export_laporan_mutasi($data, $tahun)
    {
        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('Laporan Mutasi Siswa');

        // Header kolom baru
$headers = [
    'No', 'Nama Siswa', 'NIS', 'NISN', 'Kelas Asal', 'Jenis', 'Jenis Keluar',
    'Tanggal', 'Alasan', 'No. HP Ortu', 'Tujuan', 'Tahun Ajaran', 'Dibuat Oleh'
];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col.'1', $h);
    $col++;
}

// Isi data
$rowNum = 2;
$no = 1;
foreach ($data as $m) {
    $sheet->setCellValue('A'.$rowNum, $no++);
    $sheet->setCellValue('B'.$rowNum, $m->nama_siswa);
    $sheet->setCellValue('C'.$rowNum, $m->nis);
    $sheet->setCellValue('D'.$rowNum, $m->nisn);
    $sheet->setCellValue('E'.$rowNum, $m->kelas_asal);
    $sheet->setCellValue('F'.$rowNum, ucfirst($m->jenis));
    $sheet->setCellValue('G'.$rowNum, $m->jenis == 'keluar' ? ($m->jenis_keluar ?: '-') : '-');
    $sheet->setCellValue('H'.$rowNum, !empty($m->tanggal) ? date('d-m-Y', strtotime($m->tanggal)) : '-');
    $sheet->setCellValue('I'.$rowNum, $m->alasan);
    $sheet->setCellValue('J'.$rowNum, $m->nohp_ortu);
    $sheet->setCellValue('K'.$rowNum, $m->jenis == 'keluar' ? ($m->tujuan_sekolah ?: '-') : ($m->kelas_tujuan ?: '-'));
    $sheet->setCellValue('L'.$rowNum, $m->tahun_ajaran);
    $sheet->setCellValue('M'.$rowNum, $m->dibuat_oleh);
    $rowNum++;
}


        // Styling header
        $headerStyle = array(
            'font' => array('bold' => true),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
        );
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

        // Auto width kolom
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output ke browser (download otomatis)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="laporan_mutasi_' . $tahun . '.xls"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $writer->save('php://output');
        exit();
    }
    public function export_siswa_per_kelas($siswa, $kelas_nama)
{
    $excel = new PHPExcel();
    $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();
    $sheet->setTitle('Siswa ' . $kelas_nama);

    // Header kolom
    $sheet->setCellValue('A1', 'No')
          ->setCellValue('B1', 'NIS')
          ->setCellValue('C1', 'Nama Siswa')
          ->setCellValue('D1', 'Jenis Kelamin')
          ->setCellValue('E1', 'Alamat');

    // Isi data
    $row = 2;
    $no  = 1;
    foreach ($siswa as $s) {
        $sheet->setCellValue('A' . $row, $no++)
              ->setCellValue('B' . $row, $s->nis)
              ->setCellValue('C' . $row, $s->nama)
              ->setCellValue('D' . $row, $s->jk)
              ->setCellValue('E' . $row, $s->alamat);
        $row++;
    }

    // Styling header
    $headerStyle = array(
        'font' => array('bold' => true),
        'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
        'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
    );
    $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

    // Auto width kolom
    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Output ke browser (download otomatis)
    $filename = 'Daftar_Siswa_' . str_replace(' ', '_', $kelas_nama) . '.xls';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
    $writer->save('php://output');
    exit();
}

}
