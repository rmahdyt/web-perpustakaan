<?php
include "../config/session_admin.php";
include "../config/koneksi.php";

// Ambil data admin saat ini
$username = $_SESSION['admin'];
$query = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username'");
$admin = mysqli_fetch_array($query);

// --- LOGIKA UPDATE PROFIL ---
if (isset($_POST['simpan'])) {
    
    // PENTING: Gunakan 'id_admin'
    $id_admin = $admin['id_admin']; 
    
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $user_new = mysqli_real_escape_string($conn, $_POST['username']);
    $pass_new = $_POST['password'];

    // 1. Cek Password
    $pass_sql = "";
    if (!empty($pass_new)) {
        $pass_sql = ", password='$pass_new'";
    }

    // 2. Cek Foto
    $foto_sql = "";
    if (!empty($_FILES['foto']['tmp_name'])) {
        $file = file_get_contents($_FILES['foto']['tmp_name']);
        $file = mysqli_real_escape_string($conn, $file);
        $foto_sql = ", foto='$file'";
    }

    // 3. Eksekusi Query
    $query_update = "UPDATE admin SET nama_admin='$nama', username='$user_new' $pass_sql $foto_sql WHERE id_admin='$id_admin'";
    
    if (mysqli_query($conn, $query_update)) {
        $_SESSION['admin'] = $user_new; 
        $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Berhasil!', 'text' => 'Profil diperbarui.'];
        header("Location: profil.php");
        exit;
    } else {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Gagal!', 'text' => mysqli_error($conn)];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Edit Profil | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* --- COPY STYLE GLOBAL DARI DASHBOARD --- */
        :root {
            --primary: #435ebe;
            --white: #ffffff;
            --light: #f2f7ff;
            --dark: #25396f;
            --sidebar-width: 260px;
            --grey: #969696;
            --bottom-nav-height: 60px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0; background-color: #f4f6f9;
            font-family: 'Poppins', sans-serif; color: #2c3e50;
            display: flex; min-height: 100vh;
        }

        /* --- SIDEBAR (DESKTOP) --- */
        .sidebar {
            width: var(--sidebar-width); background: var(--white);
            display: flex; flex-direction: column; position: fixed;
            top: 0; left: 0; height: 100vh;
            box-shadow: 0 0 15px rgba(0,0,0,0.05); z-index: 1000; transition: 0.3s;
        }
        .brand { padding: 25px; font-size: 20px; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #eee; }
        .menu { padding: 20px; flex: 1; overflow-y: auto; }
        .menu a { display: flex; align-items: center; padding: 12px 15px; margin-bottom: 8px; text-decoration: none; color: #607080; font-weight: 500; border-radius: 10px; transition: 0.3s; font-size: 14px; }
        .menu a:hover { background: var(--light); color: var(--primary); padding-left: 20px; }
        .menu a.active { background: var(--primary); color: white; box-shadow: 0 4px 10px rgba(67, 94, 190, 0.3); }
        .btn-logout { margin: 20px; padding: 12px; text-align: center; background: #fff5f5; color: #ff5b5c; border-radius: 10px; font-weight: 600; text-decoration: none; font-size: 14px; }

        /* --- BOTTOM NAV (MOBILE) --- */
        .bottom-nav {
            display: none; position: fixed; bottom: 0; left: 0; width: 100%;
            height: var(--bottom-nav-height); background: white;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05); z-index: 1000;
            justify-content: space-around; align-items: center;
        }
        .nav-item {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-decoration: none; color: #999; font-size: 10px; width: 20%; height: 100%;
        }
        .nav-item i { font-size: 18px; margin-bottom: 4px; }
        .nav-item.active { color: var(--primary); font-weight: 600; }

        /* --- MAIN CONTENT --- */
        .main { margin-left: var(--sidebar-width); padding: 30px; flex: 1; width: 100%; transition: 0.3s; }

        /* --- PROFILE CARD DESIGN --- */
        .profile-container {
            background: white; border-radius: 15px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            display: flex; overflow: hidden; max-width: 900px; margin: 0 auto;
        }

        /* Bagian Kiri (Foto) */
        .left-box {
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
            width: 35%; padding: 40px; text-align: center; color: white;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .big-profile-pic {
            width: 140px; height: 140px; border-radius: 50%; object-fit: cover;
            border: 5px solid rgba(255,255,255,0.3); margin-bottom: 15px; background: white;
        }
        .role-badge { background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px; font-size: 12px; margin-top: 10px; }

        /* Bagian Kanan (Form) */
        .right-box { width: 65%; padding: 40px; }
        
        h2 { margin: 0 0 25px 0; color: var(--dark); border-bottom: 2px solid #eee; padding-bottom: 15px; font-size: 20px; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #607080; font-size: 13px; }
        
        input[type="text"], input[type="password"], input[type="file"] {
            width: 100%; padding: 12px; border: 1px solid #dfe3e7; border-radius: 8px;
            font-family: inherit; font-size: 14px; box-sizing: border-box; transition: 0.3s; background: #f9f9f9;
        }
        input:focus { border-color: var(--primary); background: white; outline: none; }
        
        .btn-save {
            background: var(--primary); color: white; border: none; padding: 14px;
            border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s;
            width: 100%; margin-top: 10px; font-size: 14px;
        }
        .btn-save:hover { background: #354a96; transform: translateY(-2px); }
        .note { font-size: 11px; color: #999; margin-top: 5px; font-style: italic; }

        /* --- MOBILE RESPONSIVE --- */
        @media (max-width: 768px) {
            /* Sidebar Hide & Bottom Nav Show */
            .sidebar { display: none; }
            .bottom-nav { display: flex; }
            
            /* Main Content Adjust */
            .main { margin-left: 0; padding: 20px 15px 80px 15px; /* Jarak bawah 80px */ }
            
            /* Profile Card Stack */
            .profile-container { flex-direction: column; }
            .left-box, .right-box { width: 100%; padding: 30px 20px; }
            .left-box { padding-bottom: 40px; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand"><i class="fas fa-layer-group"></i> PANEL ADMIN</div>
        <div class="menu">
            <a href="dashboard.php"><i class="fas fa-th-large" style="width:25px"></i> Dashboard</a>
            <a href="buku.php"><i class="fas fa-book" style="width:25px"></i> Data Buku</a>
            <a href="siswa.php"><i class="fas fa-users" style="width:25px"></i> Data Siswa</a>
            <a href="transaksi.php"><i class="fas fa-exchange-alt" style="width:25px"></i> Transaksi</a>
        </div>
        <a href="../index.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="bottom-nav">
        <a href="dashboard.php" class="nav-item">
            <i class="fas fa-th-large"></i><span>Home</span>
        </a>
        <a href="buku.php" class="nav-item">
            <i class="fas fa-book"></i><span>Buku</span>
        </a>
        <a href="transaksi.php" class="nav-item">
            <i class="fas fa-exchange-alt"></i><span>Trans</span>
        </a>
        <a href="siswa.php" class="nav-item">
            <i class="fas fa-users"></i><span>Siswa</span>
        </a>
        <a href="../index.php" class="nav-item" style="color:var(--danger)">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
    </div>

    <div class="main">
        
        <div style="margin-bottom: 20px;">
            <a href="dashboard.php" style="color:var(--grey); text-decoration:none; font-size:14px; font-weight:500;">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <div class="profile-container">
            <div class="left-box">
                <?php if($admin['foto']): ?>
                    <img src="data:image/jpeg;base64,<?= base64_encode($admin['foto']) ?>" class="big-profile-pic">
                <?php else: ?>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin['nama_admin']) ?>&background=random&color=fff&size=200" class="big-profile-pic">
                <?php endif; ?>
                
                <h3 style="margin:0; font-size: 18px;"><?= htmlspecialchars($admin['nama_admin']) ?></h3>
                <div class="role-badge">Administrator</div>
            </div>

            <div class="right-box">
                <h2><i class="fas fa-user-edit"></i> Edit Biodata</h2>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($admin['nama_admin']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="password" placeholder="***">
                        <div class="note">*Kosongkan jika tidak ingin mengubah password lama.</div>
                    </div>

                    <div class="form-group">
                        <label>Ganti Foto Profil</label>
                        <input type="file" name="foto" accept="image/*">
                        <div class="note">*Format: JPG, PNG, JPEG.</div>
                    </div>

                    <button type="submit" name="simpan" class="btn-save">
                        <i class="fas fa-save"></i> SIMPAN PERUBAHAN
                    </button>
                </form>
            </div>
        </div>

    </div>

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