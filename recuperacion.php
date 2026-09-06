<?php
require 'config.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = strtolower(trim($_POST['email'] ?? ''));
  if ($email) {
    $st = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
    $st->execute([$email]);
    if ($u = $st->fetch(PDO::FETCH_ASSOC)) {
      $token = bin2hex(random_bytes(32));
      $expira = date('Y-m-d H:i:s', strtotime('+30 minutes'));
      $pdo->prepare("UPDATE users SET reset_token=?, reset_expira=? WHERE id=?")->execute([$token,$expira,$u['id']]);
      $link = 'reset.php?token='.$token;
      $msg = "<div class='alert alert-success'>Tu enlace está listo. <a href='".e($link)."' class='fw-bold'>Cambiar contraseña</a></div>";
    } else {
      $msg = "<div class='alert alert-danger'>No existe una cuenta con ese correo.</div>";
    }
  }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Recuperar contraseña · Caballeros Barber</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/style.css" rel="stylesheet"></head>
<body class="d-flex align-items-center justify-content-center p-3" style="min-height:100vh;background:#17130f;">
  <div class="bg-white border rounded-4 p-4 shadow-lg" style="max-width:480px;width:100%;">
    <div class="brand-mark mx-auto mb-3"><span>CB</span></div><h2 class="text-center mb-2">Recuperar contraseña</h2><p class="text-muted text-center">Caballeros Barber</p>
    <?=$msg?>
    <form method="post" class="row g-3"><div class="col-12"><label class="form-label">Correo electrónico</label><input type="email" name="email" class="form-control" required></div><div class="col-12"><button class="btn btn-primary w-100">Generar enlace</button></div></form>
    <a href="index.php?p=login" class="btn btn-outline-secondary w-100 mt-3">Volver a iniciar sesión</a>
  </div>
</body></html>
