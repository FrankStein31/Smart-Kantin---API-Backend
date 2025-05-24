<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "db_toko";

$conn = new mysqli($host, $user, $pass, $db);

$conn->set_charset("utf8mb4");
$conn->query("SET NAMES utf8mb4 COLLATE utf8mb4_general_ci");

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "Koneksi database gagal: " . $conn->connect_error
    ]);
    exit();
}

header('Content-Type: application/json');

$rfid_code = $_POST['rfid_code'] ?? '';
$nim = $_POST['nim'] ?? '';
$password = $_POST['password'] ?? '';
$amount = $_POST['amount'] ?? '';
$id_barang = $_POST['id_barang'] ?? '';

error_log("Payment request: " . json_encode($_POST));

$using_rfid = !empty($rfid_code);
$using_credentials = !empty($nim) && !empty($password);

if (!$using_rfid && !$using_credentials) {
    echo json_encode([
        "success" => false,
        "message" => "Metode pembayaran tidak valid"
    ]);
    exit();
}

if (empty($amount) || empty($id_barang)) {
    echo json_encode([
        "success" => false,
        "message" => "Data transaksi tidak lengkap"
    ]);
    exit();
}

try {
    $checkTableQuery = "SHOW TABLES LIKE 'rfid_cards'";
    $tableCheck = $conn->query($checkTableQuery);
    
    if ($tableCheck->num_rows == 0) {
        $createTableQuery = "CREATE TABLE rfid_cards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rfid_code VARCHAR(100) NOT NULL UNIQUE,
            nim VARCHAR(20) NOT NULL,
            registered_date DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($createTableQuery);
    }

    $email = '';
    $nama = '';

    if ($using_rfid) {
        $rfidQuery = "SELECT r.nim, e.nama, e.saldo, l.email 
                    FROM rfid_cards r
                    JOIN emoney e ON r.nim = e.nim 
                    JOIN login_mhs l ON r.nim = l.nim
                    WHERE r.rfid_code = ?";
        $rfidStmt = $conn->prepare($rfidQuery);
        
        if (!$rfidStmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        $rfidStmt->bind_param("s", $rfid_code);
        $rfidStmt->execute();
        $rfidResult = $rfidStmt->get_result();

        if ($rfidResult->num_rows == 0) {
            echo json_encode([
                "success" => false,
                "message" => "Kartu RFID tidak terdaftar"
            ]);
            exit();
        }

        $userData = $rfidResult->fetch_assoc();
        $nim = $userData['nim'];
        $email = $userData['email'];
        $nama = $userData['nama'];
    } else {
        $loginQuery = "SELECT l.password, l.email, e.nama, e.saldo 
                    FROM login_mhs l
                    JOIN emoney e ON BINARY l.nim = BINARY e.nim 
                    WHERE l.nim = ?";
        $loginStmt = $conn->prepare($loginQuery);
        
        if (!$loginStmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        $loginStmt->bind_param("s", $nim);
        $loginStmt->execute();
        $loginResult = $loginStmt->get_result();

        if ($loginResult->num_rows == 0) {
            echo json_encode([
                "success" => false,
                "message" => "Akun tidak ditemukan"
            ]);
            exit();
        }

        $userData = $loginResult->fetch_assoc();
        $email = $userData['email'];
        $nama = $userData['nama'];

        if (!password_verify($password, $userData['password'])) {
            echo json_encode([
                "success" => false,
                "message" => "Password salah"
            ]);
            exit();
        }
    }

    $checkBarangQuery = "SELECT nama_barang FROM barang WHERE id_barang = ?";
    $checkBarangStmt = $conn->prepare($checkBarangQuery);
    $checkBarangStmt->bind_param("s", $id_barang);
    $checkBarangStmt->execute();
    $barangResult = $checkBarangStmt->get_result();
    
    if ($barangResult->num_rows == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Produk tidak ditemukan"
        ]);
        exit();
    }

    $checkRestrictionQuery = "SELECT id FROM food_restriction WHERE nim = ? AND id_barang = ?";
    $checkRestrictionStmt = $conn->prepare($checkRestrictionQuery);
    $checkRestrictionStmt->bind_param("ss", $nim, $id_barang);
    $checkRestrictionStmt->execute();
    $restrictionResult = $checkRestrictionStmt->get_result();
    
    if ($restrictionResult->num_rows > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Maaf, produk ini dibatasi untuk Anda"
        ]);
        exit();
    }
    
    $barangData = $barangResult->fetch_assoc();
    $nama_barang = $barangData['nama_barang'];

    $amountNumeric = floatval(str_replace(['Rp', ' ', ','], '', $amount));
    if ($amountNumeric > $userData['saldo']) {
        echo json_encode([
            "success" => false,
            "message" => "Saldo tidak mencukupi"
        ]);
        exit();
    }

    $check_limit_query = "SELECT limit_amount FROM daily_limit WHERE nim = ?";
    $check_limit_stmt = $conn->prepare($check_limit_query);
    $check_limit_stmt->bind_param("s", $nim);
    $check_limit_stmt->execute();
    $limit_result = $check_limit_stmt->get_result();

    if ($limit_result->num_rows > 0) {
        $limit_data = $limit_result->fetch_assoc();
        $limit_amount = floatval($limit_data['limit_amount']);
        
        if ($limit_amount > 0) {
            $today = date('Y-m-d');
            $query_spend = "SELECT SUM(totalharga) as total FROM history WHERE nim = ? AND date = ? GROUP BY date";
            $stmt_spend = $conn->prepare($query_spend);
            $stmt_spend->bind_param("ss", $nim, $today);
            $stmt_spend->execute();
            $result_spend = $stmt_spend->get_result();
            $spend_data = $result_spend->fetch_assoc();
            $total_spent = floatval($spend_data['total'] ?? 0);
            
            if (($total_spent + $amountNumeric) > $limit_amount) {
                echo json_encode([
                    "success" => false,
                    "message" => "Transaksi melebihi batas pengeluaran harian"
                ]);
                exit();
            }
        }
    }

    // Mulai transaksi
    $conn->begin_transaction();

    $updateQuery = "UPDATE emoney SET saldo = saldo - ? WHERE nim = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ds", $amountNumeric, $nim);
    $updateStmt->execute();

    $insertHistoryQuery = "INSERT INTO history (nim, totalharga, id_barang, date, time) 
                           VALUES (?, ?, ?, CURRENT_DATE, CURRENT_TIME)";
    $insertHistoryStmt = $conn->prepare($insertHistoryQuery);
    $insertHistoryStmt->bind_param("sss", $nim, $amount, $id_barang);
    $insertHistoryStmt->execute();

    $conn->commit();

    if (!empty($email)) {
        $saldo_baru = $userData['saldo'] - $amountNumeric;
        $tanggal = date("d-m-Y");
        $waktu = date("H:i:s");
        
        $subject = "Notifikasi Pembayaran Smart Kantin";
        $message = "
        <html>
        <head>
            <title>Notifikasi Pembayaran</title>
        </head>
        <body>
            <h3>Detail Transaksi Smart Kantin</h3>
            <p>Halo $nama,</p>
            <p>Transaksi Anda telah berhasil diproses:</p>
            <table>
                <tr>
                    <td>Tanggal</td>
                    <td>: $tanggal</td>
                </tr>
                <tr>
                    <td>Waktu</td>
                    <td>: $waktu</td>
                </tr>
                <tr>
                    <td>Produk</td>
                    <td>: $nama_barang</td>
                </tr>
                <tr>
                    <td>Harga</td>
                    <td>: Rp " . number_format($amountNumeric, 0, ',', '.') . "</td>
                </tr>
                <tr>
                    <td>Saldo tersisa</td>
                    <td>: Rp " . number_format($saldo_baru, 0, ',', '.') . "</td>
                </tr>
            </table>
            <p>Terima kasih telah menggunakan layanan Smart Kantin!</p>
        </body>
        </html>
        ";
        
        ini_set("SMTP", "smtp.gmail.com");
        ini_set("smtp_port", "587");

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Sistem Smart Kantin <bukudigital41@gmail.com>" . "\r\n";
        
        $additional_params = "-f bukudigital41@gmail.com";
        
        $mail_sent = mail($email, $subject, $message, $headers, $additional_params);
        
        $log_message = $mail_sent ? "Email berhasil dikirim ke $email" : "Gagal mengirim email ke $email";
        error_log($log_message);
    }

    echo json_encode([
        "success" => true,
        "message" => "Pembayaran berhasil",
        "data" => [
            "nim" => $nim,
            "product" => $nama_barang,
            "saldo_baru" => $userData['saldo'] - $amountNumeric
        ]
    ]);

} catch (Exception $e) {
    if ($conn->connect_errno != 0) {
        $conn->rollback();
    }

    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan: " . $e->getMessage()
    ]);
}

$conn->close();
?>
