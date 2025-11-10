<?php
    require_once 'php/functions.php'; // Selalu panggil ini dulu

    // 1. Proteksi Halaman: Wajib login
    if (!isLoggedIn()) {
        redirect("index.php?error=Anda harus login untuk memulai tes");
    }
    
    // 2. AMBIL SEMUA SOAL DARI DATABASE
    // Kita akan ambil semua soal dan mengelompokkannya
    // agar bisa diserahkan ke JavaScript
    
    $questions_data = [
        'listening' => [],
        'structure' => [],
        'reading'   => []
    ];
    
    $passage_cache = []; // Cache untuk teks bacaan
    $audio_cache = [];   // Cache untuk file audio
    
    // Query untuk mengambil semua soal, diurutkan berdasarkan section
    $sql = "SELECT 
                q.question_id, q.section, q.question_text, 
                q.option_a, q.option_b, q.option_c, q.option_d, 
                q.passage_id, q.audio_id,
                p.passage_text,
                a.file_path AS audio_file
            FROM questions q
            LEFT JOIN passages p ON q.passage_id = p.passage_id
            LEFT JOIN audio_files a ON q.audio_id = a.audio_id
            ORDER BY 
                FIELD(q.section, 'listening', 'structure', 'reading'), 
                q.question_id";
            
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            
            // Format data soal agar bersih
            $question = [
                'id' => $row['question_id'],
                'section' => $row['section'],
                'text' => $row['question_text'],
                'options' => [
                    'a' => $row['option_a'],
                    'b' => $row['option_b'],
                    'c' => $row['option_c'],
                    'd' => $row['option_d'],
                ],
                'passage' => null, // Default
                'audio' => null    // Default
            ];
            
            // Jika ada bacaan, tambahkan
            if ($row['passage_id'] && !empty($row['passage_text'])) {
                 $question['passage'] = $row['passage_text'];
            }
            
            // Jika ada audio, tambahkan
            if ($row['audio_id'] && !empty($row['audio_file'])) {
                 $question['audio'] = $row['audio_file'];
            }

            // Masukkan ke grup section yang sesuai
            $questions_data[$row['section']][] = $question;
        }
    }
    
    $conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mulai Tes TOEFL</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header class="test-header">
        <div class="test-container">
            <h1 id="section-title">Loading...</h1>
            <div class="timer-container">
                <span id="timer-icon">⏱️</span>
                <span id="timer-display">02:00:00</span>
            </div>
        </div>
    </header>

    <div class="test-container test-body">
        
        <form id="test-form" action="php/submit_answer.php" method="POST">

            <main class="test-main">
                
                <div id="media-container">
                    </div>

                <div id="question-container">
                    <p id="question-text">Soal akan dimuat...</p>
                </div>
                
                <div id="options-container">
                    </div>

                <div class="test-navigation">
                    <button type="button" id="prev-btn" class="btn btn-secondary">Previous</button>
                    <button type="button" id="next-btn" class="btn btn-secondary">Next</button>
                    <button type="submit" id="submit-btn" class="btn btn-danger">Selesai & Kumpulkan</button>
                </div>

            </main>

            <aside class="test-sidebar">
                <h2>Navigasi Soal</h2>
                <div id="question-list">
                    </div>
            </aside>

        </form>
    </div>

    <script>
        const allQuestionsData = <?php echo json_encode($questions_data); ?>;
        const TOTAL_TIME_SECONDS = 120 * 60; // 120 Menit = 2 Jam
    </script>
    
    <script src="js/test.js"></script>

</body>
</html>