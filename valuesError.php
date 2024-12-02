<?php
include 'db_connection.php'; // Conexión a la base de datos

try {
    $conn->begin_transaction(); // Inicia una transacción

    // Insertar datos en Usuario
    $usuarios = [
        ['Administrador', 'admin@aquadesign.com', 'admin123', 'administrador', 'activo'],
        ['Maria Lopez', '', 'maria123', 'cliente', 'activo'], // Valor faltante en correo
        ['Carlos Sanchez', 'carlos.sanchez@correo.com', '', 'cliente', 'activo'], // Valor faltante en contraseña
        ['Pedro Ramirez', 'pedro.ramirez@correo.com', 'productor123', 'productor', 'activo'],
        ['Ana Ortega', 'ana.ortega@correo.com', 'logistica123', 'logistica', 'activo']
    ];

    foreach ($usuarios as $usuario) {
        [$nombre, $correo, $password, $rol, $estado] = $usuario;

        if (empty($nombre) || empty($correo) || empty($password) || !in_array($rol, ['administrador', 'cliente', 'productor', 'logistica'])) {
            throw new Exception("Error: Datos faltantes o inválidos para Usuario: $nombre.");
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Error: Correo electrónico inválido para Usuario: $nombre.");
        }

        // Preparar y ejecutar la inserción en la tabla Usuario
        $stmt = $conn->prepare("INSERT INTO Usuario (Nombre, Correo, Contraseña, Rol, Estado) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }

        $stmt->bind_param("sssss", $nombre, $correo, $password, $rol, $estado);
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar el Usuario: " . $stmt->error);
        }

        $stmt->close(); // Cerrar la declaración
    }

    // Confirmar la transacción si todo va bien
    $conn->commit();
    echo "Datos insertados correctamente en la tabla Usuario.";

} catch (Exception $e) {
    // Revertir la transacción si hubo un error
    $conn->rollback();
    echo "Error al insertar datos: " . $e->getMessage();
} finally {
    // Asegurarse de cerrar la conexión correctamente
    if ($conn->ping()) {
        $conn->close();
    }
}
?>
