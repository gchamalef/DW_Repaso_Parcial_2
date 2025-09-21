<?php
// index.php
session_start();
require_once __DIR__ . '/crudMarcas.php';
require_once __DIR__ . '/crudProductos.php';

$section = $_GET['s'] ?? 'productos';
$action  = $_GET['a'] ?? 'list';
$id      = isset($_GET['id']) ? (int)$_GET['id'] : null;

$flash = function(string $type, string $msg) {
  $_SESSION['flash'][] = ['type'=>$type, 'msg'=>$msg];
};

if (!isset($_SESSION['flash'])) $_SESSION['flash'] = [];

// Handle POST (create/update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if ($section === 'marcas') {
      $marca = trim($_POST['marca'] ?? '');
      if ($marca === '') throw new Exception('El nombre de la marca es obligatorio.');
      if (!empty($_POST['id'])) {
        marcas_update((int)$_POST['id'], $marca);
        $flash('success', 'Marca actualizada.');
      } else {
        marcas_create($marca);
        $flash('success', 'Marca creada.');
      }
      header('Location: ?s=marcas'); exit;
    }

    if ($section === 'productos') {
      $data = [
        'producto' => trim($_POST['producto'] ?? ''),
        'idMarca' => (int)($_POST['idMarca'] ?? 0),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'precio_costo' => (float)($_POST['precio_costo'] ?? 0),
        'precio_venta' => (float)($_POST['precio_venta'] ?? 0),
        'existencia' => (int)($_POST['existencia'] ?? 0),
      ];
      if ($data['producto'] === '' || $data['idMarca'] === 0) {
        throw new Exception('Producto y Marca son obligatorios.');
      }
      if (!empty($_POST['id'])) {
        productos_update((int)$_POST['id'], $data);
        $flash('success', 'Producto actualizado.');
      } else {
        productos_create($data);
        $flash('success', 'Producto creado.');
      }
      header('Location: ?s=productos'); exit;
    }

  } catch (Throwable $e) {
    $flash('danger', 'Error: ' . $e->getMessage());
    header('Location: ?s=' . urlencode($section)); exit;
  }
}

// Handle deletes (GET)
if ($action === 'delete' && $id) {
  if ($section === 'marcas') {
    if (marcas_delete($id)) $flash('success','Marca eliminada.');
    else $flash('warning','No se puede eliminar la marca porque tiene productos asociados.');
    header('Location: ?s=marcas'); exit;
  }
  if ($section === 'productos') {
    productos_delete($id);
    $flash('success','Producto eliminado.');
    header('Location: ?s=productos'); exit;
  }
}

// Data for views
$editItem = null;
if ($action === 'edit' && $id) {
  $editItem = ($section === 'marcas') ? marcas_get($id) : productos_get($id);
}

$marcas = marcas_all();
$productos = productos_all();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CRUD Productos & Marcas (PHP + MySQL)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="?">Mi Tienda</a>
    <div class="navbar-nav">
      <a class="nav-link <?= $section==='productos'?'active':'' ?>" href="?s=productos">Productos</a>
      <a class="nav-link <?= $section==='marcas'?'active':'' ?>" href="?s=marcas">Marcas</a>
    </div>
  </div>
</nav>

<div class="container my-4">
  <?php foreach ($_SESSION['flash'] as $f): ?>
    <div class="alert alert-<?= h($f['type']) ?>"><?= h($f['msg']) ?></div>
  <?php endforeach; $_SESSION['flash'] = []; ?>

  <?php if ($section === 'marcas'): ?>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card">
          <div class="card-header"><?= $editItem ? 'Editar marca' : 'Nueva marca' ?></div>
          <div class="card-body">
            <form method="post" action="?s=marcas">
              <?php if ($editItem): ?>
                <input type="hidden" name="id" value="<?= h($editItem['idMarca']) ?>">
              <?php endif; ?>
              <div class="mb-3">
                <label class="form-label">Marca</label>
                <input type="text" name="marca" class="form-control" maxlength="50"
                       value="<?= h($editItem['marca'] ?? '') ?>" required>
              </div>
              <button class="btn btn-primary" type="submit"><?= $editItem ? 'Actualizar' : 'Guardar' ?></button>
              <?php if ($editItem): ?><a class="btn btn-secondary" href="?s=marcas">Cancelar</a><?php endif; ?>
            </form>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">Listado de marcas</div>
          <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
              <thead><tr><th>ID</th><th>Marca</th><th style="width:160px">Acciones</th></tr></thead>
              <tbody>
                <?php foreach ($marcas as $m): ?>
                  <tr>
                    <td><?= h($m['idMarca']) ?></td>
                    <td><?= h($m['marca']) ?></td>
                    <td>
                      <a class="btn btn-sm btn-outline-primary" href="?s=marcas&a=edit&id=<?= h($m['idMarca']) ?>">Editar</a>
                      <a class="btn btn-sm btn-outline-danger" href="?s=marcas&a=delete&id=<?= h($m['idMarca']) ?>"
                         onclick="return confirm('¿Eliminar marca?');">Borrar</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  <?php else: /* Productos */ ?>
    <div class="row g-4">
      <div class="col-lg-5">
        <div class="card">
          <div class="card-header"><?= $editItem ? 'Editar producto' : 'Nuevo producto' ?></div>
          <div class="card-body">
            <form method="post" action="?s=productos">
              <?php if ($editItem): ?>
                <input type="hidden" name="id" value="<?= h($editItem['idProducto']) ?>">
              <?php endif; ?>
              <div class="mb-3">
                <label class="form-label">Producto</label>
                <input type="text" name="producto" class="form-control" maxlength="50"
                       value="<?= h($editItem['producto'] ?? '') ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Marca</label>
                <select name="idMarca" class="form-select" required>
                  <option value="">-- seleccione --</option>
                  <?php foreach ($marcas as $m): ?>
                    <option value="<?= h($m['idMarca']) ?>"
                      <?= isset($editItem['idMarca']) && $editItem['idMarca']==$m['idMarca'] ? 'selected':'' ?>>
                      <?= h($m['marca']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-control" maxlength="100"
                       value="<?= h($editItem['descripcion'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Precio costo</label>
                <input type="number" step="0.01" name="precio_costo" class="form-control"
                       value="<?= h($editItem['precio_costo'] ?? '0.00') ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Precio venta</label>
                <input type="number" step="0.01" name="precio_venta" class="form-control"
                       value="<?= h($editItem['precio_venta'] ?? '0.00') ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Existencia</label>
                <input type="number" name="existencia" class="form-control"
                       value="<?= h($editItem['existencia'] ?? '0') ?>" required>
              </div>
              <button class="btn btn-primary" type="submit"><?= $editItem ? 'Actualizar' : 'Guardar' ?></button>
              <?php if ($editItem): ?><a class="btn btn-secondary" href="?s=productos">Cancelar</a><?php endif; ?>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="card">
          <div class="card-header">Listado de productos</div>
          <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>ID</th><th>Producto</th><th>Marca</th><th>Descripción</th>
                  <th>Costo</th><th>Venta</th><th>Existencia</th>
                  <th style="width:160px">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($productos as $p): ?>
                  <tr>
                    <td><?= h($p['idProducto']) ?></td>
                    <td><?= h($p['producto']) ?></td>
                    <td><?= h($p['marca']) ?></td>
                    <td><?= h($p['descripcion']) ?></td>
                    <td><?= h($p['precio_costo']) ?></td>
                    <td><?= h($p['precio_venta']) ?></td>
                    <td><?= h($p['existencia']) ?></td>
                    <td>
                      <a class="btn btn-sm btn-outline-primary" href="?s=productos&a=edit&id=<?= h($p['idProducto']) ?>">Editar</a>
                      <a class="btn btn-sm btn-outline-danger" href="?s=productos&a=delete&id=<?= h($p['idProducto']) ?>"
                         onclick="return confirm('¿Eliminar producto?');">Borrar</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<footer class="text-center text-muted mb-4">
  <small>CRUD de ejemplo — PHP + MySQL + Bootstrap</small>
</footer>
</body>
</html>
