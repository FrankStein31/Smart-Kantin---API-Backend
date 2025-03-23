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

// Get NIM from GET parameter
$nim = $_GET['nim'] ?? '';

// Validate input
if (empty($nim)) {
    echo json_encode([
        "success" => false,
        "message" => "NIM tidak valid"
    ]);
    exit();
}

try {
    // Prepare query to fetch history with product information
    $query = "SELECT h.id_h, h.nim, h.totalharga, h.date, h.time, 
               b.nama_barang, k.nama_kategori 
              FROM history h
              LEFT JOIN barang b ON h.id_barang = b.id_barang
              LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
              WHERE h.nim = ? 
              ORDER BY h.date DESC, h.time DESC 
              LIMIT 50";  // Limit to prevent overwhelming data
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();

    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $transactions
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan: " . $e->getMessage()
    ]);
}

$conn->close();
?>