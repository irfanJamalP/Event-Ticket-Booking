<?php
if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit();
}

require __DIR__ . "/../koneksi/koneksi.php";

$schemaPath = __DIR__ . "/../database/db.sql";
$schema = file_get_contents($schemaPath);
if ($schema === false) {
    throw new RuntimeException("Could not read the PostgreSQL schema.");
}
$conn->exec($schema);

$username = getenv("ADMIN_USERNAME");
$password = getenv("ADMIN_PASSWORD");
if ($username === false || $password === false || $username === "" || $password === "") {
    throw new RuntimeException(
        "Set ADMIN_USERNAME and ADMIN_PASSWORD before starting the application."
    );
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$statement = $conn->prepare(
    "INSERT INTO admin (username, password) VALUES (?, ?) ON CONFLICT (username) DO NOTHING"
);
$statement->execute([$username, $passwordHash]);
