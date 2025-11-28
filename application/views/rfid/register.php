<!DOCTYPE html>
<html>
<head>
<title>Register Kartu RFID</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="p-4">

<h3>Register Kartu RFID</h3>
<p>Tempelkan kartu RFID pada reader, lalu pilih siswa dan simpan.</p>

<?php if ($this->session->flashdata('success')): ?>
<div class="alert alert-success">
    <?= $this->session->flashdata('success') ?>
</div>
<?php endif; ?>

<!-- form -->
<form method="POST" action="<?= site_url('rfid/save') ?>">
<?php if ($this->session->flashdata('error')): ?>
<div class="alert alert-danger">
    <?= $this->session->flashdata('error') ?>
</div>
<?php endif; ?>

    <!-- CSRF wajib karena kamu aktifkan -->
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>"
           value="<?= $this->security->get_csrf_hash(); ?>">

    <div class="mb-3">
        <label>UID RFID</label>
        <input id="uid" name="uid" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Pilih Siswa</label>
        <select name="id_siswa" class="form-control" required>
            <option value="">-- pilih siswa --</option>
            <?php foreach ($siswa as $s): ?>
                <option value="<?= $s->id ?>">
                    <?= htmlspecialchars($s->nama) ?> (<?= $s->nis ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <button class="btn btn-primary">Simpan</button>
</form>

<!-- input hidden untuk menerima UID dari reader -->
<input id="rfid_input" autofocus style="opacity:0; position:absolute; left:-9999px;">

<script>
const hidden = document.getElementById('rfid_input');
const uidField = document.getElementById('uid');

hidden.addEventListener('keyup', function(e) {

    // Reader biasanya kirim UID + ENTER
    if (e.key === 'Enter') {
        uidField.value = hidden.value.trim();
        hidden.value = '';
        uidField.focus(); // pindah fokus supaya terlihat
    }
});
</script>


</body>
</html>
