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

// Get NIM from parameter
$nim = isset($_GET['nim']) ? $_GET['nim'] : '';

if (empty($nim)) {
    echo json_encode([
        "success" => false,
        "message" => "Parameter nim tidak ditemukan"
    ]);
    exit();
}

// Cek apakah tabel daily_limit sudah ada
$check_table = "SHOW TABLES LIKE 'daily_limit'";
$result = $conn->query($check_table);

if ($result->num_rows == 0) {
    echo json_encode([
        "success" => false,
        "message" => "Belum ada batas pengeluaran harian"
    ]);
    exit();
}

// Query untuk mencari data batas pengeluaran
$query = "SELECT limit_amount FROM daily_limit WHERE nim = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Dapatkan total pengeluaran hari ini
    $today = date('Y-m-d');
    $query_spend = "SELECT SUM(totalharga) as total FROM history WHERE nim = ? AND date = ?";
    $stmt_spend = $conn->prepare($query_spend);
    $stmt_spend->bind_param("ss", $nim, $today);
    $stmt_spend->execute();
    $result_spend = $stmt_spend->get_result();
    $spend_data = $result_spend->fetch_assoc();
    $total_spent = floatval($spend_data['total'] ?? 0);
    
    echo json_encode([
        "success" => true,
        "data" => [
            "limit_amount" => floatval($row['limit_amount']),
            "spent_today" => $total_spent,
            "remaining" => floatval($row['limit_amount']) - $total_spent
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Batas pengeluaran belum diatur"
    ]);
}

$conn->close();
?>