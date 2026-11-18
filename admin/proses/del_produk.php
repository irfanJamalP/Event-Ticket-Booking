<?php 
require __DIR__ . '/auth.php';

$kode = isset($_GET['kode']) && is_string($_GET['kode']) ? $_GET['kode'] : '';
if ($kode === '') {
	http_response_code(400);
	exit('Kode produk tidak valid.');
}

$produk = db_query_params($conn, "SELECT * FROM produk WHERE kode_produk = ?", [$kode]);
$row = db_fetch_assoc($produk);
if (!$row) {
	http_response_code(404);
	exit('Produk tidak ditemukan.');
}

db_query_params($conn, "DELETE FROM bom_produk WHERE kode_produk = ?", [$kode]);
$del = db_query_params($conn, "DELETE FROM produk WHERE kode_produk = ?", [$kode]);

if($del){
	$imagePath = __DIR__ . "/../../image/produk/" . basename($row['image']);
	if (is_file($imagePath)) {
		unlink($imagePath);
	}
	echo "
	<script>
	alert('DATA BERHASIL DIHAPUS');
	window.location = '../m_produk.php';
	</script>
	";
}

?>