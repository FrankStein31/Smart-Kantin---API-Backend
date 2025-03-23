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

// Query untuk mengambil semua data barang dengan kategori
$query = "SELECT b.*, k.nama_kategori 
          FROM barang b 
          JOIN kategori k ON b.id_kategori = k.id_kategori 
          ORDER BY b.nama_barang ASC";
          
$result = $conn->query($query);

if ($result) {
    $barang = [];
    while ($row = $result->fetch_assoc()) {
        $barang[] = $row;
    }
    
    echo json_encode([
        "success" => true,
        "data" => $barang
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengambil data barang: " . $conn->error
    ]);
}

$conn->close();
?> 