<?php
if(!isset($_GET['id'])){
    echo "<script>window.location='index.php?page=emoney';</script>";
    exit;
}

$id = $_GET['id'];
$sql = 'SELECT * FROM emoney WHERE id = ?';
$row = $config->prepare($sql);
$row->execute(array($id));
$data = $row->fetch();
?>

<h4>Edit E-Money</h4>
<br>
<?php if (isset($_GET['success-edit'])) { ?>
    <div class="alert alert-success">
        <p>Edit Data Berhasil!</p>
    </div>
<?php } ?>

<div class="card">
    <div class="card-body">
        <form action="fungsi/edit/edit.php?emoney=edit" method="POST">
            <input type="hidden" name="id" value="<?php echo $data['id']; ?>">

            <div class="form-group">
                <label>NIM</label>
                <input type="text" name="nim" value="<?php echo $data['nim']; ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="nama" value="<?php echo $data['nama']; ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Saldo</label>
                <input type="number" name="saldo" value="<?php echo $data['saldo']; ?>" class="form-control" step="0.01" required>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fa fa-edit"></i> Update</button>
            <a href="index.php?page=emoney" class="btn btn-danger">Kembali</a>
        </form>
    </div>
</div>