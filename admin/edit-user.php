<?php
session_start();
require("checklogin.php");
check_login("admin");
require_once("dbconnection.php");

if (isset($_POST['update'])) {
  $name = $_POST['name'];
  $altemail = $_POST['alt_email'];  // Unificado con el form
  $contact = $_POST['mobile'];      // Unificado con el form
  $address = $_POST['address'];
  $gender = $_POST['gender'];
  $building_id = $_POST['building_id'];
  $userid = $_GET['id'];

  $sql = "UPDATE user 
          SET name = :name, alt_email = :altemail, mobile = :contact, gender = :gender, address = :address, building_id = :building_id 
          WHERE id = :userid";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':name', $name);
  $stmt->bindParam(':contact', $contact);
  $stmt->bindParam(':gender', $gender);
  $stmt->bindParam(':address', $address);
  $stmt->bindParam(':altemail', $altemail);
  $stmt->bindParam(':building_id', $building_id, PDO::PARAM_INT);
  $stmt->bindParam(':userid', $userid, PDO::PARAM_INT);

  if ($stmt->execute()) {
    echo "<script>alert('Data Updated'); location.replace(document.referrer)</script>";
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
  
  <style>
    :root {
      --primary-color: #4361ee;
      --secondary-color: #3a0ca3;
      --light-bg: #f8f9fa;
    }
    
    body {
      background-color: var(--light-bg);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .sidebar {
      height: calc(100vh - 56px);
      overflow-y: auto;
      background: white;
      box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    }
    
    .main-content {
      padding: 2rem;
    }
    
    .profile-card {
      border: none;
      border-radius: 10px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.05);
      background: white;
    }
    
    .form-header {
      border-bottom: 1px solid rgba(0,0,0,0.05);
      padding: 1.25rem 1.5rem;
    }
    
    .form-body {
      padding: 1.5rem;
    }
    
    .form-control, .form-select {
      border-radius: 8px;
      padding: 0.5rem 0.75rem;
      border: 1px solid #dee2e6;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
    }
    
    .btn-primary-custom {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      border-radius: 50px;
      padding: 0.5rem 1.5rem;
      color: white;
    }
    
    .btn-primary-custom:hover {
      background-color: var(--secondary-color);
      border-color: var(--secondary-color);
      color: white;
    }
    
    .user-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background-color: #e9ecef;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      font-size: 2rem;
      color: #6c757d;
    }
    
    .invalid-feedback {
      font-size: 0.85rem;
    }
    
    .building-badge {
      background-color: #f8f9fa;
      color: var(--primary-color);
      font-weight: 500;
      padding: 0.35rem 0.75rem;
      border-radius: 50px;
      display: inline-flex;
      align-items: center;
    }
  </style>
</head>

<body>
  <?php include("header.php"); ?>

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-2 p-0 sidebar">
        <?php include("leftbar.php"); ?>
      </div>

      <!-- Contenido principal -->
      <main class="col-md-9 col-lg-10 main-content">
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
              <div class="d-flex justify-content-between align-items-center">
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
                  <span class="building-badge">
                    <i class="fas fa-building me-1"></i>
                    <?= htmlspecialchars($rw['edificio_nombre']) ?>
                  </span>
                <?php endif; ?>
              </div>
            </div>
            
            <!-- Cuerpo del formulario -->
            <div class="form-body">
              <form name="muser" method="post" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
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
                          <option value="masculino" <?= ($rw['gender'] == 'masculino') ? 'selected' : '' ?>>Masculino</option>
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
                        <select class="form-select" id="building" name="building_id" required>
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
                        <input type="text" class="form-control bg-light" value="<?= ucfirst(htmlspecialchars($rw['role'])) ?>" readonly>
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
    (function() {
      'use strict'
      var forms = document.querySelectorAll('.needs-validation')
      Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
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