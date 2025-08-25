<?php
session_start();
require_once("checklogin.php");
check_login("usuario");
require("../dbconnection.php");
require_once(__DIR__ . "/../assets/config/mailer_config.php");

$email = $_SESSION['login'];

$stmt = $pdo->prepare("SELECT name, password, password_last_changed FROM user WHERE email = ?");
$stmt->execute([$email]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    $_SESSION['msg1'] = "Usuario no encontrado.";
    $_SESSION['msg_type'] = "danger";
} else {
    $puedeCambiar = true; // Ya no hay restricción de días
    
    if (isset($_POST['change'])) {
        $oldpass = $_POST['oldpass'];
        $newpass = $_POST['newpass'];

        if (password_verify($oldpass, $userData['password'])) {
            $newHashedPass = password_hash($newpass, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE user SET password = ?, password_last_changed = NOW() WHERE email = ?");
            if ($update->execute([$newHashedPass, $email])) {
                
                // Enviar notificación de cambio de contraseña
                sendPasswordChangedNotification($email, $userData['name']);

                $_SESSION['msg1'] = "Contraseña cambiada correctamente. Hemos enviado un correo de confirmación.";
                $_SESSION['msg_type'] = "success";
            } else {
                $_SESSION['msg1'] = "Error al actualizar la contraseña.";
                $_SESSION['msg_type'] = "danger";
            }
        } else {
            $_SESSION['msg1'] = "La contraseña actual es incorrecta.";
            $_SESSION['msg_type'] = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <title>SPE - Cambiar Contraseña</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="../styles/usuario/change-password.css" rel="stylesheet" />
  
  <style>
   #leftbar {
      position: fixed;
      top: 41px;
      left: 0;
      width: 250px;
      height: calc(100vh - 41px);
      background-color: #fff;
      border-right: 1px solid #dee2e6;
      z-index: 1030;
      overflow-y: auto;
      font-weight: 400;
    }

    main.main-content {
      margin-left: 250px;
      padding: 2rem;
      min-height: calc(100vh - 56px);
    }

    @media (max-width: 767px) {
      #leftbar {
        position: relative;
        top: 0;
        width: 100%;
        height: auto;
      }

      main.main-content {
        margin-left: 0;
      }
    }
  </style>
  
  <script>
    function valid() {
      const oldpass = document.form1.oldpass.value.trim();
      const newpass = document.form1.newpass.value.trim();
      const confirmpassword = document.form1.confirmpassword.value.trim();

      if (!oldpass) {
        alert("Por favor, ingresa tu contraseña actual.");
        document.form1.oldpass.focus();
        return false;
      }
      if (!newpass) {
        alert("Por favor, ingresa una nueva contraseña.");
        document.form1.newpass.focus();
        return false;
      }
      if (!confirmpassword) {
        alert("Por favor, confirma tu nueva contraseña.");
        document.form1.confirmpassword.focus();
        return false;
      }
      if (newpass.length < 8) {
        alert("La nueva contraseña debe tener al menos 6 caracteres.");
        document.form1.newpass.focus();
        return false;
      }
      if (newpass !== confirmpassword) {
        alert("La contraseña y su confirmación no coinciden.");
        document.form1.newpass.focus();
        return false;
      }
      return true;
    }
    
    function checkPasswordStrength() {
      const password = document.getElementById('newpass').value;
      const strengthBar = document.getElementById('strength-bar');
      let strength = 0;
      
      if (password.length >= 6) strength += 1;
      if (password.length >= 8) strength += 1;
      if (/[A-Z]/.test(password)) strength += 1;
      if (/[0-9]/.test(password)) strength += 1;
      if (/[^A-Za-z0-9]/.test(password)) strength += 1;
      
      switch(strength) {
        case 0:
        case 1:
          strengthBar.style.width = '20%';
          strengthBar.style.backgroundColor = '#dc3545';
          break;
        case 2:
          strengthBar.style.width = '40%';
          strengthBar.style.backgroundColor = '#fd7e14';
          break;
        case 3:
          strengthBar.style.width = '60%';
          strengthBar.style.backgroundColor = '#ffc107';
          break;
        case 4:
          strengthBar.style.width = '80%';
          strengthBar.style.backgroundColor = '#28a745';
          break;
        case 5:
          strengthBar.style.width = '100%';
          strengthBar.style.backgroundColor = '#28a745';
          break;
      }
    }
  </script>
</head>

<body>
  <?php include("header.php"); ?>

  <div class="page-container d-flex">
    <?php include("leftbar.php"); ?>

    <main class="flex-grow-1 p-4 mt-5">
      <div class="container">
      

        <?php if (!empty($_SESSION['msg1'])): ?>
          <div class="alert alert-<?php echo htmlspecialchars($_SESSION['msg_type'] ?? 'info'); ?> alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
              <i class="fas <?php 
                echo $_SESSION['msg_type'] == 'danger' ? 'fa-exclamation-circle' : 
                     ($_SESSION['msg_type'] == 'success' ? 'fa-check-circle' : 
                     ($_SESSION['msg_type'] == 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle')); 
              ?> me-2"></i>
              <div><?php echo htmlspecialchars($_SESSION['msg1']); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
          </div>
          <?php $_SESSION['msg1'] = ""; $_SESSION['msg_type'] = ""; ?>
        <?php endif; ?>

        <div class="card password-card mb-4">
          <div class="card-header password-header">
            <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Seguridad de la cuenta</h5>
          </div>
          
          <form name="form1" method="post" action="" onsubmit="return valid();" class="needs-validation" novalidate>
            <div class="card-body password-body">
              <div class="mb-4">
                <p class="text-muted">Por motivos de seguridad, te recomendamos usar una contraseña fuerte y cambiarla cuando desees.</p>
              </div>
              
              <div class="row mb-4">
                <label for="oldpass" class="col-md-4 col-lg-3 col-form-label">Contraseña Actual</label>
                <div class="col-md-8 col-lg-9">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-unlock-alt"></i></span>
                    <input type="password" class="form-control" id="oldpass" name="oldpass" required minlength="6" placeholder="Ingresa tu contraseña actual" />
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="oldpass">
                      <i class="fas fa-eye"></i>
                    </button>
                    <div class="invalid-feedback">
                      Por favor, ingresa tu contraseña actual.
                    </div>
                  </div>
                </div>
              </div>

              <div class="row mb-4">
                <label for="newpass" class="col-md-4 col-lg-3 col-form-label">Nueva Contraseña</label>
                <div class="col-md-8 col-lg-9">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="newpass" name="newpass" required minlength="6" placeholder="Ingresa tu nueva contraseña" oninput="checkPasswordStrength()" />
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="newpass">
                      <i class="fas fa-eye"></i>
                    </button>
                    <div class="invalid-feedback">
                      La contraseña debe tener al menos 8 caracteres.
                    </div>
                  </div>
                  <div class="password-strength mt-2">
                    <div id="strength-bar" class="strength-bar"></div>
                  </div>
                  <small class="text-muted mt-1 d-block">Mínimo 8 caracteres. Recomendamos usar mayúsculas, números y símbolos.</small>
                </div>
              </div>

              <div class="row mb-2">
                <label for="confirmpassword" class="col-md-4 col-lg-3 col-form-label">Confirmar Contraseña</label>
                <div class="col-md-8 col-lg-9">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="confirmpassword" name="confirmpassword" required minlength="6" placeholder="Confirma tu nueva contraseña" />
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirmpassword">
                      <i class="fas fa-eye"></i>
                    </button>
                    <div class="invalid-feedback">
                      Las contraseñas no coinciden.
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer password-footer text-end">
              <button type="reset" class="btn btn-outline-secondary me-2">
                <i class="fas fa-undo me-1"></i> Limpiar
              </button>
              <button type="submit" name="change" class="btn btn-success">
                <i class="fas fa-save me-1"></i> Guardar Cambios
              </button>
            </div>
          </form>
        </div>
        
        <div class="card password-card">
          <div class="card-header password-header">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información importante</h5>
          </div>
          <div class="card-body">
            <ul class="list-unstyled">
              <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Usa una contraseña diferente a la de otros servicios.</li>
              <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> No compartas tu contraseña con nadie.</li>
              <li><i class="fas fa-check-circle text-success me-2"></i> Recibirás un correo electrónico confirmando el cambio de contraseña</li>
            </ul>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Validación de formulario
    (() => {
      'use strict'
      const forms = document.querySelectorAll('.needs-validation')
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
    })()
    
    // Toggle para mostrar/ocultar contraseña
    document.querySelectorAll('.toggle-password').forEach(button => {
      button.addEventListener('click', function() {
        const target = document.getElementById(this.getAttribute('data-target'));
        const icon = this.querySelector('i');
        
        if (target.type === 'password') {
          target.type = 'text';
          icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
          target.type = 'password';
          icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
      });
    });
  </script>
</body>

</html>
