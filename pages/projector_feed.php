<?php
require_once __DIR__ . '/../config.php';
date_default_timezone_set('America/Bogota');

$today = (new DateTime())->format('Y-m-d');
$day = $_GET['day'] ?? $today;
$dayObj = DateTime::createFromFormat('Y-m-d', $day);
$validDay = $dayObj && $dayObj->format('Y-m-d') === $day;
if (!$validDay) {
  $day = $today;
  $dayObj = new DateTime($today);
}

$start = (clone $dayObj)->setTime(0, 0, 0);
$end   = (clone $dayObj)->setTime(23, 59, 59);
$dayLabel = $dayObj->format('d/m/Y');

$stmt = $pdo->prepare("SELECT a.code,a.appt_datetime,
    COALESCE(st.name,'Sin asignar') AS staff_name,
    st.type,
    s.name AS service_name
  FROM appointments a
  JOIN services s ON a.service_id=s.id
  LEFT JOIN staff st ON a.staff_id=st.id
  WHERE a.appt_datetime BETWEEN ? AND ?
    AND a.status IN ('pendiente','confirmada')
  ORDER BY a.appt_datetime ASC, st.sort_order ASC, a.id ASC");
$stmt->execute([
  $start->format('Y-m-d H:i:s'),
  $end->format('Y-m-d H:i:s')
]);
$rows = $stmt->fetchAll();
?>
<?php if($rows): ?>
  <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-3">
    <?php foreach($rows as $r): $dt = new DateTime($r['appt_datetime']); ?>
      <div class="col">
        <div class="card text-center h-100 projector-card <?=($r['type'] ?? '')==='beauty'?'projector-beauty':''?>">
          <div class="card-body d-flex flex-column justify-content-center">
            <div class="display-6 fw-bold">#<?=e($r['code'])?></div>
            <div class="fw-bold fs-5 mt-2"><?=e($r['staff_name'])?></div>
            <div class="text-muted small mb-2"><?=e($r['service_name'])?></div>
            <div class="projector-time"><i class="bi bi-clock me-1"></i><?=$dt->format('H:i')?></div>
            <div class="text-muted small mt-1"><?=$dt->format('d/m/Y')?></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="alert alert-info text-center mb-0 py-4">
    <i class="bi bi-calendar2-x fs-3 d-block mb-2"></i>
    No hay citas pendientes o confirmadas para el <?=e($dayLabel)?>.
  </div>
<?php endif; ?>
