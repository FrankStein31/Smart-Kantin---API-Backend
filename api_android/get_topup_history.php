<?php
// get_topup_history.php

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db = "db_toko";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set header response JSON
header('Content-Type: application/json');

// Get NIM from POST request
$nim = $_POST['nim'];

// Validate input
if (empty($nim)) {
    echo json_encode([
        "success" => false,
        "message" => "NIM harus diisi"
    ]);
    exit();
}

// Query untuk mengambil 5 history teratas
$query = "SELECT id_validasi, nim, nama, nominal, fotobukti, valid 
        FROM validasi 
        WHERE nim = ? 
        ORDER BY id_validasi DESC";
// $query = "SELECT id_validasi, nim, nama, nominal, fotobukti, valid 
//         FROM validasi 
//         WHERE nim = ? 
//         ORDER BY id_validasi DESC 
//         LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    // Convert nominal to float for proper JSON encoding
    $row['nominal'] = floatval($row['nominal']);

    // Handle validasi status
    switch ($row['valid']) {
        case 1:
            $row['valid_text'] = "Disetujui";
            break;
        case 2:
            $row['valid_text'] = "Ditolak";
            break;
        default:
            $row['valid_text'] = "Menunggu";
            break;
    }

    // Add data to result
    $data[] = $row;
}

// Return JSON response
echo json_encode([
    "success" => true,
    "message" => "Data history ditemukan",
    "data" => $data
]);

$stmt->close();
$conn->close();
