<?php
session_start();
require "../koneksi/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../user_login.php");
    exit();
}

$username = isset($_POST["username"]) && is_string($_POST["username"])
    ? trim($_POST["username"])
    : "";
$password = isset($_POST["pass"]) && is_string($_POST["pass"])
    ? $_POST["pass"]
    : "";

$result = db_query_params(
    $conn,
    "SELECT kode_customer, nama, password FROM customer WHERE username = ?",
    [$username]
);
$customer = db_fetch_assoc($result);

if (!$customer || !password_verify($password, $customer["password"])) {
    echo "<script>alert('USERNAME/PASSWORD SALAH'); window.location = '../user_login.php';</script>";
    exit();
}

session_regenerate_id(true);
$_SESSION["user"] = $customer["nama"];
$_SESSION["kd_cs"] = $customer["kode_customer"];
header("Location: ../index.php");
exit();
