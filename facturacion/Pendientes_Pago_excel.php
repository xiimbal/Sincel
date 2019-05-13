<?php
 header('Content-Type: text/html; charset=UTF-8');
  header('Content-type: application/x-msdownload; charset=UTF-8');
  header('Content-Disposition: attachment; filename=FacturasArrendamiento.xls');
  header('Pragma: no-cache');
  header('Expires: 0');

date_default_timezone_set('America/Mexico_City');
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo_fac = new CatalogoFacturacion();
$catalogo = new Catalogo();

$ejecutivo = "";
$fecha = "";
$periodos_evaluar = array();
$rfc_facturas = array();
$rfc_totales = array();

$campo_agrupar = "PeriodoFacturacion";
if(isset($_POST['fecha_facturacion']) && $_POST['fecha_facturacion'] == "1"){
    $campo_agrupar = "FechaFacturacion";
}

if (isset($_POST['ejecutivo']) && $_POST['ejecutivo'] != "") {
    $ejecutivo = " AND u.IdUsuario = " . $_POST['ejecutivo'] . " ";
}

if ((isset($_POST['fecha1']) && $_POST['fecha1'] != "") || (isset($_POST['fecha2']) && $_POST['fecha2'] != "")) {
    if ((isset($_POST['fecha1']) && $_POST['fecha1'] != "") && (isset($_POST['fecha2']) && $_POST['fecha2'] != "")) {
        $fecha = " AND (f.$campo_agrupar BETWEEN '" . $_POST['fecha1'] . " 00:00:00' AND '" . $_POST['fecha2'] . " 23:59:59') ";
        $mes_inicio = substr($_POST['fecha1'], 5, 2);
        $anio_inicio = substr($_POST['fecha1'], 0, 4);
        $mes_final = substr($_POST['fecha2'], 5, 2);
        $anio_final = substr($_POST['fecha2'], 0, 4);
    } else if (isset($_POST['fecha1']) && $_POST['fecha1'] != "") {
        $fecha = " AND f.$campo_agrupar >= '" . $_POST['fecha1'] . " 00:00:00' ";
        $mes_inicio = substr($_POST['fecha1'], 5, 2);
        $anio_inicio = substr($_POST['fecha1'], 0, 4);
        $mes_final = date('m');
        $anio_final = date('Y');
    } else if (isset($_POST['fecha2']) && $_POST['fecha2'] != "") {
        $fecha = " AND f.$campo_agrupar <= '" . $_POST['fecha2'] . " 23:59:59' ";
        $mes_inicio = 01;
        $anio_inicio = substr($_POST['fecha2'], 0, 4);
        $mes_final = substr($_POST['fecha2'], 5, 2);
        $anio_final = substr($_POST['fecha2'], 0, 4);
    }
} else {
    $mes_inicio = 01;
    $anio_inicio = date('Y');
    $mes_final = date('m');
    $anio_final = date('Y');
}
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>Pendientes de pago</title>   
        <style>
            table th {background-color: #5B9BD5; color:white; font-weight: bold; border: 1px solid white;}
            table td {background-color: #BDD7EE; border: 1px solid white;}
        </style>
    </head>
    <body>
        <?php
        $agrupar = false;
        if(isset($_POST['agrupar_grupo']) && $_POST['agrupar_grupo'] == "1"){
            $agrupar = true;
        }
        
        if (isset($_POST['reporte1'])) {
            /* Obtenemos las facturas de cada cliente */
            if(!$agrupar){
                $consulta = "SELECT
                    c.ClaveCliente, c.NombreRazonSocial,c.RFC,c.ClaveGrupo,
                    CONCAT(f.Serie,f.Folio) AS Folio, f.RFCEmisor,f.NombreEmisor, CONCAT(YEAR(f.$campo_agrupar),'-',MONTH(f.$campo_agrupar)) AS PeriodoFacturacion,
                    (CASE WHEN f.TipoComprobante = 'ingreso' THEN f.Total WHEN f.TipoComprobante = 'egreso' THEN f.Total * (-1) ELSE 0 END) AS Total, 
                    (SELECT CASE WHEN f.EstadoFactura = 0 THEN 'C' WHEN f.PendienteCancelar = 1 THEN 'Pendiente Cancelar' WHEN f.TipoComprobante <> 'ingreso' THEN 'NDC' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'INC' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFactura,
                    CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Ejecutivo
                    FROM c_factura AS f 
                    INNER JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MAX(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1 AND Modalidad = 1)
                    LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta
                    WHERE f.EstadoFactura = 1  AND (ISNULL(EstatusFactura) OR EstatusFactura!=3)
                    AND f.PendienteCancelar = 0 AND f.TipoArrendamiento = 1 $fecha $ejecutivo 
                    ORDER BY PeriodoFacturacion, Folio, NombreRazonSocial;";
            }else{
                $consulta = "SELECT
                    (CASE WHEN !ISNULL(c.ClaveGrupo) THEN CONCAT('cg_',c.ClaveGrupo) ELSE CONCAT('cc_',c.ClaveCliente) END) AS ClaveCliente, 
                    c.NombreRazonSocial,c.RFC,c.ClaveGrupo,
                    CONCAT(f.Serie,f.Folio) AS Folio, f.RFCEmisor,f.NombreEmisor, CONCAT(YEAR(f.$campo_agrupar),'-',MONTH(f.$campo_agrupar)) AS PeriodoFacturacion,
                    (CASE WHEN f.TipoComprobante = 'ingreso' THEN f.Total WHEN f.TipoComprobante = 'egreso' THEN f.Total * (-1) ELSE 0 END) AS Total, 
                    (SELECT CASE WHEN f.EstadoFactura = 0 THEN 'C' WHEN f.PendienteCancelar = 1 THEN 'Pendiente Cancelar' WHEN f.TipoComprobante <> 'ingreso' THEN 'NDC' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'INC' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFactura,
                    CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Ejecutivo
                    FROM c_factura AS f 
                    INNER JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MAX(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1 AND Modalidad = 1)
                    LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta
                    WHERE f.EstadoFactura = 1  AND (ISNULL(EstatusFactura) OR EstatusFactura!=3)
                    AND f.PendienteCancelar = 0 AND f.TipoArrendamiento = 1 AND f.FechaFacturacion >= '2015-01-01 00:00:00'
                    ORDER BY PeriodoFacturacion, Folio, NombreRazonSocial;";
            }
            $result = $catalogo_fac->obtenerLista($consulta);

            while ($rs = mysql_fetch_array($result)) {
                if (!isset($rfc_facturas[$rs['ClaveCliente']][$rs['PeriodoFacturacion']])) {
                    $rfc_facturas[$rs['ClaveCliente']][$rs['PeriodoFacturacion']] = $rs['Folio'] . ""; //Guardamos en el array el el folio de la factura                
                    $rfc_totales[$rs['ClaveCliente']][$rs['PeriodoFacturacion']] = (float) $rs['Total']; //Guardamos en el array el total de la factura                
                } else {
                    $rfc_facturas[$rs['ClaveCliente']][$rs['PeriodoFacturacion']] .= (", " . $rs['Folio']); //Concatenamos en el array el folio de la factura
                    $rfc_totales[$rs['ClaveCliente']][$rs['PeriodoFacturacion']] += (float) $rs['Total']; //Guardamos en el array el total de la factura                
                }
            }
            
            /* Obtenemos todos los clientes de arrendamiento */
            if(!$agrupar){
                $consulta2 = "SELECT c.ClaveCliente, c.NombreRazonSocial, c.RFC, c.ClaveGrupo, 
                    CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Ejecutivo, 
                    CONCAT(u2.Nombre,' ',u2.ApellidoPaterno,' ',u2.ApellidoMaterno) AS EjecutivoAtencionCliente, 
                    dfe.RFC AS RFCEmisor, DATE(ctt.FechaTermino) AS FechaTermino,
                    DATE(ctt.FechaInicio) AS FechaInicio
                    FROM c_cliente AS c 
                    LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta                
                    LEFT JOIN c_usuario AS u2 ON u2.IdUsuario = c.EjecutivoAtencionCliente
                    LEFT JOIN c_datosfacturacionempresa AS dfe ON dfe.IdDatosFacturacionEmpresa = c.IdDatosFacturacionEmpresa
                    LEFT JOIN c_contrato AS ctt ON ctt.NoContrato = (SELECT MAX(NoContrato) FROM c_contrato WHERE ClaveCliente = c.ClaveCliente ORDER BY FechaTermino DESC LIMIT 0,1)
                    WHERE c.Activo = 1 AND c.Modalidad = 1
                    GROUP BY c.ClaveCliente
                    ORDER BY Ejecutivo, c.NombreRazonSocial;";
            }else{
                $consulta2 = "SELECT 
                    (CASE WHEN !ISNULL(c.ClaveGrupo) THEN CONCAT('cg_',c.ClaveGrupo) ELSE CONCAT('cc_',c.ClaveCliente) END) AS ClaveCliente, 
                    (CASE WHEN !ISNULL(c.ClaveGrupo) THEN g.Nombre ELSE c.NombreRazonSocial END) AS NombreRazonSocial, 
                    c.RFC, c.ClaveGrupo, 
                    CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Ejecutivo, 
                    CONCAT(u2.Nombre,' ',u2.ApellidoPaterno,' ',u2.ApellidoMaterno) AS EjecutivoAtencionCliente, 
                    dfe.RFC AS RFCEmisor, DATE(ctt.FechaTermino) AS FechaTermino,
                    DATE(ctt.FechaInicio) AS FechaInicio
                    FROM c_cliente AS c 
                    LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta                
                    LEFT JOIN c_usuario AS u2 ON u2.IdUsuario = c.EjecutivoAtencionCliente
                    LEFT JOIN c_datosfacturacionempresa AS dfe ON dfe.IdDatosFacturacionEmpresa = c.IdDatosFacturacionEmpresa
                    LEFT JOIN c_contrato AS ctt ON ctt.NoContrato = (SELECT MAX(NoContrato) FROM c_contrato WHERE ClaveCliente = c.ClaveCliente ORDER BY FechaTermino DESC LIMIT 0,1)
                    LEFT JOIN c_clientegrupo AS g ON g.ClaveGrupo = c.ClaveGrupo
                    WHERE c.Activo = 1 AND c.Modalidad = 1
                    GROUP BY ClaveCliente
                    ORDER BY Ejecutivo, c.NombreRazonSocial;";
            }
            $result2 = $catalogo->obtenerLista($consulta2);

            echo "<br/><table><thead><tr>";
            echo "<th>Ejecutivo</th><th>Ejecutivo Atención Cliente</th><th>NombreRazonSocial</th><th>ClaveCliente</th><th>RFC</th><th>RFC Emisor</th>"
            . "<th>Inicio Contrato</th><th>Termino Contrato</th>";
            for ($i = 0; $i <= 1; $i++) {//Imprimimos dos veces las cabeceras de los meses, una para escribir los folios y otra para escribir los totales
                $mes_actual = (int) $mes_inicio;
                $anio_actual = (int) $anio_inicio;
                while ($mes_actual != $mes_final || $anio_actual != $anio_final) {
                    echo "<th>" . substr($catalogo->formatoFechaReportes($anio_actual . "-" . $mes_actual . "-01"), 6) . "</th>";
                    if ($i == 0) {
                        array_push($periodos_evaluar, $anio_actual . "-" . $mes_actual);
                    }
                    if ($mes_actual != $mes_final || $anio_actual != $anio_final) {
                        if ($mes_actual < 12) {
                            $mes_actual++;
                        } else {
                            $mes_actual = 1;
                            $anio_actual++;
                        }
                    }
                }
                echo "<th>" . substr($catalogo->formatoFechaReportes($anio_actual . "-" . $mes_actual . "-01"), 6) . "</th>";
                if ($i == 0) {
                    array_push($periodos_evaluar, $anio_actual . "-" . $mes_actual);
                }
            }
            echo "</tr></thead><tbody>";
            //print_r($rfc_facturas);
            while ($rs = mysql_fetch_array($result2)) {//Recorremos todos los clientes.
                
                echo "<tr>";
                echo "<td>" . $rs['Ejecutivo'] . "</td>";
                echo "<td>" . $rs['EjecutivoAtencionCliente'] . "</td>";
                echo "<td>" . $rs['NombreRazonSocial'] . "</td>";
                echo "<td>" . $rs['ClaveCliente'] . "</td>";
                echo "<td>" . $rs['RFC'] . "</td>";
                echo "<td>" . $rs['RFCEmisor'] . "</td>";
                echo "<td>" . $rs['FechaInicio'] . "</td>";
                echo "<td>" . $rs['FechaTermino'] . "</td>";

                foreach ($periodos_evaluar as $value) {//Escribirmos los totales de las facturas
                    if (isset($rfc_totales[$rs['ClaveCliente']][$value])) {
                        echo "<td style='text-align: right;'>" . $rfc_totales[$rs['ClaveCliente']][$value] . "</td>";
                    } else {
                        echo "<td style='text-align: right;'>0</td>";
                    }
                }

                foreach ($periodos_evaluar as $value) {//Escribirmos los folios de las facturas
                    if (isset($rfc_facturas[$rs['ClaveCliente']][$value])) {
                        echo "<td style='text-align: right;'>" . $rfc_facturas[$rs['ClaveCliente']][$value] . "</td>";
                    } else {
                        echo "<td style='text-align: right;'>No</td>";
                    }
                }

                echo "</tr>";
            }
            echo "</tbody></table>";
        }
        ?>
    </body>
</html>