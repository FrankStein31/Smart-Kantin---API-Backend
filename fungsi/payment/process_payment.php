<?php
// process_payment.php

session_start();
include '../../config.php';

// Get and decode JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

try {
    // Start transaction
    $config->beginTransaction();
    
    foreach ($data['items'] as $item) {
        // Check stock availability
        $sql_check = "SELECT stok FROM barang WHERE id_barang = ?";
        $stmt_check = $config->prepare($sql_check);
        $stmt_check->execute([$item['id_barang']]);
        $stok = $stmt_check->fetch(PDO::FETCH_ASSOC)['stok'];
        
        // Calculate new stock
        $new_stok = $stok - intval($item['jumlah']);
        if ($new_stok < 0) {
            throw new Exception("Stok tidak mencukupi untuk " . $item['id_barang']);
        }
        
        // Format the date in the desired format
        $formatted_date = date('d F Y, H:i', strtotime($item['tgl_input']));
        
        // Insert into nota with proper data type handling
        $sql_nota = "INSERT INTO nota (id_barang, id_member, jumlah, total, tanggal_input, periode) 
                     VALUES (:id_barang, :id_member, :jumlah, :total, :tanggal_input, :periode)";
        $stmt_nota = $config->prepare($sql_nota);
        
        // Bind parameters with proper type casting
        $stmt_nota->execute([
            ':id_barang' => $item['id_barang'],
            ':id_member' => intval($item['id_member']),
            ':jumlah' => strval($item['jumlah']),
            ':total' => strval($item['total']),
            ':tanggal_input' => $formatted_date,  // Using the formatted date
            ':periode' => $item['periode']
        ]);
        
        // Update stock
        $sql_update = "UPDATE barang SET stok = :new_stok WHERE id_barang = :id_barang";
        $stmt_update = $config->prepare($sql_update);
        $stmt_update->execute([
            ':new_stok' => $new_stok,
            ':id_barang' => $item['id_barang']
        ]);
    }
    
    // Clear the penjualan table
    $sql_clear = "DELETE FROM penjualan";
    $stmt_clear = $config->prepare($sql_clear);
    $stmt_clear->execute();
    
    // Commit transaction
    $config->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Pembayaran berhasil diproses'
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $config->rollBack();
    
    error_log("Payment Processing Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
    ]);
}
?>