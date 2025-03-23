<?php 
session_start();
include '../../config.php';

if(!isset($_GET['validasi'])) {
    echo "<script>window.location='../../index.php?page=validasi';</script>";
    exit;
}

if($_GET['validasi'] == 'update') {
    $id_validasi = $_POST['id_validasi'];
    $status = $_POST['status'];
    $nim = $_POST['nim'];
    $nominal = $_POST['nominal'];

    try {
        // Mulai transaction
        $config->beginTransaction();

        // Update status validasi
        $sql = "UPDATE validasi SET valid = ? WHERE id_validasi = ?";
        $stmt = $config->prepare($sql);
        $stmt->execute([$status, $id_validasi]);

        // Jika divalidasi (status = 1), tambahkan saldo ke emoney
        if($status == 1) {
            // Update saldo di tabel emoney
            $sql = "UPDATE emoney SET saldo = saldo + ? WHERE nim = ?";
            $stmt = $config->prepare($sql);
            $stmt->execute([$nominal, $nim]);
        }

        // Commit transaction
        $config->commit();

        // Redirect dengan pesan sukses
        if($status == 1) {
            echo "<script>window.location='../../index.php?page=validasi&success=1';</script>";
        } else {
            echo "<script>window.location='../../index.php?page=validasi&rejected=1';</script>";
        }
    } catch(Exception $e) {
        // Rollback jika terjadi error
        $config->rollBack();
        echo "<script>alert('Terjadi kesalahan!');window.location='../../index.php?page=validasi';</script>";
    }
}
?>