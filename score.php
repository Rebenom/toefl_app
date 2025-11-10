<?php
    require_once 'php/functions.php'; // Selalu panggil ini dulu

    // 1. KEAMANAN: Cek apakah user sudah login
    if (!isLoggedIn()) {
        redirect("index.php?error=Anda harus login untuk melihat skor");
    }

    // 2. AMBIL DATA DARI URL:
    // Pastikan result_id ada dan merupakan angka
    if (!isset($_GET['result_id']) || !is_numeric($_GET['result_id'])) {
        redirect("dashboard.php?error=Hasil tes tidak valid");
    }

    $result_id = $_GET['result_id'];
    $user_id = $_SESSION['user_id']; // Ambil ID user dari sesi

    // 3. AMBIL DATA SKOR DARI DATABASE
    // Query ini PENTING:
    // Kita cek 'result_id' DAN 'user_id' untuk memastikan
    // user A tidak bisa melihat skor user B dengan menebak URL.
    $stmt = $conn->prepare("SELECT * FROM test_results WHERE result_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $result_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Hasil tes ditemukan dan valid
        $test_result = $result->fetch_assoc();

        // 4. KONVERSI SKOR
        // Ambil skor mentah (jumlah benar) dari database
        $raw_listen = $test_result['score_listening'];
        $raw_struct = $test_result['score_structure'];
        $raw_read = $test_result['score_reading'];

        // Gunakan fungsi dari functions.php untuk mengkonversi
        $conv_listen = convertRawScore($raw_listen, 'listening');
        $conv_struct = convertRawScore($raw_struct, 'structure');
        $conv_read = convertRawScore($raw_read, 'reading');
        
        // Hitung total skor PBT
        $total_score = calculateTotalTOEFLScore($conv_listen, $conv_struct, $conv_read);

    } else {
        // Hasil tes tidak ditemukan atau bukan milik user ini
        redirect("dashboard.php?error=Hasil tes tidak ditemukan");
    }

    $stmt->close();
    $conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Skor TOEFL Anda</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="score-container">
        <h1><span class="icon">🏆</span> Hasil Tes Anda</h1>
        
        <p class="congrats-msg">Selamat, Anda telah menyelesaikan tes!</p>

        <div class="total-score-box">
            <p>Total Skor PBT Anda</p>
            <div class="total-score-number">
                <?php echo $total_score; ?>
            </div>
        </div>

        <h2>Rincian Skor</h2>
        <div class="score-breakdown">
            
            <div class="score-item">
                <h3>Listening</h3>
                <p><?php echo $conv_listen; ?></p>
                <span>(<?php echo $raw_listen; ?> benar)</span>
            </div>
            
            <div class="score-item">
                <h3>Structure</h3>
                <p><?php echo $conv_struct; ?></p>
                <span>(<?php echo $raw_struct; ?> benar)</span>
            </div>
            
            <div class="score-item">
                <h3>Reading</h3>
                <p><?php echo $conv_read; ?></p>
                <span>(<?php echo $raw_read; ?> benar)</span>
            </div>

        </div>

        <div class="score-footer">
            <a href="dashboard.php" class="btn btn-primary">Kembali ke Dashboard</a>
            <a href="test.php" class="btn btn-secondary">Ulangi Tes</a>
        </div>
    </div>

</body>
</html>