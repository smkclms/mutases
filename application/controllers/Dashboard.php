<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->database();
  }

  public function index() {
    $data['title'] = 'Dashboard';

    // ==========================================================
    // 🏫 JUMLAH ROMBEL PER TINGKAT
    // ==========================================================
    $data['rombel'] = $this->get_kelas_by_tingkat();

    // ==========================================================
    // 👨‍🎓 SISWA AKTIF PER TINGKAT
    // ==========================================================
    $data['aktif'] = $this->get_siswa_by_tingkat('aktif');

    // ==========================================================
    // 🚪 SISWA KELUAR PER TINGKAT
    // ==========================================================
    $data['keluar'] = $this->get_siswa_by_tingkat(['mutasi_keluar', 'keluar']);

    // ==========================================================
    // 🎓 SISWA LULUS PER TAHUN AJARAN
    // ==========================================================
    $query = $this->db
      ->select('tahun_ajaran.tahun, COUNT(siswa.id) AS jumlah')
      ->join('tahun_ajaran', 'tahun_ajaran.id = siswa.tahun_id', 'left')
      ->where('siswa.status', 'lulus')
      ->group_by('tahun_ajaran.tahun')
      ->order_by('tahun_ajaran.tahun', 'ASC')
      ->get('siswa');

    $data['lulus'] = $query ? $query->result() : [];

    // ==========================================================
    // 🧮 JUMLAH SISWA PER ROMBEL (UNTUK TABEL PUBLIK)
    // ==========================================================
    $data['per_rombel'] = $this->get_siswa_per_rombel();

    // ==========================================================
    // TAMPILKAN SESUAI LOGIN
    // ==========================================================
    if ($this->session->userdata('logged_in')) {
      // versi admin
      $data['active'] = 'dashboard';
      $this->load->view('templates/header', $data);
      $this->load->view('templates/sidebar', $data);
      $this->load->view('dashboard/index', $data);
      $this->load->view('templates/footer');
    } else {
      // versi publik (tanpa sidebar, tanpa header login)
      $this->load->view('dashboard/public', $data);
    }
  }

  // ==========================================================
  // 🔹 JUMLAH KELAS PER TINGKAT
  // ==========================================================
  private function get_kelas_by_tingkat() {
    $result = [];
    $this->db->where("(nama REGEXP '(^X($|[^I])|^10)')");
    $result['x'] = $this->db->count_all_results('kelas');
    $this->db->where("(nama REGEXP '(^XI($|[^I])|^11)')");
    $result['xi'] = $this->db->count_all_results('kelas');
    $this->db->where("(nama REGEXP '(^XII|^12)')");
    $result['xii'] = $this->db->count_all_results('kelas');
    $result['total'] = $result['x'] + $result['xi'] + $result['xii'];
    return $result;
  }

  // ==========================================================
  // 🔹 JUMLAH SISWA PER TINGKAT
  // ==========================================================
  private function get_siswa_by_tingkat($status) {
    $result = [];

    // Kelas X
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
    if (is_array($status)) $this->db->where_in('siswa.status', $status);
    else $this->db->where('siswa.status', $status);
    $this->db->where("(kelas.nama REGEXP '(^X($|[^I])|^10)')");
    $result['x'] = $this->db->count_all_results('siswa', TRUE);

    // Kelas XI
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
    if (is_array($status)) $this->db->where_in('siswa.status', $status);
    else $this->db->where('siswa.status', $status);
    $this->db->where("(kelas.nama REGEXP '(^XI($|[^I])|^11)')");
    $result['xi'] = $this->db->count_all_results('siswa', TRUE);

    // Kelas XII
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');
    if (is_array($status)) $this->db->where_in('siswa.status', $status);
    else $this->db->where('siswa.status', $status);
    $this->db->where("(kelas.nama REGEXP '(^XII|^12)')");
    $result['xii'] = $this->db->count_all_results('siswa', TRUE);

    $result['total'] = $result['x'] + $result['xi'] + $result['xii'];
    return $result;
  }

  // ==========================================================
  // 🔹 JUMLAH SISWA PER ROMBEL (UNTUK PUBLIK)
  // ==========================================================
  private function get_siswa_per_rombel() {
    $query = $this->db
      ->select("kelas.nama AS nama_kelas,
                SUM(CASE WHEN siswa.jk = 'L' THEN 1 ELSE 0 END) AS laki,
                SUM(CASE WHEN siswa.jk = 'P' THEN 1 ELSE 0 END) AS perempuan,
                COUNT(siswa.id) AS total")
      ->join('kelas', 'kelas.id = siswa.id_kelas', 'left')
      ->where('siswa.status', 'aktif')
      ->group_by('kelas.nama')
      ->order_by('kelas.nama', 'ASC')
      ->get('siswa');

    return $query ? $query->result() : [];
  }
  public function download_excel($kelas_id = null)
{
    if (!$kelas_id) show_error('Kelas tidak ditemukan.');

    $kelas = $this->db->get_where('kelas', ['id' => $kelas_id])->row();
    if (!$kelas) show_error('Data kelas tidak valid.');
// 🔹 Tambahkan hitungan download
    $this->db->set('download_count', 'download_count + 1', FALSE)
             ->where('id', $kelas_id)
             ->update('kelas');

    // Ambil data siswa aktif dari kelas ini
    $siswa = $this->db
        ->where('id_kelas', $kelas_id)
        ->where('status', 'aktif')
        ->order_by('nama', 'ASC')
        ->get('siswa')
        ->result();

    if (empty($siswa)) {
        show_error('Tidak ada data siswa aktif di kelas ini.');
    }

    // Load library PHPExcel dan panggil fungsi ekspor
    $this->load->library('PHPExcel_lib');
    $this->phpexcel_lib->export_siswa_per_kelas($siswa, $kelas->nama);
}

// ==========================================================
// 🔹 HALAMAN PUBLIK: SISWA MUTASI
// ==========================================================
public function mutasi()
{
    $this->load->model('Laporan_model');
    $this->load->library(['pagination']);

    // ambil filter dari URL (GET)
    $tahun  = date('Y');
    $kelas  = $this->input->get('kelas');
    $jenis  = $this->input->get('jenis');
    $search = $this->input->get('search');

    // pagination config
    $config['base_url'] = site_url('dashboard/mutasi');
    $config['per_page'] = 10;
    $config['page_query_string'] = TRUE;
    $config['query_string_segment'] = 'page';
    $config['reuse_query_string'] = TRUE;
    $config['full_tag_open'] = '<ul class="pagination justify-content-center">';
    $config['full_tag_close'] = '</ul>';
    $config['attributes'] = ['class' => 'page-link'];
    $config['first_link'] = '«';
    $config['last_link'] = '»';
    $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
    $config['cur_tag_close'] = '</a></li>';
    $config['num_tag_open'] = '<li class="page-item">';
    $config['num_tag_close'] = '</li>';

    $page = ($this->input->get('page')) ? (int)$this->input->get('page') : 0;
    $offset = $page;

    // ambil semua data dulu buat hitung total
    $all_mutasi = $this->Laporan_model->get_laporan($tahun, $kelas, $jenis, $search);
    $config['total_rows'] = count($all_mutasi);
    $this->pagination->initialize($config);

    // ambil data paginated
    $data['mutasi'] = array_slice($all_mutasi, $offset, $config['per_page']);

    $data['judul'] = 'Data Siswa Mutasi';
    $data['tahun'] = $tahun;
    $data['kelas_list'] = $this->Laporan_model->get_kelas();
    $data['pagination'] = $this->pagination->create_links();

    $this->load->view('dashboard/mutasi_public', $data);
}



}
