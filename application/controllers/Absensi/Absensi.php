<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Absensi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Absensi/Absensi_model');
        $this->load->model('Kelas_model'); // kita pakai kelas yg sudah ada
        $this->load->model('Siswa_model'); // data siswa aktif
    }

    public function index()
{
    $this->db->select("
        ad.id_detail,
        ad.id_absensi,
        ad.status,
        ad.keterangan,
        a.tanggal,
        a.tahun_pelajaran,
        s.nama AS nama_siswa,
        k.nama AS nama_kelas,
        g.telp AS nohp_wali
    ");
    $this->db->from("absensi_detail ad");
    $this->db->join("absensi a", "a.id_absensi = ad.id_absensi");
    $this->db->join("siswa s", "s.id = ad.id_siswa");
    $this->db->join("kelas k", "k.id = s.id_kelas");
    $this->db->join("guru g", "g.id = k.wali_kelas_id", "left"); // 🔥 FIX DISINI
    $this->db->order_by("ad.id_detail", "DESC");

    $data["absensi"] = $this->db->get()->result();
    $data['judul'] = "Data Absensi";
    $data['active'] = 'absensi';

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('absensi/index', $data);
    $this->load->view('templates/footer');
}
    public function tambah() {
    $data['judul'] = "Tambah Absensi";
    $data['kelas'] = $this->Kelas_model->get_all();
    $data['siswa_all'] = $this->Siswa_model->get_all_simple();
    $data['active'] = 'absensi';

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('absensi/tambah', $data);
    $this->load->view('templates/footer');
}


    public function form() {
        $tanggal = $this->input->post('tanggal');
        $id_kelas = $this->input->post('id_kelas');
        $tahun = $this->input->post('tahun_pelajaran');
        $data['active'] = 'absensi';

        $data['tanggal'] = $tanggal;
        $data['tahun']   = $tahun;
        $data['kelas']   = $this->Kelas_model->get_row($id_kelas);
        $data['siswa']   = $this->Siswa_model->get_by_kelas($id_kelas);

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('absensi/form', $data);
        $this->load->view('templates/footer');
    }

   public function simpan() {

    $tanggal = $this->input->post('tanggal');
    $id_kelas = $this->input->post('id_kelas'); // WAJIB
    $id_siswa = $this->input->post('id_siswa');
    $status   = $this->input->post('status');
    $alasan   = $this->input->post('keterangan');
    $tahun    = $this->input->post('tahun_pelajaran');

    // Validasi
    if (!$tanggal || !$id_kelas || !$id_siswa || !$status) {
        $this->session->set_flashdata('error', 'Lengkapi semua data!');
        redirect('Absensi/Absensi/tambah');
    }

    // Buat header absensi / ambil existing
    $id_absensi = $this->Absensi_model->get_or_create_absensi(
        $tanggal,
        $id_kelas,
        $tahun
    );

    // Simpan detail absensi
    $this->Absensi_model->insert_detail([
        'id_absensi' => $id_absensi,
        'id_siswa' => $id_siswa,
        'status' => $status,
        'keterangan' => $alasan
    ]);

    $this->session->set_flashdata('success', 'Absensi berhasil disimpan.');
    redirect('Absensi/Absensi/tambah'); // FIX redirect
}



    public function detail($id_absensi) {
    $data['judul'] = "Detail Absensi";
    $data['active'] = 'absensi';

    // data header absensi
    $data['absen'] = $this->Absensi_model->get_by_id($id_absensi);

    // ambil list siswa + status (H/I/S/A)
    $data['siswa'] = $this->Absensi_model->get_siswa_with_status(
        $data['absen']->id_kelas,
        $id_absensi
    );

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('absensi/detail', $data);
    $this->load->view('templates/footer');
}
// halaman input single
public function input()
{
    $data['judul'] = "Input Data Absen";
    $data['kelas'] = $this->Kelas_model->get_all();
    // ambil semua siswa untuk datalist (bisa diganti ajax jika jumlah banyak)
    $data['siswa_all'] = $this->Siswa_model->get_all_simple();
    $data['active'] = 'absensi';

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('absensi/input_single', $data);
    $this->load->view('templates/footer');
}

// simpan single absen
// public function save_single()
// {
//     // CSRF secara otomatis dicek karena config['csrf_protection'] = TRUE
//     $tanggal = $this->input->post('tanggal');
//     $id_kelas = $this->input->post('id_kelas');
//     $id_siswa = $this->input->post('id_siswa');
//     $status   = $this->input->post('status');
//     $keterangan = $this->input->post('keterangan');

//     if (!$tanggal || !$id_kelas || !$id_siswa || !$status) {
//         $this->session->set_flashdata('error', 'Lengkapi semua field yang wajib.');
//         redirect('index.php/Absensi/Absensi/input');
//     }

//     // dapatkan atau buat header absensi untuk tanggal+kelas+tahun (gunakan tahun dari input atau default)
//     $tahun_pelajaran = $this->input->post('tahun_pelajaran');
//     if (!$tahun_pelajaran) $tahun_pelajaran = date('Y') . '/' . (date('Y')+1); // contoh default

//     $id_absensi = $this->Absensi_model->get_or_create_absensi($tanggal, $id_kelas, $tahun_pelajaran);

//     // cek apakah sudah ada record untuk siswa ini pada header yang sama -> update atau insert
//     $exists = $this->db->get_where('absensi_detail', [
//         'id_absensi' => $id_absensi,
//         'id_siswa' => $id_siswa
//     ])->row();

//     $data_detail = [
//         'id_absensi' => $id_absensi,
//         'id_siswa' => $id_siswa,
//         'status' => $status,
//         'keterangan' => $keterangan
//     ];

//     if ($exists) {
//         // update
//         $this->db->where('id_detail', $exists->id_detail);
//         $this->db->update('absensi_detail', $data_detail);
//     } else {
//         // insert
//         $this->Absensi_model->insert_detail($data_detail);
//     }

//     $this->session->set_flashdata('success', 'Absensi tersimpan.');
//     redirect('index.php/Absensi/Absensi/input');
// }
public function ajax_siswa()
{
    $keyword = $this->input->post('keyword');

    $this->db->select('siswa.id, siswa.nisn, siswa.nama, kelas.nama AS nama_kelas, siswa.id_kelas');
    $this->db->from('siswa');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');

    // 🔥 hanya siswa aktif
    $this->db->where('siswa.status', 'aktif');

    // 🔥 perbaiki pencarian agar kondisi WHERE tidak kacau oleh OR LIKE
    $this->db->group_start();
        $this->db->like('siswa.nama', $keyword);
        $this->db->or_like('siswa.nisn', $keyword);
    $this->db->group_end();

    $this->db->limit(20);

    $result = $this->db->get()->result();

    echo json_encode($result);
}

}
