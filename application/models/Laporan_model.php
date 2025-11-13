<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan_model extends CI_Model {

  public function get_laporan($tahun, $kelas = null, $jenis = null, $search = null) {

    // Tahun ajaran aktif (ID → ambil string tahun)
    $tahun_id = $this->session->userdata('tahun_id');
    $tahun_aktif = $this->db->get_where('tahun_ajaran', ['id' => $tahun_id])->row();

    $this->db->from('v_mutasi_detail');

    // ======================================================
    // FILTER TAHUN AJARAN LOGIN (gunakan kolom `tahun_ajaran`)
    // ======================================================
    if ($tahun_aktif) {
        $this->db->where('tahun_ajaran', $tahun_aktif->tahun);
    }

    // ======================================================
    // OPTIONAL: FILTER TAHUN KALENDER (YEAR(tanggal))
    // ======================================================
    if (!empty($tahun)) {
        $this->db->where('YEAR(tanggal)', $tahun);
    }

    // Mutasi aktif saja
    $this->db->where('(status_mutasi IS NULL OR status_mutasi = "aktif")');

    // Filter kelas asal
    if (!empty($kelas)) {
        $this->db->where('kelas_asal_id', $kelas);
    }

    // Filter jenis mutasi
    if (!empty($jenis)) {
        $this->db->where('jenis', strtolower($jenis));
    }

    // Pencarian
    if (!empty($search)) {
        $this->db->group_start()
                 ->like('nama_siswa', $search)
                 ->or_like('nis', $search)
                 ->or_like('nisn', $search)
                 ->group_end();
    }

    $this->db->order_by('tanggal', 'DESC');

    return $this->db->get()->result();
  }

  public function get_kelas() {
    return $this->db->order_by('nama', 'ASC')->get('kelas')->result();
  }
}
