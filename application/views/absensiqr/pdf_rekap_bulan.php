<?php
$headBg = "#e9ecef";
?>

<style>
table { 
    border-collapse: collapse; 
    font-size:9px; 
    table-layout: fixed; 
    width: 100%;
}
th, td { 
    border:1px solid #000; 
    padding:2px; 
    text-align:center; 
    word-wrap: break-word;
}
.nama { text-align:left; padding-left:2px; }
</style>

<div style="text-align:center; margin-top:-10px; margin-bottom:5px;">

    <!-- Logo lebih kecil -->
    <img src="<?= FCPATH.'assets/img/logobonti.png' ?>" width="35" style="margin-bottom:2px;">

    <!-- Judul Sekolah -->
    <div style="font-size:14px; font-weight:bold; margin:0; padding:0;">
        SMKN 1 CILIMUS
    </div>

    <!-- Subjudul -->
    <div style="font-size:11px; margin:2px 0 0 0; padding:0;">
        Laporan Absensi QR Siswa
    </div>

    <!-- Informasi Bulan & Tahun -->
    <div style="font-size:10px; margin:2px 0 0 0; padding:0;">
        Bulan: <b><?= $bulan_label ?></b>,
        Tahun: <b><?= $tahun ?></b>
    </div>

    <!-- Info Kelas -->
    <div style="font-size:10px; margin:2px 0 0 0; padding:0;">
        Kelas: <b><?= $kelas_nama ?></b>
    </div>

</div>


<table width="100%">
    <tr>
       <th style="background:<?= $headBg ?>; width:180px;">Nama Siswa</th>


        <?php foreach($tanggal as $tgl): ?>
            <?php
                $hari = date('N', strtotime($tgl)); 
                $is_libur = in_array($tgl, $tanggalMerah);

                $bg = "";
                if ($hari == 6 || $hari == 7 || $is_libur) {
                    $bg = "background-color:#ffb3b3;";
                }
            ?>
            <th style="<?= $bg ?> width:22px;">
                <?= date('d', strtotime($tgl)) ?>
            </th>
        <?php endforeach; ?>

        <th width="18">H</th>
        <th width="18">S</th>
        <th width="18">I</th>
        <th width="18">A</th>
    </tr>

    <?php foreach($siswa as $s): ?>
        <tr>
            <td class="nama"><?= strtoupper($s->nama) ?></td>

            <?php
                $countH = $countS = $countI = $countA = 0;
            ?>

            <?php foreach ($tanggal as $tgl): ?>

                <?php
                    // ===============================
                    // Ambil KODE kehadiran
                    // ===============================
                    if (isset($rekap[$s->nis][$tgl])) {
                        $kode = strtoupper($rekap[$s->nis][$tgl]);
                    } else {
                        // Weekend / tanggal merah
                        $hari = date('N', strtotime($tgl));
                        $is_libur = in_array($tgl, $tanggalMerah);

                        if ($hari == 6 || $hari == 7 || $is_libur) {
                            $kode = 'L';
                        } else {
                            $kode = '-';
                        }
                    }

                    // Hitung rekap
                    if ($kode == 'H') $countH++;
                    if ($kode == 'S') $countS++;
                    if ($kode == 'I') $countI++;
                    if ($kode == 'A') $countA++;

                    // Warna libur
                    $hari2 = date('N', strtotime($tgl));
                    $is_libur2 = in_array($tgl, $tanggalMerah);
                    $bg = ($kode == 'L' || $hari2 == 6 || $hari2 == 7 || $is_libur2)
                        ? "background-color:#ffb3b3;" : "";
                ?>

                <td style="<?= $bg ?>"><?= $kode ?></td>

            <?php endforeach; ?>

            <td><?= $countH ?></td>
            <td><?= $countS ?></td>
            <td><?= $countI ?></td>
            <td><?= $countA ?></td>
        </tr>
    <?php endforeach; ?>
</table>
