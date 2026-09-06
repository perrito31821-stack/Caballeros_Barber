<?php
if (!is_staff()) {
  echo '<div class="alert alert-danger">Acceso restringido.</div>';
  return;
}

$staffId = (int)$_SESSION['user']['staff_id'];
$staffStmt = $pdo->prepare("SELECT id,name,type,username,active FROM staff WHERE id=? LIMIT 1");
$staffStmt->execute([$staffId]);
$staff = $staffStmt->fetch();
if (!$staff) {
  echo '<div class="alert alert-danger">No fue posible cargar tu perfil profesional.</div>';
  return;
}

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['staff_action'])) {
  $appointmentId = (int)$_POST['appointment_id'];
  $action = $_POST['staff_action'];

  if (!in_array($action, ['confirmar','cancelar'], true)) {
    $message = 'Acción no permitida.';
    $messageType = 'danger';
  } else {
    $newStatus = $action === 'confirmar' ? 'confirmada' : 'cancelada';
    $st = $pdo->prepare("UPDATE appointments SET status=? WHERE id=? AND staff_id=? AND status = 'pendiente'");
    $st->execute([$newStatus, $appointmentId, $staffId]);
    if ($st->rowCount() > 0) {
      $message = $action === 'confirmar' ? 'La cita fue confirmada correctamente.' : 'La cita fue cancelada correctamente.';
    } else {
      $message = 'La cita no existe, no pertenece a tu agenda o ya fue cerrada.';
      $messageType = 'warning';
    }
  }
}

$day = $_GET['day'] ?? (new DateTime())->format('Y-m-d');
$validDate = DateTime::createFromFormat('Y-m-d', $day);
if (!$validDate || $validDate->format('Y-m-d') !== $day) $day = (new DateTime())->format('Y-m-d');

$stmt = $pdo->prepare("SELECT a.*, s.name AS sname, s.price AS sprice, s.duration_minutes, u.name AS uname, u.phone AS uphone, u.email AS uemail
  FROM appointments a
  JOIN services s ON a.service_id=s.id
  JOIN users u ON a.user_id=u.id
  WHERE a.staff_id=? AND DATE(a.appt_datetime)=?
  ORDER BY a.appt_datetime ASC");
$stmt->execute([$staffId, $day]);
$appointments = $stmt->fetchAll();

$pendingCount = 0;
foreach ($appointments as $a) if ($a['status'] === 'pendiente') $pendingCount++;
?>
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
  <div>
    <span class="section-kicker">Panel profesional</span>
    <h2 class="section-title mb-1"><?=e($staff['name'])?></h2>
    <p class="text-muted mb-0">Aquí puedes confirmar o cancelar tus propias reservas. No tienes acceso a transferir turnos ni a modificar agendas de otros profesionales.</p>
  </div>
  <span class="badge text-bg-dark px-3 py-2"><i class="bi bi-person-check me-1"></i><?=e($staff['username'])?></span>
</div>

<?php if ($message): ?>
  <div class="alert alert-<?=e($messageType)?>"><i class="bi <?= $messageType==='success'?'bi-check-circle':'bi-info-circle' ?> me-2"></i><?=e($message)?></div>
<?php endif; ?>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="info-tile"><div class="info-icon"><i class="bi bi-calendar2-check"></i></div><h5>Reservas del día</h5><div class="display-6 fw-bold"><?=count($appointments)?></div></div></div>
  <div class="col-md-4"><div class="info-tile"><div class="info-icon"><i class="bi bi-hourglass-split"></i></div><h5>Pendientes</h5><div class="display-6 fw-bold"><?=$pendingCount?></div></div></div>
  <div class="col-md-4"><div class="info-tile"><div class="info-icon"><i class="bi bi-shield-check"></i></div><h5>Permisos</h5><p class="text-muted mb-0">Solo confirmar o cancelar tus reservas.</p></div></div>
</div>

<div class="bg-white border rounded-4 p-4 shadow-sm">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
    <div>
      <span class="section-kicker">Mi agenda</span>
      <h3 class="mb-1">Reservas del <?=e((new DateTime($day))->format('d/m/Y'))?></h3>
      <p class="text-muted mb-0">Las acciones se aplican exclusivamente a las citas asignadas a tu agenda.</p>
    </div>
    <form class="d-flex gap-2" method="get">
      <input type="hidden" name="p" value="staff_dashboard">
      <input type="date" class="form-control" name="day" value="<?=e($day)?>">
      <button class="btn btn-primary"><i class="bi bi-search me-1"></i>Consultar</button>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Código</th><th>Hora</th><th>Cliente</th><th>Servicio</th><th>Precio</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
      <tbody>
      <?php foreach ($appointments as $a): ?>
        <?php
          $status = $a['status'];
          if ($status==='completada') $badge='<span class="badge text-bg-success">Completada</span>';
          elseif ($status==='cancelada') $badge='<span class="badge text-bg-danger">Cancelada</span>';
          elseif ($status==='confirmada') $badge='<span class="badge text-bg-primary">Confirmada</span>';
          else $badge='<span class="badge text-bg-secondary">Pendiente</span>';
          $closed = in_array($status, ['completada','cancelada'], true);
        ?>
        <tr>
          <td><span class="badge text-bg-dark">#<?=e($a['code'])?></span></td>
          <td class="fw-bold"><?=e((new DateTime($a['appt_datetime']))->format('H:i'))?></td>
          <td><strong><?=e($a['uname'])?></strong><br><small class="text-muted"><?=e($a['uphone'])?></small></td>
          <td><?=e($a['sname'])?></td>
          <td>$<?=number_format($a['sprice'],0,',','.')?></td>
          <td><?=$badge?></td>
          <td class="text-end text-nowrap">
            <?php if (!$closed): ?>
              <form method="post" class="d-inline">
                <input type="hidden" name="appointment_id" value="<?=e($a['id'])?>">
                <button class="btn btn-sm btn-success" name="staff_action" value="confirmar" <?= $status === 'confirmada' ? 'disabled' : '' ?>><i class="bi bi-check2-circle me-1"></i>Confirmar</button>
                <button class="btn btn-sm btn-outline-danger" name="staff_action" value="cancelar" <?= $status === 'cancelada' ? 'disabled' : '' ?> onclick="return confirm('¿Deseas cancelar esta reserva?');"><i class="bi bi-x-circle me-1"></i>Cancelar</button>
              </form>
            <?php else: ?>
              <span class="text-muted small">Sin acciones</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$appointments): ?><tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-calendar-x fs-2 d-block mb-2"></i>No tienes reservas para esta fecha.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
(function () {
  const REFRESH_MS = 5000;
  let seconds = 5;
  let timer = null;
  let reloading = false;

  const badge = document.createElement('div');
  badge.id = 'staff-live-refresh';
  badge.className = 'position-fixed bottom-0 end-0 m-3 px-3 py-2 bg-dark text-white rounded-pill shadow-sm small';
  badge.style.zIndex = '1080';
  badge.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Actualización automática en <strong>5</strong> s';
  document.body.appendChild(badge);

  const counter = badge.querySelector('strong');

  function refreshNow() {
    if (reloading) return;
    reloading = true;
    badge.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Actualizando...';
    // Mantiene la fecha seleccionada y solicita nuevamente los datos del servidor.
    window.location.reload();
  }

  function tick() {
    if (document.hidden) return;
    seconds -= 1;
    if (seconds <= 0) {
      refreshNow();
      return;
    }
    if (counter) counter.textContent = seconds;
  }

  timer = window.setInterval(tick, 1000);

  document.addEventListener('visibilitychange', function () {
    if (!document.hidden && !reloading) {
      seconds = 5;
      if (counter) counter.textContent = seconds;
    }
  });
})();
</script>
