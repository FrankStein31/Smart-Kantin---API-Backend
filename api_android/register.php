<?php
// Nonaktifkan pesan error PHP agar tidak mengganggu output JSON
error_reporting(0);

// Set header untuk JSON dan CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

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
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // Terima data dari Android
        $nama = isset($_POST['nama']) ? $_POST['nama'] : '';
        $nim = isset($_POST['nim']) ? $_POST['nim'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        // Validasi input
        if (empty($nama) || empty($nim) || empty($password)) {
            throw new Exception("Semua field harus diisi");
        }

        // Cek apakah NIM sudah terdaftar di tabel login_mhs
        $checkQuery = "SELECT nim FROM login_mhs WHERE nim = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $nim);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("NIM sudah terdaftar");
        }
        $stmt->close();

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Query insert ke tabel login_mhs
        $queryLogin = "INSERT INTO login_mhs (nama, nim, password) VALUES (?, ?, ?)";
        $stmtLogin = $conn->prepare($queryLogin);
        $stmtLogin->bind_param("sss", $nama, $nim, $hashedPassword);
        
        if (!$stmtLogin->execute()) {
            throw new Exception("Gagal melakukan registrasi user: " . $conn->error);
        }
        $stmtLogin->close();

        // Query insert ke tabel emoney dengan saldo default 0 dan foto null
        $queryEmoney = "INSERT INTO emoney (nim, nama, foto, saldo) VALUES (?, ?, NULL, 0.00)";
        $stmtEmoney = $conn->prepare($queryEmoney);
        $stmtEmoney->bind_param("ss", $nim, $nama);
        
        if (!$stmtEmoney->execute()) {
            throw new Exception("Gagal membuat akun emoney: " . $conn->error);
        }
        $stmtEmoney->close();

        // Commit transaksi jika semua query berhasil
        $conn->commit();

        echo json_encode([
            "success" => true,
            "message" => "Registrasi berhasil"
        ]);

    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        $conn->rollback();
        throw $e;
    }

    // Tutup koneksi
    $conn->close();

} catch (Exception $e) {
    // Return error dalam format JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>