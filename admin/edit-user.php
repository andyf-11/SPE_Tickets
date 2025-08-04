<?php
session_start();
require("checklogin.php");
check_login("admin");
require_once("dbconnection.php");

if (isset($_POST['update'])) {
  $name = $_POST['name'];
  $email = $_POST['email'];  // Unificado con el form
  $contact = $_POST['mobile'];      // Unificado con el form
  $address = $_POST['address'];
  $gender = $_POST['gender'];
  $edificio_id = $_POST['edificio_id'];
  $userid = $_GET['id'];

  $sql = "UPDATE user 
          SET name = :name, email = :email, mobile = :contact, gender = :gender, address = :address, edificio_id = :edificio_id 
          WHERE id = :userid";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':name', $name);
  $stmt->bindParam(':contact', $contact);
  $stmt->bindParam(':gender', $gender);
  $stmt->bindParam(':address', $address);
  $stmt->bindParam(':email', $email);
  $stmt->bindParam(':edificio_id', $edificio_id, PDO::PARAM_INT);
  $stmt->bindParam(':userid', $userid, PDO::PARAM_INT);

  if ($stmt->execute()) {
    $toast = true;
  }

}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <title>SPE | Editar Usuario</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link href="../styles/roles-layouts/edit-user.css" rel="stylesheet">
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

<body>
  <?php include("header.php"); ?>

  <?php if (isset($toast) && $toast): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
      <div id="successToast" class="toast align-items-center text-white bg-success border-0 show" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            Datos actualizados correctamente.
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
            aria-label="Cerrar"></button>
        </div>
      </div>
    </div>
    <script>
      setTimeout(() => {
        window.location.href = "manage-users.php";
      }, 3000);
    </script>
  <?php endif; ?>


  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-2 p-0">
        <?php include("leftbar.php"); ?>
      </div>

      <!-- Contenido principal -->
      <main class="col-md-9 col-lg-10 main-content mt-5">
        <?php
        $userid = $_GET['id'];
        $sql = "SELECT u.*, e.name as edificio_nombre FROM user u LEFT JOIN edificios e ON u.edificio_id = e.id WHERE u.id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $userid, PDO::PARAM_INT);
        $stmt->execute();

        if ($rw = $stmt->fetch(PDO::FETCH_ASSOC)) {
          ?>
          <div class="profile-card">
            <!-- Encabezado -->
            <div class="form-header">
              <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                  <h3 class="mb-0"><i class="fas fa-user-edit me-2 text-primary"></i>Editar Usuario</h3>
                  <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="home.php">Inicio</a></li>
                      <li class="breadcrumb-item"><a href="manage-users.php">Usuarios</a></li>
                      <li class="breadcrumb-item active">Editar</li>
                    </ol>
                  </nav>
                </div>
                <?php if (!empty($rw['edificio_nombre'])): ?>
                  <span class="building-badge mt-2 mt-md-0">
                    <i class="fas fa-building me-1"></i>
                    <?= htmlspecialchars($rw['edificio_nombre']) ?>
                  </span>
                <?php endif; ?>
              </div>
            </div>

            <!-- Formulario -->
            <div class="form-body">
              <form name="muser" method="post" action="" enctype="multipart/form-data" class="needs-validation"
                novalidate>
                <div class="row justify-content-center">
                  <div class="col-lg-8">
                    <div class="text-center mb-4">
                      <div class="user-avatar">
                        <i class="fas fa-user"></i>
                      </div>
                      <h4><?= htmlspecialchars($rw['name']) ?></h4>
                      <p class="text-muted">ID: <?= htmlspecialchars($rw['id']) ?></p>
                    </div>

                    <div class="row g-3">
                      <div class="col-md-6">
                        <label for="name" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="name" name="name"
                          value="<?= htmlspecialchars($rw['name']) ?>" required>
                        <div class="invalid-feedback">
                          Por favor ingrese el nombre
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="email" class="form-label">Correo Institucional</label>
                        <input type="email" class="form-control bg-light" id="email" name="email"
                          value="<?= htmlspecialchars($rw['email']) ?>" readonly>
                      </div>

                      <div class="col-md-6">
                        <label for="mobile" class="form-label">Teléfono</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="fas fa-phone"></i></span>
                          <input type="text" class="form-control" id="mobile" name="mobile"
                            value="<?= htmlspecialchars($rw['mobile']) ?>" required>
                        </div>
                        <div class="invalid-feedback">
                          Por favor ingrese el teléfono
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="gender" class="form-label">Género</label>
                        <select class="form-select" id="gender" name="gender" required>
                          <option value="">Seleccione...</option>
                          <option value="masculino" <?= ($rw['gender'] == 'masculino') ? 'selected' : '' ?>>Masculino
                          </option>
                          <option value="femenino" <?= ($rw['gender'] == 'femenino') ? 'selected' : '' ?>>Femenino</option>
                          <option value="otro" <?= ($rw['gender'] == 'otro') ? 'selected' : '' ?>>Otro</option>
                        </select>
                        <div class="invalid-feedback">
                          Por favor seleccione el género
                        </div>
                      </div>

                      <div class="col-12">
                        <label for="address" class="form-label">Dirección</label>
                        <textarea class="form-control" id="address" name="address" rows="3"
                          required><?= htmlspecialchars($rw['address']) ?></textarea>
                        <div class="invalid-feedback">
                          Por favor ingrese la dirección
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="building" class="form-label">Edificio</label>
                        <select class="form-select" id="building" name="edificio_id" required>
                          <option value="">Seleccione un edificio</option>
                          <?php
                          $buildings = $pdo->query("SELECT id, name FROM edificios ORDER BY name ASC");
                          while ($b = $buildings->fetch(PDO::FETCH_ASSOC)) {
                            $selected = ($rw['building_id'] == $b['id']) ? 'selected' : '';
                            echo "<option value='{$b['id']}' $selected>" . htmlspecialchars($b['name']) . "</option>";
                          }
                          ?>
                        </select>
                        <div class="invalid-feedback">
                          Por favor seleccione un edificio
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label class="form-label">Rol</label>
                        <input type="text" class="form-control bg-light"
                          value="<?= ucfirst(htmlspecialchars($rw['role'])) ?>" readonly>
                      </div>

                      <div class="col-12 text-center mt-4">
                        <button type="submit" name="update" class="btn btn-primary-custom me-2">
                          <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                        <a href="manage-users.php" class="btn btn-outline-secondary">
                          <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        <?php } else { ?>
          <div class="alert alert-warning d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Usuario no encontrado
          </div>
        <?php } ?>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      'use strict';
      var forms = document.querySelectorAll('.needs-validation');
      Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();
  </script>
</body>

</html>