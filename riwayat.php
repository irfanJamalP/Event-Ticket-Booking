<?php
include "header.php";

if(isset($_SESSION["kd_cs"])) {
?>
<?php
// Get customer transactions
$kd_cs = $_SESSION["kd_cs"];
$result = db_query_params(
    $conn,
    "SELECT invoice, tanggal, status, terima, tolak, SUM(qty * harga) AS total FROM produksi WHERE kode_customer = ? GROUP BY invoice, tanggal, status, terima, tolak",
    [$kd_cs]
);
?>

<div class="container">
    <h2 class="text-center" style="margin: 30px;">Riwayat Pesanan</h2>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = db_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo $row["invoice"]; ?></td>
                <td><?php echo date(
                    "d-m-Y H:i",
                    strtotime($row["tanggal"])
                ); ?></td>
                <td>Rp <?php echo number_format($row["total"]); ?></td>
                <td>
                    <?php
                    if ($row["terima"] == 1) { ?>
                    <span class="badge badge-success">Pesanan Diterima (pastikan email anda tidak penuh)</span>
                    <?php } elseif ($row["tolak"] == 1) { ?>
                    <span class="badge badge-danger">Pesanan Ditolak</span>
                    <?php }
                    if ($row["terima"] == 0 && $row["tolak"] == 0) { ?>
                    <span class="badge badge-warning"><?= $row[
                        "status"
                    ] ?></span>
                    <?php }
                    ?>
                </td>

            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php } else { ?>
    <div style="margin: 0 20px;">Kosong</div>
    <?php }?>
<?php include "footer.php"; ?>
