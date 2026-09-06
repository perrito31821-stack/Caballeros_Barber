</main>
<footer class="footer-caballeros mt-5">
  <div class="container py-4">
    <div class="row g-3 align-items-center">
      <div class="col-lg-4">
        <div class="footer-mark">Caballeros Barber</div>
        <small>© <?=(new DateTime())->format('Y')?> · Bello, Antioquia</small>
      </div>
      <div class="col-lg-4 small">
        <i class="bi bi-geo-alt me-1"></i> Carrera 57a # 35a - 97 (Frente a la Iglesia de San Juan Bosco - Bello)
      </div>
      <div class="col-lg-4 text-lg-end small">
        <a href="index.php?p=projector" class="me-3"><i class="bi bi-display me-1"></i>Proyección de Citas</a>
        <a href="https://wa.me/<?=preg_replace('/\D+/', '', $ADMIN_WHATSAPP)?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-1"></i>WhatsApp</a>
      </div>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/app.js"></script>
</body>
</html>
