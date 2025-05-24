<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "db_toko";

$conn = new mysqli($host, $user, $pass, $db);

$conn->set_charset("utf8mb4");
$conn->query("SET NAMES utf8mb4 COLLATE utf8mb4_general_ci");

header('Content-Type: application/json');

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Koneksi database gagal: " . $conn->connect_error
    ]);
    exit();
}

$nim = isset($_GET['nim']) ? $_GET['nim'] : '';

if (empty($nim)) {
    echo json_encode([
        "success" => false,
        "message" => "Parameter nim tidak ditemukan"
    ]);
    exit();
}

$check_table = "SHOW TABLES LIKE 'food_restriction'";
$table_exists = $conn->query($check_table);

if ($table_exists->num_rows == 0) {
    $create_table = "CREATE TABLE food_restriction (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nim VARCHAR(20) NOT NULL,
        id_barang VARCHAR(20) NOT NULL,
        UNIQUE KEY (nim, id_barang)
    )";
    $conn->query($create_table);
}

$check_restriction = "SELECT COUNT(*) as count FROM food_restriction WHERE nim = '$nim'";
$restriction_result = $conn->query($check_restriction);
$restriction_count = $restriction_result->fetch_assoc()['count'];

if ($restriction_count > 0) {
    $query = "SELECT b.*, k.nama_kategori 
              FROM barang b 
              JOIN kategori k ON b.id_kategori = k.id_kategori 
              WHERE NOT EXISTS (
                  SELECT 1 FROM food_restriction fr 
                  WHERE fr.id_barang = b.id_barang 
                  AND fr.nim = '$nim'
              )
              ORDER BY b.nama_barang ASC";
} else {
    $query = "SELECT b.*, k.nama_kategori 
              FROM barang b 
              JOIN kategori k ON b.id_kategori = k.id_kategori 
              ORDER BY b.nama_barang ASC";
}

$result = $conn->query($query);

if ($result) {
    $barang = [];
    while ($row = $result->fetch_assoc()) {
        // Tambahkan URL foto
        if(!empty($row['foto'])) {
            // Base URL untuk foto produk
            $baseUrl = "http://" . $_SERVER['HTTP_HOST'] . "/Smart_Kantin/assets/img/barang/";
            $row['foto_url'] = $baseUrl . $row['foto'];
        } else {
            $row['foto_url'] = null;
        }
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