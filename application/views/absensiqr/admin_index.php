<h4>Data Absensi QR</h4>

<!-- FLASHDATA -->
<?php if ($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong><i class="fa fa-check-circle"></i> Berhasil!</strong>
    <?= $this->session->flashdata('success'); ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong><i class="fa fa-times-circle"></i> Gagal!</strong>
    <?= $this->session->flashdata('error'); ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
<?php endif; ?>

<!-- TOMBOL TAMBAH -->
<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambah">
    <i class="fas fa-plus"></i> Tambah Absensi QR
</button>

<br><br>

<table class="table table-bordered table-striped table-responsive-sm">
    <thead class="thead-light">
        <tr>
            <th width="120">Tanggal</th>
            <th>NIS</th>
            <th>Nama</th>
            <th>Kelas</th>
            <th>Status</th>
            <th>Jam Masuk</th>
            <th>Jam Pulang</th>
            <th width="200">Aksi</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($absen as $a): ?>
        <tr>
            <td><?= $a->tanggal ?></td>
            <td><?= $a->nis ?></td>
            <td><?= $a->nama_siswa ?></td>
            <td><?= $a->nama_kelas ?></td>

            <td>
                <?php
$ket_full = array(
    'H' => 'Hadir',
    'S' => 'Sakit',
    'I' => 'Izin',
    'A' => 'Alpa'
);

$warna_badge = array(
    'H' => 'badge-success',
    'S' => 'badge-warning',
    'I' => 'badge-info',
    'A' => 'badge-danger'
);

// tentukan teks
$label = isset($ket_full[$a->kehadiran]) ? $ket_full[$a->kehadiran] : $a->kehadiran;
// tentukan warna
$warna = isset($warna_badge[$a->kehadiran]) ? $warna_badge[$a->kehadiran] : 'badge-secondary';
?>

<span class="badge <?= $warna ?>">
    <?= $label ?>
</span>
<br>

<!-- STATUS KETERANGAN -->
<small>
    <?= $a->status ?>
<?php if ($a->status == 'Terlambat' && !empty($a->keterangan_telat)): ?>
    <small class="text-warning">(<?= $a->keterangan_telat ?>)</small>
<?php endif; ?>

</small>


            </td>

            <td><?= $a->jam_masuk ?: '-' ?></td>
            <td><?= $a->jam_pulang ?: '-' ?></td>

            <td>
                <!-- EDIT BUTTON -->
                <button class="btn btn-warning btn-sm btnEdit"
                    data-id="<?= $a->id ?>"
                    data-nis="<?= $a->nis ?>"
                    data-nama="<?= $a->nama_siswa ?>"
                    data-kelas="<?= $a->nama_kelas ?>"
                    data-kehadiran="<?= $a->kehadiran ?>"
                    data-status="<?= $a->status ?>"
                    data-masuk="<?= $a->jam_masuk ?>"
                    data-pulang="<?= $a->jam_pulang ?>"
                    data-tanggal="<?= $a->tanggal ?>">
                    <i class="fas fa-edit"></i> Edit
                </button>

                <!-- DELETE BUTTON -->
                <a href="<?= site_url('AbsensiQR/hapus/'.$a->id) ?>"
                   onclick="return confirm('Yakin ingin menghapus data ini?');"
                   class="btn btn-danger btn-sm">
                   <i class="fas fa-trash"></i> Hapus
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- ===========================
     MODAL TAMBAH — FINAL FIXED
=========================== -->
<div class="modal fade" id="modalTambah">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="post" action="<?= site_url('AbsensiQR/simpan') ?>">
                 <input type="hidden" 
           name="<?= $this->security->get_csrf_token_name(); ?>"
           value="<?= $this->security->get_csrf_hash(); ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Absensi QR Manual</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <!-- INPUT CARI NAMA SISWA -->
                    <div class="form-group position-relative">
                        <label>Nama Siswa</label>
                        <input type="text" id="cariSiswa"
                            class="form-control"
                            placeholder="Ketik minimal 2 huruf..." autocomplete="off">

                        <!-- NIS YANG DIKIRIM KE SERVER -->
                        <input type="hidden" name="nis" id="nis_real">

                        <div id="hasilCari"
                            class="border rounded bg-white position-absolute w-100"
                            style="max-height:200px; overflow-y:auto; display:none; z-index:9999;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Kehadiran</label>
                        <select class="form-control" name="kehadiran" required>
                            <option value="H">Hadir</option>
                            <option value="S">Sakit</option>
                            <option value="I">Izin</option>
                            <option value="A">Alpa</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <input type="text" class="form-control" name="status" placeholder="Hadir / Terlambat / Manual">
                    </div>

                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="form-group">
                        <label>Jam Masuk</label>
                        <input type="time" class="form-control" name="jam_masuk">
                    </div>

                    <div class="form-group">
                        <label>Jam Pulang</label>
                        <input type="time" class="form-control" name="jam_pulang">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>

            </form>

        </div>
    </div>
</div>


<!-- ===========================
     MODAL EDIT
=========================== -->
<div class="modal fade" id="modalEdit">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="post" action="<?= site_url('AbsensiQR/update') ?>">
                 <input type="hidden" 
           name="<?= $this->security->get_csrf_token_name(); ?>"
           value="<?= $this->security->get_csrf_hash(); ?>">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">Edit Absensi QR</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="id" id="edit_id">

                    <div class="form-group">
                        <label>NIS</label>
                        <input type="number" class="form-control" id="edit_nis" name="nis" readonly>
                    </div>

                    <div class="form-group">
                        <label>Kehadiran</label>
                        <select class="form-control" name="kehadiran" id="edit_kehadiran">
                            <option value="H">Hadir</option>
                            <option value="S">Sakit</option>
                            <option value="I">Izin</option>
                            <option value="A">Alpa</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <input type="text" class="form-control" id="edit_status" name="status">
                    </div>

                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" id="edit_tanggal" name="tanggal">
                    </div>

                    <div class="form-group">
                        <label>Jam Masuk</label>
                        <input type="time" class="form-control" id="edit_masuk" name="jam_masuk">
                    </div>

                    <div class="form-group">
                        <label>Jam Pulang</label>
                        <input type="time" class="form-control" id="edit_pulang" name="jam_pulang">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>

            </form>

        </div>
    </div>
</div>
<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
<script>
$(document).on("click", ".btnEdit", function(){
    $("#edit_id").val($(this).data("id"));
    $("#edit_nis").val($(this).data("nis"));
    $("#edit_kehadiran").val($(this).data("kehadiran"));
    $("#edit_status").val($(this).data("status"));
    $("#edit_tanggal").val($(this).data("tanggal"));
    $("#edit_masuk").val($(this).data("masuk"));
    $("#edit_pulang").val($(this).data("pulang"));

    $("#modalEdit").modal("show");
});
</script>

<script>
$("#cariSiswa").keyup(function () {

    let keyword = $(this).val().trim();
    if (keyword.length < 2) {
        $("#hasilCari").hide();
        return;
    }

    $.ajax({
        url: "<?= base_url('index.php/AbsensiQR/ajax_siswa'); ?>",
        type: "POST",
        data: {
            keyword: keyword,
            "<?= $this->security->get_csrf_token_name(); ?>":
            "<?= $this->security->get_csrf_hash(); ?>"
        },
        success: function(res){

            let data = JSON.parse(res);
            let html = "";

            data.forEach(s => {
                html += `
                    <div class="p-2 border-bottom pilihSiswa"
                        data-nis="${s.nis}"
                        data-nama="${s.nama}"
                        data-kelas="${s.nama_kelas}">
                        <b>${s.nis}</b> — ${s.nama_kelas} — ${s.nama}
                    </div>
                `;
            });

            $("#hasilCari").html(html).show();
        }
    });

});


// KLIK PILIH SISWA
$(document).on("click", ".pilihSiswa", function(){
    let nis   = $(this).data("nis");
    let nama  = $(this).data("nama");
    let kelas = $(this).data("kelas");

    $("#nis_real").val(nis);
    $("#cariSiswa").val(nama + " (" + kelas + ")");
    $("#hasilCari").hide();
});


// Klik luar → tutup dropdown
$(document).click(function(e){
    if (!$(e.target).closest("#cariSiswa, #hasilCari").length){
        $("#hasilCari").hide();
    }
});
</script>


