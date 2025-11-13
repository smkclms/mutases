<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

  public function __construct() {
    parent::__construct();

    $this->load->library('session');
    $this->load->database();

    // ambil tahun ajaran dari session jika ada
    $session_tahun = $this->session->userdata('tahun_id');

    if ($session_tahun) {
        $this->tahun_id = $session_tahun;
    } else {
        // public (tanpa login) -> pakai tahun ajaran aktif (kolom 'aktif')
        $tahun_aktif = $this->db->get_where('tahun_ajaran', ['aktif' => 1])->row();
        $this->tahun_id = $tahun_aktif ? $tahun_aktif->id : null;
    }
}

// private function get_tahun_aktif()
// {
//     return $this->db->get_where('tahun_ajaran', ['is_active' => 1])->row();
// }


private function get_siswa_keluar_by_tingkat()
{
    $tahun = $this->tahun_id;

    $result = [
        'x' => 0,
        'xi' => 0,
        'xii' => 0,
        'total' => 0
    ];

    // ===============================
    // KELAS X
    // ===============================
    $this->db->select("COUNT(m.id) AS jml");
    $this->db->from("mutasi m");
    $this->db->join("siswa_tahun st", "st.siswa_id = m.siswa_id AND st.tahun_id = m.tahun_id", "left");
    $this->db->join("kelas k", "k.id = st.kelas_id", "left");
    $this->db->where("m.jenis", "keluar");
    $this->db->where("m.status_mutasi", "aktif");
    $this->db->where("m.tahun_id", $tahun);
    $this->db->where("(k.nama REGEXP '(^X($|[^I])|^10)')");
    $result['x'] = $this->db->get()->row()->jml;

    // ===============================
    // KELAS XI
    // ===============================
    $this->db->select("COUNT(m.id) AS jml");
    $this->db->from("mutasi m");
    $this->db->join("siswa_tahun st", "st.siswa_id = m.siswa_id AND st.tahun_id = m.tahun_id", "left");
    $this->db->join("kelas k", "k.id = st.kelas_id", "left");
    $this->db->where("m.jenis", "keluar");
    $this->db->where("m.status_mutasi", "aktif");
    $this->db->where("m.tahun_id", $tahun);
    $this->db->where("(k.nama REGEXP '(^XI($|[^I])|^11)')");
    $result['xi'] = $this->db->get()->row()->jml;

    // ===============================
    // KELAS XII
    // ===============================
    $this->db->select("COUNT(m.id) AS jml");
    $this->db->from("mutasi m");
    $this->db->join("siswa_tahun st", "st.siswa_id = m.siswa_id AND st.tahun_id = m.tahun_id", "left");
    $this->db->join("kelas k", "k.id = st.kelas_id", "left");
    $this->db->where("m.jenis", "keluar");
    $this->db->where("m.status_mutasi", "aktif");
    $this->db->where("m.tahun_id", $tahun);
    $this->db->where("(k.nama REGEXP '(^XII|^12)')");
    $result['xii'] = $this->db->get()->row()->jml;

    // TOTAL
    $result['total'] = $result['x'] + $result['xi'] + $result['xii'];

    return $result;
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
    $data['keluar'] = $this->get_siswa_keluar_by_tingkat();


   // ==========================================================
// 🎓 SISWA LULUS PER TAHUN AJARAN
// ==========================================================
$query = $this->db
    ->select('tahun_ajaran.tahun, COUNT(siswa.id) AS jumlah')
    ->join('tahun_ajaran', 'tahun_ajaran.id = siswa.tahun_id', 'left')
    ->where('siswa.status', 'lulus')
    ->where('siswa.tahun_id', $this->tahun_id)   // ← gunakan tahun ajaran login
    ->group_by('tahun_ajaran.tahun')
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
  private function get_siswa_by_tingkat($status)
{
    $tahun = $this->tahun_id;
    $result = [];

    // ============================
    // KELAS X
    // ============================
    $this->db->select('COUNT(st.id) AS jumlah');
    $this->db->from('siswa_tahun st');
    $this->db->join('kelas k', 'k.id = st.kelas_id', 'left');
    $this->db->join('siswa s', 's.id = st.siswa_id', 'left');

    $this->db->where('st.tahun_id', $tahun);
    if (is_array($status))
        $this->db->where_in('st.status', $status);
    else
        $this->db->where('st.status', $status);

    $this->db->where("(k.nama REGEXP '(^X($|[^I])|^10)')");
    $result['x'] = $this->db->get()->row()->jumlah;

    // ============================
    // KELAS XI
    // ============================
    $this->db->select('COUNT(st.id) AS jumlah');
    $this->db->from('siswa_tahun st');
    $this->db->join('kelas k', 'k.id = st.kelas_id', 'left');
    $this->db->join('siswa s', 's.id = st.siswa_id', 'left');

    $this->db->where('st.tahun_id', $tahun);
    if (is_array($status))
        $this->db->where_in('st.status', $status);
    else
        $this->db->where('st.status', $status);

    $this->db->where("(k.nama REGEXP '(^XI($|[^I])|^11)')");
    $result['xi'] = $this->db->get()->row()->jumlah;

    // ============================
    // KELAS XII
    // ============================
    $this->db->select('COUNT(st.id) AS jumlah');
    $this->db->from('siswa_tahun st');
    $this->db->join('kelas k', 'k.id = st.kelas_id', 'left');
    $this->db->join('siswa s', 's.id = st.siswa_id', 'left');

    $this->db->where('st.tahun_id', $tahun);
    if (is_array($status))
        $this->db->where_in('st.status', $status);
    else
        $this->db->where('st.status', $status);

    $this->db->where("(k.nama REGEXP '(^XII|^12)')");
    $result['xii'] = $this->db->get()->row()->jumlah;

    $result['total'] = $result['x'] + $result['xi'] + $result['xii'];

    return $result;
}



  // ==========================================================
  // 🔹 JUMLAH SISWA PER ROMBEL (UNTUK PUBLIK)
  // ==========================================================
private function get_siswa_per_rombel()
{
    $tahun = $this->tahun_id;

    $query = $this->db
        ->select("
            k.nama AS nama_kelas,
            SUM(CASE WHEN s.jk = 'L' THEN 1 ELSE 0 END) AS laki,
            SUM(CASE WHEN s.jk = 'P' THEN 1 ELSE 0 END) AS perempuan,
            COUNT(st.id) AS total
        ")
        ->from('siswa_tahun st')
        ->join('siswa s', 's.id = st.siswa_id', 'left')
        ->join('kelas k', 'k.id = st.kelas_id', 'left')
        ->where('st.tahun_id', $tahun)
        ->where('st.status', 'aktif')
        ->group_by('k.nama')
        ->order_by('k.nama', 'ASC')
        ->get();

    return $query->result();
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
