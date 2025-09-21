<?php
// crudProductos.php
require_once __DIR__ . '/conexion.php';

function productos_all() : array {
  $sql = 'SELECT p.idProducto, p.producto, p.idMarca, m.marca, p.descripcion,
                 p.precio_costo, p.precio_venta, p.existencia
          FROM productos p
          JOIN marcas m ON m.idMarca = p.idMarca
          ORDER BY p.idProducto ASC';
  $st = db()->query($sql);
  return $st->fetchAll();
}

function productos_get(int $id) : ?array {
  $st = db()->prepare('SELECT * FROM productos WHERE idProducto = ?');
  $st->execute([$id]);
  $row = $st->fetch();
  return $row ?: null;
}

function productos_create(array $d) : int {
  $sql = 'INSERT INTO productos
          (producto, idMarca, descripcion, precio_costo, precio_venta, existencia)
          VALUES (:producto, :idMarca, :descripcion, :precio_costo, :precio_venta, :existencia)';
  $st = db()->prepare($sql);
  $st->execute([
    ':producto' => $d['producto'],
    ':idMarca' => (int)$d['idMarca'],
    ':descripcion' => $d['descripcion'] ?? null,
    ':precio_costo' => (float)$d['precio_costo'],
    ':precio_venta' => (float)$d['precio_venta'],
    ':existencia' => (int)$d['existencia'],
  ]);
  return (int) db()->lastInsertId();
}

function productos_update(int $id, array $d) : bool {
  $sql = 'UPDATE productos
            SET producto = :producto,
                idMarca = :idMarca,
                descripcion = :descripcion,
                precio_costo = :precio_costo,
                precio_venta = :precio_venta,
                existencia = :existencia
          WHERE idProducto = :id';
  $st = db()->prepare($sql);
  return $st->execute([
    ':producto' => $d['producto'],
    ':idMarca' => (int)$d['idMarca'],
    ':descripcion' => $d['descripcion'] ?? null,
    ':precio_costo' => (float)$d['precio_costo'],
    ':precio_venta' => (float)$d['precio_venta'],
    ':existencia' => (int)$d['existencia'],
    ':id' => $id,
  ]);
}

function productos_delete(int $id) : bool {
  $st = db()->prepare('DELETE FROM productos WHERE idProducto = ?');
  return $st->execute([$id]);
}
?>
