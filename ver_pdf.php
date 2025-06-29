<?php
require 'fpdf/fpdf.php';
require 'conexion.php';

// Consultar las líneas de la base de datos
$resultado = $conn->query("SELECT * FROM texto ORDER BY id ASC");

// Calcular la cantidad de filas para ajustar la altura
$num_filas = $resultado->num_rows;
$altura_fila = 7; // Altura estimada por fila (puedes ajustar si usas MultiCell)
$altura_extra = 40; // Espacio para encabezado y márgenes
$ancho_hoja = 100;
$altura_total = ($num_filas * $altura_fila) + $altura_extra;
if ($altura_total < 80) { $altura_total = 80; } // Mínimo 80mm

$pdf = new FPDF('L', 'mm', array($ancho_hoja, $altura_total)); // Cambiado a orientación horizontal
$pdf->AddPage();
$pdf->SetMargins(2, 4, 2); // Márgenes pequeños
$pdf->SetAutoPageBreak(false, 0); // No saltar de página

// Encabezado
$pdf->SetFont('Courier','B',14);
$pdf->Cell(0,8,utf8_decode('Listas Enlazadas'),0,1,'C');
$pdf->SetDrawColor(180,180,180);
$pdf->Line(2, $pdf->GetY(), 98, $pdf->GetY());
$pdf->Ln(3);

// Encabezados de tabla
$pdf->SetFont('Courier','B',9);
$pdf->Cell(8,7,'#',0,0,'C');
$pdf->Cell(26,7,'Contenido',0,0,'C');
$pdf->Cell(33,7,'Creado',0,0,'C');
$pdf->Cell(33,7,'Editado',0,1,'C');

// Contenido
$pdf->SetFont('Courier','',12);
$indice = 0;
while ($fila = $resultado->fetch_assoc()) {
    $contenido = $fila['contenido'];
    $creado = $fila['fecha_creacion'] ? $fila['fecha_creacion'] : '-';
    $editado = $fila['fecha_edicion'] ? $fila['fecha_edicion'] : '-';
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    // MultiCell para contenido
    $pdf->SetXY($x + 8, $y);
    $pdf->MultiCell(26, 3.5, utf8_decode($contenido), 0, 'L');
    $h_contenido = $pdf->GetY() - $y;
    // Creado
    $pdf->SetXY($x + 8 + 26, $y);
    if ($creado !== '-') {
        $fecha_creado = date('Y-m-d', strtotime($creado));
        $hora_creado = date('H:i:s', strtotime($creado));
        $pdf->MultiCell(33, 3.5, $fecha_creado . "\n" . $hora_creado, 0, 'L');
    } else {
        $pdf->MultiCell(33, 7, '-', 0, 'L');
    }
    $h_creado = $pdf->GetY() - $y;
    // Editado
    $pdf->SetXY($x + 8 + 26 + 33, $y);
    if ($editado !== '-') {
        $fecha_editado = date('Y-m-d', strtotime($editado));
        $hora_editado = date('H:i:s', strtotime($editado));
        $pdf->MultiCell(33, 3.5, $fecha_editado . "\n" . $hora_editado, 0, 'L');
    } else {
        $pdf->MultiCell(33, 7, '-', 0, 'L');
    }
    $h_editado = $pdf->GetY() - $y;
    // Calcular la altura máxima
    $h_max = max($h_contenido, $h_creado, $h_editado);
    // Imprimir el índice en la primera línea
    $pdf->SetXY($x, $y);
    $pdf->Cell(8, $h_max, $indice, 0, 0, 'C');
    // Mover el cursor a la siguiente fila
    $pdf->SetY($y + $h_max);
    $indice++;
}

$pdf->Output('I', 'lista_lineas.pdf');
?> 