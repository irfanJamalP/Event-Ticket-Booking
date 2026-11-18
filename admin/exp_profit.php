<!DOCTYPE html>
<html>
<head>
	<title>Event Ticket Booking</title>
</head>
<body>
	<table>
		<tr>
			<th>No</th>
			<th>Invoice</th>
			<th>Nama Produk</th>
			<th>Harga</th>
			<th>qty</th>
			<th>Subtotal</th>
			<th>tanggal</th>
		</tr>
		<?php
  header("Content-type: application/vnd-ms-excel");
  header("Content-Disposition: attachment; filename=Laporan Profit.xls");
  header("Pragma: no-cache");
  header("Expires: 0");
  include "../koneksi/koneksi.php";
  $date1 = $_POST["date1"];
  $date2 = $_POST["date2"];
  $result = db_query_params(
      $conn,
      "SELECT * FROM produksi WHERE terima = 1 AND tanggal BETWEEN ? AND ?",
      [$date1, $date2]
  );
  $no = 1;
  $total = 0;
  while ($row = db_fetch_assoc($result)) { ?>
			<tr>
				<td><?= $no ?></td>
				<td><?= $row["invoice"] ?></td>
				<td><?= $row["nama_produk"] ?></td>
				<td><?= number_format($row["harga"]) ?></td>
				<td><?= $row["qty"] ?></td>
				<td><?= number_format($row["harga"] * $row["qty"]) ?></td>
				<td><?= $row["tanggal"] ?></td>
			</tr>
			<?php
   $total += $row["harga"] * $row["qty"];
   $no++;
   }
  ?>
		<tr>
			<td colspan="7" class="text-right"><b>Total Pendapatan Kotor = <?= number_format(
       $total
   ) ?></b></td>
		</tr>
	</table>


	<h4><b>Pemotongan pendapatan balik modal</b></h4>
		<table class="table table-striped">
			<tr>
				<th>No</th>
				<th>Nama Ticket/Event</th>
				<th>Harga</th>
				<th>Kebutuhan</th>
				<th>Subtotal</th>
			</tr>
			<?php
   $result = db_query_params(
       $conn,
       "SELECT * FROM produksi WHERE terima = 1 AND tanggal BETWEEN ? AND ?",
       [$date1, $date2]
   );
   $no1 = 1;
   $totalb = 0;
   while ($row = db_fetch_assoc($result)) {
       $kd = $row["kode_produk"];
       $bahan = db_query_params(
           $conn,
           "SELECT b.kebutuhan AS kebutuhan, i.nama AS nama, i.harga AS harga FROM bom_produk b JOIN inventory i ON b.kode_bk = i.kode_bk WHERE b.kode_produk = ?",
           [$kd]
       );
       while ($row1 = db_fetch_assoc($bahan)) { ?>
					<tr>
						<td><?= $no1 ?></td>
						<td><?= $row1["nama"] ?></td>
						<td><?= $row1["harga"] ?></td>
						<td><?= $row1["kebutuhan"] ?></td>
						<td><?= number_format($row1["harga"] * $row1["kebutuhan"]) ?></td>
					</tr>
					<?php
     $totalb += $row1["harga"] * $row1["kebutuhan"];
     $no1++;
     }
   }
   ?>
		<tr>
			<td colspan="7" class="text-right"><b>Total  = <?= number_format(
       $totalb
   ) ?></b></td>
		</tr>
		<tr>
			<td colspan="7" class="text-right bg-success" style="color: green;"><b>TOTAL PENDAPATAN BERSIH = <?= number_format(
       $total - $totalb
   ) ?></b></td>
		</tr>
	</table>

</body>
</html>
