<?php
session_start();
require "../koneksi/koneksi.php";

if (empty($_SESSION["kd_cs"])) {
    header("Location: ../user_login.php");
    exit();
}

$customerCode = $_SESSION["kd_cs"];
$productCode = isset($_GET["produk"]) && is_string($_GET["produk"])
    ? $_GET["produk"]
    : "";
$quantity = isset($_GET["hal"]) && $_GET["hal"] === "2"
    ? filter_var($_GET["jml"] ?? null, FILTER_VALIDATE_INT)
    : 1;

if ($productCode === "" || $quantity === false || $quantity < 1 || $quantity > 99) {
    http_response_code(400);
    exit("Produk atau jumlah tiket tidak valid.");
}

$productResult = db_query_params(
    $conn,
    "SELECT kode_produk, nama, harga FROM produk WHERE kode_produk = ?",
    [$productCode]
);
$product = db_fetch_assoc($productResult);

if (!$product) {
    http_response_code(404);
    exit("Acara tidak ditemukan.");
}

$cartResult = db_query_params(
    $conn,
    "SELECT qty FROM keranjang WHERE kode_customer = ? AND kode_produk = ?",
    [$customerCode, $productCode]
);
$cartItem = db_fetch_assoc($cartResult);

if ($cartItem) {
    $quantity += (int) $cartItem["qty"];
    if ($quantity > 99) {
        http_response_code(400);
        exit("Jumlah tiket maksimal adalah 99.");
    }

    db_query_params(
        $conn,
        "UPDATE keranjang SET qty = ? WHERE kode_customer = ? AND kode_produk = ?",
        [$quantity, $customerCode, $productCode]
    );
} else {
    db_query_params(
        $conn,
        "INSERT INTO keranjang (kode_customer, kode_produk, nama_produk, qty, harga) VALUES (?, ?, ?, ?, ?)",
        [
            $customerCode,
            $product["kode_produk"],
            $product["nama"],
            $quantity,
            $product["harga"],
        ]
    );
}

header("Location: ../keranjang.php");
exit();
