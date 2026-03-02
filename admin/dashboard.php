<?php
include "../config/session_admin.php";
include "../config/koneksi.php";

$username = $_SESSION['admin'];
$query_admin = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username'");
$admin = mysqli_fetch_array($query_admin);

// --- HITUNG STATISTIK ---
$jml_buku   = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM buku"));
$jml_siswa  = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM siswa"));
$req_pinjam = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM peminjaman WHERE status='menunggu_persetujuan'"));
$req_balik  = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM peminjaman WHERE status='menunggu_pengembalian'"));

// --- DATA GRAFIK ---
$label_bulan = []; $data_pinjam = [];
$query_tren = mysqli_query($conn, "
    SELECT DATE_FORMAT(tanggal_pinjam, '%M') as bulan, COUNT(*) as total 
    FROM peminjaman 
    GROUP BY MONTH(tanggal_pinjam), DATE_FORMAT(tanggal_pinjam, '%M') 
    ORDER BY MAX(tanggal_pinjam) ASC LIMIT 6
");
while($row = mysqli_fetch_array($query_tren)){
    $label_bulan[] = $row['bulan']; $data_pinjam[] = $row['total'];
}

$label_buku = []; $data_populer = [];
$query_populer = mysqli_query($conn, "
    SELECT b.judul, COUNT(p.id_buku) as total 
    FROM peminjaman p JOIN buku b ON p.id_buku = b.id_buku 
    GROUP BY p.id_buku, b.judul ORDER BY total DESC LIMIT 5
");
while($row = mysqli_fetch_array($query_populer)){
    $judul_singkat = (strlen($row['judul']) > 15) ? substr($row['judul'], 0, 15) . '...' : $row['judul'];
    $label_buku[] = $judul_singkat; $data_populer[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Admin | E-Library</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css"> 
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <?php include "layout_menu.php"; ?>

    <div class="main">
        
        <a href="profil.php" class="header-welcome">
            <?php if($admin['foto']): ?>
                <img src="data:image/jpeg;base64,<?= base64_encode($admin['foto']) ?>" class="profile-pic">
            <?php else: ?>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin['nama_admin']) ?>&background=random&color=fff" class="profile-pic">
            <?php endif; ?>
            
            <div style="color:white;">
                <h2 style="margin:0; font-size: 20px; font-weight:600;">Halo, <?= htmlspecialchars($admin['nama_admin']) ?></h2>
                <p style="margin:5px 0 0 0; opacity:0.9; font-size:14px;">Administrator Perpustakaan</p>
            </div>
        </a>

        <div class="stats-grid">
            <a href="buku.php" class="card">
                <div class="card-content">
                    <h3><?= $jml_buku ?></h3>
                    <p>Total Buku</p>
                </div>
                <div class="card-icon icon-blue"><i class="fas fa-book"></i></div>
            </a>

            <a href="siswa.php" class="card">
                <div class="card-content">
                    <h3><?= $jml_siswa ?></h3>
                    <p>Siswa Terdaftar</p>
                </div>
                <div class="card-icon icon-green"><i class="fas fa-users"></i></div>
            </a>

            <a href="transaksi.php" class="card">
                <div class="card-content">
                    <h3><?= $req_pinjam ?></h3>
                    <p>Request Pinjam <?php if($req_pinjam > 0): ?><span class="badge-alert">Cek!</span><?php endif; ?></p>
                </div>
                <div class="card-icon icon-orange"><i class="fas fa-clock"></i></div>
            </a>

            <a href="transaksi.php" class="card">
                <div class="card-content">
                    <h3><?= $req_balik ?></h3>
                    <p>Request Kembali <?php if($req_balik > 0): ?><span class="badge-alert">Proses!</span><?php endif; ?></p>
                </div>
                <div class="card-icon icon-red"><i class="fas fa-check-circle"></i></div>
            </a>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <i class="fas fa-chart-line" style="color:var(--primary); margin-right:5px;"></i> Tren Peminjaman
                </div>
                <div class="chart-container">
                    <canvas id="chartTren"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <i class="fas fa-trophy" style="color:var(--warning); margin-right:5px;"></i> Buku Terpopuler
                </div>
                <div class="chart-container">
                    <canvas id="chartPopuler"></canvas>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Setup Font Global
        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.font.size = 11;
        Chart.defaults.color = '#7d879c';

        // 1. Grafik Tren (Line Chart)
        const ctxTren = document.getElementById('chartTren').getContext('2d');
        let gradient = ctxTren.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(67, 94, 190, 0.2)');
        gradient.addColorStop(1, 'rgba(67, 94, 190, 0)');

        new Chart(ctxTren, {
            type: 'line',
            data: {
                labels: <?= json_encode($label_bulan) ?>,
                datasets: [{
                    label: 'Peminjaman',
                    data: <?= json_encode($data_pinjam) ?>,
                    borderColor: '#435ebe',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#435ebe',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. Grafik Populer (Doughnut Chart)
        const ctxPopuler = document.getElementById('chartPopuler').getContext('2d');
        new Chart(ctxPopuler, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($label_buku) ?>,
                datasets: [{
                    data: <?= json_encode($data_populer) ?>,
                    backgroundColor: ['#435ebe', '#57caeb', '#39da8a', '#fdac41', '#ff5b5c'],
                    borderWidth: 0,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 15, font: {size: 10} } }
                }
            }
        });
    </script>

</body>
</html>