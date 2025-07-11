<?php
// Configuración conexión PDO
$host = 'localhost';
$db = 'crm-gestion';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
  die("Error de conexión: " . $e->getMessage());
}

$success = false;
$messages = [];

$email = '';
$password = '';

// Obtener lista de edificios para el select
$stmt = $pdo->query("SELECT id, name FROM edificios ORDER BY name");
$edificios = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $phone = trim($_POST['phone']);
  $gender = $_POST['gender'];
  $edificio_id = $_POST['edificio_id'] ?? null;
  $allowed_domain = '@spe.gob.hn';

  $errors = [];

  // Validar dominio del correo
  if (!str_ends_with($email, $allowed_domain)) {
    $errors[] = "El correo debe ser del dominio $allowed_domain";
  }

  // Validar formato del correo
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "El correo no es válido.";
  }

  // Validar seguridad de la contraseña
  if (
    strlen($password) < 8 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[0-9]/', $password) ||
    !preg_match('/[\W_]/', $password)
  ) {
    $errors[] = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.";
  }

  // Validar si el correo ya está registrado
  $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
  $stmt->execute([$email]);
  if ($stmt->fetch()) {
    $errors[] = "El correo ya está registrado.";
  }

  // Validar que haya seleccionado edificio y sea un ID válido
  if (empty($edificio_id) || !ctype_digit($edificio_id)) {
    $errors[] = "Debes seleccionar un edificio válido.";
  }

  if (empty($errors)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Asignar el rol 'usuario' por defecto
    $rol = 'usuario';

    // Insertar nuevo usuario incluyendo el edificio
    $stmt = $pdo->prepare("INSERT INTO user (name, email, password, mobile, gender, role, edificio_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hashedPassword, $phone, $gender, $rol, $edificio_id]);

    $success = true;
    $messages[] = "¡Usuario registrado exitosamente! Redireccionando al inicio de sesión...";

    echo "<div class='alert alert-success text-center'>" . implode('<br>', $messages) . "</div>";
    echo "<script>setTimeout(() => { window.location.href = 'login1.php'; }, 2500);</script>";
  } else {
    foreach ($errors as $error) {
      echo "<div class='alert alert-danger'>$error</div>";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro - SPE</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <style>
    body {
      background-color: #1ac7b2c2;
    }

    .login-container {
      max-width: 600px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .login-logo img {
      display: block;
      margin: 0 auto 20px;
      max-width: 180px;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="login-container">
      <div class="login-logo">
        <img src="assets/img/Logo-Gobierno-en-Vertical.png" alt="Logo SPE">
      </div>

      <h3 class="text-center mb-3">Crear una Cuenta</h3>
      <p class="text-center text-muted">¿Ya tienes una cuenta? <a href="login1.php">Inicia sesión</a></p>

      <!-- Mostrar mensajes -->
      <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $msg): ?>
          <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?> fade show" role="alert">
            <?php echo $msg; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- Formulario de registro -->
      <form method="POST" action="" onsubmit="return checkpass();">
        <div class="mb-3">
          <label for="name" class="form-label">Nombre completo</label>
          <input type="text" class="form-control" id="name" name="name" required autocomplete="name" />
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Correo institucional</label>
          <input type="email" class="form-control" id="email" name="email" required autocomplete="email" />
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password" />
        </div>

        <div class="mb-3">
          <label for="cpassword" class="form-label">Confirmar contraseña</label>
          <input type="password" class="form-control" id="cpassword" name="cpassword" required autocomplete="new-password" />
        </div>

        <div class="mb-3">
          <label for="phone" class="form-label">Número de contacto</label>
          <input type="tel" class="form-control" id="phone" name="phone" required autocomplete="tel" />
        </div>

        <div class="mb-4">
          <label for="gender" class="form-label">Género</label>
          <select class="form-select" id="gender" name="gender" required>
            <option value="">Seleccione una opción</option>
            <option value="male">Masculino</option>
            <option value="female">Femenino</option>
            <option value="other">Otro</option>
          </select>
        </div>

        <!-- NUEVO SELECT EDIFICIO -->
        <div class="mb-4">
          <label for="edificio_id" class="form-label">Edificio</label>
          <select class="form-select" id="edificio_id" name="edificio_id" required>
            <option value="">Seleccione un edificio</option>
            <?php foreach ($edificios as $edificio): ?>
              <option value="<?= htmlspecialchars($edificio['id']) ?>"><?= htmlspecialchars($edificio['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary rounded-pill">Registrar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function checkpass() {
      const password = document.getElementById("password").value;
      const confirmPassword = document.getElementById("cpassword").value;
      const email = document.getElementById("email").value;
      const allowedDomain = "@spe.gob.hn";

      if (!email.endsWith(allowedDomain)) {
        alert("Solo se permiten correos con el dominio " + allowedDomain);
        return false;
      }

      const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
      if (!passwordRegex.test(password)) {
        alert("La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo.");
        return false;
      }

      if (password !== confirmPassword) {
        alert("Las contraseñas no coinciden.");
        return false;
      }

      return true;
    }

    <?php if ($success): ?>
      // Redirigir al login después de 3 segundos si el registro fue exitoso
      setTimeout(function () {
        window.location.href = "login1.php";
      }, 3000);
    <?php endif; ?>
  </script>
</body>

</html>
