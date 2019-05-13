<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
header('Content-Type: text/html; charset=utf-8');
include_once("../WEB-INF/Classes/Catalogo.class.php");

$catalogo = new Catalogo();
$consulta = "SELECT 
srg.IdSolicitudRetiroGeneral, srg.FechaCreacion, sr.IdBitacora, b.NoSerie,
(CASE WHEN srg.Contestado = 1 THEN 'Autorizado' ELSE 'Falta por autorizar' END) AS Autorizado, 
(CASE WHEN srg.Aceptada = 1 THEN 'Aceptado' WHEN srg.Contestado = 1 THEN 'Rechazado' ELSE NULL END) AS Aceptado, 
srg.FechaAutorizacion,rh.NumReporte, 
(CASE WHEN rh.Retirado = 1 THEN 'Retirado' WHEN ISNULL(rh.Retirado) THEN NULL ELSE 'Falta por retirar' END) AS Retirado, 
(CASE WHEN meq.pendiente = 1 THEN 'Pendiente por recibir' WHEN ISNULL(meq.pendiente) THEN NULL ELSE 'Recibido' END) AS RecibidoAlmacen
FROM `c_solictudretirogeneral` AS srg
INNER JOIN c_solicitudretiro AS sr ON sr.IdSolicitudRetiroGeneral = srg.IdSolicitudRetiroGeneral
INNER JOIN c_bitacora AS b ON b.id_bitacora = sr.IdBitacora
LEFT JOIN movimientos_equipo AS meq ON meq.id_movimientos = 
(SELECT MAX(id_movimientos) FROM movimientos_equipo 
WHERE NoSerie = b.NoSerie AND DATE(srg.FechaAutorizacion) = DATE(FechaCreacion)
AND (srg.TipoReporte = meq.IdTipoMovimiento))
LEFT JOIN reportes_movimientos AS rm ON rm.id_movimientos = meq.id_movimientos
LEFT JOIN reportes_historicos AS rh ON rh.NumReporte = rm.id_reportes
WHERE (ISNULL(meq.id_movimientos) OR meq.pendiente = 1) AND (Aceptada <> 1 OR (Aceptada = 1 AND !ISNULL(meq.id_movimientos))) AND(srg.Contestado = 0 OR srg.Aceptada = 1)
ORDER BY Contestado,Aceptada,Retirado,NumReporte,RecibidoAlmacen,NoSerie;";

$result = $catalogo->obtenerLista($consulta);
?>
<html>
    <head>
        <title>Reporte retiros</title>
        <style>
            table, th, td {
                border: 1px solid black;
            }
        </style>
    </head>
    <body>
        <a href=javascript:window.print(); style="margin: 95%;">
            <img src="../resources/images/icono_impresora.jpg" title="Imprimir" style="width: 24px; height: 24px;"/>
        </a>
        <?php
        if (mysql_num_rows($result) > 0) {
            echo "<br/>";
            echo "<table style='width: 100%;'>";
            echo "<thead><tr>";
            echo "<th>#</th>";
            echo "<th>Fecha Solicitud</th>";
            echo "<th>Folio Solicitud</th>";
            echo "<th>Serie</th>";
            echo "<th>Autorizado</th>";
            echo "<th>Aceptado</th>";
            echo "<th>Fecha Autorización</th>";
            echo "<th>Reporte movimiento</th>";
            echo "<th>Retirado</th>";
            echo "<th>Recibido almacén</th>";
            echo "</tr></thead><body>";
            $i = 1;
            while ($rs = mysql_fetch_array($result)) {
                echo "<tr>";
                echo "<td>" . ($i++) . "</td>";
                echo "<td>" . $rs['FechaCreacion'] . "</td>";
                echo "<td>" . $rs['IdSolicitudRetiroGeneral'] . "</td>";
                echo "<td>" . $rs['NoSerie'] . "</td>";
                echo "<td>" . $rs['Autorizado'] . "</td>";
                echo "<td>" . $rs['Aceptado'] . "</td>";
                echo "<td>" . $rs['FechaAutorizacion'] . "</td>";
                echo "<td>" . $rs['NumReporte'] . "</td>";
                echo "<td>" . $rs['Retirado'] . "</td>";
                echo "<td>" . $rs['RecibidoAlmacen'] . "</td>";
                echo "</tr>";
            }
            echo "</body></table>";
        } else {
            echo "<br/>No hay resultados";
        }
        ?>
    </body>
</html>