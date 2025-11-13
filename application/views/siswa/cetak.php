<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body {
        font-family: "dejavusans", sans-serif;
        font-size: 11pt;
        color: #000;
        line-height: 1.5;
    }

    h2 {
        text-align: center;
        font-size: 18pt;
        margin-bottom: 25px;
        color: #003366;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Judul tiap bagian */
    .section-title {
        font-weight: bold;
        font-size: 13pt;
        color: #004a99;
        background-color: #eaf3ff;
        padding: 6px 10px;
        border-left: 5px solid #007bff;
        margin-top: 25px;
        margin-bottom: 10px;
        border-radius: 3px;
    }

    /* Tabel tanpa garis */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }

    td {
        padding: 4px 10px; /* Diperbarui untuk lebih rapi */
        vertical-align: top;
    }

    .label {
        width: 35%;
        font-weight: bold;
        color: #000;
        /* --- Perubahan Kunci: Garis Vertikal --- */
        border-right: 1px solid #ccc; /* Garis pemisah */
        /* --- Akhir Perubahan Kunci --- */
    }

    .value {
        width: 65%;
        color: #333;
        padding-left: 10px; /* Menjaga jarak dari garis */
    }

    /* Tambah jarak antar bagian */
    .section-spacing {
        margin-top: 20px;
    }
</style>
</head>

<body>

<h2>DATA LENGKAP PESERTA DIDIK<br>SMKN 1 Cilimus</h2>

<div class="section-title">A. DATA PRIBADI</div>
<table>
<tr><td class="label">Nama Lengkap</td><td class="value"><?= $siswa->nama ?></td></tr>
<tr><td class="label">NIS</td><td class="value"><?= $siswa->nis ?></td></tr>
<tr><td class="label">Jenis Kelamin</td><td class="value"><?= $siswa->jk ?></td></tr>
<tr><td class="label">NISN</td><td class="value"><?= $siswa->nisn ?></td></tr>
<tr><td class="label">Tempat Lahir</td><td class="value"><?= $siswa->tempat_lahir ?></td></tr>
<tr><td class="label">Tanggal Lahir</td><td class="value"><?= $siswa->tgl_lahir ?></td></tr>
<tr><td class="label">Nomor KK</td><td class="value"><?= $siswa->nomor_kk ?></td></tr>
<tr><td class="label">NIK</td><td class="value"><?= $siswa->nik ?></td></tr>
<tr><td class="label">Anak Ke</td><td class="value"><?= $siswa->anak_keberapa ?></td></tr>
<tr><td class="label">Agama</td><td class="value"><?= $siswa->agama ?></td></tr>
<tr><td class="label">Alamat</td><td class="value"><?= $siswa->alamat ?></td></tr>
<tr><td class="label">RT</td><td class="value"><?= $siswa->rt ?></td></tr>
<tr><td class="label">RW</td><td class="value"><?= $siswa->rw ?></td></tr>
<tr><td class="label">Dusun</td><td class="value"><?= $siswa->dusun ?></td></tr>
<tr><td class="label">Kelurahan/Desa</td><td class="value"><?= $siswa->alamat ?></td></tr>
<tr><td class="label">Kecamatan</td><td class="value"><?= $siswa->kecamatan ?></td></tr>
<tr><td class="label">Kode POS</td><td class="value"><?= $siswa->kode_pos ?></td></tr>
<tr><td class="label">Jenis Tinggal</td><td class="value"><?= $siswa->jenis_tinggal ?></td></tr>
<tr><td class="label">Alat Transportasi</td><td class="value"><?= $siswa->alat_transportasi ?></td></tr>
<tr><td class="label">Telp. Rumah</td><td class="value"><?= $siswa->telp ?></td></tr>
<tr><td class="label">No. HP</td><td class="value"><?= $siswa->hp ?></td></tr>
<tr><td class="label">Email</td><td class="value"><?= $siswa->email ?></td></tr>
</table>
<br>
<div class="section-title">B. KESEJAHTERAAN PESERTA DIDIK</div>
<table>
<tr><td class="label">Penerima KPS</td><td class="value"><?= $siswa->penerima_kps ?></td></tr>
<tr><td class="label">Nomor KPS</td><td class="value"><?= $siswa->no_kps ?></td></tr>
</table>
<br>
<div class="section-title">C. DATA PERIODIK</div>
<table>
<tr><td class="label">Tinggi Badan</td><td class="value"><?= $siswa->tinggi_badan ?> cm</td></tr>
<tr><td class="label">Berat Badan</td><td class="value"><?= $siswa->berat_badan ?> kg</td></tr>
<tr><td class="label">Hobi</td><td class="value"><?= $siswa->hobi ?></td></tr>
<tr><td class="label">Cita-cita</td><td class="value"><?= $siswa->cita_cita ?></td></tr>
</table>
<br><br>
<div class="section-title">D. DATA PENDIDIKAN</div>
<table>
<tr><td class="label">Sekolah Asal</td><td class="value"><?= $siswa->sekolah_asal ?></td></tr>
<tr><td class="label">Nomor SKHUN</td><td class="value"><?= $siswa->skhun ?></td></tr>
</table>
<br><br><br><br>
<div class="section-title">E. DATA AYAH KANDUNG</div>
<table>
<tr><td class="label">Nama Ayah</td><td class="value"><?= $siswa->nama_ayah ?></td></tr>
<tr><td class="label">NIK Ayah</td><td class="value"><?= $siswa->nik_ayah ?></td></tr>
<tr><td class="label">Tahun Lahir Ayah</td><td class="value"><?= $siswa->tahun_lahir_ayah ?></td></tr>
<tr><td class="label">Pekerjaan Ayah</td><td class="value"><?= $siswa->pekerjaan_ayah ?></td></tr>
<tr><td class="label">Penghasilan Ayah</td><td class="value"><?= $siswa->penghasilan_ayah ?></td></tr>
</table>
<br>
<div class="section-title">F. DATA IBU KANDUNG</div>
<table>
<tr><td class="label">Nama Ibu</td><td class="value"><?= $siswa->nama_ibu ?></td></tr>
<tr><td class="label">NIK Ibu</td><td class="value"><?= $siswa->nik_ibu ?></td></tr>
<tr><td class="label">Tahun Lahir Ibu</td><td class="value"><?= $siswa->tahun_lahir_ibu ?></td></tr>
<tr><td class="label">Pendidikan Ibu</td><td class="value"><?= $siswa->pendidikan_ibu ?></td></tr>
<tr><td class="label">Pekerjaan Ibu</td><td class="value"><?= $siswa->pekerjaan_ibu ?></td></tr>
<tr><td class="label">Penghasilan Ibu</td><td class="value"><?= $siswa->penghasilan_ibu ?></td></tr>
</table>
<br>
<div class="section-title">G. DATA WALI</div>
<table>
<tr><td class="label">Nama Wali</td><td class="value"><?= $siswa->nama_wali ?></td></tr>
<tr><td class="label">NIK Wali</td><td class="value"><?= $siswa->nik_wali ?></td></tr>
<tr><td class="label">Tahun Lahir Wali</td><td class="value"><?= $siswa->tahun_lahir_wali ?></td></tr>
<tr><td class="label">Pendidikan Wali</td><td class="value"><?= $siswa->pendidikan_wali ?></td></tr>
<tr><td class="label">Pekerjaan Wali</td><td class="value"><?= $siswa->pekerjaan_wali ?></td></tr>
<tr><td class="label">Penghasilan Wali</td><td class="value"><?= $siswa->penghasilan_wali ?></td></tr>
</table>
<br>
<div class="section-title">H. DATA AKADEMIK</div>
<table>
<tr><td class="label">Kelas/Rombel</td><td class="value"><?= $siswa->nama_kelas ?></td></tr>
<tr><td class="label">Tahun Ajaran</td><td class="value"><?= $siswa->tahun_ajaran ?></td></tr>
<tr><td class="label">Status</td><td class="value"><?= ucfirst($siswa->status) ?></td></tr>
</table>

</body>
</html>