<?php
session_start();

// Cek apakah session 'status' sudah di-set dan apakah isinya 'login_siswa'
// Jika TIDAK, maka tendang kembali ke halaman login
if(!isset($_SESSION['status']) || $_SESSION['status'] != "login_siswa"){
    header("location:../index.php?pesan=belum_login");
    exit;
}
?>