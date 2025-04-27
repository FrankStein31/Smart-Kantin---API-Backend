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

$query = "SELECT b.*, k.nama_kategori 
          FROM barang b 
          JOIN kategori k ON b.id_kategori = k.id_kategori 
          ORDER BY b.nama_barang ASC";
          
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