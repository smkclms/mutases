<h4>Detail Absensi</h4>

<p>
Tanggal : <?= $absen->tanggal ?><br>
Kelas   : <?= $absen->nama_kelas ?><br>
Tahun Pelajaran : <?= $absen->tahun_pelajaran ?>
</p>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Nama Siswa</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($siswa as $s): ?>
        <tr>
            <td><?= $s->nama_siswa ?></td>
            <td><?= $s->status ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
