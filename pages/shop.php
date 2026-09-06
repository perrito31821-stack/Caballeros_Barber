<?php
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (isset($_GET['add'])) {
  $id = (int)$_GET['add'];
  $p = $pdo->prepare("SELECT id,name,price FROM products WHERE id=? AND active=1");
  $p->execute([$id]);
  if ($prod = $p->fetch()){
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    echo '<div class="alert alert-success">Producto agregado al carrito.</div>';
  }
}
?>
<div class="bg-white border rounded-4 p-4 mb-3 d-flex justify-content-between align-items-center">
  <h2 class="mb-0">Tienda</h2>
  <a class="btn btn-outline-secondary" href="index.php?p=cart">Ver carrito (<?=array_sum($_SESSION['cart'])?>)</a>
</div>
<div class="row row-cols-1 row-cols-md-3 g-3">
<?php
$cat = $_GET['cat'] ?? null;
$sql = "SELECT * FROM products WHERE active=1";
$params = [];
if ($cat){ $sql .= " AND category=?"; $params[]=$cat; }
$sql .= " ORDER BY category,name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
foreach($stmt as $p):
?>
  <div class="col">
    <div class="card h-100">
      <div class="card-body">
        <span class="badge text-bg-secondary badge-cat"><?=e($p['category'])?></span>
        <h5 class="mt-2 mb-1"><?=e($p['name'])?></h5>
        <div class="fw-bold mb-2">$<?=number_format($p['price'],0,',','.')?></div>
        <div class="text-muted small mb-2">Stock: <?=e($p['stock'])?></div>
        <a href="index.php?p=shop&add=<?=$p['id']?>" class="btn btn-sm btn-primary">Agregar</a>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
