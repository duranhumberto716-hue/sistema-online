-- ============================================
-- CREAR BASE DE DATOS
-- ============================================
DROP DATABASE IF EXISTS ecommerce;
CREATE DATABASE ecommerce;
USE ecommerce;

-- ============================================
-- TABLA: Cuenta
-- ============================================
CREATE TABLE Cuenta (
  usuario VARCHAR(40) NOT NULL,
  password VARCHAR(30) NOT NULL,
  PRIMARY KEY (usuario)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: Cliente
-- ============================================
CREATE TABLE Cliente (
  ci VARCHAR(20) NOT NULL,
  nombres VARCHAR(50) NOT NULL,
  apPaterno VARCHAR(20) NOT NULL,
  apMaterno VARCHAR(20) NOT NULL,
  correo VARCHAR(30) NOT NULL,
  direccion VARCHAR(45) NOT NULL,
  nroCelular VARCHAR(30) NOT NULL,
  usuarioCuenta VARCHAR(40) NOT NULL,
  PRIMARY KEY (ci, usuarioCuenta),
  FOREIGN KEY (usuarioCuenta) REFERENCES Cuenta(usuario)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: NotaVenta
-- ============================================
CREATE TABLE NotaVenta (
  nro INT NOT NULL,
  fechaHora DATETIME NOT NULL,
  ciCliente VARCHAR(20) NOT NULL,
  PRIMARY KEY (nro),
  FOREIGN KEY (ciCliente) REFERENCES Cliente(ci)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: Industria
-- ============================================
CREATE TABLE Industria (
  cod INT AUTO_INCREMENT,
  nombre VARCHAR(30) NOT NULL,
  PRIMARY KEY (cod)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: Marca
-- ============================================
CREATE TABLE Marca (
  cod INT AUTO_INCREMENT,
  nombre VARCHAR(30),
  PRIMARY KEY (cod)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: Categoria
-- ============================================
CREATE TABLE Categoria (
  cod INT AUTO_INCREMENT,
  nombre VARCHAR(30),
  PRIMARY KEY (cod)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: Producto
-- ============================================
CREATE TABLE Producto (
  cod INT AUTO_INCREMENT,
  nombre VARCHAR(50) NOT NULL,
  descripcion VARCHAR(200) NOT NULL,
  precio FLOAT NOT NULL,
  imagen VARCHAR(200) NOT NULL,
  estado VARCHAR(20) NOT NULL,
  codMarca INT NOT NULL,
  codIndustria INT NOT NULL,
  codCategoria INT NOT NULL,
  PRIMARY KEY (cod),
  FOREIGN KEY (codMarca) REFERENCES Marca(cod),
  FOREIGN KEY (codIndustria) REFERENCES Industria(cod),
  FOREIGN KEY (codCategoria) REFERENCES Categoria(cod)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: DetalleNotaVenta
-- ============================================
CREATE TABLE DetalleNotaVenta (
  item INT AUTO_INCREMENT,
  nroNotaVenta INT NOT NULL,
  codProducto INT NOT NULL,
  cant INT NOT NULL,
  PRIMARY KEY (item),
  FOREIGN KEY (nroNotaVenta) REFERENCES NotaVenta(nro),
  FOREIGN KEY (codProducto) REFERENCES Producto(cod)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: Sucursal
-- ============================================
CREATE TABLE Sucursal (
  cod INT AUTO_INCREMENT,
  nombre VARCHAR(30) NOT NULL,
  direccion VARCHAR(100) NOT NULL,
  nroTelefono BIGINT NOT NULL,
  PRIMARY KEY (cod)
) ENGINE=InnoDB;

-- ============================================
-- TABLA: DetalleProductoSucursal
-- ============================================
CREATE TABLE DetalleProductoSucursal (
  codProducto INT NOT NULL,
  codSucursal INT NOT NULL,
  stock VARCHAR(45) NOT NULL,
  PRIMARY KEY (codProducto, codSucursal),
  FOREIGN KEY (codProducto) REFERENCES Producto(cod),
  FOREIGN KEY (codSucursal) REFERENCES Sucursal(cod)
) ENGINE=InnoDB;