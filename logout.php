<?php
session_start();
session_destroy(); // Hancurkan semua sesi (Admin/Siswa)
header("location:index.php"); // Kembalikan ke halaman login
exit;
?>