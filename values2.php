<?php
include 'db_connection.php'; // Conexión a la base de datos

try {
    $conn->begin_transaction(); // Inicia una transacción

    // Insertar datos en Usuario
    $usuarios = [
        ['Administrador', 'admin@aquadesign.com', 'admin123', 'administrador', 'activo'],
        ['Luis Gonzalez', 'luis.gonzalez@correo.com', 'luis123', 'cliente', 'activo'],
        ['Sofia Martinez', 'sofia.martinez@correo.com', 'sofia456', 'cliente', 'activo'],
        ['Jorge Perez', 'jorge.perez@correo.com', 'productor123', 'productor', 'activo'],
        ['Karla Diaz', 'karla.diaz@correo.com', 'logistica123', 'logistica', 'activo']
    ];

    foreach ($usuarios as $usuario) {
        [$nombre, $correo, $password, $rol, $estado] = $usuario;

        if (empty($nombre) || !filter_var($correo, FILTER_VALIDATE_EMAIL) || empty($password) || !in_array($rol, ['administrador', 'cliente', 'productor', 'logistica'])) {
            throw new Exception("Error: Datos inválidos para Usuario: $correo.");
        }

        $stmt = $conn->prepare("INSERT INTO Usuario (Nombre, Correo, Contraseña, Rol, Estado) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nombre, $correo, $password, $rol, $estado);
        $stmt->execute();
    }

    // Insertar datos en Cliente
    $clientes = [
        [2, 'Avenida Universidad 123, CDMX', '555-7654321'],
        [3, 'Boulevard Miguel Aleman 456, Puebla', '222-1122334']
    ];

    foreach ($clientes as $cliente) {
        [$id_usuario, $direccion, $telefono] = $cliente;

        if (!filter_var($id_usuario, FILTER_VALIDATE_INT) || empty($direccion) || empty($telefono)) {
            throw new Exception("Error: Datos inválidos para Cliente.");
        }

        $stmt = $conn->prepare("INSERT INTO Cliente (ID_Usuario, Direccion, Telefono) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_usuario, $direccion, $telefono);
        $stmt->execute();
    }

    // Insertar datos en Producto
    $productos = [
        ['Pecera Redonda', 'Minimalista', 'Mediana', 1600.00, 20, 'Pecera moderna y funcional.'],
        ['Pecera Cuadrada', 'Vintage', 'Grande', 1900.50, 12, 'Pecera con estilo antiguo y clásico.'],
        ['Pecera Hexagonal', 'Creativo', 'Pequeña', 1100.25, 25, 'Diseño único con formas creativas.'],
        ['Pecera Tropical', 'Exótico', 'Extra Grande', 2700.00, 8, 'Diseño exótico con elementos tropicales.']
    ];

    foreach ($productos as $producto) {
        [$nombre, $tipo_diseno, $tamano, $precio, $stock, $descripcion] = $producto;

        if (empty($nombre) || empty($tipo_diseno) || empty($tamano) || !filter_var($precio, FILTER_VALIDATE_FLOAT) || !filter_var($stock, FILTER_VALIDATE_INT)) {
            throw new Exception("Error: Datos inválidos para Producto: $nombre.");
        }

        $stmt = $conn->prepare("INSERT INTO Producto (Nombre, Tipo_Diseno, Tamano, Precio, Stock, Descripcion) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdis", $nombre, $tipo_diseno, $tamano, $precio, $stock, $descripcion);
        $stmt->execute();
    }

    // Insertar datos en Pedido
    $pedidos = [
        [1, 3400.75, 'pendiente', 'Avenida Universidad 123, CDMX'],
        [2, 4500.00, 'en_proceso', 'Boulevard Miguel Aleman 456, Puebla']
    ];

    foreach ($pedidos as $pedido) {
        [$id_cliente, $total, $estado, $direccion_entrega] = $pedido;

        if (!filter_var($id_cliente, FILTER_VALIDATE_INT) || !filter_var($total, FILTER_VALIDATE_FLOAT) || empty($estado) || empty($direccion_entrega)) {
            throw new Exception("Error: Datos inválidos para Pedido.");
        }

        $stmt = $conn->prepare("INSERT INTO Pedido (ID_Cliente, Total, Estado, Direccion_Entrega) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $id_cliente, $total, $estado, $direccion_entrega);
        $stmt->execute();
    }

    // Insertar datos en Detalle_Pedido
    $detalles_pedido = [
        [1, 1, 2, 1600.00],
        [2, 2, 1, 1900.50],
        [2, 3, 3, 1100.25]
    ];

    foreach ($detalles_pedido as $detalle) {
        [$id_pedido, $id_producto, $cantidad, $precio_unitario] = $detalle;

        if (!filter_var($id_pedido, FILTER_VALIDATE_INT) || !filter_var($id_producto, FILTER_VALIDATE_INT) || !filter_var($cantidad, FILTER_VALIDATE_INT) || !filter_var($precio_unitario, FILTER_VALIDATE_FLOAT)) {
            throw new Exception("Error: Datos inválidos para Detalle_Pedido.");
        }

        $stmt = $conn->prepare("INSERT INTO Detalle_Pedido (ID_Pedido, ID_Producto, Cantidad, Precio_Unitario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $id_pedido, $id_producto, $cantidad, $precio_unitario);
        $stmt->execute();
    }

    // Insertar datos en Material
    $materiales = [
        ['Vidrio Templado', 'Principal', 100, 20],
        ['Madera de Pino', 'Decorativo', 50, 10],
        ['Silicon', 'Adhesivo', 150, 30]
    ];

    foreach ($materiales as $material) {
        [$nombre, $tipo, $stock, $punto_reorden] = $material;

        if (empty($nombre) || empty($tipo) || !filter_var($stock, FILTER_VALIDATE_INT) || !filter_var($punto_reorden, FILTER_VALIDATE_INT)) {
            throw new Exception("Error: Datos inválidos para Material.");
        }

        $stmt = $conn->prepare("INSERT INTO Material (Nombre, Tipo, Stock, Punto_Reorden) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $nombre, $tipo, $stock, $punto_reorden);
        $stmt->execute();
    }

    // Insertar datos en Inventario
    $inventarios = [
        [1, 20, 'entrada'],
        [2, 10, 'entrada'],
        [3, 5, 'salida']
    ];

    foreach ($inventarios as $inventario) {
        [$id_material, $cantidad, $tipo] = $inventario;

        if (!filter_var($id_material, FILTER_VALIDATE_INT) || !filter_var($cantidad, FILTER_VALIDATE_INT) || !in_array($tipo, ['entrada', 'salida'])) {
            throw new Exception("Error: Datos inválidos para Inventario.");
        }

        $stmt = $conn->prepare("INSERT INTO Inventario (ID_Material, Cantidad, Tipo, Fecha) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $id_material, $cantidad, $tipo);
        $stmt->execute();
    }

    // Insertar datos en Proveedor
    $proveedores = [
        ['Vidrios Premium', 'Juan Perez', '555-1234', 'Calle de los Vidrios 100, CDMX'],
        ['Maderas Especiales', 'Laura Martinez', '555-4321', 'Avenida de la Madera 200, Guadalajara']
    ];

    foreach ($proveedores as $proveedor) {
        [$nombre, $contacto, $telefono, $direccion] = $proveedor;

        if (empty($nombre) || empty($contacto) || empty($telefono) || empty($direccion)) {
            throw new Exception("Error: Datos inválidos para Proveedor.");
        }

        $stmt = $conn->prepare("INSERT INTO Proveedor (Nombre, Contacto, Telefono, Direccion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $contacto, $telefono, $direccion);
        $stmt->execute();
    }

    // Insertar datos en Proveedor_Material
    $proveedor_materiales = [
        [1, 1, 50.00],
        [2, 2, 35.00]
    ];

    foreach ($proveedor_materiales as $proveedor_material) {
        [$id_proveedor, $id_material, $precio_compra] = $proveedor_material;

        if (!filter_var($id_proveedor, FILTER_VALIDATE_INT) || !filter_var($id_material, FILTER_VALIDATE_INT) || !filter_var($precio_compra, FILTER_VALIDATE_FLOAT)) {
            throw new Exception("Error: Datos inválidos para Proveedor_Material.");
        }

        $stmt = $conn->prepare("INSERT INTO Proveedor_Material (ID_Proveedor, ID_Material, Precio_Compra) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $id_proveedor, $id_material, $precio_compra);
        $stmt->execute();
    }

    // Insertar datos en Auditoria
    $auditorias = [
        [1, 'Creación de Pedido', 'Se creó un pedido nuevo para el cliente 1.'],
        [2, 'Actualización de Inventario', 'Se registró una salida de silicon.']
    ];

    foreach ($auditorias as $auditoria) {
        [$id_usuario, $accion, $detalles] = $auditoria;

        if (!filter_var($id_usuario, FILTER_VALIDATE_INT) || empty($accion) || empty($detalles)) {
            throw new Exception("Error: Datos inválidos para Auditoria.");
        }

        $stmt = $conn->prepare("INSERT INTO Auditoria (ID_Usuario, Accion, Detalles) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_usuario, $accion, $detalles);
        $stmt->execute();
    }

    // Insertar datos en Envio
    $envios = [
        [1, 'pendiente', 'Pedido listo para ser enviado.'],
        [2, 'en_transito', 'El pedido está en tránsito hacia el cliente.']
    ];

    foreach ($envios as $envio) {
        [$id_pedido, $estado, $detalles] = $envio;

        if (!filter_var($id_pedido, FILTER_VALIDATE_INT) || empty($estado) || empty($detalles)) {
            throw new Exception("Error: Datos inválidos para Envio.");
        }

        $stmt = $conn->prepare("INSERT INTO Envio (ID_Pedido, Estado, Detalles) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_pedido, $estado, $detalles);
        $stmt->execute();
    }

    // Insertar datos en Historial_Personalizacion
    $historiales = [
        [1, 1, 'Pecera personalizada con decoración minimalista.'],
        [3, 2, 'Pecera decorada con piedras de colores.']
    ];

    foreach ($historiales as $historial) {
        [$id_producto, $id_pedido, $descripcion] = $historial;

        if (!filter_var($id_producto, FILTER_VALIDATE_INT) || !filter_var($id_pedido, FILTER_VALIDATE_INT) || empty($descripcion)) {
            throw new Exception("Error: Datos inválidos para Historial_Personalizacion.");
        }

        $stmt = $conn->prepare("INSERT INTO Historial_Personalizacion (ID_Producto, ID_Pedido, Descripcion) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_producto, $id_pedido, $descripcion);
        $stmt->execute();
    }

    // Insertar datos en Transaccion
    $transacciones = [
        ['ingreso', 2400.75, 'Pago recibido por el pedido 1', 1],
        ['egreso', 1200.00, 'Compra de materiales para pedido 1', 1],
        ['ingreso', 3600.50, 'Pago recibido por el pedido 2', 2],
        ['ingreso', 5200.00, 'Pago recibido por el pedido 3', 3]
    ];

    foreach ($transacciones as $transaccion) {
        [$tipo, $monto, $descripcion, $id_pedido] = $transaccion;

        if (!in_array($tipo, ['ingreso', 'egreso']) || !filter_var($monto, FILTER_VALIDATE_FLOAT) || empty($descripcion)) {
            throw new Exception("Error: Datos inválidos para Transaccion.");
        }

        $stmt = $conn->prepare("INSERT INTO Transaccion (Tipo, Monto, Descripcion, ID_Pedido) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdsi", $tipo, $monto, $descripcion, $id_pedido);
        $stmt->execute();
    }

    // Reactivar restricciones de claves foráneas
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    $conn->commit(); // Confirmar la transacción

    echo "Datos insertados correctamente en todas las tablas.\n";

} catch (Exception $e) {
    $conn->rollback(); // Revertir cambios en caso de error
    echo "Error al insertar datos: " . $e->getMessage();
} finally {
    // Asegurarse de cerrar la conexión correctamente
    if ($conn->ping()) {
        $conn->close();
    }
}
?>
