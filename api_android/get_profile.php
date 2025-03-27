<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$logFile = __DIR__ . '/error_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - API get_profile.php dipanggil\n", FILE_APPEND);

try {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "db_toko";

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }

    header('Content-Type: application/json');

    $nim = isset($_POST['nim']) ? $_POST['nim'] : '';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - NIM: $nim\n", FILE_APPEND);

    if (empty($nim)) {
        echo json_encode([
            "success" => false,
            "message" => "NIM harus diisi"
        ]);
        exit();
    }

    $query = "SELECT e.nama, e.nim, e.saldo, l.email, l.nohp 
              FROM emoney e 
              LEFT JOIN login_mhs l ON e.nim COLLATE utf8mb4_general_ci = l.nim COLLATE utf8mb4_general_ci
              WHERE e.nim = ?";
              
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Data ditemukan: " . json_encode($user) . "\n", FILE_APPEND);
        
        $responseData = [
            "success" => true,
            "message" => "Data profile ditemukan",
            "data" => [
                "nama" => $user['nama'],
                "nim" => $user['nim'],
                "email" => isset($user['email']) ? $user['email'] : '',
                "no_hp" => isset($user['nohp']) ? strval($user['nohp']) : '',
                "saldo" => floatval($user['saldo'])
            ]
        ];
        
        echo json_encode($responseData);
    } else {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Data tidak ditemukan untuk NIM: $nim\n", FILE_APPEND);
        
        echo json_encode([
            "success" => false,
            "message" => "Data profile tidak ditemukan"
        ]);
    }

    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "Server error: " . $e->getMessage()
    ]);
}
?>