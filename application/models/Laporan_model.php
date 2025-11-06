<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan_model extends CI_Model {

  public function get_laporan($tahun, $kelas = null, $jenis = null, $search = null) {
    $this->db->from('v_mutasi_detail');
    $this->db->where('YEAR(tanggal)', $tahun);

    if ($kelas) {
      $this->db->where('kelas_asal_id', $kelas);
    }
    if ($jenis) {
      $this->db->where('jenis', $jenis);
    }
    if ($search) {
      $this->db->like('nama_siswa', $search);
    }

    return $this->db->get()->result();
  }

  public function get_kelas() {
    return $this->db->order_by('nama', 'ASC')->get('kelas')->result();
  }
}
