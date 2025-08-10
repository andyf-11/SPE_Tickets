<?php
require_once '../../dbconnection.php';
require_once 'mailer_config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");

    if (empty($email)) {
        die("Correo electrónico es requerido.");
    }

    $stmt = $pdo->prepare("SELECT id, name FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        //Genera token y expiración
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+ 1 hour"));

        //Guarda el nuevo token
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires]);

        if (sendPasswordResetEmail($email, $user['name'], $token)) {
            echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña | Soporte Técnico</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --success-color: #4cc9f0;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --border-color: #e9ecef;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif;
            background-color: var(--light-gray);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            line-height: 1.6;
            color: #212529;
        }
        
        .card {
            background-color: var(--white);
            border: none;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 480px;
            text-align: center;
            transition: var(--transition);
        }
        
        .card:hover {
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.12);
        }
        
        .success-icon {
            font-size: 3.5rem;
            color: var(--success-color);
            margin-bottom: 1.5rem;
        }
        
        h1 {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        p {
            color: #6c757d;
            margin-bottom: 2rem;
            font-size: 1rem;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: var(--white);
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        @media (max-width: 576px) {
            .card {
                padding: 1.5rem;
                margin: 0 1rem;
            }
            
            h1 {
                font-size: 1.25rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>¡Correo enviado con éxito!</h1>
        <p>Hemos enviado un correo electrónico con las instrucciones para restablecer tu contraseña. Por favor revisa tu bandeja de entrada.</p>
        <a href="/SPE_Soporte_Tickets/login1.php" class="btn btn-block">
            <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
        </a>
    </div>
</body>
</html>';

        } else {
            echo "Error al enviar el correo. Intenta de nueva más tarde.";
        }
    }
}