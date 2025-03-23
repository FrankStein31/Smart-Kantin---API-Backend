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
$nim = $_POST['nim'] ?? '';
$nama = $_POST['nama'] ?? '';
$email = $_POST['email'] ?? '';
$nohp = $_POST['nohp'] ?? '';

// Validasi input
if (empty($nim) || empty($nama)) {
    echo json_encode([
        "success" => false,
        "message" => "NIM dan Nama harus diisi"
    ]);
    exit();
}

// Mulai transaksi
$conn->begin_transaction();

try {
    // Update tabel emoney
    $query_emoney = "UPDATE emoney SET nama = ? WHERE nim = ?";
    $stmt_emoney = $conn->prepare($query_emoney);
    $stmt_emoney->bind_param("ss", $nama, $nim);
    $result_emoney = $stmt_emoney->execute();
    
    // Cek apakah user sudah ada di login_mhs
    $query_check = "SELECT id FROM login_mhs WHERE nim = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("s", $nim);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        // Update data di login_mhs
        $query_login = "UPDATE login_mhs SET nama = ?, email = ?, nohp = ? WHERE nim = ?";
        $stmt_login = $conn->prepare($query_login);
        $stmt_login->bind_param("ssss", $nama, $email, $nohp, $nim);
        $result_login = $stmt_login->execute();
    } else {
        // Insert data baru ke login_mhs tanpa password
        $query_insert = "INSERT INTO login_mhs (nama, nim, email, nohp) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->bind_param("ssss", $nama, $nim, $email, $nohp);
        $result_insert = $stmt_insert->execute();
    }
    
    // Commit transaksi jika semua berhasil
    $conn->commit();
    
    echo json_encode([
        "success" => true,
        "message" => "Profil berhasil diperbarui"
    ]);
    
} catch (Exception $e) {
    // Rollback jika terjadi error
    $conn->rollback();
    
    echo json_encode([
        "success" => false,
        "message" => "Gagal memperbarui profil: " . $e->getMessage()
    ]);
}

// Tutup koneksi
$conn->close();
?> 