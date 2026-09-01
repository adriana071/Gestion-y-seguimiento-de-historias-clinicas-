<?php
date_default_timezone_set('America/La_Paz');
require_once __DIR__ . '/../../vendor/autoload.php'; // ventas/view/reporte -> ventas/vendor
use Mpdf\Mpdf;

// 2) Conexión PDO
$conex = new PDO("mysql:host=localhost;dbname=db_hospital;","root","");
$conex->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 4) Consulta
$stmt =$conex->prepare("SELECT
    consulta.motivo,
    consulta.diagnostico,
    consulta.estado_consulta,
    consulta.fecha_registro,
    CONCAT_WS(' ', paciente.nombre, paciente.appaterno, paciente.apmaterno) AS nom_paciente,
    CONCAT_WS(' ', doctor.nombre, doctor.appaterno, doctor.apmaterno) AS nom_doctor
FROM consulta
INNER JOIN paciente ON paciente.ci_paciente = consulta.ci_paciente
INNER JOIN doctor ON doctor.ci_doctor = consulta.ci_doctor
WHERE consulta.estado = 1
  AND consulta.fecha_registro BETWEEN '2025-02-01' AND '2025-02-03'
ORDER BY consulta.fecha_registro DESC, consulta.id_consulta DESC
");
$stmt->execute();
$rows = $stmt->fetchAll();

$logo_path = __DIR__ . '/../img/hospital.jpg';                  //ventas/view/img/logo.png
$logoHtml = '<img src="' . $logo_path . '" alt="logo" style="height:60px;">';

$html = '
  <!DOCTYPE html>
  <html>
  <head>
      <link rel="stylesheet" type="text/css" href="rep_style.css">
  </head>
  <body>
      <div class="container">
        <table class="header-table">
          <tr>
             <td class="header-logo">'.$logoHtml.'<br></td>
            <td>
              <h1>Listado de Consultas</h1>
              <div class="small">Registros completos</div>
            </td>
          </tr>
        </table>
        <table class="table"><thead>
        <tr>
            <th style="width:170px">Motivo</th>
            <th style="width:90px">Diagnostico</th>
            <th style="width:170px"> Paciente</th>
            <th style="width:170px"> Doctor</th>
            <th style="width:170px">Fecha registro</th>
            
        </tr></thead>
        <tbody>
';
if ($rows) {
    foreach ($rows as $r) {
        $html .= '<tr>'
               .  '<td>' . htmlspecialchars($r['motivo']) . '</td>'
               .  '<td>' . htmlspecialchars($r['diagnostico']) . '</td>'
               .  '<td>' . htmlspecialchars($r['nom_paciente']) . '</td>'
               .  '<td>' . htmlspecialchars($r['nom_doctor']) . '</td>'
               .  '<td>' . htmlspecialchars($r['fecha_registro']) . '</td>'
               .  '</tr>';
    }
} else {
    $html .= '<tr><td colspan="4" class="small">No hay consultas para mostrar.</td></tr>';
}
$html .= '</tbody></table></div></body></html>';

$stylesheet = file_get_contents('rep_style.css');

$mpdf = new Mpdf(['mode'=>'utf-8', 'format'=>'Letter', 'margin_top'=>10, 'margin_bottom'=>15]);
$mpdf->SetTitle('Reporte - consulta');
$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->SetHTMLFooter('<div class="small">Generado el '.date('d/m/Y H:i').' · Página {PAGENO}/{nbpg}</div>');
$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
$mpdf->Output('consulta.pdf', 'I'); 
