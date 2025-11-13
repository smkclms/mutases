

<div class="container-fluid">

    <!-- Judul Halaman -->
    <div class="text-center mb-4">
        <h3 class="font-weight-bold text-primary">Biodata Lengkap Siswa</h3>
        <p class="text-muted">Informasi lengkap identitas peserta didik</p>
    </div>

    <!-- STYLE -->
    <style>
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            background: #e8f1ff;
            padding: 10px 12px;
            border-left: 4px solid #4e73df;
            margin-top: 25px;
            border-radius: 4px;
        }

        .bio-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 10px;
        }

        .bio-table td {
            border: 1px solid #dce3f1;
            padding: 8px 10px;
            vertical-align: top;
        }

        .bio-label {
            width: 35%;
            font-weight: 600;
            background: #f8f9fc;
        }

        .bio-value {
            background: #ffffff;
        }

        @media (max-width: 768px) {
            .bio-label {
                width: 45%;
            }
        }
        
    </style>

    <!-- CARD WRAPPER -->
    <div class="card shadow mb-4">
        <div class="card-body">

            <!-- =================== A. DATA PRIBADI =================== -->
            <div class="section-title">A. DATA PRIBADI</div>
            <div class="table-responsive">
    <table class="table table-bordered bio-table">
                <tr><td class="bio-label">Nama Lengkap</td><td class="bio-value"><?= $siswa->nama ?></td></tr>
                <tr><td class="bio-label">NIS</td><td class="bio-value"><?= $siswa->nis ?></td></tr>
                <tr><td class="bio-label">Jenis Kelamin</td><td class="bio-value"><?= $siswa->jk ?></td></tr>
                <tr><td class="bio-label">NISN</td><td class="bio-value"><?= $siswa->nisn ?></td></tr>
                <tr><td class="bio-label">Tempat Lahir</td><td class="bio-value"><?= $siswa->tempat_lahir ?></td></tr>
                <tr><td class="bio-label">Tanggal Lahir</td><td class="bio-value"><?= $siswa->tgl_lahir ?></td></tr>
                <tr><td class="bio-label">Nomor KK</td><td class="bio-value"><?= $siswa->nomor_kk ?></td></tr>
                <tr><td class="bio-label">NIK</td><td class="bio-value"><?= $siswa->nik ?></td></tr>
                <tr><td class="bio-label">Anak Ke</td><td class="bio-value"><?= $siswa->anak_keberapa ?></td></tr>
                <tr><td class="bio-label">Agama</td><td class="bio-value"><?= $siswa->agama ?></td></tr>
                <tr><td class="bio-label">Alamat</td><td class="bio-value"><?= $siswa->alamat ?></td></tr>
                <tr><td class="bio-label">RT</td><td class="bio-value"><?= $siswa->rt ?></td></tr>
                <tr><td class="bio-label">RW</td><td class="bio-value"><?= $siswa->rw ?></td></tr>
                <tr><td class="bio-label">Dusun</td><td class="bio-value"><?= $siswa->dusun ?></td></tr>
                <tr><td class="bio-label">Kelurahan/Desa</td><td class="bio-value"><?= $siswa->dusun ?></td></tr>
                <tr><td class="bio-label">Kecamatan</td><td class="bio-value"><?= $siswa->kecamatan ?></td></tr>
                <tr><td class="bio-label">Kode POS</td><td class="bio-value"><?= $siswa->kode_pos ?></td></tr>
                <tr><td class="bio-label">Jenis Tinggal</td><td class="bio-value"><?= $siswa->jenis_tinggal ?></td></tr>
                <tr><td class="bio-label">Alat Transportasi</td><td class="bio-value"><?= $siswa->alat_transportasi ?></td></tr>
                <tr><td class="bio-label">Telp. Rumah</td><td class="bio-value"><?= $siswa->telp ?></td></tr>
                <tr><td class="bio-label">No. HP</td><td class="bio-value"><?= $siswa->hp ?></td></tr>
                <tr><td class="bio-label">Email</td><td class="bio-value"><?= $siswa->email ?></td></tr>
            </table>
    </div>

            <!-- =================== B. KESEJAHTERAAN =================== -->
            <div class="section-title">B. KESEJAHTERAAN PESERTA DIDIK</div>
            <div class="table-responsive">
    <table class="table table-bordered bio-table">
                <tr><td class="bio-label">Penerima KPS</td><td class="bio-value"><?= $siswa->penerima_kps ?></td></tr>
                <tr><td class="bio-label">Nomor KPS</td><td class="bio-value"><?= $siswa->no_kps ?></td></tr>
            </table>
        </div>
            <!-- =================== C. PERIODIK =================== -->
            <div class="section-title">C. DATA PERIODIK</div>
            <div class="table-responsive">
    <table class="table table-bordered bio-table">
                <tr><td class="bio-label">Tinggi Badan</td><td class="bio-value"><?= $siswa->tinggi_badan ?> cm</td></tr>
                <tr><td class="bio-label">Berat Badan</td><td class="bio-value"><?= $siswa->berat_badan ?> kg</td></tr>
                <tr><td class="bio-label">Hobi</td><td class="bio-value"><?= $siswa->hobi ?></td></tr>
                <tr><td class="bio-label">Cita-cita</td><td class="bio-value"><?= $siswa->cita_cita ?></td></tr>
            </table>
        </div>
            <!-- =================== D. DATA PENDIDIKAN =================== -->
            <div class="section-title">D. DATA PENDIDIKAN</div>
            <div class="table-responsive">
    <table class="table table-bordered bio-table">
                <tr><td class="bio-label">Sekolah Asal</td><td class="bio-value"><?= $siswa->sekolah_asal ?></td></tr>
                <tr><td class="bio-label">Nomor SKHUN</td><td class="bio-value"><?= $siswa->skhun ?></td></tr>
            </table>
        </div>
            <!-- =================== E. AYAH =================== -->
            <div class="section-title">E. DATA AYAH KANDUNG</div>
            <div class="table-responsive">
    <table class="table table-bordered bio-table">
                <tr><td class="bio-label">Nama Ayah</td><td class="bio-value"><?= $siswa->nama_ayah ?></td></tr>
                <tr><td class="bio-label">NIK Ayah</td><td class="bio-value"><?= $siswa->nik_ayah ?></td></tr>
                <tr><td class="bio-label">Tahun Lahir Ayah</td><td class="bio-value"><?= $siswa->tahun_lahir_ayah ?></td></tr>
                <tr><td class="bio-label">Pendidikan Ayah</td><td class="bio-value"><?= $siswa->pendidikan_ayah ?></td></tr>
                <tr><td class="bio-label">Pekerjaan Ayah</td><td class="bio-value"><?= $siswa->pekerjaan_ayah ?></td></tr>
                <tr><td class="bio-label">Penghasilan Ayah</td><td class="bio-value"><?= $siswa->penghasilan_ayah ?></td></tr>
            </table>
        </div>
            <!-- =================== F. IBU =================== -->
            <div class="section-title">F. DATA IBU KANDUNG</div>
            <div class="table-responsive">
    <table class="table table-bordered bio-table">
                <tr><td class="bio-label">Nama Ibu</td><td class="bio-value"><?= $siswa->nama_ibu ?></td></tr>
                <tr><td class="bio-label">NIK Ibu</td><td class="bio-value"><?= $siswa->nik_ibu ?></td></tr>
                <tr><td class="bio-label">Tahun Lahir Ibu</td><td class="bio-value"><?= $siswa->tahun_lahir_ibu ?></td></tr>
                <tr><td class="bio-label">Pendidikan Ibu</td><td class="bio-value"><?= $siswa->pendidikan_ibu ?></td></tr>
                <tr><td class="bio-label">Pekerjaan Ibu</td><td class="bio-value"><?= $siswa->pekerjaan_ibu ?></td></tr>
                <tr><td class="bio-label">Penghasilan Ibu</td><td class="bio-value"><?= $siswa->penghasilan_ibu ?></td></tr>
            </table>
        </div>
            <!-- =================== G. WALI =================== -->
            <div class="section-title">G. DATA WALI</div>
            <div class="table-responsive">
    <table class="table table-bordered bio-table">
                <tr><td class="bio-label">Nama Wali</td><td class="bio-value"><?= $siswa->nama_wali ?></td></tr>
                <tr><td class="bio-label">NIK Wali</td><td class="bio-value"><?= $siswa->nik_wali ?></td></tr>
                <tr><td class="bio-label">Tahun Lahir Wali</td><td class="bio-value"><?= $siswa->tahun_lahir_wali ?></td></tr>
                <tr><td class="bio-label">Pendidikan Wali</td><td class="bio-value"><?= $siswa->pendidikan_wali ?></td></tr>
                <tr><td class="bio-label">Pekerjaan Wali</td><td class="bio-value"><?= $siswa->pekerjaan_wali ?></td></tr>
                <tr><td class="bio-label">Penghasilan Wali</td><td class="bio-value"><?= $siswa->penghasilan_wali ?></td></tr>
            </table>
        </div>
            <!-- =================== H. AKADEMIK =================== -->
            <div class="section-title">H. DATA AKADEMIK</div>
            <div class="table-responsive">
    <table class="table table-bordered bio-table">
                <tr><td class="bio-label">Kelas / Rombel</td><td class="bio-value"><?= $siswa->nama_kelas ?></td></tr>
                <tr><td class="bio-label">Tahun Ajaran</td><td class="bio-value"><?= $siswa->tahun_ajaran ?></td></tr>
                <tr><td class="bio-label">Status</td><td class="bio-value"><?= ucfirst($siswa->status) ?></td></tr>
            </table>
        </div>
            <a href="<?= site_url('SiswaDashboard/cetak') ?>" class="btn btn-primary mt-3">
                <i class="fas fa-file-pdf"></i> Cetak Biodata PDF
            </a>

        </div>
    </div>

</div>
