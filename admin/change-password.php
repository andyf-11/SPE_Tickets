<?php
session_start();
require_once("checklogin.php");
check_login("admin");
require("dbconnection.php");

$email = $_SESSION['login'];
$DIAS_ESPERA = 90; // Cambiar contraseña cada 90 días

$stmt = $pdo->prepare("SELECT password, password_last_changed, puede_cambiar_password FROM user WHERE email = ?");
$stmt->execute([$email]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    $_SESSION['msg1'] = "Usuario no encontrado.";
    $_SESSION['msg_type'] = "danger";
} elseif ($userData['puede_cambiar_password'] != 1) {
    echo "<div class='alert alert-danger text-center mt-5'>No tienes permiso para cambiar tu contraseña. Contacta con el administrador.</div>";
    exit();
} else {
    $puedeCambiar = true;

    if ($userData['password_last_changed']) {
        $fechaUltimoCambio = new DateTime($userData['password_last_changed']);
        $hoy = new DateTime();
        $interval = $fechaUltimoCambio->diff($hoy);
        if ($interval->days < $DIAS_ESPERA) {
            $diasRestantes = $DIAS_ESPERA - $interval->days;
            $puedeCambiar = false;
            $_SESSION['msg1'] = "Puedes cambiar la contraseña nuevamente en $diasRestantes días.";
            $_SESSION['msg_type'] = "warning";
        }
    }

    if (isset($_POST['change'])) {
        if (!$puedeCambiar) {
            $_SESSION['msg1'] = "No puedes cambiar la contraseña aún. Espera $diasRestantes días.";
            $_SESSION['msg_type'] = "warning";
        } else {
            $oldpass = $_POST['oldpass'];
            $newpass = $_POST['newpass'];

            if (password_verify($oldpass, $userData['password'])) {
                $newHashedPass = password_hash($newpass, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE user SET password = ?, password_last_changed = NOW(), puede_cambiar_password = 0 WHERE email = ?");
                $update->execute([$newHashedPass, $email]);

                $_SESSION['msg1'] = "Contraseña cambiada correctamente.";
                $_SESSION['msg_type'] = "success";
            } else {
                $_SESSION['msg1'] = "La contraseña actual es incorrecta.";
                $_SESSION['msg_type'] = "danger";
            }
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link href="../styles/admin/change-password.css" rel="stylesheet">
  <style>
     /* Sidebar fijo con altura completa menos header */
    #leftbar {
      position: fixed;
      top: 56px;
      /* altura del header fijo */
      left: 0;
      width: 250px;
      height: calc(100vh - 56px);
      background-color: #fff;
      border-right: 1px solid #dee2e6;
      z-index: 1030;
      overflow-y: auto;
      font-weight: 400;
    }

    /* Para el contenido principal, margen izquierdo igual al sidebar para evitar superposición */
    #main-content {
      margin-left: 250px;
      padding-top: 70px;
      /* espacio para header */
      min-height: 100vh;
    }
  </style>
</head>

<body class="bg-light">
  <?php include("header.php"); ?>

  <div class="container-fluid">
    <div class="row">
      <!-- Botón menú para móviles -->
      <button class="btn btn-outline-primary d-md-none m-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#leftbar">
        <i class="fas fa-bars me-2"></i> Menú
      </button>

      <!-- Sidebar -->
      <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse offcanvas-md offcanvas-start" id="leftbar">
        <?php include("leftbar.php"); ?>
      </nav>

      <!-- Contenido principal -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 mt-5">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
          <div>
            <h1 class="h2 mb-0"><i class="fas fa-key me-2 text-primary"></i>Cambiar Contraseña</h1>
            <p class="lead text-muted mb-0">Actualiza tu contraseña de acceso al sistema</p>
          </div>
          <div class="btn-toolbar mb-2 mb-md-0">
            <a href="home.php" class="btn btn-outline-secondary">
              <i class="fas fa-chevron-left me-1"></i> Volver
            </a>
          </div>
        </div>

        <?php if (!empty($_SESSION['msg1'])): ?>
          <div class="alert alert-<?= htmlspecialchars($_SESSION['msg_type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
            <i class="fas <?= $_SESSION['msg_type'] == 'success' ? 'fa-check-circle' : ($_SESSION['msg_type'] == 'danger' ? 'fa-exclamation-circle' : 'fa-info-circle') ?> me-2"></i>
            <?= htmlspecialchars($_SESSION['msg1']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
          </div>
          <?php $_SESSION['msg1'] = ""; $_SESSION['msg_type'] = ""; ?>
        <?php endif; ?>

        <div class="card shadow-sm password-card mb-4">
          <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-user-shield me-2 text-info"></i>Seguridad de la cuenta</h5>
          </div>
          <form name="form1" method="post" action="" onsubmit="return valid();" class="needs-validation" novalidate>
            <div class="card-body">
              <div class="row mb-4">
                <div class="col-12 mb-3">
                  <label for="oldpass" class="form-label fw-bold">Contraseña Actual</label>
                  <div class="input-group">
                    <span class="input-group-text password-icon">
                      <i class="fas fa-unlock-alt"></i>
                    </span>
                    <input type="password" class="form-control form-control-lg" id="oldpass" name="oldpass" required minlength="6" placeholder="Ingresa tu contraseña actual" />
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="oldpass">
                      <i class="fas fa-eye"></i>
                    </button>
                    <div class="invalid-feedback">
                      Por favor ingresa tu contraseña actual.
                    </div>
                  </div>
                </div>

                <div class="col-12 mb-3">
                  <label for="newpass" class="form-label fw-bold">Nueva Contraseña</label>
                  <div class="input-group">
                    <span class="input-group-text password-icon">
                      <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control form-control-lg" id="newpass" name="newpass" required minlength="6" placeholder="Ingresa tu nueva contraseña" />
                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="newpass">
                      <i class="fas fa-eye"></i>
                    </button>
                    <div class="invalid-feedback">
                      La contraseña debe tener al menos 6 caracteres.
                    </div>
                  </div>
                  <div class="password-strength mt-2">
                    <div class="password-strength-bar" id="password-strength-bar"></div>
                  </div>
                  <small class="text-muted">Mínimo 6 caracteres</small>
                </div>

                <div class="col-12 mb-3">
                  <label for="confirmpassword" class="form-label fw-bold">Confirmar Contraseña</label>
                  <div class="input-group">
                    <span class="input-group-text password-icon">
                      <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control form-control-lg" id="confirmpassword" name="confirmpassword" required minlength="6" placeholder="Confirma tu nueva contraseña" />
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
            <div class="card-footer bg-white text-end">
              <button type="reset" class="btn btn-outline-secondary me-2">
                <i class="fas fa-undo me-1"></i> Limpiar
              </button>
              <button type="submit" name="change" class="btn btn-success">
                <i class="fas fa-save me-1"></i> Guardar Cambios
              </button>
            </div>
          </form>
        </div>

        <div class="card shadow-sm password-card">
          <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-dark"></i>Requisitos de seguridad</h5>
          </div>
          <div class="card-body">
            <ul class="list-group list-group-flush">
              <li class="list-group-item border-0"><i class="fas fa-check-circle text-success me-2"></i>Mínimo 6 caracteres</li>
              <li class="list-group-item border-0"><i class="fas fa-check-circle text-success me-2"></i>Se recomienda usar mayúsculas, números y símbolos</li>
              <li class="list-group-item border-0"><i class="fas fa-check-circle text-success me-2"></i>No uses contraseñas obvias o comunes</li>
              <li class="list-group-item border-0"><i class="fas fa-history text-info me-2"></i>Puedes cambiar tu contraseña cada <?= $DIAS_ESPERA ?> días</li>
            </ul>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Validación del formulario
    (() => {
      'use strict'
      const forms = document.querySelectorAll('.needs-validation')
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          
          // Validar que las contraseñas coincidan
          const newpass = document.getElementById('newpass').value;
          const confirmpassword = document.getElementById('confirmpassword').value;
          
          if (newpass !== confirmpassword) {
            document.getElementById('confirmpassword').setCustomValidity("Las contraseñas no coinciden");
            event.preventDefault();
            event.stopPropagation();
          } else {
            document.getElementById('confirmpassword').setCustomValidity("");
          }
          
          form.classList.add('was-validated')
        }, false)
      })
    })();

    // Toggle para mostrar/ocultar contraseña
    document.querySelectorAll('.toggle-password').forEach(button => {
      button.addEventListener('click', function() {
        const target = this.getAttribute('data-target');
        const input = document.getElementById(target);
        const icon = this.querySelector('i');
        
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
      });
    });

    // Indicador de fortaleza de contraseña
    document.getElementById('newpass').addEventListener('input', function() {
      const password = this.value;
      const strengthBar = document.getElementById('password-strength-bar');
      let strength = 0;
      
      if (password.length > 0) strength += 20;
      if (password.length >= 6) strength += 20;
      if (password.match(/[A-Z]/)) strength += 20;
      if (password.match(/[0-9]/)) strength += 20;
      if (password.match(/[^A-Za-z0-9]/)) strength += 20;
      
      strengthBar.style.width = strength + '%';
      
      if (strength < 40) {
        strengthBar.style.backgroundColor = '#dc3545';
      } else if (strength < 80) {
        strengthBar.style.backgroundColor = '#ffc107';
      } else {
        strengthBar.style.backgroundColor = '#28a745';
      }
    });
  </script>
</body>
</html>
