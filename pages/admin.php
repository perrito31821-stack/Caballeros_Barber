<?php
if (!is_admin()) { echo '<div class="alert alert-danger">Acceso restringido.</div>'; return; }

$barberMessage = '';
$barberMessageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_barber_names'])) {
  $names = $_POST['barber_name'] ?? [];
  $active = $_POST['barber_active'] ?? [];

  try {
    $pdo->beginTransaction();
    $update = $pdo->prepare("UPDATE staff SET name=?, active=? WHERE id=? AND type='barber'");

    for ($i = 1; $i <= 6; $i++) {
      $name = trim($names[$i] ?? '');
      if ($name === '') $name = 'Barbero ' . $i;
      $isActive = isset($active[$i]) ? 1 : 0;
      $update->execute([$name, $isActive, $i]);
    }

    $pdo->commit();
    $barberMessage = 'Los nombres y el estado de los barberos se actualizaron correctamente.';
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $barberMessage = 'No fue posible actualizar los barberos. Verifica la conexión con la base de datos.';
    $barberMessageType = 'danger';
  }
}

$barbers = $pdo->query("SELECT id,name,active FROM staff WHERE type='barber' ORDER BY sort_order,id")->fetchAll();
$barberNames = [];
$barberActive = [];
foreach ($barbers as $barber) {
  $barberNames[(int)$barber['id']] = $barber['name'];
  $barberActive[(int)$barber['id']] = (int)$barber['active'];
}
?>
<div class="mb-4">
  <span class="section-kicker">Panel privado</span>
  <h2 class="section-title mb-1">Administración · Caballeros Barber</h2>
  <p class="text-muted mb-0">Consulta agendas, servicios, productos e ingresos desde un solo lugar.</p>
</div>

<div class="bg-white border rounded-4 p-4 mb-4 shadow-sm">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <div>
      <span class="section-kicker">Personal</span>
      <h4 class="mb-1"><i class="bi bi-person-badge me-2"></i>Nombres de los barberos</h4>
      <p class="text-muted mb-0">El número del puesto se conserva, el nombre aparecerá debajo y puedes inhabilitar temporalmente cualquier barbero para que no pueda recibir nuevas reservas.</p>
    </div>
    <span class="badge text-bg-dark px-3 py-2">6 puestos</span>
  </div>

  <?php if ($barberMessage): ?>
    <div class="alert alert-<?=$barberMessageType?> d-flex align-items-center gap-2">
      <i class="bi <?=$barberMessageType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'?>"></i>
      <span><?=e($barberMessage)?></span>
    </div>
  <?php endif; ?>

  <form method="post">
    <input type="hidden" name="save_barber_names" value="1">
    <div class="row g-3">
      <?php for ($i = 1; $i <= 6; $i++):
        $currentName = $barberNames[$i] ?? ('Barbero ' . $i);
        $inputValue = preg_match('/^Barbero\s+' . $i . '$/iu', $currentName) ? '' : $currentName;
      ?>
        <div class="col-md-6 col-lg-4">
          <div class="barber-name-editor">
            <div class="barber-name-editor-head">
              <span class="barber-number">Barbero <?=$i?></span>
              <i class="bi bi-scissors"></i>
            </div>
            <label class="form-label small text-muted mb-1" for="barber-name-<?=$i?>">Nombre que verá el cliente</label>
            <input type="text" class="form-control" id="barber-name-<?=$i?>" name="barber_name[<?=$i?>]" value="<?=e($inputValue)?>" maxlength="80" placeholder="Ej. Carlos Restrepo">
            <div class="form-check form-switch mt-3">
              <input class="form-check-input" type="checkbox" role="switch" id="barber-active-<?=$i?>" name="barber_active[<?=$i?>]" value="1" <?=($barberActive[$i] ?? 1) ? 'checked' : ''?>>
              <label class="form-check-label fw-semibold" for="barber-active-<?=$i?>">Barbero habilitado para reservas</label>
            </div>
          </div>
        </div>
      <?php endfor; ?>
    </div>
    <div class="d-flex justify-content-end mt-4">
      <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save2 me-2"></i>Guardar cambios</button>
    </div>
  </form>
</div>

<div class="bg-white border rounded-4 p-4 mb-4 shadow-sm">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <div>
      <span class="section-kicker">Acceso profesional</span>
      <h4 class="mb-1"><i class="bi bi-shield-lock me-2"></i>Usuarios de barberos y Belleza</h4>
      <p class="text-muted mb-0">Cada profesional tiene un usuario independiente. Solo pueden gestionar las reservas de su propia agenda.</p>
    </div>
    <span class="badge text-bg-dark px-3 py-2">7 cuentas</span>
  </div>
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Profesional</th><th>Usuario</th><th>Acceso</th></tr></thead>
      <tbody>
      <?php
        $staffCredentials = $pdo->query("SELECT id,name,type,username,active FROM staff ORDER BY sort_order,id")->fetchAll();
        foreach ($staffCredentials as $cred):
      ?>
        <tr>
          <td><strong><?=e($cred['name'])?></strong><br><small class="text-muted"><?=$cred['type']==='beauty'?'Belleza':'Barbería'?></small></td>
          <td><code><?=e($cred['username'])?></code></td>
          <td><?=((int)$cred['active']===1)?'<span class="badge text-bg-success">Habilitado</span>':'<span class="badge text-bg-secondary">Inhabilitado para reservas</span>'?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="alert alert-light border mt-3 mb-0 small"><i class="bi bi-info-circle me-1"></i>Las contraseñas iniciales están documentadas en el archivo <strong>CREDENCIALES_PROFESIONALES.txt</strong>. Por seguridad, no se muestran en pantalla.</div>
</div>

<div class="row g-3">
  <div class="col-md-4"><div class="info-tile"><div class="info-icon"><i class="bi bi-calendar2-week"></i></div><h5>Agendas y citas</h5><p class="text-muted">Revisa por separado los 6 barberos y la sección de belleza.</p><a href="index.php?p=appt_admin" class="btn btn-primary w-100">Ver agendas</a></div></div>
  <div class="col-md-4"><div class="info-tile"><div class="info-icon"><i class="bi bi-scissors"></i></div><h5>Servicios</h5><p class="text-muted">Mantén precios, duración y disponibilidad del catálogo.</p><a href="index.php?p=svc_crud" class="btn btn-primary w-100">Gestionar servicios</a></div></div>
  <div class="col-md-4"><div class="info-tile"><div class="info-icon"><i class="bi bi-bag"></i></div><h5>Productos</h5><p class="text-muted">Administra inventario y productos disponibles en la tienda.</p><a href="index.php?p=prod_crud" class="btn btn-primary w-100">Gestionar productos</a></div></div>
</div>
