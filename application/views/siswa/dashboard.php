
<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h4 mb-4 text-gray-800">Selamat Datang, <?= $siswa->nama ?> 👋</h1>

    <div class="row">

        <!-- Card NISN -->
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        NISN
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?= $siswa->nisn ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Kelas -->
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Kelas / Rombel
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?= $siswa->nama_kelas ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Tahun Ajaran -->
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Tahun Ajaran
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?= $siswa->tahun_ajaran ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Status -->
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Status
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?= ucfirst($siswa->status) ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- LINK BIODATA -->
    <div class="mt-4">
        <a href="<?= site_url('SiswaDashboard/biodata') ?>" class="btn btn-primary btn-lg shadow-sm">
            <i class="fas fa-id-card"></i> Lihat Biodata Lengkap
        </a>
    </div>

</div>
<!-- End Page Content -->

<?php $this->load->view('siswa/layout/footer'); ?>
