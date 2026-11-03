<?php
/**
 * Formulario compartido de alta y edición de productos.
 * Variables: $accion_destino, $producto, $categorias, $presentaciones, $cat_retorno, $texto_boton, $titulo_form
 */
$errores_form = $_SESSION['errores_form'] ?? [];
unset($_SESSION['errores_form']);
$fecha_max = fecha_maxima_vencimiento();
?>
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h1 class="h4 mb-3"><?= e($titulo_form) ?></h1>

        <?php if ($errores_form): ?>
          <div class="alert alert-danger"><ul class="mb-0">
            <?php foreach ($errores_form as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
          </ul></div>
        <?php endif; ?>

        <form action="<?= e($accion_destino) ?>" method="POST" id="formInsumo" novalidate>
          <?= csrf_campo() ?>
          <input type="hidden" name="cat_retorno" value="<?= (int) $cat_retorno ?>">
          <?php if (!empty($producto['id_producto'])): ?>
            <input type="hidden" name="id_producto" value="<?= (int) $producto['id_producto'] ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label for="nombre_producto" class="form-label">Nombre del insumo</label>
            <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" maxlength="150" value="<?= e($producto['nombre_producto'] ?? '') ?>" required>
          </div>

          <?php if (count($categorias) === 1): ?>
          <div class="mb-3">
            <label class="form-label" for="categoria_fija">Categoría</label>
            <input type="text" class="form-control" id="categoria_fija" value="<?= e($categorias[0]['nombre_categoria']) ?>" disabled>
            <input type="hidden" id="id_categoria" name="id_categoria" value="<?= (int) $categorias[0]['id_categoria'] ?>">
          </div>
          <?php else: ?>
          <div class="mb-3">
            <label for="id_categoria" class="form-label">Categoría</label>
            <select class="form-select" id="id_categoria" name="id_categoria" required>
              <option value="">Seleccione</option>
              <?php foreach ($categorias as $c): ?>
                <option value="<?= (int) $c['id_categoria'] ?>" <?= (int) ($producto['id_categoria'] ?? 0) === (int) $c['id_categoria'] ? 'selected' : '' ?>><?= e($c['nombre_categoria']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>

          <div class="mb-3">
            <label for="id_presentacion" class="form-label">Presentación</label>
            <select class="form-select" id="id_presentacion" name="id_presentacion" required>
              <option value="">Seleccione</option>
              <?php foreach ($presentaciones as $p): ?>
                <option value="<?= (int) $p['id_presentacion'] ?>" <?= (int) ($producto['id_presentacion'] ?? 0) === (int) $p['id_presentacion'] ? 'selected' : '' ?>><?= e($p['nombre_presentacion']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="cantidad" class="form-label">Cantidad</label>
              <input type="text" inputmode="numeric" class="form-control" id="cantidad" name="cantidad" maxlength="7" value="<?= e($producto['cantidad'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="fecha_vencimiento" class="form-label">Fecha de vencimiento</label>
              <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento"
                     min="<?= FECHA_MINIMA ?>" max="<?= e($fecha_max) ?>" value="<?= e($producto['fecha_vencimiento'] ?? '') ?>">
              <div class="form-text">Déjela vacía si el insumo no vence.</div>
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-institucional"><?= e($texto_boton) ?></button>
            <a href="listar.php<?= $cat_retorno ? '?cat=' . (int) $cat_retorno : '' ?>" class="btn btn-outline-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
