<?php

session_start();
if (!empty($_SESSION['admin'])) {
    require '../../config.php';
    if (!empty($_GET['kategori'])) {
        $id= $_GET['id'];
        $data[] = $id;
        $sql = 'DELETE FROM kategori WHERE id_kategori=?';
        $row = $config -> prepare($sql);
        $row -> execute($data);
        echo '<script>window.location="../../index.php?page=kategori&&remove=hapus-data"</script>';
    }

    if (!empty($_GET['barang'])) {
        $id= $_GET['id'];
        $data[] = $id;
        $sql = 'DELETE FROM barang WHERE id_barang=?';
        $row = $config -> prepare($sql);
        $row -> execute($data);
        echo '<script>window.location="../../index.php?page=barang&&remove=hapus-data"</script>';
    }

    // if (!empty($_GET['emoney'])) {
    //     $id= $_GET['id'];
    //     $data[] = $id;
    //     $sql = 'DELETE FROM emoney WHERE id=?';
    //     $row = $config -> prepare($sql);
    //     $row -> execute($data);
    //     echo '<script>window.location="../../index.php?page=emoney&&remove=hapus-data"</script>';
    // }

    if (!empty($_GET['emoney'])) {
        $id = $_GET['id'];
        
        // Ambil data foto sebelum menghapus
        $sql = 'SELECT foto FROM emoney WHERE id = ?';
        $row = $config->prepare($sql);
        $row->execute([$id]);
        $data = $row->fetch();
        
        // Hapus foto jika ada
        if(!empty($data['foto'])){
            $file = "../../assets/img/emoney/" . $data['foto'];
            if(file_exists($file)){
                unlink($file);
            }
        }
        
        // Hapus data dari database
        $sql = 'DELETE FROM emoney WHERE id=?';
        $row = $config->prepare($sql);
        $row->execute([$id]);
        
        echo '<script>window.location="../../index.php?page=emoney&remove=hapus-data"</script>';
    }

    if (!empty($_GET['jual'])) {
        $dataI[] = $_GET['brg'];
        $sqlI = 'select*from barang where id_barang=?';
        $rowI = $config -> prepare($sqlI);
        $rowI -> execute($dataI);
        $hasil = $rowI -> fetch();

        /*$jml = $_GET['jml'] + $hasil['stok'];

        $dataU[] = $jml;
        $dataU[] = $_GET['brg'];
        $sqlU = 'UPDATE barang SET stok =? where id_barang=?';
        $rowU = $config -> prepare($sqlU);
        $rowU -> execute($dataU);*/

        $id = $_GET['id'];
        $data[] = $id;
        $sql = 'DELETE FROM penjualan WHERE id_penjualan=?';
        $row = $config -> prepare($sql);
        $row -> execute($data);
        echo '<script>window.location="../../index.php?page=jual"</script>';
    }

    if (!empty($_GET['penjualan'])) {
        $sql = 'DELETE FROM penjualan';
        $row = $config -> prepare($sql);
        $row -> execute();
        echo '<script>window.location="../../index.php?page=jual"</script>';
    }
    
    if (!empty($_GET['laporan'])) {
        $sql = 'DELETE FROM nota';
        $row = $config -> prepare($sql);
        $row -> execute();
        echo '<script>window.location="../../index.php?page=laporan&remove=hapus"</script>';
    }
}
