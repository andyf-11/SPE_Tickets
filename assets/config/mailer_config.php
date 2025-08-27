<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php'; // Ajusta la ruta si es necesario

// Cargar variables de entorno del archivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../', '.env.local');
$dotenv->load();

// Configuración del servidor SMTP desde .env
$mail_host       = $_ENV['MAIL_HOST'];
$mail_port       = $_ENV['MAIL_PORT'];
$mail_username   = $_ENV['MAIL_USERNAME'];
$mail_password   = $_ENV['MAIL_PASSWORD'];
$mail_encryption = $_ENV['MAIL_ENCRYPTION'];
$mail_from       = $_ENV['MAIL_FROM'];
$mail_from_name  = $_ENV['MAIL_FROM_NAME'];

function sendVerificationEmail($toEmail, $toName, $token) {
    global $mail_host, $mail_port, $mail_username, $mail_password, $mail_encryption, $mail_from, $mail_from_name;

    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = $mail_host;
        $mail->SMTPAuth = true;
        $mail->Username = $mail_username;
        $mail->Password = $mail_password;
        $mail->SMTPSecure = $mail_encryption;
        $mail->Port = $mail_port;

        // Remitente y destinatario
        $mail->setFrom($mail_from, $mail_from_name);
        $mail->addAddress($toEmail, $toName);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Verifica tu cuenta - SPE';

        $verificationLink = $_ENV['APP_DOMAIN_PHP'] . "/SPE_Soporte_Tickets/assets/config/verify.php?token=" . $token;
        $mail->Body = "
            Hola $toName, <br><br>
            Por favor haz clic en el siguiente enlace para verificar tu cuenta: <br>
            <a href='$verificationLink'>$verificationLink</a><br><br>
            Si no solicitaste esta cuenta, puedes ignorar este correo.
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        return false;
    }
}

function sendPasswordResetEmail($toEmail, $toName, $token) {
    global $mail_host, $mail_port, $mail_username, $mail_password, $mail_encryption, $mail_from, $mail_from_name;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $mail_host;
        $mail->SMTPAuth = true;
        $mail->Username = $mail_username;
        $mail->Password = $mail_password;
        $mail->SMTPSecure = $mail_encryption;
        $mail->Port = $mail_port;

        $mail->setFrom($mail_from, $mail_from_name);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Restablece tu contraseña - SPE';

        $resetLink = $_ENV['APP_DOMAIN_PHP'] . "/SPE_Soporte_Tickets/assets/config/reset_form.php?token=" . $token;
        $mail->Body = "
            Hola $toName, <br><br>
            Hemos recibido una solicitud para restablecer tu contraseña. <br>
            Haz clic en el siguiente enlace para continuar: <br>
            <a href='$resetLink>$resetLink</a><br><br>
            Si no solicitaste este cambio, puedes ignorar este correo.";

            $mail->send();
            return true;
           
    } catch (Exception $e) {
        error_log("Error al enviar correo de recuperación: " . $mail->ErrorInfo);
        return false;
    }
}

function sendPasswordChangedNotification($toEmail, $toName) {
    global $mail_host, $mail_port, $mail_username, $mail_password, $mail_encryption, $mail_from, $mail_from_name;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $mail_host;
        $mail->SMTPAuth = true;
        $mail->Username = $mail_username;
        $mail->Password = $mail_password;
        $mail->SMTPSecure = $mail_encryption;
        $mail->Port = $mail_port;

        $mail->setFrom($mail_from, $mail_from_name);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Notificación de cambio de contraseña - SPE';
        $mail->Body = "
            Hola $toName, <br><br>
            Te informamos que tu contraseña ha sido cambiada exitosamente desde el sistema SPE. <br>
            Si no realizaste este cambio, por favor contacta de inmediato al soporte técnico. <br><br>
            Saludos, <br>
            El equipo de TI SPE.
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error al enviar notificación de cambio de contraseña: " . $mail->ErrorInfo);
        return false;
    }
}

function sendRoleNotification($toEmail, $toName, $role, $subject, $message) {
    global $mail_host, $mail_port, $mail_username, $mail_password, $mail_encryption, $mail_from, $mail_from_name;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $mail_host;
        $mail->SMTPAuth = true;
        $mail->Username = $mail_username;
        $mail->Password = $mail_password;
        $mail->SMTPSecure = $mail_encryption;
        $mail->Port = $mail_port;

        $mail->setFrom($mail_from, $mail_from_name);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);

        // Personalización según rol
        switch ($role) {
            case 'admin':
                $mail->Subject = "🔔 [Admin] $subject";
                $mail->Body = "
                    <h3>Hola $toName,</h3>
                    <p>$message</p>
                    <p><b>Rol:</b> Administrador</p>
                ";
                break;

            case 'supervisor':
                $mail->Subject = "📊 [Supervisor] $subject";
                $mail->Body = "
                    <h3>Hola $toName,</h3>
                    <p>$message</p>
                    <p><b>Rol:</b> Supervisor</p>
                ";
                break;

            case 'tecnico':
                $mail->Subject = "🛠 [Técnico] $subject";
                $mail->Body = "
                    <h3>Hola $toName,</h3>
                    <p>$message</p>
                    <p><b>Rol:</b> Técnico</p>
                ";
                break;

            case 'usuario':
                $mail->Subject = "📌 [Usuario] $subject";
                $mail->Body = "
                    <h3>Hola $toName,</h3>
                    <p>$message</p>
                    <p><b>Rol:</b> Usuario</p>
                ";
                break;

            default:
                $mail->Subject = $subject;
                $mail->Body = $message;
        }

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error al enviar notificación de $role: " . $mail->ErrorInfo);
        return false;
    }
}