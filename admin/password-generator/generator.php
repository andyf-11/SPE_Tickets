<?php
session_start();
require_once("../checklogin.php");
require("../dbconnection.php");
check_login("admin");

// Función para generar contraseñas
function generarContrasena($longitud = 10, $numeros = true, $simbolos = true, $area ='')
{
    $letras = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numeros_str = '0123456789';
    $simbolos_str = '!@#$%^&*()_-+=<>?';

    $caracteres = $letras;
    if ($numeros)
        $caracteres .= $numeros_str;
    if ($simbolos)
        $caracteres .= $simbolos_str;

    //nombre de área a 3 letras mayúsculas
    $abreviatura = strtoupper(substr(preg_replace('/[a-zA-Z]/', '', $area),0, 3));
    if (!$abreviatura) $abreviatura = 'GEN'; //este es el valor por defecto

    $password = '';
    $body_length = max($longitud - strlen($abreviatura), 8);

    for ($i = 0; $i < $body_length; $i++){
        $password .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }

    return $abreviatura . $password;
}

// Procesar formulario
$mensaje = '';
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['generar'])) {
    $area = $_POST['area'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $longitud = intval($_POST['longitud']) ?: 10;
    $numeros = isset($_POST['numeros']);
    $simbolos = isset($_POST['simbolos']);

    $nueva_contrasena = generarContrasena($longitud, $numeros, $simbolos);
} elseif ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['asignar'])) {
    $area = $_POST['area'] ?? '';
    $rol = $_POST['role'] ?? '';
    $password = $_POST['contrasena'] ?? '';

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE user SET password = :password WHERE 1=1";
    $params = [':password' => $hash];

    if (!empty($area)) {
        $sql .= " AND area = :area";
        $params[':area'] = $area;
    }
    if (!empty($rol)) {
        $sql .= " AND role = :role";
        $params[':role'] = $rol;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $mensaje = "Contraseña asignada correctamente a los usuarios seleccionados.";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Contraseñas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="css/pass-generator.css" rel="stylesheet">
</head>

<body>
    <?php include "../header.php" ?>
    <div class="container-fluid">
        <div class="row">
            <main class="container py-5">
                <a href="../home.php" class="go-back"><i class="fa-solid fa-hand-point-left"></i></i></a>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card-style">
                            <h2>Generador de Contraseñas</h2>

                            <?php if (!empty($mensaje)): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
                            <?php endif; ?>

                            <form method="post" class="row g-3">
                                <div class="col-md-4">
                                    <label for="area" class="form-label">Área</label>
                                    <select name="area" id="area" class="form-select">
                                        <option value="">-- Selecciona un área --</option>
                                        <option value="Finanzas">Finanzas</option>
                                        <option value="Informática">Informática</option>
                                        <option value="Talento Humano">Talento Humano</option>
                                        <!-- Más áreas aquí -->
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="rol" class="form-label">Rol</label>
                                    <select name="rol" id="rol" class="form-select">
                                        <option value="">-- Selecciona un rol --</option>
                                        <option value="usuario">Usuario</option>
                                        <option value="tecnico">Técnico</option>
                                        <option value="supervisor">Supervisor</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="longitud" class="form-label">Longitud</label>
                                    <input type="number" name="longitud" id="longitud" class="form-control" value="10"
                                        min="6" max="32">
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="numeros" id="numeros">
                                        <label class="form-check-label" for="numeros">Incluir números</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="simbolos" id="simbolos">
                                        <label class="form-check-label" for="simbolos">Incluir símbolos</label>
                                    </div>
                                </div>

                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" name="generar" class="btn btn-primary">Generar
                                        Contraseña</button>
                                </div>

                                <?php if (!empty($nueva_contrasena)): ?>
                                    <div class="col-md-12">
                                        <label class="form-label">Contraseña Generada</label>
                                        <input type="text" class="form-control" name="contrasena"
                                            value="<?= htmlspecialchars($nueva_contrasena) ?>" readonly>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" name="asignar" class="btn btn-success">Asignar a
                                            usuarios</button>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
        </div>

        </main>
    </div>
    </div>
</body>

</html>