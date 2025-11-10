<?php
    // Memulai session dan memanggil functions.php
    require_once 'php/functions.php';

    // Jika user sudah login, JANGAN tampilkan halaman login.
    // Langsung lempar ke dashboard.
    if (isLoggedIn()) {
        redirect('dashboard.php');
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi TOEFL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="form-container">
        <h1>Login</h1>
        <p>Selamat datang kembali! Silakan login.</p>

        <?php
            // Tampilkan pesan error dari URL (jika ada)
            if (isset($_GET['error'])) {
                echo '<div class="msg error-msg">' . htmlspecialchars($_GET['error']) . '</div>';
            }
            // Tampilkan pesan sukses dari URL (jika ada, misal: setelah register)
            if (isset($_GET['success'])) {
                echo '<div class="msg success-msg">' . htmlspecialchars($_GET['success']) . '</div>';
            }
        ?>

        <form id="login-form" action="php/auth.php" method="POST">
            <input type="hidden" name="action" value="login">

            <div class="form-group">
                <label for="username">Username atau Email</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Login</button>
            </div>
        </form>

        <div class="message-container">
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>