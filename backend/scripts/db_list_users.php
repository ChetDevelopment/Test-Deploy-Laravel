<?php

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$db = getenv('DB_DATABASE') ?: 'attendance_system';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "DB connect failed: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

$stmt = $pdo->query('SELECT id, email, name, role_id FROM users ORDER BY id ASC LIMIT 50');
foreach ($stmt as $row) {
    echo $row['id'] . "\t" . $row['email'] . "\t" . $row['name'] . "\trole_id=" . $row['role_id'] . PHP_EOL;
}

