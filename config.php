<?php
// CONFIGURACIÓN GENERAL
date_default_timezone_set('America/Bogota');
$DB_HOST = "localhost";
$DB_NAME = "salon_bello";
$DB_USER = "root";
$DB_PASS = "";
$ADMIN_WHATSAPP = "+573044382968"; // Reemplace por el número del administrador (formato internacional)

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);

  // Preparar las cuentas independientes de los 6 barberos y Belleza.
  // La migración es idempotente para que funcione también sobre una base existente.
  $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='staff' AND COLUMN_NAME IN ('username','password_hash')")->fetchAll(PDO::FETCH_COLUMN);
  if (!in_array('username', $cols, true)) {
    $pdo->exec("ALTER TABLE staff ADD COLUMN username VARCHAR(50) NULL AFTER name");
  }
  if (!in_array('password_hash', $cols, true)) {
    $pdo->exec("ALTER TABLE staff ADD COLUMN password_hash VARCHAR(255) NULL AFTER username");
  }
  $pdo->exec("UPDATE staff SET username=CASE id WHEN 1 THEN 'Barbero1' WHEN 2 THEN 'Barbero2' WHEN 3 THEN 'Barbero3' WHEN 4 THEN 'Barbero4' WHEN 5 THEN 'Barbero5' WHEN 6 THEN 'Barbero6' WHEN 7 THEN 'Belleza' ELSE username END WHERE id BETWEEN 1 AND 7");
  $credentialHashes = [
    1 => '$2y$12$FX6psmClKtuU.AteI4mb1OmPoVPld1sZlMFFj9AsHwvIA1JTj1LoG',
    2 => '$2y$12$fWMz.8rEqdtQ7n9CQ5TA7O7xvQuKsWlG9zEaGY8EfKtoLpzni/bOq',
    3 => '$2y$12$.BWc9Nt51X/5fWPabrEG.uBmx8pHHW1zQJUcSSLbHzoMO482RHBnm',
    4 => '$2y$12$FAsQMNADBDHlHWM/7BOT/OzkNIDcJOq6Adii4sAXMR83KhaXr.jF6',
    5 => '$2y$12$CZvewXykTRqjp.8Cke4Y0.JVdfGSTlmLotywg51OFLR8SuKPwuoly',
    6 => '$2y$12$WIlTbSsuCVXriFPgzcLoZOdNvT7C3klxHFFD9rIq9SCdd8Z7Qnioa',
    7 => '$2y$12$w.3XDqjXRRPHCnC8aXwuF.4kb7GUZmIbL.qXyr0uHbwMEiDIzJZYq',
  ];
  foreach ($credentialHashes as $staffId => $hash) {
    $stCred = $pdo->prepare("UPDATE staff SET password_hash=? WHERE id=?");
    $stCred->execute([$hash, $staffId]);
  }
} catch (Exception $e) {
  die("Error de conexión: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Helpers
function is_logged(){ return isset($_SESSION['user']); }
function is_admin(){ return is_logged() && $_SESSION['user']['role'] === 'admin'; }
function is_staff(){ return is_logged() && $_SESSION['user']['role'] === 'staff' && !empty($_SESSION['user']['staff_id']); }
function is_staff_manager(){ return is_staff() && (int)$_SESSION['user']['staff_id'] >= 1; }
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function redirect($path){
  header("Location: index.php?p=$path");
  exit;
}

?>
