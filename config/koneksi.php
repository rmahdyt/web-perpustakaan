<?php
$host = "sql104.infinityfree.com";
$user = "if0_41284260";
$pass = "WQb6hNqUIUUFwJm"; 
$db   = "if0_41284260_perpus";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>
