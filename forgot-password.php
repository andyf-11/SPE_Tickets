<?php
session_start();
require_once 'dbconnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  $correo =trim($_POST["email"] ?? '');
  $motivo = trim($_POST["motive"] ?? '');

  if ($correo ==== '' || $motivo === '') {
    $_SESSION["error_message"] = "Por favor completa todos los campos";
    header("Location: login1.php");
    exit();
  }

  try {
    $stmt = $pdo->prepare("INSERT INTO password_request (email,motive) VALUES (?, ?)");
    $stmt->execute([$correo,$motivo]);

    $_SESSION["success_message"] = "Tu solicitud fue enviada correctamente.";
  } catch (PDOException $e) {
    $error_log("Error al guardar la solicitud de recuperación: " . $e->getMessage());
    $_SESSION["error_message"] = "Ocurrió un error. Intenta de nuevo más tarde";
  }

  header(Location: login1.php);
  exit();
} else {
  header(Location: login1.php);
  exit();
}
?>