<?php 
require __DIR__ . '/auth.php';
$kode = $_POST['kd_material'];
$nama = $_POST['nama'];
$stok = $_POST['stok'];
$satuan = $_POST['satuan'];
$harga = $_POST['harga'];
$tanggal = date("y-m-d");

$result = db_query_params(
	$conn,
	"UPDATE inventory SET kode_bk = ?, nama = ?, qty = ?, satuan = ?, harga = ?, tanggal = ? WHERE kode_bk = ?",
	[$kode, $nama, $stok, $satuan, $harga, $tanggal, $kode]
);

if($result){
	echo "
	<script>
	alert('DATA BERHASIL DIUPDATE');
	window.location = '../inventory.php';
	</script>
	";
}

?>