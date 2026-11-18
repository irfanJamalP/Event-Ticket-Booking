<?php
session_start();
require "../koneksi/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../keranjang.php");
    exit();
}

$kodeCustomer = $_SESSION["kd_cs"] ?? "";
$type = isset($_POST["type"]) && is_string($_POST["type"])
    ? trim($_POST["type"])
    : "";
$province = isset($_POST["prov"]) && is_string($_POST["prov"])
    ? trim($_POST["prov"])
    : "";
$city = isset($_POST["kota"]) && is_string($_POST["kota"])
    ? trim($_POST["kota"])
    : "";
$address = isset($_POST["almt"]) && is_string($_POST["almt"])
    ? trim($_POST["almt"])
    : "";
$postalCode = isset($_POST["kopos"]) && is_string($_POST["kopos"])
    ? trim($_POST["kopos"])
    : "";

if (
    $kodeCustomer === "" ||
    !in_array($type, ["transfer", "qris"], true) ||
    $province === "" ||
    $city === "" ||
    $address === "" ||
    $postalCode === "" ||
    strlen($province) > 200 ||
    strlen($city) > 200 ||
    strlen($address) > 200 ||
    strlen($postalCode) > 20
) {
    http_response_code(400);
    exit("Data checkout tidak valid. Kembali ke keranjang dan periksa kembali pesanan Anda.");
}

$invoice = "INV" . date("YmdHis") . strtoupper(bin2hex(random_bytes(4)));

try {
    $conn->beginTransaction();

    $customerStatement = $conn->prepare(
        "SELECT kode_customer FROM customer WHERE kode_customer = ?"
    );
    $customerStatement->execute([$kodeCustomer]);
    $customerExists = $customerStatement->fetchColumn() !== false;

    if (!$customerExists) {
        $conn->rollback();
        http_response_code(401);
        exit("Sesi pelanggan tidak valid. Silakan login kembali.");
    }

    $methodStatement = $conn->prepare(
        "SELECT id FROM metode_pembayaran WHERE type = ? LIMIT 1"
    );
    $methodStatement->execute([$type]);
    $methodExists = $methodStatement->fetchColumn() !== false;

    if (!$methodExists) {
        $conn->rollback();
        http_response_code(400);
        exit("Metode pembayaran yang dipilih tidak tersedia.");
    }

    $cartStatement = $conn->prepare(
        "SELECT kode_produk, nama_produk, qty, harga FROM keranjang WHERE kode_customer = ? FOR UPDATE"
    );
    $cartStatement->execute([$kodeCustomer]);
    $cartItems = $cartStatement->fetchAll(PDO::FETCH_ASSOC);

    if (count($cartItems) === 0) {
        $conn->rollback();
        http_response_code(400);
        exit("Keranjang kosong. Tambahkan tiket sebelum membuat pesanan.");
    }

    $status = "Pesanan Baru";
    $orderDate = date("Y-m-d");
    $received = "0";
    $rejected = "0";
    $checked = 0;
    $insertStatement = $conn->prepare(
        "INSERT INTO produksi (invoice, kode_customer, kode_produk, nama_produk, qty, harga, status, tanggal, provinsi, kota, alamat, kode_pos, terima, tolak, cek) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    foreach ($cartItems as $item) {
        $productCode = $item["kode_produk"];
        $productName = $item["nama_produk"];
        $quantity = (int) $item["qty"];
        $price = (int) $item["harga"];

        if ($quantity < 1 || $price < 0) {
            $conn->rollback();
            http_response_code(400);
            exit("Keranjang berisi jumlah atau harga yang tidak valid.");
        }

        $insertStatement->execute([
            $invoice,
            $kodeCustomer,
            $productCode,
            $productName,
            $quantity,
            $price,
            $status,
            $orderDate,
            $province,
            $city,
            $address,
            $postalCode,
            $received,
            $rejected,
            $checked,
        ]);
    }

    $deleteStatement = $conn->prepare(
        "DELETE FROM keranjang WHERE kode_customer = ?"
    );
    $deleteStatement->execute([$kodeCustomer]);

    $conn->commit();
} catch (PDOException $exception) {
    $conn->rollback();
    error_log("Order creation failed: " . $exception->getMessage());
    http_response_code(500);
    exit("Pesanan belum dapat dibuat karena terjadi kesalahan. Silakan coba kembali.");
}

header(
    "Location: ../bukti_pembayaran.php?type=" .
        rawurlencode($type) .
        "&invoice=" .
        rawurlencode($invoice)
);
exit();
