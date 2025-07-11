<?php
session_start();
require("dbconnection.php");
require("checklogin.php");
check_login("usuario");

$page = 'profile';

if (isset($_POST['update'])) {
  $name = $_POST['name'];
  $mobile = $_POST['phone'];
  $gender = $_POST['gender'];
  $address = $_POST['address'];
  $building_id = $_POST['edificio_id'];

  try {
    $stmt = $pdo->prepare("UPDATE user SET name = :name, mobile = :mobile, gender = :gender, address = :address, edificio_id = :edificio_id WHERE email = :email");
    $updated = $stmt->execute([
      ':name' => $name,
      ':mobile' => $mobile,
      ':gender' => $gender,
      ':address' => $address,
      ':edificio_id' => $building_id,
      ':email' => $_SESSION['login']
    ]);

    if ($updated) {
      header("Location: profile.php?actualizado=1");
      exit();
    }
  } catch (PDOException $e) {
    error_log("Error actualizando perfil: " . $e->getMessage());
    $errorMsg = "Ocurrió un error al actualizar el perfil.";
  }
}

$stmt = $pdo->prepare("SELECT * FROM user WHERE email = :email");
$stmt->execute([':email' => $_SESSION['login']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener edificios para el select
$buildings = $pdo->query("SELECT id, name FROM edificios ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Obtener nombre del edificio actual
$buildingName = '';
if (!empty($row['edificio_id'])) {
  $stmtBuilding = $pdo->prepare("SELECT name FROM edificios WHERE id = ?");
  $stmtBuilding->execute([$row['edificio_id']]);
  $building = $stmtBuilding->fetch();
  $buildingName = $building ? $building['name'] : 'No asignado';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mi Perfil</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <!-- Estilos personalizados -->
  <link href="styles/user.css" rel="stylesheet">
</head>

<body class="bg-light">

  <?php include 'header.php'; ?>

  <div class="container-fluid" style="padding-top: 1rem;">
    <div class="row">

      <!-- Sidebar -->
      <div class="col-lg-2 p-0">
        <?php include 'leftbar.php'; ?>
      </div>

      <!-- Contenido principal -->
      <main class="col-lg-10 py-4 px-4 mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h1 class="mb-0"><i class="fas fa-user-circle text-primary me-2"></i> Mi Perfil</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mt-2">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home me-1"></i> Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Perfil</li>
              </ol>
            </nav>
          </div>
          <a href="change-password.php" class="btn btn-outline-primary">
            <i class="fas fa-lock me-1"></i> Ayuda contraseña
          </a>
        </div>

        <?php if (!empty($errorMsg)): ?>
          <div class="alert alert-danger d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div><?= htmlspecialchars($errorMsg) ?></div>
          </div>
        <?php endif; ?>

        <?php if ($row): ?>
          <!-- Tarjeta de perfil -->
          <div class="profile-card mb-4">
            <div class="profile-header d-flex align-items-center">
              <img src="assets/img/user-profile.png" alt="Avatar" class="profile-avatar rounded-circle me-3">
              <div>
                <h3 class="mb-1"><?= htmlspecialchars($row['name']) ?></h3>
                <p class="mb-0 text-light"><?= htmlspecialchars($row['email']) ?></p>
                <small class="text-light opacity-75">Miembro desde <?= date('d/m/Y', strtotime($row['posting_date'])) ?></small>
              </div>
            </div>
            
            <form method="post" class="p-4">
              <!-- Sección de información personal -->
              <div class="mb-4">
                <h5 class="mb-3"><i class="fas fa-user-tag text-primary me-2"></i> Información Personal</h5>
                
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Nombre completo</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="fas fa-user"></i></span>
                      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <label class="form-label">Correo institucional</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                      <input type="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>" disabled>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="fas fa-phone"></i></span>
                      <input type="tel" name="phone" maxlength="10" class="form-control" value="<?= htmlspecialchars($row['mobile']) ?>">
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <label class="form-label">Género</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                      <select name="gender" class="form-select" required>
                        <option value="masculino" <?= $row['gender'] == 'masculino' ? 'selected' : '' ?>>Masculino</option>
                        <option value="femenino" <?= $row['gender'] == 'femenino' ? 'selected' : '' ?>>Femenino</option>
                        <option value="otro" <?= $row['gender'] == 'otro' ? 'selected' : '' ?>>Otro</option>
                      </select>
                    </div>
                  </div>
                  
                  <div class="col-12">
                    <label class="form-label">Dirección</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                      <textarea name="address" rows="3" class="form-control"><?= htmlspecialchars($row['address']) ?></textarea>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Sección de edificio -->
              <div class="mb-4">
                <h5 class="mb-3"><i class="fas fa-building text-primary me-2"></i> Ubicación</h5>
                
                <?php if (!empty($buildingName)): ?>
                  <div class="info-badge d-flex align-items-center mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>
                      <strong>Edificio actual:</strong> <?= htmlspecialchars($buildingName) ?>
                    </div>
                  </div>
                <?php endif; ?>
                
                <div class="mb-3">
                  <label class="form-label">Seleccionar edificio</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-warehouse"></i></span>
                    <select name="building_id" class="form-select" required>
                      <option value="">-- Selecciona un edificio --</option>
                      <?php foreach ($buildings as $building): ?>
                        <option value="<?= $building['id'] ?>" <?= ($row['edificio_id'] == $building['id']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($building['name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>
              
              <!-- Botones de acción -->
              <div class="d-flex justify-content-between pt-3 border-top">
                <button type="reset" class="btn btn-outline-secondary">
                  <i class="fas fa-undo me-1"></i> Restablecer
                </button>
                <button type="submit" name="update" class="btn btn-save text-white">
                  <i class="fas fa-save me-1"></i> Guardar cambios
                </button>
              </div>
            </form>
          </div>
        <?php else: ?>
          <div class="alert alert-warning d-flex align-items-center">
            <i class="fas fa-exclamation-circle me-2"></i>
            <div>No se pudo cargar la información del perfil.</div>
          </div>
        <?php endif; ?>
      </main>
    </div>
  </div>

  <!-- Toast de éxito -->
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
    <div id="toastPerfilActualizado" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center">
          <i class="fas fa-check-circle me-2"></i>
          <span>Perfil actualizado correctamente</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <?php if (isset($_GET['actualizado'])): ?>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const toastEl = document.getElementById('toastPerfilActualizado');
        const toast = new bootstrap.Toast(toastEl, {
          animation: true,
          autohide: true,
          delay: 3000
        });
        toast.show();
      });
    </script>
  <?php endif; ?>
</body>
</html>