<?php
// crudMarcas.php
require_once __DIR__ . '/conexion.php';

function marcas_all() : array {
  $st = db()->query('SELECT idMarca, marca FROM marcas ORDER BY idMarca ASC');
  return $st->fetchAll();
}

function marcas_get(int $id) : ?array {
  $st = db()->prepare('SELECT idMarca, marca FROM marcas WHERE idMarca = ?');
  $st->execute([$id]);
  $row = $st->fetch();
  return $row ?: null;
}

function marcas_create(string $marca) : int {
  $st = db()->prepare('INSERT INTO marcas (marca) VALUES (?)');
  $st->execute([$marca]);
  return (int) db()->lastInsertId();
}

function marcas_update(int $id, string $marca) : bool {
  $st = db()->prepare('UPDATE marcas SET marca = ? WHERE idMarca = ?');
  return $st->execute([$marca, $id]);
}

function marcas_delete(int $id) : bool {
  // No permitir borrar si hay productos relacionados
  $st = db()->prepare('SELECT COUNT(*) c FROM productos WHERE idMarca = ?');
  $st->execute([$id]);
  if ((int)$st->fetchColumn() > 0) return false;

  $st = db()->prepare('DELETE FROM marcas WHERE idMarca = ?');
  return $st->execute([$id]);
}
?>
