<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Mutasi Siswa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <style>
    :root {
      --bg-light: #f8f9fa;
      --text-light: #333;
      --card-light: #fff;
      --bg-dark: #121212;
      --text-dark: #eaeaea;
      --card-dark: #1f1f1f;
    }

    body {
      background: var(--bg-light);
      color: var(--text-light);
      font-family: 'Segoe UI', sans-serif;
      transition: background 0.3s, color 0.3s;
    }

    body.dark-mode {
      background: var(--bg-dark);
      color: var(--text-dark);
    }

    header {
      background: linear-gradient(90deg, #007bff, #00bcd4);
      color: #fff;
      padding: 1rem 0;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    header .brand {
      font-weight: 700;
      font-size: 1.4rem;
      letter-spacing: .5px;
    }

    header .actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    header a.btn-login {
      color: #007bff;
      background: #fff;
      border-radius: 50px;
      padding: .5rem 1.2rem;
      font-weight: 500;
      transition: 0.3s;
    }

    header a.btn-login:hover {
      background: #e2e6ea;
      text-decoration: none;
    }

    header .btn-toggle {
      background: #fff;
      border: none;
      border-radius: 50%;
      width: 38px;
      height: 38px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: 0.3s;
    }

    header .btn-toggle:hover {
      background: #e2e6ea;
    }

    h2.section-title {
      font-weight: 700;
      margin-bottom: 1.5rem;
      text-align: center;
    }

    .dark-mode h2.section-title {
      color: #eaeaea;
    }

    footer {
      padding: 2rem 0;
      background: var(--card-light);
      border-top: 1px solid #ddd;
      margin-top: 3rem;
      color: #777;
      font-size: .9rem;
      transition: background 0.3s;
    }

    body.dark-mode footer {
      background: var(--card-dark);
      color: #bbb;
      border-color: #333;
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      background: var(--card-light);
      transition: background 0.3s;
    }

    body.dark-mode .card {
      background: var(--card-dark);
      box-shadow: 0 2px 10px rgba(255,255,255,0.05);
    }
  </style>
</head>

<body>

<!-- 🔹 HEADER -->
<header>
  <div class="container d-flex justify-content-between align-items-center">
    <div class="brand">
      <i class="fas fa-chart-line"></i> Dashboard Mutasi Siswa
    </div>
    <div class="actions">
      <button class="btn-toggle" id="toggleDark" title="Ganti Tema">
        <i class="fas fa-moon"></i>
      </button>
      <a href="<?= base_url('index.php/auth/login') ?>" class="btn-login">
        <i class="fas fa-sign-in-alt"></i> Login
      </a>
    </div>
  </div>
</header>

<!-- 🔹 MAIN CONTENT -->
<main class="container my-5">
  <div class="text-center mb-4">
    <h3 class="fw-bold mb-2">Selamat Datang di Sistem Mutasi Siswa 👋</h3>
    <p class="text-muted" id="currentTime">
      Data per <strong>-</strong>
    </p>
  </div>

  <h2 class="section-title mb-5">Statistik Mutasi Siswa Sekolah</h2>

  <?php $this->load->view('dashboard/index', [
      'rombel' => $rombel,
      'aktif' => $aktif,
      'keluar' => $keluar,
      'lulus' => $lulus
  ]); ?>
</main>

<!-- 🔹 FOOTER -->
<footer class="text-center">
  <div class="container">
    &copy; <?= date('Y') ?> Sistem Mutasi Siswa — Dibuat dengan 💙 oleh <strong>Nazmudin</strong>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// 🕒 Realtime tanggal & jam
function updateTime() {
  const el = document.getElementById('currentTime');
  const now = new Date();
  const hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][now.getDay()];
  const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][now.getMonth()];
  const jam = now.getHours().toString().padStart(2,'0');
  const menit = now.getMinutes().toString().padStart(2,'0');
  const detik = now.getSeconds().toString().padStart(2,'0');
  const tanggalStr = `${hari}, ${now.getDate()} ${bulan} ${now.getFullYear()} — ${jam}:${menit}:${detik} WIB`;
  el.innerHTML = `Data per <strong>${tanggalStr}</strong>`;
}
setInterval(updateTime, 1000);
updateTime();

// 🌗 Dark mode toggle
const btnToggle = document.getElementById('toggleDark');
btnToggle.addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');
  const icon = btnToggle.querySelector('i');
  icon.classList.toggle('fa-moon');
  icon.classList.toggle('fa-sun');
});
</script>

</body>
</html>
