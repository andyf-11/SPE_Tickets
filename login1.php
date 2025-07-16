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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="assets/css/login.css" rel="stylesheet">
</head>

<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6 col-xl-5">
        <div class="login-card">
          <div class="login-header">
            <img src="assets/img/Logo-Gobierno-en-Vertical.png" alt="Logo" class="login-logo">
            <h4 class="mb-0">Bienvenido al Sistema</h4>
          </div>
          
          <div class="login-body">
            <h5 class="text-center mb-4">Iniciar Sesión</h5>
            
            <?php if (!empty($error)): ?>
              <div class="alert alert-danger py-2 mb-4">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <?= htmlspecialchars($error) ?>
              </div>
            <?php endif; ?>
            
            <form method="POST" action="assets/data/Users.php" id="formLogin">
              <div class="mb-3">
                <label for="username" class="form-label fw-semibold">Correo electrónico</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-envelope-fill text-muted"></i></span>
                  <input type="text" class="form-control" name="username" id="username" placeholder="tu@correo.com" required>
                </div>
              </div>
              
              <div class="mb-4">
                <label for="pass" class="form-label fw-semibold">Contraseña</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-lock-fill text-muted"></i></span>
                  <input type="password" class="form-control" name="pass" id="pass" placeholder="••••••••" required>
                  <button class="btn btn-outline-secondary toggle-password" type="button">
                    <i class="bi bi-eye-fill"></i>
                  </button>
                </div>
              </div>
              
              <div class="d-grid mb-3">
                <button type="submit" name="login-button" id="btnLogin" class="btn btn-primary btn-lg">
                  <i class="fa fa-sign-in-alt me-2"></i> Iniciar Sesión
                </button>
              </div>
              
              <div class="text-center">
                <a href="#" class="forgot-link" data-bs-toggle="modal" data-bs-target="#recuperarModal">
                  <i class="bi bi-question-circle me-1"></i> ¿Olvidaste tu contraseña?
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de recuperación -->
  <div class="modal fade" id="recuperarModal" tabindex="-1" aria-labelledby="recuperarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="recuperarModalLabel">
            <i class="bi bi-key-fill me-2"></i> Recuperar contraseña
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <form class="modal-content" method="post" action="forgot-password.php">
          <div class="modal-body">
            <p class="text-muted mb-4">Ingresa tu correo electrónico registrado y te enviaremos instrucciones para recuperar tu contraseña.</p>
            
            <div class="mb-3">
              <label for="correo" class="form-label fw-semibold">Correo electrónico</label>
              <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-envelope-fill text-muted"></i></span>
                <input type="email" name="correo" id="correo" class="form-control" value="" placeholder="tu@correo.com" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="motivo" class="form-label fw-semibold">Motivo</label>
              <textarea name="motivo" id="motivo" rows="3" class="form-control" placeholder="Describe brevemente tu problema..." required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Enviar solicitud</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

  <script>
    // Prellenar el correo si ya se escribió
    document.getElementById('username').addEventListener('input', function () {
      document.getElementById('correo').value = this.value;
    });
    
    // Toggle para mostrar/ocultar contraseña
    document.querySelectorAll('.toggle-password').forEach(button => {
      button.addEventListener('click', function() {
        const passwordInput = this.closest('.input-group').querySelector('input');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          icon.classList.remove('bi-eye-fill');
          icon.classList.add('bi-eye-slash-fill');
        } else {
          passwordInput.type = 'password';
          icon.classList.remove('bi-eye-slash-fill');
          icon.classList.add('bi-eye-fill');
        }
      });
    });
  </script>
</body>
</html>