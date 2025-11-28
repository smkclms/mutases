<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RfidAbsensi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('AbsensiQR_model', 'qr');
        $this->load->database();
        $this->load->helper('wa_helper'); // pastikan helper WA diload
    }

    /* ------------------------------
        Helper untuk kirim CSRF
    ------------------------------ */
    private function csrf()
    {
        return [
            "csrfName" => $this->security->get_csrf_token_name(),
            "csrfHash" => $this->security->get_csrf_hash()
        ];
    }

    public function register()
    {
        $data['siswa'] = $this->db->get_where('siswa', ['status' => 'aktif'])->result();
        $this->load->view('rfid/register', $data);
    }

    // ✔ Halaman tes manual RFID
    public function scan_test()
    {
        $this->load->view('rfid/scan_test');
    }

    // ========================================
    //             SCAN RFID
    // ========================================
    public function scan()
    {
        $uid = $this->input->post('uid');

        if (!$uid) {
            echo json_encode(array_merge([
                'status' => false,
                'msg' => 'UID kosong'
            ], $this->csrf()));
            return;
        }

        // ✔ mencari siswa berdasarkan UID RFID
        $siswa = $this->db->get_where('siswa', ['rfid_uid' => $uid])->row();

        if (!$siswa) {
            echo json_encode(array_merge([
                'status' => false,
                'msg' => 'Kartu tidak dikenal'
            ], $this->csrf()));
            return;
        }

        // --------------------------
        // Data dasar
        // --------------------------
        $nis     = $siswa->nis;
        $tanggal = date('Y-m-d');
        $jamNow  = date('H:i:s');

        // ============================
        //   CEK JADWAL DAN LIBUR
        // ============================
        $hariIndex = date('N');
        $hariNamaMap = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
        ];
        $hariNama = $hariNamaMap[$hariIndex];

        $cekLibur = $this->db->get_where('hari_libur', ['start' => $tanggal])->row();
        $isWeekend = ($hariNama == 'Sabtu' || $hariNama == 'Minggu');

        if ($cekLibur || $isWeekend) {
            echo json_encode(array_merge([
                'status' => false,
                'msg' => "Hari ini libur ({$hariNama})"
            ], $this->csrf()));
            return;
        }

        // jadwal hari ini
        $jadwal = $this->db->get_where('absensi_jadwal', ['hari' => $hariNama])->row();
        $jamMasukResmi  = $jadwal ? $jadwal->jam_masuk  : "07:00:00";
        $jamPulangResmi = $jadwal ? $jadwal->jam_pulang : "14:00:00";

        // ambil absen hari ini (absensi_qr)
        $absen = $this->qr->get_absen_hari_ini($nis, $tanggal);


        // ===================================================
        //                      ABSEN MASUK
        // ===================================================
        if (!$absen) {

            if (strtotime($jamNow) <= strtotime($jamMasukResmi)) {
                $status = "Tepat";
                $keterangan_telat = null;
            } else {
                $status = "Terlambat";
                $telat_detik = strtotime($jamNow) - strtotime($jamMasukResmi);
                $keterangan_telat = $this->format_telat($telat_detik);
            }

            $insert = [
                'nis'               => $nis,
                'tanggal'           => $tanggal,
                'jam_masuk'         => $jamNow,
                'status'            => $status,
                'kehadiran'         => 'H',
                'keterangan_telat'  => $keterangan_telat,
                'sumber'            => 'scan_rfid'
            ];

            $this->qr->insert_absen_masuk($insert);

            // ===========================================
            //     KIRIM WHATSAPP – ABSEN MASUK
            // ===========================================
            $this->kirim_wa_rfid($siswa, "masuk", $jamNow, $tanggal, $status, $keterangan_telat);

            echo json_encode(array_merge([
                'type'    => 'masuk',
                'nama'    => $siswa->nama,
                'jam'     => $jamNow,
                'status'  => $status
            ], $this->csrf()));
            return;
        }

        // ===========================================
        // SUDAH PULANG
        // ===========================================
        if ($absen->jam_pulang != null) {
            echo json_encode(array_merge([
                'type' => 'sudah_pulang',
                'nama' => $siswa->nama
            ], $this->csrf()));
            return;
        }

        // ===============================================
        // BELUM WAKTU PULANG
        // ===============================================
        if (strtotime($jamNow) < strtotime($jamPulangResmi)) {
            echo json_encode(array_merge([
                'type'      => 'belum_waktu',
                'nama'      => $siswa->nama,
                'jam_now'   => $jamNow,
                'waktu_pulang' => $jamPulangResmi
            ], $this->csrf()));
            return;
        }

        // ===========================================
        //                 ABSEN PULANG
        // ===========================================
        $this->qr->update_absen_pulang($absen->id, $jamNow);

        // ✔ KIRIM WA
        $this->kirim_wa_rfid($siswa, "pulang", $jamNow, $tanggal, null);

        echo json_encode(array_merge([
            'type' => 'pulang',
            'nama' => $siswa->nama,
            'jam_pulang' => $jamNow
        ], $this->csrf()));
    }


    /* -------------------------------------------------
        FUNGSI KIRIM WA UNTUK RFID
    --------------------------------------------------- */
    private function kirim_wa_rfid($siswa, $tipe, $jam, $tanggal, $status, $keterangan_telat = null)
{
    if (empty($siswa->no_hp_ortu)) return;

    // normalisasi nomor
    $no = preg_replace('/\D/', '', $siswa->no_hp_ortu);
    if (substr($no, 0, 1) == '0') $no = '62' . substr($no, 1);

    if ($tipe == "masuk") {

        $pesan = 
        "Assalamualaikum, Orang tua/wali dari *{$siswa->nama}*.\n\n" .
        "Ananda telah *HADIR* melalui *RFID*.\n" .
        "⏰ Jam: *{$jam}*\n" .
        "📅 Tanggal: *{$tanggal}*\n" .
        "📌 Status: *{$status}*\n";

        if ($status == "Terlambat" && !empty($keterangan_telat)) {
            $pesan .= "⏱️ Keterlambatan: _{$keterangan_telat}_\n";
        }

        $pesan .= "\nTerima kasih.";

    } else {
        // pulang
        $pesan =
        "Assalamualaikum, Orang tua/wali dari *{$siswa->nama}*.\n\n" .
        "Ananda telah *PULANG* melalui *RFID*.\n" .
        "⏰ Jam Pulang: *{$jam}*\n" .
        "📅 Tanggal: *{$tanggal}*\n\n" .
        "Terima kasih.";
    }

    send_wa($no, $pesan);
}


    /* -------------------------------------------------
        Format telat
    --------------------------------------------------- */
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
    public function scan_real()
{
    $this->load->view('rfid/scan_real');
}
public function test_reader()
{
    $this->load->view('rfid/test_reader');
}

}
