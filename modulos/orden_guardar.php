<?php
/** Guarda una orden de entrega: crea la orden, descuenta la existencia y registra cada salida, todo en una transacción. */
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/auditoria.php';
requerir_login($conexion, '../');
requerir_permiso('ordenes', '../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ordenes.php'); exit(); }
verificar_csrf();

[$errores, $d] = validar_orden($conexion, $_POST);
[$err_recipe, $recipe] = validar_recipe($_FILES['recipe'] ?? null);
$errores = array_merge($errores, $err_recipe);
$destino = null; // ruta del archivo del récipe, si llega a guardarse

if (!$errores) {
    try {
        $conexion->beginTransaction();

        $s = $conexion->prepare("SELECT nombres, apellidos, tipo_documento, cedula, tipo_beneficiario, status FROM beneficiarios WHERE id_beneficiario = ?");
        $s->execute([$d['id_beneficiario']]);
        $b = $s->fetch();
        if (!$b || $b['status'] !== 'Activo') {
            throw new RuntimeException('El beneficiario no existe o está inactivo.');
        }
        $nombre_completo = $b['nombres'] . ' ' . $b['apellidos'];
        $doc_txt = documento_formateado($b['tipo_documento'], $b['cedula']);
        $uid = id_usuario_actual();

        $conexion->prepare("INSERT INTO ordenes_entrega (id_beneficiario, fecha_salida, observacion, recipe_numero, recipe_archivo, recipe_original, usuario_registro) VALUES (?, ?, ?, ?, ?, ?, ?)")
                 ->execute([$d['id_beneficiario'], $d['fecha_salida'], $d['observacion'], $d['recipe_numero'], $recipe['guardado'] ?? null, $recipe['original'] ?? null, $uid]);
        $id_orden = (int) $conexion->lastInsertId();
        $num      = numero_orden($id_orden);

        ksort($d['lineas']); // se bloquean los insumos siempre en el mismo orden para evitar bloqueos cruzados
        $sel = $conexion->prepare("SELECT nombre_producto, cantidad, status, fecha_vencimiento, id_categoria FROM inventario_general WHERE id_producto = ? FOR UPDATE");
        $upd = $conexion->prepare("UPDATE inventario_general SET cantidad = ?, fecha_modificacion = NOW(), usuario_modificacion = ? WHERE id_producto = ?");
        $ins = $conexion->prepare(
            "INSERT INTO movimientos_inventario
                (id_producto, id_orden, tipo, cantidad, existencia_resultante, fecha_movimiento, nombre_persona, cedula_persona, destinatario, observacion, usuario_registro)
             VALUES (?, ?, 'Salida', ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        foreach ($d['lineas'] as $id_producto => $cantidad) {
            $sel->execute([$id_producto]);
            $p = $sel->fetch();
            if (!$p || $p['status'] !== 'Activo' || !categoria_visible((int) $p['id_categoria'])) {
                throw new RuntimeException('Uno de los medicamentos no existe o está inactivo.');
            }
            if ($p['fecha_vencimiento'] && $p['fecha_vencimiento'] < date('Y-m-d')) {
                throw new RuntimeException("«{$p['nombre_producto']}» está vencido y no puede entregarse.");
            }
            $existencia = (int) $p['cantidad'];
            if ($cantidad > $existencia) {
                throw new RuntimeException("No hay existencia suficiente de «{$p['nombre_producto']}»: disponible $existencia, solicitado $cantidad.");
            }
            $nueva = $existencia - $cantidad;
            $upd->execute([$nueva, $uid, $id_producto]);
            $ins->execute([
                $id_producto, $id_orden, $cantidad, $nueva, $d['fecha_salida'],
                mb_substr($nombre_completo, 0, 100), $doc_txt, mb_substr($nombre_completo . ' (' . $b['tipo_beneficiario'] . ')', 0, 100),
                "Orden $num", $uid,
            ]);
        }
        // Se guarda el archivo del récipe antes de confirmar: si falla, se deshace toda la orden
        if ($recipe) {
            if (!is_dir(RECIPE_DIR) && !mkdir(RECIPE_DIR, 0755, true)) {
                throw new RuntimeException('No se pudo crear la carpeta de récipes en el servidor.');
            }
            $destino = RECIPE_DIR . '/' . $recipe['guardado'];
            if (!move_uploaded_file($recipe['tmp'], $destino)) {
                $destino = null;
                throw new RuntimeException('No se pudo guardar el archivo del récipe en el servidor.');
            }
        }
        $conexion->commit();

        registrar_evento($conexion, 'ORDEN_ENTREGA', 'ordenes_entrega', $id_orden,
            "Orden $num para $nombre_completo ({$doc_txt}, {$b['tipo_beneficiario']}): " . count($d['lineas']) . ' medicamento(s), ' . array_sum($d['lineas']) . ' unidad(es)' . ($d['recipe_numero'] ? ". Récipe N° {$d['recipe_numero']}" : '') . ($recipe ? ". Récipe adjunto: {$recipe['original']}" : ''));
        flash('success', "Orden $num registrada. Ya puede descargar la orden de entrega en PDF para las firmas.");
        header("Location: orden_ver.php?id=$id_orden");
        exit();
    } catch (RuntimeException $ex) {
        if ($conexion->inTransaction()) $conexion->rollBack();
        if ($destino && is_file($destino)) @unlink($destino);
        $errores[] = $ex->getMessage();
    } catch (PDOException $ex) {
        if ($conexion->inTransaction()) $conexion->rollBack();
        if ($destino && is_file($destino)) @unlink($destino);
        error_log('orden_guardar.php: ' . $ex->getMessage());
        $errores[] = 'No fue posible registrar la orden. Intente de nuevo.';
    }
}

$_SESSION['errores_form'] = $errores;
$_SESSION['datos_form']   = $_POST;
header('Location: orden_form.php');
exit();
