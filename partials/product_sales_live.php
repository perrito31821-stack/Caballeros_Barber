<?php
// Resumen de ingresos y ventas de productos en tiempo real para administradores.
$today = (new DateTime())->format('Y-m-d');

$qDay = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE DATE(created_at)=?");
$qDay->execute([$today]);
$serviceDay = (float)$qDay->fetchColumn();
$serviceWeek = (float)($pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)")->fetchColumn() ?: 0);
$service15 = (float)($pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 15 DAY)")->fetchColumn() ?: 0);
$serviceMonth = (float)($pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetchColumn() ?: 0);

$productDay = (float)($pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='pagado' AND DATE(created_at)=CURDATE()")->fetchColumn() ?: 0);
$productWeek = (float)($pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='pagado' AND YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)")->fetchColumn() ?: 0);
$product15 = (float)($pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='pagado' AND created_at >= DATE_SUB(NOW(), INTERVAL 15 DAY)")->fetchColumn() ?: 0);
$productMonth = (float)($pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='pagado' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetchColumn() ?: 0);

$totalDay = $serviceDay + $productDay;
$totalWeek = $serviceWeek + $productWeek;
$total15 = $service15 + $product15;
$totalMonth = $serviceMonth + $productMonth;

$qOrders = $pdo->query("SELECT
    o.id,
    o.total,
    o.status,
    o.created_at,
    u.name AS customer_name,
    u.phone AS customer_phone,
    GROUP_CONCAT(CONCAT(COALESCE(p.name,'Producto eliminado'), ' × ', oi.qty) ORDER BY p.name SEPARATOR ' · ') AS product_list,
    SUM(oi.qty) AS total_units
  FROM orders o
  JOIN users u ON o.user_id=u.id
  LEFT JOIN order_items oi ON oi.order_id=o.id
  LEFT JOIN products p ON p.id=oi.product_id
  GROUP BY o.id,o.total,o.status,o.created_at,u.name,u.phone
  ORDER BY o.created_at DESC");
$ordersHistory = $qOrders->fetchAll();

$productUnits = (int)($pdo->query("SELECT COALESCE(SUM(oi.qty),0) FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.status='pagado'")->fetchColumn() ?: 0);
$productOrdersCount = (int)($pdo->query("SELECT COUNT(*) FROM orders WHERE status='pagado'")->fetchColumn() ?: 0);
?>
<div class="bg-white border rounded-4 p-4 mt-4 shadow-sm">
  <span class="section-kicker">Caja</span>
  <h3 class="mb-1">Resumen de ingresos</h3>
  <p class="text-muted mb-3">Total general de los servicios cobrados y productos vendidos en Caballeros Barber.</p>
  <div class="row g-3 text-center">
    <div class="col-6 col-md-3"><div class="info-tile"><small class="text-muted">Hoy</small><h4 class="mt-1 mb-0">$<?=number_format($totalDay,0,',','.')?></h4></div></div>
    <div class="col-6 col-md-3"><div class="info-tile"><small class="text-muted">Semana</small><h4 class="mt-1 mb-0">$<?=number_format($totalWeek,0,',','.')?></h4></div></div>
    <div class="col-6 col-md-3"><div class="info-tile"><small class="text-muted">Últimos 15 días</small><h4 class="mt-1 mb-0">$<?=number_format($total15,0,',','.')?></h4></div></div>
    <div class="col-6 col-md-3"><div class="info-tile"><small class="text-muted">Mes</small><h4 class="mt-1 mb-0">$<?=number_format($totalMonth,0,',','.')?></h4></div></div>
  </div>
  <div class="row g-3 mt-1">
    <div class="col-md-6"><div class="p-3 rounded-4 bg-light border"><div class="d-flex justify-content-between align-items-center"><span><i class="bi bi-scissors me-2"></i>Servicios</span><strong>$<?=number_format($serviceMonth,0,',','.')?></strong></div><small class="text-muted">Ingresos acumulados del mes por servicios.</small></div></div>
    <div class="col-md-6"><div class="p-3 rounded-4 bg-light border"><div class="d-flex justify-content-between align-items-center"><span><i class="bi bi-bag me-2"></i>Productos</span><strong>$<?=number_format($productMonth,0,',','.')?></strong></div><small class="text-muted">Ventas de productos acumuladas durante el mes.</small></div></div>
  </div>
</div>

<div class="bg-white border rounded-4 p-4 mt-4 shadow-sm">
  <span class="section-kicker">Ventas de productos</span>
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
    <div>
      <h3 class="mb-1">Productos vendidos</h3>
      <p class="text-muted mb-0">Las compras pagadas se suman automáticamente al Resumen de ingresos y quedan registradas en este historial.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <span class="badge text-bg-dark px-3 py-2"><?=number_format($productOrdersCount,0,',','.')?> compras pagadas</span>
      <span class="badge text-bg-primary px-3 py-2"><?=number_format($productUnits,0,',','.')?> unidades</span>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="info-tile"><small class="text-muted">Productos · Hoy</small><h5 class="mt-1 mb-0">$<?=number_format($productDay,0,',','.')?></h5></div></div>
    <div class="col-6 col-md-3"><div class="info-tile"><small class="text-muted">Productos · Semana</small><h5 class="mt-1 mb-0">$<?=number_format($productWeek,0,',','.')?></h5></div></div>
    <div class="col-6 col-md-3"><div class="info-tile"><small class="text-muted">Productos · 15 días</small><h5 class="mt-1 mb-0">$<?=number_format($product15,0,',','.')?></h5></div></div>
    <div class="col-6 col-md-3"><div class="info-tile"><small class="text-muted">Productos · Mes</small><h5 class="mt-1 mb-0">$<?=number_format($productMonth,0,',','.')?></h5></div></div>
  </div>

  <div class="d-flex align-items-center justify-content-end gap-2 mb-2" aria-live="polite">
    <span class="live-dot"></span>
    <small class="text-muted">Ventas actualizadas automáticamente cada 5 segundos · <?=e((new DateTime())->format('d/m/Y H:i:s'))?></small>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Compra</th><th>Cliente</th><th>Productos</th><th>Unidades</th><th>Total pagado</th><th>Estado</th><th>Fecha</th></tr></thead>
      <tbody>
        <?php foreach($ordersHistory as $o): ?>
          <tr>
            <td><span class="badge text-bg-secondary">#<?=e($o['id'])?></span></td>
            <td><strong><?=e($o['customer_name'])?></strong><?php if($o['customer_phone']): ?><br><small class="text-muted"><?=e($o['customer_phone'])?></small><?php endif; ?></td>
            <td><?=e($o['product_list'] ?: 'Sin productos')?></td>
            <td><?=number_format((int)$o['total_units'],0,',','.')?></td>
            <td class="fw-bold text-success">$<?=number_format($o['total'],0,',','.')?></td>
            <td><?php if($o['status']==='pagado'): ?><span class="badge text-bg-success">Pagado</span><?php elseif($o['status']==='cancelado'): ?><span class="badge text-bg-danger">Cancelado</span><?php else: ?><span class="badge text-bg-warning">Pendiente</span><?php endif; ?></td>
            <td><?= (new DateTime($o['created_at']))->format('d/m/Y H:i:s') ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$ordersHistory): ?><tr><td colspan="7" class="text-center text-muted py-4">No hay compras de productos registradas.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
