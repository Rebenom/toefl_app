<?php
/*
 * File: php/functions.php
 * Deskripsi: Berisi fungsi-fungsi bantuan (helper)
 * seperti manajemen sesi dan konversi skor.
 */

// Mulai session di sini. 
// File ini akan di-include di awal, jadi sesi akan selalu aktif.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include koneksi database agar tersedia untuk fungsi yang mungkin membutuhkannya
require_once 'db_connect.php';

/**
 * Mengecek apakah pengguna sudah login atau belum.
 * @return bool True jika sudah login, False jika belum.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Fungsi untuk mengalihkan (redirect) pengguna ke halaman lain.
 * @param string $url URL tujuan (misal: "index.php" atau "../dashboard.php")
 */
function redirect($url) {
    header("Location: " . $url);
    exit(); // Selalu panggil exit() setelah redirect
}

/**
 * Konversi skor mentah (jumlah benar) ke skor PBT section.
 * Ini adalah DUMMY/CONTOH konversi linier sederhana.
 * Range skor PBT per section adalah 31-68 (Listening), 31-68 (Structure), 31-67 (Reading).
 * * @param int $rawScore Jumlah jawaban benar
 * @param string $section Tipe section ('listening', 'structure', 'reading')
 * @return int Skor yang telah dikonversi
 */
function convertRawScore($rawScore, $section) {
    // Aturan konversi ini SANGAT DISIMPLIFIKASI untuk contoh
    // Anda harus mencari tabel konversi TOEFL PBT yang asli untuk akurasi
    
    if ($section == 'listening') {
        // Misal 50 soal. Range 31-68
        $score = 31 + round(($rawScore / 50) * 37);
    } elseif ($section == 'structure') {
        // Misal 40 soal. Range 31-68
        $score = 31 + round(($rawScore / 40) * 37);
    } elseif ($section == 'reading') {
        // Misal 50 soal. Range 31-67
        $score = 31 + round(($rawScore / 50) * 36);
    } else {
        $score = 0; // Default
    }
    
    return $score;
}

/**
 * Menghitung total skor PBT dari 3 skor section.
 * Rumus: ((Score1 + Score2 + Score3) * 10) / 3
 * * @param int $score_listening Skor konversi listening
 * @param int $score_structure Skor konversi structure
 * @param int $score_reading Skor konversi reading
 * @return int Skor total PBT (range 310 - 677)
 */
function calculateTotalTOEFLScore($score_listening, $score_structure, $score_reading) {
    $total = (($score_listening + $score_structure + $score_reading) * 10) / 3;
    return floor($total); // Dibulatkan ke bawah
}

?>