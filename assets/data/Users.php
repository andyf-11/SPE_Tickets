<?php
session_start();
require_once '../../dbconnection.php'; // Ajusta la ruta según la estructura

// Función para detectar sistema operativo desde user agent
function getOS($user_agent) {
    $os_array = [
        '/windows nt 11' => 'Windows 11',
        '/windows nt 10/i' => 'Windows 10',
        '/windows nt 6.3/i' => 'Windows 8.1',
        '/windows nt 6.2/i' => 'Windows 8',
        '/windows nt 6.1/i' => 'Windows 7',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/linux/i' => 'Linux',
        '/iphone/i' => 'iPhone',
        '/android/i' => 'Android',
    ];
    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) return $value;
    }
    return "Desconocido";
}

try {
    if (empty($_POST["username"]) || empty($_POST["pass"])) {
        $_SESSION["error_message"] = "Por favor ingrese usuario y contraseña.";
        header("Location: ../../login1.php");
        exit();
    }

    $username = trim($_POST["username"]);
    $pass = $_POST["pass"];

    // Agregamos is_verified
    $query = $pdo->prepare("SELECT id, name, email, password, role, is_verified FROM user WHERE email = :username");
    $query->execute([":username" => $username]);
    $user = $query->fetch();

    if ($user && password_verify($pass, $user["password"])) {
        // Verificar si la cuenta está verificada
        if (isset($user["is_verified"]) && $user["is_verified"] == 0) {
            $_SESSION["error_message"] = "Tu cuenta no está verificada. Revisa tu correo para activarla.";
            header("Location: ../../login1.php");
            exit();
        }

        // Si está verificada, continuar con el login normal
        $_SESSION["login"] = $user["email"];
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_name"] = $user["name"];
        $_SESSION["user_role"] = strtolower(trim($user["role"]));

        // Obtener datos del cliente
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
        $os = getOS($user_agent);

        // Registrar inicio de sesión en usercheck
        try {
            $insertLogin = $pdo->prepare("INSERT INTO usercheck (user_id, logindate, email, username, ip, user_agent, os) 
                                          VALUES (:user_id, NOW(), :email, :username, :ip, :user_agent, :os)");
            $insertLogin->execute([
                ':user_id' => $user["id"],
                ':email' => $user["email"],
                ':username' => $user["name"],
                ':ip' => $ip,
                ':user_agent' => $user_agent,
                ':os' => $os
            ]);
        } catch (PDOException $e) {
            error_log("Fallo al registrar login en usercheck: " . $e->getMessage());
        }

        // Redirección por rol
        error_log("Login exitoso: " . $user['email'] . " | Rol: " . $_SESSION["user_role"]);

        switch ($_SESSION["user_role"]) {
            case "admin":
                header("Location: /SPE_Soporte_Tickets/admin/home.php");
                break;
            case "tecnico":
                header("Location: /SPE_Soporte_Tickets/tecnico/t_dashboard.php");
                break;
            case "supervisor":
                header("Location: /SPE_Soporte_Tickets/supervisor/s_dashboard.php");
                break;
            case "usuario":
                header("Location: /SPE_Soporte_Tickets/dashboard.php");
                break;
            default:
                $_SESSION["error_message"] = "Rol no reconocido.";
                header("Location: /SPE_Soporte_Tickets/login1.php");
                break;
        }
        exit();
    } else {
        $_SESSION["error_message"] = "Usuario o contraseña incorrectos.";
        header("Location: /SPE_Soporte_Tickets/login1.php");
        exit();
    }

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    $_SESSION["error_message"] = "Error del servidor. Intente más tarde.";
    header("Location: /SPE_Soporte_Tickets/login1.php");
    exit();
}
?>
