<?php
include 'db_connection.php'; // Conexión a la base de datos

// Limpia las tablas en el orden correcto (de las dependientes a las principales)
$conn->query("SET FOREIGN_KEY_CHECKS = 0"); // Desactiva las restricciones de claves foráneas temporalmente
$conn->query("TRUNCATE TABLE Detalle_Pedido");
$conn->query("TRUNCATE TABLE Pedido");
$conn->query("TRUNCATE TABLE Cliente");
$conn->query("TRUNCATE TABLE Usuario");
$conn->query("TRUNCATE TABLE Proveedor_Material");
$conn->query("TRUNCATE TABLE Proveedor");
$conn->query("TRUNCATE TABLE Inventario");
$conn->query("TRUNCATE TABLE Material");
$conn->query("TRUNCATE TABLE Producto");
$conn->query("SET FOREIGN_KEY_CHECKS = 1"); // Reactiva las restricciones de claves foráneas

echo "Tablas limpiadas correctamente.<br>";

$conn->close();
?>
