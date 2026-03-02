<?php
// Mendapatkan nama file yang sedang dibuka (contoh: dashboard.php, buku.php)
$page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="brand">
        <i class="fas fa-layer-group"></i> PANEL ADMIN
    </div>
    
    <div class="menu">
        <a href="dashboard.php" class="<?= $page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-th-large" style="width:25px"></i> Dashboard
        </a>
        <a href="buku.php" class="<?= $page == 'buku.php' ? 'active' : '' ?>">
            <i class="fas fa-book" style="width:25px"></i> Data Buku
        </a>
        <a href="siswa.php" class="<?= $page == 'siswa.php' ? 'active' : '' ?>">
            <i class="fas fa-users" style="width:25px"></i> Data Siswa
        </a>
        <a href="transaksi.php" class="<?= $page == 'transaksi.php' ? 'active' : '' ?>">
            <i class="fas fa-exchange-alt" style="width:25px"></i> Transaksi
        </a>
    </div>

    <a href="../index.php" class="btn-logout">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<div class="bottom-nav">
    <a href="dashboard.php" class="nav-item <?= $page == 'dashboard.php' ? 'active' : '' ?>">
        <i class="fas fa-th-large"></i><span>Home</span>
    </a>
    <a href="buku.php" class="nav-item <?= $page == 'buku.php' ? 'active' : '' ?>">
        <i class="fas fa-book"></i><span>Buku</span>
    </a>
    <a href="transaksi.php" class="nav-item <?= $page == 'transaksi.php' ? 'active' : '' ?>">
        <i class="fas fa-exchange-alt"></i><span>Trans</span>
    </a>
    <a href="siswa.php" class="nav-item <?= $page == 'siswa.php' ? 'active' : '' ?>">
        <i class="fas fa-users"></i><span>Siswa</span>
    </a>
    <a href="../index.php" class="nav-item" style="color:var(--danger)">
        <i class="fas fa-sign-out-alt"></i><span>Logout</span>
    </a>
</div>