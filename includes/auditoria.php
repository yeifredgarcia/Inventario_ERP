<?php
/**
 * Registro de eventos en la tabla auditoria (historial consultable por el administrador).
 */
function registrar_evento(PDO $conexion, string $accion, string $tabla = '', ?int $id_registro = null, string $detalle = '', ?string $usuario_forzado = null): void
{
    $id_usuario = $_SESSION['id_usuario'] ?? null;
    $nombre     = $usuario_forzado ?? ($_SESSION['usuario'] ?? 'anónimo');
    $ip         = $_SERVER['REMOTE_ADDR'] ?? '';

    try {
        $sql = "INSERT INTO auditoria
                    (id_usuario, nombre_usuario, accion, tabla_afectada, id_registro, detalle, ip_origen, usuario_registro)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $conexion->prepare($sql)->execute([
            $id_usuario,
            mb_substr($nombre, 0, 50),
            $accion,
            $tabla !== '' ? $tabla : null,
            $id_registro,
            mb_substr($detalle, 0, 500),
            $ip,
            $id_usuario,
        ]);
    } catch (PDOException $e) {
        error_log('No se pudo registrar el evento de auditoría: ' . $e->getMessage());
    }
}

/** Texto legible para cada código de acción de la auditoría. */
function etiqueta_accion(string $accion): string
{
    $etiquetas = [
        'LOGIN'              => 'Inició sesión',
        'LOGIN_FALLIDO'      => 'Intento de ingreso fallido',
        'LOGIN_INACTIVO'     => 'Intento con usuario inactivo',
        'LOGOUT'             => 'Cerró sesión',
        'CREAR_PRODUCTO'     => 'Registró un insumo',
        'EDITAR_PRODUCTO'    => 'Corrigió un insumo',
        'BAJA_PRODUCTO'      => 'Dio de baja un insumo',
        'REACTIVAR_PRODUCTO' => 'Reactivó un insumo',
        'CREAR_USUARIO'      => 'Registró un usuario',
        'EDITAR_USUARIO'     => 'Editó un usuario',
        'REPORTE'            => 'Generó un reporte',
        'ENTRADA_INSUMO'     => 'Registró una entrada de insumo',
        'SALIDA_INSUMO'      => 'Registró una salida de insumo',
        'SESION_EXPIRADA'    => 'Sesión cerrada por inactividad',
        'EXPORTAR_PDF'       => 'Exportó un PDF',
        'VER_RECIPE'         => 'Consultó un récipe',
        'CREAR_BENEFICIARIO' => 'Registró un beneficiario',
        'EDITAR_BENEFICIARIO'=> 'Editó un beneficiario',
        'ORDEN_ENTREGA'      => 'Generó una orden de entrega',
    ];
    return $etiquetas[$accion] ?? $accion;
}
