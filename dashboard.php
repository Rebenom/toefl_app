<?php
    // Selalu panggil functions.php di setiap halaman yang butuh sesi/login
    require_once 'php/functions.php';

    // 1. KEAMANAN: Cek apakah user sudah login
    if (!isLoggedIn()) {
        redirect("index.php?error=Anda harus login untuk mengakses halaman ini");
    }

    // 2. AMBIL DATA USER: Dapatkan info dari Sesi
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    // 3. AMBIL DATA RIWAYAT: Query database untuk riwayat tes user ini
    // Urutkan berdasarkan tanggal terbaru (DESC)
    $stmt = $conn->prepare("SELECT * FROM test_results WHERE user_id = ? ORDER BY test_date DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Simpan semua riwayat ke dalam array
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }

    $stmt->close();
    $conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>

    <div class="dashboard-container">
        
        <header class="dashboard-header">
            <h1>Dashboard</h1>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </header>

        <h2>Selamat Datang, <?php echo htmlspecialchars($username); ?>!</h2>

        <div class="card cta-card">
            <h3>Mulai Tes Baru</h3>
            <p>Siap untuk menguji kemampuan TOEFL PBT Anda? Klik tombol di bawah untuk memulai.</p>
            <a href="test.php" class="btn btn-primary">Mulai Tes</a>
        </div>

        <div class="card history-card">
            <h2>Riwayat Tes Anda</h2>
            
            <?php if (empty($history)): ?>
                <p class="no-history">Anda belum memiliki riwayat tes. Klik "Mulai Tes" untuk mengambil tes pertama Anda!</p>
            
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal Tes</th>
                                <th>Listening</th>
                                <th>Structure</th>
                                <th>Reading</th>
                                <th>Skor Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $test): ?>
                                <?php
                                    // PENTING:
                                    // Kita panggil fungsi konversi dari functions.php
                                    // untuk mengubah skor mentah (jumlah benar) menjadi skor TOEFL
                                    
                                    $conv_listen = convertRawScore($test['score_listening'], 'listening');
                                    $conv_struct = convertRawScore($test['score_structure'], 'structure');
                                    $conv_read = convertRawScore($test['score_reading'], 'reading');
                                    
                                    // Hitung total skor TOEFL
                                    $total_toefl_score = calculateTotalTOEFLScore($conv_listen, $conv_struct, $conv_read);
                                ?>
                                <tr>
                                    <td><?php echo date('d M Y, H:i', strtotime($test['test_date'])); ?></td>
                                    <td><?php echo $conv_listen; ?></td>
                                    <td><?php echo $conv_struct; ?></td>
                                    <td><?php echo $conv_read; ?></td>
                                    <td><strong><?php echo $total_toefl_score; ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
    
    </body>
</html>