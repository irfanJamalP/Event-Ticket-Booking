<?php
include "header.php";

if (empty($_SESSION["kd_cs"])) { ?>
	<div class="container">
		<div class="alert alert-warning">Silakan login terlebih dahulu untuk melanjutkan checkout.</div>
		<a class="btn btn-primary" href="user_login.php">Login</a>
	</div>
	<?php include "footer.php";
    exit();
}

$kodeCustomer = $_SESSION["kd_cs"];
$customerStatement = db_prepare(
    $conn,
    "SELECT nama FROM customer WHERE kode_customer = ?"
);
db_stmt_bind_param($customerStatement, "s", $kodeCustomer);
db_stmt_execute($customerStatement);
$customerResult = db_stmt_get_result($customerStatement);
$customer = db_fetch_assoc($customerResult);
db_stmt_close($customerStatement);

if (!$customer) { ?>
	<div class="container">
		<div class="alert alert-warning">Akun pelanggan tidak ditemukan. Silakan login kembali.</div>
		<a class="btn btn-primary" href="user_login.php">Login</a>
	</div>
	<?php include "footer.php";
    exit();
}

$cartStatement = db_prepare(
    $conn,
    "SELECT nama_produk, harga, qty FROM keranjang WHERE kode_customer = ?"
);
db_stmt_bind_param($cartStatement, "s", $kodeCustomer);
db_stmt_execute($cartStatement);
$cartResult = db_stmt_get_result($cartStatement);
$paymentMethods = db_query(
    $conn,
    "SELECT DISTINCT type FROM metode_pembayaran WHERE type IN ('transfer', 'qris') ORDER BY type"
);
$cartTotal = 0;
?>

<div class="container" style="padding-bottom: 200px">
	<h2 style="width: 100%; border-bottom: 4px solid #ff8680"><b>Checkout</b></h2>
	<?php if (db_num_rows($cartResult) === 0) { ?>
		<div class="alert alert-warning">Keranjang Anda masih kosong.</div>
		<a class="btn btn-success" href="produk.php">Lihat acara</a>
	<?php } else { ?>
		<form action="proses/order.php" method="POST">
			<div class="row">
				<div class="col-md-7">
					<h4>Daftar Pesanan</h4>
					<table class="table table-striped">
						<thead>
							<tr>
								<th>No</th>
								<th>Nama acara</th>
								<th>Harga</th>
								<th>Jumlah</th>
								<th>Subtotal</th>
							</tr>
						</thead>
						<tbody>
							<?php
       $number = 1;
       while ($item = db_fetch_assoc($cartResult)) {
           $subtotal = (int) $item["harga"] * (int) $item["qty"];
           $cartTotal += $subtotal;
           ?>
								<tr>
									<td><?= $number++ ?></td>
									<td><?= htmlspecialchars(
             $item["nama_produk"],
             ENT_QUOTES,
             "UTF-8"
         ) ?></td>
									<td>Rp<?= number_format((int) $item["harga"], 0, ",", ".") ?></td>
									<td><?= (int) $item["qty"] ?></td>
									<td>Rp<?= number_format($subtotal, 0, ",", ".") ?></td>
								</tr>
							<?php
       }
       db_stmt_close($cartStatement);
       ?>
							<tr>
								<th colspan="4" class="text-right">Total</th>
								<th>Rp<?= number_format($cartTotal, 0, ",", ".") ?></th>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<h4>Informasi pemesan dan pembayaran</h4>
			<div class="form-group">
				<label for="customer-name">Nama</label>
				<input type="text" class="form-control" id="customer-name" value="<?= htmlspecialchars(
        $customer["nama"],
        ENT_QUOTES,
        "UTF-8"
    ) ?>" readonly>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="province">Provinsi</label>
						<input type="text" class="form-control" id="province" name="prov" maxlength="200" required>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="city">Kota</label>
						<input type="text" class="form-control" id="city" name="kota" maxlength="200" required>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="address">Alamat</label>
						<input type="text" class="form-control" id="address" name="almt" maxlength="200" required>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="postal-code">Kode pos</label>
						<input type="text" class="form-control" id="postal-code" name="kopos" maxlength="20" required>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="payment-type">Metode pembayaran</label>
						<select id="payment-type" name="type" class="form-control" required>
							<option value="" disabled selected>Pilih metode pembayaran</option>
							<?php while ($method = db_fetch_assoc($paymentMethods)) {
           $type = $method["type"];
           $label =
               $type === "qris"
                   ? "QRIS"
                   : ($type === "transfer"
                       ? "Transfer bank"
                       : ucfirst($type)); ?>
								<option value="<?= htmlspecialchars(
               $type,
               ENT_QUOTES,
               "UTF-8"
           ) ?>"><?= htmlspecialchars(
               $label,
               ENT_QUOTES,
               "UTF-8"
           ) ?></option>
							<?php
       } ?>
						</select>
					</div>
				</div>
			</div>
			<button type="submit" class="btn btn-success">Buat pesanan</button>
			<a href="keranjang.php" class="btn btn-default">Kembali ke keranjang</a>
		</form>
	<?php } ?>
</div>

<?php include "footer.php"; ?>
