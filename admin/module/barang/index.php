<h4>Data Barang</h4>
        <br />
        <?php if (isset($_GET['success-stok'])) { ?>
            <div class="alert alert-success">
                <p>Tambah Stok Berhasil !</p>
            </div>
        <?php } ?>
        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success">
                <p>Tambah Data Berhasil !</p>
            </div>
        <?php } ?>
        <?php if (isset($_GET['remove'])) { ?>
            <div class="alert alert-danger">
                <p>Hapus Data Berhasil !</p>
            </div>
        <?php } ?>

        <?php
        $sql = " select * from barang where stok <= 3";
        $row = $config->prepare($sql);
        $row->execute();
        $r = $row->rowCount();
        if ($r > 0) {
            echo "
				<div class='alert alert-warning'>
					<span class='glyphicon glyphicon-info-sign'></span> Ada <span style='color:red'>$r</span> barang yang Stok tersisa sudah kurang dari 3 items. silahkan pesan lagi !!
					<span class='pull-right'><a href='index.php?page=barang&stok=yes'>Cek Barang <i class='fa fa-angle-double-right'></i></a></span>
				</div>
				";
        }

        // $sql1 = "SELECT * FROM barang WHERE DATEDIFF(expired, CURDATE()) <= 7";
        // $row1 = $config->prepare($sql1);
        // $row1->execute();
        // $r1 = $row1->rowCount();

        // if ($r1 > 0) {
        //     echo "
        //         <div class='alert alert-warning'>
        //             <span class='glyphicon glyphicon-info-sign'></span> Ada <span style='color:red'>$r1</span> barang yang akan kedaluwarsa dalam waktu kurang dari 7 hari. Silakan cek dan lakukan tindakan yang diperlukan!
        //             <span class='pull-right'><a href='index.php?page=barang&expired=yes'>Cek Barang <i class='fa fa-angle-double-right'></i></a></span>
        //         </div>
        //         ";
        // }
        $sql1 = "SELECT 
                    id,
                    id_barang,
                    nama_barang,
                    merk,
                    stok,
                    expired,
                    DATEDIFF(expired, CURDATE()) AS days_until_expiry,
                    CASE 
                        WHEN DATEDIFF(expired, CURDATE()) BETWEEN 0 AND 3 THEN 'Kritis'
                        WHEN DATEDIFF(expired, CURDATE()) BETWEEN 3 AND 7 THEN 'Waspada'
                        WHEN DATEDIFF(expired, CURDATE()) BETWEEN -100 AND 0 THEN 'Kadaluarsa'
                    END AS expiry_status,
                    CONCAT(
                        FLOOR(stok), ' ', satuan_barang
                    ) AS stock_info,
                    harga_beli,
                    harga_jual,
                    id_kategori
                FROM barang
                WHERE 
                    DATEDIFF(expired, CURDATE()) <= 7
                    AND stok > 0
                ORDER BY 
                    days_until_expiry ASC, 
                    stok DESC";

        $row1 = $config->prepare($sql1);
        $row1->execute();
        $expired_items = $row1->fetchAll(PDO::FETCH_ASSOC);

        if (count($expired_items) > 0) {
            echo "<div class='alert alert-warning'>";
            echo "<span class='glyphicon glyphicon-info-sign'></span> ";
            echo "Ada <span style='color:red'>" . count($expired_items) . "</span> barang yang akan kedaluwarsa atau sudah kedaluwarsa ";
            
            echo "<button id='toggleExpiredItemsBtn' class='btn btn-link'>
                    <i class='fa fa-chevron-down' id='toggleIcon'></i>
                  </button>";
            
            echo "<div id='expiredItemsTable' style='display:none;'>";
            echo "<table class='table table-striped'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th>Nama Barang</th>";
            echo "<th>Merk</th>";
            echo "<th>Stok</th>";
            echo "<th>Tanggal Expired</th>";
            echo "<th>Hari Tersisa</th>";
            echo "<th>Status</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            
            foreach ($expired_items as $item) {
                echo "<tr class='";
                echo ($item['days_until_expiry'] <= 3) ? 'table-danger' : 
                     (($item['days_until_expiry'] <= 7) ? 'table-warning' : 'table-info');
                echo "'>";
                echo "<td>" . htmlspecialchars($item['nama_barang']) . "</td>";
                echo "<td>" . htmlspecialchars($item['merk']) . "</td>";
                echo "<td>" . htmlspecialchars($item['stock_info']) . "</td>";
                echo "<td>" . $item['expired'] . "</td>";
                echo "<td>" . $item['days_until_expiry'] . " hari</td>";
                echo "<td>" . $item['expiry_status'] . "</td>";
                echo "</tr>";
            }
            
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            
            echo "<span class='pull-right'>
                <a href='index.php?page=barang&expired=yes'>
                    Cek Detail Barang <i class='fa fa-angle-double-right'></i>
                </a>
            </span>";
            echo "</div>";
            
            echo "<script>
            document.getElementById('toggleExpiredItemsBtn').addEventListener('click', function() {
                var table = document.getElementById('expiredItemsTable');
                var icon = document.getElementById('toggleIcon');
                
                if (table.style.display === 'none') {
                    table.style.display = 'block';
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    table.style.display = 'none';
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            });
            </script>";
        }

        ?>
        <!-- Trigger the modal with a button -->
        <button type="button" class="btn btn-primary btn-md mr-2" data-toggle="modal" data-target="#myModal">
            <i class="fa fa-plus"></i> Tambah Data</button>
        <a href="index.php?page=barang&stok=yes" class="btn btn-warning btn-md mr-2">
            <i class="fa fa-list"></i> Sortir Stok Kurang</a>
        <a href="index.php?page=barang&expired=yes" class="btn btn-warning btn-md mr-2">
            <i class="fa fa-list"></i> Sortir Kedaluwarsa
        </a>
        <a href="index.php?page=barang" class="btn btn-success btn-md">
            <i class="fa fa-refresh"></i> Refresh Data</a>
        <div class="clearfix"></div>
        <br />
        <!-- view barang -->
        <div class="card card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm" id="example1">
                    <thead>
                        <tr style="background:#DFF0D8;color:#333;">
                            <th>No.</th>
                            <th>ID Produk</th>
                            <th>Kategori</th>
                            <th>Nama Produk</th>
                            <th>Merk</th>
                            <th>Stok</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Satuan</th>
                            <th>Expired</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalBeli = 0;
                        $totalJual = 0;
                        $totalStok = 0;
                        
                        if ($_GET['stok'] == 'yes') {
                            $hasil = $lihat->barang_stok();
                        } elseif ($_GET['expired'] == 'yes') {
                            $hasil = $lihat->barang_expired();
                        } else {
                            $hasil = $lihat->barang();
                        }

                        $no = 1;
                        foreach ($hasil as $isi) {
                        ?>
                            <tr>
                                <td><?php echo $no; ?></td>
                                <td><?php echo $isi['id_barang']; ?></td>
                                <td><?php echo $isi['nama_kategori']; ?></td>
                                <td><?php echo $isi['nama_barang']; ?></td>
                                <td><?php echo $isi['merk']; ?></td>
                                <td>
                                    <?php if ($isi['stok'] == '0') { ?>
                                        <button class="btn btn-danger"> Habis</button>
                                    <?php } else { ?>
                                        <?php echo $isi['stok']; ?>
                                    <?php } ?>
                                </td>
                                <td>Rp.<?php echo number_format($isi['harga_beli']); ?>,-</td>
                                <td>Rp.<?php echo number_format($isi['harga_jual']); ?>,-</td>
                                <td> <?php echo $isi['satuan_barang']; ?></td>
                                <td><?php echo date('j F Y', strtotime($isi['expired'])); ?></td>
                                <td>
                                    <?php if ($isi['stok'] <=  '3') { ?>
                                        <form method="POST" action="fungsi/edit/edit.php?stok=edit">
                                            <input type="text" name="restok" class="form-control">
                                            <input type="hidden" name="id" value="<?php echo $isi['id_barang']; ?>"
                                                class="form-control">
                                            <button class="btn btn-primary btn-sm">
                                                Restok
                                            </button>
                                            <a href="fungsi/hapus/hapus.php?barang=hapus&id=<?php echo $isi['id_barang']; ?>"
                                                onclick="javascript:return confirm('Hapus Data barang ?');">
                                                <button class="btn btn-danger btn-sm">Hapus</button></a>
                                        </form>
                                    <?php } else { ?>
                                        <a href="index.php?page=barang/details&barang=<?php echo $isi['id_barang']; ?>"><button
                                                class="btn btn-primary btn-xs">Details</button></a>

                                        <a href="index.php?page=barang/edit&barang=<?php echo $isi['id_barang']; ?>"><button
                                                class="btn btn-warning btn-xs">Edit</button></a>
                                        <a href="fungsi/hapus/hapus.php?barang=hapus&id=<?php echo $isi['id_barang']; ?>"
                                            onclick="javascript:return confirm('Hapus Data barang ?');"><button
                                                class="btn btn-danger btn-xs">Hapus</button></a>
                                    <?php } ?>
                            </tr>
                        <?php
                            $no++;
                            $totalBeli += $isi['harga_beli'] * $isi['stok'];
                            $totalJual += $isi['harga_jual'] * $isi['stok'];
                            $totalStok += $isi['stok'];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5">Total </td>
                            <th><?php echo $totalStok; ?></td>
                            <th>Rp.<?php echo number_format($totalBeli); ?>,-</td>
                            <th>Rp.<?php echo number_format($totalJual); ?>,-</td>
                            <th colspan="2" style="background:#ddd"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <!-- end view barang -->
        <!-- tambah barang MODALS-->
        <!-- Modal -->

        <div id="myModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content" style=" border-radius:0px;">
                    <div class="modal-header" style="background:#285c64;color:#fff;">
                        <h5 class="modal-title"><i class="fa fa-plus"></i> Tambah Produk</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="fungsi/tambah/tambah.php?barang=tambah" method="POST">
                        <div class="modal-body">
                            <table class="table table-striped bordered">
                                <?php
                                $format = $lihat->barang_id();
                                ?>
                                <tr>
                                    <td>ID Produk</td>
                                    <td><input type="text" readonly="readonly" required value="<?php echo $format; ?>"
                                            class="form-control" name="id"></td>
                                </tr>
                                <tr>
                                    <td>Kategori</td>
                                    <td>
                                        <select name="kategori" class="form-control" required>
                                            <option value="#">Pilih Kategori</option>
                                            <?php $kat = $lihat->kategori();
                                            foreach ($kat as $isi) {     ?>
                                                <option value="<?php echo $isi['id_kategori']; ?>">
                                                    <?php echo $isi['nama_kategori']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Nama Produk</td>
                                    <td><input type="text" placeholder="Nama Produk" required class="form-control"
                                            name="nama"></td>
                                </tr>
                                <tr>
                                    <td>Merk Produk</td>
                                    <td><input type="text" placeholder="Merk Produk" required class="form-control"
                                            name="merk"></td>
                                </tr>
                                <tr>
                                    <td>Harga Beli</td>
                                    <td><input type="number" placeholder="Harga beli" required class="form-control"
                                            name="beli"></td>
                                </tr>
                                <tr>
                                    <td>Harga Jual</td>
                                    <td><input type="number" placeholder="Harga Jual" required class="form-control"
                                            name="jual"></td>
                                </tr>
                                <tr>
                                    <td>Satuan Produk</td>
                                    <td>
                                        <select name="satuan" class="form-control" required>
                                            <option value="#">Pilih Satuan</option>
                                            <option value="PCS">PCS</option>
                                            <option value="Porsi">Porsi</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Stok</td>
                                    <td><input type="number" required Placeholder="Stok" class="form-control"
                                            name="stok"></td>
                                </tr>
                                <tr>
                                    <td>Expired</td>
                                    <td><input type="date" required Placeholder="expired" class="form-control"
                                            name="expired"></td>
                                </tr>
                                <tr>
                                    <td>Tanggal Input</td>
                                    <td><input type="text" required readonly="readonly" class="form-control"
                                            value="<?php echo  date("j F Y, G:i"); ?>" name="tgl"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Insert
                                Data</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>