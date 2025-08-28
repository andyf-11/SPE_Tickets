<?php
session_start();
require_once("../dbconnection.php");
require("checklogin.php");
require_once '../assets/data/notifications_helper.php'; // helper
check_login("usuario");

$page = 'create-ticket';

// Obtener lista de edificios
$stmt_edificios = $pdo->query("SELECT id, name FROM edificios WHERE name IN ('Santa Esmeralda', 'Palmira') ORDER BY name ASC");
$edificios = $stmt_edificios->fetchAll(PDO::FETCH_ASSOC);

$show_success_toast = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = null;

    // ID de ticket (puede ser AUTO_INCREMENT)
    $count_my_page = "../hitcounter.txt";
    $hits = file($count_my_page);
    $hits[0]++;
    file_put_contents($count_my_page, $hits[0]);
    $tid = $hits[0];

    $email = $_SESSION['login'];
    $subject = trim($_POST['subject']);
    $ticket = trim($_POST['description']);
    $edificio_id = $_POST['edificio_id'] ?? null;
    $st = "Abierto";
    $pdate = date('Y-m-d H:i:s');

    // Manejo de archivos adjuntos
    $archivo_nombre = null;
    $max_size = 100 * 1024 * 1024; //100MB
    $allowed_types = [
        'pdf'=>'application/pdf','doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'=>'application/vnd.ms-excel','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','txt'=>'text/plain','zip'=>'application/zip'
    ];

    if(isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK){
        $archivo_tmp = $_FILES['archivo']['tmp_name'];
        $archivo_name = preg_replace('/[^A-Za-z0-9._-]/','_', $_FILES['archivo']['name']);
        $archivo_size = $_FILES['archivo']['size'];
        $archivo_ext = strtolower(pathinfo($archivo_name, PATHINFO_EXTENSION));

        if($archivo_size > $max_size){
            $error = "El archivo es demasiado grande. Máximo permitido: 100MB";
        } elseif(!array_key_exists($archivo_ext, $allowed_types)){
            $error = "Tipo de archivo no permitido.";
        } else {
            $archivo_nombre = time().'_'.$archivo_name;
            $archivo_destino = '../uploads/'.$archivo_nombre;

            if(!is_dir('../uploads')) mkdir('../uploads',0777,true);

            if(!move_uploaded_file($archivo_tmp, $archivo_destino)){
                $error = "Error al guardar el archivo en el servidor.";
            }
        }
    }

    if(!$subject || !$ticket || !$edificio_id){
        $error = "Por favor completa todos los campos.";
    }

    if(!$error){
        $stmt = $pdo->prepare("
            INSERT INTO ticket(ticket_id, email_id, subject, ticket, status, posting_date, edificio_id, archivo) 
            VALUES(:tid, :email, :subject, :ticket, :status, :pdate, :edificio_id, :archivo)
        ");

        if($stmt->execute([
            ':tid'=>$tid, ':email'=>$email, ':subject'=>$subject, ':ticket'=>$ticket,
            ':status'=>$st, ':pdate'=>$pdate, ':edificio_id'=>$edificio_id, ':archivo'=>$archivo_nombre
        ])){
            notificarCreacionTicket($tid); // helper
            $show_success_toast = true;
            $_POST = [];
        } else {
            $error = "Error al registrar el ticket. Intente nuevamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Crear Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="../styles/user.css" rel="stylesheet">

    <style>
        body { font-family:'Poppins'; background: linear-gradient(to left,#0ED2F7,#B2FEFA); overflow-x:hidden;}
        #leftbar{position:fixed;top:41px;left:0;width:250px;height:calc(100vh - 41px);background:#fff;border-right:1px solid #dee2e6;overflow-y:auto;}
        main.main-content{margin-left:250px;padding:2rem;min-height:calc(100vh - 56px);}
        @media(max-width:767px){#leftbar{position:relative;width:100%;height:auto;} main.main-content{margin-left:0;}}
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    <div id="leftbar"><?php include('leftbar.php'); ?></div>

    <main class="main-content">
        <!-- Formulario de creación de ticket -->
        <div class="form-container mt-4">
            <div class="form-header">
                <h2 class="mb-0"><i class="fas fa-ticket me-2 text-primary"></i> Crear Nuevo Ticket</h2>
                <p class="text-muted mb-0">Complete todos los campos para registrar un nuevo ticket de soporte</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="mb-4">
                    <h5 class="section-title"><i class="fas fa-info-circle me-2"></i>Información Básica</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="subject" class="form-label fw-semibold">Asunto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" required
                            value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edificio_id" class="form-label fw-semibold">Edificio/Localización <span class="text-danger">*</span></label>
                            <select class="form-select" id="edificio_id" name="edificio_id" required>
                                <option value="" disabled <?= empty($_POST['edificio_id'])?'selected':'' ?>>Seleccione un edificio</option>
                                <?php foreach($edificios as $edificio): ?>
                                    <option value="<?= $edificio['id'] ?>" <?= (isset($_POST['edificio_id']) && $_POST['edificio_id']==$edificio['id'])?'selected':'' ?>>
                                        <?= htmlspecialchars($edificio['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="section-title"><i class="fas fa-align-left me-2"></i>Descripción Detallada</h5>
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Descripción <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?= isset($_POST['description'])?htmlspecialchars($_POST['description']):'' ?></textarea>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="section-title"><i class="fas fa-paperclip me-2"></i>Archivos Adjuntos</h5>
                    <input type="file" id="archivo" name="archivo" style="display:none;">
                    <button type="button" class="btn btn-outline-primary me-3" id="select-file-btn"><i class="fas fa-upload me-2"></i>Seleccionar archivo</button>
                    <span id="file-display" class="text-muted">Ningún archivo seleccionado
                        <button type="button" id="remove-file" class="btn btn-sm btn-outline-danger ms-2" style="display:none;"><i class="fas fa-times"></i></button>
                    </span>
                    <div class="form-text">Adjunte archivos relevantes</div>
                </div>

                <div class="d-flex justify-content-between pt-4 mt-3 border-top">
                    <button type="reset" class="btn btn-outline-secondary"><i class="fas fa-eraser me-1"></i> Limpiar</button>
                    <button type="submit" class="btn btn-submit text-white"><i class="fas fa-paper-plane me-1"></i> Enviar Ticket</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Toast éxito -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080">
        <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>Ticket creado exitosamente. Redirigiendo...
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if($show_success_toast): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const toastElement = document.getElementById('successToast');
                const toast = new bootstrap.Toast(toastElement,{animation:true,autohide:true,delay:3000});
                toast.show();
                setTimeout(()=>{window.location.href='user_dashboard.php';},3000);
            });
        </script>
    <?php endif; ?>

    <script>
        const archivoInput = document.getElementById('archivo');
        const selectBtn = document.getElementById('select-file-btn');
        const fileDisplay = document.getElementById('file-display');
        const removeBtn = document.getElementById('remove-file');

        selectBtn.addEventListener('click', ()=>archivoInput.click());
        archivoInput.addEventListener('change', ()=>{
            if(archivoInput.files.length>0){
                fileDisplay.childNodes[0].textContent = archivoInput.files[0].name;
                removeBtn.style.display='inline-block';
            }else{
                fileDisplay.childNodes[0].textContent='Ningún archivo seleccionado';
                removeBtn.style.display='none';
            }
        });
        removeBtn.addEventListener('click', ()=>{
            archivoInput.value='';
            fileDisplay.childNodes[0].textContent='Ningún archivo seleccionado';
            removeBtn.style.display='none';
        });
    </script>

    <!-- Scripts de notificaciones Socket.IO -->
    <script src="https://cdn.socket.io/4.6.1/socket.io.min.js"></script>
    <script>
        const userId = <?= json_encode($_SESSION['user_id']) ?>;
        const role = <?= json_encode($_SESSION['user_role']) ?>;
    </script>
    <script src="../chat-server/notifications.js"></script>
</body>
</html>
