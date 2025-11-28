<!DOCTYPE html>
<html>
<head>
<title>Auto Register Kartu RFID</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="p-4">

<h3>Auto Register Kartu RFID</h3>
<p>Tempelkan kartu sebanyak-banyaknya. UID akan otomatis tersimpan.</p>

<div class="alert alert-info">
    Mode ini cocok untuk mendaftarkan 2000 kartu dengan cepat.
</div>

<div id="log" class="p-3 mb-3 border rounded" style="height:200px; overflow:auto;">
    <em>Menunggu kartu...</em>
</div>

<input id="reader" autofocus style="opacity:0; position:absolute; left:-9999px;">

<script>
let buffer = "";
let timer = null;
const log = document.getElementById("log");
const reader = document.getElementById("reader");

// pastikan fokus ke input setiap detik
setInterval(() => reader.focus(), 800);

// gunakan keydown (lebih kompatibel)
reader.addEventListener("keydown", function(e){

    if (e.key === "Enter") {
        let uid = buffer.trim();
        buffer = "";

        if (uid.length > 0) kirim(uid);
        return;
    }

    // tambahkan karakter
    if (e.key.length === 1) buffer += e.key;

    clearTimeout(timer);
    timer = setTimeout(() => buffer = "", 300);
});

function kirim(uid) {
    addLog("Scan: " + uid);

    fetch("<?= site_url('rfid/auto_register_save') ?>", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: "uid=" + encodeURIComponent(uid)
        + "&<?= $this->security->get_csrf_token_name(); ?>=<?= $this->security->get_csrf_hash(); ?>"
})

    .then(res => res.json())
    .then(data => {
        addLog("→ " + data.message);
    })
}

function addLog(text){
    log.innerHTML += text + "<br>";
    log.scrollTop = log.scrollHeight;
}
</script>


</body>
</html>
