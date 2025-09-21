# DW_Repaso_Parcial_2

## Script de las Tablas

```sql
-- Tabla MARCAS
CREATE TABLE IF NOT EXISTS marcas (
  idMarca SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  marca   VARCHAR(50) NOT NULL,
  CONSTRAINT pk_marcas PRIMARY KEY (idMarca),
  CONSTRAINT uq_marcas_marca UNIQUE (marca)
) ENGINE=InnoDB;

-- Tabla PRODUCTOS
CREATE TABLE IF NOT EXISTS productos (
  idProducto   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  producto     VARCHAR(50)  NOT NULL,
  idMarca      SMALLINT UNSIGNED NOT NULL,
  descripcion  VARCHAR(100),
  precio_costo DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  precio_venta DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  existencia   INT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT pk_productos PRIMARY KEY (idProducto),
  CONSTRAINT fk_productos_marcas FOREIGN KEY (idMarca)
    REFERENCES marcas(idMarca)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  INDEX idx_productos_marca (idMarca),
  INDEX idx_productos_nombre (producto)
) ENGINE=InnoDB;
```
