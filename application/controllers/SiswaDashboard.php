<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SiswaDashboard extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Siswa_model');
        $this->load->database();

        // simpan ID siswa di properti controller
        $this->siswa_id = $this->session->userdata('siswa_id');
    }

    private function cek_login()
{
    if (!$this->session->userdata('siswa_login')) {
        redirect('SiswaAuth');
    }
}


    public function index()
    {
        $this->cek_login();

        $data['siswa'] = $this->getSiswa();
        $data['active'] = 'dashboard';

        $this->load->view('siswa/layout/header', $data);
        $this->load->view('siswa/layout/sidebar', $data);
        $this->load->view('siswa/dashboard', $data);
        $this->load->view('siswa/layout/footer');
    }

    // ===================== BIODATA =========================
    public function biodata()
    {
        $this->cek_login();

        $data['siswa'] = $this->getSiswa();
        $data['active'] = 'biodata';

        $this->load->view('siswa/layout/header', $data);
        $this->load->view('siswa/layout/sidebar', $data);
        $this->load->view('siswa/biodata', $data);
        $this->load->view('siswa/layout/footer');
    }

    // ===================== CETAK PDF =======================
    public function cetak()
    {
        $this->cek_login();

        $data['siswa'] = $this->getSiswa();
        $html = $this->load->view('siswa/cetak', $data, TRUE);

        $this->load->library('pdf');
        $pdf = new Tcpdf('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        $pdf->writeHTML($html);
        $pdf->Output('Biodata_'.$data['siswa']->nama.'.pdf', 'I');
    }

    // ===================== RIWAYAT MUTASI ==================
    public function mutasi()
    {
        $this->cek_login();

        $data['mutasi'] = $this->db
            ->where('siswa_id', $this->siswa_id)
            ->get('mutasi')
            ->result();

        $data['siswa'] = $this->getSiswa();
        $data['active'] = 'mutasi';

        $this->load->view('siswa/layout/header', $data);
        $this->load->view('siswa/layout/sidebar', $data);
        $this->load->view('siswa/mutasi', $data);
        $this->load->view('siswa/layout/footer');
    }

    // ===================== PENGUMUMAN ======================
    public function pengumuman()
    {
        $this->cek_login();

        $data['pengumuman'] = $this->db->order_by('id','DESC')->get('pengumuman')->result();
        $data['siswa'] = $this->getSiswa();
        $data['active'] = 'pengumuman';

        $this->load->view('siswa/layout/header', $data);
        $this->load->view('siswa/layout/sidebar', $data);
        $this->load->view('siswa/pengumuman', $data);
        $this->load->view('siswa/layout/footer');
    }

    // ===================== UBAH PASSWORD ===================
    public function password()
    {
        $this->cek_login();

        $data['siswa'] = $this->getSiswa();
        $data['active'] = 'password';

        $this->load->view('siswa/layout/header', $data);
        $this->load->view('siswa/layout/sidebar', $data);
        $this->load->view('siswa/password', $data);
        $this->load->view('siswa/layout/footer');
    }

    public function save_password()
    {
        $this->cek_login();

        $old = $this->input->post('old');
        $new = $this->input->post('new');

        $siswa = $this->getSiswa();

        $password_now = $siswa->password ? $siswa->password : $siswa->nisn;

        if ($old != $password_now) {
            $this->session->set_flashdata('error', "Password lama salah!");
            redirect('SiswaDashboard/password');
        }

        $this->db->where('id', $this->siswa_id)
                 ->update('siswa', ['password' => $new]);

        $this->session->set_flashdata('success', "Password berhasil diubah!");
        redirect('SiswaDashboard/password');
    }

    // ===================== GET SISWA DETAIL =================
    private function getSiswa()
    {
        return $this->db
            ->select('siswa.*, kelas.nama AS nama_kelas, tahun_ajaran.tahun AS tahun_ajaran')
            ->join('kelas','kelas.id = siswa.id_kelas','left')
            ->join('tahun_ajaran','tahun_ajaran.id = siswa.tahun_id','left')
            ->where('siswa.id', $this->siswa_id)
            ->get('siswa')->row();
    }
}
