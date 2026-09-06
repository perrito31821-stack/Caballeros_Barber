<?php
require_once __DIR__ . '/config.php';
$page = $_GET['p'] ?? 'home';
$allowed = ['home','services','shop','book','login','register','logout','my','admin','svc_crud','prod_crud','appt_admin','receipt','checkout','cart','projector','staff_dashboard'];
if (!in_array($page, $allowed)) $page = 'home';
include __DIR__ . '/partials/header.php';
include __DIR__ . '/pages/' . $page . '.php';
include __DIR__ . '/partials/footer.php';
