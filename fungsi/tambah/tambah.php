<?php

session_start();
if (!empty($_SESSION['admin'])) {
    require '../../config.php';
    if (!empty($_GET['kategori'])) {
        $nama= $_POST['kategori'];
        $tgl= date("j F Y, G:i");
        $data[] = $nama;
        $data[] = $tgl;
        $sql = 'INSERT INTO kategori (nama_kategori,tgl_input) VALUES(?,?)';
        $row = $config -> prepare($sql);
        $row -> execute($data);
        echo '<script>window.location="../../index.php?page=kategori&&success=tambah-data"</script>';
    }

    if (!empty($_GET['barang'])) {
        $id = $_POST['id'];
        $kategori = $_POST['kategori'];
        $nama = $_POST['nama'];
        $merk = $_POST['merk'];
        $beli = $_POST['beli'];
        $jual = $_POST['jual'];
        $satuan = $_POST['satuan'];
        $stok = $_POST['stok'];
        $expired = $_POST['expired'];
        $tgl = $_POST['tgl'];

        // Handle foto upload
        $foto = '';
        if(!empty($_FILES['foto']['name'])){
            $target_dir = "../../assets/img/barang/";
            $foto = time() . '_' . basename($_FILES["foto"]["name"]);
            $target_file = $target_dir . $foto;
            
            // Cek dan buat direktori jika belum ada
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Upload file
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                // File berhasil diupload
            } else {
                echo '<script>alert("Maaf, terjadi kesalahan saat mengupload file.");
                      window.location="../../index.php?page=barang"</script>';
                exit;
            }
        }

        $data[] = $id;
        $data[] = $kategori;
        $data[] = $nama;
        $data[] = $merk;
        $data[] = $beli;
        $data[] = $jual;
        $data[] = $satuan;
        $data[] = $stok;
        $data[] = $foto;
        $data[] = $expired;
        $data[] = $tgl;
        $sql = 'INSERT INTO barang (id_barang,id_kategori,nama_barang,merk,harga_beli,harga_jual,satuan_barang,stok,foto,expired,tgl_input) 
			    VALUES (?,?,?,?,?,?,?,?,?,?,?) ';
        $row = $config -> prepare($sql);
        $row -> execute($data);
        echo '<script>window.location="../../index.php?page=barang&success=tambah-data"</script>';
    }
    // if (!empty($_GET['emoney'])) {
    //     $id = $_POST['id'];
    //     $nim = $_POST['nim'];
    //     $nama = $_POST['nama'];
    //     $foto = $_POST['foto'];
    //     $saldo = $_POST['saldo'];

    //     $data[] = $id;
    //     $data[] = $nim;
    //     $data[] = $nama;
    //     $data[] = $foto;
    //     $data[] = $saldo;

    //     $sql = 'INSERT INTO emoney (id,nim,nama,foto,saldo) 
	// 		    VALUES (?,?,?,?,?) ';
    //     $row = $config -> prepare($sql);
    //     $row -> execute($data);
    //     echo '<script>window.location="../../index.php?page=emoney&success=tambah-data"</script>';
    // }

    if (!empty($_GET['emoney'])) {
        $nim = htmlentities($_POST['nim']);
        $nama = htmlentities($_POST['nama']);
        $saldo = htmlentities($_POST['saldo']);
        
        $sql = 'INSERT INTO emoney (nim, nama, saldo) VALUES (?,?,?)';
        $row = $config->prepare($sql);
        $row->execute([$nim, $nama, $saldo]);
        
        echo '<script>window.location="../../index.php?page=emoney&success=tambah-data"</script>';
    }

    if(!empty($_GET['topup']) && $_GET['topup'] == 'tambah') {
        $nim = strip_tags($_POST['nim']);
        $nominal = strip_tags($_POST['nominal']);
        
        // Get nama from emoney table
        $sql_nama = "SELECT nama FROM emoney WHERE nim = ?";
        $stmt_nama = $config->prepare($sql_nama);
        $stmt_nama->execute([$nim]);
        $nama = $stmt_nama->fetchColumn();
        
        // Insert into validasi table with valid=1 (auto approved)
        $sql = "INSERT INTO validasi (nim, nama, nominal, valid) 
               VALUES (?, ?, ?, 1)";
        
        try {
            $stmt = $config->prepare($sql);
            $stmt->execute([$nim, $nama, $nominal]);
            
            // Update saldo in emoney table
            $update_saldo = "UPDATE emoney SET saldo = saldo + ? WHERE nim = ?";
            $stmt_update = $config->prepare($update_saldo);
            $stmt_update->execute([$nominal, $nim]);
            
            echo '<script>window.location="../../index.php?page=emoney/topup&success=tambah"</script>';
        } catch(PDOException $e) {
            echo '<script>alert("Gagal menambahkan saldo: ' . $e->getMessage() . '");
                  window.location="../../index.php?page=emoney/topup"</script>';
        }
    }
    
    if (!empty($_GET['jual'])) {
        $id = $_GET['id'];

        // get tabel barang id_barang
        $sql = 'SELECT * FROM barang WHERE id_barang = ?';
        $row = $config->prepare($sql);
        $row->execute(array($id));
        $hsl = $row->fetch();

        if ($hsl['stok'] > 0) {
            $kasir =  $_GET['id_kasir'];
            $jumlah = 1;
            $total = $hsl['harga_jual'];
            $tgl = date("j F Y, G:i");

            $data1[] = $id;
            $data1[] = $kasir;
            $data1[] = $jumlah;
            $data1[] = $total;
            $data1[] = $tgl;

            $sql1 = 'INSERT INTO penjualan (id_barang,id_member,jumlah,total,tanggal_input) VALUES (?,?,?,?,?)';
            $row1 = $config -> prepare($sql1);
            $row1 -> execute($data1);

            echo '<script>window.location="../../index.php?page=jual&success=tambah-data"</script>';
        } else {
            echo '<script>alert("Stok Barang Anda Telah Habis !");
					window.location="../../index.php?page=jual#keranjang"</script>';
        }
    }
}
