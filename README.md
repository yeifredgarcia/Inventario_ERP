# VERSIÓN CON LIBRERÍA DOMPDF

Esta versión genera los PDF con la librería **DOMPDF**, que **no viene incluida**: instálela como se explica en `libs/dompdf/LEEME.txt` (descarga directa o `composer require dompdf/dompdf`). Solo cambian `includes/pdf.php` e `includes/pdf_orden.php`; el resto del sistema es idéntico a la versión sin librería.

Ventaja de esta versión: si el récipe se sube como imagen (JPG o PNG), la orden de entrega en PDF incluye una segunda hoja con el récipe como prueba. Si el récipe es un PDF, queda asociado a la orden en el sistema (no se incrusta en el PDF).

---

# Sistema de Inventario Médico – PST Trayecto II

## Instalación (XAMPP)
1. Copie esta carpeta a `htdocs/inventario_ERP`.
2. En phpMyAdmin, importe `sql/inventario_medico.sql` (crea la base, las tablas, los datos y la vista).
3. Si su MySQL no usa `root` sin clave, edite `config/conexion.php` (es el único archivo de conexión).
4. Abra `http://localhost/inventario_ERP/` e ingrese con **admin / Admin2026\*** y cambie la clave desde *Usuarios*.
5. Bootstrap se carga desde internet (CDN). Para usarlo sin conexión, descargue Bootstrap 5 a `css/` y `js/` y cambie los enlaces en `includes/encabezado.php` y `includes/pie.php`.

## Estructura
```
config/conexion.php        única conexión PDO (require_once en cada página)
includes/                  seguridad (sesiones, roles, CSRF), validaciones, auditoría, plantilla
modulos/                   listar, agregar, editar, guardar, actualizar, estatus (baja),
                           reporte, auditoria, usuarios, usuario_form, usuario_guardar
js/  css/  sql/  docs/     scripts, estilos, base de datos y diccionario de datos + MER
```

## Cumplimiento de la guía de requisitos
| Requisito | Dónde |
|---|---|
| PHP + MySQL | Todo el sistema |
| Un solo archivo de conexión | `config/conexion.php` |
| Sentencias preparadas | PDO con parámetros en todas las consultas |
| Contraseñas cifradas | `password_hash()` / `password_verify()`; se eliminó la comparación en texto plano |
| Sesiones seguras | `includes/seguridad.php`: cookie HttpOnly, `session_regenerate_id`, expiración por inactividad, usuario inactivo pierde la sesión |
| Alta / Baja (Activo-Inactivo) / Modificación / Consulta | `agregar`, `estatus`, `editar`, `listar` (ya no se borran filas) |
| Roles | Administrador (todo) y Encargado (consultar, registrar, corregir y reportes). Máximo 2 usuarios en total |
| Validación en cliente (JS) | `js/*.js`: numéricos, formato de texto, longitud, correo, fechas |
| Validación en servidor | `includes/validaciones.php`: duplicados, existencia de registros, rangos de fecha |
| Reportes con filtros | `modulos/reporte.php` (imprimir o guardar como PDF desde el navegador) |
| Auditoría | `modulos/auditoria.php` (solo administrador) |
| Bootstrap y colores institucionales | `css/estilos.css` (variables al inicio; ajústelas a los colores de la universidad) |
| PK, FK, UNIQUE, campos de auditoría | `sql/inventario_medico.sql` |
| Diccionario de datos y MER | `docs/diccionario_datos.md` |

## Cambios importantes en los datos
- Las dos bases (`inventario_medico` y `usuarios_db`) se unieron en una sola.
- `estado` ya no se guarda: se calcula desde `fecha_vencimiento` (vista `v_inventario`; 90 días o menos = Por Vencer).
- Se eliminaron 39 lentes intraoculares repetidos (ids 506 a 544) y se fusionó FLUOXETINA 20 MG (ids 393 y 402, cantidad 170).
- Se corrigieron nombres de categorías y presentaciones (ortografía y acentos).

## Novedades de esta versión
- **Entradas y salidas de insumos** (`modulos/movimientos.php`): registra quién retira o entrega (nombre y cédula), para quién es (paciente o servicio), cantidad y fecha. Actualiza la existencia automáticamente, impide retirar más de lo que hay y no deja retirar insumos vencidos.
- **Quién entra y sale** (`modulos/accesos.php`): ingresos, cierres de sesión, sesiones expiradas e intentos fallidos.
- **Exportar PDF** en inventario, reportes, movimientos, accesos y auditoría (`includes/pdf.php`, sin librerías externas).
- Menú en barra lateral izquierda (en el celular se abre con el botón "Menú").
- El inicio ya no tiene módulos por categoría: solo "Inventario general", donde se corrige (editar) y se filtra por categoría.
- Vista general en el inicio: personas con acceso, quiénes han entrado y últimas modificaciones. El registro completo está en "Registro de actividad".
- Rol "Usuario" renombrado a "Encargado". El administrador registra y edita a la otra persona desde "Usuarios" (límite de 2).

- Al Encargado, las opciones que no le corresponden (Quién entra y sale, Auditoría, Usuarios, Dar de baja) se le muestran con candado y un aviso que explica que son solo del Administrador. Si intenta abrirlas por la dirección, el servidor lo regresa al inicio con el mismo mensaje.

## Actualización: solo medicamentos y nuevo inicio
- El sistema muestra y gestiona **solo la categoría Medicamentos** (inventario, formularios, entradas y salidas, reportes y PDF). Los datos de las otras categorías siguen guardados en la base, pero no se ven. Para mostrar otra, agregue su id en `CATEGORIAS_VISIBLES` de `includes/seguridad.php` (por ejemplo `[1, 3]` para incluir Blíster).
- El inicio vuelve a mostrar los totales, las personas con acceso, quiénes han entrado y salido, las últimas modificaciones y las últimas entradas y salidas, junto con la tarjeta "Inventario general". Se mantiene la barra lateral y el resto de las modificaciones.

## Actualización: beneficiarios y órdenes de entrega
- **Registro de beneficiarios** (`modulos/beneficiarios.php`): lista numerada (0001, 0002...) de trabajadores y afiliados con nombres, apellidos y cédula.
- **Órdenes de entrega** (`modulos/ordenes.php`, `orden_form.php`): la salida de medicamentos ahora se registra con una orden a un beneficiario, con uno o varios medicamentos (presentación y cantidad). Descuenta la existencia y numera la orden (OE-000001).
- **PDF de la orden de entrega** (`modulos/orden_pdf.php`): fecha de salida, nombre, apellido, cédula, condición (trabajador o afiliado), medicamentos con presentación y cantidad, y recuadros de firma del responsable y del beneficiario.
- Las **entradas** siguen en "Entradas y salidas" (`movimientos.php`); el botón de salida lleva a la orden de entrega.
- **Base de datos:** si ya importó la versión anterior y quiere conservar sus datos, ejecute una sola vez `sql/actualizacion_ordenes.sql`. Si empieza de cero, importe `sql/inventario_medico.sql`.

## Actualización: récipe médico y auditoría
- En la **orden de entrega** se sube el récipe de lo solicitado (PDF, JPG o PNG, máximo 5 MB). Se guarda en `uploads/recipes/` con un nombre aleatorio y la carpeta está bloqueada con `.htaccess`: solo se abre desde el sistema (`modulos/orden_recipe.php`), con sesión. Para hacerlo opcional, ponga `RECIPE_OBLIGATORIO = false` en `includes/seguridad.php`.
- Base de datos: si ya tenía las tablas de órdenes, ejecute `sql/actualizacion_recipe.sql` (una vez). Si no las tenía y quiere conservar sus datos, `sql/actualizacion_ordenes.sql`. Desde cero: `sql/inventario_medico.sql`.
- Auditoría: se quitó la columna «Reg.» (era el número del registro afectado por el evento).

## Actualización: documentos, teléfono, número de récipe
- **Beneficiarios:** además de la cédula venezolana (V) se aceptan cédula de extranjero (E), RIF y pasaporte (P), y se puede registrar un teléfono (opcional). En la orden de entrega el beneficiario se busca escribiendo el documento (por ejemplo `V-12345678`, `J-12345678-9`, `P-AB12345`) o el nombre.
- **Orden de entrega:** además del archivo del récipe se escribe el **número del récipe** de la persona; ambos quedan asociados a la orden.
- **Inventario:** se quitaron de Medicamentos los insumos 453 a 458 (SOLUCION 0,9, GLUCOSA, RINGER, DEXTROSA, AGUA OXIGENADA y ALCOHOL).
- **Base de datos:** si ya tiene datos, ejecute una vez `sql/actualizacion_documentos.sql` (después de los scripts anteriores). Desde cero: `sql/inventario_medico.sql`.
