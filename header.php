<?php
session_start();
include "koneksi/koneksi.php";
if (isset($_SESSION["kd_cs"])) {
    $kode_cs = $_SESSION["kd_cs"];
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Event Ticket Booking</title>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap-theme.css">
	<script  src="js/jquery.js"></script>
	<script  src="js/bootstrap.min.js"></script>
</head>
<body>
	<div class="container-fluid">
		<div class="row top">
			<div style="display: flex; justify-content: space-between; align-items: center; padding: 0 20px;">
				<div  style="padding: 3px;">
					<span>Pemesanan Tiket</span>
				</div>
			</div>
		</div>
	</div>

	<nav class="navbar navbar-default" style="padding: 5px;">
		<div class="container">

			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#" style="color: #ff8680"><b>Event Ticket Booking</b></a>
			</div>

			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav navbar-right">
					<li><a href="index.php">Home</a></li>
					<li><a href="produk.php">Produk</a></li>
					<li><a href="about.php">Tentang Kami</a></li>
					<li><a href="manual.php">Cara Memesan Tiket</a></li>
					<li><a href="riwayat.php">Riwayat</a></li>
					<?php
     if (isset($_SESSION["kd_cs"])) {
         $kode_cs = $_SESSION["kd_cs"];
         $cekStatement = db_prepare(
             $conn,
             "SELECT kode_produk FROM keranjang WHERE kode_customer = ?"
         );
         db_stmt_bind_param($cekStatement, "s", $kode_cs);
         db_stmt_execute($cekStatement);
         $cek = db_stmt_get_result($cekStatement);
         $value = db_num_rows($cek);
         if ($value > 0) { ?>
		<li><a href="keranjang.php"><i class="glyphicon glyphicon-shopping-cart"></i> <span class="badge" style="background-color: red; font-size: 11px;"><?= $value ?></span></a></li>
		<?php 		} else { ?>
		<li><a href="keranjang.php"><i class="glyphicon glyphicon-shopping-cart"></i></a></li>
		<?php }
		db_stmt_close($cekStatement);
     } else {
          ?>
					<li><a href="keranjang.php"><i class="glyphicon glyphicon-shopping-cart"></i></a></li>
					<?php
     }
     if (!isset($_SESSION["user"])) { ?>

						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="glyphicon glyphicon-user"></i> Akun <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="user_login.php">Login</a></li>
								<li><a href="register.php">Register</a></li>
							</ul>
						</li>
						<?php } else { ?>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="glyphicon glyphicon-user"></i> <?= htmlspecialchars(
           $_SESSION["user"],
           ENT_QUOTES,
           "UTF-8"
       ) ?> <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="proses/logout.php">Log Out</a></li>
							</ul>
						</li>

						<?php }
     ?>
				</ul>
			</div><!-- /.navbar-collapse -->
		</div><!-- /.container-fluid -->
	</nav>
	<div style="display: flex; flex-direction: column; height: calc(100vh - 108px);">
