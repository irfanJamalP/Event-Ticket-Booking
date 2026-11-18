<?php
$envPath = __DIR__ . "/../.env";
$env = [];

if (is_file($envPath)) {
    $parsedEnv = parse_ini_file($envPath, false, INI_SCANNER_RAW);
    if ($parsedEnv === false) {
        throw new RuntimeException("Could not parse the .env configuration.");
    }
    $env = $parsedEnv;
}

$databaseUrl = getenv("DATABASE_URL");
if ($databaseUrl === false || $databaseUrl === "") {
    $databaseUrl = $env["DATABASE_URL"] ?? "";
}

if ($databaseUrl === "") {
    throw new RuntimeException(
        "DATABASE_URL is required. Configure it in .env or the hosting environment."
    );
}

if (!extension_loaded("pdo_pgsql")) {
    throw new RuntimeException(
        "The PHP pdo_pgsql extension is required to connect to Neon PostgreSQL."
    );
}

$url = parse_url($databaseUrl);
if (
    $url === false ||
    !isset($url["scheme"], $url["host"], $url["user"], $url["pass"], $url["path"]) ||
    !in_array($url["scheme"], ["postgres", "postgresql"], true)
) {
    throw new RuntimeException("DATABASE_URL must be a valid PostgreSQL connection URL.");
}

$port = $url["port"] ?? 5432;
$databaseName = ltrim($url["path"], "/");
if ($databaseName === "") {
    throw new RuntimeException("DATABASE_URL must include a database name.");
}

$dsn = sprintf(
    "pgsql:host=%s;port=%d;dbname=%s;sslmode=require",
    $url["host"],
    $port,
    $databaseName
);

require_once __DIR__ . "/database.php";

try {
    $conn = new PDO(
        $dsn,
        rawurldecode($url["user"]),
        rawurldecode($url["pass"]),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    error_log("PostgreSQL connection failed: " . $exception->getMessage());
    http_response_code(503);
    exit("Database unavailable. Check DATABASE_URL and ensure the Neon database is active.");
}
