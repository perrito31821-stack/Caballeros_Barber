<?php
if (is_logged()) { 
  echo '<div class="alert alert-success">Ya has iniciado sesión.</div>'; 
  return; 
}

if ($_SERVER['REQUEST_METHOD']==='POST'){
  $name  = trim($_POST['name'] ?? '');
  $email = strtolower(trim($_POST['email'] ?? ''));
  $phone = trim($_POST['phone'] ?? '');
  $pass  = $_POST['password'] ?? '';

  if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($pass) >= 6){
    $hash = password_hash($pass, PASSWORD_BCRYPT);

    try {
      // Verificar si el correo ya existe
      $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
      $check->execute([$email]);

      if ($check->fetch()) {
        echo '<div class="alert alert-danger">El correo ya está registrado.</div>';
      } else {
        // Insertar nuevo usuario
        $st = $pdo->prepare("INSERT INTO users (name,email,phone,password_hash) VALUES (?,?,?,?)");
        $st->execute([$name,$email,$phone,$hash]);
        echo '<div class="alert alert-success">Registro exitoso, ahora puede iniciar sesión.</div>';
      }

    } catch (PDOException $e) {
      if ($e->errorInfo[1] == 1062) {
        // Error de clave duplicada
        echo '<div class="alert alert-danger">El correo ya está registrado.</div>';
      } else {
        echo '<div class="alert alert-danger">Error en el registro: '.$e->getMessage().'</div>';
      }
    }

  } else {
    echo '<div class="alert alert-warning">Complete los campos correctamente (contraseña mínimo 6 caracteres).</div>';
  }
}
?>

<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="bg-white border rounded-4 p-4">
      <h2>Registrarse</h2>
      <form method="post" class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre completo</label>
          <input name="name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Teléfono</label>
          <input name="phone" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Correo</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Contraseña</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="col-12 text-end">
          <button class="btn btn-primary">Crear cuenta</button>
        </div>
      </form>
    </div>
  </div>
</div>
