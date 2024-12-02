<?php
$servername = "localhost"; // Servidor MySQL
$username = "root";        // Usuario MySQL (por defecto, root)
$password = "";            // Contraseña (vacía por defecto en XAMPP)
$dbname = "AquaDesign";    // Nombre de tu base de datos

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
