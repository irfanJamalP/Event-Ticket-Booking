<?php
session_start();
require "../koneksi/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit();
}

$customerCode = $_SESSION["kd_cs"] ?? "";
$invoice = isset($_POST["invoice"]) && is_string($_POST["invoice"])
    ? trim($_POST["invoice"])
    : "";
$type = isset($_POST["type"]) && is_string($_POST["type"])
    ? trim($_POST["type"])
    : "";
$paymentMethodId = filter_var(
    $_POST["metode_pembayaran_id"] ?? null,
    FILTER_VALIDATE_INT
);

if (
    $customerCode === "" ||
    $invoice === "" ||
    !in_array($type, ["transfer", "qris"], true) ||
    !$paymentMethodId
) {
    http_response_code(400);
    exit("Data pembayaran tidak valid.");
}

$order = db_query_params(
    $conn,
    "SELECT 1 FROM produksi WHERE invoice = ? AND kode_customer = ? LIMIT 1",
    [$invoice, $customerCode]
);
$method = db_query_params(
    $conn,
    "SELECT 1 FROM metode_pembayaran WHERE id = ? AND type = ?",
    [$paymentMethodId, $type]
);

if (db_num_rows($order) === 0 || db_num_rows($method) === 0) {
    http_response_code(404);
    exit("Pesanan atau metode pembayaran tidak ditemukan.");
}

if (
    !isset($_FILES["payment_proof"]) ||
    $_FILES["payment_proof"]["error"] !== UPLOAD_ERR_OK
) {
    http_response_code(400);
    exit("Bukti pembayaran gagal diunggah.");
}

$file = $_FILES["payment_proof"];
if ($file["size"] < 1 || $file["size"] > 5 * 1024 * 1024) {
    http_response_code(400);
    exit("Ukuran file harus maksimal 5 MB.");
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file["tmp_name"]);
$extensions = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
];

if (!isset($extensions[$mimeType])) {
    http_response_code(400);
    exit("Format bukti pembayaran harus JPG atau PNG.");
}

$uploadDirectory = __DIR__ . "/../bukti_pembayaran";
if (
    !is_dir($uploadDirectory) &&
    !mkdir($uploadDirectory, 0750, true) &&
    !is_dir($uploadDirectory)
) {
    http_response_code(500);
    exit("Direktori penyimpanan bukti pembayaran tidak tersedia.");
}

$fileName = bin2hex(random_bytes(16)) . "." . $extensions[$mimeType];
$filePath = $uploadDirectory . "/" . $fileName;
if (!move_uploaded_file($file["tmp_name"], $filePath)) {
    http_response_code(500);
    exit("Bukti pembayaran tidak dapat disimpan.");
}

try {
    $statement = $conn->prepare(
        "UPDATE produksi SET status = 'Menunggu Konfirmasi', bukti_pembayaran = ?, metode_pembayaran_id = ? WHERE invoice = ? AND kode_customer = ?"
    );
    $statement->execute([
        $fileName,
        $paymentMethodId,
        $invoice,
        $customerCode,
    ]);

    if ($statement->rowCount() === 0) {
        unlink($filePath);
        http_response_code(404);
        exit("Pesanan tidak ditemukan.");
    }
} catch (PDOException $exception) {
    unlink($filePath);
    error_log("Payment proof update failed: " . $exception->getMessage());
    http_response_code(500);
    exit("Pembayaran belum dapat diperbarui. Silakan coba kembali.");
}

header("Location: ../selesai.php");
exit();
