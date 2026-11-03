<?php
/** Registra una entrada o salida y actualiza la existencia del insumo en una sola transacción. */
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/auditoria.php';
requerir_login($conexion, '../');
requerir_permiso('movimientos', '../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: movimientos.php'); exit(); }
verificar_csrf();

[$errores, $d] = validar_movimiento($conexion, $_POST);
if ($d['tipo'] === 'Salida') {
    flash('warning', 'Las salidas de medicamentos se registran con una orden de entrega a un beneficiario.');
    header('Location: orden_form.php');
    exit();
}
$volver_form = 'movimiento_form.php?tipo=' . urlencode('Entrada');

if (!$errores) {
    try {
        $conexion->beginTransaction();

        // Se bloquea la fila para que dos registros simultáneos no dañen la existencia
        $s = $conexion->prepare("SELECT nombre_producto, cantidad, status, fecha_vencimiento FROM inventario_general WHERE id_producto = ? FOR UPDATE");
        $s->execute([$d['id_producto']]);
        $p = $s->fetch();
        if (!$p || $p['status'] !== 'Activo') {
            throw new RuntimeException('El insumo no existe o está inactivo.');
        }

        $existencia = (int) $p['cantidad'];
        if ($d['tipo'] === 'Salida') {
            if ($p['fecha_vencimiento'] && $p['fecha_vencimiento'] < date('Y-m-d')) {
                throw new RuntimeException('El insumo está vencido y no puede retirarse.');
            }
            if ($d['cantidad'] > $existencia) {
                throw new RuntimeException("No hay existencia suficiente: disponible $existencia, solicitado {$d['cantidad']}.");
            }
            $nueva = $existencia - $d['cantidad'];
        } else {
            $nueva = $existencia + $d['cantidad'];
            if ($nueva > CANTIDAD_MAXIMA) {
                throw new RuntimeException('La existencia resultante supera el máximo permitido.');
            }
        }

        $uid = id_usuario_actual();
        $conexion->prepare("UPDATE inventario_general SET cantidad = ?, fecha_modificacion = NOW(), usuario_modificacion = ? WHERE id_producto = ?")
                 ->execute([$nueva, $uid, $d['id_producto']]);

        $conexion->prepare(
            "INSERT INTO movimientos_inventario
                (id_producto, tipo, cantidad, existencia_resultante, fecha_movimiento, nombre_persona, cedula_persona, destinatario, observacion, usuario_registro)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        )->execute([$d['id_producto'], $d['tipo'], $d['cantidad'], $nueva, $d['fecha_movimiento'],
                    $d['nombre_persona'], $d['cedula_persona'], $d['destinatario'], $d['observacion'], $uid]);
        $id_mov = (int) $conexion->lastInsertId();

        $conexion->commit();

        $detalle = "{$d['tipo']} de {$d['cantidad']} × «{$p['nombre_producto']}» ({$existencia} → {$nueva}). "
                 . ($d['tipo'] === 'Salida' ? "Retira: {$d['nombre_persona']} (C.I. {$d['cedula_persona']}); para: {$d['destinatario']}" : "Entrega: {$d['nombre_persona']} (C.I. {$d['cedula_persona']})");
        registrar_evento($conexion, $d['tipo'] === 'Salida' ? 'SALIDA_INSUMO' : 'ENTRADA_INSUMO', 'movimientos_inventario', $id_mov, $detalle);

        flash('success', $d['tipo'] === 'Salida' ? 'Salida registrada correctamente.' : 'Entrada registrada correctamente.');
        header('Location: movimientos.php');
        exit();
    } catch (RuntimeException $ex) {
        if ($conexion->inTransaction()) $conexion->rollBack();
        $errores[] = $ex->getMessage();
    } catch (PDOException $ex) {
        if ($conexion->inTransaction()) $conexion->rollBack();
        error_log('movimiento_guardar.php: ' . $ex->getMessage());
        $errores[] = 'No fue posible registrar el movimiento. Intente de nuevo.';
    }
}

$_SESSION['errores_form'] = $errores;
$_SESSION['datos_form']   = $_POST;
header("Location: $volver_form");
exit();
