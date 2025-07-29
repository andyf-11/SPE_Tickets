<?php
session_start();
require("dbconnection.php");
require_once("checklogin.php");
check_login("admin");

// Eliminar usuario si se confirma
if (isset($_POST['delete_user_id'])) {
    $userIdToDelete = $_POST['delete_user_id'];
    $stmt = $pdo->prepare("DELETE FROM user WHERE id = ?");
    if ($stmt->execute([$userIdToDelete])) {
        $_SESSION['user_deleted'] = true;
    }
    header("Location: manage-users.php");
    exit;
}

// Crear usuario
if (isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $gender = trim($_POST['gender']);
    $edificio = $_POST['edificio'];
    $area = $_POST['area'];
    $role = $_POST['role'];
    $password_plain = $_POST['password'];

    if (!str_ends_with($email, '@spe.gob.hn')) {
        echo "<script>alert('Solo se permiten correos @spe.gob.hn'); window.location.href = 'manage-users.php';</script>";
        exit;
    }

    if (!preg_match('/^(?=.*\d)(?=.*[a-zA-Z])(?=.*[\W_]).{8,}$/', $password_plain)) {
        echo "<script>alert('La contraseña debe tener al menos 8 caracteres, incluir una letra, un número y un símbolo.'); window.location.href = 'manage-users.php';</script>";
        exit;
    }

    $password = password_hash($password_plain, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO user (name, email, mobile, gender, edificio_id, area_id, role, password, posting_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    if ($stmt->execute([$name, $email, $mobile, $gender, $edificio, $area, $role, $password])) {
        echo "<script>alert('Usuario creado correctamente'); window.location.href = 'manage-users.php';</script>";
    } else {
        echo "<script>alert('Error al crear el usuario'); window.location.href = 'manage-users.php';</script>";
    }
}

$edificios = $pdo->query("SELECT id, name FROM edificios ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$areas = $pdo->query("SELECT id, name FROM areas ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Admin | Gestión de Usuarios</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Estilos externos -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link href="../styles/roles-layouts/manage-users.css" rel="stylesheet">
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

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-lg-2 p-0">
        <?php include("leftbar.php"); ?>
      </div>

      <!-- Contenido principal -->
      <main class="col-lg-10 px-4 py-4 mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="fw-semibold text-primary"><i class="fas fa-user-cog me-2"></i>Gestión de Usuarios</h4>
            <small class="text-muted">Administra los usuarios del sistema</small>
          </div>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
          </button>
        </div>

        <div class="card shadow-sm border-0">
          <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <h5 class="mb-0">Listado de Usuarios</h5>
            <span class="badge bg-light text-dark">
              <i class="fas fa-database me-1"></i>
              <?php echo $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn(); ?> registros
            </span>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Contacto</th>
                    <th>Rol</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $stmt = $pdo->query("SELECT * FROM user ORDER BY posting_date DESC");
                  $cnt = 1;
                  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $badgeClass = match (strtolower($row['role'])) {
                      'admin' => 'badge-admin',
                      'tecnico' => 'badge-tech',
                      'supervisor' => 'badge-supervisor',
                      default => 'badge-user',
                    };
                    echo "<tr>
                      <td class='text-center'>{$cnt}</td>
                      <td>
                        <div class='d-flex align-items-center'>
                          <div class='user-avatar'><i class='fas fa-user text-muted'></i></div>
                          <div>
                            <div class='fw-semibold'>" . htmlspecialchars($row['name']) . "</div>
                            <small class='text-muted'>" . htmlspecialchars($row['gender']) . "</small>
                          </div>
                        </div>
                      </td>
                      <td>" . htmlspecialchars($row['email']) . "</td>
                      <td>" . htmlspecialchars($row['mobile']) . "</td>
                      <td><span class='badge {$badgeClass} badge-role'>" . htmlspecialchars($row['role']) . "</span></td>
                      <td>" . date('d/m/Y', strtotime($row['posting_date'])) . "</td>
                      <td>
                        <div class='d-flex gap-2'>
                          <a href='edit-user.php?id=" . urlencode($row['id']) . "' class='btn btn-sm btn-outline-primary'>
                            <i class='fas fa-edit'></i>
                          </a>
                          <button class='btn btn-sm btn-outline-danger' data-bs-toggle='modal' data-bs-target='#deleteModal'
                            data-user-id='{$row['id']}' data-user-name='" . htmlspecialchars($row['name']) . "'>
                            <i class='fas fa-trash-alt'></i>
                          </button>
                        </div>
                      </td>
                    </tr>";
                    $cnt++;
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Modal: Nuevo Usuario -->
  <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form method="post" class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Nuevo Usuario</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre:</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Correo institucional:</label>
              <input type="email" name="email" class="form-control" required pattern="^[a-zA-Z0-9._%+-]+@spe\.gob\.hn$" title="Debe ser un correo @spe.gob.hn">
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono:</label>
              <input type="text" name="mobile" class="form-control" pattern="[0-9]{8}" required title="Debe contener 8 dígitos">
            </div>
            <div class="col-md-6">
              <label class="form-label">Género:</label>
              <select name="gender" class="form-select" required>
                <option value="">Seleccione...</option>
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
                <option value="Otro">Otro</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Edificio:</label>
              <select name="edificio" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php foreach ($edificios as $e): ?>
                  <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Área:</label>
              <select name="area" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php foreach ($areas as $a): ?>
                  <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Rol:</label>
              <select name="role" class="form-select" required>
                <option value="admin">Admin</option>
                <option value="tecnico">Técnico</option>
                <option value="supervisor">Supervisor</option>
                <option value="usuario">Usuario</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Contraseña:</label>
              <input type="text" name="password" class="form-control" minlength="8" required pattern="^(?=.*\d)(?=.*[a-zA-Z])(?=.*[\W_]).{8,}$" title="Debe tener al menos 8 caracteres, una letra, un número y un símbolo">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="add_user" class="btn btn-success">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal de Confirmación de Eliminación -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="post" class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Confirmar Eliminación</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="fs-5">¿Eliminar a <strong id="userToDeleteName"></strong>?</p>
          <p class="text-muted small">Al aceptar, este usuario ya no tendrá acceso al sistema.</p>
          <input type="hidden" name="delete_user_id" id="deleteUserId">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Aceptar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Toast de Éxito -->
  <?php if (!empty($_SESSION['user_deleted'])): unset($_SESSION['user_deleted']); ?>
    <div class="toast-container position-fixed top-0 end-0 p-3">
      <div class="toast align-items-center text-white bg-success border-0 show" role="alert">
        <div class="d-flex">
          <div class="toast-body">Usuario eliminado correctamente</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const deleteModal = document.getElementById("deleteModal");
      deleteModal.addEventListener("show.bs.modal", event => {
        const button = event.relatedTarget;
        const userId = button.getAttribute("data-user-id");
        const userName = button.getAttribute("data-user-name");
        document.getElementById("deleteUserId").value = userId;
        document.getElementById("userToDeleteName").textContent = userName;
      });
    });
  </script>
</body>

</html>
