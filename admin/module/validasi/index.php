<h4>Validasi</h4>
<br />
<?php if (isset($_GET['success'])) { ?>
    <div class="alert alert-success">
        <p>Data berhasil divalidasi!</p>
    </div>
<?php } ?>
<?php if (isset($_GET['rejected'])) { ?>
    <div class="alert alert-danger">
        <p>Data ditolak!</p>
    </div>
<?php } ?>

<div class="card card-body">
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm" id="example1">
            <thead>
                <tr style="background:#DFF0D8;color:#333;">
                    <th>No.</th>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Saldo</th>
                    <th>Status Validasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = 'SELECT * FROM validasi ORDER BY id_validasi DESC';
                $stmt = $config->prepare($sql);
                $stmt->execute();
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $id = 1;

                foreach ($data as $isi):
                    // Menentukan status validasi
                    $status = '';
                    if($isi['valid'] == 0) {
                        $status = '<span class="badge badge-warning">Pending</span>';
                    } else if($isi['valid'] == 1) {
                        $status = '<span class="badge badge-success">Tervalidasi</span>';
                    } else if($isi['valid'] == 2) {
                        $status = '<span class="badge badge-danger">Ditolak</span>';
                    }
                ?>
                    <tr>
                        <td><?php echo $id++; ?></td>
                        <td><?php echo $isi['nim']; ?></td>
                        <td><?php echo $isi['nama']; ?></td>
                        <td><?php echo number_format($isi['nominal'], 2, ',', '.'); ?></td>
                        <td><?php echo $status; ?></td>
                        <td>
                            <a href="index.php?page=validasi/detail&id=<?php echo $isi['id_validasi']; ?>" 
                               class="btn btn-info btn-sm">
                                <i class="fa fa-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>