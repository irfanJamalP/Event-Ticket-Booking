<?php
require "../koneksi/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../register.php");
    exit();
}

$name = isset($_POST["nama"]) && is_string($_POST["nama"])
    ? trim($_POST["nama"])
    : "";
$email = isset($_POST["email"]) && is_string($_POST["email"])
    ? trim($_POST["email"])
    : "";
$username = isset($_POST["username"]) && is_string($_POST["username"])
    ? trim($_POST["username"])
    : "";
$phone = isset($_POST["telp"]) && is_string($_POST["telp"])
    ? trim($_POST["telp"])
    : "";
$password = isset($_POST["password"]) && is_string($_POST["password"])
    ? $_POST["password"]
    : "";
$confirmation = isset($_POST["konfirmasi"]) && is_string($_POST["konfirmasi"])
    ? $_POST["konfirmasi"]
    : "";

if (
    $name === "" ||
    strlen($name) > 100 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 100 ||
    $username === "" ||
    strlen($username) > 100 ||
    $phone === "" ||
    strlen($phone) > 200 ||
    strlen($password) < 8 ||
    $password !== $confirmation
) {
    echo "<script>alert('Periksa kembali data pendaftaran dan gunakan kata sandi minimal 8 karakter.'); window.location = '../register.php';</script>";
    exit();
}

$customerCode = "C" . strtoupper(bin2hex(random_bytes(5)));
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$statement = $conn->prepare(
    "INSERT INTO customer (kode_customer, nama, email, username, password, telp) VALUES (?, ?, ?, ?, ?, ?)"
);
$statement->execute([
    $customerCode,
    $name,
    $email,
    $username,
    $passwordHash,
    $phone,
]);

echo "<script>alert('REGISTER BERHASIL'); window.location = '../user_login.php';</script>";
