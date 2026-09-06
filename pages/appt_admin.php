<?php
if (!is_admin()) {
  echo '<div class="alert alert-danger">Acceso restringido.</div>';
  return;
}

if (isset($_POST['status'], $_POST['id'])) {
  $id = (int)$_POST['id'];
  $status = $_POST['status'];
  $allowedStatus = ['pendiente','confirmada','completada','cancelada'];
  if (in_array($status, $allowedStatus, true)) {
    $st = $pdo->prepare("UPDATE appointments SET status=? WHERE id=?");
    $st->execute([$status, $id]);

    if ($status === 'completada' && isset($_POST['price'], $_POST['method'])) {
      $method = $_POST['method'];
      if (in_array($method, ['efectivo','transferencia','tarjeta'], true)) {
        $exists = $pdo->prepare("SELECT id FROM payments WHERE appointment_id=? LIMIT 1");
        $exists->execute([$id]);
        if (!$exists->fetch()) {
          $sp = $pdo->prepare("INSERT INTO payments (appointment_id,method,amount) VALUES (?,?,?)");
          $sp->execute([$id, $method, (float)$_POST['price']]);
        }
      }
    }
  }
}

if (isset($_POST['delete_payment'])) {
  $del = $pdo->prepare("DELETE FROM payments WHERE id=?");
  $del->execute([(int)$_POST['delete_payment']]);
  echo '<div class="alert alert-warning">Pago eliminado correctamente.</div>';
}

if (isset($_POST['delete_product_sale'])) {
  $orderId = (int)$_POST['delete_product_sale'];
  $pdo->beginTransaction();
  try {
    // Solo se pueden eliminar compras de productos (no órdenes asociadas a una cita).
    $orderStmt = $pdo->prepare("SELECT id, appointment_id, status FROM orders WHERE id=? LIMIT 1");
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch();
    if (!$order || $order['appointment_id'] !== null) {
      throw new Exception('La venta seleccionada no corresponde a una compra de productos.');
    }

    // Devolver al inventario las unidades de la compra eliminada.
    $itemsStmt = $pdo->prepare("SELECT product_id, qty FROM order_items WHERE order_id=?");
    $itemsStmt->execute([$orderId]);
    $restore = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id=?");
    foreach ($itemsStmt->fetchAll() as $item) {
      $restore->execute([(int)$item['qty'], (int)$item['product_id']]);
    }

    $pdo->prepare("DELETE FROM order_items WHERE order_id=?")->execute([$orderId]);
    $pdo->prepare("DELETE FROM orders WHERE id=?")->execute([$orderId]);
    $pdo->commit();
    echo '<div class="alert alert-warning">La venta de productos #'.e($orderId).' fue eliminada y las unidades fueron devueltas al inventario.</div>';
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo '<div class="alert alert-danger">No fue posible eliminar la venta: '.e($e->getMessage()).'</div>';
  }
}

$day = $_GET['day'] ?? (new DateTime())->format('Y-m-d');
$staffList = $pdo->query("SELECT id,name,type FROM staff WHERE active=1 ORDER BY sort_order,id")->fetchAll();

$stmt = $pdo->prepare("SELECT a.*, s.name AS sname, s.price AS sprice, u.name AS uname, u.phone AS uphone,
  COALESCE(st.name,'Sin asignar') AS staff_name, st.type AS staff_type
  FROM appointments a
  JOIN services s ON a.service_id=s.id
  JOIN users u ON a.user_id=u.id
  LEFT JOIN staff st ON a.staff_id=st.id
  WHERE DATE(a.appt_datetime)=?
  ORDER BY st.sort_order, a.appt_datetime ASC");
$stmt->execute([$day]);
$rows = $stmt->fetchAll();

$grouped = [];
foreach ($rows as $r) {
  $key = (int)($r['staff_id'] ?? 0);
  $grouped[$key][] = $r;
}

function status_badge($status) {
  if ($status==='completada') return '<span class="badge text-bg-success">Completada</span>';
  if ($status==='cancelada') return '<span class="badge text-bg-danger">Cancelada</span>';
  if ($status==='confirmada') return '<span class="badge text-bg-primary">Confirmada</span>';
  return '<span class="badge text-bg-secondary">Pendiente</span>';
}
?>
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
  <div>
    <span class="section-kicker">Administración de turnos</span>
    <h2 class="section-title mb-1">Agendas por profesional</h2>
    <p class="text-muted mb-0">Cada sección funciona de forma independiente; por eso pueden existir varias citas a la misma hora.</p>
  </div>
  <form class="d-flex gap-2" method="get">
    <input type="hidden" name="p" value="appt_admin">
    <input type="date" class="form-control" name="day" value="<?=e($day)?>">
    <button class="btn btn-primary">Consultar</button>
  </form>
</div>

<div class="d-flex align-items-center justify-content-end gap-2 mb-3 admin-live-status" aria-live="polite">
  <span class="live-dot"></span>
  <small class="text-muted">Actualización automática cada 5 segundos · <span id="adminLiveTime">conectando...</span></small>
</div>

<div id="adminAgendaLive" data-day="<?=e($day)?>">
  <?php include __DIR__ . '/../partials/appt_agendas.php'; ?>
</div>

<script>
(function(){
  const container = document.getElementById('adminAgendaLive');
  const liveTime = document.getElementById('adminLiveTime');
  if (!container) return;

  let lastHtml = container.innerHTML.trim();
  let pendingHtml = null;
  let loading = false;

  function isInteracting(){
    const active = document.activeElement;
    return active && container.contains(active) && ['INPUT','SELECT','BUTTON','TEXTAREA'].includes(active.tagName);
  }

  function stamp(message){
    if (!liveTime) return;
    const now = new Date();
    liveTime.textContent = message || ('última revisión ' + now.toLocaleTimeString('es-CO'));
  }

  async function refreshAgendas(){
    if (loading || document.hidden) return;
    loading = true;
    try {
      const day = container.dataset.day;
      const response = await fetch('appt_admin_feed.php?day=' + encodeURIComponent(day) + '&_=' + Date.now(), {
        credentials: 'same-origin',
        cache: 'no-store',
        headers: {'X-Requested-With':'XMLHttpRequest'}
      });
      if (!response.ok) throw new Error('HTTP ' + response.status);
      const html = (await response.text()).trim();
      if (html !== lastHtml) {
        if (isInteracting()) {
          pendingHtml = html;
          stamp('cambio detectado · se aplicará al terminar la acción');
        } else {
          container.innerHTML = html;
          lastHtml = html;
          pendingHtml = null;
          stamp();
        }
      } else {
        stamp();
      }
    } catch (err) {
      stamp('sin conexión · reintentando');
    } finally {
      loading = false;
    }
  }

  container.addEventListener('focusout', function(){
    if (pendingHtml) {
      setTimeout(function(){
        if (!isInteracting() && pendingHtml) {
          container.innerHTML = pendingHtml;
          lastHtml = pendingHtml;
          pendingHtml = null;
          stamp();
        }
      }, 100);
    }
  });

  stamp();
  setInterval(refreshAgendas, 5000);
})();
</script>

<?php
// Ingresos separados por cada profesional/sección.
$qStaffIncome = $pdo->query("SELECT
    st.id,
    st.name,
    st.type,
    COALESCE(SUM(CASE WHEN DATE(p.created_at)=CURDATE() THEN p.amount ELSE 0 END),0) AS total_day,
    COALESCE(SUM(CASE WHEN YEARWEEK(p.created_at,1)=YEARWEEK(CURDATE(),1) THEN p.amount ELSE 0 END),0) AS total_week,
    COALESCE(SUM(CASE WHEN p.created_at >= DATE_SUB(NOW(), INTERVAL 15 DAY) THEN p.amount ELSE 0 END),0) AS total_15,
    COALESCE(SUM(CASE WHEN MONTH(p.created_at)=MONTH(CURDATE()) AND YEAR(p.created_at)=YEAR(CURDATE()) THEN p.amount ELSE 0 END),0) AS total_month
  FROM staff st
  LEFT JOIN appointments a ON a.staff_id=st.id
  LEFT JOIN payments p ON p.appointment_id=a.id
  WHERE st.active=1
  GROUP BY st.id,st.name,st.type,st.sort_order
  ORDER BY st.sort_order,st.id");
$staffIncome = $qStaffIncome->fetchAll();

// Historial de servicios cobrados.
$qServ = $pdo->query("SELECT p.id AS pid, s.name AS servicio, COALESCE(st.name,'Sin asignar') AS staff_name, p.amount, p.method, p.created_at
  FROM payments p
  JOIN appointments a ON p.appointment_id=a.id
  JOIN services s ON a.service_id=s.id
  LEFT JOIN staff st ON a.staff_id=st.id
  ORDER BY p.created_at DESC");
$pagos = $qServ->fetchAll();

// Historial de productos vendidos para administración.
$qProdSales = $pdo->query("SELECT
    o.id AS order_id, o.total, o.status, o.created_at,
    u.name AS customer_name, u.phone AS customer_phone,
    GROUP_CONCAT(CONCAT(COALESCE(p.name,'Producto eliminado'), ' × ', oi.qty) ORDER BY p.name SEPARATOR ' · ') AS product_list,
    SUM(oi.qty) AS total_units
  FROM orders o
  JOIN users u ON o.user_id=u.id
  LEFT JOIN order_items oi ON oi.order_id=o.id
  LEFT JOIN products p ON p.id=oi.product_id
  WHERE o.appointment_id IS NULL
  GROUP BY o.id,o.total,o.status,o.created_at,u.name,u.phone
  ORDER BY o.created_at DESC");
$prodSales = $qProdSales->fetchAll();
?>
<div id="productSalesLive">
  <?php include __DIR__ . '/../partials/product_sales_live.php'; ?>
</div>

<script>
(function(){
  const container = document.getElementById('productSalesLive');
  if (!container) return;
  let loading = false;

  async function refreshProductSales(){
    if (loading || document.hidden) return;
    loading = true;
    try {
      const response = await fetch('product_sales_feed.php?_=' + Date.now(), {
        credentials: 'same-origin',
        cache: 'no-store',
        headers: {'X-Requested-With':'XMLHttpRequest'}
      });
      if (!response.ok) throw new Error('HTTP ' + response.status);
      const html = (await response.text()).trim();
      if (html && html !== container.innerHTML.trim()) {
        container.innerHTML = html;
      }
    } catch (err) {
      // La siguiente ejecución volverá a intentar la consulta.
    } finally {
      loading = false;
    }
  }

  setInterval(refreshProductSales, 5000);
})();
</script>

<div class="bg-white border rounded-4 p-4 mt-4 shadow-sm">
  <span class="section-kicker">Historial</span>
  <h3 class="mb-1">Servicios realizados</h3>
  <p class="text-muted mb-4">Consulta cuánto ha generado cada barbero y la sección de Belleza, además del detalle de cada servicio cobrado.</p>

  <div class="income-by-staff mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-3">
      <div>
        <h5 class="mb-1"><i class="bi bi-bar-chart-line me-2"></i>Ingresos por profesional</h5>
        <small class="text-muted">Los valores se calculan únicamente con citas que ya tienen un pago registrado.</small>
      </div>
      <span class="badge text-bg-dark px-3 py-2">Actualizado al <?=e((new DateTime())->format('d/m/Y H:i'))?></span>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle income-table mb-0">
        <thead>
          <tr><th>Profesional</th><th>Hoy</th><th>Semana</th><th>15 días</th><th>Mes</th></tr>
        </thead>
        <tbody>
          <?php foreach($staffIncome as $income): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <span class="income-staff-icon <?=$income['type']==='beauty'?'beauty':''?>"><i class="bi <?=$income['type']==='beauty'?'bi-stars':'bi-scissors'?>"></i></span>
                  <div><strong><?=e($income['name'])?></strong><br><small class="text-muted"><?=$income['type']==='beauty'?'Belleza':'Barbería'?></small></div>
                </div>
              </td>
              <td class="fw-bold">$<?=number_format($income['total_day'],0,',','.')?></td>
              <td class="fw-bold">$<?=number_format($income['total_week'],0,',','.')?></td>
              <td class="fw-bold">$<?=number_format($income['total_15'],0,',','.')?></td>
              <td class="fw-bold text-primary">$<?=number_format($income['total_month'],0,',','.')?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
    <h5 class="mb-0">Detalle de servicios cobrados</h5>
    <span class="text-muted small"><?=count($pagos)?> registro<?=count($pagos)===1?'':'s'?></span>
  </div>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead><tr><th>Servicio</th><th>Profesional</th><th>Valor</th><th>Método</th><th>Fecha</th><th class="text-end">Acciones</th></tr></thead>
      <tbody>
        <?php foreach($pagos as $p): ?>
          <tr>
            <td><?=e($p['servicio'])?></td><td><?=e($p['staff_name'])?></td>
            <td class="fw-bold">$<?=number_format($p['amount'],0,',','.')?></td><td><?=e($p['method'])?></td>
            <td><?= (new DateTime($p['created_at']))->format('d/m/Y H:i') ?></td>
            <td class="text-end"><form method="post" onsubmit="return confirm('¿Eliminar este pago?');"><input type="hidden" name="delete_payment" value="<?=$p['pid']?>"><button class="btn btn-sm btn-outline-danger">Eliminar</button></form></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$pagos): ?><tr><td colspan="6" class="text-center text-muted py-4">No hay pagos registrados.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="bg-white border rounded-4 p-4 mt-4 shadow-sm">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-3">
    <div>
      <span class="section-kicker">Productos vendidos</span>
      <h5 class="mb-1">Detalle de productos cobrados</h5>
      <p class="text-muted mb-0">Historial de compras de productos. Solo los administradores pueden eliminar una venta.</p>
    </div>
    <span class="badge text-bg-dark px-3 py-2"><?=count($prodSales)?> venta<?=count($prodSales)===1?'':'s'?></span>
  </div>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead><tr><th>Compra</th><th>Cliente</th><th>Productos</th><th>Unidades</th><th>Total</th><th>Estado</th><th>Fecha</th><th class="text-end">Acciones</th></tr></thead>
      <tbody>
        <?php foreach($prodSales as $sale): ?>
          <tr>
            <td><span class="badge text-bg-secondary">#<?=e($sale['order_id'])?></span></td>
            <td><strong><?=e($sale['customer_name'])?></strong><?php if($sale['customer_phone']): ?><br><small class="text-muted"><?=e($sale['customer_phone'])?></small><?php endif; ?></td>
            <td><?=e($sale['product_list'] ?: 'Sin productos')?></td>
            <td><?=number_format((int)$sale['total_units'],0,',','.')?></td>
            <td class="fw-bold text-success">$<?=number_format($sale['total'],0,',','.')?></td>
            <td><?php if($sale['status']==='pagado'): ?><span class="badge text-bg-success">Pagado</span><?php elseif($sale['status']==='cancelado'): ?><span class="badge text-bg-danger">Cancelado</span><?php else: ?><span class="badge text-bg-warning">Pendiente</span><?php endif; ?></td>
            <td><?= (new DateTime($sale['created_at']))->format('d/m/Y H:i:s') ?></td>
            <td class="text-end">
              <form method="post" onsubmit="return confirm('¿Eliminar la venta #<?=e($sale['order_id'])?>? Las unidades serán devueltas al inventario y el ingreso se descontará del resumen.');">
                <input type="hidden" name="delete_product_sale" value="<?=e($sale['order_id'])?>">
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Eliminar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$prodSales): ?><tr><td colspan="8" class="text-center text-muted py-4">No hay ventas de productos registradas.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
