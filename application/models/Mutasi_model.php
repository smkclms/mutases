<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mutasi_model extends CI_Model {

    // ================================================================
    //  COUNT (UNTUK PAGINATION)
    // ================================================================
    public function count_all()
    {
        $this->db->where('status_mutasi', 'aktif');
        return $this->db->count_all_results('mutasi');
    }


    // ================================================================
    //  GET ALL (LIST MUTASI + JOIN SISWA, KELAS, TAHUN)
    // ================================================================
    public function get_all($limit, $offset)
    {
        $this->db->select('
            mutasi.*,
            siswa.nama AS nama_siswa,
            siswa.nis,
            kelas.nama AS tujuan_kelas,
            tahun_ajaran.tahun AS tahun_ajaran
        ');
        $this->db->from('mutasi');
        $this->db->join('siswa', 'siswa.id = mutasi.siswa_id', 'left');
        $this->db->join('kelas', 'kelas.id = mutasi.tujuan_kelas_id', 'left');
        $this->db->join('tahun_ajaran', 'tahun_ajaran.id = mutasi.tahun_id', 'left');
        $this->db->where('mutasi.status_mutasi', 'aktif');
        $this->db->order_by('mutasi.id', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }


    // ================================================================
    //  GET SISWA AKTIF (untuk mutasi keluar)
    // ================================================================
    public function get_siswa_aktif()
    {
        return $this->db->where('status', 'aktif')->get('siswa')->result();
    }

    // ================================================================
    //  GET KELAS LIST
    // ================================================================
    public function get_kelas_list()
    {
        return $this->db->order_by('nama', 'ASC')->get('kelas')->result();
    }

    // ================================================================
    //  GET TAHUN LIST
    // ================================================================
    public function get_tahun_list()
    {
        return $this->db->order_by('id', 'DESC')->get('tahun_ajaran')->result();
    }


    // ================================================================
    //  MUTASI KELUAR (UPDATE SISWA + siswa_tahun + siswa_history)
    // ================================================================
    public function mutasi_keluar($data)
    {
        // Insert mutasi
        $this->db->insert('mutasi', $data);

        // Update status terbaru siswa
        $status_keluar = 'mutasi_keluar';

        if ($data['jenis_keluar'] == 'meninggal') {
            $status_keluar = 'meninggal';
        } elseif ($data['jenis_keluar'] == 'mengundurkan diri') {
            $status_keluar = 'keluar';
        }

        $this->db->where('id', $data['siswa_id'])
                 ->update('siswa', [
                     'status' => $status_keluar
                 ]);

        // Update siswa_tahun (tahun di mana dia keluar)
        $this->db->where('siswa_id', $data['siswa_id'])
                 ->where('tahun_id', $data['tahun_id'])
                 ->update('siswa_tahun', ['status' => 'mutasi_keluar']);

        // Insert history ↴
        $this->db->insert('siswa_history', [
            'siswa_id' => $data['siswa_id'],
            'kelas_id' => $data['kelas_asal_id'],
            'tahun_id' => $data['tahun_id'],
            'status'   => 'mutasi_keluar'
        ]);
    }


    // ================================================================
    //  MUTASI MASUK (siswa pindahan / masuk tahun berjalan)
    // ================================================================
    public function mutasi_masuk($data)
    {
        // Insert mutasi
        $this->db->insert('mutasi', $data);

        // Update status & kelas siswa terbaru
        $this->db->where('id', $data['siswa_id'])
                 ->update('siswa', [
                     'id_kelas' => $data['tujuan_kelas_id'],
                     'status'   => 'aktif'
                 ]);

        // Tambah siswa_tahun (tahun baru)
        $this->db->insert('siswa_tahun', [
            'siswa_id' => $data['siswa_id'],
            'kelas_id' => $data['tujuan_kelas_id'],
            'tahun_id' => $data['tahun_id'],
            'status'   => 'aktif'
        ]);

        // Insert history
        $this->db->insert('siswa_history', [
            'siswa_id' => $data['siswa_id'],
            'kelas_id' => $data['tujuan_kelas_id'],
            'tahun_id' => $data['tahun_id'],
            'status'   => 'mutasi_masuk'
        ]);
    }


    // ================================================================
    //  BATALKAN MUTASI
    // ================================================================
    public function batalkan($id)
    {
        $mutasi = $this->db->get_where('mutasi', ['id' => $id])->row();
        if (!$mutasi) return false;

        // Set mutasi jadi dibatalkan
        $this->db->where('id', $id)->update('mutasi', ['status_mutasi' => 'dibatalkan']);

        // Kembalikan data siswa seperti sebelum mutasi keluar
        if ($mutasi->jenis == 'keluar') {
            $updateData = ['status' => 'aktif'];

            if (!empty($mutasi->kelas_asal_id)) {
                $updateData['id_kelas'] = $mutasi->kelas_asal_id;
            }

            $this->db->where('id', $mutasi->siswa_id)->update('siswa', $updateData);

            // kembalikan siswa_tahun
            $this->db->where('siswa_id', $mutasi->siswa_id)
                     ->where('tahun_id', $mutasi->tahun_id)
                     ->update('siswa_tahun', ['status' => 'aktif']);
        }

        return true;
    }


    // ================================================================
    // Search siswa (autocomplete)
    // ================================================================
    public function search_siswa($keyword, $jenis)
    {
        $this->db->group_start()
                 ->like('nama', $keyword)
                 ->or_like('nis', $keyword)
                 ->group_end();

        if ($jenis === 'masuk') {
            $this->db->where('status', 'mutasi_masuk');
        } else {
            $this->db->where('status', 'aktif');
        }

        return $this->db->limit(10)->get('siswa')->result();
    }
}
