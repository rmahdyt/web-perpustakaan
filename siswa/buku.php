<?php
include "../config/session_siswa.php";
include "../config/koneksi.php";

$nis = $_SESSION['nis'];

// --- LOGIKA PINJAM BUKU (TETAP SAMA) ---
if (isset($_GET['pinjam'])) {
    $id_buku = $_GET['pinjam'];
    
    // 1. Ambil data stok
    $cek_buku = mysqli_fetch_array(mysqli_query($conn, "SELECT stok, judul FROM buku WHERE id_buku='$id_buku'"));
    
    // 2. Cek duplikasi pinjam (buku yang sama dan belum kembali)
    $cek_pinjam = mysqli_query($conn, "SELECT * FROM peminjaman WHERE nis='$nis' AND id_buku='$id_buku' AND status != 'kembali'");

    if (mysqli_num_rows($cek_pinjam) > 0) {
        $_SESSION['swal'] = [
            'icon' => 'error',
            'title' => 'Oops...',
            'text' => 'Kamu sedang meminjam buku ini. Selesaikan dulu transaksi sebelumnya!'
        ];
        header("Location: buku.php"); exit;
    } elseif ($cek_buku['stok'] > 0) {
        // 3. Proses Pinjam
        $tgl_pinjam = date('Y-m-d');
        $tgl_kembali = date('Y-m-d', strtotime('+7 days'));
        
        mysqli_query($conn, "INSERT INTO peminjaman VALUES (NULL, '$nis', '$id_buku', '$tgl_pinjam', '$tgl_kembali', 'menunggu_persetujuan')");
        mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku='$id_buku'");
        
        $_SESSION['swal'] = [
            'icon' => 'success',
            'title' => 'Permintaan Terkirim!',
            'text' => 'Silakan tunggu persetujuan dari Admin.'
        ];
        header("Location: buku.php"); exit;
        
    } else {
        $_SESSION['swal'] = [
            'icon' => 'warning',
            'title' => 'Stok Habis',
            'text' => 'Yah, buku ini sedang kosong.'
        ];
        header("Location: buku.php"); exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Cari Buku | E-Library</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* --- CSS KHUSUS HALAMAN PENCARIAN BUKU --- */

        /* SEARCH BAR */
        .search-container { position: relative; margin-bottom: 25px; }
        .search-input {
            width: 100%; padding: 15px 20px 15px 50px; border-radius: 12px;
            border: 1px solid #ddd; background: white; font-family: inherit; font-size: 14px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03); transition: 0.3s; outline: none;
        }
        .search-input:focus { border-color: var(--primary); box-shadow: 0 4px 15px rgba(67, 94, 190, 0.1); }
        .search-icon { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: #aaa; }

        /* GRID BUKU */
        .book-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;
        }
        
        .book-card {
            background: white; border-radius: 12px; overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03); border: 1px solid #eee;
            transition: 0.3s; display: flex; flex-direction: column; position: relative;
        }
        .book-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); border-color: var(--primary); }
        
        .book-cover-container {
            width: 100%; height: 260px; background: #f9f9f9;
            position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center;
        }
        .book-cover { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
        .book-card:hover .book-cover { transform: scale(1.05); }

        .book-info { padding: 15px; flex: 1; display: flex; flex-direction: column; }
        
        .book-title {
            font-size: 15px; font-weight: 700; color: var(--dark); margin: 0 0 5px 0;
            line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .book-author { font-size: 12px; color: var(--grey); margin-bottom: 10px; }
        
        .stock-badge {
            font-size: 11px; padding: 3px 8px; border-radius: 4px;
            background: #e7f2ff; color: var(--primary); font-weight: 600;
            display: inline-block; margin-bottom: 15px; width: fit-content;
        }
        
        /* BUTTONS */
        .btn-pinjam {
            margin-top: auto; padding: 10px 0; width: 100%; text-align: center;
            background: var(--primary); color: white; border: none; border-radius: 8px;
            font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s;
        }
        .btn-pinjam:hover { background: #354a96; }
        
        .btn-habis {
            margin-top: auto; padding: 10px 0; width: 100%; text-align: center;
            background: #eee; color: #999; border-radius: 8px;
            font-size: 13px; font-weight: 600; cursor: not-allowed;
        }

        /* RESPONSIVE GRID */
        @media (max-width: 768px) {
            .book-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; }
            .book-cover-container { height: 200px; }
            .book-title { font-size: 14px; }
            .book-info { padding: 12px; }
        }
    </style>
</head>
<body>

    <?php include "layout_menu.php"; ?>

    <div class="main">
        <h2 style="margin-top:0; color:var(--dark); margin-bottom:20px;">Koleksi Pustaka</h2>
        
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" class="search-input" placeholder="Cari judul buku atau nama pengarang..." onkeyup="searchBook()">
        </div>

        <div class="book-grid" id="bookGrid">
            <?php
            $query = mysqli_query($conn, "SELECT * FROM buku ORDER BY judul ASC");
            while ($b = mysqli_fetch_array($query)) {
                $stok_ada = $b['stok'] > 0;
            ?>
            <div class="book-card item">
                <div class="book-cover-container">
                    <?php if($b['gambar']): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($b['gambar']) ?>" class="book-cover">
                    <?php else: ?>
                        <div style="font-size:12px; color:#aaa; display:flex; flex-direction:column; align-items:center;">
                            <i class="fas fa-image" style="font-size:24px; margin-bottom:5px;"></i> No Cover
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="book-info">
                    <div class="book-title"><?= $b['judul'] ?></div>
                    <div class="book-author"><i class="fas fa-pen-nib" style="font-size:10px;"></i> <?= $b['pengarang'] ?></div>
                    <div class="stock-badge">Stok: <?= $b['stok'] ?></div>

                    <?php if ($stok_ada): ?>
                        <button class="btn-pinjam" onclick="konfirmasiPinjam(<?= $b['id_buku'] ?>, '<?= addslashes($b['judul']) ?>')">
                            Pinjam
                        </button>
                    <?php else: ?>
                        <div class="btn-habis">Habis</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php } ?>
        </div>
        
        <div id="noResult" style="display:none; text-align:center; margin-top:50px; color:#999;">
            <i class="fas fa-box-open" style="font-size:40px; margin-bottom:10px; opacity:0.5;"></i>
            <p>Buku yang kamu cari tidak ditemukan.</p>
        </div>
    </div>

    <script>
        function searchBook() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let items = document.getElementsByClassName('item');
            let hasResult = false;
            for (let i = 0; i < items.length; i++) {
                let title = items[i].getElementsByClassName('book-title')[0].innerText.toLowerCase();
                let author = items[i].getElementsByClassName('book-author')[0].innerText.toLowerCase();
                if (title.includes(input) || author.includes(input)) {
                    items[i].style.display = "flex"; hasResult = true;
                } else { items[i].style.display = "none"; }
            }
            document.getElementById('noResult').style.display = hasResult ? "none" : "block";
        }

        function konfirmasiPinjam(id, judul) {
            Swal.fire({
                title: 'Pinjam buku ini?',
                text: judul,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#435ebe',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Ajukan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'buku.php?pinjam=' + id;
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
            confirmButtonColor: '#435ebe',
            timer: 3000
        });
    </script>
    <?php unset($_SESSION['swal']); endif; ?>

</body>
</html>