<?php
    // Memulai session dan memanggil functions.php
    require_once 'php/functions.php';

    // Jika user sudah login, lempar ke dashboard.
    if (isLoggedIn()) {
        redirect('dashboard.php');
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Aplikasi TOEFL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="form-container">
        <h1>Buat Akun Baru</h1>
        <p>Isi data di bawah ini untuk mendaftar.</p>

        <?php
            // Tampilkan pesan error dari URL (jika ada)
            if (isset($_GET['error'])) {
                echo '<div class="msg error-msg">' . htmlspecialchars($_GET['error']) . '</div>';
            }
        ?>

        <form id="register-form" action="php/auth.php" method="POST">
            <input type="hidden" name="action" value="register">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Daftar</button>
            </div>
        </form>

        <div class="message-container">
            <p>Sudah punya akun? <a href="index.php">Login di sini</a></p>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>