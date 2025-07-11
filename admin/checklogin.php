<?php
function check_login($required_role = null)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['login'])) {
        header("Location: index.html");
        exit();
    }

    if ($required_role !== null && strtolower($_SESSION["user_role"]) !== strtolower($required_role)) {
        header("Location: index.html"); // O a una página "acceso denegado"
        exit();
    }
}
?>
