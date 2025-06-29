<?php
require 'conexion.php';

date_default_timezone_set('America/La_Paz'); // Zona horaria de Bolivia

// Verificamos si se envió texto
if (isset($_POST['contenido']) && !empty(trim($_POST['contenido']))) {
    $contenido = trim($_POST['contenido']);
    $fecha_creacion = date('Y-m-d H:i:s');

    // Insertamos en la base de datos
    $stmt = $conn->prepare("INSERT INTO texto (contenido, fecha_creacion) VALUES (?, ?)");
    $stmt->bind_param("ss", $contenido, $fecha_creacion);
    $stmt->execute();
}

// Redireccionamos de vuelta a index
header("Location: index.php");
exit();
