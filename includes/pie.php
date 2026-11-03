    </main>
    <footer class="text-center text-muted small py-3 border-top no-imprimir">
      Sistema de Inventario Médico &middot; PST Trayecto II &middot; UPTEC "Mariscal Sucre"
    </footer>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base ?>js/main.js"></script>
<?php foreach (($scripts_extra ?? []) as $js): ?>
<script src="<?= $base . e($js) ?>"></script>
<?php endforeach; ?>
</body>
</html>
