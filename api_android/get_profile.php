<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log error untuk debugging
$logFile = __DIR__ . '/error_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - API get_profile.php dipanggil\n", FILE_APPEND);

try {
    // Konfigurasi database
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "db_toko";

    // Membuat koneksi
    $conn = new mysqli($host, $user, $pass, $db);

    // Cek koneksi
    if ($conn->connect_error) {
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }

    // Set header response JSON
    header('Content-Type: application/json');

    // Terima data dari Android
    $nim = isset($_POST['nim']) ? $_POST['nim'] : '';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - NIM: $nim\n", FILE_APPEND);

    // Validasi input
    if (empty($nim)) {
        echo json_encode([
            "success" => false,
            "message" => "NIM harus diisi"
        ]);
        exit();
    }

    // Query untuk mencari data user dan saldo
    // Tambahkan COLLATE untuk mengatasi perbedaan collation
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
        
        // Log data yang ditemukan
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Data ditemukan: " . json_encode($user) . "\n", FILE_APPEND);
        
        // Data ditemukan
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
        // Data tidak ditemukan
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Data tidak ditemukan untuk NIM: $nim\n", FILE_APPEND);
        
        echo json_encode([
            "success" => false,
            "message" => "Data profile tidak ditemukan"
        ]);
    }

    // Tutup koneksi
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Log error
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Kirim respons error
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "Server error: " . $e->getMessage()
    ]);
}
?>