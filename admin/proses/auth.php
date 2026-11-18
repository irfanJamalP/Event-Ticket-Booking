<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION["admin"])) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . "/../../koneksi/koneksi.php";
