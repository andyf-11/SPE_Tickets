<?php
// Incluye PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once __DIR__ . '/assets/config/mailer_config.php';

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
$errors = [];

$email = '';
$password = '';

// Obtener lista de edificios
$stmt = $pdo->query("SELECT id, name FROM edificios ORDER BY name");
$edificios = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $phone = trim($_POST['phone']);
  $gender = $_POST['gender'];
  $edificio_id = $_POST['edificio_id'] ?? null;

  // Validar dominio permitido
  $allowed_domains = ['@spe.gob.hn', '@gmail.com'];
  $domain_valid = false;
  foreach ($allowed_domains as $domain) {
    if (str_ends_with($email, $domain)) {
      $domain_valid = true;
      break;
    }
  }

  if (!$domain_valid) {
    $errors[] = "Este correo no es permitido. Solo se permiten los dominios: " . implode(", ", $allowed_domains);
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "El correo no es válido.";
  }

  // Validar contraseña segura
  if (
    strlen($password) < 8 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[0-9]/', $password) ||
    !preg_match('/[\W_]/', $password)
  ) {
    $errors[] = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.";
  }

  // Validar correo existente
  $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
  $stmt->execute([$email]);
  if ($stmt->fetch()) {
    $errors[] = "El correo ya está registrado.";
  }

  // Validar edificio
  if (empty($edificio_id) || !ctype_digit($edificio_id)) {
    $errors[] = "Debes seleccionar un edificio válido.";
  }

  if (empty($errors)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $rol = 'usuario';
    $token = bin2hex(random_bytes(32));

    $stmt = $pdo->prepare("INSERT INTO user (name, email, password, mobile, gender, role, edificio_id, is_verified, verification_token) 
    VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)");
    $stmt->execute([$name, $email, $hashedPassword, $phone, $gender, $rol, $edificio_id, $token]);

    if (sendVerificationEmail($email, $name, $token)) {
      $success = true;
      $messages[] = "¡Usuario registrado exitosamente! Revisa tu correo electrónico para activar la cuenta.";
    } else {
      $messages[] = "Error al enviar el correo de verificación.";
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/registration.css" rel="stylesheet">

</head>

<body>
  <div class="container py-4">
    <div class="register-card">
      <div class="register-header">
        <img src="assets/img/Logo-Gobierno-en-Vertical.png" alt="Logo SPE" class="logo">
        <h2 class="mb-0">Crear una cuenta</h2>
      </div>

      <div class="register-body">
        <p class="text-center mb-4">¿Ya tienes una cuenta? <a href="login1.php" class="login-link">Inicia sesión
            aquí</a></p>

        <!-- Mostrar mensajes -->
        <?php if (!empty($messages)): ?>
          <?php foreach ($messages as $msg): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show"
              role="alert">
              <?php echo $msg; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <!-- Formulario de registro -->
        <form method="POST" action="" onsubmit="return checkpass();">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="name" class="form-label">Nombre completo</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control" id="name" name="name" required autocomplete="name"
                  placeholder="Ingrese su nombre completo">
              </div>
            </div>

            <div class="col-md-6">
              <label for="email" class="form-label">Correo institucional</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email" required autocomplete="email"
                  placeholder="usuario@spe.gob.hn">
              </div>
              <small class="text-muted">Solo correos @spe.gob.hn</small>
            </div>

            <div class="col-md-6">
              <label for="password" class="form-label">Contraseña</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" required
                  autocomplete="new-password" placeholder="Crea una contraseña segura">
                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
              <div class="password-strength mt-2" id="password-strength"></div>
              <div class="password-hint">
                <small>Debe contener 8+ caracteres, mayúsculas, minúsculas, números y símbolos</small>
              </div>
            </div>

            <div class="col-md-6">
              <label for="cpassword" class="form-label">Confirmar contraseña</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="cpassword" name="cpassword" required
                  autocomplete="new-password" placeholder="Confirma tu contraseña">
                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="cpassword">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>

            <div class="col-md-6">
              <label for="phone" class="form-label">Número de contacto</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                <input type="tel" class="form-control" id="phone" name="phone" required autocomplete="tel"
                  placeholder="Ingrese su número telefónico">
              </div>
            </div>

            <div class="col-md-6">
              <label for="gender" class="form-label">Género</label>
              <select class="form-select" id="gender" name="gender" required>
                <option value="">Seleccione una opción</option>
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
                <option value="Otro">Otro</option>
              </select>
            </div>

            <div class="col-12">
              <label for="edificio_id" class="form-label">Edificio</label>
              <select class="form-select" id="edificio_id" name="edificio_id" required>
                <option value="">Seleccione un edificio</option>
                <?php foreach ($edificios as $edificio): ?>
                  <option value="<?= htmlspecialchars($edificio['id']) ?>"><?= htmlspecialchars($edificio['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-12 mt-4">
              <button type="submit" class="btn btn-primary w-100 py-3">
                <i class="bi bi-person-plus me-2"></i> Registrar cuenta
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Toggle password visibility
      document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function () {
          const targetId = this.getAttribute('data-target');
          const input = document.getElementById(targetId);
          const icon = this.querySelector('i');

          if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
          } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
          }
        });
      });

      // Password strength indicator
      const passwordInput = document.getElementById('password');
      const strengthBar = document.getElementById('password-strength');

      passwordInput.addEventListener('input', function () {
        const password = this.value;
        let strength = 0;

        // Check length
        if (password.length >= 8) strength += 1;
        if (password.length >= 12) strength += 1;

        // Check for uppercase, lowercase, numbers, symbols
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[a-z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[\W_]/.test(password)) strength += 1;

        // Update strength bar
        let color, width;
        if (strength <= 2) {
          color = '#dc3545';
          width = '30%';
        } else if (strength <= 4) {
          color = '#fd7e14';
          width = '60%';
        } else {
          color = '#28a745';
          width = '100%';
        }

        strengthBar.style.backgroundColor = color;
        strengthBar.style.width = width;
      });
    });

    function checkpass() {
      const password = document.getElementById("password").value;
      const confirmPassword = document.getElementById("cpassword").value;
      const email = document.getElementById("email").value;

      // Lista de dominios permitidos
      const allowedDomains = ["@spe.gob.hn", "@gmail.com"]; // Agrega más si lo deseas

      // Validar dominio del correo
      let domainValid = allowedDomains.some(domain => email.endsWith(domain));
      if (!domainValid) {
        alert("Solo se permiten correos con los dominios: " + allowedDomains.join(", "));
        return false;
      }

      // Validar contraseña
      const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
      if (!passwordRegex.test(password)) {
        alert("La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un símbolo.");
        return false;
      }

      // Confirmar contraseña
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