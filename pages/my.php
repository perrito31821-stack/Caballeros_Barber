<?php
if (!is_logged()) redirect('login');
$uid = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT a.*, s.name AS sname, COALESCE(st.name,'Sin asignar') AS staff_name
  FROM appointments a
  JOIN services s ON a.service_id=s.id
  LEFT JOIN staff st ON a.staff_id=st.id
  WHERE a.user_id=? ORDER BY a.appt_datetime DESC");
$stmt->execute([$uid]);
$rows = $stmt->fetchAll();

$orderStmt = $pdo->prepare("SELECT
    o.id AS order_id, o.total, o.status, o.created_at,
    GROUP_CONCAT(CONCAT(COALESCE(p.name,'Producto eliminado'), ' × ', oi.qty) ORDER BY p.name SEPARATOR ' · ') AS product_list,
    SUM(oi.qty) AS total_units
  FROM orders o
  LEFT JOIN order_items oi ON oi.order_id=o.id
  LEFT JOIN products p ON p.id=oi.product_id
  WHERE o.user_id=? AND o.appointment_id IS NULL
  GROUP BY o.id,o.total,o.status,o.created_at
  ORDER BY o.created_at DESC");
$orderStmt->execute([$uid]);
$productOrders = $orderStmt->fetchAll();
?>
<div class="bg-white border rounded-4 p-4 shadow-sm">
  <span class="section-kicker">Tu historial</span>
  <h2>Mis Citas</h2>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Código</th><th>Servicio</th><th>Profesional / sección</th><th>Fecha/Hora</th><th>Estado</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><span class="badge text-bg-secondary">#<?=e($r['code'])?></span></td>
            <td><?=e($r['sname'])?></td>
            <td><?=e($r['staff_name'])?></td>
            <td><?= (new DateTime($r['appt_datetime']))->format('d/m/Y H:i') ?></td>
            <td><?=e(ucfirst($r['status']))?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="5" class="text-center text-muted py-4">Todavía no tienes citas registradas.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <hr class="my-4">

  <span class="section-kicker">Tus compras</span>
  <h3 class="mb-1">Productos comprados</h3>
  <p class="text-muted mb-3">Aquí puedes consultar el historial de los productos que has comprado.</p>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Compra</th><th>Productos</th><th>Unidades</th><th>Total</th><th>Fecha/Hora</th><th>Estado</th></tr></thead>
      <tbody>
        <?php foreach($productOrders as $order): ?>
          <tr>
            <td><span class="badge text-bg-secondary">#<?=e($order['order_id'])?></span></td>
            <td><?=e($order['product_list'] ?: 'Sin productos')?></td>
            <td><?=number_format((int)$order['total_units'],0,',','.')?></td>
            <td class="fw-bold text-success">$<?=number_format($order['total'],0,',','.')?></td>
            <td><?= (new DateTime($order['created_at']))->format('d/m/Y H:i:s') ?></td>
            <td>
              <?php if($order['status']==='pagado'): ?><span class="badge text-bg-success">Pagado</span>
              <?php elseif($order['status']==='cancelado'): ?><span class="badge text-bg-danger">Cancelado</span>
              <?php else: ?><span class="badge text-bg-warning">Pendiente</span><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$productOrders): ?><tr><td colspan="6" class="text-center text-muted py-4">Todavía no tienes compras de productos registradas.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
