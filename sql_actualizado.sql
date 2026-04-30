-- =========================================
-- ELIMINAR Y CREAR BASE DE DATOS (MySQL/MariaDB)
-- =========================================
DROP DATABASE IF EXISTS sistema_online;
CREATE DATABASE sistema_online CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_online;

-- =========================================
-- TABLA USUARIOS
-- =========================================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(40) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- =========================================
-- TABLAS AUXILIARES
-- =========================================
CREATE TABLE Marca (
    id_marca INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE Industria (
    id_industria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE Categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE Proveedor (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    direccion VARCHAR(100),
    telefono VARCHAR(20)
) ENGINE=InnoDB;

-- =========================================
-- TABLA PRODUCTOS
-- =========================================
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(300),
    precio DECIMAL(10,2) NOT NULL,
    imagen LONGTEXT,
    estado VARCHAR(20) NOT NULL DEFAULT 'activo',
    stock INT NOT NULL DEFAULT 0,
    id_marca INT NOT NULL,
    id_industria INT NOT NULL,
    id_categoria INT NOT NULL,
    id_proveedor INT,
    CONSTRAINT FK_producto_marca FOREIGN KEY (id_marca) REFERENCES Marca(id_marca),
    CONSTRAINT FK_producto_industria FOREIGN KEY (id_industria) REFERENCES Industria(id_industria),
    CONSTRAINT FK_producto_categoria FOREIGN KEY (id_categoria) REFERENCES Categoria(id_categoria),
    CONSTRAINT FK_producto_proveedor FOREIGN KEY (id_proveedor) REFERENCES Proveedor(id_proveedor)
) ENGINE=InnoDB;

-- =========================================
-- TABLA CLIENTES
-- =========================================
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    documento VARCHAR(20),
    nombre VARCHAR(120) NOT NULL,
    telefono VARCHAR(30),
    direccion VARCHAR(200),
    estado VARCHAR(20) NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB;

-- =========================================
-- TABLA VENTAS
-- =========================================
CREATE TABLE ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    id_usuario INT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    CONSTRAINT FK_venta_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    CONSTRAINT FK_venta_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- =========================================
-- DETALLE DE VENTAS
-- =========================================
CREATE TABLE detalle_ventas (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT FK_detalle_venta FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    CONSTRAINT FK_detalle_producto FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
) ENGINE=InnoDB;

-- =========================================
-- NOTA DE VENTA
-- =========================================
CREATE TABLE NotaVenta (
    id_nota INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE ItemNotaVenta (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_nota INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    CONSTRAINT FK_item_nota FOREIGN KEY (id_nota) REFERENCES NotaVenta(id_nota),
    CONSTRAINT FK_item_producto FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
) ENGINE=InnoDB;

-- =========================================
-- DATOS DE PRUEBA
-- =========================================
INSERT INTO usuarios (usuario, password)
VALUES ('humby', 'Duran716');

INSERT INTO Marca (nombre) VALUES ('Samsung'), ('LG');
INSERT INTO Industria (nombre) VALUES ('Tecnologia');
INSERT INTO Categoria (nombre) VALUES ('Electrodomesticos');

INSERT INTO Proveedor (nombre, direccion, telefono)
VALUES ('Proveedor1', 'Av. Principal', '12345678');

INSERT INTO productos (nombre, descripcion, precio, imagen, estado, stock, id_marca, id_industria, id_categoria, id_proveedor)
VALUES ('Televisor', 'Smart TV 50 pulgadas', 3500, NULL, 'activo', 15, 1, 1, 1, 1);

INSERT INTO productos (nombre, descripcion, precio, imagen, estado, stock, id_marca, id_industria, id_categoria, id_proveedor)
VALUES
('Mouse', 'Mouse óptico inalámbrico', 120.00, NULL, 'activo', 30, 1, 1, 1, 1),
('Teclado', 'Teclado mecánico retroiluminado', 280.00, NULL, 'activo', 20, 1, 1, 1, 1),
('Monitor', 'Monitor Full HD de 24 pulgadas', 950.00, NULL, 'activo', 12, 1, 1, 1, 1),
('Audífonos', 'Audífonos gamer con micrófono', 190.00, NULL, 'activo', 18, 1, 1, 1, 1),
('Disco SSD', 'Unidad SSD de alta velocidad', 410.00, NULL, 'activo', 25, 1, 1, 1, 1),
('Webcam', 'Cámara web HD para videollamadas', 160.00, NULL, 'activo', 14, 1, 1, 1, 1),
('Silla Gamer', 'Silla ergonómica para largas jornadas', 1250.00, NULL, 'activo', 8, 1, 1, 1, 1);
