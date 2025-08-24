<?php

function mostrarArchivoBadge($archivo) {
    if (empty($archivo)) return;

    $fileUrl = 'uploads/' . $archivo;
    $fileName = htmlspecialchars($archivo);

    // Determinar tipo de archivo por extensión
    $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
    $class = 'badge-other';
    $icon = 'fa-paperclip';

    if (in_array($ext, ['pdf'])) {
        $class = 'badge-pdf';
        $icon = 'fa-file-pdf';
    } elseif (in_array($ext, ['doc', 'docx'])) {
        $class = 'badge-doc';
        $icon = 'fa-file-word';
    } elseif (in_array($ext, ['xls', 'xlsx'])) {
        $class = 'badge-excel';
        $icon = 'fa-file-excel';
    } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        $class = 'badge-img';
        $icon = 'fa-file-image';
    }

    echo "<a href='{$fileUrl}' download class='badge-archivo {$class}' title='{$fileName}'>
            <i class='fas {$icon}'></i> {$fileName}
          </a>";
}
?>
