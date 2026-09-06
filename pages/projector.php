<?php
date_default_timezone_set('America/Bogota');

$today = (new DateTime())->format('Y-m-d');
$day = $_GET['day'] ?? $today;
$dayObj = DateTime::createFromFormat('Y-m-d', $day);
$validDay = $dayObj && $dayObj->format('Y-m-d') === $day;
if (!$validDay) {
  $day = $today;
  $dayObj = new DateTime($today);
}

$prevDay = (clone $dayObj)->modify('-1 day')->format('Y-m-d');
$nextDay = (clone $dayObj)->modify('+1 day')->format('Y-m-d');
$dayLabel = $dayObj->format('d/m/Y');
?>
<div class="bg-white border rounded-4 p-4 shadow-sm projector-shell">
  <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
    <div>
      <span class="section-kicker">Pantalla de turnos</span>
      <h2 class="mb-1">Proyección de Citas — <?=e($dayLabel)?></h2>
      <p class="text-muted mb-0">
        <i class="bi bi-broadcast-pin me-1"></i>
        Actualización automática cada 5 segundos · Última actualización: <strong id="projectorLastUpdate"><?=date('H:i:s')?></strong>
      </p>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center">
      <a class="btn btn-outline-secondary" href="index.php?p=projector&day=<?=e($prevDay)?>" title="Día anterior">
        <i class="bi bi-chevron-left"></i>
      </a>

      <form method="get" class="d-flex gap-2" id="projectorDateForm">
        <input type="hidden" name="p" value="projector">
        <input type="date" name="day" id="projectorDay" class="form-control" value="<?=e($day)?>" aria-label="Fecha de proyección">
        <button class="btn btn-primary" type="submit"><i class="bi bi-calendar3 me-1"></i>Ver fecha</button>
      </form>

      <a class="btn btn-outline-secondary" href="index.php?p=projector&day=<?=e($nextDay)?>" title="Día siguiente">
        <i class="bi bi-chevron-right"></i>
      </a>
      <a class="btn btn-outline-dark" href="index.php?p=projector&day=<?=e($today)?>">Hoy</a>
    </div>
  </div>

  <div id="projectorContent" aria-live="polite">
    <?php include __DIR__ . '/projector_feed.php'; ?>
  </div>

  <div id="projectorConnectionStatus" class="small text-muted text-end mt-3">
    <i class="bi bi-wifi me-1"></i>Proyección conectada
  </div>
</div>

<script>
(function () {
  const REFRESH_MS = 5000;
  const day = <?=json_encode($day)?>;
  const content = document.getElementById('projectorContent');
  const lastUpdate = document.getElementById('projectorLastUpdate');
  const connectionStatus = document.getElementById('projectorConnectionStatus');
  const dayInput = document.getElementById('projectorDay');
  const dateForm = document.getElementById('projectorDateForm');

  if (dayInput && dateForm) {
    dayInput.addEventListener('change', function () {
      dateForm.submit();
    });
  }

  async function refreshProjection() {
    try {
      const url = 'pages/projector_feed.php?day=' + encodeURIComponent(day) + '&_=' + Date.now();
      const response = await fetch(url, {
        method: 'GET',
        cache: 'no-store',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      if (!response.ok) throw new Error('HTTP ' + response.status);
      const html = await response.text();
      content.innerHTML = html;

      const now = new Date();
      lastUpdate.textContent = now.toLocaleTimeString('es-CO', { hour12: false });
      connectionStatus.innerHTML = '<i class="bi bi-wifi me-1"></i>Proyección conectada · datos actualizados';
      connectionStatus.className = 'small text-success text-end mt-3';
    } catch (error) {
      connectionStatus.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>No se pudo actualizar en este ciclo; se intentará nuevamente.';
      connectionStatus.className = 'small text-warning text-end mt-3';
    }
  }

  // La proyección consulta nuevamente la base de datos cada 5 segundos,
  // sin recargar toda la página ni cambiar la fecha seleccionada.
  setInterval(refreshProjection, REFRESH_MS);
})();
</script>
