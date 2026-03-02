<?php 
// Mengaktifkan session php
session_start();

// Menghubungkan dengan koneksi
include 'config/koneksi.php';

// Menangkap data yang dikirim dari form
// FITUR KEAMANAN: mysqli_real_escape_string mencegah karakter aneh masuk ke query SQL
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

// --- TAHAP 1: CEK ADMIN ---
$login_admin = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
$cek_admin = mysqli_num_rows($login_admin);

if($cek_admin > 0){
    // Jika ketemu di tabel admin
    $data = mysqli_fetch_assoc($login_admin);
    
    // Buat Session Admin
    $_SESSION['admin'] = $username;
    $_SESSION['status'] = "login";
    $_SESSION['id_admin'] = $data['id_admin']; // Opsional: jika butuh ID untuk log aktivitas
    
    header("location:admin/dashboard.php");

} else {
    // --- TAHAP 2: CEK SISWA (Jika bukan admin) ---
    $login_siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE username='$username' AND password='$password'");
    $cek_siswa = mysqli_num_rows($login_siswa);

    if($cek_siswa > 0){
        // Jika ketemu di tabel siswa
        $data = mysqli_fetch_assoc($login_siswa);
        
        // Buat Session Siswa
        $_SESSION['nis'] = $data['nis']; // KUNCI UTAMA: Halaman siswa butuh NIS
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['status'] = "login_siswa";
        
        header("location:siswa/dashboard.php");
    } else {
        // --- TAHAP 3: GAGAL TOTAL ---
        // Alihkan kembali ke index.php dengan membawa pesan gagal untuk SweetAlert
        header("location:index.php?pesan=gagal");
    }
}
?>