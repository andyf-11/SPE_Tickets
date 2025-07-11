<?php
session_start();
require_once("../checklogin.php");
check_login("admin");
require("../dbconnection.php");

$page = "manage-passwords";

// Marcar solicitud como atendida
if (isset($_GET['atender']) && is_numeric($_GET['atender'])) {
    $id = intval($_GET['atender']);
    $stmt = $pdo->prepare("UPDATE password_request SET status = 'atendida' WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['msg'] = "Solicitud marcada como atendida.";
    header("Location: password-requests.php");
    exit();
}

// Obtener solicitudes
$stmt = $pdo->query("SELECT * FROM password_request ORDER BY soli_date DESC");
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Solicitudes de Contraseña</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="../styles/admin.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f6f9;
      margin-left: 250px;
    }
  </style>
</head>
<body>

<?php include("header.php");?>

<div class="container-fluid">
  <div class="row">
    <!-- Leftbar (columna izquierda) -->
    <div class="col-md-3 fixed-leftbar p-0 mt-5">
      <?php include("leftbar.php"); ?>
    </div>

    <!-- Contenido principal (columna derecha) -->
    <div class="col-md-9 p-4 mt-5">
      <h3 class="mb-4 mt-4"><i class="fa-solid fa-key me-2 text-primary"></i>Solicitudes de Recuperación de Contraseña</h3>

      <?php if (!empty($_SESSION['msg'])): ?>
        <div class="alert alert-success">
          <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
        </div>
      <?php endif; ?>

      <?php if (count($solicitudes) === 0): ?>
        <div class="alert alert-info">No hay solicitudes en este momento.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-bordered table-hover bg-white">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Correo</th>
                <th>Motivo</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($solicitudes as $i => $s): ?>
                <tr>
                  <td><?= $i + 1 ?></td>
                  <td><?= htmlspecialchars($s['correo']) ?></td>
                  <td><?= nl2br(htmlspecialchars($s['motivo'])) ?></td>
                  <td><?= date('d/m/Y H:i', strtotime($s['soli_date'])) ?></td>
                  <td>
                    <span class="badge <?= $s['status'] === 'pendiente' ? 'bg-warning text-dark' : 'bg-success' ?>">
                      <?= ucfirst($s['status']) ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($s['status'] === 'pendiente'): ?>
                      <a href="?atender=<?= $s['id'] ?>" class="btn btn-sm btn-outline-success">Marcar como atendida</a>
                    <?php else: ?>
                      <span class="text-muted">✓ Atendida</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
