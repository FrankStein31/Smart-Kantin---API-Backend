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

$nim = $_GET['nim'] ?? '';
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

if (empty($nim)) {
    echo json_encode([
        "success" => false,
        "message" => "NIM tidak valid"
    ]);
    exit();
}

try {
    // Filter tanggal jika parameter disediakan
    $whereClause = "WHERE h.nim = ?";
    $params = [$nim];
    $types = "s";
    
    if (!empty($start_date)) {
        $whereClause .= " AND h.date >= ?";
        $params[] = $start_date;
        $types .= "s";
    }
    
    if (!empty($end_date)) {
        $whereClause .= " AND h.date <= ?";
        $params[] = $end_date;
        $types .= "s";
    }
    
    // Query untuk riwayat transaksi
    $query = "SELECT h.id_h, h.nim, h.totalharga, h.date, h.time, 
               b.nama_barang, k.nama_kategori 
              FROM history h
              LEFT JOIN barang b ON h.id_barang = b.id_barang
              LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
              $whereClause
              ORDER BY h.date DESC, h.time DESC 
              LIMIT 50";  
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }

    // Query untuk total pengeluaran per hari (30 hari terakhir)
    $daily_query = "SELECT 
                    h.date, 
                    DAY(h.date) as day, 
                    SUM(h.totalharga) as total 
                   FROM history h 
                   WHERE h.nim = ? 
                   AND h.date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
                   GROUP BY h.date, DAY(h.date) 
                   ORDER BY h.date DESC";
                   
    $daily_stmt = $conn->prepare($daily_query);
    $daily_stmt->bind_param("s", $nim);
    $daily_stmt->execute();
    $daily_result = $daily_stmt->get_result();
    
    $daily_totals = [];
    while ($row = $daily_result->fetch_assoc()) {
        $daily_totals[] = $row;
    }
    
    // Query untuk total pengeluaran per bulan (12 bulan terakhir)
    $monthly_query = "SELECT 
                    YEAR(h.date) as year, 
                    MONTH(h.date) as month, 
                    DATE_FORMAT(h.date, '%Y-%m') as month_year,
                    SUM(h.totalharga) as total 
                   FROM history h 
                   WHERE h.nim = ? 
                   AND h.date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
                   GROUP BY YEAR(h.date), MONTH(h.date), DATE_FORMAT(h.date, '%Y-%m') 
                   ORDER BY YEAR(h.date) DESC, MONTH(h.date) DESC";
                   
    $monthly_stmt = $conn->prepare($monthly_query);
    $monthly_stmt->bind_param("s", $nim);
    $monthly_stmt->execute();
    $monthly_result = $monthly_stmt->get_result();
    
    $monthly_totals = [];
    while ($row = $monthly_result->fetch_assoc()) {
        $monthly_totals[] = $row;
    }
    
    // Query untuk total pengeluaran berdasarkan filter aktif
    $total_query = "SELECT SUM(h.totalharga) as filter_total FROM history h $whereClause";
    $total_stmt = $conn->prepare($total_query);
    $total_stmt->bind_param($types, ...$params);
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $filter_total = $total_result->fetch_assoc()['filter_total'] ?? 0;

    echo json_encode([
        "success" => true,
        "data" => $transactions,
        "daily_totals" => $daily_totals,
        "monthly_totals" => $monthly_totals,
        "filter_total" => $filter_total
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan: " . $e->getMessage()
    ]);
}

$conn->close();
?>