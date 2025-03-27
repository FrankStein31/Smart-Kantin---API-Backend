<?php
error_reporting(0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "db_toko";

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $conn->begin_transaction();

    try {
        $nama = isset($_POST['nama']) ? $_POST['nama'] : '';
        $nim = isset($_POST['nim']) ? $_POST['nim'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($nama) || empty($nim) || empty($password)) {
            throw new Exception("Semua field harus diisi");
        }

        $checkQuery = "SELECT nim FROM login_mhs WHERE nim = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $nim);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("NIM sudah terdaftar");
        }
        $stmt->close();

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $queryLogin = "INSERT INTO login_mhs (nama, nim, password) VALUES (?, ?, ?)";
        $stmtLogin = $conn->prepare($queryLogin);
        $stmtLogin->bind_param("sss", $nama, $nim, $hashedPassword);
        
        if (!$stmtLogin->execute()) {
            throw new Exception("Gagal melakukan registrasi user: " . $conn->error);
        }
        $stmtLogin->close();

        $queryEmoney = "INSERT INTO emoney (nim, nama, foto, saldo) VALUES (?, ?, NULL, 0.00)";
        $stmtEmoney = $conn->prepare($queryEmoney);
        $stmtEmoney->bind_param("ss", $nim, $nama);
        
        if (!$stmtEmoney->execute()) {
            throw new Exception("Gagal membuat akun emoney: " . $conn->error);
        }
        $stmtEmoney->close();

        $conn->commit();

        echo json_encode([
            "success" => true,
            "message" => "Registrasi berhasil"
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>