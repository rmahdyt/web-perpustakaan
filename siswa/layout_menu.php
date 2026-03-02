<?php
// Mendapatkan nama file saat ini untuk penanda menu aktif
$page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="brand"><i class="fas fa-book-reader"></i> SISWA AREA</div>
    
    <div class="menu">
        <a href="dashboard.php" class="<?= ($page == 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="buku.php" class="<?= ($page == 'buku.php') ? 'active' : '' ?>">
            <i class="fas fa-search"></i> Cari Buku
        </a>
        <a href="riwayat.php" class="<?= ($page == 'riwayat.php') ? 'active' : '' ?>">
            <i class="fas fa-history"></i> Riwayat
        </a>
    </div>

    <a href="../logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</a>
</div>

<div class="bottom-nav">
    <a href="dashboard.php" class="nav-item <?= ($page == 'dashboard.php') ? 'active' : '' ?>">
        <i class="fas fa-home"></i> Home
    </a>
    <a href="buku.php" class="nav-item <?= ($page == 'buku.php') ? 'active' : '' ?>">
        <i class="fas fa-search"></i> Cari
    </a>
    <a href="riwayat.php" class="nav-item <?= ($page == 'riwayat.php') ? 'active' : '' ?>">
        <i class="fas fa-history"></i> Riwayat
    </a>
    <a href="../logout.php" class="nav-item" style="color:var(--danger)">
        <i class="fas fa-sign-out-alt"></i> Keluar
    </a>
</div>