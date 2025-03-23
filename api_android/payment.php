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

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "Koneksi database gagal: " . $conn->connect_error
    ]);
    exit();
}

// Set header response JSON
header('Content-Type: application/json');

// Terima data dari Android
$nim = $_POST['nim'] ?? '';
$password = $_POST['password'] ?? '';
$amount = $_POST['amount'] ?? '';

// Validasi input
if (empty($nim) || empty($password) || empty($amount)) {
    echo json_encode([
        "success" => false,
        "message" => "Data tidak lengkap"
    ]);
    exit();
}

try {
    // Cek kredensial login
    $loginQuery = "SELECT l.password, e.nama, e.saldo 
                FROM login_mhs l
                JOIN emoney e ON BINARY l.nim = BINARY e.nim 
                WHERE l.nim = ?";
    $loginStmt = $conn->prepare($loginQuery);
    
    if (!$loginStmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $loginStmt->bind_param("s", $nim);
    $loginStmt->execute();
    $loginResult = $loginStmt->get_result();

    if ($loginResult->num_rows == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Akun tidak ditemukan"
        ]);
        exit();
    }

    $loginData = $loginResult->fetch_assoc();

    // Verifikasi password
    if (!password_verify($password, $loginData['password'])) {
        echo json_encode([
            "success" => false,
            "message" => "Password salah"
        ]);
        exit();
    }

    // Cek saldo
    $amountNumeric = floatval(str_replace(['Rp', ' ', ','], '', $amount));
    if ($amountNumeric > $loginData['saldo']) {
        echo json_encode([
            "success" => false,
            "message" => "Saldo tidak mencukupi"
        ]);
        exit();
    }

    // Mulai transaksi
    $conn->begin_transaction();

    // Proses pembayaran (kurangi saldo)
    $updateQuery = "UPDATE emoney SET saldo = saldo - ? WHERE nim = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ds", $amountNumeric, $nim);
    $updateStmt->execute();

    // Insert ke tabel history
    $insertHistoryQuery = "INSERT INTO history (nim, totalharga, date, time) VALUES (?, ?, CURRENT_DATE, CURRENT_TIME)";
    $insertHistoryStmt = $conn->prepare($insertHistoryQuery);
    $insertHistoryStmt->bind_param("ss", $nim, $amount);
    $insertHistoryStmt->execute();

    // Commit transaksi
    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Pembayaran berhasil",
        "data" => [
            "nim" => $nim,
            "saldo_baru" => $loginData['saldo'] - $amountNumeric
        ]
    ]);

} catch (Exception $e) {
    // Rollback transaksi jika terjadi kesalahan
    $conn->rollback();

    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan: " . $e->getMessage()
    ]);
}

$conn->close();
?>