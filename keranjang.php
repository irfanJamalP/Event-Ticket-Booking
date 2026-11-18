<?php
include "header.php";
$customerCode = $_SESSION["kd_cs"] ?? "";

if (isset($_POST["submit1"]) && $customerCode !== "") {
    $cartId = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
    $quantity = filter_var($_POST["qty"] ?? null, FILTER_VALIDATE_INT);

    if ($cartId && $quantity && $quantity <= 99) {
        db_query_params(
            $conn,
            "UPDATE keranjang SET qty = ? WHERE id_keranjang = ? AND kode_customer = ?",
            [$quantity, $cartId, $customerCode]
        );
        echo "<script>alert('KERANJANG BERHASIL DIPERBARUI'); window.location = 'keranjang.php';</script>";
        exit();
    }

    http_response_code(400);
    echo "<div class=\"container\"><div class=\"alert alert-warning\">Jumlah tiket tidak valid.</div></div>";
} elseif (isset($_GET["del"]) && $customerCode !== "") {
    $cartId = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT);

    if ($cartId) {
        db_query_params(
            $conn,
            "DELETE FROM keranjang WHERE id_keranjang = ? AND kode_customer = ?",
            [$cartId, $customerCode]
        );
        echo "<script>alert('1 PRODUK DIHAPUS'); window.location = 'keranjang.php';</script>";
        exit();
    }
}
?>

<div class="container" style="padding-bottom: 300px;">
	<h2 style=" width: 100%; border-bottom: 4px solid #ff8680"><b>Keranjang</b></h2>
		<table class="table table-striped">
			<?php 
			if(isset($_SESSION['user']) && $customerCode !== ""){
				$kode_cs = $customerCode;
			// CEK JUMLAH KERANJANG
				$cek = db_query_params($conn, "SELECT id_keranjang FROM keranjang WHERE kode_customer = ?", [$kode_cs]);
				$jml = db_num_rows($cek);

				if($jml > 0){
					?>	
					<thead>
						<tr>
							<th scope="col">No</th>
							<th scope="col">Image</th>
							<th scope="col">Nama</th>
							<th scope="col">Harga</th>
							<th scope="col">Qty</th>
							<th scope="col">SubTotal</th>
							<th scope="col">Action</th>
						</tr>
					</thead>
					<?php 
					if(isset($_SESSION['kd_cs'])){
						$kode_cs = $_SESSION['kd_cs'];

						$result = db_query_params($conn, "SELECT k.id_keranjang AS keranjang, k.kode_produk AS kd, k.nama_produk AS nama, k.qty AS jml, p.image AS gambar, p.harga AS hrg FROM keranjang k JOIN produk p ON k.kode_produk = p.kode_produk WHERE k.kode_customer = ?", [$kode_cs]);
						$no = 1;
						$hasil = 0;
						while($row = db_fetch_assoc($result)){
				
					?>
					<tbody>
					<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
						<input type="hidden" name="id" value="<?php echo $row['keranjang']; ?>">
						<tr>
							<th scope="row"><?= $no;  ?></th>
							<td><img src="image/produk/<?= htmlspecialchars($row['gambar'], ENT_QUOTES, 'UTF-8'); ?>" width="100"></td>
							<td><?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
							<td>Rp.<?= number_format((int) $row['hrg']);  ?></td>
							<td><input type="number" name="qty" min="1" max="99" class="form-control" style="text-align: center;" value="<?= (int) $row['jml']; ?>"></td>
							<td>Rp.<?= number_format((int) $row['hrg'] * (int) $row['jml']);  ?></td>
							<td><button type="submit" name="submit1" class="btn btn-warning">Update</button> | <a href="keranjang.php?del=1&id=<?= (int) $row['keranjang']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin dihapus ?')">Delete</a></td>
						</tr>
						</form>
					<?php 
							$sub = $row['hrg'] * $row['jml'];
							$hasil += $sub;
							$no++;
						}
					}
					 ?>
					 
						<tr>
							<td colspan="7" style="text-align: right; font-weight: bold;">Grand Total = Rp.<?= number_format($hasil); ?></td>
						</tr>
						<tr>
							<td colspan="7" style="text-align: right; font-weight: bold;"><a href="index.php" class="btn btn-success">Lanjutkan Belanja</a> <a href="checkout.php?kode_cs=<?= $kode_cs; ?>" class="btn btn-primary">Checkout</a></td>
						</tr>
						<?php 
					}else{
						echo "
						<tr>
						<th scope='col'>No</th>
						<th scope='col'>Image</th>
						<th scope='col'>Nama</th>
						<th scope='col'>Harga</th>
						<th scope='col'>Qty</th>
						<th scope='col'>SubTotal</th>
						<th scope='col'>Action</th>
						</tr>
						<tr>
						<td colspan='7' class='text-center bg-warning'><h5><b>KERANJANG BELANJA ANDA KOSONG </b></h5></td>
						</tr>

						";
					}

				}else{
					echo "<tr>
					<td colspan='7' class='text-center bg-danger'><h5><b>SILAHKAN LOGIN TERLEBIH DAHULU SEBELUM BERBELANJA</b></h5></td>
					</tr>";
				}
				?>


			</tbody>
		</table>
	
</div>




<?php 
include 'footer.php';
?>