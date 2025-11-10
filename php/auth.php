<?php
/*
 * File: php/auth.php
 * Deskripsi: Menangani logika untuk REGISTER dan LOGIN.
 * File ini dipanggil oleh <form action="php/auth.php">
 */

// Selalu includekan functions.php di awal
require_once 'functions.php'; 
// $conn (koneksi DB) sudah otomatis di-include oleh functions.php

// Cek data 'action' dari form (bisa 'register' atau 'login')
if (isset($_POST['action'])) {

    // --- PROSES REGISTER ---
    if ($_POST['action'] == 'register') {
        
        // 1. Ambil data dari form
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // 2. Validasi Sederhana
        if (empty($username) || empty($email) || empty($password)) {
            redirect("../register.php?error=Data tidak boleh kosong");
        }
        if ($password != $confirm_password) {
            redirect("../register.php?error=Password tidak cocok");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            redirect("../register.php?error=Email tidak valid");
        }

        // 3. Cek apakah username atau email sudah ada
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User sudah ada
            redirect("../register.php?error=Username atau Email sudah terdaftar");
        } else {
            // 4. User belum ada, lanjutkan pendaftaran
            // Hash password (SANGAT PENTING!)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // 5. Insert ke database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password_hash);
            
            if ($stmt->execute()) {
                // Registrasi berhasil
                redirect("../index.php?success=Registrasi berhasil. Silakan login.");
            } else {
                // Gagal insert
                redirect("../register.php?error=Registrasi gagal. Coba lagi.");
            }
        }
        $stmt->close();
    } 
    
    // --- PROSES LOGIN ---
    elseif ($_POST['action'] == 'login') {
        
        // 1. Ambil data
        $username_or_email = $_POST['username']; // User bisa input username atau email
        $password = $_POST['password'];

        // 2. Validasi
        if (empty($username_or_email) || empty($password)) {
            redirect("../index.php?error=Username atau Password tidak boleh kosong");
        }

        // 3. Cari user di database
        $stmt = $conn->prepare("SELECT user_id, username, password_hash FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username_or_email, $username_or_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            // User ditemukan
            $user = $result->fetch_assoc();
            
            // 4. Verifikasi password
            if (password_verify($password, $user['password_hash'])) {
                // Password cocok!
                // 5. Buat Session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                
                // Redirect ke dashboard
                redirect("../dashboard.php");
                
            } else {
                // Password salah
                redirect("../index.php?error=Password salah");
            }
        } else {
            // User tidak ditemukan
            redirect("../index.php?error=Username atau Email tidak ditemukan");
        }
        $stmt->close();
    }

} else {
    // Jika file diakses langsung tanpa 'action'
    redirect("../index.php");
}

$conn->close();
?>