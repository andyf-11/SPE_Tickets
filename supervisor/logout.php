<?php
$token = $_POST['session_token'] ?? $_GET['session_token'] ?? null;

if($token) {
    session_id('sess_' . md5($token));
    session_start();
    session_unset();
    session_destroy();
} else {
    //si no hay token, cerra la sesión
    session_start();
    session_unset();
    session_destroy();
}

header("Location: ../index.php");
exit();
?>