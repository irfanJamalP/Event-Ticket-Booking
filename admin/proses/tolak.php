<?php 
require __DIR__ . '/auth.php';
$inv = isset($_GET['inv']) && is_string($_GET['inv']) ? $_GET['inv'] : '';
if ($inv === '') {
	http_response_code(400);
	exit('Nomor invoice tidak valid.');
}
$tolak = db_query_params($conn, "UPDATE produksi SET tolak = '1', terima = '2' WHERE invoice = ?", [$inv]);

if($tolak){
	echo "
	<script>
	alert('PESANAN DITOLAK');
	window.location = '../produksi.php';
	</script>
	";
}

?>