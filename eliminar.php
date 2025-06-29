<?php
require 'conexion.php';

// Verificamos si llega el ID por la URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Eliminamos la línea correspondiente
    $stmt = $conn->prepare("DELETE FROM texto WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Redireccionamos de vuelta a index
header("Location: index.php");
exit();
