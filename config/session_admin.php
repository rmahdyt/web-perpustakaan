<?php
session_start();

// Cek apakah session admin sudah ada
// Kita asumsikan saat login nanti kita buat $_SESSION['admin']
if (!isset($_SESSION['admin'])) {
    echo "<script>
            alert('Anda harus login sebagai Admin dulu!');
            window.location='../index.php'; 
          </script>";
    exit;
}
?>