<?php
require_once("dbconnection.php");
require("../fpdf/fpdf.php");  // Ruta según dónde tengas FPDF

// Obtener filtro desde GET
$filtro = $_GET['filtro'] ?? 'todos';
$condicionFecha = '';

switch ($filtro) {
    case 'hoy':
        $condicionFecha = "AND DATE(posting_date) = CURDATE()";
        break;
    case '7dias':
        $condicionFecha = "AND posting_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case '30dias':
        $condicionFecha = "AND posting_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        break;
    case 'todos':
    default:
        $condicionFecha = "";
        break;
}

// Función para obtener tickets por estado
function obtenerTickets($pdo, $estado, $condicionFecha) {
    // Asegura que cualquier 'posting_date' sea 't.posting_date' para evitar ambigüedad
    $condicionFecha = str_replace('posting_date', 't.posting_date', $condicionFecha);

    $sql = "SELECT t.*, u.name as tecnico_nombre 
            FROM ticket t 
            LEFT JOIN user u ON t.technician_id = u.id 
            WHERE t.status = :estado $condicionFecha 
            ORDER BY t.posting_date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['estado' => $estado]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Crear PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->image('../assets/img/Logo-Gobierno_small.png', 90, 10, 30);
$pdf->Ln(40);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Reporte de Tickets - Supervisor', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Filtro aplicado: ' . ucfirst($filtro), 0, 1, 'C');
$pdf->Ln(5);

// Estados a mostrar
$estados = ['Abierto', 'En Proceso', 'Cerrado'];

foreach ($estados as $estado) {
    $tickets = obtenerTickets($pdo, $estado, $condicionFecha);
    
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(
        $estado === 'Abierto' ? 173 : ($estado === 'En Proceso' ? 255 : 144), // R
        $estado === 'Abierto' ? 216 : ($estado === 'En Proceso' ? 255 : 238), // G
        $estado === 'Abierto' ? 230 : ($estado === 'En Proceso' ? 179 : 144)  // B
    );
    $pdf->Cell(0, 10, "Tickets $estado", 1, 1, 'L', true);
    
    if (empty($tickets)) {
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Sin tickets en esta categoría.', 1, 1);
    } else {
        // Cabecera de tabla
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(25, 8, 'ID', 1);
        $pdf->Cell(90, 8, 'Asunto', 1);
        if ($estado === 'En Proceso') {
            $pdf->Cell(40, 8, 'Técnico', 1);
        }
        $pdf->Cell(75, 8, 'Fecha', 1);
        $pdf->Ln();

        // Filas
        $pdf->SetFont('Arial', '', 10);
        foreach ($tickets as $t) {
            $pdf->Cell(25, 7, $t['ticket_id'], 1);
            $pdf->Cell(90, 7, mb_convert_encoding($t['subject'], 'ISO-8859-1', 'UTF-8'), 1);
            if ($estado === 'En Proceso') {
                $pdf->Cell(60, 7, mb_convert_encoding($t['tecnico_nombre'] ?? 'N/A', 'ISO-8859', 'UTF-8'), 1);
            }
            $pdf->Cell(75, 7, $t['posting_date'], 1);
            $pdf->Ln();
        }
    }

    $pdf->Ln(8);
}

$pdf->Output("I", "reporte_tickets.pdf");
