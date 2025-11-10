<?php
/*
 * File: php/db_connect.php
 * Deskripsi: Membuat koneksi ke database MySQL (db_toefl).
 * File ini akan di-include oleh file PHP lain yang butuh akses DB.
 */

$servername = "localhost";
$username = "root";       // Username default XAMPP
$password = "";           // Password default XAMPP (kosong)
$dbname = "db_toefl";

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    // Hentikan eksekusi dan tampilkan error jika koneksi gagal
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

// Set karakter encoding ke UTF-8 (rekomendasi)
$conn->set_charset("utf8mb4");

?>