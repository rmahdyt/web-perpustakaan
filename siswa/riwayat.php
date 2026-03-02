<?php
include "../config/session_siswa.php";
include "../config/koneksi.php";
$nis = $_SESSION['nis'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Riwayat Peminjaman | E-Library</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* --- CSS KHUSUS HALAMAN RIWAYAT --- */
        
        .page-header { margin-bottom: 25px; }
        .page-header h2 { margin: 0; color: var(--dark); font-weight: 600; font-size: 20px; }
        .page-header p { margin: 5px 0 0; color: var(--grey); font-size: 13px; }

        /* HISTORY GRID */
        .history-grid {
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .history-card {
            background: white; border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03); border: 1px solid #eee;
            padding: 20px; display: flex; flex-direction: column; gap: 15px;
            transition: 0.2s; position: relative; overflow: hidden;
        }
        .history-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); border-color: var(--primary); }

        .border-left-success { border-left: 5px solid var(--success); }

        /* BOOK SECTION (TOP) */
        .book-section { display: flex; gap: 15px; border-bottom: 1px dashed #eee; padding-bottom: 15px; }
        .book-cover { 
            width: 50px; height: 75px; border-radius: 6px; object-fit: cover; 
            background: #eee; flex-shrink: 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .no-cover {
            width: 50px; height: 75px; background: #f0f0f0; border-radius: 6px;
            display: flex; align-items: center; justify-content: center; font-size: 9px; color: #999;
        }

        /* INFO SECTION (MIDDLE) */
        .info-section { display: flex; justify-content: space-between; font-size: 12px; color: var(--grey); }
        .info-item { display: flex; flex-direction: column; gap: 2px; }
        .info-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { font-weight: 600; color: var(--dark); font-size: 13px; }

        /* DENDA STATUS (BOTTOM) */
        .denda-box {
            background: #fff5f5; color: var(--danger); padding: 8px; border-radius: 8px;
            font-size: 12px; font-weight: 600; text-align: center; margin-top: 5px;
        }
        .no-denda {
            background: #f0fdf4; color: var(--success); padding: 8px; border-radius: 8px;
            font-size: 12px; font-weight: 600; text-align: center; margin-top: 5px;
        }

        /* EMPTY STATE */
        .empty-state {
            grid-column: 1 / -1; padding: 50px; text-align: center; color: #999;
            background: white; border-radius: 12px; border: 1px dashed #ddd;
        }

        @media (max-width: 768px) {
            .history-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <?php include "layout_menu.php"; ?>

    <div class="main">
        
        <div class="page-header">
            <h2>Riwayat Peminjaman</h2>
            <p>Daftar buku yang sudah kamu kembalikan.</p>
        </div>

        <div class="history-grid">
            <?php
            // Query ambil data pengembalian (Join 3 tabel: pengembalian -> peminjaman -> buku)
            $query = mysqli_query($conn, "SELECT * FROM pengembalian 
                                          JOIN peminjaman ON pengembalian.id_peminjaman = peminjaman.id_peminjaman 
                                          JOIN buku ON peminjaman.id_buku = buku.id_buku 
                                          WHERE peminjaman.nis='$nis' 
                                          ORDER BY id_pengembalian DESC");
            
            if(mysqli_num_rows($query) == 0) {
                echo "<div class='empty-state'>
                        <i class='fas fa-folder-open' style='font-size: 40px; margin-bottom: 10px; opacity: 0.5;'></i>
                        <p>Belum ada riwayat pengembalian buku.</p>
                      </div>";
            }

            while($row = mysqli_fetch_array($query)) {
                $tgl_pinjam = date('d M Y', strtotime($row['tanggal_pinjam']));
                $tgl_batas  = date('d M Y', strtotime($row['tanggal_kembali'])); 
                $tgl_balik  = date('d M Y', strtotime($row['tgl_pengembalian']));
                
                // Cek Telat (Untuk warna merah pada tanggal batas)
                $is_late = strtotime($row['tgl_pengembalian']) > strtotime($row['tanggal_kembali']);
                $style_batas = $is_late ? "color:var(--danger);" : "";

                $denda_val = $row['denda'];
                $denda_fmt = "Rp " . number_format($denda_val, 0, ',', '.');
            ?>

            <div class="history-card border-left-success">
                
                <div class="book-section">
                    <?php if($row['gambar']): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($row['gambar']) ?>" class="book-cover">
                    <?php else: ?>
                        <div class="no-cover">No Img</div>
                    <?php endif; ?>
                    
                    <div style="flex:1;">
                        <div style="font-weight:700; color:var(--dark); margin-bottom:3px; font-size:15px; line-height:1.2;"><?= $row['judul'] ?></div>
                        <div style="font-size:12px; color:var(--grey); margin-bottom:5px;">ID Trx: #<?= $row['id_peminjaman'] ?></div>
                        <span style="background:#e0ffef; color:#16a34a; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:700;">
                            DIKEMBALIKAN
                        </span>
                    </div>
                </div>

                <div class="info-section">
                    <div class="info-item">
                        <span class="info-label">Pinjam</span>
                        <span class="info-value"><?= $tgl_pinjam ?></span>
                    </div>

                    <div class="info-item" style="text-align:center;">
                        <span class="info-label">Batas Waktu</span>
                        <span class="info-value" style="<?= $style_batas ?>"><?= $tgl_batas ?></span>
                    </div>

                    <div class="info-item" style="text-align:right;">
                        <span class="info-label">Kembali</span>
                        <span class="info-value"><?= $tgl_balik ?></span>
                    </div>
                </div>

                <?php if($denda_val > 0): ?>
                    <div class="denda-box">
                        <i class="fas fa-exclamation-circle"></i> Terkena Denda: <?= $denda_fmt ?>
                    </div>
                <?php else: ?>
                    <div class="no-denda">
                        <i class="fas fa-check-circle"></i> Tidak Ada Denda
                    </div>
                <?php endif; ?>

            </div>

            <?php } ?>
        </div>

    </div>

</body>
</html>