<!-- ================= HEADER.PHP ================= -->
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? $title.' - Mutases' : 'Mutases' ?></title>

  <!-- FontAwesome -->
  <link href="<?= base_url('assets/sbadmin2/vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
  <!-- Bootstrap -->
  <link href="<?= base_url('assets/sbadmin2/vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
  <!-- SB Admin 2 -->
  <link href="<?= base_url('assets/sbadmin2/css/sb-admin-2.min.css') ?>" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="<?= base_url('assets/css/custom-style.css') ?>" rel="stylesheet">
  <style>

/* ===============================
   LIGHT MODE — SOFT BLUE THEME
   =============================== */

/* Warna dasar agak kebiruan lembut */
body.light-mode {
    background-color: #f0f4ff !important; /* soft blue-white */
    color: #1a1a1a !important;
}

/* Wrapper & Content lebih redup (soft white-blue) */
body.light-mode #wrapper,
body.light-mode #content-wrapper,
body.light-mode #content,
body.light-mode .container-fluid {
    background-color: #f7f9ff !important; /* very soft blue white */
    color: #000 !important;
}

/* Card tema terang lembut */
body.light-mode .card,
body.light-mode .card-body,
body.light-mode .card-header {
    background-color: #ffffff !important;
    border: 1px solid #e3e7ff !important; /* border sedikit biru */
    box-shadow: 0 2px 6px rgba(0, 50, 150, 0.05) !important;
    color: #000 !important;
}

/* Tabel header biru muda */
body.light-mode .table thead th {
    background-color: #e4ebff !important;
    color: #000 !important;
}

/* Baris tabel tetap putih */
body.light-mode .table tbody tr {
    background-color: #ffffff !important;
}

/* Hover tabel sedikit kebiruan */
body.light-mode .table tbody tr:hover {
    background-color: #eef3ff !important;
}

/* Topbar putih kebiruan */
body.light-mode .topbar {
    background-color: #f9fbff !important;
    border-bottom: 1px solid #dce4ff !important;
}

/* Sidebar tetap gradient biru */
body.light-mode .sidebar {
    background: linear-gradient(180deg, #4e73df 10%, #224abe 100%) !important;
}

/* Warna teks umum */
body.light-mode h1,
body.light-mode h2,
body.light-mode h3,
body.light-mode h4,
body.light-mode p,
body.light-mode span,
body.light-mode label,
body.light-mode td,
body.light-mode th {
    color: #1a1a1a !important;
}

</style>

</head>

<body id="page-top">
  <!-- Page Wrapper -->
  <div id="wrapper">
