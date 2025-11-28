<!DOCTYPE html>
<html>
<head>
    <title>Scan RFID Absensi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body {
            background: #f4f6f9;
            font-size: 16px;             /* lebih kecil */
            padding-top: 25px;
        }

        .scan-container {
            max-width: 550px;            /* lebih ramping */
            margin: auto;
        }

        .card-scan {
            border-radius: 14px;
            padding: 25px;               /* diperkecil */
            box-shadow: 0 6px 14px rgba(0,0,0,0.15);
            background: white;
            animation: fadeIn .3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        #uid_box {
            font-size: 32px;             /* lebih kecil */
            font-weight: 700;
            color: #007bff;
        }

        .scan-title {
            font-size: 26px;             /* lebih kecil */
            font-weight: 800;
            text-align: center;
            margin-bottom: 18px;
        }

        .card-result {
            padding: 22px;               /* box lebih kecil */
            border-radius: 14px;
            color: white;
            text-align: center;
            min-height: 110px;
            transition: all .25s ease;
        }

        .bg-success-custom { background: #28a745; }
        .bg-late-custom    { background: #ffc107; color: #000 !important; }
        .bg-info-custom    { background: #17a2b8; }
        .bg-danger-custom  { background: #dc3545; }

        .loading-dot::after {
            content: "...";
            animation: dots 1.2s steps(3, end) infinite;
        }

        @keyframes dots {
            0%, 20% { content: ""; }
            40% { content: "."; }
            60% { content: ".."; }
            80%,100% { content: "..."; }
        }

        @keyframes shake {
            0% { transform: translateX(0px); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
            100% { transform: translateX(0px); }
        }
        .shake { animation: shake 0.35s ease; }

        .icon-x {
            font-size: 48px;        /* icon diperkecil */
            margin-bottom: 8px;
        }

    </style>
</head>

<body onload="document.getElementById('reader').focus()">

<div class="scan-container">
    <div class="card card-scan">

        <h3 class="text-center mb-3 fw-bold">Scan RFID Absensi</h3>
        <p class="text-center text-muted mb-3">Tempelkan kartu pada reader...</p>

        <div id="uid_box" class="text-center mb-2">UID: -</div>

        <h2 id="scanTitle" class="scan-title"></h2>

        <div id="result" class="card-result bg-info-custom text-center">
            Menunggu kartu<span class="loading-dot"></span>
        </div>

        <input id="reader" autofocus style="opacity:0; position:absolute; left:-9999px;">

    </div>
</div>


<script>
let buffer = "";
let timer = null;
let lockScan = false;

document.getElementById("reader").addEventListener("keypress", function (e) {

    if (lockScan) return;

    if (e.key === "Enter") {
        let uid = buffer.trim();
        buffer = "";
        if (uid.length > 0) prosesUID(uid);
        return;
    }

    buffer += e.key;
    clearTimeout(timer);
    timer = setTimeout(() => buffer = "", 300);
});

function resetScanUI() {
    lockScan = false;

    let resultBox = document.getElementById("result");
    let scanTitle = document.getElementById("scanTitle");

    scanTitle.innerText = "";
    resultBox.className = "card-result bg-info-custom text-center";
    resultBox.innerHTML = 'Menunggu kartu<span class="loading-dot"></span>';

    document.getElementById("uid_box").innerHTML = "UID: -";
    document.getElementById("reader").focus();
}

function prosesUID(uid) {

    lockScan = true;

    let resultBox = document.getElementById("result");
    let scanTitle = document.getElementById("scanTitle");

    document.getElementById("uid_box").innerHTML = "UID: <b>" + uid + "</b>";

    scanTitle.innerText = "SCAN BERHASIL";
    resultBox.innerHTML = "Memproses<span class='loading-dot'></span>";
    resultBox.className = "card-result bg-info-custom text-center";

    let csrfName = "<?= $this->security->get_csrf_token_name(); ?>";
    let csrfHash = "<?= $this->security->get_csrf_hash(); ?>";

    fetch("<?= site_url('RfidAbsensi/scan'); ?>", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: csrfName + "=" + csrfHash + "&uid=" + encodeURIComponent(uid)
    })
    .then(response => response.json())
    .then(data => {

        resultBox.className = "card-result text-center";

        /* ===== FAIL ===== */
        if (data.status === false) {

            scanTitle.innerText = "SCAN GAGAL";
            resultBox.classList.add("bg-danger-custom", "shake");
            setTimeout(()=>resultBox.classList.remove("shake"), 500);

            resultBox.innerHTML = `
                <div class="icon-x">✖</div>
                <h4 class="fw-bold mb-1">Kartu Tidak Terdaftar</h4>
                <p class="m-0" style="font-size:14px;">Silakan cek kembali kartu siswa.</p>
            `;

            setTimeout(resetScanUI, 2200);
            return;
        }

        /* ===== SUDAH PULANG ===== */
        if (data.type === "sudah_pulang") {
            resultBox.classList.add("bg-info-custom");
            resultBox.innerHTML = `
                <h4 class="fw-bold">${data.nama}</h4>
                <p class="m-0">Anda sudah absen pulang hari ini.</p>
            `;
            setTimeout(resetScanUI, 2200);
            return;
        }

        /* ===== BELUM WAKTU PULANG ===== */
        if (data.type === "belum_waktu") {

            resultBox.classList.add("bg-danger-custom", "shake");
            setTimeout(()=>resultBox.classList.remove("shake"), 500);

            resultBox.innerHTML = `
                <div class="icon-x">✖</div>
                <h4 class="fw-bold mb-1">Belum Waktunya Pulang</h4>
                <p class="m-0">${data.nama}</p>
                <p class="m-0" style="font-size:14px;">Sekarang: <b>${data.jam_now}</b></p>
                <p class="m-0" style="font-size:14px;">Pulang: <b>${data.waktu_pulang}</b></p>
            `;
            setTimeout(resetScanUI, 2500);
            return;
        }

        /* ===== PULANG ===== */
        if (data.type === "pulang") {
            resultBox.classList.add("bg-info-custom");
            resultBox.innerHTML = `
                <h4 class="fw-bold">${data.nama}</h4>
                <p class="m-0">Pulang: ${data.jam_pulang}</p>
            `;
            setTimeout(resetScanUI, 2200);
            return;
        }

        /* ===== TERLAMBAT ===== */
        if (data.status === "Terlambat") {
            resultBox.classList.add("bg-late-custom");
            resultBox.innerHTML = `
                <h4 class="fw-bold">${data.nama}</h4>
                <p class="m-0">Terlambat — ${data.jam}</p>
            `;
            setTimeout(resetScanUI, 2200);
            return;
        }

        /* ===== MASUK ===== */
        if (data.type === "masuk") {
            resultBox.classList.add("bg-success-custom");
            resultBox.innerHTML = `
                <h4 class="fw-bold">${data.nama}</h4>
                <p class="m-0">Masuk: ${data.jam}</p>
            `;
            setTimeout(resetScanUI, 2200);
            return;
        }

    })
    .catch(err => {
        resultBox.className = "card-result bg-danger-custom text-center";
        resultBox.innerHTML = "Error: " + err;
        setTimeout(resetScanUI, 2500);
    });
}
</script>

</body>
</html>
