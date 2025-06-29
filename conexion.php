<?php
// Configuración de conexión a MySQL (XAMPP)
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "editor_texto";

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
