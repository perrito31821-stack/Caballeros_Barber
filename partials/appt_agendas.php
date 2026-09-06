<?php
// Este parcial recibe $staffList y $grouped y renderiza únicamente las agendas.
?>
<div class="row g-3 mb-4">
  <?php foreach($staffList as $st): $count = count($grouped[(int)$st['id']] ?? []); ?>
    <div class="col-6 col-md-4 col-xl">
      <a href="#agenda-<?=$st['id']?>" class="text-decoration-none">
        <div class="info-tile py-3">
          <div class="d-flex align-items-center justify-content-between gap-2">
            <div>
              <div class="fw-bold text-dark"><?=e($st['name'])?></div>
              <small class="text-muted"><?=$st['type']==='beauty'?'Belleza':'Barbería'?></small>
            </div>
            <span class="agenda-count"><?=$count?></span>
          </div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<div class="d-grid gap-4">
<?php foreach($staffList as $st): $staffRows = $grouped[(int)$st['id']] ?? []; ?>
  <section class="agenda-section" id="agenda-<?=$st['id']?>">
    <div class="agenda-header <?=$st['type']==='beauty'?'beauty':''?>">
      <div>
        <div class="small text-uppercase opacity-75"><?=$st['type']==='beauty'?'Sección independiente':'Puesto de barbería'?></div>
        <h4 class="mb-0"><?=e($st['name'])?></h4>
      </div>
      <span class="agenda-count"><?=count($staffRows)?></span>
    </div>
    <div class="table-responsive">
      <table class="table agenda-table align-middle">
        <thead><tr><th>Código</th><th>Hora</th><th>Cliente</th><th>Servicio</th><th>Precio</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
          <?php foreach($staffRows as $r):
            $disabled = ($r['status']==='completada' || $r['status']==='cancelada') ? 'disabled' : '';
            if ($r['status']==='completada') $badge = '<span class="badge text-bg-success">Completada</span>';
            elseif ($r['status']==='cancelada') $badge = '<span class="badge text-bg-danger">Cancelada</span>';
            elseif ($r['status']==='confirmada') $badge = '<span class="badge text-bg-primary">Confirmada</span>';
            else $badge = '<span class="badge text-bg-secondary">Pendiente</span>';
          ?>
            <tr>
              <td><span class="badge text-bg-dark">#<?=e($r['code'])?></span></td>
              <td class="fw-bold"><?= (new DateTime($r['appt_datetime']))->format('H:i') ?></td>
              <td><?=e($r['uname'])?><br><small class="text-muted"><?=e($r['uphone'])?></small></td>
              <td><?=e($r['sname'])?></td>
              <td>$<?=number_format($r['sprice'],0,',','.')?></td>
              <td><?=$badge?></td>
              <td class="text-end text-nowrap">
                <form method="post" class="d-inline">
                  <input type="hidden" name="id" value="<?=$r['id']?>">
                  <button class="btn btn-sm btn-outline-secondary" name="status" value="confirmada" <?=$disabled?>>Confirmar</button>
                  <button class="btn btn-sm btn-outline-warning" name="status" value="cancelada" <?=$disabled?>>Cancelar</button>
                </form>
                <form method="post" class="d-inline">
                  <input type="hidden" name="id" value="<?=$r['id']?>">
                  <input type="hidden" name="price" value="<?=$r['sprice']?>">
                  <select name="method" class="form-select form-select-sm d-inline w-auto" <?=$disabled?>>
                    <option value="efectivo">Efectivo</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="tarjeta">Tarjeta</option>
                  </select>
                  <button class="btn btn-sm btn-success" name="status" value="completada" <?=$disabled?>>Cobrar</button>
                </form>
                <a class="btn btn-sm btn-outline-primary" href="index.php?p=receipt&aid=<?=$r['id']?>">Recibo</a>
                <a class="btn btn-sm btn-outline-success" target="_blank" rel="noopener"
                  href="https://wa.me/<?=preg_replace('/\D+/', '', $ADMIN_WHATSAPP)?>?text=Confirmación%20cita%20%23<?=urlencode($r['code'])?>%20<?=urlencode((new DateTime($r['appt_datetime']))->format('Y-m-d H:i'))?>%20<?=urlencode($r['staff_name'])?>%20Cliente:%20<?=urlencode($r['uname'])?>">WhatsApp</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$staffRows): ?><tr><td colspan="7" class="text-center text-muted py-4">Sin turnos para esta fecha.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endforeach; ?>
</div>
