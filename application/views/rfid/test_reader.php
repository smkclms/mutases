<!DOCTYPE html>
<html>
<head>
    <title>Test RFID Reader</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        #out { font-size: 28px; margin-top: 20px; color: blue; }
    </style>
</head>
<body>

<h2>Test RFID Reader</h2>
<p>Tempelkan kartu di RFID reader. Jika UID tampil, berarti pembacaan berhasil.</p>

<!-- Input tersembunyi -->
<input id="reader" autofocus
       style="opacity:0; position:absolute; left:-9999px;">

<div id="out">Menunggu kartu...</div>

<script>
function forceFocus() {
    let input = document.getElementById("reader");
    if (document.activeElement !== input) input.focus();
}
setInterval(forceFocus, 300);

document.getElementById("reader").addEventListener("input", function() {
    let uid = this.value.trim();
    this.value = "";
    forceFocus();

    document.getElementById("out").innerHTML = "UID Terbaca: <b>" + uid + "</b>";
});
</script>

</body>
</html>
