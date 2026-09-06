<?php require_once __DIR__ . '/../config.php'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#17130f">
  <title>Caballeros Barber</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="manifest" href="pwa/manifest.json">
  <link rel="icon" type="image/png" href="pwa/icon-192.png">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-caballeros sticky-top">
  <div class="container py-1">
    <a class="navbar-brand brand-lockup" href="index.php?p=home" aria-label="Caballeros Barber - Inicio">
      <span class="brand-mark"><span>CB</span></span>
      <span class="brand-copy"><strong>Caballeros</strong><small>BARBER</small></span>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Abrir menú">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
        <li class="nav-item"><a class="nav-link" href="index.php?p=home">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?p=services">Servicios</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?p=shop">Tienda</a></li>
        <li class="nav-item"><a class="nav-link nav-book" href="index.php?p=book"><i class="bi bi-calendar2-check me-1"></i>Reservar</a></li>
        <?php if (is_admin()): ?>
          <li class="nav-item"><a class="nav-link" href="index.php?p=admin"><i class="bi bi-speedometer2 me-1"></i>Administración</a></li>
        <?php elseif (is_staff()): ?>
          <li class="nav-item"><a class="nav-link" href="index.php?p=staff_dashboard"><i class="bi bi-calendar2-week me-1"></i>Mis reservas</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav align-items-lg-center">
        <?php if (is_logged()): ?>
          <?php if (!is_staff()): ?><li class="nav-item"><a class="nav-link" href="index.php?p=my">Mis Citas</a></li><?php endif; ?>
          <li class="nav-item"><span class="nav-link user-chip"><i class="bi bi-person-circle me-1"></i><?=e($_SESSION['user']['name'])?></span></li>
          <li class="nav-item"><a class="nav-link" href="index.php?p=logout">Salir</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="index.php?p=login">Ingresar</a></li>
          <li class="nav-item"><a class="btn btn-brand-outline btn-sm ms-lg-2" href="index.php?p=register">Registrarse</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<main class="container py-4 py-lg-5">
