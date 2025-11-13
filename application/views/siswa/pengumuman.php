<?php $this->load->view('siswa/layout/sidebar'); ?>

<div style="margin-left:220px;padding:20px;">
  <h3>Pengumuman</h3>
  <hr>

  <?php foreach($pengumuman as $p): ?>
    <div style="padding:10px;background:#f5f7ff;border:1px solid #d6d8ff;margin-bottom:12px;">
      <b><?= $p->judul ?></b><br>
      <small><?= $p->tanggal ?></small>
      <p><?= $p->isi ?></p>
    </div>
  <?php endforeach; ?>
</div>
