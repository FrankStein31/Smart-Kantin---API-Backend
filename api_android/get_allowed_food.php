<?php
// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db = "db_toko";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Explicitly set connection charset and collation
$conn->set_charset("utf8mb4");
$conn->query("SET NAMES utf8mb4 COLLATE utf8mb4_general_ci");

// Set header response JSON
header('Content-Type: application/json');

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Koneksi database gagal: " . $conn->connect_error
    ]);
    exit();
}

// Mengambil parameter nim dari request
$nim = isset($_GET['nim']) ? $_GET['nim'] : '';

if (empty($nim)) {
    echo json_encode([
        "success" => false,
        "message" => "Parameter nim tidak ditemukan"
    ]);
    exit();
}

// Periksa apakah tabel food_restriction sudah ada
$check_table = "SHOW TABLES LIKE 'food_restriction'";
$table_exists = $conn->query($check_table);

if ($table_exists->num_rows == 0) {
    // Buat tabel jika belum ada
    $create_table = "CREATE TABLE food_restriction (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nim VARCHAR(20) NOT NULL,
        id_barang VARCHAR(20) NOT NULL,
        UNIQUE KEY (nim, id_barang)
    )";
    $conn->query($create_table);
}

// Cek apakah ada pembatasan makanan
$check_restriction = "SELECT COUNT(*) as count FROM food_restriction WHERE nim = '$nim'";
$restriction_result = $conn->query($check_restriction);
$restriction_count = $restriction_result->fetch_assoc()['count'];

if ($restriction_count > 0) {
    // Jika ada pembatasan, ambil makanan yang diizinkan
    $query = "SELECT b.*, k.nama_kategori 
              FROM barang b 
              JOIN kategori k ON b.id_kategori = k.id_kategori 
              JOIN food_restriction fr ON b.id_barang = fr.id_barang 
              WHERE fr.nim = '$nim' 
              ORDER BY b.nama_barang ASC";
} else {
    // Jika tidak ada pembatasan, ambil semua makanan
    $query = "SELECT b.*, k.nama_kategori 
              FROM barang b 
              JOIN kategori k ON b.id_kategori = k.id_kategori 
              ORDER BY b.nama_barang ASC";
}

$result = $conn->query($query);

if ($result) {
    $barang = [];
    while ($row = $result->fetch_assoc()) {
        $barang[] = $row;
    }
    
    echo json_encode([
        "success" => true,
        "has_restriction" => $restriction_count > 0,
        "data" => $barang
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengambil data makanan yang diizinkan: " . $conn->error
    ]);
}

$conn->close();
?> 