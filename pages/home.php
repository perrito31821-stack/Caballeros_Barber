<?php
$today = (new DateTime())->format('Y-m-d');
$stmt = $pdo->prepare("SELECT a.code, a.appt_datetime, s.name AS sname, u.name AS uname, COALESCE(st.name,'Sin asignar') AS staff_name, st.type AS staff_type
  FROM appointments a
  JOIN services s ON a.service_id=s.id
  JOIN users u ON a.user_id=u.id
  LEFT JOIN staff st ON a.staff_id=st.id
  WHERE DATE(a.appt_datetime)=? AND a.status IN ('pendiente','confirmada')
  ORDER BY a.appt_datetime ASC LIMIT 7");
$stmt->execute([$today]);
$todayAppointments = $stmt->fetchAll();
?>
<section class="hero-card mb-4 mb-lg-5">
  <div class="hero-eyebrow"><i class="bi bi-stars"></i> Estilo, detalle y buena atención</div>
  <h1 class="hero-title">Tu mejor versión empieza en <em>Caballeros Barber</em>.</h1>
  <p class="hero-copy">Reserva con el profesional que prefieras, elige tu servicio y asegura tu horario. Contamos con seis puestos de barbería y una agenda independiente para servicios de belleza.</p>
  <div class="hero-actions">
    <a href="index.php?p=book" class="btn btn-brand btn-lg px-4"><i class="bi bi-calendar2-check me-2"></i>Reservar mi turno</a>
    <a href="index.php?p=services" class="btn btn-brand-outline btn-lg px-4">Ver servicios</a>
  </div>
</section>

<section class="mb-4 mb-lg-5">
  <div class="row g-3">
    <div class="col-md-4"><div class="info-tile"><div class="info-icon"><i class="bi bi-person-check"></i></div><h5>Elige tu barbero</h5><p class="text-muted mb-0">Seis agendas independientes para que reserves con el profesional de tu preferencia.</p></div></div>
    <div class="col-md-4"><div class="info-tile"><div class="info-icon"><i class="bi bi-gem"></i></div><h5>Zona de belleza</h5><p class="text-muted mb-0">Turnos independientes para uñas, peinados, pestañas, tinturas y demás servicios de belleza.</p></div></div>
    <div class="col-md-4"><div class="info-tile"><div class="info-icon"><i class="bi bi-clock-history"></i></div><h5>Horarios simultáneos</h5><p class="text-muted mb-0">Cada profesional administra su propia agenda; varios pueden atender a la misma hora sin conflicto.</p></div></div>
  </div>
</section>

<section class="row g-4 align-items-stretch mb-4 mb-lg-5">
  <div class="col-lg-7">
    <div class="address-card h-100">
      <div class="address-copy">
        <span class="section-kicker">Encuéntranos</span>
        <h2 class="section-title mb-2">Estamos en Bello</h2>
        <p class="fs-5 fw-semibold mb-3"><i class="bi bi-geo-alt-fill text-primary me-2"></i>Carrera 57a # 35a - 97 (Frente a la Iglesia de San Juan Bosco - Bello)</p>
        <p class="text-muted">Abre la ruta directamente en Google Maps y llega sin complicaciones.</p>
        <a href="https://www.google.com/maps/dir/?api=1&destination=Carrera+57a+%23+35a-97+Bello+Antioquia" target="_blank" rel="noopener" class="btn btn-primary"><i class="bi bi-sign-turn-right me-2"></i>Cómo llegar</a>
      </div>
      <div class="ratio ratio-21x9 address-map">
        <iframe src="https://www.google.com/maps?q=Carrera%2057a%20%23%2035a-97%20Bello%20Antioquia&output=embed" style="border:0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="bg-white border rounded-4 shadow-sm p-4 h-100">
      <span class="section-kicker">Agenda</span>
      <h3 class="mb-3">Próximos turnos de hoy</h3>
      <?php if ($todayAppointments): ?>
        <div class="list-group list-group-flush">
          <?php foreach($todayAppointments as $row): ?>
            <div class="list-group-item px-0 py-3 bg-transparent d-flex justify-content-between gap-3 align-items-center">
              <div>
                <div class="fw-bold"><?=e($row['sname'])?></div>
                <small class="text-muted"><?=e($row['staff_name'])?> · #<?=e($row['code'])?></small>
              </div>
              <span class="badge text-bg-dark fs-6"><?= (new DateTime($row['appt_datetime']))->format('H:i') ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-light border mb-0"><i class="bi bi-calendar-heart me-2"></i>Aún no hay turnos programados para hoy.</div>
      <?php endif; ?>
    </div>
  </div>
</section>
