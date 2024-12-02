-- Desactiva las restricciones de claves foraneas temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- Limpieza de  base de datos si ya existe
DROP DATABASE IF EXISTS AquaDesign;

-- Creacion de la base de datos
CREATE DATABASE IF NOT EXISTS AquaDesign;
USE AquaDesign;

-- Tabla Usuario
CREATE TABLE Usuario (
    ID_Usuario INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(100) NOT NULL,
    Correo VARCHAR(100) UNIQUE NOT NULL,
    Contraseña VARCHAR(255) NOT NULL,
    Rol ENUM('administrador', 'cliente', 'productor', 'logistica') DEFAULT 'cliente',
    Estado ENUM('activo', 'inactivo') DEFAULT 'activo'
);

-- Tabla Cliente
CREATE TABLE Cliente (
    ID_Cliente INT PRIMARY KEY AUTO_INCREMENT,
    ID_Usuario INT NOT NULL,
    Direccion TEXT NOT NULL,
    Telefono VARCHAR(15) NOT NULL,
    FOREIGN KEY (ID_Usuario) REFERENCES Usuario(ID_Usuario) ON DELETE CASCADE
);

-- Tabla Producto
CREATE TABLE Producto (
    ID_Producto INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(100) NOT NULL,
    Tipo_Diseno VARCHAR(50) NOT NULL,
    Tamano VARCHAR(50) NOT NULL,
    Precio DECIMAL(10, 2) NOT NULL,
    Stock INT NOT NULL CHECK (Stock >= 0),
    Descripcion TEXT
);

-- Tabla Pedido
CREATE TABLE Pedido (
    ID_Pedido INT PRIMARY KEY AUTO_INCREMENT,
    ID_Cliente INT,
    Total DECIMAL(10, 2),
    Estado ENUM('pendiente', 'en_proceso', 'enviado', 'entregado') DEFAULT 'pendiente',
    Direccion_Entrega TEXT,
    Fecha_Pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_Cliente) REFERENCES Cliente(ID_Cliente) ON DELETE SET NULL
);

-- Tabla Detalle_Pedido
CREATE TABLE Detalle_Pedido (
    ID_Detalle INT PRIMARY KEY AUTO_INCREMENT,
    ID_Pedido INT NOT NULL,
    ID_Producto INT NOT NULL,
    Cantidad INT NOT NULL CHECK (Cantidad > 0),
    Precio_Unitario DECIMAL(10, 2) NOT NULL,
    Subtotal DECIMAL(10, 2) GENERATED ALWAYS AS (Cantidad * Precio_Unitario) STORED,
    FOREIGN KEY (ID_Pedido) REFERENCES Pedido(ID_Pedido) ON DELETE CASCADE,
    FOREIGN KEY (ID_Producto) REFERENCES Producto(ID_Producto) ON DELETE RESTRICT
);

-- Tabla Material
CREATE TABLE Material (
    ID_Material INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(100) NOT NULL,
    Tipo VARCHAR(50),
    Stock INT NOT NULL CHECK (Stock >= 0),
    Punto_Reorden INT NOT NULL DEFAULT 10
);

-- Tabla Inventario
CREATE TABLE Inventario (
    ID_Inventario INT PRIMARY KEY AUTO_INCREMENT,
    ID_Material INT NOT NULL,
    Cantidad INT NOT NULL,
    Tipo ENUM('entrada', 'salida') NOT NULL,
    Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_Material) REFERENCES Material(ID_Material) ON DELETE RESTRICT
);

-- Tabla Proveedor
CREATE TABLE Proveedor (
    ID_Proveedor INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(100) NOT NULL,
    Contacto VARCHAR(100),
    Telefono VARCHAR(20),
    Direccion TEXT
);

-- Tabla Proveedor_Material
CREATE TABLE Proveedor_Material (
    ID_Proveedor INT,
    ID_Material INT,
    Precio_Compra DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (ID_Proveedor, ID_Material),
    FOREIGN KEY (ID_Proveedor) REFERENCES Proveedor(ID_Proveedor) ON DELETE CASCADE,
    FOREIGN KEY (ID_Material) REFERENCES Material(ID_Material) ON DELETE CASCADE
);

-- Tabla Auditoria
CREATE TABLE Auditoria (
    ID_Log INT PRIMARY KEY AUTO_INCREMENT,
    ID_Usuario INT NOT NULL,
    Accion VARCHAR(255) NOT NULL,
    Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Detalles TEXT,
    FOREIGN KEY (ID_Usuario) REFERENCES Usuario(ID_Usuario) ON DELETE CASCADE
);

-- Tabla Envio
CREATE TABLE Envio (
    ID_Envio INT PRIMARY KEY AUTO_INCREMENT,
    ID_Pedido INT NOT NULL,
    Fecha_Envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Fecha_Entrega TIMESTAMP NULL,
    Estado ENUM('pendiente', 'en_transito', 'entregado') DEFAULT 'pendiente',
    Detalles TEXT,
    FOREIGN KEY (ID_Pedido) REFERENCES Pedido(ID_Pedido) ON DELETE CASCADE
);

-- Tabla Historial_Personalizacion
CREATE TABLE Historial_Personalizacion (
    ID_Historial INT PRIMARY KEY AUTO_INCREMENT,
    ID_Producto INT NOT NULL,
    ID_Pedido INT NOT NULL,
    Descripcion TEXT,
    Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_Producto) REFERENCES Producto(ID_Producto) ON DELETE CASCADE,
    FOREIGN KEY (ID_Pedido) REFERENCES Pedido(ID_Pedido) ON DELETE CASCADE
);

-- Tabla Transaccion
CREATE TABLE Transaccion (
    ID_Transaccion INT PRIMARY KEY AUTO_INCREMENT,
    Tipo ENUM('ingreso', 'egreso') NOT NULL,
    Monto DECIMAL(10, 2) NOT NULL,
    Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Descripcion TEXT,
    ID_Pedido INT,
    FOREIGN KEY (ID_Pedido) REFERENCES Pedido(ID_Pedido) ON DELETE SET NULL
);

-- Procedimiento para el calculo de balance mensual
DELIMITER //
CREATE PROCEDURE CalcularBalanceMensual()
BEGIN
    DECLARE ingresos DECIMAL(10, 2) DEFAULT 0;
    DECLARE egresos DECIMAL(10, 2) DEFAULT 0;
    DECLARE ganancia DECIMAL(10, 2) DEFAULT 0;

    -- Calculo ingresos mensuales
    SELECT IFNULL(SUM(Monto), 0) INTO ingresos
    FROM Transaccion
    WHERE Tipo = 'ingreso' AND MONTH(Fecha) = MONTH(CURRENT_DATE);

    -- Calculo egresos mensuales
    SELECT IFNULL(SUM(Monto), 0) INTO egresos
    FROM Transaccion
    WHERE Tipo = 'egreso' AND MONTH(Fecha) = MONTH(CURRENT_DATE);

    -- Calculo de ganancia
    SET ganancia = ingresos - egresos;

    -- Retorno de  resultados
    SELECT 
        ingresos AS Ingresos_Mensuales,
        egresos AS Egresos_Mensuales,
        ganancia AS Balance_Mensual,
        CASE
            WHEN ingresos > 0 THEN (ganancia / ingresos) * 100
            ELSE 0
        END AS Porcentaje_Ganancia;
END //
DELIMITER ;

-- Trigger para actualizacion  de ingresos al cambiar el precio de un producto
DELIMITER //
CREATE TRIGGER ActualizarIngresoPrecio
AFTER UPDATE ON Producto
FOR EACH ROW
BEGIN
    UPDATE Transaccion
    SET Monto = (
        SELECT SUM(dp.Cantidad * NEW.Precio)
        FROM Detalle_Pedido dp
        WHERE dp.ID_Producto = NEW.ID_Producto
    )
    WHERE Tipo = 'ingreso' AND ID_Pedido IN (
        SELECT ID_Pedido
        FROM Detalle_Pedido dp
        WHERE dp.ID_Producto = NEW.ID_Producto
    );
END //
DELIMITER ;

-- Reactivar restricciones de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

