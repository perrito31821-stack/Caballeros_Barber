<?php
$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0;
if ($cart){
  $ids = implode(',', array_map('intval', array_keys($cart)));
  $rows = $pdo->query("SELECT id,name,price FROM products WHERE id IN ($ids)");
  foreach($rows as $r){
    $qty = $cart[$r['id']];
    $items[] = ['id'=>$r['id'],'name'=>$r['name'],'price'=>$r['price'],'qty'=>$qty,'subtotal'=>$qty*$r['price']];
    $total += $qty*$r['price'];
  }
}
if (isset($_POST['update'])){
  foreach($_POST['qty'] as $id=>$q){
    $q = max(0, (int)$q);
    if ($q==0) unset($_SESSION['cart'][$id]);
    else $_SESSION['cart'][$id] = $q;
  }
  header("Location: index.php?p=cart"); exit;
}
?>
<div class="bg-white border rounded-4 p-4">
  <h2 class="mb-3">Carrito</h2>
  <?php if(!$items): ?>
    <p>No hay productos.</p>
  <?php else: ?>
    <form method="post">
      <table class="table align-middle">
        <thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead>
        <tbody>
          <?php foreach($items as $it): ?>
            <tr>
              <td><?=e($it['name'])?></td>
              <td style="max-width:120px"><input class="form-control" type="number" name="qty[<?=$it['id']?>]" value="<?=$it['qty']?>" min="0"></td>
              <td>$<?=number_format($it['price'],0,',','.')?></td>
              <td>$<?=number_format($it['subtotal'],0,',','.')?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="d-flex justify-content-between">
        <button class="btn btn-outline-secondary" name="update" value="1">Actualizar</button>
        <div class="h5">Total: $<?=number_format($total,0,',','.')?></div>
      </div>
    </form>
    <div class="text-end mt-3">
      <a href="index.php?p=checkout" class="btn btn-primary">Ir a pagar</a>
    </div>
  <?php endif; ?>
</div>
