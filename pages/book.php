<?php
if (!is_logged()) {
  echo '<div class="alert alert-info">Por favor inicia sesión para reservar.</div>';
  include __DIR__.'/login.php';
  return;
}

$service_id = isset($_GET['sid']) ? (int)$_GET['sid'] : null;
$staff_id = isset($_GET['staff']) ? (int)$_GET['staff'] : null;
$dt = '';
$notes = '';
$bookingFeedback = '';

$staffList = $pdo->query("SELECT id,name,type FROM staff WHERE active=1 ORDER BY sort_order,id")->fetchAll();
$services = $pdo->query("SELECT * FROM services WHERE active=1 ORDER BY category,name")->fetchAll();

/**
 * Comprueba si el intervalo solicitado se cruza con otra cita activa del profesional.
 * La duración se toma del servicio configurado, tanto para la nueva cita como para las existentes.
 */
function cb_staff_has_conflict(PDO $pdo, int $staffId, DateTime $start, int $durationMinutes): bool {
  $durationMinutes = max(1, $durationMinutes);
  $end = clone $start;
  $end->modify('+' . $durationMinutes . ' minutes');

  $sql = "SELECT COUNT(*)
          FROM appointments a
          JOIN services existing_service ON existing_service.id=a.service_id
          WHERE a.staff_id=?
            AND a.status IN ('pendiente','confirmada','completada')
            AND a.appt_datetime < ?
            AND DATE_ADD(a.appt_datetime, INTERVAL existing_service.duration_minutes MINUTE) > ?";
  $st = $pdo->prepare($sql);
  $st->execute([
    $staffId,
    $end->format('Y-m-d H:i:s'),
    $start->format('Y-m-d H:i:s')
  ]);
  return (int)$st->fetchColumn() > 0;
}

/**
 * Horario de barbería: todos los días de 09:00 a 22:00.
 * El servicio debe caber completo dentro de esa franja. Belleza no usa esta restricción.
 */
function cb_barber_time_allowed(DateTime $start, int $durationMinutes): bool {
  $open = clone $start;
  $open->setTime(9, 0, 0);
  $close = clone $start;
  $close->setTime(22, 0, 0);
  $end = clone $start;
  $end->modify('+' . max(1, $durationMinutes) . ' minutes');
  return $start >= $open && $end <= $close;
}

/**
 * Busca próximos horarios disponibles del mismo profesional dentro del mismo día.
 * Para barbería respeta 09:00-22:00; Belleza conserva el comportamiento anterior.
 */
function cb_next_free_slots(PDO $pdo, int $staffId, string $staffType, DateTime $requested, int $durationMinutes, int $limit=6): array {
  $slots = [];
  $cursor = clone $requested;
  $cursor->modify('+5 minutes');

  $dayLimit = clone $requested;
  if ($staffType === 'barber') {
    $opening = clone $requested;
    $opening->setTime(9, 0, 0);
    if ($cursor < $opening) $cursor = $opening;
    $dayLimit->setTime(22, 0, 0);
  } else {
    $dayLimit->setTime(23, 59, 59);
  }

  while ($cursor <= $dayLimit && count($slots) < $limit) {
    $candidateEnd = clone $cursor;
    $candidateEnd->modify('+' . max(1, $durationMinutes) . ' minutes');
    if ($candidateEnd > $dayLimit) break;

    if (($staffType !== 'barber' || cb_barber_time_allowed($cursor, $durationMinutes))
        && !cb_staff_has_conflict($pdo, $staffId, $cursor, $durationMinutes)) {
      $slots[] = clone $cursor;
      $cursor->modify('+' . max(5, $durationMinutes) . ' minutes');
    } else {
      $cursor->modify('+5 minutes');
    }
  }
  return $slots;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $service_id = (int)($_POST['service_id'] ?? 0);
  $staff_id = (int)($_POST['staff_id'] ?? 0);
  $dt = $_POST['datetime'] ?? '';
  $notes = trim($_POST['notes'] ?? '');
  $code = substr(strtoupper(bin2hex(random_bytes(3))),0,6);

  $st = $pdo->prepare("SELECT id,name,type FROM staff WHERE id=? AND active=1");
  $st->execute([$staff_id]);
  $staff = $st->fetch();

  $sv = $pdo->prepare("SELECT id,name,duration_minutes,category FROM services WHERE id=? AND active=1");
  $sv->execute([$service_id]);
  $svc = $sv->fetch();

  if (!$staff) {
    $bookingFeedback = '<div class="alert alert-danger">Selecciona un profesional o la sección de belleza.</div>';
  } elseif (!$svc) {
    $bookingFeedback = '<div class="alert alert-danger">Servicio inválido.</div>';
  } elseif (($staff['type']==='barber' && $svc['category']!=='barberia') || ($staff['type']==='beauty' && $svc['category']!=='belleza')) {
    $bookingFeedback = '<div class="alert alert-warning">El servicio seleccionado no corresponde a esta sección. Elige un servicio compatible.</div>';
  } else {
    $dti = DateTime::createFromFormat('Y-m-d\TH:i', $dt);
    $now = new DateTime();
    $duration = max(1, (int)$svc['duration_minutes']);

    if (!$dti) {
      $bookingFeedback = '<div class="alert alert-danger">Fecha/hora inválida.</div>';
    } elseif ($dti < $now) {
      $bookingFeedback = '<div class="alert alert-warning">Selecciona una fecha y hora futura.</div>';
    } elseif ($staff['type'] === 'barber' && !cb_barber_time_allowed($dti, $duration)) {
      $bookingFeedback = '<div class="alert alert-warning"><strong>Horario de barbería:</strong> los turnos de Barbero 1 al 6 se atienden todos los días entre las 09:00 y las 22:00. El servicio seleccionado debe finalizar máximo a las 22:00. Elige otra hora.</div>';
    } else {
      // Disponibilidad independiente por profesional, respetando la duración completa del servicio.
      $conflict = cb_staff_has_conflict($pdo, $staff_id, $dti, $duration);

      if ($conflict) {
        $sameTimeFreeStaff = [];

        // Para Barbería, primero buscamos otros barberos libres exactamente a la misma hora.
        if ($staff['type'] === 'barber') {
          $otherStaff = $pdo->prepare("SELECT id,name,type FROM staff WHERE active=1 AND type='barber' AND id<>? ORDER BY sort_order,id");
          $otherStaff->execute([$staff_id]);
          foreach ($otherStaff->fetchAll() as $candidateStaff) {
            if (!cb_staff_has_conflict($pdo, (int)$candidateStaff['id'], $dti, $duration)) {
              $sameTimeFreeStaff[] = $candidateStaff;
            }
          }
        }

        // También buscamos próximos espacios para el profesional originalmente elegido.
        $nextSlots = cb_next_free_slots($pdo, $staff_id, $staff['type'], $dti, $duration, 6);

        ob_start();
        ?>
        <div class="availability-panel mb-4">
          <div class="availability-title">
            <div class="availability-icon"><i class="bi bi-calendar-x"></i></div>
            <div>
              <span class="section-kicker">Turno ocupado</span>
              <h4 class="mb-1"><?=e($staff['name'])?> ya tiene una cita en ese horario</h4>
              <p class="mb-0 text-muted">Tu servicio <strong><?=e($svc['name'])?></strong> dura <?=$duration?> minutos. Puedes conservar la misma hora con otro barbero libre o mantener tu barbero preferido y elegir un horario posterior.</p>
            </div>
          </div>

          <?php if ($sameTimeFreeStaff): ?>
            <div class="availability-block">
              <h6><i class="bi bi-people me-2"></i>Misma fecha y hora con otro barbero</h6>
              <p class="small text-muted mb-2"><?=e($dti->format('d/m/Y H:i'))?> está disponible con:</p>
              <div class="d-flex flex-wrap gap-2">
                <?php foreach($sameTimeFreeStaff as $freeStaff): ?>
                  <form method="post" class="d-inline">
                    <input type="hidden" name="staff_id" value="<?=(int)$freeStaff['id']?>">
                    <input type="hidden" name="service_id" value="<?=(int)$service_id?>">
                    <input type="hidden" name="datetime" value="<?=e($dti->format('Y-m-d\TH:i'))?>">
                    <input type="hidden" name="notes" value="<?=e($notes)?>">
                    <button class="btn btn-success"><i class="bi bi-person-check me-1"></i><?=e($freeStaff['name'])?> · <?=$dti->format('H:i')?></button>
                  </form>
                <?php endforeach; ?>
              </div>
            </div>
          <?php else: ?>
            <?php if ($staff['type']==='barber'): ?>
              <div class="availability-block">
                <h6><i class="bi bi-people me-2"></i>Otros barberos a la misma hora</h6>
                <p class="small text-muted mb-0">En este momento no hay otro barbero libre exactamente a las <?=$dti->format('H:i')?> para cubrir la duración completa de este servicio.</p>
              </div>
            <?php endif; ?>
          <?php endif; ?>

          <div class="availability-block">
            <h6><i class="bi bi-clock-history me-2"></i>Próximos horarios con <?=e($staff['name'])?></h6>
            <?php if ($nextSlots): ?>
              <div class="d-flex flex-wrap gap-2">
                <?php foreach($nextSlots as $slot): ?>
                  <form method="post" class="d-inline">
                    <input type="hidden" name="staff_id" value="<?=(int)$staff_id?>">
                    <input type="hidden" name="service_id" value="<?=(int)$service_id?>">
                    <input type="hidden" name="datetime" value="<?=e($slot->format('Y-m-d\TH:i'))?>">
                    <input type="hidden" name="notes" value="<?=e($notes)?>">
                    <button class="btn btn-outline-success"><i class="bi bi-clock me-1"></i><?=$slot->format('H:i')?></button>
                  </form>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="small text-muted mb-0">No encontramos otro espacio disponible más adelante durante ese mismo día para completar este servicio. Puedes escoger otra fecha.</p>
            <?php endif; ?>
          </div>
        </div>
        <?php
        $bookingFeedback = ob_get_clean();
      } else {
        // Revalidación e inserción de la cita. Agendas distintas pueden coincidir en fecha y hora.
        $ins = $pdo->prepare("INSERT INTO appointments (user_id,service_id,staff_id,appt_datetime,notes,code) VALUES (?,?,?,?,?,?)");
        $ins->execute([$_SESSION['user']['id'],$service_id,$staff_id,$dti->format('Y-m-d H:i:00'),$notes,$code]);
        $appt_id = $pdo->lastInsertId();
        $_SESSION['receipt'] = ['type'=>'appointment','id'=>$appt_id,'code'=>$code];
        redirect('receipt');
      }
    }
  }
}

$minDateTime = (new DateTime())->format('Y-m-d\TH:i');
?>
<div class="booking-shell">
  <div class="booking-head">
    <span class="hero-eyebrow"><i class="bi bi-calendar2-check"></i> Reserva en pocos pasos</span>
    <h2 class="mt-2 mb-2">Elige tu sección y tu turno</h2>
    <p>Cada profesional tiene agenda independiente. La misma hora puede estar disponible para varios barberos y para belleza.</p>
  </div>
  <div class="booking-body">
    <?=$bookingFeedback?>

    <form method="post" class="row g-4" id="bookingForm">
      <div class="col-12">
        <label class="form-label mb-2">1. ¿Quién te atenderá?</label>
        <div class="staff-grid">
          <?php foreach($staffList as $st): ?>
            <div class="staff-option">
              <input type="radio" name="staff_id" id="staff-<?=$st['id']?>" value="<?=$st['id']?>" data-type="<?=e($st['type'])?>" <?= $staff_id==$st['id']?'checked':'' ?> required>
              <label class="staff-card <?=$st['type']==='beauty'?'beauty':''?>" for="staff-<?=$st['id']?>">
                <span class="staff-avatar"><i class="bi <?=$st['type']==='beauty'?'bi-stars':'bi-scissors'?>"></i></span>
                <?php if ($st['type']==='barber'): ?>
                  <span class="staff-number">Barbero <?=((int)$st['id'])?></span>
                  <strong class="staff-name"><?=e($st['name'])?></strong>
                <?php else: ?>
                  <strong class="staff-name"><?=e($st['name'])?></strong>
                <?php endif; ?>
                <span class="staff-type"><?=$st['type']==='beauty'?'Uñas · pelo · pestañas · más':'Agenda de barbería'?></span>
              </label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="col-md-6">
        <label class="form-label">2. Servicio</label>
        <select name="service_id" id="serviceSelect" class="form-select" required>
          <option value="">Seleccione primero una sección...</option>
          <?php foreach($services as $s): ?>
            <option value="<?=$s['id']?>" data-category="<?=e($s['category'])?>" data-duration="<?=e($s['duration_minutes'])?>" <?= $service_id==$s['id']?'selected':'' ?>>
              <?=e($s['name'])?> (<?=e($s['duration_minutes'])?> min) — $<?=number_format($s['price'],0,',','.')?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="form-text" id="serviceHint">La duración configurada del servicio se usa para calcular cruces y sugerir horarios realmente disponibles.</div>
      </div>
      <div class="col-md-6">
        <label class="form-label">3. Fecha y hora</label>
        <input type="datetime-local" name="datetime" id="bookingDateTime" class="form-control" min="<?=e($minDateTime)?>" step="300" value="<?=e($dt)?>" required>
        <div class="form-text" id="bookingHoursHint">Selecciona el profesional para consultar su horario.</div>
      </div>
      <div class="col-12">
        <label class="form-label">Notas</label>
        <input type="text" name="notes" class="form-control" maxlength="255" value="<?=e($notes)?>" placeholder="Ej: estilo de corte, diseño, color o detalle que deseas...">
      </div>
      <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-primary btn-lg px-4"><i class="bi bi-check2-circle me-2"></i>Confirmar reserva</button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  const radios = document.querySelectorAll('input[name="staff_id"]');
  const select = document.getElementById('serviceSelect');
  const dateTime = document.getElementById('bookingDateTime');
  const hoursHint = document.getElementById('bookingHoursHint');
  const form = document.getElementById('bookingForm');
  const originalOptions = Array.from(select.options).map(o => ({
    value: o.value,
    text: o.text,
    category: o.dataset.category || '',
    duration: parseInt(o.dataset.duration || '0', 10),
    selected: o.selected
  }));

  function applyFilter(){
    const chosen = document.querySelector('input[name="staff_id"]:checked');
    const wanted = chosen ? (chosen.dataset.type === 'beauty' ? 'belleza' : 'barberia') : '';
    const current = select.value;
    select.innerHTML = '<option value="">Seleccione...</option>';
    originalOptions.forEach(o => {
      if (!o.value || (wanted && o.category !== wanted)) return;
      if (!wanted && o.value) return;
      const opt = document.createElement('option');
      opt.value = o.value;
      opt.textContent = o.text;
      opt.dataset.category = o.category;
      opt.dataset.duration = String(o.duration || 0);
      if (o.value === current || (!current && o.selected)) opt.selected = true;
      select.appendChild(opt);
    });
    if (select.selectedIndex < 0) select.selectedIndex = 0;
    updateHoursHint();
    validateBarberHours(false);
  }

  function selectedDuration(){
    const opt = select.options[select.selectedIndex];
    return opt ? parseInt(opt.dataset.duration || '0', 10) : 0;
  }

  function updateHoursHint(){
    const chosen = document.querySelector('input[name="staff_id"]:checked');
    if (!chosen) {
      hoursHint.textContent = 'Selecciona el profesional para consultar su horario.';
      return;
    }
    if (chosen.dataset.type === 'barber') {
      const duration = selectedDuration();
      if (duration > 0) {
        const latest = 22 * 60 - duration;
        const hh = String(Math.floor(latest / 60)).padStart(2,'0');
        const mm = String(latest % 60).padStart(2,'0');
        hoursHint.textContent = 'Barbería: lunes a domingo de 09:00 a 22:00. Para este servicio, la última hora de inicio es ' + hh + ':' + mm + '.';
      } else {
        hoursHint.textContent = 'Barbería: lunes a domingo de 09:00 a 22:00.';
      }
    } else {
      hoursHint.textContent = 'Belleza conserva sus horarios actuales sin cambios.';
    }
  }

  function validateBarberHours(showMessage){
    if (!dateTime) return true;
    dateTime.setCustomValidity('');
    const chosen = document.querySelector('input[name="staff_id"]:checked');
    if (!chosen || chosen.dataset.type !== 'barber' || !dateTime.value) return true;

    const selected = new Date(dateTime.value);
    if (Number.isNaN(selected.getTime())) return true;
    const duration = Math.max(1, selectedDuration());
    const mins = selected.getHours() * 60 + selected.getMinutes();
    const endMins = mins + duration;
    const ok = mins >= 9 * 60 && endMins <= 22 * 60;
    if (!ok) {
      dateTime.setCustomValidity('Para barbería, el servicio debe realizarse entre las 09:00 y las 22:00 y finalizar máximo a las 22:00.');
      if (showMessage) dateTime.reportValidity();
      return false;
    }
    return true;
  }

  radios.forEach(r => r.addEventListener('change', applyFilter));
  select.addEventListener('change', function(){ updateHoursHint(); validateBarberHours(false); });
  dateTime.addEventListener('change', function(){ validateBarberHours(true); });
  if (form) form.addEventListener('submit', function(ev){
    if (!validateBarberHours(true)) ev.preventDefault();
  });
  applyFilter();
})();
</script>
