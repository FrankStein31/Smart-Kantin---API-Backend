<?php
// process_payment.php

session_start();
include '../../config.php';

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    try {
        $config->beginTransaction();

        foreach ($data['items'] as $item) {
            // Insert into nota
            $sql = "INSERT INTO nota (id_barang, id_member, nim, jumlah, total, tanggal_input, periode) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $config->prepare($sql);
            $stmt->execute([
                $item['id_barang'],
                $item['id_member'],
                $item['nim'],
                $item['jumlah'],
                $item['total'],
                $item['tgl_input'],
                $item['periode']
            ]);

            // Insert into history if NIM exists
            if (!empty($item['nim'])) {
                $date = date('Y-m-d');
                $time = date('H:i:s');
                $history_data = [
                    'nim' => $item['nim'],
                    'totalharga' => $item['total'],
                    'id_barang' => $item['id_barang'],
                    'date' => $date,
                    'time' => $time
                ];
                
                $sql_history = 'INSERT INTO history (nim,totalharga,id_barang,date,time) 
                               VALUES (:nim,:totalharga,:id_barang,:date,:time)';
                $stmt_history = $config->prepare($sql_history);
                $stmt_history->execute($history_data);
            }

            // Update stock
            $sql_barang = "SELECT stok FROM barang WHERE id_barang = ?";
            $stmt_barang = $config->prepare($sql_barang);
            $stmt_barang->execute([$item['id_barang']]);
            $barang = $stmt_barang->fetch();

            $new_stok = $barang['stok'] - $item['jumlah'];
            $sql_update = "UPDATE barang SET stok = ? WHERE id_barang = ?";
            $stmt_update = $config->prepare($sql_update);
            $stmt_update->execute([$new_stok, $item['id_barang']]);
        }

        // Clear penjualan table
        $sql_clear = "DELETE FROM penjualan";
        $stmt_clear = $config->prepare($sql_clear);
        $stmt_clear->execute();

        $config->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Pembayaran berhasil'
        ]);
    } catch (Exception $e) {
        $config->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data received'
    ]);
}
?>