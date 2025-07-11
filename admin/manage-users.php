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

if (isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_plain = $_POST['password'];
    $role = $_POST['role'];
    $mobile = trim($_POST['mobile']);
    $gender = trim($_POST['gender']);
    $address = trim($_POST['address']);

    if (!str_ends_with($email, '@spe.gob.hn')) {
        echo "<script>alert('Solo se permiten correos @spe.gob.hn'); window.location.href = 'manage-users.php';</script>";
        exit;
    }

    if (!preg_match('/^(?=.*\d)(?=.*[a-zA-Z])(?=.*[\W_]).{8,}$/', $password_plain)) {
        echo "<script>alert('La contraseña debe tener al menos 8 caracteres, incluir una letra, un número y un símbolo.'); window.location.href = 'manage-users.php';</script>";
        exit;
    }

    $password = password_hash($password_plain, PASSWORD_DEFAULT);

    if (!preg_match('/^[0-9]{8}$/', $mobile)) {
        echo "<script>alert('Número de teléfono inválido. Debe contener 8 dígitos.'); window.location.href = 'manage-users.php';</script>";
        exit;
    }

    $generos_permitidos = ['Masculino', 'Femenino', 'Otro'];
    if (!in_array($gender, $generos_permitidos)) {
        echo "<script>alert('Género inválido.'); window.location.href = 'manage-users.php';</script>";
        exit;
    }

    if (strlen($address) < 5) {
        echo "<script>alert('Dirección demasiado corta.'); window.location.href = 'manage-users.php';</script>";
        exit;
    }

    $roles_permitidos = ['admin', 'usuario', 'supervisor', 'tecnico'];
    if (!in_array($role, $roles_permitidos)) {
        echo "<script>alert('Rol inválido'); window.location.href = 'manage-users.php';</script>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo "<script>alert('Este correo ya está registrado'); window.location.href = 'manage-users.php';</script>";
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO user (name, email, password, role, mobile, gender, address, posting_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    if ($stmt->execute([$name, $email, $password, $role, $mobile, $gender, $address])) {
        echo "<script>alert('Usuario creado correctamente'); window.location.href = 'manage-users.php';</script>";
    } else {
        echo "<script>alert('Error al crear el usuario'); window.location.href = 'manage-users.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | Gestionar Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../styles/admin.css" rel="stylesheet">
</head>
<body>
<?php include("header.php"); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 p-0 sidebar">
            <?php include("leftbar.php"); ?>
        </div>
        <main class="col-md-9 col-lg-10 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-0"><i class="fas fa-user-cog me-2 text-primary"></i>Gestión de Usuarios</h2>
                    <p class="text-muted mb-0">Administra los usuarios del sistema</p>
                </div>
                <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
                </button>
            </div>
            <div class="card card-custom">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
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
                                        <td><div class='d-flex align-items-center'><div class='user-avatar'><i class='fas fa-user text-muted'></i></div><div><div class='fw-semibold'>" . htmlspecialchars($row['name']) . "</div><small class='text-muted'>" . htmlspecialchars($row['gender']) . "</small></div></div></td>
                                        <td>" . htmlspecialchars($row['email']) . "</td>
                                        <td>" . htmlspecialchars($row['mobile']) . "</td>
                                        <td><span class='badge {$badgeClass} badge-role'>" . htmlspecialchars($row['role']) . "</span></td>
                                        <td>" . date('d/m/Y', strtotime($row['posting_date'])) . "</td>
                                        <td><div class='d-flex gap-2'><a href='edit-user.php?id=" . urlencode($row['id']) . "' class='btn btn-sm btn-outline-primary'><i class='fas fa-edit'></i></a><button class='btn btn-sm btn-outline-danger' data-bs-toggle='modal' data-bs-target='#deleteModal' data-user-id='{$row['id']}' data-user-name='" . htmlspecialchars($row['name']) . "'><i class='fas fa-trash-alt'></i></button></div></td>
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

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="fs-5">¿Eliminar a <strong id="userToDeleteName"></strong>?</p>
                <p class="text-muted small">Al aceptar, este usuario ya no tendrá acceso al sitio de Soporte Técnico.</p>
                <input type="hidden" name="delete_user_id" id="deleteUserId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Aceptar</button>
            </div>
        </form>
    </div>
</div>

<!-- Toast de éxito -->
<?php if (!empty($_SESSION['user_deleted'])): unset($_SESSION['user_deleted']); ?>
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div class="toast align-items-center text-white bg-success border-0 show" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                Usuario eliminado correctamente
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
<?php endif; ?>

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
