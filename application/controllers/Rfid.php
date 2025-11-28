<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rfid extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'form']);
        $this->load->library('session');
    }

    // halaman register kartu
    public function register()
    {
        $data['siswa'] = $this->db->order_by('nama','asc')->get('siswa')->result();
        $this->load->view('rfid/register', $data);
    }
    public function search_siswa()
{
    $keyword = $this->input->get('q');
    $kelas = $this->input->get('kelas');

    $this->db->select('siswa.id, siswa.nama, kelas.nama AS nama_kelas');
    $this->db->from('siswa');
    $this->db->join('kelas', 'kelas.id = siswa.id_kelas', 'left');

    // ❗ Hanya siswa yang BELUM punya UID
    $this->db->where('siswa.rfid_uid IS NULL', null, false);

    // Filter nama jika ada pencarian
    if ($keyword) {
        $this->db->like('siswa.nama', $keyword);
    }

    // Filter kelas bila dipilih
    if ($kelas && $kelas !== 'all') {
        $this->db->where('siswa.id_kelas', $kelas);
    }

    $this->db->order_by('siswa.nama', 'asc');
    $this->db->limit(50);

    $result = $this->db->get()->result();

    echo json_encode($result);
}


    // simpan UID ke siswa
    public function save()
{
    $uid = $this->input->post('uid', TRUE);
    $id_siswa = $this->input->post('id_siswa', TRUE);

    if (!$uid || !$id_siswa) {
        show_error("Data tidak valid.");
    }

    // Cek apakah UID sudah digunakan oleh siswa lain
    $cek = $this->db->get_where('siswa', ['rfid_uid' => $uid])->row();

    if ($cek) {
        // UID dipakai oleh siswa lain → kirim notifikasi ramah
        $this->session->set_flashdata('error', 'UID sudah digunakan oleh siswa lain!');
        redirect('rfid/register');
        return;
    }

    // UPDATE aman karena tidak duplicate
    $this->db->where('id', $id_siswa)->update('siswa', [
        'rfid_uid' => $uid
    ]);

    $this->session->set_flashdata('success', 'Kartu RFID berhasil ditautkan.');
    redirect('rfid/register');
}
public function auto()
{
    $this->load->view('rfid/auto_register');
}

// API dari fetch() untuk menyimpan UID otomatis
public function auto_register_save()
{
    $uid = $this->input->post('uid', TRUE);

    if (!$uid) {
        echo json_encode(['status' => false, 'message' => 'UID kosong']);
        return;
    }

    // Jika sudah ada di pending, tidak usah dimasukkan lagi
    $exist = $this->db->get_where('rfid_pending', ['uid' => $uid])->row();
    if ($exist) {
        echo json_encode([
            'status' => true,
            'message' => 'UID sudah tercatat sebelumnya'
        ]);
        return;
    }

    $this->db->insert('rfid_pending', [
        'uid' => $uid,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    echo json_encode([
        'status' => true,
        'message' => 'UID berhasil direkam'
    ]);
}

// daftar UID pending
public function pending()
{
    $data['pending'] = $this->db->order_by('created_at','desc')
                                ->get('rfid_pending')->result();
    $this->load->view('rfid/list_pending', $data);
}

// halaman match UID → siswa
public function match()
{
    $data['pending'] = $this->db->order_by('uid','asc')->get('rfid_pending')->result();
    $data['siswa'] = $this->db->order_by('nama','asc')->get('siswa')->result();
    $data['kelas'] = $this->db->order_by('nama','asc')->get('kelas')->result();

    $this->load->view('rfid/match', $data);
}

public function save_match()
{
    $uid = $this->input->post('uid');
    $id_siswa = $this->input->post('id_siswa');

    if (!$uid || !$id_siswa) {
        show_error("Data tidak valid");
    }

    // update ke siswa
    $this->db->where('id', $id_siswa)
             ->update('siswa', ['rfid_uid' => $uid]);

    // hapus dari pending
    $this->db->delete('rfid_pending', ['uid' => $uid]);

    $this->session->set_flashdata('success', 'Berhasil memetakan UID ke siswa.');
    redirect('rfid/match');
}
public function ajax_save_match()
{
    $uid = $this->input->post('uid');
    $id_siswa = $this->input->post('id_siswa');

    if (!$uid || !$id_siswa) {
        echo json_encode(['status' => false, 'message' => 'Data tidak lengkap']);
        return;
    }

    // Update siswa
    $this->db->where('id', $id_siswa)->update('siswa', [
        'rfid_uid' => $uid
    ]);

    // Hapus dari pending
    $this->db->delete('rfid_pending', ['uid' => $uid]);

    echo json_encode(['status' => true, 'message' => 'Berhasil ditautkan']);
}

}
