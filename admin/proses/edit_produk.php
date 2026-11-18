<?php 
require __DIR__ . '/auth.php';

$kode = $_POST['kode'];
$nm_produk = $_POST['nama'];
$harga = $_POST['harga'];
$desk = $_POST['desk'];
$nama_gambar = $_FILES['files']['name'];
$size_gambar = $_FILES['files']['size'];
$tmp_file = $_FILES['files']['tmp_name'];
$eror = $_FILES['files']['error'];
$type = $_FILES['files']['type'];

// BOM
$kd_material = $_POST['material'];
$keb = $_POST['keb'];

$filter = array_filter($kd_material);
$jml = count($filter) - 1;
$bk = db_query_params($conn, "SELECT kode_bk FROM bom_produk WHERE kode_produk = ?", [$kode]);



if($eror === 4){

	$result = db_query_params($conn, "UPDATE produk SET nama = ?, deskripsi = ?, harga = ? WHERE kode_produk = ?", [$nm_produk, $desk, $harga, $kode]);

	$materials = [];
	while ($row = db_fetch_assoc($bk)) {
		$materials[] = $row['kode_bk'];
	}
	foreach ($filter as $index => $material) {
		if (!isset($materials[$index])) {
			break;
		}
		db_query_params(
			$conn,
			"UPDATE bom_produk SET kode_bk = ?, kebutuhan = ? WHERE kode_produk = ? AND kode_bk = ?",
			[$material, $keb[$index], $kode, $materials[$index]]
		);
	}
	if($result){
		echo "
		<script>
		alert('PRODUK BERHASIL DIEDIT');
		window.location = '../m_produk.php';
		</script>
		";
	}
	die;

}



$ekstensiGambar = array('jpg','jpeg','png');
$ekstensiGambarValid = explode(".", $nama_gambar);
$ekstensiGambarValid = strtolower(end($ekstensiGambarValid));

if(!in_array($ekstensiGambarValid, $ekstensiGambar)){
	echo "
	<script>
	alert('EKSTENSI GAMBAR HARUS JPG, JPEG, PNG');
	window.location = '../edit_produk.php?kode=".$kode."';
	</script>
	";
	die;
}

if($size_gambar > 1000000){
	echo "
	<script>
	alert('UKURAN GAMBAR TERLALU BESAR');
	window.location = '../tm_produk.php';
	</script>
	";
	die;
}

$namaGambarBaru = uniqid();
$namaGambarBaru.=".";
$namaGambarBaru.=$ekstensiGambarValid;

$gambar = db_query_params($conn, "SELECT image FROM produk WHERE kode_produk = ?", [$kode]);
$tgambar = db_fetch_assoc($gambar);
if (file_exists("../../image/produk/".$tgambar['image'])) {
	unlink("../../image/produk/".$tgambar['image']);
	move_uploaded_file($tmp_file, "../../image/produk/".$namaGambarBaru);

	$result = db_query_params($conn, "UPDATE produk SET nama = ?, image = ?, deskripsi = ?, harga = ? WHERE kode_produk = ?", [$nm_produk, $namaGambarBaru, $desk, $harga, $kode]);

	$materials = [];
	while ($row = db_fetch_assoc($bk)) {
		$materials[] = $row['kode_bk'];
	}
	foreach ($filter as $index => $material) {
		if (!isset($materials[$index])) {
			break;
		}
		db_query_params(
			$conn,
			"UPDATE bom_produk SET kode_bk = ?, kebutuhan = ? WHERE kode_produk = ? AND kode_bk = ?",
			[$material, $keb[$index], $kode, $materials[$index]]
		);
	}

	if($result){
		echo "
		<script>
		alert('PRODUK BERHASIL DIEDIT');
		window.location = '../m_produk.php';
		</script>
		";
	}


}else{

move_uploaded_file($tmp_file, "../../image/produk/".$namaGambarBaru);

	$result = db_query_params($conn, "UPDATE produk SET nama = ?, image = ?, deskripsi = ?, harga = ? WHERE kode_produk = ?", [$nm_produk, $namaGambarBaru, $desk, $harga, $kode]);

	$materials = [];
	while ($row = db_fetch_assoc($bk)) {
		$materials[] = $row['kode_bk'];
	}
	foreach ($filter as $index => $material) {
		if (!isset($materials[$index])) {
			break;
		}
		db_query_params(
			$conn,
			"UPDATE bom_produk SET kode_bk = ?, kebutuhan = ? WHERE kode_produk = ? AND kode_bk = ?",
			[$material, $keb[$index], $kode, $materials[$index]]
		);
	}

	if($result){
		echo "
		<script>
		alert('PRODUK BERHASIL DIEDIT');
		window.location = '../m_produk.php';
		</script>
		";
	}
}




?>