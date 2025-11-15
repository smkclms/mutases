<h4>Data Absensi</h4>

<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambah">
  <i class="fas fa-plus"></i> Tambah Absensi
</button>

<br><br>

<table class="table table-bordered table-striped table-responsive-sm">
  <thead class="thead-light">
    <tr>
      <th width="120">Tanggal</th>
      <th>Nama Siswa</th>
      <th>Kelas</th>
      <th>Status</th>
      <th>Alasan</th>
      <th width="250">Aksi</th>
    </tr>
  </thead>

  <tbody>

    <?php foreach($absensi as $a): ?>
      <tr>
        <td><?= $a->tanggal ?></td>
        <td><?= $a->nama_siswa ?></td>
        <td><?= $a->nama_kelas ?></td>

        <td>
          <span class="badge 
            <?= ($a->status=='SAKIT'?'badge-warning':
                ($a->status=='IZIN'?'badge-info':'badge-danger')) ?>">
            <?= $a->status ?>
          </span>
        </td>

        <td><?= $a->keterangan ?></td>

        <td>

          <!-- DETAIL -->
          <a href="<?= base_url('Absensi/Absensi/detail/'.$a->id_absensi) ?>" 
             class="btn btn-info btn-sm">
             <i class="fas fa-eye"></i> Detail
          </a>

          <!-- HAPUS -->
          <a href="<?= base_url('Absensi/Absensi/hapus/'.$a->id_absensi) ?>"
             onclick="return confirm('Hapus data absensi ini?');"
             class="btn btn-danger btn-sm">
             <i class="fas fa-trash"></i> Hapus
          </a>

          <!-- WHATSAPP NOTIFIKASI -->
          <a href="https://wa.me/<?= $a->nohp_wali ?>?text=Informasi%20Absensi%20:%0A
Nama%20:%20<?= urlencode($a->nama_siswa) ?>%0A
Kelas%20:%20<?= urlencode($a->nama_kelas) ?>%0A
Status%20:%20<?= urlencode($a->status) ?>%0A
Alasan%20:%20<?= urlencode($a->keterangan) ?>%0A
Tanggal%20:%20<?= urlencode($a->tanggal) ?>"
             target="_blank"
             class="btn btn-success btn-sm">
             <i class="fab fa-whatsapp"></i> WA
          </a>

        </td>

      </tr>
    <?php endforeach; ?>

  </tbody>
</table>


<!-- ————————————————————————————————
     MODAL TAMBAH ABSENSI (FULL)
——————————————————————————————— -->
<div class="modal fade" id="modalTambah">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <form method="post" action="<?= site_url('Absensi/Absensi/simpan') ?>">

        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Tambah Absensi Siswa</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">

          <!-- CSRF -->
          <input type="hidden" 
                 name="<?= $this->security->get_csrf_token_name(); ?>"
                 value="<?= $this->security->get_csrf_hash(); ?>">

          <input type="hidden" name="id_siswa" id="id_siswa">
          <input type="hidden" name="id_kelas" id="id_kelas">

          <!-- CARI SISWA -->
          <div class="form-group position-relative">
            <label>Nama Siswa</label>
            <input type="text" id="cariSiswa" 
                   class="form-control" 
                   placeholder="Ketik minimal 2 huruf..." autocomplete="off">

            <div id="hasilCari" 
                 class="border rounded bg-white position-absolute w-100"
                 style="max-height:230px; overflow-y:auto; display:none; z-index:9999;">
            </div>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select class="form-control" name="status" id="status" required>
              <option value="">Pilih</option>
              <option value="SAKIT">SAKIT</option>
              <option value="IZIN">IZIN</option>
              <option value="ALPA">ALPA</option>
            </select>
          </div>

          <div class="form-group">
            <label>Alasan</label>
            <input type="text" class="form-control" name="keterangan" id="alasan" required>
          </div>

          <div class="form-group">
            <label>Tanggal</label>
            <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>">
          </div>

          <div class="form-group">
            <label>Tahun Pelajaran</label>
            <input type="text" class="form-control" name="tahun_pelajaran" value="2025/2026">
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

<!-- ————————————————————————————————
     SCRIPT AJAX PENCARIAN SISWA
——————————————————————————————— -->
<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>

<script>
$("#status").change(function() {
  const v = $(this).val();
  $("#alasan").val(
    v === "SAKIT" ? "Sakit" :
    v === "IZIN" ? "Izin" :
    v === "ALPA" ? "Tanpa Keterangan" : ""
  );
});

// ————— PENCARIAN AJAX —————
$("#cariSiswa").keyup(function () {

    let keyword = $(this).val().trim();
    if (keyword.length < 2) {
        $("#hasilCari").hide();
        return;
    }

    $.ajax({
        url: "<?= base_url('index.php/Absensi/Absensi/ajax_siswa'); ?>",
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
                       data-id="${s.id}" 
                       data-kelas="${s.nama_kelas}"
                       data-nama="${s.nama}">
                    <b>${s.nisn}</b> — ${s.nama_kelas} — ${s.nama}
                  </div>
                `;
            });

            $("#hasilCari").html(html).show();
        }
    });

});

// pilih siswa
$(document).on("click", ".pilihSiswa", function(){
    $("#id_siswa").val($(this).data("id"));
    $("#id_kelas").val($(this).data("kelas"));
    $("#cariSiswa").val($(this).data("nama"));
    $("#hasilCari").hide();
});

// klik luar
$(document).click(function(e){
    if (!$(e.target).closest("#cariSiswa, #hasilCari").length){
        $("#hasilCari").hide();
    }
});
</script>
