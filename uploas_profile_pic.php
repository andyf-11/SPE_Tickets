<?php
session_start();
include("dbconnection.php");

$user_id = $_SESSION['id'];

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['profile_picture']['tmp_name'];
    $fileName = basename($_FILES['profile_picture']['name']);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = 'profile_' . $user_id . '.' . $ext;
    $uploadDir = 'uploads/';
    $uploadPath = $uploadDir . $newFileName;

    // Validar extensión
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($ext, $allowed)) {
        die("Tipo de archivo no permitido.");
    }

    // Crear carpeta si no existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Subir archivo
    if (move_uploaded_file($fileTmp, $uploadPath)) {
        // Actualizar en la base de datos
        $stmt = $con->prepare("UPDATE user SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param("si", $newFileName, $user_id);
        $stmt->execute();

        // Actualizar sesión
        $_SESSION['profile_picture'] = $newFileName;

        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error al subir la imagen.";
    }
} else {
    echo "No se seleccionó ninguna imagen.";
}
?>
