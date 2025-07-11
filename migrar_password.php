---Este archivo se ejecuta una sola vez---
<?php
// Datos de conexión (ajusta según tu configuración)
$host = 'localhost';
$usuario = 'root';
$contrasena = '';
$basedatos = 'crm-gestion';

// Conectar a la base de datos
$mysqli = new mysqli($host, $usuario, $contrasena, $basedatos);

if ($mysqli->connect_errno) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Consulta para obtener usuarios con contraseña en texto plano
$sql = "SELECT id, password FROM user";
$result = $mysqli->query($sql);

if (!$result) {
    die("Error en la consulta: " . $mysqli->error);
}

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $passwordPlano = $row['password'];

    // Crear el hash seguro con password_hash()
    $passwordHasheada = password_hash($passwordPlano, PASSWORD_DEFAULT);

    // Actualizar la contraseña en la base de datos
    $stmt = $mysqli->prepare("UPDATE user SET password = ? WHERE id = ?");
    $stmt->bind_param('si', $passwordHasheada, $id);
    $stmt->execute();
    $stmt->close();
}

echo "Migración de contraseñas completada.";

$mysqli->close();
?>
