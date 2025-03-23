<?php
// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Prevent any output before JSON response
ob_clean();

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db = "db_toko";

// Set header response JSON
header('Content-Type: application/json');

try {
    // Create connection
    $conn = new mysqli($host, $user, $pass, $db);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get POST data
    if (!isset($_POST['nim']) || !isset($_POST['nominal']) || !isset($_FILES['fotobukti'])) {
        throw new Exception("Missing required fields");
    }

    $nim = $_POST['nim'];
    $nominal = $_POST['nominal'];
    $fotoBukti = $_FILES['fotobukti'];

    // Validate input
    if (empty($nim) || empty($nominal) || empty($fotoBukti)) {
        throw new Exception("Semua field harus diisi");
    }

    // Get user name from emoney table
    $query = "SELECT nama FROM emoney WHERE nim = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        throw new Exception("User tidak ditemukan");
    }

    // Handle file upload
    $target_dir = "../assets/img/bukti_transfer/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($fotoBukti["name"], PATHINFO_EXTENSION));
    $new_filename = "bukti_" . time() . "_" . $nim . "." . $file_extension;
    $target_file = $target_dir . $new_filename;

    // Check file type
    $allowed_types = array("jpg", "jpeg", "png");
    if (!in_array($file_extension, $allowed_types)) {
        throw new Exception("Hanya file JPG, JPEG, dan PNG yang diperbolehkan");
    }

    // Upload file
    if (!move_uploaded_file($fotoBukti["tmp_name"], $target_file)) {
        throw new Exception("Gagal mengupload file");
    }

    // Insert into validasi table
    $query = "INSERT INTO validasi (nim, nama, nominal, fotobukti, valid) VALUES (?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssds", $nim, $user['nama'], $nominal, $new_filename);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data top up");
    }

    echo json_encode([
        "success" => true,
        "message" => "Top up request berhasil disubmit"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}