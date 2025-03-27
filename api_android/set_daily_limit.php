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

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['nim']) || !isset($data['limit_amount'])) {
    echo json_encode([
        "success" => false,
        "message" => "Data tidak lengkap"
    ]);
    exit();
}

$nim = $data['nim'];
$limit_amount = floatval($data['limit_amount']);

$check_table = "SHOW TABLES LIKE 'daily_limit'";
$result = $conn->query($check_table);

if ($result->num_rows == 0) {
    $create_table = "CREATE TABLE daily_limit (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nim VARCHAR(20) NOT NULL,
        limit_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (nim)
    )";
    $conn->query($create_table);
}

$check_query = "SELECT * FROM daily_limit WHERE nim = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("s", $nim);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $query = "UPDATE daily_limit SET limit_amount = ? WHERE nim = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ds", $limit_amount, $nim);
} else {
    $query = "INSERT INTO daily_limit (nim, limit_amount) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sd", $nim, $limit_amount);
}

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Batas pengeluaran harian berhasil diatur"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal mengatur batas pengeluaran harian: " . $conn->error
    ]);
}

$conn->close();
?> 