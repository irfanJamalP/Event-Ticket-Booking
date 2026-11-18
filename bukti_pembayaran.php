<?php
include "header.php";

$invoice = isset($_GET["invoice"]) && is_string($_GET["invoice"])
    ? $_GET["invoice"]
    : "";
$type = isset($_GET["type"]) && is_string($_GET["type"])
    ? $_GET["type"]
    : "";
$customerCode = $_SESSION["kd_cs"] ?? "";

if ($invoice === "" || $customerCode === "" || !in_array($type, ["transfer", "qris"], true)) {
    http_response_code(400);
    echo '<div class="container"><div class="alert alert-warning">Permintaan pembayaran tidak valid.</div></div>';
    include "footer.php";
    exit();
}

$order = db_query_params(
    $conn,
    "SELECT 1 FROM produksi WHERE invoice = ? AND kode_customer = ? LIMIT 1",
    [$invoice, $customerCode]
);

if (db_num_rows($order) === 0) {
    http_response_code(404);
    echo '<div class="container"><div class="alert alert-warning">Pesanan tidak ditemukan.</div></div>';
    include "footer.php";
    exit();
}

$paymentMethods = db_query_params(
    $conn,
    "SELECT id, data FROM metode_pembayaran WHERE type = ? ORDER BY id",
    [$type]
);
$methods = [];
while ($method = db_fetch_assoc($paymentMethods)) {
    $methods[] = $method;
}

if (count($methods) === 0) {
    http_response_code(503);
    echo '<div class="container"><div class="alert alert-warning">Metode pembayaran ini belum tersedia.</div></div>';
    include "footer.php";
    exit();
}
?>

<div class="container" style="padding-bottom: 300px;">
	<h2 class="bg-warning text-center" style="padding: 10px;">Kirim Bukti Pembayaran</h2>
	<h4 class="text-center" style="font-weight: bold;">Terima kasih telah memesan tiket.</h4>
	<h5 class="text-center">Silakan lakukan pembayaran dan unggah bukti pembayaran.</h5>
	<div style="width: 100%;">
		<div style="width: 400px; margin-left: auto; margin-right: auto;">
			<div class="card">
				<div class="card-body">
					<form action="proses/upload_bukti_pembayaran.php" method="post" enctype="multipart/form-data">
						<input type="hidden" name="invoice" value="<?= htmlspecialchars($invoice, ENT_QUOTES, "UTF-8") ?>">
						<input type="hidden" name="type" value="<?= htmlspecialchars($type, ENT_QUOTES, "UTF-8") ?>">
						<?php if ($type === "qris") { ?>
							<div class="form-group mt-3 text-center">
								<img src="image/qris-toko.png" alt="QRIS" style="width: 200px; height: auto;">
								<input type="hidden" name="metode_pembayaran_id" value="<?= (int) $methods[0]["id"] ?>">
							</div>
						<?php } else { ?>
							<div class="form-group mt-3">
								<label for="payment-method">Bank tujuan</label>
								<select id="payment-method" class="form-control" name="metode_pembayaran_id" required>
									<?php foreach ($methods as $method) { ?>
										<option value="<?= (int) $method["id"] ?>"><?= htmlspecialchars($method["data"], ENT_QUOTES, "UTF-8") ?></option>
									<?php } ?>
								</select>
							</div>
						<?php } ?>
						<div class="form-group mt-3">
							<label for="payment-proof">Bukti pembayaran (JPG/PNG, maksimal 5 MB)</label>
							<input type="file" id="payment-proof" class="form-control" name="payment_proof" accept=".jpg,.jpeg,.png" required>
						</div>
						<div class="form-group mt-3">
							<button type="submit" class="btn btn-primary" style="width: 100%;">Unggah bukti pembayaran</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include "footer.php"; ?>
