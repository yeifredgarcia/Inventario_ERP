<?php
/**
 * VERSIÓN CON LIBRERÍA DOMPDF.
 * Los PDF se arman como una página HTML con CSS y DOMPDF la convierte en PDF.
 * Mantiene la misma interfaz que la versión sin librería, así que las páginas del sistema no cambian:
 *   $pdf = new PdfTabla('Título', ['Línea de filtros']);
 *   $pdf->tabla(['Col A', 'Col B'], [10, 30], $filas);   // los anchos son proporciones
 *   $pdf->descargar('archivo.pdf', 'Texto del pie');
 *
 * Requiere libs/dompdf/autoload.inc.php  o  vendor/autoload.php  (ver libs/dompdf/LEEME.txt).
 */
$cargado_dompdf = false;
foreach ([__DIR__ . '/../libs/dompdf/autoload.inc.php', __DIR__ . '/../vendor/autoload.php'] as $ruta_autoload) {
    if (is_file($ruta_autoload)) {
        require_once $ruta_autoload;
        $cargado_dompdf = true;
        break;
    }
}
if (!$cargado_dompdf) {
    http_response_code(500);
    die('Falta la librería DOMPDF. Instálela como se indica en libs/dompdf/LEEME.txt (descarga directa o composer require dompdf/dompdf).');
}

/** Utilidades comunes: escape HTML, estilos y conversión del HTML a PDF con DOMPDF. */
class PdfHtml
{
    public static function h($valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }

    public const ESTILOS = "
        @page { margin: 30px 32px 44px 32px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 8px; color: #111; }
        h1 { font-size: 15px; margin: 0 0 4px 0; color: #0b4f8a; }
        p.sub { font-size: 8px; color: #444; margin: 0 0 2px 0; }
        table.t { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 8px; }
        table.t thead { display: table-header-group; }
        table.t th { background: #e4eaf2; border: 1px solid #8a96a3; padding: 3px 4px; text-align: left; font-size: 8px; }
        table.t td { border: 1px solid #b8c0c8; padding: 2px 4px; vertical-align: top; word-wrap: break-word; }
        table.t tr { page-break-inside: avoid; }
    ";

    /** Convierte el HTML en PDF, agrega el pie con número de página y lo envía como descarga. */
    public static function descargar(string $html, string $nombre, string $orientacion, string $pie): void
    {
        $opciones = new \Dompdf\Options();
        $opciones->set('defaultFont', 'DejaVu Sans');
        $opciones->set('isRemoteEnabled', false);        // no se descarga nada de internet
        $opciones->set('chroot', realpath(__DIR__ . '/..')); // DOMPDF solo puede leer archivos dentro del sistema

        $dompdf = new \Dompdf\Dompdf($opciones);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', $orientacion);
        $dompdf->render();

        // Pie de página en todas las hojas: texto a la izquierda y «Pagina X/Y» a la derecha
        $canvas  = $dompdf->getCanvas();
        $metricas = $dompdf->getFontMetrics();
        $fuente  = method_exists($metricas, 'getFont') ? $metricas->getFont('DejaVu Sans', 'normal') : $metricas->get_font('DejaVu Sans', 'normal');
        $ancho   = $canvas->get_width();
        $alto    = $canvas->get_height();
        $canvas->page_text(32, $alto - 26, $pie, $fuente, 7, [0.3, 0.3, 0.3]);
        $canvas->page_text($ancho - 32 - 62, $alto - 26, 'Pagina {PAGE_NUM}/{PAGE_COUNT}', $fuente, 7, [0.3, 0.3, 0.3]);

        $dompdf->stream(preg_replace('/[^A-Za-z0-9_.-]/', '_', $nombre), ['Attachment' => true]);
        exit();
    }
}

class PdfTabla
{
    private string $titulo;
    private array $subtitulos;
    private string $tabla_html = '';

    public function __construct(string $titulo, array $subtitulos = [])
    {
        $this->titulo = $titulo;
        $this->subtitulos = $subtitulos;
    }

    /** Los anchos se reparten en proporción sobre el ancho de la hoja. */
    public function tabla(array $cabeceras, array $anchos, array $filas): void
    {
        $suma = max(1, array_sum($anchos));
        $h = '<table class="t"><thead><tr>';
        foreach ($cabeceras as $i => $c) {
            $h .= '<th style="width:' . round(($anchos[$i] ?? 1) / $suma * 100, 2) . '%">' . PdfHtml::h($c) . '</th>';
        }
        $h .= '</tr></thead><tbody>';
        foreach ($filas as $fila) {
            $h .= '<tr>';
            foreach (array_keys($cabeceras) as $i) {
                $h .= '<td>' . PdfHtml::h($fila[$i] ?? '') . '</td>';
            }
            $h .= '</tr>';
        }
        $this->tabla_html = $h . '</tbody></table>';
    }

    public function descargar(string $nombre_archivo, string $pie = 'Sistema de Inventario Medico'): void
    {
        $html = '<html><head><meta charset="UTF-8"><style>' . PdfHtml::ESTILOS . '</style></head><body>'
              . '<h1>' . PdfHtml::h($this->titulo) . '</h1>';
        foreach ($this->subtitulos as $sub) {
            $html .= '<p class="sub">' . PdfHtml::h($sub) . '</p>';
        }
        $html .= $this->tabla_html . '</body></html>';
        PdfHtml::descargar($html, $nombre_archivo, 'landscape', $pie);
    }
}

/** Formatea una fecha/hora de la base de datos para mostrarla (dd/mm/aaaa [hh:mm]). */
function fecha_pdf(?string $f, bool $con_hora = false): string
{
    if (!$f) {
        return '';
    }
    return date($con_hora ? 'd/m/Y H:i' : 'd/m/Y', strtotime($f));
}
