<?php
session_start();
if (!empty($_SESSION['admin'])) {
    require '../../config.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $total = $_POST['total'];
        $bayar = $_POST['bayar'];
        
        // Process the payment
        if ($bayar >= $total) {
            $id_barang = $_POST['id_barang'];
            $id_member = $_POST['id_member'];
            $jumlah = $_POST['jumlah'];
            $total_array = $_POST['total1'];
            $tgl_input = $_POST['tgl_input'];
            $periode = $_POST['periode'];
            $jumlah_dipilih = count($id_barang);

            try {
                // Begin transaction
                $config->beginTransaction();

                for ($x = 0; $x < $jumlah_dipilih; $x++) {
                    // Insert into nota
                    $sql = "INSERT INTO nota (id_barang, id_member, jumlah, total, tanggal_input, periode) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $row = $config->prepare($sql);
                    $row->execute([
                        $id_barang[$x],
                        $id_member[$x],
                        $jumlah[$x],
                        $total_array[$x],
                        $tgl_input[$x],
                        $periode[$x]
                    ]);

                    // Update stock
                    $sql_barang = "SELECT stok FROM barang WHERE id_barang = ?";
                    $row_barang = $config->prepare($sql_barang);
                    $row_barang->execute([$id_barang[$x]]);
                    $current_stock = $row_barang->fetchColumn();

                    $new_stock = $current_stock - $jumlah[$x];
                    
                    $sql_update = "UPDATE barang SET stok = ? WHERE id_barang = ?";
                    $row_update = $config->prepare($sql_update);
                    $row_update->execute([$new_stock, $id_barang[$x]]);
                }

                // Clear the cart
                $sql_clear = "DELETE FROM penjualan";
                $row_clear = $config->prepare($sql_clear);
                $row_clear->execute();

                // Commit transaction
                $config->commit();

                // Show success alert and redirect
                echo '<script>
                    alert("Pembayaran berhasil dilakukan!");
                    window.location.href = "../../index.php?page=jual&success=payment-complete";
                </script>';
                exit;

            } catch (Exception $e) {
                // Rollback transaction on error
                $config->rollBack();
                echo '<script>
                    alert("Pembayaran gagal! Silakan coba lagi.");
                    window.location.href = "../../index.php?page=jual&error=payment-failed";
                </script>';
                exit;
            }
        } else {
            echo '<script>
                alert("Pembayaran gagal! Jumlah pembayaran kurang dari total.");
                window.location.href = "../../index.php?page=jual&error=insufficient-payment";
            </script>';
            exit;
        }
    }
}