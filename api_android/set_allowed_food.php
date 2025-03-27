<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "db_toko";

$conn = new mysqli($host, $user, $pass, $db);

$conn->set_charset("utf8mb4");
$conn->query("SET NAMES utf8mb4 COLLATE utf8mb4_general_ci");

header('Content-Type: application/json');

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Koneksi database gagal: " . $conn->connect_error
    ]);
    exit();
}

$json_input = file_get_contents("php://input");
error_log("Received data: " . $json_input);

$data = json_decode($json_input, true);

if (!isset($data['nim']) || !isset($data['allowed_food'])) {
    echo json_encode([
        "success" => false,
        "message" => "Data tidak lengkap"
    ]);
    exit();
}

$nim = $data['nim'];
$allowed_food = $data['allowed_food']; 

$check_table = "SHOW TABLES LIKE 'food_restriction'";
$table_exists = $conn->query($check_table);

if ($table_exists->num_rows == 0) {
    $create_table = "CREATE TABLE food_restriction (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nim VARCHAR(20) NOT NULL,
        id_barang VARCHAR(20) NOT NULL,
        UNIQUE KEY (nim, id_barang)
    )";
    $conn->query($create_table);
}

$delete_query = "DELETE FROM food_restriction WHERE nim = ?";
$stmt_delete = $conn->prepare($delete_query);
$stmt_delete->bind_param("s", $nim);
$stmt_delete->execute();

$success = true;

if (is_array($allowed_food)) {
    $insert_query = "INSERT INTO food_restriction (nim, id_barang) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($insert_query);
    
    if ($stmt_insert) {
        foreach ($allowed_food as $id_barang) {
            $stmt_insert->bind_param("ss", $nim, $id_barang);
            if (!$stmt_insert->execute()) {
                $success = false;
                error_log("Error inserting: " . $conn->error);
                break;
            }
        }
    } else {
        $success = false;
        error_log("Error preparing statement: " . $conn->error);
    }
} else {
    error_log("Warning: allowed_food is not an array, received: " . gettype($allowed_food));
    if (!empty($allowed_food)) {
        $insert_query = "INSERT INTO food_restriction (nim, id_barang) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($insert_query);
        if ($stmt_insert) {
            $stmt_insert->bind_param("ss", $nim, $allowed_food);
            $success = $stmt_insert->execute();
        } else {
            $success = false;
        }
    }
}

if ($success) {
    echo json_encode([
        "success" => true,
        "message" => "Pengaturan makanan yang diizinkan berhasil disimpan"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal menyimpan pengaturan makanan: " . $conn->error
    ]);
}

$conn->close();
?> 