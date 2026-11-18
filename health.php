<?php
require "koneksi/koneksi.php";

$conn->query("SELECT 1");
http_response_code(200);
header("Content-Type: text/plain; charset=utf-8");
echo "ok";
