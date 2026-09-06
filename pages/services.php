<?php $services = $pdo->query("SELECT * FROM services WHERE active=1 ORDER BY category,name")->fetchAll(); ?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
  <div><span class="section-kicker">Nuestro catálogo</span><h2 class="section-title mb-0">Servicios Caballeros Barber</h2></div>
  <a href="index.php?p=book" class="btn btn-primary"><i class="bi bi-calendar2-check me-1"></i>Reservar ahora</a>
</div>
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
  <?php foreach($services as $s): ?>
    <div class="col"><div class="card h-100 border-0"><div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-start gap-2 mb-3"><span class="badge <?=$s['category']==='belleza'?'text-bg-danger':'text-bg-dark'?>"><?=$s['category']==='belleza'?'Belleza':'Barbería'?></span><span class="text-muted small"><i class="bi bi-clock me-1"></i><?=e($s['duration_minutes'])?> min</span></div>
      <h5 class="card-title mb-2"><?=e($s['name'])?></h5><div class="fs-4 fw-bold text-primary mb-3">$<?=number_format($s['price'],0,',','.')?></div>
      <a href="index.php?p=book&sid=<?=$s['id']?>" class="btn btn-outline-primary btn-sm">Elegir servicio</a>
    </div></div></div>
  <?php endforeach; ?>
</div>
