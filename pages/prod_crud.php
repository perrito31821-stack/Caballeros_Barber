<?php
if (!is_admin()) { echo '<div class="alert alert-danger">Acceso restringido.</div>'; return; }

$message = null;
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD']==='POST'){
  try {
    if (isset($_POST['create'])){
      $st = $pdo->prepare("INSERT INTO products (name,category,price,stock,active) VALUES (?,?,?,?,1)");
      $st->execute([trim($_POST['name']), trim($_POST['category']), (float)$_POST['price'], (int)$_POST['stock']]);
      $message = 'Producto agregado correctamente.';
    } elseif (isset($_POST['update'])){
      $st = $pdo->prepare("UPDATE products SET name=?, category=?, price=?, stock=?, active=? WHERE id=?");
      $st->execute([trim($_POST['name']), trim($_POST['category']), (float)$_POST['price'], (int)$_POST['stock'], isset($_POST['active'])?1:0, (int)$_POST['id']]);
      $message = 'Producto actualizado correctamente.';
    } elseif (isset($_POST['delete'])){
      $id = (int)$_POST['id'];
      $check = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id=?");
      $check->execute([$id]);
      $hasSales = (int)$check->fetchColumn() > 0;

      if ($hasSales) {
        // Conservamos la referencia histórica para no borrar ventas ya registradas.
        $st = $pdo->prepare("UPDATE products SET active=0 WHERE id=?");
        $st->execute([$id]);
        $message = 'El producto tiene ventas registradas y no puede eliminarse físicamente sin perder el historial. Se desactivó correctamente y ya no aparecerá en la tienda.';
        $messageType = 'warning';
      } else {
        $st = $pdo->prepare("DELETE FROM products WHERE id=?");
        $st->execute([$id]);
        $message = 'Producto eliminado correctamente.';
      }
    }
  } catch (Throwable $e) {
    $message = 'No fue posible completar la operación. Verifica que el producto no esté siendo utilizado por otros registros.';
    $messageType = 'danger';
  }
}

$rows = $pdo->query("SELECT * FROM products ORDER BY active DESC, category,name")->fetchAll();
$cats = array_unique(array_map(fn($r)=>$r['category'],$rows));
?>
<div class="bg-white border rounded-4 p-4">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <div>
      <span class="section-kicker">Inventario</span>
      <h2 class="mb-1">Productos</h2>
      <p class="text-muted mb-0">Los dos administradores pueden agregar, editar, desactivar o eliminar productos.</p>
    </div>
    <span class="badge text-bg-dark px-3 py-2"><?=count($rows)?> productos registrados</span>
  </div>

  <?php if($message): ?>
    <div class="alert alert-<?=e($messageType)?> alert-dismissible fade show" role="alert">
      <?=e($message)?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>

  <div class="alert alert-light border small mb-4">
    <i class="bi bi-info-circle me-1"></i>
    Si un producto ya aparece en una venta, el sistema lo <strong>desactiva</strong> en lugar de borrarlo físicamente para conservar intacto el historial de compras.
  </div>

  <form method="post" class="row g-2 align-items-end mb-4">
    <div class="col-md-3"><label class="form-label">Nombre</label><input name="name" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">Categoría</label><input name="category" class="form-control" list="dcat" required></div>
    <datalist id="dcat">
      <?php foreach($cats as $c): ?><option value="<?=e($c)?>"><?php endforeach; ?>
    </datalist>
    <div class="col-md-2"><label class="form-label">Precio</label><input name="price" type="number" step="0.01" min="0" class="form-control" required></div>
    <div class="col-md-2"><label class="form-label">Stock</label><input name="stock" type="number" min="0" class="form-control" required></div>
    <div class="col-md-2"><button class="btn btn-primary w-100" name="create" value="1">Agregar</button></div>
  </form>

  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Activo</th><th class="text-end">Acciones</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
        <tr class="<?= !$r['active'] ? 'table-light' : '' ?>">
          <td colspan="6">
            <form method="post" class="row g-2 align-items-center">
              <div class="col-lg-3"><input name="name" class="form-control" value="<?=e($r['name'])?>" required></div>
              <div class="col-lg-2"><input name="category" class="form-control" value="<?=e($r['category'])?>" required></div>
              <div class="col-lg-2"><input name="price" type="number" step="0.01" min="0" class="form-control" value="<?=$r['price']?>" required></div>
              <div class="col-lg-1"><input name="stock" type="number" min="0" class="form-control" value="<?=$r['stock']?>" required></div>
              <div class="col-lg-1 text-center"><input type="checkbox" name="active" <?= $r['active']?'checked':'' ?>></div>
              <div class="col-lg-3 text-lg-end text-nowrap">
                <input type="hidden" name="id" value="<?=$r['id']?>">
                <button class="btn btn-sm btn-outline-primary" name="update" value="1">Guardar</button>
                <button class="btn btn-sm btn-outline-danger" name="delete" value="1" onclick="return confirm('¿Eliminar este producto? Si ya tiene ventas, se desactivará para conservar el historial.')">Eliminar</button>
              </div>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="6" class="text-center text-muted py-4">No hay productos registrados.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
