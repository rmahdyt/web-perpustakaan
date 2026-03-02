<?php
include "../config/session_admin.php";
include "../config/koneksi.php";

// --- INISIALISASI VARIABEL FORM ---
$edit_nis = ""; $edit_nama = ""; $edit_kelas = ""; $edit_username = ""; $mode_edit = false; 

// 1. AMBIL DATA EDIT
if (isset($_GET['edit'])) {
    $nis = $_GET['edit'];
    $query_data = mysqli_query($conn, "SELECT * FROM siswa WHERE nis='$nis'");
    $data = mysqli_fetch_array($query_data);
    if ($data) {
        $edit_nis = $data['nis']; $edit_nama = $data['nama']; 
        $edit_kelas = $data['kelas']; $edit_username = $data['username']; $mode_edit = true;
    }
}

// 2. SIMPAN DATA
if (isset($_POST['simpan'])) {
    $nis = $_POST['nis']; $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kelas = $_POST['kelas']; $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; $is_update = $_POST['is_update'];

    // Gambar
    $gambar_sql = ""; $file_content = NULL;
    if (!empty($_FILES['gambar']['tmp_name'])) {
        $file_content = file_get_contents($_FILES['gambar']['tmp_name']);
        $file_content = mysqli_real_escape_string($conn, $file_content);
        $gambar_sql   = ", gambar='$file_content'"; 
    }

    if ($is_update == "1") {
        $pass_sql = ""; if (!empty($password)) $pass_sql = ", password='$password'";
        $query = mysqli_query($conn, "UPDATE siswa SET nama='$nama', kelas='$kelas', username='$username' $pass_sql $gambar_sql WHERE nis='$nis'");
        $msg_title = "Data Diperbarui!";
    } else {
        $cek = mysqli_query($conn, "SELECT * FROM siswa WHERE nis='$nis' OR username='$username'");
        if (mysqli_num_rows($cek) > 0) {
            $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Gagal', 'text' => 'NIS atau Username sudah dipakai.'];
            header("Location: siswa.php"); exit;
        }
        if ($file_content) {
            $query = mysqli_query($conn, "INSERT INTO siswa (nis, nama, kelas, username, password, gambar) VALUES ('$nis', '$nama', '$kelas', '$username', '$password', '$file_content')");
        } else {
            $query = mysqli_query($conn, "INSERT INTO siswa (nis, nama, kelas, username, password) VALUES ('$nis', '$nama', '$kelas', '$username', '$password')");
        }
        $msg_title = "Siswa Ditambahkan!";
    }

    if ($query) {
        $_SESSION['swal'] = ['icon' => 'success', 'title' => $msg_title, 'text' => 'Data berhasil disimpan.'];
        header("Location: siswa.php"); exit;
    } else {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Gagal', 'text' => mysqli_error($conn)];
    }
}

// 3. HAPUS SISWA
if (isset($_GET['hapus'])) {
    $nis = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM siswa WHERE nis='$nis'");
    $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Terhapus!', 'text' => 'Data siswa dihapus.'];
    header("Location: siswa.php"); exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Data Siswa | Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* --- CSS KHUSUS HALAMAN SISWA --- */
        
        /* Grid Layout */
        .content-grid {
            display: grid;
            grid-template-columns: 320px 1fr; /* Form Kiri, List Kanan */
            gap: 25px; align-items: start;
        }

        /* Form Elements overrides for this page */
        .sticky-form { position: sticky; top: 30px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #607080; margin-bottom: 5px; }
        input, select { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9; font-size: 14px; margin-bottom: 15px; }
        input:focus, select:focus { border-color: var(--primary); background: white; outline: none; }
        input[readonly] { background-color: #eee; cursor: not-allowed; color: #888; }

        .btn-submit { width: 100%; padding: 12px; background: var(--success); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: #2cc678; transform: translateY(-2px); }
        .btn-cancel { display: block; width: 100%; text-align: center; padding: 10px; margin-top: 10px; background: #fff5f5; color: var(--danger); text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;}

        /* Panel Style */
        .panel {
            background: white; border-radius: 12px; padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.04);
        }
        .panel-header { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .panel-header h3 { margin: 0; font-size: 18px; color: var(--dark); font-weight: 700; }

        /* Student Card Styles */
        .student-list { display: flex; flex-direction: column; gap: 15px; }

        .student-card {
            display: flex; background: #fff; border: 1px solid transparent;
            border-radius: 12px; padding: 15px; align-items: center; gap: 15px;
            transition: 0.2s; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.04);
        }
        .student-card:hover { transform: translateY(-3px); border-color: var(--primary); }

        .student-avatar {
            width: 55px; height: 55px; border-radius: 50%; object-fit: cover;
            flex-shrink: 0; border: 2px solid #f0f0f0;
        }

        .student-info { flex: 1; min-width: 0; }
        .student-name { font-weight: 700; color: var(--dark); font-size: 15px; margin-bottom: 4px; }
        .student-meta { font-size: 13px; color: var(--grey); display: flex; align-items: center; gap: 8px; }
        .badge-nis { background: #e7f2ff; color: var(--primary); padding: 2px 8px; border-radius: 4px; font-family: monospace; font-weight: 600; font-size: 12px; }

        .student-actions { display: flex; gap: 8px; }
        .action-btn {
            width: 35px; height: 35px; border-radius: 8px; display: flex;
            align-items: center; justify-content: center; text-decoration: none;
            transition: 0.2s; cursor: pointer; border: none; font-size: 14px;
        }
        .btn-edit { background: #fff4e3; color: var(--warning); }
        .btn-del { background: #ffeaea; color: var(--danger); }

        /* Responsive */
        @media (max-width: 900px) {
            .content-grid { grid-template-columns: 1fr; }
            .sticky-form { position: static; margin-bottom: 20px; }
        }
        
        @media (max-width: 768px) {
            .student-card { padding-right: 15px; flex-wrap: wrap; padding-bottom: 50px; }
            .student-actions {
                position: absolute; bottom: 15px; right: 15px;
            }
        }
    </style>
</head>
<body>

    <?php include "layout_menu.php"; ?>

    <div class="main">
        <div class="content-grid">
            
            <div class="panel sticky-form">
                <div class="panel-header">
                    <h3><?= $mode_edit ? 'Edit Data' : 'Tambah Siswa' ?></h3>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="is_update" value="<?= $mode_edit ? '1' : '0' ?>">

                    <label>NIS (Nomor Induk)</label>
                    <input type="number" name="nis" placeholder="10xxx" value="<?= $edit_nis ?>" <?= $mode_edit ? 'readonly' : 'required' ?>>
                    <?php if($mode_edit): ?><p style="color:#d33; font-size:11px; margin-top:-10px;">*NIS tidak bisa diubah</p><?php endif; ?>

                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" placeholder="Nama Lengkap" value="<?= $edit_nama ?>" required>

                    <label>Kelas</label>
                    <select name="kelas" required>
                        <option value="">- Pilih Kelas -</option>
                        <?php
                        $list_kelas = ["X RPL 1", "X RPL 2", "XI RPL 1", "XI RPL 2", "XII RPL 1", "XII RPL 2"];
                        foreach($list_kelas as $k) {
                            $selected = ($edit_kelas == $k) ? "selected" : "";
                            echo "<option value='$k' $selected>$k</option>";
                        }
                        ?>
                    </select>

                    <label>Foto Profil</label>
                    <input type="file" name="gambar" accept="image/*">
                    
                    <hr style="border:0; border-top:1px dashed #ddd; margin: 15px 0;">

                    <label>Username</label>
                    <input type="text" name="username" value="<?= $edit_username ?>" required>

                    <label>Password</label>
                    <input type="text" name="password" placeholder="<?= $mode_edit ? 'Isi jika ganti pass' : 'Wajib diisi' ?>" <?= $mode_edit ? '' : 'required' ?>>

                    <button type="submit" name="simpan" class="btn-submit">
                        <i class="fas fa-save"></i> <?= $mode_edit ? 'Update' : 'Simpan' ?>
                    </button>

                    <?php if($mode_edit): ?><a href="siswa.php" class="btn-cancel">Batal</a><?php endif; ?>
                </form>
            </div>

            <div style="width: 100%;">
                <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; color: var(--dark); font-size: 18px;">Daftar Siswa</h3>
                    <span style="font-size: 13px; color: #777; background:white; padding:5px 15px; border-radius:20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        Total: <b><?= mysqli_num_rows(mysqli_query($conn, "SELECT * FROM siswa")) ?></b>
                    </span>
                </div>

                <div class="student-list">
                    <?php
                    $query = mysqli_query($conn, "SELECT * FROM siswa ORDER BY kelas ASC, nama ASC");
                    if(mysqli_num_rows($query) > 0) {
                        while ($row = mysqli_fetch_array($query)) {
                    ?>
                    
                    <div class="student-card">
                        <?php if($row['gambar']): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($row['gambar']) ?>" class="student-avatar">
                        <?php else: ?>
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['nama']) ?>&background=random&color=fff&size=128" class="student-avatar">
                        <?php endif; ?>

                        <div class="student-info">
                            <div class="student-name"><?= $row['nama'] ?></div>
                            <div class="student-meta">
                                <span class="badge-nis"><?= $row['nis'] ?></span>
                                <span>| <?= $row['kelas'] ?></span>
                            </div>
                            <div style="font-size:12px; color:#888; margin-top:3px;">
                                <i class="fas fa-user-circle"></i> <?= $row['username'] ?>
                            </div>
                        </div>

                        <div class="student-actions">
                            <a href="siswa.php?edit=<?= $row['nis'] ?>" class="action-btn btn-edit"><i class="fas fa-pencil-alt"></i></a>
                            <button class="action-btn btn-del" onclick="confirmHapus('<?= $row['nis'] ?>', '<?= addslashes($row['nama']) ?>')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>

                    <?php 
                        }
                    } else {
                        echo "<div style='text-align:center; padding: 40px; color:#999; background:white; border-radius:12px; box-shadow: 0 5px 15px rgba(0,0,0,0.04);'>Belum ada data siswa.</div>";
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

    <script>
        function confirmHapus(nis, nama) {
            Swal.fire({
                title: 'Hapus Siswa?',
                text: "Hapus data: " + nama + "?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff5b5c',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'siswa.php?hapus=' + nis;
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