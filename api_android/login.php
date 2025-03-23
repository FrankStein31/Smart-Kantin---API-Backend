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
$password = $_POST['password'];

// Validasi input
if (empty($nim) || empty($password)) {
    echo json_encode([
        "success" => false,
        "message" => "NIM dan password harus diisi"
    ]);
    exit();
}

// Query untuk mencari user
$query = "SELECT id, nama, nim, password FROM login_mhs WHERE nim = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Verifikasi password
    if (password_verify($password, $user['password'])) {
        // Password benar
        echo json_encode([
            "success" => true,
            "message" => "Login berhasil",
            "data" => [
                "id" => $user['id'],
                "nama" => $user['nama'],
                "nim" => $user['nim']
            ]
        ]);
    } else {
        // Password salah
        echo json_encode([
            "success" => false,
            "message" => "Password salah"
        ]);
    }
} else {
    // User tidak ditemukan
    echo json_encode([
        "success" => false,
        "message" => "NIM tidak terdaftar"
    ]);
}

// Tutup koneksi
$stmt->close();
$conn->close();
?>