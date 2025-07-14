<?php
session_start();
require("dbconnection.php");
require("checklogin.php");
require("../fpdf/fpdf.php");
check_login("admin");

$adminName = $_SESSION['name'] ?? 'Administrador';
$fechaHoy = date('d/m/Y');
$mesActual = date('m');
$anioActual = date('Y');

function contarTicketsPorEstado($pdo, $estado, $mes, $anio) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ticket WHERE status = :status AND MONTH(posting_date) = :mes AND YEAR(posting_date) = :anio");
    $stmt->execute([':status' => $estado, ':mes' => $mes, ':anio' => $anio]);
    return (int)$stmt->fetchColumn();
}

$ticketsAbiertos = contarTicketsPorEstado($pdo, 'Abierto', $mesActual, $anioActual);
$ticketsEnProceso = contarTicketsPorEstado($pdo, 'En Proceso', $mesActual, $anioActual);
$ticketsCerrados = contarTicketsPorEstado($pdo, 'Cerrado', $mesActual, $anioActual);
$totalTickets = $ticketsAbiertos + $ticketsEnProceso + $ticketsCerrados;

$stmtTecnicos = $pdo->prepare("SELECT u.name, COUNT(t.ticket_id) AS tickets_resueltos FROM user u LEFT JOIN ticket t ON t.assigned_to = u.id AND t.status = 'Cerrado' AND MONTH(t.posting_date) = :mes AND YEAR(t.posting_date) = :anio WHERE u.role = 'tecnico' GROUP BY u.id, u.name ORDER BY tickets_resueltos DESC LIMIT 5");
$stmtTecnicos->execute(['mes' => $mesActual, 'anio' => $anioActual]);
$tecnicos = $stmtTecnicos->fetchAll(PDO::FETCH_ASSOC);

$stmtSolicitudes = $pdo->query("SELECT COUNT(*) FROM application_approv WHERE status = 'pendiente'");
$solicitudesNuevas = (int)$stmtSolicitudes->fetchColumn();

$stmtTecnicosSolicitudes = $pdo->query("SELECT DISTINCT u.name FROM application_approv aa INNER JOIN user u ON aa.tech_id = u.id WHERE aa.status = 'pendiente'");
$tecnicosSolicitudes = $stmtTecnicosSolicitudes->fetchAll(PDO::FETCH_ASSOC);

$totalDias = cal_days_in_month(CAL_GREGORIAN, $mesActual, $anioActual);
$visitasUnicas = array_fill(1, $totalDias, 0);
$visitasTotales = array_fill(1, $totalDias, 0);

$stmtVU = $pdo->prepare("SELECT DAY(uc.logindatetime) AS dia, COUNT(DISTINCT uc.user_id) AS total FROM usercheck uc INNER JOIN user u ON uc.user_id = u.id WHERE u.role = 'usuario' AND MONTH(uc.logindatetime) = :mes AND YEAR(uc.logindatetime) = :anio GROUP BY dia");
$stmtVU->execute(['mes' => $mesActual, 'anio' => $anioActual]);
foreach ($stmtVU as $row) $visitasUnicas[(int)$row['dia']] = (int)$row['total'];

$stmtVT = $pdo->prepare("SELECT DAY(uc.logindatetime) AS dia, COUNT(*) AS total FROM usercheck uc INNER JOIN user u ON uc.user_id = u.id WHERE u.role = 'usuario' AND MONTH(uc.logindatetime) = :mes AND YEAR(uc.logindatetime) = :anio GROUP BY dia");
$stmtVT->execute(['mes' => $mesActual, 'anio' => $anioActual]);
foreach ($stmtVT as $row) $visitasTotales[(int)$row['dia']] = (int)$row['total'];

$pdf = new FPDF();
$pdf->AddPage();

// Logo y encabezado
$pdf->Image('../assets/img/Logo-Gobierno_small.png', 10, 10, 30);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Reporte Mensual de Administracion - SPE', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, "Fecha: $fechaHoy", 0, 1, 'C');
$pdf->Cell(0, 6, "Generado por: $adminName", 0, 1, 'C');
$pdf->Ln(8);

// Resumen de Tickets
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, mb_convert_encoding('Resumen de Tickets del Mes', 'ISO-8859-1','utf-8'),0, 1);

// Resumen de Tickets
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, mb_convert_encoding('Resumen de Tickets del Mes', 'ISO-8859-1', 'UTF-8'), 0, 1);


function barra($pdf, $label, $valor, $total, $color) {
    $porcentaje = $total > 0 ? ($valor / $total) * 100 : 0;
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(35, 8, $label, 0);
    $pdf->SetFillColor(...$color);
    $pdf->Cell($porcentaje, 8, '', 0, 0, '', true);
    $pdf->Cell(20, 8, round($porcentaje) . '%', 0, 1);
}

barra($pdf, 'Abiertos', $ticketsAbiertos, $totalTickets, [0, 123, 255]);
barra($pdf, 'En Proceso', $ticketsEnProceso, $totalTickets, [255, 193, 7]);
barra($pdf, 'Cerrados', $ticketsCerrados, $totalTickets, [40, 167, 69]);
$pdf->Ln(5);

// Técnicos
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, utf8_decode('Top Técnicos con Tickets Cerrados'), 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetX(50);
$pdf->Cell(100, 7, 'Nombre', 1);
$pdf->Cell(30, 7, 'Cerrados', 1, 1);
$pdf->SetFont('Arial', '', 10);
foreach ($tecnicos as $tec) {
    $pdf->SetX(50);
    $pdf->Cell(100, 7, utf8_decode($tec['name']), 1);
    $pdf->Cell(30, 7, $tec['tickets_resueltos'], 1, 1);
}
$pdf->Ln(5);

// Solicitudes
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Solicitudes Pendientes', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, "Total: $solicitudesNuevas", 0, 1, 'C');
if ($tecnicosSolicitudes) {
    foreach ($tecnicosSolicitudes as $tec) {
        $pdf->Cell(0, 6, '- ' . utf8_decode($tec['name']), 0, 1, 'C');
    }
} else {
    $pdf->Cell(0, 6, 'Sin solicitudes pendientes.', 0, 1, 'C');
}
$pdf->Ln(5);

// Visitas
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Estadisticas de Visitas del Mes', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetX(50);
$pdf->Cell(20, 7, 'Dia', 1);
$pdf->Cell(40, 7, 'Visitas Unicas', 1);
$pdf->Cell(40, 7, 'Inicios Totales', 1, 1);
$pdf->SetFont('Arial', '', 10);
for ($i = 1; $i <= $totalDias; $i++) {
    $pdf->SetX(50);
    $pdf->Cell(20, 6, $i, 1);
    $pdf->Cell(40, 6, $visitasUnicas[$i], 1);
    $pdf->Cell(40, 6, $visitasTotales[$i], 1, 1);
}

$pdf->Output('I', 'reporte_mensual_admin.pdf');
