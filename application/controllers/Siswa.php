<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Siswa extends CI_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->model('Siswa_model');
    $this->load->library(['form_validation', 'pagination', 'PHPExcel_lib']);
    $this->load->helper(['url', 'form']);
  }

  public function index($offset = 0)
{
    $this->load->library('pagination');

    // 🔹 Ambil parameter filter dan pencarian dari GET
    $kelas_id = $this->input->get('kelas');
    $search   = $this->input->get('search');
    $limit    = $this->input->get('limit') ?: 10;

    // 🔹 Hitung total baris untuk pagination
    $this->db->from('siswa');
    $this->db->where('status', 'aktif');
    if (!empty($kelas_id)) {
        $this->db->where('id_kelas', $kelas_id);
    }
    if (!empty($search)) {
        $this->db->group_start()
                 ->like('nama', $search)
                 ->or_like('nis', $search)
                 ->or_like('nisn', $search)
                 ->group_end();
    }
    $config['total_rows'] = $this->db->count_all_results();

    // 🔹 Konfigurasi pagination
    $config['base_url'] = site_url('siswa/index');
    $config['per_page'] = $limit;
    $config['uri_segment'] = 3;
    $config['reuse_query_string'] = true;

    // 💅 Bootstrap 5 pagination style
$config['full_tag_open']   = '<nav><ul class="pagination pagination-sm justify-content-center my-3">';
$config['full_tag_close']  = '</ul></nav>';
$config['attributes']      = ['class' => 'page-link'];

$config['first_link']      = '<i class="fas fa-angle-double-left"></i>';
$config['first_tag_open']  = '<li class="page-item">';
$config['first_tag_close'] = '</li>';

$config['last_link']       = '<i class="fas fa-angle-double-right"></i>';
$config['last_tag_open']   = '<li class="page-item">';
$config['last_tag_close']  = '</li>';

$config['next_link']       = '<i class="fas fa-angle-right"></i>';
$config['next_tag_open']   = '<li class="page-item">';
$config['next_tag_close']  = '</li>';

$config['prev_link']       = '<i class="fas fa-angle-left"></i>';
$config['prev_tag_open']   = '<li class="page-item">';
$config['prev_tag_close']  = '</li>';

$config['cur_tag_open']    = '<li class="page-item active"><a class="page-link bg-primary border-primary text-white" href="#">';
$config['cur_tag_close']   = '</a></li>';

$config['num_tag_open']    = '<li class="page-item">';
$config['num_tag_close']   = '</li>';

$config['reuse_query_string'] = true;

    $this->pagination->initialize($config);

    // 🔹 Query utama siswa (dengan join dan filter)
    $this->db->select('siswa.*, kelas.nama AS nama_kelas, tahun_ajaran.tahun AS tahun_ajaran');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
    $this->db->join('tahun_ajaran', 'tahun_ajaran.id = siswa.tahun_id', 'left');
    $this->db->where('siswa.status', 'aktif');
    if (!empty($kelas_id)) {
        $this->db->where('siswa.id_kelas', $kelas_id);
    }
    if (!empty($search)) {
        $this->db->group_start()
                 ->like('siswa.nama', $search)
                 ->or_like('siswa.nis', $search)
                 ->or_like('siswa.nisn', $search)
                 ->group_end();
    }
    $this->db->order_by('siswa.id', 'DESC');
    $data['siswa'] = $this->db->get('siswa', $limit, $offset)->result();

    // 🔹 Data tambahan untuk tampilan
    $data['title'] = 'Data Siswa Aktif';
    $data['active'] = 'siswa';
    $data['pagination'] = $this->pagination->create_links();
    $data['kelas'] = $this->Siswa_model->get_kelas_list();
    $data['tahun'] = $this->Siswa_model->get_tahun_list();
    $data['start'] = $offset;
    $data['kelas_id'] = $kelas_id;
    $data['search'] = $search;
    $data['limit'] = $limit;

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('siswa/index', $data);
    $this->load->view('templates/footer');
}


  // ========================================
  // TAMBAH SISWA
  // ========================================
  public function add() {
  if ($this->input->post()) {
    $nisn = $this->input->post('nisn', TRUE);

    // 🔹 Cek apakah NISN sudah ada
    $cek = $this->db->get_where('siswa', ['nisn' => $nisn])->row();

    if ($cek) {
      $this->session->set_flashdata('error', 
        'NISN <strong>' . $nisn . '</strong> sudah terdaftar atas nama <strong>' . $cek->nama . '</strong>.');
      redirect('siswa');
      return;
    }

    // 🔹 Data baru
    $data = [
      'nis'          => $this->input->post('nis', TRUE),
      'nisn'         => $nisn,
      'nama'         => $this->input->post('nama', TRUE),
      'jk'           => $this->input->post('jk', TRUE),
      'agama'        => $this->input->post('agama', TRUE),
      'tempat_lahir' => $this->input->post('tempat_lahir', TRUE),
      'tgl_lahir'    => $this->input->post('tgl_lahir', TRUE),
      'alamat'       => $this->input->post('alamat', TRUE),
      'id_kelas'     => $this->input->post('id_kelas', TRUE),
      'tahun_id'     => $this->input->post('tahun_id', TRUE),
      'status'       => 'aktif'
    ];

    $this->Siswa_model->insert($data);
    $this->session->set_flashdata('success', 'Data siswa berhasil ditambahkan.');
    redirect('siswa');
  }
}

  // ========================================
  // EDIT SISWA
  // ========================================
  public function edit($id) {
    if ($this->input->post()) {
      $data = [
        'nis' => $this->input->post('nis', TRUE),
        'nisn' => $this->input->post('nisn', TRUE),
        'nama' => $this->input->post('nama', TRUE),
        'jk' => $this->input->post('jk', TRUE),
        'agama' => $this->input->post('agama', TRUE),
        'tempat_lahir' => $this->input->post('tempat_lahir', TRUE),
        'tgl_lahir' => $this->input->post('tgl_lahir', TRUE),
        'alamat' => $this->input->post('alamat', TRUE),
        'id_kelas' => $this->input->post('id_kelas', TRUE),
        'tahun_id' => $this->input->post('tahun_id', TRUE),
        'status' => $this->input->post('status', TRUE)
      ];

      $this->Siswa_model->update($id, $data);
      $this->session->set_flashdata('success', 'Data siswa berhasil diperbarui.');
      redirect('siswa');
    } else {
      $data['siswa'] = $this->Siswa_model->get_by_id($id);
      $data['kelas'] = $this->Siswa_model->get_kelas_list();
      $data['tahun'] = $this->Siswa_model->get_tahun_list();
      $data['title'] = 'Edit Siswa';
      $data['active'] = 'siswa';
      $this->load->view('templates/header', $data);
      $this->load->view('templates/sidebar', $data);
      $this->load->view('siswa/edit', $data);
      $this->load->view('templates/footer');
    }
  }

  // ========================================
  // HAPUS SISWA (cek mutasi dulu)
  // ========================================
  public function delete($id) {
    $ada_mutasi = $this->db->where('siswa_id', $id)->count_all_results('mutasi');
    if ($ada_mutasi > 0) {
      $this->session->set_flashdata('error', 'Siswa ini sudah memiliki data mutasi dan tidak dapat dihapus.');
    } else {
      $this->Siswa_model->delete($id);
      $this->session->set_flashdata('success', 'Data siswa berhasil dihapus.');
    }
    redirect('siswa');
  }

  // EXPORT EXCEL
  public function export_excel() {
    $data = $this->Siswa_model->get_all(10000, 0);
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0)
      ->setCellValue('A1', 'No')
      ->setCellValue('B1', 'NIS')
      ->setCellValue('C1', 'Nama')
      ->setCellValue('D1', 'Tempat Lahir')
      ->setCellValue('E1', 'Tanggal Lahir')
      ->setCellValue('F1', 'Alamat')
      ->setCellValue('G1', 'Kelas')
      ->setCellValue('H1', 'Tahun Ajaran')
      ->setCellValue('I1', 'Status');

    $no = 1; $row = 2;
    foreach ($data as $s) {
      $objPHPExcel->getActiveSheet()
        ->setCellValue("A$row", $no++)
        ->setCellValue("B$row", $s->nis)
        ->setCellValue("C$row", $s->nama)
        ->setCellValue("D$row", $s->tempat_lahir)
        ->setCellValue("E$row", $s->tgl_lahir)
        ->setCellValue("F$row", $s->alamat)
        ->setCellValue("G$row", $s->nama_kelas)
        ->setCellValue("H$row", $s->tahun_ajaran)
        ->setCellValue("I$row", ucfirst($s->status));
      $row++;
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="data_siswa.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
  }
public function download_template() {
    $kelas = $this->db->select('nama')->get('kelas')->result_array();
    $tahun = $this->db->select('tahun')->get('tahun_ajaran')->result_array();
    $status = ['aktif', 'mutasi_keluar', 'mutasi_masuk', 'lulus', 'keluar'];
    $agama_list = ['Islam','Kristen','Katolik','Hindu','Budha','Konghucu'];

    $objPHPExcel = new PHPExcel();
    $sheet = $objPHPExcel->setActiveSheetIndex(0);
    $sheet->setTitle('Template Siswa');

    // === HEADER BARU (dengan NISN di kolom B) ===
    $sheet->setCellValue('A1', 'NIS')
          ->setCellValue('B1', 'NISN')
          ->setCellValue('C1', 'Nama')
          ->setCellValue('D1', 'JK (L/P)')
          ->setCellValue('E1', 'Agama')
          ->setCellValue('F1', 'Tempat Lahir')
          ->setCellValue('G1', 'Tanggal Lahir (YYYY-MM-DD)')
          ->setCellValue('H1', 'Alamat')
          ->setCellValue('I1', 'Kelas')
          ->setCellValue('J1', 'Tahun Ajaran')
          ->setCellValue('K1', 'Status');

    // === SHEET REFERENSI ===
    $objPHPExcel->createSheet();
    $refSheet = $objPHPExcel->setActiveSheetIndex(1);
    $refSheet->setTitle('Referensi');

    // Data referensi
    $rowKelas = 1;
    foreach ($kelas as $k) {
        $refSheet->setCellValue("A$rowKelas", $k['nama']);
        $rowKelas++;
    }

    $rowTahun = 1;
    foreach ($tahun as $t) {
        $refSheet->setCellValue("B$rowTahun", $t['tahun']);
        $rowTahun++;
    }

    $rowStatus = 1;
    foreach ($status as $s) {
        $refSheet->setCellValue("C$rowStatus", $s);
        $rowStatus++;
    }

    $rowAgama = 1;
    foreach ($agama_list as $a) {
        $refSheet->setCellValue("D$rowAgama", $a);
        $rowAgama++;
    }

    // Kembali ke sheet utama
    $sheet = $objPHPExcel->setActiveSheetIndex(0);

    // === Ranges referensi ===
    $kelasRange  = 'Referensi!$A$1:$A$' . count($kelas);
    $tahunRange  = 'Referensi!$B$1:$B$' . count($tahun);
    $statusRange = 'Referensi!$C$1:$C$' . count($status);
    $agamaRange  = 'Referensi!$D$1:$D$' . count($agama_list);

    // === Dropdown: JK, Agama, Kelas, Tahun, Status ===
    for ($i = 2; $i <= 100; $i++) {
        // JK
        $validJK = $sheet->getCell("D$i")->getDataValidation();
        $validJK->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $validJK->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
        $validJK->setAllowBlank(true);
        $validJK->setShowDropDown(true);
        $validJK->setFormula1('"L,P"');
        $sheet->getCell("D$i")->setDataValidation($validJK);

        // Agama
        $validAgama = $sheet->getCell("E$i")->getDataValidation();
        $validAgama->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $validAgama->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
        $validAgama->setAllowBlank(true);
        $validAgama->setShowDropDown(true);
        $validAgama->setFormula1($agamaRange);
        $sheet->getCell("E$i")->setDataValidation($validAgama);

        // Kelas
        $validKelas = $sheet->getCell("I$i")->getDataValidation();
        $validKelas->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $validKelas->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
        $validKelas->setAllowBlank(true);
        $validKelas->setShowDropDown(true);
        $validKelas->setFormula1($kelasRange);
        $sheet->getCell("I$i")->setDataValidation($validKelas);

        // Tahun
        $validTahun = $sheet->getCell("J$i")->getDataValidation();
        $validTahun->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $validTahun->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
        $validTahun->setAllowBlank(true);
        $validTahun->setShowDropDown(true);
        $validTahun->setFormula1($tahunRange);
        $sheet->getCell("J$i")->setDataValidation($validTahun);

        // Status
        $validStatus = $sheet->getCell("K$i")->getDataValidation();
        $validStatus->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $validStatus->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
        $validStatus->setAllowBlank(true);
        $validStatus->setShowDropDown(true);
        $validStatus->setFormula1($statusRange);
        $sheet->getCell("K$i")->setDataValidation($validStatus);
    }

    // === Auto width semua kolom ===
    foreach (range('A','K') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Set aktif ke sheet utama
    $objPHPExcel->setActiveSheetIndex(0);

    // Output file
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="template_siswa.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
}


  // IMPORT EXCEL
  public function import_excel() {
    if (isset($_FILES['file']['name'])) {
        $path = $_FILES['file']['tmp_name'];
        $objPHPExcel = PHPExcel_IOFactory::load($path);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

        $gagal = [];
        $berhasil_update = 0;
        $berhasil_insert = 0;

        foreach ($sheetData as $key => $row) {
            if ($key == 1) continue; // skip header

            // --- Kolom sesuai urutan template baru ---
            $nis        = trim($row['A']);
            $nisn       = trim($row['B']);
            $nama       = trim($row['C']);
            $jk         = strtoupper(trim($row['D']));
            $agama      = trim($row['E']);
            $tempat     = trim($row['F']);
            $tgl_lahir  = trim($row['G']);
            $alamat     = trim($row['H']);
            $kelas_nama = trim(strtolower($row['I']));
            $tahun_nama = trim($row['J']);
            $status     = strtolower(trim($row['K'])) ?: 'aktif';

            // --- Validasi wajib minimal NISN dan Nama ---
            if (empty($nisn) || empty($nama)) {
                $gagal[] = "Baris $key: NISN dan Nama wajib diisi.";
                continue;
            }

            // --- Cari kelas dan tahun ajaran berdasarkan nama ---
            $kelas = $this->db->where('LOWER(nama)', $kelas_nama)->get('kelas')->row();
            $tahun = $this->db->where('tahun', $tahun_nama)->get('tahun_ajaran')->row();

            if (!$kelas || !$tahun) {
                $gagal[] = "Baris $key: Kelas atau Tahun Ajaran tidak valid ($kelas_nama / $tahun_nama)";
                continue;
            }

            // --- Cek apakah NISN sudah ada ---
            $existing = $this->db->get_where('siswa', ['nisn' => $nisn])->row();

            $data = [
                'nis'          => $nis,
                'nisn'         => $nisn,
                'nama'         => $nama,
                'jk'           => in_array($jk, ['L','P']) ? $jk : 'L',
                'agama'        => $agama,
                'tempat_lahir' => $tempat,
                'tgl_lahir'    => $tgl_lahir,
                'alamat'       => $alamat,
                'id_kelas'     => $kelas->id,
                'tahun_id'     => $tahun->id,
                'status'       => $status
            ];

            if ($existing) {
                // Jika siswa dengan NISN ini sudah ada → update datanya
                $this->db->where('nisn', $nisn)->update('siswa', $data);
                $berhasil_update++;
            } else {
                // Jika belum ada → insert baru
                $this->db->insert('siswa', $data);
                $berhasil_insert++;
            }
        }

        // --- Flash message hasil import ---
        if (!empty($gagal)) {
            $msg = "<b>Import selesai dengan beberapa catatan:</b><br>";
            $msg .= "🟢 Insert baru: <strong>$berhasil_insert</strong><br>";
            $msg .= "🟡 Update data lama: <strong>$berhasil_update</strong><br>";
            $msg .= "<br><b>Gagal:</b><ul>";
            foreach ($gagal as $g) $msg .= "<li>$g</li>";
            $msg .= "</ul>";
            $this->session->set_flashdata('error', $msg);
        } else {
            $this->session->set_flashdata('success', 
                "Import selesai. Insert baru: <b>$berhasil_insert</b>, update data lama: <b>$berhasil_update</b>."
            );
        }
    }

    redirect('siswa');
}


}
