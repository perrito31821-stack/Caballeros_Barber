<?php
if (!is_logged()) redirect('login');
$cart = $_SESSION['cart'] ?? [];
if (!$cart){ echo '<div class="alert alert-warning">Carrito vacío.</div>'; return; }

$total = 0;
$ids = implode(',', array_map('intval', array_keys($cart)));
$rows = $pdo->query("SELECT id,name,price,stock FROM products WHERE id IN ($ids)");
$products = [];
foreach($rows as $r){
  $qty = $cart[$r['id']];
  $products[$r['id']] = $r;
  $total += $qty*$r['price'];
}

if ($_SERVER['REQUEST_METHOD']==='POST'){
  $method = $_POST['method'] ?? 'efectivo';
  $pdo->beginTransaction();
  try{
    $st = $pdo->prepare("INSERT INTO orders (user_id,total,status) VALUES (?,?, 'pagado')");
    $st->execute([$_SESSION['user']['id'], $total]);
    $order_id = $pdo->lastInsertId();

    $sti = $pdo->prepare("INSERT INTO order_items (order_id,product_id,qty,price) VALUES (?,?,?,?)");
    $stu = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id=? AND stock>=?");
    foreach($cart as $pid=>$qty){
      $p = $products[$pid];
      if ($p['stock'] < $qty) throw new Exception("Sin stock para {$p['name']}");
      $sti->execute([$order_id, $pid, $qty, $p['price']]);
      $stu->execute([$qty,$pid,$qty]);
    }
    $pdo->commit();
    $_SESSION['cart'] = [];
    $_SESSION['receipt'] = ['type'=>'order','id'=>$order_id,'total'=>$total,'method'=>$method];
    redirect('receipt');
  }catch(Exception $e){
    $pdo->rollBack();
    echo '<div class="alert alert-danger">Error: '.e($e->getMessage()).'</div>';
  }
}
?>
<div class="bg-white border rounded-4 p-4">
  <h2>Checkout</h2>
  <form method="post" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Método de pago</label>
      <select name="method" class="form-select" required>
        <option value="efectivo">Efectivo</option>
        <option value="transferencia">Transferencia</option>
        <option value="tarjeta">Tarjeta</option>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Total</label>
      <input class="form-control" value="$<?=number_format($total,0,',','.')?>" disabled>
    </div>
    <div class="col-12 text-end">
      <button class="btn btn-primary">Confirmar pago</button>
    </div>
  </form>
</div>