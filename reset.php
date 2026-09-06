<?php
require 'config.php';
$msg=''; $token=$_GET['token'] ?? $_POST['token'] ?? '';
if ($token) {
  $st=$pdo->prepare("SELECT id,reset_expira FROM users WHERE reset_token=? LIMIT 1"); $st->execute([$token]); $u=$st->fetch(PDO::FETCH_ASSOC);
  if ($u && strtotime($u['reset_expira']) > time()) {
    if ($_SERVER['REQUEST_METHOD']==='POST') {
      $pass1=$_POST['password']??''; $pass2=$_POST['confirm']??'';
      if (strlen($pass1)<8) $msg="<div class='alert alert-danger'>La contraseña debe tener al menos 8 caracteres.</div>";
      elseif ($pass1!==$pass2) $msg="<div class='alert alert-danger'>Las contraseñas no coinciden.</div>";
      else {
        $hash=password_hash($pass1,PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password_hash=?,reset_token=NULL,reset_expira=NULL WHERE id=?")->execute([$hash,$u['id']]);
        $msg="<div class='alert alert-success'>Contraseña cambiada correctamente. <a href='index.php?p=login'>Inicia sesión</a>.</div>"; $token='';
      }
    }
  } else { $msg="<div class='alert alert-danger'>El enlace no es válido o ya expiró.</div>"; $token=''; }
} else $msg="<div class='alert alert-danger'>Token no encontrado.</div>";
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Nueva contraseña · Caballeros Barber</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/style.css" rel="stylesheet"></head>
<body class="d-flex align-items-center justify-content-center p-3" style="min-height:100vh;background:#17130f;">
  <div class="bg-white border rounded-4 p-4 shadow-lg" style="max-width:480px;width:100%;"><div class="brand-mark mx-auto mb-3"><span>CB</span></div><h2 class="text-center">Nueva contraseña</h2><p class="text-muted text-center">Caballeros Barber</p><?=$msg?><?php if($token): ?><form method="post" class="row g-3"><input type="hidden" name="token" value="<?=e($token)?>"><div class="col-12"><label class="form-label">Nueva contraseña</label><input type="password" name="password" class="form-control" minlength="8" required></div><div class="col-12"><label class="form-label">Confirmar contraseña</label><input type="password" name="confirm" class="form-control" minlength="8" required></div><div class="col-12"><button class="btn btn-primary w-100">Guardar contraseña</button></div></form><?php endif; ?></div>
</body></html>
