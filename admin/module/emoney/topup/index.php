<!-- Add Top-up Button -->
<button type="button" class="btn btn-success btn-md mr-2" data-toggle="modal" data-target="#modalTopup">
    <i class="fa fa-plus"></i> Top Up Saldo
</button>

<!-- Modal Top-up -->
<div id="modalTopup" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:0px;">
            <div class="modal-header" style="background:#285c64;color:#fff;">
                <h5 class="modal-title"><i class="fa fa-plus"></i> Top Up E-Money</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="fungsi/tambah/tambah.php?topup=tambah" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <table class="table table-striped bordered">
                        <tr>
                            <td>NIM</td>
                            <td>
                                <select name="nim" class="form-control" required>
                                    <option value="">- Pilih Mahasiswa -</option>
                                    <?php
                                    $sql = 'SELECT nim, nama FROM emoney ORDER BY nama ASC';
                                    $stmt = $config->prepare($sql);
                                    $stmt->execute();
                                    $mahasiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach($mahasiswa as $mhs):
                                    ?>
                                        <option value="<?php echo $mhs['nim']; ?>"><?php echo $mhs['nim'] . ' - ' . $mhs['nama']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Nominal Top Up</td>
                            <td><input type="number" name="nominal" class="form-control" step="0.01" required></td>
                        </tr>
                        <tr>
                            <td>Bukti Transfer</td>
                            <td><input type="file" name="fotobukti" class="form-control" required accept="image/*"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Ajukan Top Up</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php
// module/emoney/topup/index.php
?>

<br>
<br>

<h4>Daftar Top Up E-Money</h4>

<?php if (isset($_GET['success'])) { ?>
    <div class="alert alert-success">
        <p>Pengajuan Top Up Berhasil!</p>
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
                    <th>Nominal</th>
                    <th>Bukti Transfer</th>
                    <th>Status</th>
                    <!-- <th>Aksi</th> -->
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = 'SELECT * FROM validasi ORDER BY id_validasi DESC';
                $stmt = $config->prepare($sql);
                $stmt->execute();
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $no = 1;

                foreach ($data as $row):
                ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $row['nim']; ?></td>
                        <td><?php echo $row['nama']; ?></td>
                        <td>Rp <?php echo number_format($row['nominal'], 2, ',', '.'); ?></td>
                        <td>
                            <?php if($row['fotobukti']): ?>
                                <img src="assets/img/bukti_transfer/<?php echo $row['fotobukti']; ?>" 
                                     alt="Bukti Transfer" style="width:50px; height:50px; object-fit:cover;">
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row['valid'] == 0): ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php elseif($row['valid'] == 2): ?>
                                <span class="badge badge-danger">Ditolak</span>
                            <?php else: ?>
                                <span class="badge badge-success">Tervalidasi</span>
                            <?php endif; ?>
                        </td>
                        <!-- <td>
                            <?php if($row['valid'] == 0): ?>
                                <button class="btn btn-success btn-sm" 
                                        onclick="validateTopup(<?php echo $row['id_validasi']; ?>)">
                                    <i class="fa fa-check"></i> Validate
                                </button>
                            <?php endif; ?>
                        </td> -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function validateTopup(id) {
    if(confirm('Validasi top up ini?')) {
        window.location.href = 'fungsi/validasi/validasi.php?id=' + id;
    }
}
</script>