<!DOCTYPE html>
<html>
<head>
<title>Scan QR Siswa</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://unpkg.com/html5-qrcode"></script>

<style>
    /* Supaya area scanner berada tepat di tengah */
    .scan-wrapper {
        width: 100%;
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    #reader {
        width: 100%;
        max-width: 400px;
    }
</style>

</head>

<body class="p-4">

<!-- === KUNCI PERANGKAT PETUGAS === -->
<script>
    document.cookie = "petugas_scan=OK; path=/; SameSite=Lax";
</script>

<h3 class="text-center">Scan Kartu Siswa</h3>
<p class="text-center">Silakan arahkan QR Code kartu siswa ke kamera.</p>

<!-- WRAPPER UNTUK MEMBUAT SCANNER TENGAH -->
<div class="scan-wrapper">
    <div id="reader"></div>
</div>

<script>
const BASE_URL = "<?= base_url() ?>";

// === SCAN PROSES ===
function onScanSuccess(decodedText) {

    let parts = decodedText.split('/');
    let token = parts[parts.length - 1];

    fetch(BASE_URL + "index.php/izin/scan_process?token=" + token, {
        headers: {
            "X-Scanner": "MUTASES"
        }
    })
    .then(res => res.text())
    .then(url => {
        if (url === "403") {
            alert("Akses ditolak! Hanya perangkat petugas yang boleh scan.");
        } else {
            window.location.href = url;
        }
    });
}

var html5QrcodeScanner = new Html5QrcodeScanner(
    "reader",
    { fps: 10, qrbox: 250 }
);
html5QrcodeScanner.render(onScanSuccess);
</script>

</body>
</html>
