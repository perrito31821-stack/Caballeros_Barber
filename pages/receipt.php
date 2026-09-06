<?php
$data = $_SESSION['receipt'] ?? null;
if (isset($_GET['aid'])) {
  $aid = (int)$_GET['aid'];
  $st = $pdo->prepare("SELECT id,code FROM appointments WHERE id=?");
  $st->execute([$aid]);
  if ($a = $st->fetch()) $data = ['type'=>'appointment','id'=>$aid,'code'=>$a['code']];
}
if (!$data) { echo '<div class="alert alert-warning">No hay recibo para mostrar.</div>'; return; }
?>
<div class="bg-white border rounded-4 p-4 shadow-sm">
  <div class="no-print text-end"><button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Imprimir</button></div>
  <?php if($data['type']==='appointment'):
    $aid = $data['id'];
    $st = $pdo->prepare("SELECT a.*, s.name AS sname, s.price AS sprice, u.name AS uname, u.phone AS uphone, u.email AS uemail, COALESCE(stf.name,'Sin asignar') AS staff_name
      FROM appointments a
      JOIN services s ON a.service_id=s.id
      JOIN users u ON a.user_id=u.id
      LEFT JOIN staff stf ON a.staff_id=stf.id
      WHERE a.id=?");
    $st->execute([$aid]); $a=$st->fetch();
  ?>
  <div class="text-center py-2">
    <div class="brand-mark mx-auto mb-2"><span>CB</span></div>
    <h3 class="mb-0">Recibo de Reserva</h3>
    <div class="small text-muted">Caballeros Barber · Carrera 57a # 35a - 97 (Frente a la Iglesia de San Juan Bosco - Bello)</div>
  </div>
  <hr>
  <div class="row g-2">
    <div class="col-md-6"><strong>Código:</strong> #<?=e($a['code'])?></div>
    <div class="col-md-6"><strong>Estado:</strong> <?=e(ucfirst($a['status']))?></div>
    <div class="col-12"><strong>Cliente:</strong> <?=e($a['uname'])?> — <?=e($a['uphone'])?> — <?=e($a['uemail'])?></div>
    <div class="col-md-6"><strong>Servicio:</strong> <?=e($a['sname'])?> — $<?=number_format($a['sprice'],0,',','.')?></div>
    <div class="col-md-6"><strong>Profesional / sección:</strong> <?=e($a['staff_name'])?></div>
    <div class="col-12"><strong>Fecha/Hora:</strong> <?= (new DateTime($a['appt_datetime']))->format('d/m/Y H:i') ?></div>
  </div>
  <hr>
  <div class="no-print">
    <a class="btn btn-success" target="_blank" rel="noopener" href="https://wa.me/<?=preg_replace('/\D+/', '', $ADMIN_WHATSAPP)?>?text=Nueva%20reserva%20%23<?=urlencode($a['code'])?>%20<?=urlencode((new DateTime($a['appt_datetime']))->format('Y-m-d H:i'))?>%20<?=urlencode($a['staff_name'])?>%20Cliente:%20<?=urlencode($a['uname'])?>"><i class="bi bi-whatsapp me-1"></i>Enviar confirmación por WhatsApp</a>
  </div>
  <div class="print-only"><p>Gracias por elegir Caballeros Barber.</p></div>
  <?php else:
    $oid = $data['id'];
    $st = $pdo->prepare("SELECT o.*, u.name AS uname, u.email AS uemail FROM orders o JOIN users u ON o.user_id=u.id WHERE o.id=?");
    $st->execute([$oid]); $o=$st->fetch();
    $its = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
    $its->execute([$oid]);
  ?>
  <div class="text-center py-2">
    <div class="brand-mark mx-auto mb-2"><span>CB</span></div>
    <h3 class="mb-0">Recibo de Compra</h3>
    <div class="small text-muted">Caballeros Barber</div>
  </div>
  <hr>
  <div><strong>Cliente:</strong> <?=e($o['uname'])?> — <?=e($o['uemail'])?></div>
  <table class="table mt-3">
    <thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead>
    <tbody>
      <?php $total=0; foreach($its as $it): $sub=$it['qty']*$it['price']; $total+=$sub; ?>
        <tr><td><?=e($it['name'])?></td><td><?=$it['qty']?></td><td>$<?=number_format($it['price'],0,',','.')?></td><td>$<?=number_format($sub,0,',','.')?></td></tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot><tr><th colspan="3" class="text-end">Total</th><th>$<?=number_format($total,0,',','.')?></th></tr></tfoot>
  </table>
  <?php endif; ?>
</div>
