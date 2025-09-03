<?php
session_start();
require("../checklogin.php");
check_login("supervisor");
require_once("../dbconnection.php");

/**
 * 1) Tomar el ID de GET (al entrar) o de POST (al guardar)
 */
$userid_get  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$userid_post = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$userid = $userid_post ?: $userid_get;

if ($userid === null || $userid === false) {
    die("ID de usuario no especificado o inválido.");
}

/**
 * 2) Actualización
 */
if (isset($_POST['update'])) {
  $name        = $_POST['name']    ?? '';
  $email       = $_POST['email']   ?? '';
  $contact     = $_POST['mobile']  ?? '';
  $address     = $_POST['address'] ?? '';
  $gender      = $_POST['gender']  ?? '';
  $edificio_id = isset($_POST['edificio_id']) ? (int)$_POST['edificio_id'] : null;
  $area_id     = isset($_POST['area_id'])     ? (int)$_POST['area_id']     : null;

  $sql = "UPDATE user 
          SET name = :name, email = :email, mobile = :contact, gender = :gender, 
              address = :address, edificio_id = :edificio_id, area_id = :area_id 
          WHERE id = :userid";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':name',        $name);
  $stmt->bindParam(':contact',     $contact);
  $stmt->bindParam(':gender',      $gender);
  $stmt->bindParam(':address',     $address);
  $stmt->bindParam(':email',       $email);
  $stmt->bindParam(':edificio_id', $edificio_id, PDO::PARAM_INT);
  $stmt->bindParam(':area_id',     $area_id,     PDO::PARAM_INT);
  $stmt->bindParam(':userid',      $userid,      PDO::PARAM_INT);

  if ($stmt->execute()) {
    $toast = true;
  }
}

// 3) Obtener todas las áreas
$areas = $pdo->query("SELECT id, name FROM areas ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// 4) Traer datos del usuario
$sql = "SELECT u.*, e.name as edificio_nombre 
        FROM user u 
        LEFT JOIN edificios e ON u.edificio_id = e.id 
        WHERE u.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $userid, PDO::PARAM_INT);
$stmt->execute();
$rw = $stmt->fetch(PDO::FETCH_ASSOC);
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
  <link href="../../styles/roles-layouts/edit-users-techs.css" rel="stylesheet">
  <style>
    #leftbar {
      position: fixed; top: 56px; left: 0; width: 250px; height: calc(100vh - 56px);
      background-color: #fff; border-right: 1px solid #dee2e6; z-index: 1030; overflow-y: auto; font-weight: 400;
    }
    #main-content { margin-left: 250px; padding-top: 70px; min-height: 100vh; }
  </style>
</head>
<body>
  <?php include("../header.php"); ?>

  <?php if (isset($toast) && $toast): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
      <div id="successToast" class="toast align-items-center text-white bg-success border-0 show" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">Datos actualizados correctamente.</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
      </div>
    </div>
    <script>
      setTimeout(() => { window.location.href = "../tech-layout.php"; }, 3000);
    </script>
  <?php endif; ?>

  <div class="container-fluid">
    <div class="row">
     

      <main class="col-md-9 col-lg-10 main-content mt-5">
        <?php if ($rw): ?>
          <div class="profile-card">
            <div class="form-header">
              <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                  <h3 class="mb-0"><i class="fas fa-user-edit me-2 text-primary"></i>Editar Usuario</h3>
                  <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="home.php">Inicio</a></li>
                      <li class="breadcrumb-item"><a href="manage-users.php">Técnicos</a></li>
                      <li class="breadcrumb-item active">Editar</li>
                    </ol>
                  </nav>
                </div>
                <?php if (!empty($rw['edificio_nombre'])): ?>
                  <span class="building-badge mt-2 mt-md-0">
                    <i class="fas fa-building me-1"></i> <?= htmlspecialchars($rw['edificio_nombre']) ?>
                  </span>
                <?php endif; ?>
              </div>
            </div>

            <div class="form-body">
              <form name="muser" method="post" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                <!-- IMPORTANTÍSIMO: ID oculto para el POST -->
                <input type="hidden" name="id" value="<?= (int)$rw['id'] ?>">

                <div class="row justify-content-center">
                  <div class="col-lg-8">
                    <div class="text-center mb-4">
                      <div class="user-avatar"><i class="fas fa-user"></i></div>
                      <h4><?= htmlspecialchars($rw['name']) ?></h4>
                      <p class="text-muted">ID: <?= htmlspecialchars($rw['id']) ?></p>
                    </div>

                    <div class="row g-3">
                      <div class="col-md-6">
                        <label for="name" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($rw['name']) ?>" required>
                        <div class="invalid-feedback">Por favor ingrese el nombre</div>
                      </div>

                      <div class="col-md-6">
                        <label for="email" class="form-label">Correo Institucional</label>
                        <input type="email" class="form-control bg-light" id="email" name="email" value="<?= htmlspecialchars($rw['email']) ?>" readonly>
                      </div>

                      <div class="col-md-6">
                        <label for="mobile" class="form-label">Teléfono</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="fas fa-phone"></i></span>
                          <input type="text" class="form-control" id="mobile" name="mobile" value="<?= htmlspecialchars($rw['mobile']) ?>" required>
                        </div>
                        <div class="invalid-feedback">Por favor ingrese el teléfono</div>
                      </div>

                      <div class="col-md-6">
                        <label for="gender" class="form-label">Género</label>
                        <select class="form-select" id="gender" name="gender" required>
                          <option value="">Seleccione...</option>
                          <option value="Femenino"  <?= ($rw['gender'] === 'Femenino')  ? 'selected' : '' ?>>Femenino</option>
                          <option value="Masculino" <?= ($rw['gender'] === 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                          <option value="Otro"      <?= ($rw['gender'] === 'Otro')      ? 'selected' : '' ?>>Otro</option>
                        </select>
                        <div class="invalid-feedback">Por favor seleccione el género</div>
                      </div>

                      <div class="col-md-6">
                        <label class="form-label">Área</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                          <select name="area_id" class="form-select" required>
                            <option value="">-- Selecciona un área --</option>
                            <?php foreach ($areas as $areaItem): ?>
                              <option value="<?= $areaItem['id'] ?>" <?= ($rw['area_id'] == $areaItem['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($areaItem['name']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label for="building" class="form-label">Edificio</label>
                        <select class="form-select" id="building" name="edificio_id" required>
                          <option value="">Seleccione un edificio</option>
                          <?php
                          $buildings = $pdo->query("SELECT id, name FROM edificios ORDER BY name ASC");
                          while ($b = $buildings->fetch(PDO::FETCH_ASSOC)) {
                            $selected = ($rw['edificio_id'] == $b['id']) ? 'selected' : '';
                            echo "<option value='{$b['id']}' $selected>" . htmlspecialchars($b['name']) . "</option>";
                          }
                          ?>
                        </select>
                        <div class="invalid-feedback">Por favor seleccione un edificio</div>
                      </div>

                      <div class="col-md-6">
                        <label class="form-label">Rol</label>
                        <input type="text" class="form-control bg-light" value="<?= ucfirst(htmlspecialchars($rw['role'])) ?>" readonly>
                      </div>

                      <div class="col-12 text-center mt-4">
                        <button type="submit" name="update" class="btn btn-primary-custom me-2">
                          <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                        <a href="../tech-layout.php" class="btn btn-outline-secondary">
                          <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        <?php else: ?>
          <div class="alert alert-warning d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2"></i> Usuario no encontrado
          </div>
        <?php endif; ?>
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
          if (!form.checkValidity()) { event.preventDefault(); event.stopPropagation(); }
          form.classList.add('was-validated');
        }, false);
      });
    })();
  </script>
</body>
</html>
