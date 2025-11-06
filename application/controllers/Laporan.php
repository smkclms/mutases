<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan extends CI_Controller {

  public function __construct(){
    parent::__construct();
    $this->load->model('Laporan_model');
    $this->load->library('pdf'); // library TCPDF
    $this->load->library('PHPExcel_lib'); // untuk export Excel
  }

  public function index() {
    $tahun_aktif = date('Y');
    $kelas  = $this->input->get('kelas');
    $jenis  = $this->input->get('jenis');
    $search = $this->input->get('search');

    $data['judul'] = 'Laporan Mutasi Siswa';
    $data['active'] = 'laporan';
    $data['tahun']  = $tahun_aktif;
    $data['kelas_list'] = $this->Laporan_model->get_kelas();
    $data['mutasi'] = $this->Laporan_model->get_laporan($tahun_aktif, $kelas, $jenis, $search);

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('laporan/index', $data);
    $this->load->view('templates/footer');
  }

  // ==========================================================
  // 🔹 EXPORT PDF
  // ==========================================================
  public function export_pdf()
  {
      $tahun_id = $this->session->userdata('tahun_id');
      $tahun_row = $this->db->get_where('tahun_ajaran', ['id' => $tahun_id])->row();
      $tahun = isset($tahun_row->tahun) ? $tahun_row->tahun : date('Y');

      $kelas  = $this->input->get('kelas');
      $jenis  = $this->input->get('jenis');
      $search = $this->input->get('search');

      $this->db->from('v_mutasi_detail');
      $this->db->where('tahun_ajaran', $tahun);

      if (!empty($kelas)) $this->db->where('kelas_asal_id', $kelas);
      if (!empty($jenis)) $this->db->where('jenis', strtolower($jenis));
      if (!empty($search)) $this->db->like('nama_siswa', $search);

      $mutasi = $this->db->order_by('tanggal', 'DESC')->get()->result();

      // 🔸 PDF Setup
      $this->load->library('tcpdf');
      $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
      $pdf->SetCreator('Mutases');
      $pdf->SetTitle('Laporan Mutasi Siswa Tahun ' . $tahun);
      $pdf->SetMargins(6, 8, 6);
      $pdf->AddPage('L');
      $pdf->SetFont('helvetica', '', 10);

      // Header Judul
      $pdf->SetFont('helvetica', 'B', 14);
      $pdf->Cell(0, 8, 'LAPORAN MUTASI SISWA TAHUN ' . $tahun, 0, 1, 'C');
      $pdf->Ln(3);

      // Header Tabel
      $pdf->SetFont('helvetica', 'B', 9);
      $pdf->SetFillColor(230, 230, 230);

      $headers = [
          'No', 'Nama Siswa', 'NIS', 'NISN', 'Kelas Asal', 
          'Jenis', 'Jenis Keluar', 'Tanggal', 'Alasan', 
          'No. HP Ortu', 'Tujuan', 'Tahun Ajaran' 
        //   'Dibuat Oleh'
      ];

      // Lebar kolom disesuaikan total 297mm
      $widths = [8, 35, 18, 22, 25, 18, 25, 20, 30, 28, 30, 25, 25];

      foreach ($headers as $i => $header) {
          $pdf->MultiCell($widths[$i], 9, $header, 1, 'C', true, 0);
      }
      $pdf->Ln();

      // Isi Data
      $pdf->SetFont('helvetica', '', 8.5);
      $no = 1;
      if (!empty($mutasi)) {
          foreach ($mutasi as $m) {
              $row = [
                  $no++,
                  $m->nama_siswa,
                  $m->nis,
                  $m->nisn,
                  isset($m->kelas_asal) ? $m->kelas_asal : '-',
                  ucfirst($m->jenis),
                  $m->jenis == 'keluar' ? ($m->jenis_keluar ?: '-') : '-',
                  !empty($m->tanggal) ? date('d-m-Y', strtotime($m->tanggal)) : '-',
                  $m->alasan ?: '-',
                  $m->nohp_ortu ?: '-',
                  $m->jenis == 'keluar' ? ($m->tujuan_sekolah ?: '-') : ($m->kelas_tujuan ?: '-'),
                  $m->tahun_ajaran
                //   isset($m->dibuat_oleh) ? $m->dibuat_oleh : '-'
              ];

              foreach ($row as $i => $cell) {
                  $align = in_array($i, [0, 2, 3, 5, 7, 10, 11]) ? 'C' : 'L';
                  $pdf->MultiCell($widths[$i], 8, $cell, 1, $align, false, 0);
              }
              $pdf->Ln();
          }
      } else {
          $pdf->Cell(array_sum($widths), 10, 'Tidak ada data mutasi ditemukan.', 1, 1, 'C');
      }

      // Footer
      $pdf->Ln(5);
      $pdf->SetFont('helvetica', 'I', 9);
      $pdf->Cell(0, 7, 'Dicetak pada: ' . date('d/m/Y H:i') . ' | Sistem Mutasi Siswa', 0, 1, 'R');

      $pdf->Output('Laporan_Mutasi_' . $tahun . '.pdf', 'I');
  }


  // ==========================================================
  // 🔹 EXPORT EXCEL
  // ==========================================================
  public function export_excel()
  {
      $tahun = $this->input->get('tahun');
      if (empty($tahun)) $tahun = date('Y');

      $kelas  = $this->input->get('kelas');
      $jenis  = $this->input->get('jenis');
      $search = $this->input->get('search');

      $data = $this->Laporan_model->get_laporan($tahun, $kelas, $jenis, $search);

      // 🔹 Buat Excel pakai PHPExcel_lib
      $this->load->library('PHPExcel_lib');
      $this->phpexcel_lib->export_laporan_mutasi($data, $tahun);
  }

}
