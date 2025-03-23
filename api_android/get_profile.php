<?php
// Konfigurasi database
$host = "localhost";
$user = "root";
$pass = "";
$db = "db_toko";

// Membuat koneksi
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set header response JSON
header('Content-Type: application/json');

// Terima data dari Android
$nim = $_POST['nim'];

// Validasi input
if (empty($nim)) {
    echo json_encode([
        "success" => false,
        "message" => "NIM harus diisi"
    ]);
    exit();
}

// Query untuk mencari data user dan saldo
$query = "SELECT e.nama, e.nim, e.saldo 
          FROM emoney e 
          WHERE e.nim = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Data ditemukan
    echo json_encode([
        "success" => true,
        "message" => "Data profile ditemukan",
        "data" => [
            "nama" => $user['nama'],
            "nim" => $user['nim'],
            "saldo" => floatval($user['saldo'])
        ]
    ]);
} else {
    // Data tidak ditemukan
    echo json_encode([
        "success" => false,
        "message" => "Data profile tidak ditemukan"
    ]);
}

// Tutup koneksi
$stmt->close();
$conn->close();
?>