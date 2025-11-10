<?php
/*
 * File: php/submit_answer.php
 * Deskripsi: Menerima jawaban dari form tes, menghitung skor,
 * menyimpannya ke DB, dan mengarahkan ke halaman skor.
 */
 
require_once 'functions.php'; // Memulai sesi dan koneksi DB

// 1. Cek apakah user login
if (!isLoggedIn()) {
    die("Akses ditolak. Anda harus login.");
}

// 2. Ambil ID user dari sesi
$user_id = $_SESSION['user_id'];

// 3. Ambil jawaban user dari form
// Asumsi: form Anda mengirim jawaban dalam format array: name="answers[question_id]"
if (!isset($_POST['answers']) || empty($_POST['answers'])) {
    redirect("../test.php?error=Tidak ada jawaban yang dikirim.");
}

$user_answers = $_POST['answers']; // Ini adalah array [question_id => user_choice]

// --- Proses Perhitungan Skor ---

// 4. Ambil KUNCI JAWABAN dari database
// Ini lebih aman daripada mengandalkan data tersembunyi di form

// Buat daftar placeholder (?) untuk query IN()
$question_ids = array_keys($user_answers);
$placeholders = implode(',', array_fill(0, count($question_ids), '?'));
$types = str_repeat('i', count($question_ids)); // 'i' untuk integer

$sql = "SELECT question_id, correct_answer, section FROM questions WHERE question_id IN ($placeholders)";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$question_ids);
$stmt->execute();
$result = $stmt->get_result();

$correct_answers = []; // [question_id => [answer, section]]
while ($row = $result->fetch_assoc()) {
    $correct_answers[$row['question_id']] = [
        'answer' => $row['correct_answer'],
        'section' => $row['section']
    ];
}

// 5. Bandingkan jawaban user dengan kunci jawaban
$raw_score = [
    'listening' => 0,
    'structure' => 0,
    'reading' => 0
];
$total_questions = [
    'listening' => 0, // Akan kita hitung untuk konversi
    'structure' => 0,
    'reading' => 0
];

foreach ($user_answers as $question_id => $user_choice) {
    // Pastikan soal itu ada di kunci jawaban
    if (isset($correct_answers[$question_id])) {
        $correct_data = $correct_answers[$question_id];
        $section = $correct_data['section'];
        
        $total_questions[$section]++; // Hitung total soal per section
        
        // Cek jika jawaban benar
        if ($user_choice === $correct_data['answer']) {
            $raw_score[$section]++; // Tambah skor mentah
        }
    }
}

// 6. Simpan hasil (SKOR MENTAH) ke database
$stmt = $conn->prepare("INSERT INTO test_results (user_id, score_listening, score_structure, score_reading, total_score) VALUES (?, ?, ?, ?, ?)");

$total_correct = $raw_score['listening'] + $raw_score['structure'] + $raw_score['reading'];

$stmt->bind_param("iiiii", 
    $user_id, 
    $raw_score['listening'],
    $raw_score['structure'],
    $raw_score['reading'],
    $total_correct
);

if ($stmt->execute()) {
    // Berhasil disimpan
    // Ambil ID dari hasil tes yang baru saja dimasukkan
    $new_result_id = $conn->insert_id;
    
    // 7. Redirect ke halaman skor
    redirect("../score.php?result_id=" . $new_result_id);
    
} else {
    // Gagal menyimpan
    redirect("../test.php?error=Gagal menyimpan skor Anda. Coba lagi.");
}

$stmt->close();
$conn->close();

?>