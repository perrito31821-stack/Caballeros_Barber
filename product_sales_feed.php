<?php
require_once __DIR__ . '/config.php';

if (!is_admin()) {
  http_response_code(403);
  exit('Acceso restringido.');
}

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

include __DIR__ . '/partials/product_sales_live.php';
