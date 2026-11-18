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
		"INSERT INTO inventory (kode_bk, nama, qty, satuan, harga, tanggal) VALUES (?, ?, ?, ?, ?, ?)",
		[$kode, $nama, $stok, $satuan, $harga, $tanggal]
	);

	if($result){
		echo "
			<script>
				alert('DATA BERHASIL DITAMBAHKAN');
				window.location = '../inventory.php';
			</script>
		";
	}


 ?>