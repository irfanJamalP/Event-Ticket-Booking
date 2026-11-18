<?php
session_start();
require "../../koneksi/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit();
}

$username = isset($_POST["user"]) && is_string($_POST["user"])
    ? trim($_POST["user"])
    : "";
$password = isset($_POST["pass"]) && is_string($_POST["pass"])
    ? $_POST["pass"]
    : "";

$result = db_query_params(
    $conn,
    "SELECT username, password FROM admin WHERE username = ?",
    [$username]
);
$admin = db_fetch_assoc($result);

if (!$admin || !password_verify($password, $admin["password"])) {
    echo "<script>alert('USERNAME/PASSWORD SALAH'); window.location = '../index.php';</script>";
    exit();
}

session_regenerate_id(true);
$_SESSION["admin"] = true;
header("Location: ../halaman_utama.php");
exit();
