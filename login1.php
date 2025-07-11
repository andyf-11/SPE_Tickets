<?php
session_start();
$error = $_SESSION["error_message"] ?? null;
unset($_SESSION["error_message"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SPE - Login</title>

  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background-color: #f4f6f9;
      height: 100vh;
    }
    .login-container {
      max-width: 400px;
      margin: auto;
      padding-top: 80px;
    }
    .card {
      border-radius: 1rem;
    }
    .login-logo {
      text-align: center;
      margin-bottom: 1rem;
    }
    .login-logo img {
      max-width: 200px;
    }
    .forgot-password {
      text-align: center;
      margin-top: 15px;
      font-size: 0.95rem;
    }
  </style>
</head>

<body>
  <div class="container login-container">
    <div class="card shadow-sm p-4">
      <div class="login-logo">
        <img src="assets/img/Logo-Gobierno-en-Vertical.png" alt="Logo">
      </div>
      <h5 class="text-center mb-4">Iniciar Sesión</h5>
      <form method="POST" action="assets/data/Users.php" id="formLogin">
        <div class="mb-3">
          <label for="username" class="form-label">Correo</label>
          <input type="text" class="form-control" name="username" id="username" placeholder="Ingresa tu correo electrónico" required>
        </div>
        <div class="mb-3">
          <label for="pass" class="form-label">Contraseña</label>
          <input type="password" class="form-control" name="pass" id="pass" placeholder="Ingresa tu contraseña" required>
        </div>
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger py-2">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        <div class="d-grid">
          <button type="submit" name="login-button" id="btnLogin" class="btn btn-primary">
            <i class="fa fa-sign-in-alt me-2"></i> Iniciar Sesión
          </button>
        </div>
      </form>

      <div class="forgot-password mt-3">
        ¿Olvidaste tu contraseña?
        <a href="#" data-bs-toggle="modal" data-bs-target="#recuperarModal">Haz clic aquí</a> para solicitar ayuda.
      </div>
    </div>
  </div>

  <!-- Modal de recuperación -->
  <div class="modal fade" id="recuperarModal" tabindex="-1" aria-labelledby="recuperarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content" method="post" action="forgot-password.php">
        <div class="modal-header">
          <h5 class="modal-title" id="recuperarModalLabel"><i class="bi bi-question-circle me-2"></i> Solicitar ayuda para recuperar contraseña</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="correo" class="form-label">Correo electrónico</label>
            <input type="email" name="correo" id="correo" class="form-control" value="" placeholder="Tu correo registrado" required>
          </div>
          <div class="mb-3">
            <label for="motivo" class="form-label">Motivo</label>
            <textarea name="motivo" id="motivo" rows="3" class="form-control" placeholder="Describe brevemente tu problema..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Enviar solicitud</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Prellenar el correo si ya se escribió
    document.getElementById('username').addEventListener('input', function () {
      document.getElementById('email').value = this.value;
    });
  </script>
</body>
</html>
