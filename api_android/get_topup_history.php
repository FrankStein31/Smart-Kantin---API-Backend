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

if (empty($nim)) {
    echo json_encode([
        "success" => false,
        "message" => "NIM harus diisi"
    ]);
    exit();
}

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
    $row['nominal'] = floatval($row['nominal']);

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

    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "message" => "Data history ditemukan",
    "data" => $data
]);

$stmt->close();
$conn->close();
