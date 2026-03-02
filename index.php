<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login | E-Library</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #435ebe;
            --dark: #25396f;
            --grey: #969696;
            --bg: #f4f6f9;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px; /* Jarak aman tepi layar HP */
        }

        .login-box {
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .brand-logo {
            font-size: 45px;
            color: var(--primary);
            margin-bottom: 10px;
        }

        h2 {
            color: var(--dark);
            margin: 0 0 5px 0;
            font-weight: 600;
            font-size: 24px;
        }

        p {
            color: var(--grey);
            font-size: 14px;
            margin: 0 0 30px 0;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            transition: 0.3s;
            font-size: 18px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 15px 14px 50px; /* Padding kiri besar untuk icon */
            border: 1px solid #ddd;
            border-radius: 10px;
            font-family: inherit;
            font-size: 15px;
            outline: none;
            transition: 0.3s;
            background: #fdfdfd;
        }

        .input-group input:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(67, 94, 190, 0.1);
        }

        .input-group input:focus + i {
            color: var(--primary);
        }

        button {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(67, 94, 190, 0.3);
        }

        button:hover {
            background: #354a96;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 94, 190, 0.4);
        }

        .footer-text {
            margin-top: 25px;
            font-size: 12px;
            color: #aaa;
        }

        /* --- RESPONSIVE MOBILE --- */
        @media (max-width: 480px) {
            .login-box {
                padding: 30px 25px; /* Padding lebih kecil di HP */
            }
            .brand-logo { font-size: 40px; }
            h2 { font-size: 22px; }
            p { font-size: 13px; margin-bottom: 25px; }
            
            .input-group input {
                font-size: 16px; /* Mencegah auto-zoom di iPhone */
            }
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="brand-logo">
        <i class="fas fa-book-reader"></i>
    </div>
    <h2>Selamat Datang</h2>
    <p>Silakan login untuk mengakses perpustakaan.</p>

    <form action="cek_login.php" method="POST">
        <div class="input-group">
            <input type="text" name="username" placeholder="Username" required autocomplete="off">
            <i class="fas fa-user"></i>
        </div>
        
        <div class="input-group">
            <input type="password" name="password" placeholder="Password" required>
            <i class="fas fa-lock"></i>
        </div>

        <button type="submit">MASUK SEKARANG</button>
    </form>
    
    <div class="footer-text">
        &copy; <?= date('Y') ?> E-Library System
    </div>
</div>

<?php 
if(isset($_GET['pesan'])){
    if($_GET['pesan'] == "gagal"){
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal!',
                text: 'Username atau Password salah.',
                confirmButtonColor: '#435ebe',
                confirmButtonText: 'Coba Lagi'
            });
        </script>";
    }
    else if($_GET['pesan'] == "logout"){
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Logout',
                text: 'Anda telah keluar dari sistem.',
                confirmButtonColor: '#435ebe',
                timer: 2000,
                showConfirmButton: false
            });
        </script>";
    }
    else if($_GET['pesan'] == "belum_login"){
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Akses Ditolak',
                text: 'Silakan login terlebih dahulu.',
                confirmButtonColor: '#435ebe'
            });
        </script>";
    }
}
?>

</body>
</html>