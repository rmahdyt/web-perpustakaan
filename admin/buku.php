<?php
include "../config/session_admin.php";
include "../config/koneksi.php";

// --- LOGIKA PHP (CRUD) TETAP SAMA ---

// 1. TAMBAH & EDIT BUKU
if (isset($_POST['simpan'])) {
    $id_buku   = $_POST['id_buku'];
    $judul     = mysqli_real_escape_string($conn, $_POST['judul']);
    $pengarang = mysqli_real_escape_string($conn, $_POST['pengarang']);
    $penerbit  = mysqli_real_escape_string($conn, $_POST['penerbit']);
    $tahun     = $_POST['tahun'];
    $stok      = $_POST['stok'];

    // Cek Gambar
    $gambar_sql = ""; 
    $file_content = NULL;
    if (!empty($_FILES['gambar']['tmp_name'])) {
        $file_content = file_get_contents($_FILES['gambar']['tmp_name']);
        $file_content = mysqli_real_escape_string($conn, $file_content);
        $gambar_sql   = ", gambar='$file_content'";
    }

    if ($id_buku == "") {
        // --- INSERT ---
        if ($file_content) {
            $query = mysqli_query($conn, "INSERT INTO buku (judul, pengarang, penerbit, tahun, stok, gambar) VALUES ('$judul', '$pengarang', '$penerbit', '$tahun', '$stok', '$file_content')");
        } else {
            $query = mysqli_query($conn, "INSERT INTO buku (judul, pengarang, penerbit, tahun, stok) VALUES ('$judul', '$pengarang', '$penerbit', '$tahun', '$stok')");
        }
        $msg_title = "Berhasil Ditambahkan!";
    } else {
        // --- UPDATE ---
        $query = mysqli_query($conn, "UPDATE buku SET judul='$judul', pengarang='$pengarang', penerbit='$penerbit', tahun='$tahun', stok='$stok' $gambar_sql WHERE id_buku='$id_buku'");
        $msg_title = "Berhasil Diperbarui!";
    }

    if($query) {
        $_SESSION['swal'] = ['icon' => 'success', 'title' => $msg_title, 'text' => 'Data buku telah disimpan.'];
        header("Location: buku.php"); exit;
    } else {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Gagal!', 'text' => mysqli_error($conn)];
    }
}

// 2. HAPUS BUKU
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM buku WHERE id_buku='$id'");
    $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Terhapus!', 'text' => 'Buku berhasil dihapus.'];
    header("Location: buku.php"); exit;
}

// 3. AMBIL DATA UNTUK EDIT
$edit_id = ""; $edit_judul = ""; $edit_pengarang = ""; $edit_penerbit = ""; 
$edit_tahun = ""; $edit_stok = ""; 

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $data = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM buku WHERE id_buku='$id'"));
    if($data) {
        $edit_id = $data['id_buku']; $edit_judul = $data['judul']; $edit_pengarang = $data['pengarang'];
        $edit_penerbit = $data['penerbit']; $edit_tahun = $data['tahun']; $edit_stok = $data['stok'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Buku | Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* --- CSS KHUSUS HALAMAN INI (Layout Form & Kartu Buku) --- */
        
        /* Layout Split: Kiri Form, Kanan List */
        .content-grid {
            display: grid;
            grid-template-columns: 320px 1fr; 
            gap: 25px; align-items: start;
        }

        /* Panel Style */
        .panel {
            background: white; border-radius: 12px; padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.04);
        }
        .panel-header { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .panel-header h3 { margin: 0; font-size: 18px; color: var(--dark); font-weight: 700; }

        /* Form Elements */
        .sticky-form { position: sticky; top: 30px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #607080; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9; font-size: 14px; margin-bottom: 15px; }
        input:focus { border-color: var(--primary); background: white; outline: none; }

        .btn-submit { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: #354a96; transform: translateY(-2px); }
        .btn-cancel { display: block; width: 100%; text-align: center; padding: 10px; margin-top: 10px; background: #fff5f5; color: var(--danger); text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;}

        /* Book List Styles */
        .book-list { display: flex; flex-direction: column; gap: 15px; }

        .book-card {
            display: flex; background: #fff; border: 1px solid transparent;
            border-radius: 12px; padding: 15px; align-items: center; gap: 15px;
            transition: 0.2s; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.04);
        }
        .book-card:hover { transform: translateY(-3px); border-color: var(--primary); }

        .book-img {
            width: 60px; height: 85px; border-radius: 6px; object-fit: cover;
            flex-shrink: 0; background: #eee; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .book-info { flex: 1; min-width: 0; }
        .book-title { font-weight: 700; color: var(--dark); font-size: 15px; margin-bottom: 4px; line-height: 1.3; }
        .book-meta { font-size: 12px; color: var(--grey); margin-bottom: 5px; }
        .stock-pill { background: #e0ffef; color: var(--success); padding: 3px 10px; border-radius: 15px; font-size: 11px; font-weight: 700; display: inline-block; }

        .book-actions { display: flex; gap: 8px; }
        .action-btn {
            width: 35px; height: 35px; border-radius: 8px; display: flex;
            align-items: center; justify-content: center; text-decoration: none;
            transition: 0.2s; font-size: 14px;
        }
        .btn-edit { background: #fff4e3; color: var(--warning); }
        .btn-del { background: #ffeaea; color: var(--danger); }

        /* Responsive Fixes */
        @media (max-width: 900px) {
            .content-grid { grid-template-columns: 1fr; } 
            .sticky-form { position: static; margin-bottom: 20px; }
        }

        @media (max-width: 768px) {
            .book-card { padding-right: 15px; } 
            .book-actions {
                flex-direction: column; 
                position: absolute; right: 15px; top: 15px;
            }
            .book-title { padding-right: 40px; }
        }
    </style>
</head>
<body>

    <?php include "layout_menu.php"; ?>

    <div class="main">
        <div class="content-grid">
            
            <div class="panel sticky-form">
                <div class="panel-header">
                    <h3><?= $edit_id ? 'Edit Buku' : 'Tambah Buku' ?></h3>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_buku" value="<?= $edit_id ?>">
                    
                    <label>Judul Buku</label>
                    <input type="text" name="judul" value="<?= $edit_judul ?>" required placeholder="Masukkan judul...">

                    <label>Pengarang</label>
                    <input type="text" name="pengarang" value="<?= $edit_pengarang ?>" required placeholder="Nama penulis...">

                    <label>Penerbit</label>
                    <input type="text" name="penerbit" value="<?= $edit_penerbit ?>" placeholder="Nama penerbit...">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div><label>Tahun</label><input type="number" name="tahun" value="<?= $edit_tahun ?>" required placeholder="2024"></div>
                        <div><label>Stok</label><input type="number" name="stok" value="<?= $edit_stok ?>" required placeholder="0"></div>
                    </div>

                    <label>Cover Buku</label>
                    <input type="file" name="gambar" accept="image/*">
                    <?php if($edit_id): ?><p style="font-size:11px;color:#999;margin-top:-10px;">*Biarkan kosong jika tidak diganti.</p><?php endif; ?>

                    <button type="submit" name="simpan" class="btn-submit">
                        <i class="fas fa-save"></i> <?= $edit_id ? 'Simpan' : 'Tambah' ?>
                    </button>
                    <?php if($edit_id): ?><a href="buku.php" class="btn-cancel">Batal</a><?php endif; ?>
                </form>
            </div>

            <div style="width: 100%;"> <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; color: var(--dark); font-size:18px;">Daftar Buku</h3>
                    <span style="font-size: 13px; color: #777; background:white; padding:5px 15px; border-radius:20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        Total: <b><?= mysqli_num_rows(mysqli_query($conn, "SELECT * FROM buku")) ?></b>
                    </span>
                </div>

                <div class="book-list">
                    <?php
                    $query = mysqli_query($conn, "SELECT * FROM buku ORDER BY id_buku DESC");
                    if(mysqli_num_rows($query) > 0) {
                        while ($row = mysqli_fetch_array($query)) {
                    ?>
                    
                    <div class="book-card">
                        <?php if($row['gambar']): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($row['gambar']) ?>" class="book-img">
                        <?php else: ?>
                            <div class="book-img" style="display:flex;align-items:center;justify-content:center;color:#999;font-size:10px;">No Cover</div>
                        <?php endif; ?>

                        <div class="book-info">
                            <div class="book-title"><?= $row['judul'] ?></div>
                            <div class="book-meta">
                                <i class="fas fa-pen"></i> <?= $row['pengarang'] ?> &bull; <?= $row['tahun'] ?>
                            </div>
                            <div class="stock-pill">Stok: <?= $row['stok'] ?></div>
                        </div>

                        <div class="book-actions">
                            <a href="buku.php?edit=<?= $row['id_buku'] ?>" class="action-btn btn-edit"><i class="fas fa-pencil-alt"></i></a>
                            <button class="action-btn btn-del" onclick="confirmHapus(<?= $row['id_buku'] ?>)"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>

                    <?php 
                        }
                    } else {
                        echo "<div style='text-align:center; padding: 40px; color:#999; background:white; border-radius:12px; box-shadow: 0 5px 15px rgba(0,0,0,0.04);'>Belum ada data buku.</div>";
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

    <script>
        function confirmHapus(id) {
            Swal.fire({
                title: 'Hapus Buku?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff5b5c',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'buku.php?hapus=' + id;
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
            confirmButtonColor: '#435ebe', timer: 2000, showConfirmButton: false
        });
    </script>
    <?php unset($_SESSION['swal']); endif; ?>

</body>
</html>