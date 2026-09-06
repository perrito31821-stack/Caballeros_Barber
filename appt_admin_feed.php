<?php
require_once __DIR__ . '/config.php';

if (!is_admin()) {
  http_response_code(403);
  exit('Acceso restringido.');
}

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$day = $_GET['day'] ?? (new DateTime())->format('Y-m-d');
$validDate = DateTime::createFromFormat('Y-m-d', $day);
if (!$validDate || $validDate->format('Y-m-d') !== $day) {
  http_response_code(400);
  exit('Fecha inválida.');
}

$staffList = $pdo->query("SELECT id,name,type FROM staff WHERE active=1 ORDER BY sort_order,id")->fetchAll();
$stmt = $pdo->prepare("SELECT a.*, s.name AS sname, s.price AS sprice, u.name AS uname, u.phone AS uphone,
  COALESCE(st.name,'Sin asignar') AS staff_name, st.type AS staff_type
  FROM appointments a
  JOIN services s ON a.service_id=s.id
  JOIN users u ON a.user_id=u.id
  LEFT JOIN staff st ON a.staff_id=st.id
  WHERE DATE(a.appt_datetime)=?
  ORDER BY st.sort_order, a.appt_datetime ASC");
$stmt->execute([$day]);
$rows = $stmt->fetchAll();

$grouped = [];
foreach ($rows as $r) {
  $key = (int)($r['staff_id'] ?? 0);
  $grouped[$key][] = $r;
}

include __DIR__ . '/partials/appt_agendas.php';
