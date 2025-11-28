<!DOCTYPE html>
<html>
<head>
<title>UID Pending</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="p-4">

<h3>Daftar UID Pending</h3>

<table class="table table-bordered">
    <tr>
        <th>UID</th>
        <th>Tanggal</th>
    </tr>
    <?php foreach ($pending as $p): ?>
    <tr>
        <td><?= $p->uid ?></td>
        <td><?= $p->created_at ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
