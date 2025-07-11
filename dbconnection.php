<?php
// dbconnection.php (usando PDO de forma global y reutilizable)
require_once 'load_env.php';
load_env();

$host = '127.0.0.1';
$db   = 'crm-gestion';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Mostrar error en desarrollo, ocultar en producción
    error_log("DB Error:" . $e->getMessage());
    die("Error de conexión. Intente más tarde");
}

// Ahora puedes usar $pdo en cualquier script que incluya este archivo
