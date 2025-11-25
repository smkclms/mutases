<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AbsensiQR extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('AbsensiQR_model', 'qr');
        $this->load->database();
    }

    // ==========================================
    //              SCAN QR
    // ==========================================
    public function scan($token = null)
    {
        // mapping numeric -> nama hari
        $hariIndex = date('N');
        $hariNamaMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu'
        ];
        $hariNama = $hariNamaMap[$hariIndex];
        // ============================
// CEK LIBUR NASIONAL / CUSTOM
// ============================
$tanggalHariIni = date('Y-m-d');

// cek di tabel hari_libur
$cekLibur = $this->db->get_where('hari_libur', [
    'start' => $tanggalHariIni
])->row();

// cek weekend
$isWeekend = ($hariNama == 'Sabtu' || $hariNama == 'Minggu');

if ($cekLibur || $isWeekend) {
    // Kirim ke view bahwa hari ini libur
    $data = [
        'hari_nama' => $hariNama,
        'libur'     => true,
        'keterangan_libur' => $cekLibur ? $cekLibur->nama : "Hari " . $hariNama
    ];
    $this->load->view('absensiqr/scan', $data);
    return;
}


        // ambil jadwal hari ini
        $jadwal = $this->db->get_where('absensi_jadwal', ['hari' => $hariNama])->row();

        // fallback jika tidak ada
        $jamMasukResmi  = $jadwal ? $jadwal->jam_masuk  : "07:00:00";
        $jamPulangResmi = $jadwal ? $jadwal->jam_pulang : "14:00:00";

        // jika tidak ada token → tampilkan halaman scanner
        if ($token == null || $token == "") {
            $data['jam_masuk']  = $jamMasukResmi;
            $data['jam_pulang'] = $jamPulangResmi;
            $data['hari_nama']  = $hariNama;
            $this->load->view('absensiqr/scan', $data);
            return;
        }

        // cari siswa
        $siswa = $this->qr->get_siswa_by_token($token);
        if (!$siswa) {
            echo "<h2 style='color:red;'>QR tidak dikenal!</h2>";
            return;
        }

        $nis     = $siswa->nis;
        $tanggal = date('Y-m-d');
        $jamNow  = date('H:i:s');

        // cek absen hari ini
        $absen = $this->qr->get_absen_hari_ini($nis, $tanggal);


        // ============================
        // ABSEN MASUK
        // ============================
        if (!$absen) {

            if (strtotime($jamNow) <= strtotime($jamMasukResmi)) {
    $status = "Tepat";
    $keterangan_telat = null;
} else {
    $status = "Terlambat";

    // hitung detik keterlambatan
    $telat_detik = strtotime($jamNow) - strtotime($jamMasukResmi);

    // format seperti "1 jam 5 menit 1 detik"
    $keterangan_telat = $this->format_telat($telat_detik);
}


            $insert = [
    'nis'               => $nis,
    'tanggal'           => $tanggal,
    'jam_masuk'         => $jamNow,
    'status'            => $status,
    'kehadiran'         => 'H',
    'keterangan_telat'  => $keterangan_telat,
    'sumber'            => 'scan_qr'
];

            $this->qr->insert_absen_masuk($insert);

            $this->load->view('absensiqr/hasil', [
                'type'             => 'masuk',
                'nama'             => $siswa->nama,
                'jam_masuk'        => $jamNow,
                'status'           => $status,
                'jam_resmi_masuk'  => $jamMasukResmi,
                'jam_resmi_pulang' => $jamPulangResmi
            ]);
            return;
        }


        // ============================
        // SUDAH ABSEN PULANG
        // ============================
        if ($absen->jam_pulang != null) {
            $this->load->view('absensiqr/hasil', [
                'type' => 'sudah_pulang',
                'nama' => $siswa->nama
            ]);
            return;
        }


        // ============================
        // BELUM WAKTU PULANG
        // ============================
        if (strtotime($jamNow) < strtotime($jamPulangResmi)) {
            $this->load->view('absensiqr/hasil', [
                'type'             => 'belum_waktu',
                'nama'             => $siswa->nama,
                'jam_now'          => $jamNow,
                'jam_resmi_pulang' => $jamPulangResmi
            ]);
            return;
        }


        // ============================
        // ABSEN PULANG
        // ============================
        $this->qr->update_absen_pulang($absen->id, $jamNow);

        $this->load->view('absensiqr/hasil', [
            'type' => 'pulang',
            'nama' => $siswa->nama,
            'jam_pulang' => $jamNow
        ]);
    }
    public function scan_ajax($token = null)
{
    if (!$token) {
        echo json_encode([
            "type" => "error",
            "redirect" => base_url("index.php/AbsensiQR/hasil_error?msg=QR%20tidak%20dikenal")
        ]);
        return;
    }

    // ambil logika yang sama dengan scan(), tapi tanpa view
    $hasil = $this->_process_scan_logic($token);

    echo json_encode($hasil);
}

public function index()
{
    $data['judul'] = "Absensi QR";

    // Ambil data absen + join siswa + kelas
    $this->db->select("
    a.id,
    a.nis,
    a.tanggal,
    a.jam_masuk,
    a.jam_pulang,
    a.status,
    a.kehadiran,
    a.keterangan_telat,
    a.sumber,
    s.nama AS nama_siswa,
    k.nama AS nama_kelas
");

    $this->db->from("absensi_qr a");
    $this->db->join("siswa s", "s.nis = a.nis", "left");
    $this->db->join("kelas k", "k.id = s.id_kelas", "left");
    $this->db->order_by("a.tanggal", "DESC");

    $data['absen'] = $this->db->get()->result();

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('absensiqr/index', $data);
    $this->load->view('templates/footer');
}

public function simpan()
{
    $data = [
        'nis'        => $this->input->post('nis'),
        'tanggal'    => $this->input->post('tanggal'),
        'jam_masuk'  => $this->input->post('jam_masuk'),
        'jam_pulang' => $this->input->post('jam_pulang'),
        'kehadiran'  => $this->input->post('kehadiran'),
        'status'     => $this->input->post('status')
    ];

    $this->db->insert('absensi_qr', $data);
    $this->session->set_flashdata('success', 'Data absensi berhasil ditambahkan!');
    redirect('AbsensiQRAdmin');
}

public function update()
{
    $id = $this->input->post('id');

    $data = [
        'kehadiran'  => $this->input->post('kehadiran'),
        'status'     => $this->input->post('status'),
        'tanggal'    => $this->input->post('tanggal'),
        'jam_masuk'  => $this->input->post('jam_masuk'),
        'jam_pulang' => $this->input->post('jam_pulang'),
    ];

    $this->db->where('id', $id)->update('absensi_qr', $data);

    $this->session->set_flashdata('success', 'Data absensi berhasil diupdate!');
    redirect('AbsensiQRAdmin');
}

public function hapus($id)
{
    $this->db->delete('absensi_qr', ['id' => $id]);
    $this->session->set_flashdata('success', 'Data berhasil dihapus!');
    redirect('AbsensiQRAdmin');
}
public function ajax_siswa()
{
    $keyword = $this->input->post('keyword');

    $this->db->select('siswa.nis, siswa.nama, kelas.nama AS nama_kelas');
    $this->db->from('siswa');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');

    // hanya siswa aktif
    $this->db->where('siswa.status', 'aktif');

    // pencarian
    $this->db->group_start();
        $this->db->like('siswa.nama', $keyword);
        $this->db->or_like('siswa.nis', $keyword);
    $this->db->group_end();

    $this->db->limit(20);

    $data = $this->db->get()->result();

    echo json_encode($data);
}
// hitung keterlambatan siswa ketika hadir tapi telat
private function format_telat($total_detik)
{
    $jam = floor($total_detik / 3600);
    $menit = floor(($total_detik % 3600) / 60);
    $detik = $total_detik % 60;

    $hasil = [];

    if ($jam > 0)   $hasil[] = $jam . ' jam';
    if ($menit > 0) $hasil[] = $menit . ' menit';
    if ($detik > 0) $hasil[] = $detik . ' detik';

    return implode(' ', $hasil);
}

}
