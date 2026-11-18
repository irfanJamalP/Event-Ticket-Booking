<?php
require __DIR__ . '/auth.php';
$inv = isset($_GET['inv']) && is_string($_GET['inv']) ? $_GET['inv'] : '';
if ($inv === '') {
	http_response_code(400);
	exit('Nomor invoice tidak valid.');
}

$result = db_query_params($conn, "SELECT * FROM produksi WHERE invoice = ?", [$inv]);

while($row = db_fetch_assoc($result)){
	$kodep = $row['kode_produk'];
	$t_bom = db_query_params($conn, "SELECT * FROM bom_produk WHERE kode_produk = ?", [$kodep]);

	while($row1 = db_fetch_assoc($t_bom)){
		$kodebk = $row1['kode_bk'];

		$inventory = db_query_params($conn, "SELECT * FROM inventory WHERE kode_bk = ?", [$kodebk]);
		$r_inv = db_fetch_assoc($inventory);
		if (!$r_inv) {
			http_response_code(409);
			exit('Material pesanan tidak ditemukan di inventory.');
		}

		$kebutuhan = $row1['kebutuhan'];	
		$qtyorder = $row['qty'];
		$inven = $r_inv['qty'];
		$bom = ($kebutuhan * $qtyorder);
		$hasil = $inven - $bom;

		$inventory = db_query_params($conn, "UPDATE inventory SET qty = ? WHERE kode_bk = ?", [$hasil, $kodebk]);

		if($inventory){
			db_query_params($conn, "UPDATE produksi SET terima = '1', status = '0' WHERE invoice = ?", [$inv]);

			echo "
			<script>
			alert('PESANAN BERHASIL DITERIMA,  TELAH DIKURANGKAN');
			window.location = '../produksi.php';
			</script>
			";
		}

	}

}

?>