<?php
/**
 * Validaciones del lado del servidor (segunda capa).
 */
const FECHA_MINIMA          = '2000-01-01';
const ANIOS_MAXIMO_VENCIM   = 20;
const CANTIDAD_MAXIMA       = 1000000;

function limpiar(string $texto): string
{
    return trim(preg_replace('/\s+/u', ' ', $texto));
}

function fecha_valida(string $fecha): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

function fecha_maxima_vencimiento(): string
{
    return date('Y-m-d', strtotime('+' . ANIOS_MAXIMO_VENCIM . ' years'));
}

/**
 * Valida los datos de un producto.
 * @return array [errores, datos]
 */
function validar_producto(PDO $conexion, array $in, int $id_excluir = 0): array
{
    $errores = [];
    $nombre  = limpiar($in['nombre_producto'] ?? '');
    $id_cat  = $in['id_categoria'] ?? '';
    $id_pre  = $in['id_presentacion'] ?? '';
    $cant    = trim($in['cantidad'] ?? '');
    $fecha   = trim($in['fecha_vencimiento'] ?? '');

    // Nombre
    $largo = mb_strlen($nombre);
    if ($largo < 3 || $largo > 150) {
        $errores[] = 'El nombre debe tener entre 3 y 150 caracteres.';
    } elseif (!preg_match('/^[\p{L}\p{N}\s.,%\/()\-+ºª°#\']+$/u', $nombre)) {
        $errores[] = 'El nombre contiene caracteres no permitidos.';
    }

    // Categoría y presentación (deben existir y estar activas)
    if (!ctype_digit((string) $id_cat) || !categoria_visible((int) $id_cat) || !existe_activo($conexion, 'categorias', 'id_categoria', (int) $id_cat)) {
        $errores[] = 'Seleccione una categoría válida.';
    }
    if (!ctype_digit((string) $id_pre) || !existe_activo($conexion, 'presentaciones', 'id_presentacion', (int) $id_pre)) {
        $errores[] = 'Seleccione una presentación válida.';
    }

    // Cantidad
    if (!ctype_digit($cant) || (int) $cant > CANTIDAD_MAXIMA) {
        $errores[] = 'La cantidad debe ser un número entero entre 0 y ' . number_format(CANTIDAD_MAXIMA, 0, ',', '.') . '.';
    }

    // Fecha de vencimiento (opcional, con rango lógico)
    $fecha_bd = null;
    if ($fecha !== '') {
        if (!fecha_valida($fecha)) {
            $errores[] = 'La fecha de vencimiento no es válida.';
        } elseif ($fecha < FECHA_MINIMA || $fecha > fecha_maxima_vencimiento()) {
            $errores[] = 'La fecha de vencimiento debe estar entre el 01/01/2000 y el ' . date('d/m/Y', strtotime(fecha_maxima_vencimiento())) . '.';
        } else {
            $fecha_bd = $fecha;
        }
    }

    // Duplicados (mismo nombre, categoría, presentación y vencimiento)
    if (!$errores) {
        $stmt = $conexion->prepare(
            "SELECT id_producto FROM inventario_general
             WHERE nombre_producto = ? AND id_categoria = ? AND id_presentacion = ?
               AND fecha_vencimiento <=> ? AND id_producto <> ? LIMIT 1"
        );
        $stmt->execute([$nombre, (int) $id_cat, (int) $id_pre, $fecha_bd, $id_excluir]);
        if ($stmt->fetch()) {
            $errores[] = 'Ya existe un producto con el mismo nombre, categoría, presentación y fecha de vencimiento.';
        }
    }

    return [$errores, [
        'nombre_producto'   => $nombre,
        'id_categoria'      => (int) $id_cat,
        'id_presentacion'   => (int) $id_pre,
        'cantidad'          => (int) $cant,
        'fecha_vencimiento' => $fecha_bd,
    ]];
}

function existe_activo(PDO $conexion, string $tabla, string $pk, int $id): bool
{
    // $tabla y $pk son valores fijos del código (nunca provienen del usuario)
    $stmt = $conexion->prepare("SELECT 1 FROM $tabla WHERE $pk = ? AND status = 'Activo'");
    $stmt->execute([$id]);
    return (bool) $stmt->fetchColumn();
}

/**
 * Valida los datos de un usuario del sistema.
 * @return array [errores, datos]
 */
function validar_usuario(PDO $conexion, array $in, int $id_excluir = 0): array
{
    $errores = [];
    $cedula  = trim($in['cedula'] ?? '');
    $usuario = trim($in['nombre_usuario'] ?? '');
    $nombre  = limpiar($in['nombre_completo'] ?? '');
    $correo  = trim($in['correo'] ?? '');
    $rol     = $in['rol'] ?? '';
    $clave   = $in['clave'] ?? '';
    $nuevo   = $id_excluir === 0;

    if (!preg_match('/^\d{6,10}$/', $cedula)) {
        $errores[] = 'La cédula debe contener solo números (entre 6 y 10 dígitos).';
    }
    if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $usuario)) {
        $errores[] = 'El usuario debe tener entre 3 y 30 caracteres (letras, números, punto o guion bajo).';
    }
    if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 100 || !preg_match('/^[\p{L}\s.\'-]+$/u', $nombre)) {
        $errores[] = 'El nombre completo debe tener entre 3 y 100 caracteres y solo letras.';
    }
    if (mb_strlen($correo) > 100 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    }
    if (!in_array($rol, [ROL_ADMIN, ROL_ENCARGADO], true)) {
        $errores[] = 'Seleccione un rol válido.';
    }
    if ($nuevo || $clave !== '') {
        if (strlen($clave) < 8 || strlen($clave) > 72 || !preg_match('/[A-Za-z]/', $clave) || !preg_match('/\d/', $clave)) {
            $errores[] = 'La contraseña debe tener entre 8 y 72 caracteres e incluir letras y números.';
        }
    }

    // Duplicados a nivel lógico (además de los UNIQUE de la base de datos)
    if (!$errores) {
        foreach (['cedula' => [$cedula, 'La cédula'], 'nombre_usuario' => [$usuario, 'El nombre de usuario'], 'correo' => [$correo, 'El correo']] as $campo => [$valor, $etiqueta]) {
            $stmt = $conexion->prepare("SELECT 1 FROM usuarios WHERE $campo = ? AND id_usuario <> ? LIMIT 1");
            $stmt->execute([$valor, $id_excluir]);
            if ($stmt->fetchColumn()) {
                $errores[] = "$etiqueta ya está registrado.";
            }
        }
    }

    return [$errores, [
        'cedula' => $cedula, 'nombre_usuario' => $usuario, 'nombre_completo' => $nombre,
        'correo' => $correo, 'rol' => $rol, 'clave' => $clave,
    ]];
}

/**
 * Valida los datos de un movimiento (entrada o salida de insumos).
 * La existencia disponible se verifica después, dentro de la transacción de guardado.
 * @return array [errores, datos]
 */
function validar_movimiento(PDO $conexion, array $in): array
{
    $errores = [];
    $tipo    = $in['tipo'] ?? '';
    $id      = $in['id_producto'] ?? '';
    $cant    = trim($in['cantidad'] ?? '');
    $fecha   = trim($in['fecha_movimiento'] ?? '');
    $persona = limpiar($in['nombre_persona'] ?? '');
    $cedula  = trim($in['cedula_persona'] ?? '');
    $dest    = limpiar($in['destinatario'] ?? '');
    $obs     = limpiar($in['observacion'] ?? '');

    if (!in_array($tipo, ['Entrada', 'Salida'], true)) {
        $errores[] = 'El tipo de movimiento no es válido.';
    }
    if (!ctype_digit((string) $id) || !producto_visible($conexion, (int) $id)) {
        $errores[] = 'Seleccione un insumo válido y activo.';
    }
    if (!ctype_digit($cant) || (int) $cant < 1 || (int) $cant > CANTIDAD_MAXIMA) {
        $errores[] = 'La cantidad debe ser un número entero mayor que 0 y no superar ' . number_format(CANTIDAD_MAXIMA, 0, ',', '.') . '.';
    }
    if (!fecha_valida($fecha)) {
        $errores[] = 'La fecha del movimiento no es válida.';
    } elseif ($fecha > date('Y-m-d') || $fecha < FECHA_MINIMA) {
        $errores[] = 'La fecha del movimiento no puede ser futura ni anterior al 01/01/2000.';
    }

    $etiqueta = $tipo === 'Entrada' ? 'quien entrega o proveedor' : 'quien retira';
    if (mb_strlen($persona) < 3 || mb_strlen($persona) > 100 || !preg_match('/^[\p{L}\p{N}\s.,\'-]+$/u', $persona)) {
        $errores[] = "El nombre de la persona ($etiqueta) debe tener entre 3 y 100 caracteres.";
    }
    if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9-]{4,19}$/', $cedula)) {
        $errores[] = 'Indique la cédula, RIF, extranjero o pasaporte (de 5 a 20 caracteres: letras, números y guion).';
    }
    if ($tipo === 'Salida') {
        if (mb_strlen($dest) < 3 || mb_strlen($dest) > 100 || !preg_match('/^[\p{L}\p{N}\s.,()\/#\'-]+$/u', $dest)) {
            $errores[] = 'Indique para quién es el insumo (paciente o servicio), entre 3 y 100 caracteres.';
        }
    } else {
        $dest = '';
    }
    if (mb_strlen($obs) > 255) {
        $errores[] = 'La observación no puede superar los 255 caracteres.';
    }

    return [$errores, [
        'tipo' => $tipo, 'id_producto' => (int) $id, 'cantidad' => (int) $cant, 'fecha_movimiento' => $fecha,
        'nombre_persona' => $persona, 'cedula_persona' => $cedula,
        'destinatario' => $dest !== '' ? $dest : null, 'observacion' => $obs !== '' ? $obs : null,
    ]];
}

/** Verifica que el insumo exista, esté activo y pertenezca a una categoría que el sistema muestra. */
function producto_visible(PDO $conexion, int $id): bool
{
    $stmt = $conexion->prepare("SELECT 1 FROM inventario_general WHERE id_producto = ? AND status = 'Activo' AND id_categoria IN (" . categorias_sql() . ")");
    $stmt->execute([$id]);
    return (bool) $stmt->fetchColumn();
}

/** Número de orden de entrega como se muestra al usuario: OE-000012. */
function numero_orden(int $id): string
{
    return 'OE-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
}

/** Número correlativo del beneficiario en el registro: 0004. */
function numero_beneficiario(int $id): string
{
    return str_pad((string) $id, 4, '0', STR_PAD_LEFT);
}

/** Tipos de documento de identidad que se aceptan para un beneficiario. */
const TIPOS_DOCUMENTO = [
    'V'   => 'Cédula de identidad (V)',
    'E'   => 'Cédula de extranjero (E)',
    'RIF' => 'RIF',
    'P'   => 'Pasaporte (P)',
];

/** Quita guiones, puntos y espacios y pasa a mayúsculas: «v-12.345.678» queda «V12345678». */
function normalizar_documento(string $texto): string
{
    return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $texto));
}

/** Documento listo para mostrar: V-12345678, E-12345678, RIF J-12345678-9 o P-AB123456. */
function documento_formateado(string $tipo, string $numero): string
{
    if ($tipo === 'RIF') {
        if (preg_match('/^([A-Z])(\d{8})(\d)$/', $numero, $m)) {
            return 'RIF ' . $m[1] . '-' . $m[2] . '-' . $m[3];
        }
        return 'RIF ' . $numero;
    }
    return $tipo . '-' . $numero;
}

/** Nombre del documento según su tipo, para las pantallas y los PDF. */
function etiqueta_documento(string $tipo): string
{
    $etiquetas = ['V' => 'Cédula de identidad', 'E' => 'Cédula de extranjero', 'RIF' => 'RIF', 'P' => 'Pasaporte'];
    return $etiquetas[$tipo] ?? 'Documento';
}

/**
 * Valida los datos de un beneficiario (persona a quien se entrega medicamento).
 * @return array [errores, datos]
 */
function validar_beneficiario(PDO $conexion, array $in, int $id_excluir = 0): array
{
    $errores = [];
    $nombres   = limpiar($in['nombres'] ?? '');
    $apellidos = limpiar($in['apellidos'] ?? '');
    $tdoc      = $in['tipo_documento'] ?? '';
    $doc       = normalizar_documento((string) ($in['cedula'] ?? ''));
    $telefono  = limpiar($in['telefono'] ?? '');
    $tipo      = $in['tipo_beneficiario'] ?? '';

    if (mb_strlen($nombres) < 2 || mb_strlen($nombres) > 60 || !preg_match('/^[\p{L}\s.\'-]+$/u', $nombres)) {
        $errores[] = 'Los nombres deben tener entre 2 y 60 caracteres y solo letras.';
    }
    if (mb_strlen($apellidos) < 2 || mb_strlen($apellidos) > 60 || !preg_match('/^[\p{L}\s.\'-]+$/u', $apellidos)) {
        $errores[] = 'Los apellidos deben tener entre 2 y 60 caracteres y solo letras.';
    }

    // Documento de identidad según su tipo
    if (!isset(TIPOS_DOCUMENTO[$tdoc])) {
        $errores[] = 'Seleccione el tipo de documento (cédula V, extranjero E, RIF o pasaporte).';
    } elseif (($tdoc === 'V' || $tdoc === 'E') && !preg_match('/^\d{6,10}$/', $doc)) {
        $errores[] = 'La cédula (V o E) debe contener solo números, entre 6 y 10 dígitos.';
    } elseif ($tdoc === 'RIF' && !preg_match('/^[VEJGPC]\d{9}$/', $doc)) {
        $errores[] = 'El RIF debe tener una letra (V, E, J, G, P o C) seguida de 9 dígitos. Ejemplo: J-12345678-9.';
    } elseif ($tdoc === 'P' && !preg_match('/^[A-Z0-9]{5,15}$/', $doc)) {
        $errores[] = 'El pasaporte debe tener entre 5 y 15 letras o números, sin símbolos.';
    }

    // Teléfono (opcional)
    if ($telefono !== '') {
        $digitos = preg_replace('/\D/', '', $telefono);
        if (!preg_match('/^[0-9+() -]{7,20}$/', $telefono) || strlen($digitos) < 7 || strlen($digitos) > 15) {
            $errores[] = 'El teléfono debe tener entre 7 y 15 dígitos (puede usar +, espacios, guiones y paréntesis).';
        }
    }
    if (!in_array($tipo, ['Trabajador', 'Afiliado'], true)) {
        $errores[] = 'Indique si el beneficiario es Trabajador o Afiliado.';
    }
    if (!$errores) {
        $stmt = $conexion->prepare("SELECT id_beneficiario FROM beneficiarios WHERE tipo_documento = ? AND cedula = ? AND id_beneficiario <> ? LIMIT 1");
        $stmt->execute([$tdoc, $doc, $id_excluir]);
        if ($stmt->fetch()) {
            $errores[] = 'Ya existe un beneficiario registrado con ese documento.';
        }
    }
    return [$errores, [
        'nombres' => $nombres, 'apellidos' => $apellidos, 'tipo_documento' => $tdoc, 'cedula' => $doc,
        'telefono' => $telefono !== '' ? $telefono : null, 'tipo_beneficiario' => $tipo,
    ]];
}

/**
 * Valida los datos de una orden de entrega (varias líneas de medicamento para un beneficiario).
 * La existencia suficiente se comprueba después, dentro de la transacción de guardado.
 * @return array [errores, datos]   datos['lineas'] es [id_producto => cantidad total]
 */
function validar_orden(PDO $conexion, array $in): array
{
    $errores = [];
    $id_ben  = $in['id_beneficiario'] ?? '';
    $fecha   = trim($in['fecha_salida'] ?? '');
    $obs     = limpiar($in['observacion'] ?? '');
    $rnum    = limpiar($in['recipe_numero'] ?? '');
    $prods   = is_array($in['id_producto'] ?? null) ? $in['id_producto'] : [];
    $cants   = is_array($in['cantidad'] ?? null) ? $in['cantidad'] : [];

    if (!ctype_digit((string) $id_ben) || !existe_activo($conexion, 'beneficiarios', 'id_beneficiario', (int) $id_ben)) {
        $errores[] = 'Seleccione un beneficiario registrado y activo.';
    }
    if (!fecha_valida($fecha)) {
        $errores[] = 'La fecha de salida no es válida.';
    } elseif ($fecha > date('Y-m-d') || $fecha < FECHA_MINIMA) {
        $errores[] = 'La fecha de salida no puede ser futura ni anterior al 01/01/2000.';
    }

    $lineas = [];
    $n = 0;
    foreach ($prods as $i => $idp) {
        $idp  = trim((string) $idp);
        $cant = trim((string) ($cants[$i] ?? ''));
        if ($idp === '' && $cant === '') {
            continue; // fila vacía
        }
        $n++;
        if ($n > 10) {
            $errores[] = 'Una orden admite como máximo 10 medicamentos.';
            break;
        }
        if (!ctype_digit($idp) || !producto_visible($conexion, (int) $idp)) {
            $errores[] = "Línea $n: seleccione un medicamento válido.";
            continue;
        }
        if (!ctype_digit($cant) || (int) $cant < 1 || (int) $cant > CANTIDAD_MAXIMA) {
            $errores[] = "Línea $n: la cantidad debe ser un entero mayor que 0 y no superar " . number_format(CANTIDAD_MAXIMA, 0, ',', '.') . '.';
            continue;
        }
        $lineas[(int) $idp] = ($lineas[(int) $idp] ?? 0) + (int) $cant; // si se repite el medicamento, se suman
    }
    if (!$lineas && !$errores) {
        $errores[] = 'Agregue al menos un medicamento a la orden.';
    }
    if (mb_strlen($obs) > 255) {
        $errores[] = 'La observación no puede superar los 255 caracteres.';
    }
    if ($rnum === '' && RECIPE_OBLIGATORIO) {
        $errores[] = 'Escriba el número del récipe de la persona.';
    } elseif ($rnum !== '' && !preg_match('/^[A-Za-z0-9][A-Za-z0-9\/ -]{1,29}$/', $rnum)) {
        $errores[] = 'El número de récipe solo admite letras, números, guion y barra (de 2 a 30 caracteres).';
    }
    return [$errores, ['id_beneficiario' => (int) $id_ben, 'fecha_salida' => $fecha, 'observacion' => $obs !== '' ? $obs : null,
                       'recipe_numero' => $rnum !== '' ? $rnum : null, 'lineas' => $lineas]];
}

/**
 * Valida el archivo del récipe médico subido con el formulario de la orden.
 * Acepta PDF, JPG o PNG de hasta 5 MB y comprueba el contenido real del archivo, no solo la extensión.
 * @return array [errores, info]  info = tmp, ext, original, guardado (o null si no se subió archivo)
 */
function validar_recipe(?array $f): array
{
    $errores = [];
    $codigo  = $f['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($codigo === UPLOAD_ERR_NO_FILE) {
        if (RECIPE_OBLIGATORIO) {
            $errores[] = 'Adjunte el récipe médico (PDF, JPG o PNG).';
        }
        return [$errores, null];
    }
    if ($codigo === UPLOAD_ERR_INI_SIZE || $codigo === UPLOAD_ERR_FORM_SIZE) {
        return [['El archivo del récipe es demasiado grande (máximo 5 MB).'], null];
    }
    if ($codigo !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
        return [['No se pudo subir el archivo del récipe. Intente de nuevo.'], null];
    }
    if ($f['size'] < 1 || $f['size'] > RECIPE_MAX_BYTES) {
        return [['El archivo del récipe debe pesar entre 1 byte y 5 MB.'], null];
    }

    $nombre = basename((string) $f['name']);
    $ext    = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
    if (!isset(RECIPE_EXTENSIONES[$ext])) {
        return [['El récipe debe ser un archivo PDF, JPG o PNG.'], null];
    }

    // Contenido real del archivo
    if (class_exists('finfo')) {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if ($mime !== RECIPE_EXTENSIONES[$ext]) {
            return [['El contenido del archivo no corresponde a un ' . strtoupper($ext) . ' válido.'], null];
        }
    }
    if ($ext === 'pdf') {
        $cabecera = (string) file_get_contents($f['tmp_name'], false, null, 0, 5);
        if ($cabecera !== '%PDF-') {
            return [['El archivo PDF del récipe no es válido.'], null];
        }
    } elseif (@getimagesize($f['tmp_name']) === false) {
        return [['La imagen del récipe no es válida.'], null];
    }

    return [[], [
        'tmp'      => $f['tmp_name'],
        'ext'      => $ext,
        'original' => mb_substr(preg_replace('/[^\p{L}\p{N}._ -]/u', '_', $nombre), 0, 150),
        'guardado' => bin2hex(random_bytes(16)) . '.' . $ext,   // nombre aleatorio: nadie puede adivinarlo
    ]];
}
