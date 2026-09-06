<?php
if (!is_admin()) { echo '<div class="alert alert-danger">Acceso restringido.</div>'; return; }
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $category = ($_POST['category'] ?? 'barberia') === 'belleza' ? 'belleza' : 'barberia';
  if (isset($_POST['create'])) {
    $st = $pdo->prepare("INSERT INTO services (name,price,duration_minutes,category,active) VALUES (?,?,?,?,1)");
    $st->execute([trim($_POST['name']), $_POST['price'], $_POST['duration'], $category]);
  } elseif (isset($_POST['update'])) {
    $st = $pdo->prepare("UPDATE services SET name=?, price=?, duration_minutes=?, category=?, active=? WHERE id=?");
    $st->execute([trim($_POST['name']), $_POST['price'], $_POST['duration'], $category, isset($_POST['active'])?1:0, $_POST['id']]);
  } elseif (isset($_POST['delete'])) {
    try {
      $st = $pdo->prepare("DELETE FROM services WHERE id=?");
      $st->execute([$_POST['id']]);
    } catch (PDOException $ex) {
      echo '<div class="alert alert-warning">No se puede eliminar un servicio que ya tiene citas asociadas. Puedes desactivarlo.</div>';
    }
  }
}
$rows = $pdo->query("SELECT * FROM services ORDER BY category,name")->fetchAll();
?>
<div class="bg-white border rounded-4 p-4 shadow-sm">
  <span class="section-kicker">Configuración</span>
  <h2>Servicios</h2>
  <p class="text-muted">Indica si cada servicio pertenece a Barbería o Belleza; así aparecerá en la agenda correcta.</p>
  <form method="post" class="row g-2 align-items-end mb-4">
    <div class="col-md-3"><label class="form-label">Nombre</label><input name="name" class="form-control" required></div>
    <div class="col-md-2"><label class="form-label">Precio</label><input name="price" type="number" min="0" step="0.01" class="form-control" required></div>
    <div class="col-md-2"><label class="form-label">Duración (min)</label><input name="duration" type="number" min="1" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">Sección</label><select name="category" class="form-select" required><option value="barberia">Barbería</option><option value="belleza">Belleza</option></select></div>
    <div class="col-md-2"><button class="btn btn-primary w-100" name="create" value="1">Agregar</button></div>
  </form>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Nombre</th><th>Precio</th><th>Duración</th><th>Sección</th><th>Activo</th><th></th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
        <tr><form method="post">
          <td><input name="name" class="form-control" value="<?=e($r['name'])?>" required></td>
          <td><input name="price" type="number" min="0" step="0.01" class="form-control" value="<?=$r['price']?>" required></td>
          <td><input name="duration" type="number" min="1" class="form-control" value="<?=$r['duration_minutes']?>" required></td>
          <td><select name="category" class="form-select"><option value="barberia" <?=$r['category']==='barberia'?'selected':''?>>Barbería</option><option value="belleza" <?=$r['category']==='belleza'?'selected':''?>>Belleza</option></select></td>
          <td><input type="checkbox" name="active" <?=$r['active']?'checked':''?>></td>
          <td class="text-end text-nowrap"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-sm btn-outline-primary" name="update" value="1">Guardar</button> <button class="btn btn-sm btn-outline-danger" name="delete" value="1" onclick="return confirm('¿Eliminar?')">Eliminar</button></td>
        </form></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
