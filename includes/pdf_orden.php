<?php
/**
 * VERSIÓN CON LIBRERÍA DOMPDF.
 * Orden de entrega de medicamentos en PDF (A4 vertical): fecha de salida, datos del beneficiario
 * (nombre, apellido, documento, teléfono y condición de trabajador o afiliado), medicamentos con presentación
 * y cantidad, número de récipe, recuadros de firma de ambas partes y, si el récipe se subió como imagen,
 * una segunda hoja con el récipe como prueba (anexo).
 *
 * $orden: numero, fecha, nombre, apellido, doc_etiqueta, cedula (documento ya formateado), telefono, tipo,
 *         observacion, recipe, recipe_numero, recipe_original, recipe_ruta, entregado_por
 * $lineas: lista de [medicamento, presentacion, cantidad]
 */
require_once __DIR__ . '/pdf.php';

class PdfOrden
{
    private array $orden;
    private array $lineas;
    private string $generado;

    public function __construct(array $orden, array $lineas, string $generado)
    {
        $this->orden = $orden;
        $this->lineas = $lineas;
        $this->generado = $generado;
    }

    /** Segunda hoja con la imagen del récipe, ajustada a la hoja. Devuelve '' si el récipe no es una imagen. */
    private function anexo_recipe(): string
    {
        $o = $this->orden;
        $ruta = $o['recipe_ruta'] ?? '';
        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        if ($ruta === '' || !is_file($ruta) || !in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            return '';
        }
        $tam = @getimagesize($ruta);
        if (!$tam || $tam[0] < 1 || $tam[1] < 1) {
            return '';
        }
        // Se ajusta a un recuadro de 500 x 640 puntos conservando la proporción
        $razon = $tam[0] / $tam[1];
        if ($razon > 500 / 640) {
            $w = 500.0;
            $h = 500.0 / $razon;
        } else {
            $h = 640.0;
            $w = 640.0 * $razon;
        }
        $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
        $datos = base64_encode((string) file_get_contents($ruta));

        return '<div style="page-break-before: always;">'
             . '<div style="font-size:11px;font-weight:bold;margin-bottom:6px;">ANEXO: RÉCIPE MÉDICO (prueba de la orden ' . PdfHtml::h($o['numero']) . ')</div>'
             . '<div style="font-size:9px;margin-bottom:8px;">N° de récipe: ' . PdfHtml::h($o['recipe_numero'] !== '' ? $o['recipe_numero'] : 'No indicado')
             . ' &nbsp;|&nbsp; Archivo: ' . PdfHtml::h($o['recipe_original']) . '</div>'
             . '<img src="data:' . $mime . ';base64,' . $datos . '" style="width:' . round($w) . 'pt;height:' . round($h) . 'pt;border:1px solid #999;">'
             . '</div>';
    }

    public function descargar(string $nombre_archivo): void
    {
        $o = $this->orden;
        $h = fn($v) => PdfHtml::h($v);

        $trab = $o['tipo'] === 'Trabajador' ? '[X]' : '[&nbsp;&nbsp;]';
        $afil = $o['tipo'] === 'Afiliado' ? '[X]' : '[&nbsp;&nbsp;]';

        $filas = '';
        foreach ($this->lineas as $i => $l) {
            $filas .= '<tr><td style="text-align:center">' . ($i + 1) . '</td><td>' . $h($l[0]) . '</td><td>' . $h($l[1]) . '</td><td style="text-align:right">' . $h($l[2]) . '</td></tr>';
        }

        if (($o['recipe_numero'] ?? '') !== '' || ($o['recipe_original'] ?? '') !== '') {
            $recipe = 'N° ' . ($o['recipe_numero'] !== '' ? $o['recipe_numero'] : 'no indicado') . ($o['recipe_original'] !== '' ? ' — archivo: ' . $o['recipe_original'] : ' — sin archivo adjunto');
        } else {
            $recipe = 'No indicado';
        }

        $css = '
            @page { margin: 34px 40px 50px 40px; }
            body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #111; }
            h1 { font-size: 17px; margin: 4px 0 6px 0; }
            .linea { border-top: 2px solid #111; margin-bottom: 10px; }
            table { border-collapse: collapse; width: 100%; }
            table.datos td { padding: 4px 8px; font-size: 10px; }
            table.datos td.et { width: 34%; font-weight: bold; font-size: 9px; }
            table.marco { border: 1px solid #111; }
            table.meds th { background: #e4eaf2; border: 1px solid #111; padding: 5px; font-size: 9px; text-align: left; }
            table.meds td { border: 1px solid #111; padding: 5px; font-size: 9px; vertical-align: top; }
            table.meds tr { page-break-inside: avoid; }
            p { margin: 6px 0; }
            table.firmas td.caja { border: 1px solid #111; height: 128px; vertical-align: top; padding: 8px; width: 48%; }
            table.firmas td.sep { width: 4%; border: 0; }
            .titulo-caja { font-weight: bold; font-size: 9px; }
            .firma { border-top: 1px solid #111; margin-top: 58px; padding-top: 2px; font-size: 8px; }
            .peq { font-size: 8px; margin-top: 5px; }
        ';

        $html = '<html><head><meta charset="UTF-8"><style>' . $css . '</style></head><body>'
            . '<table><tr><td style="font-weight:bold;font-size:9px;">SISTEMA DE INVENTARIO MÉDICO</td>'
            . '<td style="text-align:right;font-weight:bold;font-size:13px;">Nº ' . $h($o['numero']) . '</td></tr></table>'
            . '<h1>ORDEN DE ENTREGA DE MEDICAMENTOS</h1><div class="linea"></div>'
            . '<table class="datos marco">'
            . '<tr><td class="et">Fecha de salida:</td><td>' . $h($o['fecha']) . '</td></tr>'
            . '<tr><td class="et">Nombre:</td><td>' . $h($o['nombre']) . '</td></tr>'
            . '<tr><td class="et">Apellido:</td><td>' . $h($o['apellido']) . '</td></tr>'
            . '<tr><td class="et">' . $h($o['doc_etiqueta'] ?? 'Cédula de identidad') . ':</td><td>' . $h($o['cedula']) . '</td></tr>'
            . '<tr><td class="et">Teléfono:</td><td>' . $h(($o['telefono'] ?? '') !== '' ? $o['telefono'] : 'No indicado') . '</td></tr>'
            . '<tr><td class="et">Condición:</td><td>' . $trab . ' Trabajador &nbsp;&nbsp;&nbsp; ' . $afil . ' Afiliado</td></tr>'
            . '</table><br>'
            . '<table class="meds"><thead><tr><th style="width:7%">Nº</th><th style="width:55%">MEDICAMENTO</th><th style="width:22%">PRESENTACIÓN</th><th style="width:16%;text-align:right">CANTIDAD</th></tr></thead><tbody>'
            . $filas . '</tbody></table>'
            . '<p><b>Observaciones:</b> ' . $h($o['observacion'] !== '' ? $o['observacion'] : 'Ninguna') . '</p>'
            . '<p><b>Récipe médico:</b> ' . $h($recipe) . '</p>'
            . '<p class="peq">Declaro haber recibido los medicamentos descritos en esta orden, en las cantidades indicadas y en conformidad. '
            . 'Esta orden no es válida sin la firma de ambas partes.</p><br>'
            . '<table class="firmas"><tr>'
            . '<td class="caja"><div class="titulo-caja">ENTREGADO POR (RESPONSABLE)</div><div class="firma">Firma</div>'
            . '<div class="peq">Nombre: ' . $h($o['entregado_por']) . '</div><div class="peq">Cargo: ______________________ &nbsp; Sello:</div></td>'
            . '<td class="sep"></td>'
            . '<td class="caja"><div class="titulo-caja">RECIBIDO POR (BENEFICIARIO)</div><div class="firma">Firma</div>'
            . '<div class="peq">Nombre: ' . $h($o['nombre'] . ' ' . $o['apellido']) . '</div><div class="peq">Doc.: ' . $h($o['cedula']) . ' &nbsp; Fecha: ' . $h($o['fecha']) . '</div></td>'
            . '</tr></table>'
            . $this->anexo_recipe()
            . '</body></html>';

        PdfHtml::descargar($html, $nombre_archivo, 'portrait', 'Documento generado el ' . $this->generado . ' - Original: beneficiario / Copia: archivo');
    }
}
