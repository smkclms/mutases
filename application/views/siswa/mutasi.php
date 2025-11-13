<?php $this->load->view('siswa/layout/sidebar'); ?>

<div style="margin-left:220px;padding:20px;">
  <h3>Riwayat Mutasi</h3>
  <hr>

  <table class="table table-bordered">
    <thead>
      <tr><th>Tanggal</th><th>Jenis Mutasi</th><th>Keterangan</th></tr>
    </thead>
    <tbody>
      <?php foreach($mutasi as $m): ?>
        <tr>
          <td><?= $m->tanggal ?></td>
          <td><?= ucfirst($m->jenis) ?></td>
          <td><?= $m->keterangan ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
