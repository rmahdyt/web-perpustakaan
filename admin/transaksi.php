<?php
include "../config/session_admin.php";
include "../config/koneksi.php";

$username = $_SESSION['admin'];
$admin = mysqli_fetch_array(mysqli_query($conn, "SELECT nama_admin, foto FROM admin WHERE username='$username'"));

// --- LOGIKA PHP (SERVER SIDE) TETAP SAMA ---

// 1. TERIMA Peminjaman
if (isset($_GET['aksi']) && $_GET['aksi'] == 'terima_pinjam') {
    $id_pinjam = $_GET['id'];
    mysqli_query($conn, "UPDATE peminjaman SET status='dipinjam' WHERE id_peminjaman='$id_pinjam'");
    $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Disetujui!', 'text' => 'Siswa boleh membawa buku.'];
    header("Location: transaksi.php");
    exit;
}

// 2. TOLAK Peminjaman
if (isset($_GET['aksi']) && $_GET['aksi'] == 'tolak_pinjam') {
    $id_pinjam = $_GET['id'];
    $id_buku   = $_GET['buku'];
    mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku='$id_buku'");
    mysqli_query($conn, "DELETE FROM peminjaman WHERE id_peminjaman='$id_pinjam'");
    $_SESSION['swal'] = ['icon' => 'info', 'title' => 'Ditolak', 'text' => 'Stok buku dikembalikan.'];
    header("Location: transaksi.php");
    exit;
}

// 3. TERIMA PENGEMBALIAN (LOGIKA DENDA)
if (isset($_GET['aksi']) && $_GET['aksi'] == 'terima_kembali') {
    $id_pinjam = $_GET['id'];
    $id_buku   = $_GET['buku'];
    
    // A. Ambil Data Tanggal Jatuh Tempo
    $q_cek = mysqli_query($conn, "SELECT tanggal_kembali FROM peminjaman WHERE id_peminjaman='$id_pinjam'");
    $data_pinjam = mysqli_fetch_array($q_cek);
    
    $tgl_jatuh_tempo = $data_pinjam['tanggal_kembali'];
    $tgl_dikembalikan = date('Y-m-d'); // Hari ini

    // B. Hitung Selisih Hari
    $deadline = new DateTime($tgl_jatuh_tempo);
    $sekarang = new DateTime($tgl_dikembalikan);
    
    $denda = 0;
    $tarif_awal = 5000; 

    // C. Logika Denda Tambah Flat (Denda Awal * Jumlah Hari)
    if ($sekarang > $deadline) {
        $selisih = $sekarang->diff($deadline);
        $jumlah_hari_telat = $selisih->days;
        
        // Rumus Baru: Tarif Awal dikali Jumlah Hari
        $denda = $tarif_awal * $jumlah_hari_telat;
    }
    
    // D. Update Database
    mysqli_query($conn, "UPDATE peminjaman SET status='kembali' WHERE id_peminjaman='$id_pinjam'");
    mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku='$id_buku'");
    mysqli_query($conn, "INSERT INTO pengembalian (id_peminjaman, tgl_pengembalian, denda) VALUES ('$id_pinjam', '$tgl_dikembalikan', '$denda')");
    
    // E. Pesan Notifikasi
    if ($denda > 0) {
        $rupiah = "Rp " . number_format($denda,0,',','.');
        $_SESSION['swal'] = [
            'icon' => 'warning', 
            'title' => 'Terlambat!', 
            'text' => "Telat $jumlah_hari_telat hari. Denda Berlipat: $rupiah"
        ];
    } else {
        $_SESSION['swal'] = [
            'icon' => 'success', 
            'title' => 'Buku Diterima', 
            'text' => 'Pengembalian tepat waktu.'
        ];
    }

    header("Location: transaksi.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Transaksi Peminjaman | Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* --- CSS KHUSUS HALAMAN TRANSAKSI --- */
        
        .main { display: flex; flex-direction: column; gap: 25px; }

        /* Panels Style Overrides */
        .panel {
            background: var(--white); border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.04); overflow: hidden; border: 1px solid transparent;
        }
        .panel-header {
            padding: 15px 25px; border-bottom: 1px solid #eee;
            display: flex; align-items: center; gap: 10px;
        }
        .panel-header h3 { margin: 0; font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Header Colors */
        .header-warning { background: #fff8e1; color: #d97706; border-left: 5px solid #d97706; }
        .header-danger { background: #fff5f5; color: var(--danger); border-left: 5px solid var(--danger); }
        .header-success { background: #e3fdfd; color: #008da6; border-left: 5px solid #008da6; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; }
        th { background: #fcfcfc; color: #607080; font-weight: 600; font-size: 13px; text-transform: uppercase; padding: 15px 25px; text-align: left; }
        td { padding: 15px 25px; border-bottom: 1px solid #eee; font-size: 14px; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        
        .book-item { display: flex; align-items: center; gap: 15px; }
        .book-thumb {
            width: 40px; height: 55px; object-fit: cover; border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); background: #eee; flex-shrink: 0;
        }

        /* Buttons */
        .btn-action {
            padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
            transition: 0.2s; border: none; cursor: pointer; justify-content: center;
        }
        .btn-accept { background: var(--success); color: white; }
        .btn-accept:hover { background: #2cc678; box-shadow: 0 4px 10px rgba(57, 218, 138, 0.3); }
        
        .btn-reject { background: #ffeaea; color: var(--danger); }
        .btn-reject:hover { background: var(--danger); color: white; }

        .btn-verify { background: var(--primary); color: white; }
        .btn-verify:hover { background: #354a96; box-shadow: 0 4px 10px rgba(67, 94, 190, 0.3); }

        /* Badges */
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-deadline { background: #ffeaea; color: var(--danger); }
        .badge-active { background: #e0ffef; color: var(--success); }

        /* --- MOBILE TABLE (Card View) --- */
        @media (max-width: 768px) {
            /* Sembunyikan Header Tabel */
            table thead { display: none; }
            table, tbody, tr, td { display: block; width: 100%; box-sizing: border-box; }

            /* Ubah Baris jadi Card */
            table tbody tr {
                background: var(--white);
                margin-bottom: 15px; border-bottom: 2px solid #f0f0f0; padding: 15px;
            }
            table tbody tr:last-child { border-bottom: none; margin-bottom: 0; }

            table tbody td { text-align: left; padding: 5px 0; border: none; }

            .book-item { border-bottom: 1px dashed #eee; padding-bottom: 10px; margin-bottom: 10px; }
            .book-thumb { width: 50px; height: 70px; }

            td:last-child { margin-top: 10px; }
            td:last-child div { width: 100%; } /* Wrapper tombol */
            .btn-action { flex: 1; height: 40px; font-size: 14px; }
        }
    </style>
</head>
<body>

    <?php include "layout_menu.php"; ?>

    <div class="main">
        
        <div class="panel">
            <div class="panel-header header-warning">
                <i class="fas fa-clock"></i>
                <h3>Request Peminjaman</h3>
            </div>
            <div>
                <table>
                    <thead>
                        <tr>
                            <th>Buku</th>
                            <th>Peminjam</th>
                            <th>Tgl Request</th>
                            <th width="220">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q_pinjam = mysqli_query($conn, "SELECT * FROM peminjaman JOIN siswa ON peminjaman.nis=siswa.nis JOIN buku ON peminjaman.id_buku=buku.id_buku WHERE status='menunggu_persetujuan'");
                        
                        if(mysqli_num_rows($q_pinjam) == 0) {
                            echo "<tr><td colspan='4' style='text-align:center; padding:30px; color:#999;'>Tidak ada request baru.</td></tr>";
                        }
                        
                        while($row = mysqli_fetch_array($q_pinjam)){
                        ?>
                        <tr>
                            <td>
                                <div class="book-item">
                                    <?php if($row['gambar']): ?>
                                        <img src="data:image/jpeg;base64,<?= base64_encode($row['gambar']) ?>" class="book-thumb">
                                    <?php else: ?>
                                        <div class="book-thumb" style="display:flex;align-items:center;justify-content:center;font-size:10px;">No Img</div>
                                    <?php endif; ?>
                                    <div>
                                        <div style="font-weight:600; color:var(--dark);"><?= $row['judul'] ?></div>
                                        <div style="font-size:12px; color:#888;"><?= $row['penerbit'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:600; font-size:14px;"><?= $row['nama'] ?></div>
                                <div style="font-size:12px; color:#888;"><?= $row['kelas'] ?></div>
                            </td>
                            <td>
                                <span style="font-size:13px; color:#555;">
                                    <i class="far fa-calendar-alt"></i> <?= date('d M', strtotime($row['tanggal_pinjam'])) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; gap:8px; width:100%;">
                                    <button class="btn-action btn-accept" onclick="confirmAction('terima_pinjam', <?= $row['id_peminjaman'] ?>)">
                                        <i class="fas fa-check"></i> Terima
                                    </button>
                                    <button class="btn-action btn-reject" onclick="confirmAction('tolak_pinjam', <?= $row['id_peminjaman'] ?>, <?= $row['id_buku'] ?>)">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header header-danger">
                <i class="fas fa-undo"></i>
                <h3>Verifikasi Kembali</h3>
            </div>
            <div>
                <table>
                    <thead>
                        <tr>
                            <th>Buku</th>
                            <th>Peminjam</th>
                            <th>Deadline</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
$q_balik = mysqli_query($conn, "SELECT * FROM peminjaman JOIN siswa ON peminjaman.nis=siswa.nis JOIN buku ON peminjaman.id_buku=buku.id_buku WHERE status='menunggu_pengembalian'");

if(mysqli_num_rows($q_balik) == 0) {
    echo "<tr><td colspan='4' style='text-align:center; padding:30px; color:#999;'>Tidak ada pengembalian.</td></tr>";
}

while($row = mysqli_fetch_array($q_balik)){
    // --- LOGIKA TAMPILAN PREVIEW (DIUBAH MENJADI FLAT) ---
    $tgl_kembali_sistem = new DateTime($row['tanggal_kembali']);
    $hari_ini = new DateTime(date('Y-m-d')); // Pastikan hanya tanggal hari ini tanpa jam
    $badge_status = "";
    
    if($hari_ini > $tgl_kembali_sistem) {
        $selisih = $hari_ini->diff($tgl_kembali_sistem)->days;
        
        // Rumus Flat: Tarif (5000) * Jumlah Hari
        $est_denda = 5000 * $selisih; 
        
        $est_denda_fmt = number_format($est_denda, 0, ',', '.');
        $badge_status = "<br><span style='color:red; font-size:11px; font-weight:bold;'>Telat $selisih Hari (Estimasi Denda: Rp $est_denda_fmt)</span>";
    }
?>
                        <tr>
                            <td>
                                <div style="font-weight:600; color:var(--dark);"><?= $row['judul'] ?></div>
                            </td>
                            <td><?= $row['nama'] ?> <br><span style="font-size:12px;color:#999;"><?= $row['kelas'] ?></span></td>
                            <td>
                                <span class="badge badge-deadline">
                                    <i class="fas fa-clock"></i> <?= date('d M', strtotime($row['tanggal_kembali'])) ?>
                                </span>
                                <?= $badge_status ?>
                            </td>
                            <td>
                                <button class="btn-action btn-verify" style="width:100%;" onclick="confirmAction('terima_kembali', <?= $row['id_peminjaman'] ?>, <?= $row['id_buku'] ?>)">
                                    <i class="fas fa-box-open"></i> Terima Buku
                                </button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header header-success">
                <i class="fas fa-book-reader"></i>
                <h3>Sedang Dipinjam</h3>
            </div>
            <div>
                <table>
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Peminjam</th>
                            <th>Buku</th>
                            <th>Batas Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $q_aktif = mysqli_query($conn, "SELECT * FROM peminjaman JOIN siswa ON peminjaman.nis=siswa.nis JOIN buku ON peminjaman.id_buku=buku.id_buku WHERE status='dipinjam' ORDER BY tanggal_kembali ASC");
                        
                        if(mysqli_num_rows($q_aktif) == 0) {
                            echo "<tr><td colspan='4' style='text-align:center; padding:30px; color:#999;'>Tidak ada data aktif.</td></tr>";
                        }
                        
                        while($row = mysqli_fetch_array($q_aktif)){
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <div style="font-weight:600;"><?= $row['nama'] ?></div>
                                <div style="font-size:12px; color:#888;">NIS: <?= $row['nis'] ?></div>
                            </td>
                            <td><?= $row['judul'] ?></td>
                            <td style="color:var(--danger); font-weight:500;">
                                <?= date('d M Y', strtotime($row['tanggal_kembali'])) ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        function confirmAction(action, id, buku = null) {
            let title, text, icon, confirmColor, confirmText, url;

            if (action === 'terima_pinjam') {
                title = 'Izinkan Pinjam?'; text = 'Siswa akan diperbolehkan mengambil buku.'; icon = 'question'; confirmColor = '#39da8a'; confirmText = 'Ya, Izinkan';
                url = `transaksi.php?aksi=terima_pinjam&id=${id}`;
            } else if (action === 'tolak_pinjam') {
                title = 'Tolak Request?'; text = 'Stok buku akan dikembalikan.'; icon = 'warning'; confirmColor = '#ff5b5c'; confirmText = 'Ya, Tolak';
                url = `transaksi.php?aksi=tolak_pinjam&id=${id}&buku=${buku}`;
            } else if (action === 'terima_kembali') {
                title = 'Terima Buku?'; text = 'Sistem akan menghitung denda otomatis jika terlambat.'; icon = 'info'; confirmColor = '#435ebe'; confirmText = 'Ya, Terima';
                url = `transaksi.php?aksi=terima_kembali&id=${id}&buku=${buku}`;
            }

            Swal.fire({
                title: title, text: text, icon: icon, showCancelButton: true,
                confirmButtonColor: confirmColor, cancelButtonColor: '#d33',
                confirmButtonText: confirmText, cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) { window.location.href = url; }
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