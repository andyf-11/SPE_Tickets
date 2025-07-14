<?php
session_start();
require_once("../checklogin.php");
require("../dbconnection.php");
check_login("admin");

// Función para generar contraseñas
function generarContrasena($longitud = 10, $numeros = true, $simbolos = true, $area = '')
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
    $abreviatura = strtoupper(substr(preg_replace('/[a-zA-Z]/', '', $area), 0, 3));
    if (!$abreviatura)
        $abreviatura = 'GEN'; //este es el valor por defecto

    $password = '';
    $body_length = max($longitud - strlen($abreviatura), 8);

    for ($i = 0; $i < $body_length; $i++) {
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
    <style>
        .password-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .password-card:hover {
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
        }

        .password-display {
            font-family: 'Courier New', monospace;
            font-size: 1.2rem;
            letter-spacing: 1px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 12px 15px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .password-display:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
        }

        .password-display:focus {
            outline: none;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .btn-custom {
            padding: 10px 20px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }
    </style>
</head>

<body>
    <?php include "../header.php" ?>
    <div class="container-fluid">
        <div class="row">
            <main class="container py-5">
                <a href="../home.php" class="btn btn-outline-dark mb-4 mt-3">
                    <i class="fa-solid fa-arrow-left me-2"></i>Volver
                </a>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="password-card p-4 mb-4">
                            <h2 class="mb-4 text-center" style="color: black;"><i class="fas fa-key me-2"></i>Generador de Contraseñas</h2>

                            <?php if (!empty($mensaje)): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <?= htmlspecialchars($mensaje) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form method="post" class="row g-3">
                                <div class="col-md-6">
                                    <label for="area" class="form-label">Área</label>
                                    <select name="area" id="area" class="form-select">
                                        <option value="">-- Selecciona un área --</option>
                                        <option value="Finanzas">Finanzas</option>
                                        <option value="Informática">Informática</option>
                                        <option value="Talento Humano">Talento Humano</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
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

                                <div class="col-md-4">
                                    <div class="form-check form-switch mt-4 pt-2">
                                        <input class="form-check-input" type="checkbox" name="numeros" id="numeros"
                                            checked>
                                        <label class="form-check-label" for="numeros">Incluir números</label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check form-switch mt-4 pt-2">
                                        <input class="form-check-input" type="checkbox" name="simbolos" id="simbolos">
                                        <label class="form-check-label" for="simbolos">Incluir símbolos</label>
                                    </div>
                                </div>

                                <div class="col-12 text-center mt-3">
                                    <button type="submit" name="generar" class="btn btn-primary btn-custom">
                                        <i class="fas fa-key me-2"></i>Generar Contraseña
                                    </button>
                                </div>

                                <?php if (!empty($nueva_contrasena)): ?>
                                    <div class="col-md-12 mt-4">
                                        <label class="form-label">Contraseña Generada</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control password-display" name="contrasena"
                                                value="<?= htmlspecialchars($nueva_contrasena) ?>" readonly
                                                onclick="this.select(); document.execCommand('copy');">
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="copyToClipboard('<?= htmlspecialchars($nueva_contrasena) ?>')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">Haz clic en la contraseña para copiarla</small>
                                    </div>
                                    <div class="col-12 text-center mt-3">
                                        <button type="submit" name="asignar" class="btn btn-success btn-custom">
                                            <i class="fas fa-save me-2"></i>Asignar a usuarios
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function () {
                // Mostrar notificación de copiado
                const alert = document.createElement('div');
                alert.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3';
                alert.style.zIndex = '1060';
                alert.textContent = '¡Contraseña copiada al portapapeles!';
                document.body.appendChild(alert);

                setTimeout(() => {
                    alert.remove();
                }, 2000);
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>