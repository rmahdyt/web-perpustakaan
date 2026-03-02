<?php
include "../config/session_siswa.php";
include "../config/koneksi.php";

$nis = $_SESSION['nis'];
$siswa = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM siswa WHERE nis='$nis'"));

// --- LOGIKA STATISTIK ---
$hitung_aktif = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM peminjaman WHERE nis='$nis' AND status IN ('menunggu_persetujuan', 'dipinjam')"));
$hitung_total = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM peminjaman WHERE nis='$nis'"));
$hitung_kembali = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM peminjaman WHERE nis='$nis' AND status='kembali'"));

// --- LOGIKA REQUEST PENGEMBALIAN ---
if (isset($_GET['aksi']) && $_GET['aksi'] == 'ajukan_kembali') {
    $id_pinjam = $_GET['id'];
    $update = mysqli_query($conn, "UPDATE peminjaman SET status='menunggu_pengembalian' WHERE id_peminjaman='$id_pinjam'");
    
    if($update) {
        $_SESSION['swal'] = [
            'icon' => 'success', 'title' => 'Permintaan Terkirim',
            'text' => 'Silakan serahkan buku fisik kepada Admin untuk verifikasi.'
        ];
        header("Location: dashboard.php"); exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Siswa | E-Library</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* --- CSS KHUSUS HALAMAN DASHBOARD (KONTEN) --- */

        /* WELCOME BANNER */
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary) 0%, #25396f 100%);
            padding: 30px; border-radius: 15px; color: white;
            display: flex; align-items: center; gap: 20px;
            box-shadow: 0 10px 20px rgba(67, 94, 190, 0.25); position: relative; overflow: hidden;
        }
        .welcome-text h2 { margin: 0; font-size: 24px; font-weight: 600; }
        .welcome-text p { margin: 5px 0 0; opacity: 0.9; font-size: 14px; }
        .profile-pic { width: 70px; height: 70px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.4); object-fit: cover; z-index: 2; }
        
        /* HIASAN BACKGROUND BANNER */
        .circle-bg { position: absolute; border-radius: 50%; background: rgba(255,255,255,0.1); }
        .c1 { width: 150px; height: 150px; top: -50px; right: -50px; }
        .c2 { width: 100px; height: 100px; bottom: -30px; right: 80px; }

        /* STATS GRID */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 15px; }
        .stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .stat-info h3 { margin: 0; font-size: 24px; color: var(--dark); }
        .stat-info p { margin: 0; font-size: 13px; color: #888; }
        
        /* SECTION TITLE */
        .section-title { font-size: 18px; font-weight: 700; color: var(--dark); display: flex; align-items: center; gap: 8px; margin-bottom: 15px; }

        /* LOAN CARD (ACTIVE) */
        .active-loans { display: grid; gap: 15px; }
        .loan-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); border: 1px solid #f0f0f0; display: flex; gap: 20px; align-items: flex-start; }
        .book-cover { width: 70px; height: 100px; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); background: #eee; flex-shrink: 0; }
        .loan-details { flex: 1; min-width: 0; }
        .loan-details h4 { margin: 0 0 5px; font-size: 16px; color: var(--dark); }
        .loan-details p { margin: 0 0 10px; font-size: 13px; color: #888; }
        
        .badge { display: inline-block; padding: 4px 10px; border-radius: 5px; font-size: 11px; font-weight: 600; }
        .bg-warning { background: #fff8e1; color: #d97706; }
        .bg-success { background: #e0ffef; color: #16a34a; }
        .bg-info { background: #e3fdfd; color: #008da6; }

        .btn-return { display: inline-block; padding: 8px 15px; background: var(--primary); color: white; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: 0.2s; }
        .btn-return:hover { background: #354a96; }

        /* NEW BOOKS GRID */
        .new-books-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; }
        .mini-book { background: white; padding: 10px; border-radius: 10px; text-align: center; transition: 0.3s; border: 1px solid transparent; text-decoration: none; color: inherit; display: block; }
        .mini-book:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-color: var(--primary); }
        .mini-cover { width: 100%; height: 160px; object-fit: cover; border-radius: 6px; margin-bottom: 10px; }
        .mini-title { font-size: 13px; font-weight: 600; color: var(--dark); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 38px; }

        /* RESPONSIVE KHUSUS KONTEN */
        @media (max-width: 900px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .welcome-banner { flex-direction: column; text-align: center; }
            .c1, .c2 { display: none; }
            .new-books-grid { grid-template-columns: repeat(2, 1fr); }
            .loan-card { flex-direction: column; gap: 15px; }
            .book-cover { width: 100px; height: 140px; margin: 0 auto; }
            .loan-details { text-align: center; width: 100%; }
        }
    </style>
</head>
<body>

    <?php include "layout_menu.php"; ?>

    <div class="main">
        
        <div class="welcome-banner">
            <div class="circle-bg c1"></div> <div class="circle-bg c2"></div>
            
            <?php if($siswa['gambar']): ?>
                <img src="data:image/jpeg;base64,<?= base64_encode($siswa['gambar']) ?>" class="profile-pic">
            <?php else: ?>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($siswa['nama']) ?>&background=random&color=fff" class="profile-pic">
            <?php endif; ?>
            
            <div class="welcome-text">
                <h2>Halo, <?= htmlspecialchars($siswa['nama']) ?>!</h2>
                <p>Selamat datang di E-Library. <br>Kelas: <?= $siswa['kelas'] ?> | NIS: <?= $siswa['nis'] ?></p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0ffef; color:#16a34a;"><i class="fas fa-book-open"></i></div>
                <div class="stat-info"><h3><?= $hitung_aktif ?></h3><p>Pinjaman Aktif</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e3fdfd; color:#008da6;"><i class="fas fa-history"></i></div>
                <div class="stat-info"><h3><?= $hitung_total ?></h3><p>Total Riwayat</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1; color:#d97706;"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info"><h3><?= $hitung_kembali ?></h3><p>Buku Kembali</p></div>
            </div>
        </div>

        <div>
            <div class="section-title"><i class="fas fa-bookmark" style="color:var(--primary)"></i> Pinjaman Saya</div>
            
            <div class="active-loans">
                <?php
                $q_pinjam = mysqli_query($conn, "SELECT * FROM peminjaman JOIN buku ON peminjaman.id_buku=buku.id_buku WHERE nis='$nis' AND status != 'kembali' ORDER BY id_peminjaman DESC");
                
                if(mysqli_num_rows($q_pinjam) == 0) {
                    echo "<div style='background:white; padding:30px; border-radius:12px; text-align:center; color:#999; border:1px dashed #ddd;'>
                            <i class='fas fa-folder-open' style='font-size:30px; margin-bottom:10px; display:block;'></i>
                            Tidak ada buku yang sedang dipinjam.
                          </div>";
                }

                while($row = mysqli_fetch_array($q_pinjam)) {
                    // Tentukan Badge & Tombol
                    if($row['status'] == 'menunggu_persetujuan') {
                        $badge_html = '<span class="badge bg-warning">Menunggu Konfirmasi</span>';
                        $btn_html = '<span style="font-size:12px; color:#888;"><i class="fas fa-clock"></i> Menunggu Admin</span>';
                    } elseif ($row['status'] == 'dipinjam') {
                        $badge_html = '<span class="badge bg-success">Sedang Dipinjam</span>';
                        $btn_html = '<button onclick="kembalikanBuku('.$row['id_peminjaman'].', \''.addslashes($row['judul']).'\')" class="btn-return"><i class="fas fa-undo"></i> Kembalikan</button>';
                    } else {
                        $badge_html = '<span class="badge bg-info">Verifikasi Pengembalian</span>';
                        $btn_html = '<span style="font-size:12px; color:#008da6;"><i class="fas fa-spinner"></i> Cek Admin</span>';
                    }
                ?>
                
                <div class="loan-card">
                    <?php if($row['gambar']): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($row['gambar']) ?>" class="book-cover">
                    <?php else: ?>
                        <div class="book-cover" style="display:flex; align-items:center; justify-content:center; font-size:10px; color:#999;">No Img</div>
                    <?php endif; ?>
                    
                    <div class="loan-details">
                        <div style="margin-bottom:5px;"><?= $badge_html ?></div>
                        <h4><?= $row['judul'] ?></h4>
                        <p><?= $row['pengarang'] ?> • <?= $row['penerbit'] ?></p>
                        
                        <?php if($row['status'] == 'dipinjam'): ?>
                            <div style="font-size:12px; color:var(--danger); margin-bottom:10px;">
                                <i class="fas fa-calendar-alt"></i> Tempo: <b><?= date('d M Y', strtotime($row['tanggal_kembali'])) ?></b>
                            </div>
                        <?php endif; ?>
                        
                        <?= $btn_html ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <div style="margin-top: 25px;">
            <div class="section-title">
                <i class="fas fa-star" style="color:#fdac41"></i> Koleksi Terbaru
                <a href="buku.php" style="font-size:12px; margin-left:auto; text-decoration:none; color:var(--primary);">Lihat Semua</a>
            </div>
            
            <div class="new-books-grid">
                <?php
                $q_buku = mysqli_query($conn, "SELECT * FROM buku ORDER BY id_buku DESC LIMIT 6");
                while($b = mysqli_fetch_array($q_buku)) {
                ?>
                <a href="buku.php?id=<?= $b['id_buku'] ?>" class="mini-book">
                    <?php if($b['gambar']): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($b['gambar']) ?>" class="mini-cover">
                    <?php else: ?>
                        <div class="mini-cover" style="display:flex; align-items:center; justify-content:center; background:#eee; color:#999;">No Cover</div>
                    <?php endif; ?>
                    <div class="mini-title"><?= $b['judul'] ?></div>
                </a>
                <?php } ?>
            </div>
        </div>

    </div>

    <script>
        function kembalikanBuku(id, judul) {
            Swal.fire({
                title: 'Kembalikan Buku?',
                text: "Serahkan buku '" + judul + "' ke Admin setelah ini.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#435ebe',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Saya Paham'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'dashboard.php?aksi=ajukan_kembali&id=' + id;
                }
            })
        }
    </script>

    <?php if(isset($_SESSION['swal'])): ?>
    <script>
        Swal.fire({
            icon: '<?= $_SESSION['swal']['icon'] ?>',
            title: '<?= $_SESSION['swal']['title'] ?>',
            text: '<?= $_SESSION['swal']['text'] ?>',
            confirmButtonColor: '#435ebe'
        });
    </script>
    <?php unset($_SESSION['swal']); endif; ?>

</body>
</html>