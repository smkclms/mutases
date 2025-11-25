<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AbsensiQRAdmin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();

        // models yang dibutuhkan (pastikan ada)
        $this->load->model('Kelas_model');
        $this->load->model('Siswa_model');
        $this->load->model('Hari_libur_model');

        // coba load pdf library (jika ada wrapper). Jika tidak, controller akan fallback ke TCPDF di third_party.
        // Jangan hapus; wrapper sering ada di project CI
        @$this->load->library('pdf');

        // PHPExcel wrapper (jika tersedia)
        @$this->load->library('PHPExcel_Lib');
    }

    // 1) Halaman list absensi QR
    public function index() {

    $data['kelas']  = $this->Kelas_model->get_all();
    $data['judul']  = "Absensi QR Siswa";
    $data['active'] = "absensiqr_siswa";

    // === AMBIL SEMUA DATA ABSENSI QR ===
    $this->db->select("
        q.id,
        q.nis,
        q.tanggal,
        q.jam_masuk,
        q.jam_pulang,
        q.status,
        q.kehadiran,
        q.keterangan_telat,
        s.nama as nama_siswa,
        k.nama as nama_kelas
    ");
    $this->db->from("absensi_qr q");
    $this->db->join("siswa s", "s.nis = q.nis", "left");
    $this->db->join("kelas k", "k.id = s.id_kelas", "left");
    $this->db->order_by("q.id", "DESC");

    $data['absen'] = $this->db->get()->result();

    // === LOAD VIEW ===
    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('absensiqr/admin_index', $data);
    $this->load->view('templates/footer');
}


    // 2) Halaman laporan (form filter)
    public function laporan() {
        $data['kelas']  = $this->Kelas_model->get_all();
        $data['judul']  = "Laporan Absensi QR";
        $data['active'] = "laporan_absensiqr";

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('absensiqr/admin_laporan', $data);
        $this->load->view('templates/footer');
    }

    // 3) AJAX: ambil data filtered (untuk preview tabel)
    public function data()
    {
        $nama   = $this->input->post('nama');
        $kelas  = $this->input->post('kelas');
        $dari   = $this->input->post('dari');
        $sampai = $this->input->post('sampai');
        $status = $this->input->post('status');

        $this->db->select("
            q.id,
            q.nis,
            q.tanggal,
            q.jam_masuk,
            q.jam_pulang,
            q.status,
            q.kehadiran,
            q.keterangan_telat,
            s.nama as nama_siswa,
            k.nama as nama_kelas
        ");
        $this->db->from("absensi_qr q");
        $this->db->join("siswa s", "s.nis = q.nis", "left");
        $this->db->join("kelas k", "k.id = s.id_kelas", "left");

        if ($nama != "") {
            $this->db->like("s.nama", $nama);
        }

        if ($kelas != "") {
            $this->db->where("s.id_kelas", $kelas);
        }

        if ($status != "") {
            // status filter expects kehadiran code: H, S, I, A
            $this->db->where("q.kehadiran", $status);
        }

        if ($dari != "" && $sampai != "") {
            $this->db->where("q.tanggal >=", $dari);
            $this->db->where("q.tanggal <=", $sampai);
        }

        $this->db->order_by("q.tanggal", "ASC");

        echo json_encode($this->db->get()->result());
    }

    // 4) PDF export: TCPDF, kalender per kelas per bulan
    public function pdf()
{
    ob_clean();
    $kelas  = $this->input->get('kelas');
    $dari   = $this->input->get('dari');
    $sampai = $this->input->get('sampai');

    if (!$dari || !$sampai) show_error("Tanggal wajib diisi");

    $this->load->library('pdf');
    $this->pdf->setPrintHeader(false);
    $this->pdf->setPrintFooter(false);
    $this->pdf->SetMargins(5, 5, 5);
    $this->pdf->SetFont('helvetica', '', 9);

    // -----------------------------
    // Buat range tanggal
    // -----------------------------
    $tanggal = [];
    $start = strtotime($dari);
    $end   = strtotime($sampai);

    while ($start <= $end) {
        $tanggal[] = date("Y-m-d", $start);
        $start = strtotime("+1 day", $start);
    }

    // -----------------------------
    // Ambil siswa per kelas
    // -----------------------------
    $siswa = $this->db->query("
        SELECT nis, nama FROM siswa
        WHERE id_kelas = ?
        ORDER BY nama ASC
    ", [$kelas])->result();

    // -----------------------------
    // Ambil data absensi QR
    // -----------------------------
    $q = $this->db->query("
        SELECT nis, tanggal, kehadiran
        FROM absensi_qr
        WHERE tanggal BETWEEN ? AND ?
    ", [$dari, $sampai])->result();

    // bentuk index
    $rekap = [];
    foreach ($q as $r) {
        $rekap[$r->nis][$r->tanggal] = strtoupper($r->kehadiran);
    }

    // -----------------------------
    // Ambil tanggal libur (hari_libur)
    // -----------------------------
    $hari_libur = $this->db->get("hari_libur")->result();
    $tanggalMerah = [];
    foreach ($hari_libur as $hl) {
        $tanggalMerah[] = $hl->start;
    }

    // -----------------------------
    // Ambil nama kelas
    // -----------------------------
    $kelas_row = $this->db->get_where("kelas", ["id" => $kelas])->row();
    $kelas_nama = $kelas_row ? $kelas_row->nama : "-";

    // -----------------------------
    // Data ke view
    // -----------------------------
    $data = [
        'tanggal'      => $tanggal,
        'rekap'        => $rekap,
        'siswa'        => $siswa,
        'bulan_label'  => date("F Y", strtotime($dari)),
        'tahun'        => date("Y"),
        'kelas_nama'   => $kelas_nama,
        'tanggalMerah' => $tanggalMerah
    ];

    // Render ke PDF
    $this->pdf->AddPage('L', array(330, 210));
    $html = "<div style='font-size:8px'>" .
        $this->load->view("absensiqr/pdf_rekap_bulan", $data, true) .
        "</div>";

    $this->pdf->writeHTML($html, true, false, true, false, '');
    $this->pdf->Output("Rekap_Absensi_QR.pdf", "I");
}

    // 5) Excel export (kalender style)
    public function excel()
{
    // bersihkan buffer supaya header file tidak rusak
    if (ob_get_length()) { ob_end_clean(); }

    $kelas_param  = $this->input->get('kelas');
    $dari_raw     = $this->input->get('dari');
    $sampai_raw   = $this->input->get('sampai');
    $status_param = $this->input->get('status'); // optional filter kehadiran

    if (!$dari_raw || !$sampai_raw) {
        show_error("Filter tanggal wajib diisi.");
    }

    $dari   = date('Y-m-d', strtotime($dari_raw));
    $sampai = date('Y-m-d', strtotime($sampai_raw));

    // load PHPExcel wrapper & buat object
    $this->load->library('PHPExcel_Lib');
    $excel = new PHPExcel();

    // 1) daftar kelas
    if ($kelas_param === "" || $kelas_param === "all" || $kelas_param === null) {
        $kelas_list = $this->db->query("SELECT id, nama FROM kelas ORDER BY nama ASC")->result();
        $single_class = false;
    } else {
        $kelas_list = $this->db->query("SELECT id, nama FROM kelas WHERE id = ?", array($kelas_param))->result();
        $single_class = true;
    }

    if (empty($kelas_list)) {
        show_error("Tidak ada data kelas.");
    }

    // 2) buat array tanggal dari dari..sampai
    $tanggal_all = array();
    $start = new DateTime($dari);
    $end   = new DateTime($sampai);
    for ($d = $start; $d <= $end; $d->modify('+1 day')) {
        $tanggal_all[] = $d->format('Y-m-d');
    }

    // 3) kelompokkan tanggal per bulan (format key: YYYY-mm)
    $tanggal_per_bulan = array();
    foreach ($tanggal_all as $tgl) {
        $bulan_key = date('Y-m', strtotime($tgl));
        if (!isset($tanggal_per_bulan[$bulan_key])) {
            $tanggal_per_bulan[$bulan_key] = array();
        }
        $tanggal_per_bulan[$bulan_key][] = $tgl;
    }

    // 4) ambil hari libur (kolom start)
    $q_libur = $this->db->query("SELECT start FROM hari_libur")->result();
    $hariMerah = array();
    foreach ($q_libur as $r) {
        $hariMerah[] = $r->start;
    }

    // 5) Ambil semua absensi_qr dalam rentang global (agar tidak query ulang di tiap sheet)
    // optional: filter by status kehadiran jika diberikan (H/S/I/A)
    $params = array($dari, $sampai);
    $sqlStatus = "";
    if ($status_param !== null && $status_param !== "") {
        // user might pass full words like "Terlambat" or codes H/I/S/A;
        // here we assume the status filter maps to q.kehadiran code directly (H/I/S/A)
        $sqlStatus = " AND kehadiran = ?";
        $params[] = $status_param;
    }
    $q_all = $this->db->query("SELECT nis, tanggal, kehadiran FROM absensi_qr WHERE tanggal BETWEEN ? AND ? {$sqlStatus}", $params)->result();

    // index absensi [nis][tanggal] => kode (H/I/S/A)
    $arrAbsenGlobal = array();
    foreach ($q_all as $aa) {
        $arrAbsenGlobal[$aa->nis][$aa->tanggal] = strtoupper($aa->kehadiran);
    }

    $sheetIndex = 0;

    // 6) loop per kelas
    foreach ($kelas_list as $k) {

        // ambil siswa untuk kelas ini (gunakan nis untuk mapping)
        $siswa = $this->db->query("SELECT id, nis, nama FROM siswa WHERE id_kelas = ? AND status='aktif' ORDER BY nama ASC", array($k->id))->result();

        // jika tidak ada siswa, skip kelas
        if (empty($siswa)) {
            continue;
        }

        // untuk setiap bulan di rentang
        foreach ($tanggal_per_bulan as $bulan_key => $tgl_bulan) {

            // create or select sheet
            if ($sheetIndex > 0) {
                $excel->createSheet();
            }
            $sheet = $excel->setActiveSheetIndex($sheetIndex);

            // penamaan sheet:
            if ($single_class) {
                // nama sheet = "Jan 2025"
                $sheetTitle = date('F Y', strtotime($bulan_key . '-01'));
            } else {
                // "KELAS - Mon YYYY"
                $sheetTitle = $k->nama . ' - ' . date('M Y', strtotime($bulan_key . '-01'));
            }

            // pastikan tidak lebih dari 31 karakter untuk sheet title
            $sheet->setTitle(substr($sheetTitle, 0, 31));

            // Header utama
            $sheet->setCellValue('A1', 'REKAP ABSENSI SISWA');
            $sheet->setCellValue('A2', 'KELAS: ' . $k->nama);
            $sheet->setCellValue('A3', 'PERIODE: ' . date('d-m-Y', strtotime($dari)) . ' s/d ' . date('d-m-Y', strtotime($sampai)));
            $sheet->setCellValue('A4', 'BULAN: ' . date('F Y', strtotime($bulan_key . '-01')));

            // Header kolom (baris 6)
            $sheet->setCellValue('A6', 'No');
            $sheet->setCellValue('B6', 'Nama Siswa');

            // tulis tanggal khusus untuk bulan ini
            $col = 'C';
            foreach ($tgl_bulan as $tgl) {
                $sheet->setCellValue($col . '6', date('d', strtotime($tgl)));
                // set column width kecil
                $sheet->getColumnDimension($col)->setWidth(4.5);
                $col++;
            }

            // setelah tanggal, tulis header jumlah
            $sheet->setCellValue($col . '6', 'H'); $col++;
            $sheet->setCellValue($col . '6', 'S'); $col++;
            $sheet->setCellValue($col . '6', 'I'); $col++;
            $sheet->setCellValue($col . '6', 'A'); $col++;
            $sheet->setCellValue($col . '6', 'L');

            // tulis data siswa mulai baris 7
            $row = 7;
            $no = 1;
            foreach ($siswa as $s) {

                $sheet->setCellValue('A' . $row, $no);
                $sheet->setCellValue('B' . $row, $s->nama);

                // reset hitungan
                $jumlahH = $jumlahS = $jumlahI = $jumlahA = $jumlahL = 0;

                $col = 'C';
                foreach ($tgl_bulan as $tgl) {

                    $hariNum = date('N', strtotime($tgl));
                    $isWeekend = ($hariNum == 6 || $hariNum == 7);
                    $isMerah   = in_array($tgl, $hariMerah);

                    // default: '-' (sama seperti PDF)
                    $val = '-';

                    // weekend / tanggal merah -> L (tapi still '-' if we prefer? user's PDF used L for libur, so we keep L)
                    if ($isWeekend || $isMerah) {
                        $val = 'L';
                    }

                    // override jika ada data QR untuk nis ini & tanggal ini
                    if (isset($arrAbsenGlobal[$s->nis][$tgl])) {
                        $val = strtoupper($arrAbsenGlobal[$s->nis][$tgl]);
                    }

                    // count only real statuses
                    if ($val == 'H') $jumlahH++;
                    if ($val == 'S') $jumlahS++;
                    if ($val == 'I') $jumlahI++;
                    if ($val == 'A') $jumlahA++;
                    if ($val == 'L') $jumlahL++;

                    $sheet->setCellValue($col . $row, $val);

                    // jika L beri warna ringan merah
                    if ($val == 'L') {
                        $sheet->getStyle($col . $row)->getFill()->applyFromArray(array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => array('rgb' => 'FF9999')
                        ));
                    }

                    $col++;
                }

                // tulis totals di kolom setelah tanggal
                $sheet->setCellValue($col . $row, $jumlahH); $col++;
                $sheet->setCellValue($col . $row, $jumlahS); $col++;
                $sheet->setCellValue($col . $row, $jumlahI); $col++;
                $sheet->setCellValue($col . $row, $jumlahA); $col++;
                $sheet->setCellValue($col . $row, $jumlahL);

                $row++;
                $no++;
            }

            $sheetIndex++;
        } // end foreach bulan

    } // end foreach kelas

    // aktifkan sheet pertama
    $excel->setActiveSheetIndex(0);

    // output ke browser
    $filename = 'Rekap_Absensi_QR_' . date('Ymd_His') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    $writer->save('php://output');
    exit;
}

    // internal helper kalau perlu (tidak wajib)
    private function _array_months_from_range($start, $end)
    {
        $months = [];
        $startDt = new DateTime($start);
        $endDt = new DateTime($end);
        for ($d = $startDt; $d <= $endDt; $d->modify('+1 month')) {
            $months[] = $d->format('Y-m');
        }
        return $months;
    }
}
