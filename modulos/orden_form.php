<?php
/** Nueva orden de entrega: salida de medicamentos asociada a un beneficiario registrado. */
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
requerir_login($conexion, '../');
requerir_permiso('ordenes', '../');

$beneficiarios = $conexion->query(
    "SELECT id_beneficiario, nombres, apellidos, tipo_documento, cedula, tipo_beneficiario
     FROM beneficiarios WHERE status = 'Activo' ORDER BY apellidos, nombres"
)->fetchAll();

// Solo medicamentos con existencia; los vencidos se muestran pero no se pueden elegir
$productos = $conexion->query(
    "SELECT id_producto, nombre_producto, nombre_presentacion, cantidad, estado_vencimiento
     FROM v_inventario WHERE status = 'Activo' AND id_categoria IN (" . categorias_sql() . ") AND cantidad > 0
     ORDER BY nombre_producto, id_producto"
)->fetchAll();

$d = $_SESSION['datos_form'] ?? [];
unset($_SESSION['datos_form']);
$errores_form = $_SESSION['errores_form'] ?? [];
unset($_SESSION['errores_form']);

$sel_ben  = (string) ($d['id_beneficiario'] ?? ($_GET['id_beneficiario'] ?? ''));
$fecha    = $d['fecha_salida'] ?? date('Y-m-d');
$obs      = $d['observacion'] ?? '';
$prods    = is_array($d['id_producto'] ?? null) ? array_values($d['id_producto']) : [''];
$cants    = is_array($d['cantidad'] ?? null) ? array_values($d['cantidad']) : [''];
$n_filas  = max(1, min(10, count($prods)));

function opciones_producto(array $productos, string $sel): string
{
    $html = '<option value="">Seleccione el medicamento</option>';
    foreach ($productos as $p) {
        $vencido = $p['estado_vencimiento'] === 'Vencido';
        $texto = $p['nombre_producto'] . ' — ' . $p['nombre_presentacion'] . ' (existencia: ' . (int) $p['cantidad'] . ')' . ($vencido ? ' [VENCIDO]' : '');
        $html .= '<option value="' . (int) $p['id_producto'] . '"' . ((string) $p['id_producto'] === $sel ? ' selected' : '') . ($vencido ? ' disabled' : '') . '>' . e($texto) . '</option>';
    }
    return $html;
}

$base = '../';
$titulo_pagina = 'Nueva orden de entrega';
$scripts_extra = ['js/ordenes.js'];
require __DIR__ . '/../includes/encabezado.php';
?>
<div class="row justify-content-center"><div class="col-xl-9">
  <div class="card shadow-sm"><div class="card-body">
    <h1 class="h4 mb-1">Nueva orden de entrega de medicamentos</h1>
    <p class="text-muted small">Al guardar se descuenta la existencia, se registra la salida y se genera la orden de entrega en PDF para las firmas.</p>

    <?php if ($errores_form): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores_form as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form action="orden_guardar.php" method="POST" id="formOrden" enctype="multipart/form-data" novalidate>
      <?= csrf_campo() ?>

      <h2 class="h6 text-uppercase text-muted">1. Beneficiario</h2>
      <div class="row g-2 mb-3">
        <div class="col-md-5">
          <label class="form-label small mb-1" for="buscarBen">Buscar por cédula, RIF, extranjero o pasaporte</label>
          <input type="text" class="form-control" id="buscarBen" autocomplete="off" placeholder="Ej.: V-12345678, E-87654321, J-12345678-9, P-AB12345">
          <div class="form-text">También funciona con el nombre. Si hay un solo resultado, se selecciona solo.</div>
        </div>
        <div class="col-md-7">
          <label class="form-label small mb-1" for="id_beneficiario">Beneficiario registrado</label>
          <select class="form-select" id="id_beneficiario" name="id_beneficiario" required>
            <option value="">Seleccione</option>
            <?php foreach ($beneficiarios as $b): ?>
              <option value="<?= (int) $b['id_beneficiario'] ?>" data-doc="<?= e(normalizar_documento($b['tipo_documento'] . $b['cedula'])) ?>" <?= $sel_ben === (string) $b['id_beneficiario'] ? 'selected' : '' ?>>
                N° <?= e(numero_beneficiario((int) $b['id_beneficiario'])) ?> — <?= e($b['apellidos'] . ' ' . $b['nombres']) ?> — <?= e(documento_formateado($b['tipo_documento'], $b['cedula'])) ?> (<?= e($b['tipo_beneficiario']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">¿No está registrado? <a href="beneficiario_form.php?volver=orden">Registrar beneficiario</a></div>
        </div>
      </div>

      <h2 class="h6 text-uppercase text-muted">2. Datos de la salida</h2>
      <div class="row g-2 mb-3">
        <div class="col-md-4">
          <label class="form-label small mb-1" for="fecha_salida">Fecha de salida</label>
          <input type="date" class="form-control" id="fecha_salida" name="fecha_salida" min="<?= FECHA_MINIMA ?>" max="<?= date('Y-m-d') ?>" value="<?= e($fecha) ?>" required>
        </div>
        <div class="col-md-8">
          <label class="form-label small mb-1" for="observacion">Observación (opcional)</label>
          <input type="text" class="form-control" id="observacion" name="observacion" maxlength="255" value="<?= e($obs) ?>">
        </div>
      </div>

      <h2 class="h6 text-uppercase text-muted">3. Medicamentos entregados</h2>
      <div class="row g-2 mb-2">
        <div class="col-md-6">
          <input type="text" class="form-control form-control-sm" id="buscarMed" autocomplete="off" placeholder="Filtrar la lista de medicamentos escribiendo aquí">
        </div>
      </div>
      <div id="lineasOrden">
        <?php for ($i = 0; $i < $n_filas; $i++): ?>
        <div class="row g-2 mb-2 linea-orden">
          <div class="col-md-8">
            <select class="form-select" name="id_producto[]"><?= opciones_producto($productos, (string) ($prods[$i] ?? '')) ?></select>
          </div>
          <div class="col-8 col-md-3">
            <input type="text" inputmode="numeric" class="form-control" name="cantidad[]" maxlength="7" placeholder="Cantidad" value="<?= e($cants[$i] ?? '') ?>">
          </div>
          <div class="col-4 col-md-1">
            <button type="button" class="btn btn-outline-danger w-100 quitar-linea" aria-label="Quitar medicamento">&times;</button>
          </div>
        </div>
        <?php endfor; ?>
      </div>
      <button type="button" class="btn btn-outline-secondary btn-sm mb-4" id="agregarLinea">+ Agregar otro medicamento</button>
      <div class="form-text mb-3">Máximo 10 medicamentos por orden. La presentación aparece junto al nombre del medicamento.</div>

      <h2 class="h6 text-uppercase text-muted">4. Récipe médico (prueba de la orden)</h2>
      <div class="row g-2 mb-2">
        <div class="col-md-5">
          <label class="form-label small mb-1" for="recipe_numero">N° de récipe de la persona</label>
          <input type="text" class="form-control" id="recipe_numero" name="recipe_numero" maxlength="30" value="<?= e($d['recipe_numero'] ?? '') ?>" <?= RECIPE_OBLIGATORIO ? 'required' : '' ?>>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label small mb-1" for="recipe">Suba el récipe como prueba de lo que se está solicitando (PDF, JPG o PNG, máximo 5 MB). Queda asociado a la orden</label>
        <input type="file" class="form-control" id="recipe" name="recipe" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" <?= RECIPE_OBLIGATORIO ? 'required' : '' ?>>
        <div class="form-text"><?= RECIPE_OBLIGATORIO ? 'Es obligatorio.' : 'Es opcional.' ?> Si el formulario muestra algún error, deberá volver a seleccionar el archivo.</div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-institucional">Guardar y generar orden</button>
        <a href="ordenes.php" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div></div>
</div></div>
<?php require __DIR__ . '/../includes/pie.php'; ?>
