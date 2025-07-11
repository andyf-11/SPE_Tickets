<?php
session_start();
require_once("checklogin.php");
check_login("usuario");
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
  <title>SPE Cambiar Contraseña</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="../assets/css/style.css" rel="stylesheet" />
  <!-- Puedes conservar responsive.css y custom-icon-set.css si los usas -->

  <script>
    function valid() {
      const oldpass = document.form1.oldpass.value.trim();
      const newpass = document.form1.newpass.value.trim();
      const confirmpassword = document.form1.confirmpassword.value.trim();

      if (!oldpass) {
        alert("Campo de contraseña anterior vacío.");
        document.form1.oldpass.focus();
        return false;
      }
      if (!newpass) {
        alert("Nuevo campo de contraseña vacío.");
        document.form1.newpass.focus();
        return false;
      }
      if (!confirmpassword) {
        alert("Campo confirmar contraseña vacío.");
        document.form1.confirmpassword.focus();
        return false;
      }
      if (newpass.length < 6) {
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
  </script>
</head>

<body>
  <?php include("header.php"); ?>

  <div class="page-container d-flex">
    <?php include("leftbar.php"); ?>

    <main class="flex-grow-1 p-4">
      <div class="container-fluid">
        <h3 class="mb-4">Cambiar Contraseña</h3>

        <?php if (!empty($_SESSION['msg1'])): ?>
          <div class="alert alert-<?php echo htmlspecialchars($_SESSION['msg_type'] ?? 'info'); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['msg1']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
          </div>
          <?php $_SESSION['msg1'] = ""; $_SESSION['msg_type'] = ""; ?>
        <?php endif; ?>

        <form name="form1" method="post" action="" onsubmit="return valid();" class="needs-validation" novalidate>
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="row mb-3 align-items-center">
                <label for="oldpass" class="col-sm-4 col-form-label">Contraseña Actual</label>
                <div class="col-sm-8 col-md-6 col-lg-5">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-unlock-alt"></i></span>
                    <input type="password" class="form-control" id="oldpass" name="oldpass" required minlength="6" />
                    <div class="invalid-feedback">
                      Ingresa tu contraseña actual (mínimo 8 caracteres).
                    </div>
                  </div>
                </div>
              </div>

              <div class="row mb-3 align-items-center">
                <label for="newpass" class="col-sm-4 col-form-label">Nueva Contraseña</label>
                <div class="col-sm-8 col-md-6 col-lg-5">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                    <input type="password" class="form-control" id="newpass" name="newpass" required minlength="6" />
                    <div class="invalid-feedback">
                      Ingresa la nueva contraseña (mínimo 6 caracteres).
                    </div>
                  </div>
                </div>
              </div>

              <div class="row mb-3 align-items-center">
                <label for="confirmpassword" class="col-sm-4 col-form-label">Confirmar Contraseña</label>
                <div class="col-sm-8 col-md-6 col-lg-5">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                    <input type="password" class="form-control" id="confirmpassword" name="confirmpassword" required minlength="6" />
                    <div class="invalid-feedback">
                      Confirma la nueva contraseña.
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer text-end">
              <button type="reset" class="btn btn-secondary me-2">Resetear</button>
              <button type="submit" name="change" class="btn btn-primary">Cambiar</button>
            </div>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
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
  </script>
</body>

</html>
