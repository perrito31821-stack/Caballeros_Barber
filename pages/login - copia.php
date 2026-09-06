<?php
if (is_logged()) { 
  echo '<div class="alert alert-success">Ya has iniciado sesión.</div>'; 
  return; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = strtolower(trim($_POST['email'] ?? ''));
  $pass  = $_POST['password'] ?? ''; // ahora el input se llama 'password'

  if ($email && $pass) {
    // Buscar usuario por correo
    $st = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $st->execute([$email]);
    $u = $st->fetch(PDO::FETCH_ASSOC);

    if ($u && password_verify($pass, $u['password_hash'])) {
      // Guardamos al usuario en sesión
      $_SESSION['user'] = $u;
      redirect('home');
      exit;
    }
  }

  // Si no coincide, mostramos error
  echo '<div class="alert alert-danger">Credenciales inválidas.</div>';
}
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="bg-white border rounded-4 p-4 shadow-lg">
      <h2 class="mb-4 text-center text-primary">Ingresar</h2>
      <form method="post" class="row g-3">
        <div class="col-12">
          <label class="form-label">Correo</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-12">
          <label class="form-label">Contraseña</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="col-12 text-end">
          <button class="btn btn-primary px-4">Entrar</button>
        </div>
      </form>
    </div>
  </div>
</div>
