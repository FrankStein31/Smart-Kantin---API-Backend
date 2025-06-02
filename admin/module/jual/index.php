<!-- <div style="background-color: #FE9900; color: white; padding: 10px; border-radius: 5px;">
    &#9432; <b>Sistem Kasir Kedai Kopi Tanah Jawa - jika ada kendala silahkan langsung hubungi 08883866931 (Frankie)</b>
</div> -->
<br>
<?php
$id = $_SESSION['admin']['id_member'];
$hasil = $lihat->member_edit($id);
?>
<h4>Keranjang Penjualan</h4>
<br>
<?php if (isset($_GET['success'])) { ?>
	<div class="alert alert-success">
		<p>Data di Masukan Ke Keranjang !</p>
	</div>
<?php } ?>
<?php if (isset($_GET['remove'])) { ?>
	<div class="alert alert-danger">
		<p>Hapus Data Berhasil !</p>
	</div>
<?php } ?>
<div class="row">
	<div class="col-sm-4">
		<div class="card card-primary mb-3">
			<div class="card-header bg-primary text-white">
				<h5><i class="fa fa-search"></i> Cari Produk</h5>
			</div>
			<div class="card-body">
				<input type="text" id="cari" class="form-control" name="cari" placeholder="Masukan : Kode / Nama Barang  [ENTER]" autocomplete="off">
			</div>
		</div>
	</div>
	<div class="col-sm-8">
		<div class="card card-primary mb-3">
			<div class="card-header bg-primary text-white">
				<h5><i class="fa fa-list"></i> Hasil Pencarian</h5>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<div id="hasil_cari"></div>
					<div id="tunggu"></div>
				</div>
			</div>
		</div>
	</div>


	<div class="col-sm-12">
		<div class="card card-primary">
			<div class="card-header bg-primary text-white">
				<h5><i class="fa fa-shopping-cart"></i> KASIR
					<!-- <a class="btn btn-danger float-right"
						onclick="javascript:return confirm('Apakah anda ingin reset keranjang ?');" href="fungsi/hapus/hapus.php?penjualan=jual">
						<b>RESET KERANJANG</b></a> -->
				</h5>
			</div>
			<div class="card-body">
				<div id="keranjang" class="table-responsive">
					<table class="table table-bordered">
						<tr>
							<td><b>Tanggal</b></td>
							<td><input type="text" readonly="readonly" class="form-control" value="<?php echo date("j F Y, G:i"); ?>" name="tgl"></td>
						</tr>
						<tr>
							<td><b>Siswa (Opsional)</b></td>
							<td>
								<select name="nim" class="form-control" onchange="updateNimPenjualan(this.value)">
									<option value="">- Pilih Siswa -</option>
									<?php
									$sql = 'SELECT nim, nama FROM emoney ORDER BY nama ASC';
									$stmt = $config->prepare($sql);
									$stmt->execute();
									$mahasiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
									
									// Ambil NIM dari tabel penjualan
									$sql_nim = 'SELECT DISTINCT nim FROM penjualan WHERE nim IS NOT NULL LIMIT 1';
									$stmt_nim = $config->prepare($sql_nim);
									$stmt_nim->execute();
									$selected_nim = $stmt_nim->fetch(PDO::FETCH_COLUMN);
									
									foreach($mahasiswa as $mhs):
										$selected = ($mhs['nim'] == $selected_nim) ? 'selected' : '';
									?>
										<option value="<?php echo $mhs['nim']; ?>" <?php echo $selected; ?>><?php echo $mhs['nim'] . ' - ' . $mhs['nama']; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>
					<table class="table table-bordered w-100" id="example1">
						<thead>
							<tr>
								<td> No</td>
								<td> Nama Produk</td>
								<td style="width:10%;"> Jumlah</td>
								<td style="width:20%;"> Total</td>
								<td> Kasir</td>
								<td> Siswa</td>
								<td> Aksi</td>
							</tr>
						</thead>
						<tbody>
							<?php $total_bayar = 0;
							$no = 1;
							$hasil_penjualan = $lihat->penjualan(); ?>
							<?php foreach ($hasil_penjualan  as $isi) { ?>
								<tr>
									<td><?php echo $no; ?></td>
									<td><?php echo $isi['nama_barang']; ?></td>
									<td>
										<!-- aksi ke table penjualan -->
										<form method="POST" action="fungsi/edit/edit.php?jual=jual">
											<input type="number" name="jumlah" value="<?php echo $isi['jumlah']; ?>" class="form-control">
											<input type="hidden" name="id" value="<?php echo $isi['id_penjualan']; ?>" class="form-control">
											<input type="hidden" name="id_barang" value="<?php echo $isi['id_barang']; ?>" class="form-control">
									</td>
									<td>Rp.<?php echo number_format($isi['total']); ?>,-</td>
									<td><?php echo $isi['nm_member']; ?></td>
									<td>
										<?php 
										if(!empty($isi['nim'])) {
											$sql = 'SELECT nama FROM emoney WHERE nim = ?';
											$stmt = $config->prepare($sql);
											$stmt->execute(array($isi['nim']));
											$siswa = $stmt->fetch();
											echo $siswa['nama'];
										} else {
											echo '-';
										}
										?>
									</td>
									<td>
										<button type="submit" class="btn btn-warning">Update</button>
										</form>
										<!-- aksi ke table penjualan -->
										<a href="fungsi/hapus/hapus.php?jual=jual&id=<?php echo $isi['id_penjualan']; ?>&brg=<?php echo $isi['id_barang']; ?>
											&jml=<?php echo $isi['jumlah']; ?>" class="btn btn-danger"><i class="fa fa-times"></i>
										</a>
									</td>
								</tr>
							<?php $no++;
								$total_bayar += $isi['total'];
							} ?>
						</tbody>
					</table>
					<br />
					<?php $hasil = $lihat->jumlah(); ?>
					<div id="kasirnya">
						<table class="table table-stripped">
							<?php
							// proses bayar dan ke nota
							if (!empty($_GET['nota'] == 'yes')) {
								$total = $_POST['total'];
								$bayar = $_POST['bayar'];
								if (!empty($bayar)) {
									$hitung = $bayar - $total;
									if ($bayar >= $total) {
										$id_barang = $_POST['id_barang'];
										$id_member = $_POST['id_member'];
										$nim = $_POST['nim'];
										$jumlah = $_POST['jumlah'];
										$total = $_POST['total1'];
										$tgl_input = $_POST['tgl_input'];
										$periode = $_POST['periode'];
										$jumlah_dipilih = count($id_barang);

										for ($x = 0; $x < $jumlah_dipilih; $x++) {
											$d = array($id_barang[$x], $id_member[$x], $nim[$x], $jumlah[$x], $total[$x], $tgl_input[$x], $periode[$x]);
											$sql = "INSERT INTO nota (id_barang,id_member,nim,jumlah,total,tanggal_input,periode) VALUES(?,?,?,?,?,?,?)";
											$row = $config->prepare($sql);
											$row->execute($d);

											// Jika ada NIM, masukkan ke history
											if (!empty($nim[$x])) {
												$date = date('Y-m-d');
												$time = date('H:i:s');
												$history_data = array(
													'nim' => $nim[$x],
													'totalharga' => $total[$x],
													'id_barang' => $id_barang[$x],
													'date' => $date,
													'time' => $time
												);
												
												$sql_history = 'INSERT INTO history (nim,totalharga,id_barang,date,time) VALUES (:nim,:totalharga,:id_barang,:date,:time)';
												$stmt_history = $config->prepare($sql_history);
												$stmt_history->execute($history_data);
											}

											// ubah stok barang
											$sql_barang = "SELECT * FROM barang WHERE id_barang = ?";
											$row_barang = $config->prepare($sql_barang);
											$row_barang->execute(array($id_barang[$x]));
											$hsl = $row_barang->fetch();

											$stok = $hsl['stok'];
											$idb  = $hsl['id_barang'];

											$total_stok = $stok - $jumlah[$x];
											// echo $total_stok;
											$sql_stok = "UPDATE barang SET stok = ? WHERE id_barang = ?";
											$row_stok = $config->prepare($sql_stok);
											$row_stok->execute(array($total_stok, $idb));
										}
										echo '<script>
										alert("Belanjaan Berhasil Di Bayar !");
										document.getElementById("kasirnya").reset(); // Reset form setelah pembayaran berhasil
										</script>';
									} else {
										echo '<script>alert("Uang Kurang ! Rp.' . $hitung . '");</script>';
									}
								}
							}
							?>
							<!-- aksi ke table nota -->
							<form method="POST" action="index.php?page=jual&nota=yes#kasirnya">
								<?php foreach ($hasil_penjualan as $isi) {; ?>
									<input type="hidden" name="id_barang[]" value="<?php echo $isi['id_barang']; ?>">
									<input type="hidden" name="id_member[]" value="<?php echo $isi['id_member']; ?>">
									<input type="hidden" name="nim[]" value="<?php echo $isi['nim']; ?>">
									<input type="hidden" name="jumlah[]" value="<?php echo $isi['jumlah']; ?>">
									<input type="hidden" name="total1[]" value="<?php echo $isi['total']; ?>">
									<input type="hidden" name="tgl_input[]" value="<?php echo $isi['tanggal_input']; ?>">
									<input type="hidden" name="periode[]" value="<?php echo date('m-Y'); ?>">
								<?php $no++;
								} ?>
								<tr>
									<td>Total Semua </td>
									<td><input type="text" class="form-control" name="total" value="<?php echo $total_bayar; ?>"></td>
									<td>
										<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#bayarModal">
											<i class="fa fa-shopping-cart"></i> Bayar
										</button>
										<button type="button" class="btn btn-info" data-toggle="modal" data-target="#myModal">
											<i class="fa fa-plus"></i> Scan Qr
										</button>
										<?php if (!empty($_GET['nota'] == 'yes')) { ?>
											<a class="btn btn-danger" href="fungsi/hapus/hapus.php?penjualan=jual">
												<b>RESET</b></a>
									</td><?php } ?></td>
								</tr>
							</form>
							<!-- aksi ke table nota -->
							<tr>
								<!-- <td>Kembali</td>
								<td><input type="text" class="form-control" value="<?php echo $hitung; ?>"></td> -->
								<td></td>
								<!-- <td>
								<button class="btn btn-secondary" onclick="handleButtonClick()">
									<i class="fa fa-print"></i> Print Untuk Bukti Pembayaran
								</button>
								</td> -->
							</tr>
						</table>
						<br />
						<br />
					</div>
				</div>
			</div>
		</div>
	</div>
										</div>

	<div id="bayarModal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header bg-primary text-white">
					<h5 class="modal-title"><i class="fa fa-money"></i> Pembayaran</h5>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body">
					<form id="formBayar">
						<div class="form-group">
							<label for="totalModal">Total Bayar</label>
							<input type="text" id="totalModal" class="form-control" value="Rp. <?php echo number_format($total_bayar); ?>" readonly>
						</div>
						<div class="form-group">
							<label for="jumlahBayar">Jumlah Bayar</label>
							<input type="text" id="jumlahBayar" class="form-control" onkeypress="hitungOtomatis(event)" placeholder="Masukkan jumlah bayar">
						</div>
						<div class="form-group">
							<label for="kembalian">Kembalian</label>
							<input type="text" id="kembalian" class="form-control" placeholder="Kembalian" readonly>
						</div>
						<button type="button" class="btn btn-success" onclick="prosesBayar()">Proses Pembayaran</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<div id="myModal" class="modal fade" role="dialog">
		<div class="modal-dialog" style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
			<!-- Modal content-->
			<<div class="modal-content" style="border-radius: 0px;">
				<div class="modal-header" style="background: #285c64; color: #fff;">
					<h5 class="modal-title"><i class="fa fa-qrcode"></i> Scan QR</h5>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body text-center">
					<?php
					// Include library QR Code
					include 'libs/phpqrcode.php';

					// Data untuk QR Code: Total Semua
					$totalSemua = $total_bayar; // Ambil dari PHP
					$qrData = "Total Harga: Rp. " . number_format($totalSemua);

					// Path untuk menyimpan gambar QR Code
					$filePath = 'temp/qr_total_harga.png';
					QRcode::png($qrData, $filePath, QR_ECLEVEL_L, 6);

					echo "<img src='$filePath' alt='QR Code' class='img-fluid' />";
					?>
					<p class="mt-3"><b>Tanggal:</b> <?php echo date("j F Y, G:i"); ?></p>
					<p class="mt-3"><b>Total Harga:</b> Rp. <?php echo number_format($total_bayar); ?></p>
				</div>
				<div class="modal-footer">
					<form method="POST" action="fungsi/payment/process_qr_payment.php">
						<?php foreach ($hasil_penjualan as $isi) { ?>
							<input type="hidden" name="id_barang[]" value="<?php echo $isi['id_barang']; ?>">
							<input type="hidden" name="id_member[]" value="<?php echo $isi['id_member']; ?>">
							<input type="hidden" name="nim[]" value="<?php echo $isi['nim']; ?>">
							<input type="hidden" name="jumlah[]" value="<?php echo $isi['jumlah']; ?>">
							<input type="hidden" name="total1[]" value="<?php echo $isi['total']; ?>">
							<input type="hidden" name="tgl_input[]" value="<?php echo $isi['tanggal_input']; ?>">
							<input type="hidden" name="periode[]" value="<?php echo date('m-Y'); ?>">
						<?php } ?>
						<input type="hidden" name="total" value="<?php echo $total_bayar; ?>">
						<input type="hidden" name="bayar" value="<?php echo $total_bayar; ?>">
						<button type="submit" class="btn btn-success"><b>Selesai</b></button>
					</form>
				</div>
			</div>
			
		</div>
	</div>

<script>
	function prosesBayar() {
		// Get payment values
		var totalBayar = <?php echo $total_bayar; ?>;
		var jumlahBayar = parseFloat(document.getElementById('jumlahBayar').value);
		var selectedNim = document.querySelector('select[name="nim"]').value;

		if (isNaN(jumlahBayar) || jumlahBayar < totalBayar) {
			alert("Jumlah bayar tidak cukup atau tidak valid!");
			document.getElementById('kembalian').value = '';
			return;
		}

		var kembalian = jumlahBayar - totalBayar;
		document.getElementById('kembalian').value = "Rp. " + kembalian.toLocaleString();

		// Collect all items from the sales table
		var items = [];
		<?php foreach ($hasil_penjualan as $isi) { ?>
			items.push({
				id_barang: '<?php echo $isi['id_barang']; ?>',
				id_member: '<?php echo $isi['id_member']; ?>',
				nim: selectedNim,
				jumlah: '<?php echo $isi['jumlah']; ?>',
				total: '<?php echo $isi['total']; ?>',
				tgl_input: '<?php echo date('d F Y, H:i'); ?>', 
				periode: '<?php echo date('m-Y'); ?>'
			});
		<?php } ?>

		// Create payment data object
		var paymentData = {
			items: items,
			total: totalBayar,
			bayar: jumlahBayar,
			kembalian: kembalian,
			nim: selectedNim
		};

		// Send payment data to server
		fetch('fungsi/payment/process_payment.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(paymentData)
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				// Open print receipt in new tab
				var printUrl = 'print.php?nm_member=<?php echo urlencode($_SESSION['admin']['nm_member']); ?>&bayar=' + 
							encodeURIComponent(jumlahBayar) + '&kembali=' + encodeURIComponent(kembalian);
				window.open(printUrl, '_blank');

				// Reset cart
				window.location.href = 'fungsi/hapus/hapus.php?penjualan=jual';
			} else {
				alert('Error: ' + data.message);
			}
		})
		.catch(error => {
			console.error('Error:', error);
			alert('Terjadi kesalahan saat memproses pembayaran');
		});
	}

    // Fungsi untuk menangani klik tombol print
    function handleButtonClick() {
        // URL untuk cetak bukti pembayaran
        var printUrl = 'print.php?nm_member=<?php echo urlencode($_SESSION['admin']['nm_member']); ?>&bayar=<?php echo urlencode($bayar); ?>&kembali=<?php echo urlencode($hitung); ?>';
        
        // URL untuk hapus penjualan
        var hapusUrl = 'fungsi/hapus/hapus.php?penjualan=jual';
        
        // Membuka print.php di tab baru
        window.open(printUrl, '_blank');
        
        // Mengarahkan halaman saat ini ke hapus.php
        window.location.href = hapusUrl;
    }

    // AJAX call untuk autocomplete
    $(document).ready(function() {
        $("#cari").change(function() {
            $.ajax({
                type: "POST",
                url: "fungsi/edit/edit.php?cari_barang=yes",
                data: 'keyword=' + $(this).val(),
                beforeSend: function() {
                    $("#hasil_cari").hide();
                    $("#tunggu").html('<p style="color:green"><blink>tunggu sebentar</blink></p>');
                },
                success: function(html) {
                    $("#tunggu").html('');
                    $("#hasil_cari").show();
                    $("#hasil_cari").html(html);
                }
            });
        });
    });

function updateNimPenjualan(nim) {
    fetch('fungsi/edit/edit.php?update_nim=yes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'nim=' + encodeURIComponent(nim)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh halaman untuk menampilkan perubahan
            window.location.reload();
        } else {
            alert('Gagal mengupdate NIM: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate NIM');
    });
}
</script>
