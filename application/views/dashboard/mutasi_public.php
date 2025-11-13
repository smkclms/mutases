<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Siswa Mutasi — Sistem Mutasi Siswa</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <style>
    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'Segoe UI', sans-serif;
      transition: background 0.3s, color 0.3s;
    }
    :root {
      --bg: #f8f9fa;
      --text: #212529;
      --card: #fff;
      --table-header: #007bff;
      --table-header-text: #fff;
    }
    body.dark {
      --bg: #1e1e1e;
      --text: #eaeaea;
      --card: #2b2b2b;
      --table-header: #333;
      --table-header-text: #fff;
    }
    header {
      background: linear-gradient(90deg, #007bff, #00bcd4);
      color: #fff;
      padding: 1rem 0;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    header .brand { font-weight: 700; font-size: 1.3rem; }
    header .actions a {
      color: #007bff;
      background: #fff;
      border-radius: 50px;
      padding: .45rem 1.1rem;
      font-weight: 500;
      transition: 0.3s;
      text-decoration: none;
    }
    header .actions a:hover { background: #e2e6ea; }
    .btn-toggle {
      border: none;
      background: transparent;
      color: #fff;
      font-size: 1.3rem;
      cursor: pointer;
      margin-right: 10px;
    }
    .card { background: var(--card); border: none; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
    table th { background: var(--table-header); color: var(--table-header-text); }
    footer { padding: 2rem 0; background: var(--card); border-top: 1px solid #ddd; margin-top: 3rem; color: var(--text); font-size: .9rem; }
  </style>
</head>

<body>
<header>
  <div class="container d-flex justify-content-between align-items-center">
    <div class="brand"><i class="fas fa-exchange-alt"></i> Data Siswa Mutasi</div>
    <div class="actions d-flex align-items-center">
      <button id="toggleDark" class="btn-toggle" title="Ganti Tema"><i class="fas fa-moon"></i></button>
      <a href="<?= base_url('index.php/dashboard') ?>" class="me-2">
  <i class="fas fa-home"></i> Dashboard
</a>

<!-- <div class="dropdown">
  <button class="btn-login dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="fas fa-sign-in-alt"></i> Login
  </button>

  <ul class="dropdown-menu dropdown-menu-end shadow">
    <li>
      <a class="dropdown-item" href="<?= base_url('index.php/auth/login') ?>">
        <i class="fas fa-user-shield text-primary"></i> Login Admin
      </a>
    </li>
    <li>
      <a class="dropdown-item" href="<?= base_url('index.php/SiswaAuth') ?>">
        <i class="fas fa-user-graduate text-success"></i> Login Siswa
      </a>
    </li>
  </ul>
</div> -->

  </div>
</header>

<main class="container my-5">
  <h3 class="text-center mb-4">Laporan Mutasi Siswa Tahun <?= $tahun ?></h3>

  <!-- Filter -->
  <form method="get" class="row g-3 mb-4">
    <div class="col-md-3">
      <label><strong>Kelas</strong></label>
      <select name="kelas" class="form-control form-control-sm">
        <option value="">Semua Kelas</option>
        <?php foreach($kelas_list as $k): ?>
          <option value="<?= $k->id ?>" <?= ($this->input->get('kelas') == $k->id ? 'selected' : '') ?>><?= $k->nama ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <label><strong>Jenis Mutasi</strong></label>
      <select name="jenis" class="form-control form-control-sm">
        <option value="">Semua</option>
        <option value="masuk" <?= ($this->input->get('jenis') == 'masuk' ? 'selected' : '') ?>>Masuk</option>
        <option value="keluar" <?= ($this->input->get('jenis') == 'keluar' ? 'selected' : '') ?>>Keluar</option>
      </select>
    </div>

    <div class="col-md-3">
      <label><strong>Cari Nama</strong></label>
      <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama siswa..." value="<?= $this->input->get('search') ?>">
    </div>

    <div class="col-md-3" style="margin-top: 25px;">
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
      <a href="<?= site_url('dashboard/mutasi') ?>" class="btn btn-secondary btn-sm"><i class="fas fa-sync-alt"></i> Reset</a>
    </div>
  </form>

  <!-- Export -->
  <div class="mb-3">
    <a href="<?= site_url('laporan/export_excel?' . http_build_query($_GET)) ?>" class="btn btn-success btn-sm">
      <i class="fas fa-file-excel"></i> Excel
    </a>
    <a href="<?= site_url('laporan/export_pdf?' . http_build_query($_GET)) ?>" class="btn btn-danger btn-sm">
      <i class="fas fa-file-pdf"></i> PDF
    </a>
  </div>

  <!-- Tabel -->
  <div class="card p-3">
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-sm align-middle">
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Siswa</th>
            <th>NIS</th>
            <th>NISN</th>
            <th>Kelas Asal</th>
            <th>Jenis</th>
            <th>Jenis Keluar</th>
            <th>Tanggal</th>
            <th>Alasan</th>
            <th>Kelas / Sekolah Tujuan</th>
            <th>Tahun Ajaran</th>
            <th>Dibuat Oleh</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($mutasi): $no=1+$this->input->get('page'); foreach($mutasi as $m): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= $m->nama_siswa ?></td>
            <td><?= $m->nis ?></td>
            <td><?= $m->nisn ?></td>
            <td><?= $m->kelas_asal ?></td>
            <td><?= ucfirst($m->jenis) ?></td>
            <td><?= $m->jenis == 'keluar' ? ($m->jenis_keluar ?: '-') : '-' ?></td>
            <td><?= !empty($m->tanggal) ? date('d-m-Y', strtotime($m->tanggal)) : '-' ?></td>
            <td><?= $m->alasan ?: '-' ?></td>
            <td><?= $m->jenis == 'keluar' ? ($m->tujuan_sekolah ?: '-') : ($m->kelas_tujuan ?: '-') ?></td>
            <td><?= $m->tahun_ajaran ?></td>
            <td><?= $m->dibuat_oleh ?: '-' ?></td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="12" class="text-center text-muted">Tidak ada data mutasi ditemukan.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?= $pagination ?>
  </div>
</main>

<footer class="text-center text-muted mt-4 mb-3">
  <small>&copy; <?= date('Y') ?> Sistem Mutasi Siswa — Dibuat oleh <strong>Nazmudin</strong></small>
</footer>

<script>
const toggleDark = document.getElementById('toggleDark');
toggleDark.addEventListener('click', () => {
  document.body.classList.toggle('dark');
  localStorage.setItem('darkMode', document.body.classList.contains('dark'));
});
if (localStorage.getItem('darkMode') === 'true') document.body.classList.add('dark');
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
