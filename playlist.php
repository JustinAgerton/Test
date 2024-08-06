<?php
// Konfigurasi
$file = 'https://wakhaji.my.id/ams/pl.m3u'; // Path ke file utama yang ingin diakses
$expired_file = 'https://wakhaji.my.id/ams/ex.m3u'; // Path ke file yang diakses jika waktu sudah kedaluwarsa
$allowed_user_agent = 'samarinda'; // User-Agent yang diizinkan
$expiration_time = 3600; // Waktu kedaluwarsa dalam detik (misalnya 3600 detik = 1 jam)

// File untuk menyimpan daftar pengguna dan waktu akses
$users_file = 'users_ams.json';

// Mendapatkan User-Agent dari request
$user_agent = $_SERVER['HTTP_USER_AGENT'];

// Mengecek apakah User-Agent sesuai
if (strpos($user_agent, $allowed_user_agent) === false) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Access denied.';
    exit;
}

// Mendapatkan nama pengguna dari query string
$username = isset($_GET['username']) ? $_GET['username'] : '';

// Membaca daftar pengguna dan waktu akses dari file JSON
if (file_exists($users_file)) {
    $users_data = json_decode(file_get_contents($users_file), true);
} else {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'Users file not found.';
    exit;
}

// Mengecek apakah nama pengguna ada dalam data
if (!array_key_exists($username, $users_data)) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Invalid user.';
    exit;
}

// Mengecek dan memperbarui waktu akses
$current_time = time();
$last_access = $users_data[$username];

if (($current_time - $last_access) > $expiration_time) {
    // Jika waktu akses sudah kedaluwarsa, alihkan ke file lain
    header('HTTP/1.0 410 Gone');
    if (file_exists($expired_file) && is_readable($expired_file)) {
        header('Content-Type: ' . mime_content_type($expired_file));
        header('Content-Length: ' . filesize($expired_file));
        readfile($expired_file);
    } else {
        echo 'Expired file not found.';
    }
    exit;
}

// Memperbarui waktu akses pengguna
$users_data[$username] = $current_time;
file_put_contents($users_file, json_encode($users_data));

// Mengecek apakah file utama ada dan bisa dibaca
if (file_exists($file) && is_readable($file)) {
    header('Content-Type: ' . mime_content_type($file));
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
} else {
    header('HTTP/1.0 404 Not Found');
    echo 'File not found.';
}
?>