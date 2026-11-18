<!DOCTYPE html>
<html>
<head>
	<title>Event Ticket Booking</title>
</head>
<body>
	<table>
		<tr>
			<th>No</th>
			<th>Nama Produk</th>
			<th>tanggal</th>
			<th>qty</th>
		</tr>
		<?php
  header("Content-type: application/vnd-ms-excel");
  header("Content-Disposition: attachment; filename=Laporan Pembatalan.xls");
  header("Pragma: no-cache");
  header("Expires: 0");
  include "../koneksi/koneksi.php";
  $date1 = $_POST["date1"];
  $date2 = $_POST["date2"];
  $result = db_query_params(
      $conn,
      "SELECT * FROM produksi WHERE tolak = 1 AND tanggal BETWEEN ? AND ?",
      [$date1, $date2]
  );
  $no = 1;
  $total = 0;
  while ($row = db_fetch_assoc($result)) { ?>
			<tr>
				<td><?= $no ?></td>
				<td><?= $row["nama_produk"] ?></td>
				<td><?= $row["tanggal"] ?></td>
				<td><?= $row["qty"] ?></td>
			</tr>
			<?php
   $total += $row["qty"];
   $no++;
   }
  ?>
		<tr>
			<td colspan="4" class="text-right"><b>Jumlah dibatalkan = <?= $total ?></b></td>
		</tr>
	</table>

</body>
</html>
