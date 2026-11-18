<?php
include "header.php";
$invoices = isset($_GET["inv"]) && is_string($_GET["inv"]) ? $_GET["inv"] : "";
$d_order = db_query_params(
    $conn,
    "SELECT * FROM produksi WHERE invoice = ?",
    [$invoices]
);
$t_order = db_fetch_assoc($d_order);
if (!$t_order) {
    http_response_code(404);
    exit("Pesanan tidak ditemukan.");
}

$sortage = db_query($conn, "SELECT * FROM produksi where cek = '1'");
$cek_sor = db_num_rows($sortage);

// customer
$cs = db_query_params(
    $conn,
    "SELECT * FROM customer WHERE kode_customer = ?",
    [$t_order["kode_customer"]]
);
$t_cs = db_fetch_assoc($cs);
?>
<title>Event Ticket Booking</title>
<div class="container">
	<h2 style=" width: 100%; border-bottom: 4px solid gray"><b>Daftar Pesanan</b></h2>
	<br>
	<h5 class="bg-success" style="padding: 7px; width: 100%; font-weight: bold;"><marquee>Lakukan Reload Setiap Masuk Halaman ini, untuk menghindari terjadinya kesalahan data dan informasi</marquee></h5>
	<a href="produksi.php" class="btn btn-default"><i class="glyphicon glyphicon-refresh"></i> Reload</a>
	<table class="table table-striped">
		<thead>
			<tr>
				<th scope="col">No</th>
				<th scope="col">Invoice</th>
				<th scope="col">Kode Customer</th>
				<th scope="col">Status</th>
				<th scope="col">Tanggal</th>
				<th scope="col">Action</th>
			</tr>
		</thead>
		<tbody>

			<?php
   $result = db_query(
       $conn,
       "SELECT DISTINCT invoice, kode_customer, status, kode_produk, qty,terima,tolak, cek FROM produksi"
   );
   $no = 1;
   $array = 0;
   while ($row = db_fetch_assoc($result)) {

       $kodep = $row["kode_produk"];
       $inv = $row["invoice"];
       ?>

				<tr>
					<td><?= $no ?></td>
					<td><?= $row["invoice"] ?></td>
					<td><?= $row["kode_customer"] ?></td>
					<?php
     if ($row["terima"] == 1) { ?>
						<td style="color: green;font-weight: bold;">Pesanan Diterima (Siap Kirim)
							<?php } elseif ($row["tolak"] == 1) { ?>
							<td style="color: red;font-weight: bold;">Pesanan Ditolak
								<?php }
     if ($row["terima"] == 0 && $row["tolak"] == 0) { ?>
								<td style="color: orange;font-weight: bold;"><?= $row["status"] ?>
								<?php }
     $t_bom = db_query_params(
         $conn,
         "SELECT * FROM bom_produk WHERE kode_produk = ?",
         [$kodep]
     );

     while ($row1 = db_fetch_assoc($t_bom)) {
         $kodebk = $row1["kode_bk"];

         $inventory = db_query_params(
             $conn,
             "SELECT * FROM inventory WHERE kode_bk = ?",
             [$kodebk]
         );
         $r_inv = db_fetch_assoc($inventory);

         $kebutuhan = $row1["kebutuhan"];
         $qtyorder = $row["qty"];
         $inventory = $r_inv["qty"];

         $bom = $kebutuhan * $qtyorder;
         $hasil = $inventory - $bom;
         if ($hasil < 0 && $row["tolak"] == 0) {

             db_query_params(
                 $conn,
                 "UPDATE produksi SET cek = '1' WHERE invoice = ?",
                 [$inv]
             );
             $nama_material[] = $r_inv["nama"];
             ?>



									<?php
         }
     }
     ?>
						</td>
						<td>2020/26-01</td>
						<td>
							<?php if ($row["tolak"] == 0 && $row["cek"] == 1 && $row["terima"] == 0) { ?>
								<a href="inventory.php?cek=0" id="rq" class="btn btn-warning"><i class="glyphicon glyphicon-warning-sign"></i> Request Material Shortage</a>
								<a href="proses/tolak.php?inv=<?= $row[
            "invoice"
        ] ?>" class="btn btn-danger" onclick="return confirm('Yakin Ingin Menolak ?')"><i class="glyphicon glyphicon-remove-sign"></i> Tolak</a>
							<?php } elseif ($row["terima"] == 0 && $row["cek"] == 0) { ?>

								<a href="proses/terima.php?inv=<?= $row["invoice"] ?>&kdp=<?= $row[
    "kode_produk"
] ?>" class="btn btn-success"><i class="glyphicon glyphicon-ok-sign"></i> Terima</a>
								<a href="proses/tolak.php?inv=<?= $row[
            "invoice"
        ] ?>" class="btn btn-danger" onclick="return confirm('Yakin Ingin Menolak ?')"><i class="glyphicon glyphicon-remove-sign"></i> Tolak</a>
							<?php } ?>

							<a href="detailorder.php?inv=<?= $row[
           "invoice"
       ] ?>" type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-eye-open"></i> Detail Pesanan</a>
						</td>
					</tr>
					<?php $no++;
   }
   ?>

			</tbody>
		</table>



	<button type="hidden" data-toggle="modal" data-target="#myModal" id="btn" style="background-color: #fff; border: #fff;">
	</button>
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<a href="m_produk.php" class="btn btn-default close"></a>
					<h4 class="modal-title" id="myModalLabel">#<?= $t_order["invoice"] ?></h4>
				</div>
				<div class="modal-body">
					<table class="table table-striped">
						<tr>
							<td>Invoice</td>
							<td><?= $t_order["invoice"] ?></td>
						</tr>
						<tr>
							<td>Kode Customer</td>
							<td><?= $t_order["kode_customer"] ?></td>
						</tr>
						<tr>
							<td>Nama</td>
							<td><?= $t_cs["nama"] ?></td>
						</tr>
						<tr>
							<td>Alamat</td>
							<td><?php echo $t_order["alamat"] .
           "," .
           $t_order["kota"] .
           " " .
           $t_order["provinsi"] .
           "," .
           $t_order["kode_pos"]; ?></td>
						</tr>
						<tr>
							<td>No Telp</td>
							<td><?= $t_cs["telp"] ?></td>
						</tr>
					</table>

					<hr>
					<h4>List Order</h4>
					<table class="table table-striped">
						<tr>
							<th>No</th>
							<th>Kode Produk</th>
							<th>Nama Produk</th>
							<th>Harga</th>
							<th>qty</th>
							<th>Subtotal</th>
						</tr>
					<?php
     $order = db_query_params(
         $conn,
         "SELECT * FROM produksi WHERE invoice = ?",
         [$invoices]
     );
     $no = 1;
     $grand = 0;
     while ($list = db_fetch_assoc($order)) { ?>
						<tr>
							<td><?= $no ?></td>
							<td><?= $list["kode_produk"] ?></td>
							<td><?= $list["nama_produk"] ?></td>
							<td><?= number_format($list["harga"], 0, ".", ".") ?></td>
							<td><?= $list["qty"] ?></td>
							<td><?= number_format($list["harga"] * $list["qty"], 0, ".", ".") ?></td>
						</tr>
					<?php
     $sub = $list["harga"] * $list["qty"];
     $grand += $sub;
     $no++;
     }
     ?>
						<tr>
							<td colspan="6" class="text-right"><b>Grand Total = <?= number_format(
           $grand,
           0,
           ".",
           "."
       ) ?></b></td>
						</tr>
					</table>

					<div class="row">
						<div class="col-md-12">
							<h4>Pembayaran</h4>
							<table class="table table-striped">
								<tr>
									<td>Metode Pembayaran</td>
									<?php
         $metode = db_query_params(
             $conn,
             "SELECT * FROM metode_pembayaran WHERE id = ?",
             [$t_order["metode_pembayaran_id"]]
         );
         $t_metode = db_fetch_assoc($metode);
         ?>
									<td><?= isset($t_metode) ? $t_metode["data"] : "-" ?></td>
								</tr>
							</table>

							<?php if (!empty($t_order["bukti_pembayaran"])): ?>
								<img src="../bukti_pembayaran/<?= $t_order[
            "bukti_pembayaran"
        ] ?>" class="img-responsive" style="max-width: 500px;">
							<?php endif; ?>
						</div>
					</div>
				</div>



				<div class="modal-footer">
					<a href="produksi.php" class="btn btn-default">Close</a>
				</div>
			</div>
		</div>
	</div>

		<?php if ($cek_sor > 0) { ?>
	<br>
	<br>
	<div class="row">
		<div class="col-md-4 bg-danger" style="padding:10px;">
			<h4>Kekurangan Material </h4>
			<h5 style="color: red;font-weight: bold;">Silahkan Tambah Stok Material dibawah ini : </h5>
			<table class="table table-striped">
				<tr>
					<th>No</th>
					<th>Material</th>
				</tr>
	<?php
 $arr = array_values(array_unique($nama_material));
 for ($i = 0; $i < count($arr); $i++) { ?>
				<tr>
					<td><?= $i + 1 ?></td>
					<td><?= $arr[$i] ?></td>
				</tr>
	<?php }
 ?>
			</table>
		</div>
	</div>
<?php } ?>
	</div>

<script type="text/javascript">
	$( document ).ready(function() {
		$( "#btn" ).click();
	});
</script>

	<?php include "footer.php";
?>
