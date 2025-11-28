<!DOCTYPE html>
<html>
<head>
    <title>Match UID ke Siswa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        .uid-item { cursor: pointer; padding: 10px; border-bottom: 1px solid #eee; }
        .uid-item.active { background: #007bff; color: white; font-weight: bold; }
        .siswa-box { border: 1px solid #ddd; padding: 12px; border-radius: 8px; margin-bottom: 10px; }
        .siswa-box:hover { background: #f7f7f7; }
        .uid-item {
    cursor: pointer;
    border-bottom: 1px solid #eee;
    border-radius: 5px;
}

.uid-item:hover {
    background: #f1f1f1;
}

.uid-item.active {
    background: #007bff;
    color: white;
}

.siswa-box {
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 10px;
}

.siswa-box:hover {
    background: #f8f9fa;
}

    </style>
</head>

<body class="p-3">

<h3>Match UID ke Siswa</h3>
<p>Pilih UID → pilih siswa → klik hubungkan</p>

<div class="row mt-3">

    <!-- LEFT: UID Pending -->
    <div class="col-md-3">
        <h5>UID Pending</h5>
        <div id="uid_list" class="border rounded p-2" style="height: 600px; overflow-y: auto;">
            <?php foreach ($pending as $p): ?>
                <div class="uid-item p-2" data-uid="<?= $p->uid ?>">
                    <?= $p->uid ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- RIGHT: Siswa per kelas -->
    <div class="col-md-9">
        <div class="d-flex mb-3 gap-2">
            <input id="search" class="form-control" placeholder="Cari siswa (min 2 huruf)...">
            <select id="kelas" class="form-control" style="max-width: 250px">
                <option value="all">Semua Kelas</option>
                <?php foreach ($kelas as $k): ?>
                    <option value="<?= $k->id ?>"><?= $k->nama ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="siswa_results" class="border rounded p-3"
             style="height: 600px; overflow-y: auto;">
        </div>
    </div>

</div>


<script>
let selectedUID = null;

// pilih UID
document.querySelectorAll('.uid-item').forEach(item => {
    item.addEventListener('click', function() {
        selectedUID = this.dataset.uid;

        document.querySelectorAll('.uid-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
    });
});

// live search
document.getElementById('search').addEventListener('keyup', function() {
    loadSiswa();
});

document.getElementById('kelas').addEventListener('change', function() {
    loadSiswa();
});

function loadSiswa() {
    let q = document.getElementById('search').value;
    let kelas = document.getElementById('kelas').value;

    // Jika kelas dipilih tapi tidak ada pencarian
    if (kelas !== "all" && q.length < 2) {
        fetch("<?= site_url('rfid/search_siswa') ?>?kelas=" + kelas)
            .then(res => res.json())
            .then(data => {
                let html = "";
                data.forEach(s => {
                    html += `
                        <div class="siswa-box">
                            <b>${s.nama}</b> (${s.nama_kelas})
                            <button class="btn btn-sm btn-primary float-end" onclick="match('${s.id}')">Hubungkan</button>
                        </div>
                    `;
                });
                document.getElementById('siswa_results').innerHTML = html;
            });
        return;
    }

    // Jika pencarian digunakan
    if (q.length >= 2) {
        fetch("<?= site_url('rfid/search_siswa') ?>?q=" + q + "&kelas=" + kelas)
            .then(res => res.json())
            .then(data => {
                let html = "";
                data.forEach(s => {
                    html += `
                        <div class="siswa-box">
                            <b>${s.nama}</b> (${s.nama_kelas})
                            <button class="btn btn-sm btn-primary float-end" onclick="match('${s.id}')">Hubungkan</button>
                        </div>
                    `;
                });
                document.getElementById('siswa_results').innerHTML = html;
            });
    }
}


function match(id_siswa) {

    if (!selectedUID) {
        alert("Pilih UID dulu!");
        return;
    }

    if (!confirm("Hubungkan UID " + selectedUID + " ke siswa ini?")) return;

    fetch("<?= site_url('rfid/ajax_save_match') ?>", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "uid=" + selectedUID + "&id_siswa=" + id_siswa +
              "&<?= $this->security->get_csrf_token_name(); ?>=<?= $this->security->get_csrf_hash(); ?>"
    })
    .then(res => res.json())
    .then(data => {

    alert(data.message);

    // 1. Hapus UID dari daftar pending
    document.querySelector(`.uid-item[data-uid="${selectedUID}"]`)?.remove();

    // 2. Reset selected UID
    selectedUID = null;

    // 3. Reload daftar siswa (kelas tetap, pencarian tetap)
    loadSiswa();
});

}
</script>

</body>
</html>
