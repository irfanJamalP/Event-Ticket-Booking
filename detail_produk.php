<?php
include "header.php";
$kode = isset($_GET["produk"]) && is_string($_GET["produk"])
    ? $_GET["produk"]
    : "";
$statement = db_prepare(
    $conn,
    "SELECT * FROM produk WHERE kode_produk = ?"
);
db_stmt_bind_param($statement, "s", $kode);
db_stmt_execute($statement);
$result = db_stmt_get_result($statement);
$row = db_fetch_assoc($result);
db_stmt_close($statement);

if (!$row) {
    http_response_code(404);
    echo '<div class="container"><div class="alert alert-warning">Acara tidak ditemukan.</div></div>';
    include "footer.php";
    exit();
}

?>
<div class="container">
	<h2 style=" width: 100%; border-bottom: 4px solid #ff8680"><b>Detail produk</b></h2>

	<div class="row">
		<div class="col-md-4">
			<div class="thumbnail">
				<img src="image/produk/<?= htmlspecialchars(
        $row["image"],
        ENT_QUOTES,
        "UTF-8"
    ) ?>" width="400">
			</div>
		</div>

		<div class="col-md-8">
			<form action="proses/add.php" method="GET">
				<input type="hidden" name="kd_cs" value="<?= htmlspecialchars(
        $kode_cs ?? "",
        ENT_QUOTES,
        "UTF-8"
    ) ?>">
				<input type="hidden" name="produk" value="<?= htmlspecialchars(
        $kode,
        ENT_QUOTES,
        "UTF-8"
    ) ?>">
				<input type="hidden" name="hal"  value="2">
				<table class="table table-striped">
					<tbody>
						<tr>
							<td><b>Nama</b></td>
							<td><?= htmlspecialchars(
            $row["nama"],
            ENT_QUOTES,
            "UTF-8"
        ) ?></td>
						</tr>
						<tr>
							<td><b>Harga</b></td>
							<td>Rp.<?= number_format($row['harga']); ?></td>
						</tr>
						<tr>
							<td><b>Deskripsi</b></td>
							<td><?= nl2br(
            htmlspecialchars($row["deskripsi"], ENT_QUOTES, "UTF-8")
        ) ?></td>
						</tr>
						<tr>
							<td><b>Jumlah</b></td>
							<td><input class="form-control" type="number" min="1" name="jml" value="1" style="width: 155px;"></td>
						</tr>
					</tbody>
				</table>
				<?php 
				if(isset($_SESSION['user'])){
					?>
					<button type="submit" class="btn btn-success"><i class="glyphicon glyphicon-shopping-cart"></i> Tambahkan ke Keranjang</button>
					<?php 
				}else{

					?>
					<a href="keranjang.php" class="btn btn-success"><i class="glyphicon glyphicon-shopping-cart"></i> Tambahkan ke Keranjang</a>
					<?php 
				}
				?>
				<a href="index.php" class="btn btn-warning"> Kembali Belanja</a>
			</div>
		</form>
	</div>


</div>	
<br>
<br>

<?php 
include 'footer.php';
?>