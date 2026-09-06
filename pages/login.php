<?php
if (is_logged()) { 
  echo '<div class="alert alert-success">Ya has iniciado sesión.</div>'; 
  return; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $login = trim($_POST['login'] ?? '');
  $pass  = $_POST['password'] ?? '';

  if ($login && $pass) {
    // Clientes/administradores ingresan con correo; profesionales con su usuario fijo.
    $st = $pdo->prepare("SELECT * FROM users WHERE LOWER(email)=LOWER(?) LIMIT 1");
    $st->execute([$login]);
    $u = $st->fetch(PDO::FETCH_ASSOC);

    if ($u && password_verify($pass, $u['password_hash'])) {
      $_SESSION['user'] = $u;
      redirect('home');
      exit;
    }

    $st = $pdo->prepare("SELECT id,name,type,username,password_hash,active FROM staff WHERE LOWER(username)=LOWER(?) LIMIT 1");
    $st->execute([$login]);
    $staff = $st->fetch(PDO::FETCH_ASSOC);

    if ($staff && !empty($staff['password_hash']) && password_verify($pass, $staff['password_hash'])) {
      $_SESSION['user'] = [
        'id' => 'staff_' . (int)$staff['id'],
        'name' => $staff['name'],
        'role' => 'staff',
        'staff_id' => (int)$staff['id'],
        'staff_type' => $staff['type'],
        'username' => $staff['username'],
        'active' => (int)$staff['active']
      ];
      redirect('staff_dashboard');
      exit;
    }
  }

  echo '<div class="alert alert-danger">Usuario/correo o contraseña incorrectos.</div>';
}
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="bg-white border rounded-4 p-4 shadow-lg">
      <h2 class="mb-4 text-center text-primary">Ingresar</h2>
      <form method="post" class="row g-3">
        <div class="col-12">
          <label class="form-label">Usuario o correo</label>
          <input type="text" name="login" class="form-control" autocomplete="username" placeholder="Ej. Barbero1 o correo@ejemplo.com" required>
        </div>
        <div class="col-12">
          <label class="form-label">Contraseña</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="col-12 text-end">
          <button class="btn btn-primary px-4">Entrar</button>
        </div>
<div class="col-12 text-center mt-3">
  <a href="recuperacion.php" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
</div>

      </form>
    </div>
  </div>
</div>
