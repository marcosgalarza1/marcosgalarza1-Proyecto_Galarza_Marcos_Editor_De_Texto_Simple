<?php
require 'conexion.php';

date_default_timezone_set('America/La_Paz'); // Zona horaria de Bolivia

if (isset($_POST['id']) && isset($_POST['contenido'])) {
    $id = intval($_POST['id']);
    $contenido = trim($_POST['contenido']);
    if ($contenido !== '') {
        // Obtener el contenido actual
        $stmt = $conn->prepare("SELECT contenido FROM texto WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($contenido_actual);
        $stmt->fetch();
        $stmt->close();
        // Solo actualizar si el contenido cambió
        if ($contenido !== $contenido_actual) {
            $fecha_edicion = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("UPDATE texto SET contenido = ?, fecha_edicion = ? WHERE id = ?");
            $stmt->bind_param("ssi", $contenido, $fecha_edicion, $id);
            $stmt->execute();
        }
    }
}
// Mantener búsqueda si aplica
$extra = '';
if (isset($_GET['busqueda'])) {
    $extra = '?busqueda=' . urlencode($_GET['busqueda']);
}
header("Location: index.php$extra");
exit(); 