<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "db_toko";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

$nim = $_POST['nim'];
$password = $_POST['password'];

if (empty($nim) || empty($password)) {
    echo json_encode([
        "success" => false,
        "message" => "NIM dan password harus diisi"
    ]);
    exit();
}

$query = "SELECT id, nama, nim, password FROM login_mhs WHERE nim = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
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
        echo json_encode([
            "success" => false,
            "message" => "Password salah"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "NIM tidak terdaftar"
    ]);
}

// Tutup koneksi
$stmt->close();
$conn->close();
?>