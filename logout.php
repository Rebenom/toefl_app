<?php
/*
 * File: logout.php
 * Deskripsi: Menghancurkan sesi (logout) dan mengarahkan ke halaman login.
 */

// Include functions.php untuk memulai sesi yang ada
require_once 'php/functions.php';

// 1. Hapus semua variabel sesi
$_SESSION = array();

// 2. Hancurkan sesi
session_destroy();

// 3. Redirect ke halaman login (index.php)
redirect("index.php");

?>