<?php
// Pastikan ID validasi ada
if(!isset($_GET['id'])) {
    echo "<script>window.location='index.php?page=validasi';</script>";
    exit;
}

// Ambil data validasi
$id = $_GET['id'];
$sql = 'SELECT * FROM validasi WHERE id_validasi = ?';
$stmt = $config->prepare($sql);
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika data tidak ditemukan
if(!$data) {
    echo "<script>window.location='index.php?page=validasi';</script>";
    exit;
}
?>

<h4>Detail Validasi</h4>
<br />

<div class="card">
    <div class="card-header">
        <a href="index.php?page=validasi" class="btn btn-warning btn-sm">
            <i class="fa fa-chevron-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Informasi Detail -->
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="200">NIM</th>
                        <td><?php echo $data['nim']; ?></td>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <td><?php echo $data['nama']; ?></td>
                    </tr>
                    <tr>
                        <th>Nominal</th>
                        <td>Rp <?php echo number_format($data['nominal'], 2, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <th>Status Validasi</th>
                        <td>
                            <?php 
                            if($data['valid'] == 0) {
                                echo '<span class="badge badge-warning">Pending</span>';
                            } else if($data['valid'] == 1) {
                                echo '<span class="badge badge-success">Tervalidasi</span>';
                            } else {
                                echo '<span class="badge badge-danger">Ditolak</span>';
                            }
                            ?>
                        </td>
                    </tr>
                </table>

                <?php if($data['valid'] == 0): // Tampilkan tombol aksi hanya jika status masih pending ?>
                <div class="mt-3">
                    <form action="fungsi/update/update.php?validasi=update" method="POST" 
                          onsubmit="return confirm('Yakin ingin memproses data ini?')">
                        <input type="hidden" name="id_validasi" value="<?php echo $data['id_validasi']; ?>">
                        <input type="hidden" name="nim" value="<?php echo $data['nim']; ?>">
                        <input type="hidden" name="nominal" value="<?php echo $data['nominal']; ?>">
                        
                        <button type="submit" name="status" value="1" class="btn btn-success mr-2">
                            <i class="fa fa-check"></i> Validasi
                        </button>
                        <button type="submit" name="status" value="2" class="btn btn-danger">
                            <i class="fa fa-times"></i> Tolak
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <!-- Foto Bukti -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Foto Bukti Transfer</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if($data['fotobukti']): ?>
                            <img src="assets/img/bukti_transfer/<?php echo $data['fotobukti']; ?>" 
                                 alt="Bukti Transfer" 
                                 class="img-fluid" 
                                 style="max-height: 400px;">
                        <?php else: ?>
                            <p class="text-muted">Tidak ada foto bukti</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>