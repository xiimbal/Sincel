<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

ini_set("memory_limit", "1024M");
set_time_limit(0);
header('Content-Type: text/html; charset=utf-8');

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "EquiposSinLectura.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');

$catalogo = new Catalogo();
$writer = new XLSXWriter();
$writer->setAuthor('Techra');
/* Ponemos las cabeceras */
$cabeceras = array("EjecutivoCuenta" => "string", "EjecutivoAtencionCliente" => "string", "NombreCliente" => "string", 
    "Localidad" => "string", "NoSerie" => "string", "Modelo" => "string","Ubicacion" => "string");

$hoja = "Reporte";
$writer->writeSheetHeader($hoja, $cabeceras);

$where = "";
$having = "";

if(isset($_POST['fecha']) && $_POST['fecha']!=""){                            
    $month = substr($_POST['fecha'], 0, 2);
    $year = substr($_POST['fecha'], 3, 4);                               
}else{
    $month = date('m');
    $year = date('Y');                            
}

if(isset($_POST['vendedor']) && !empty($_POST['vendedor'])){
    $where.= "u.IdUsuario = ".$_POST['vendedor'];
}

if(isset($_POST['cliente']) && !empty($_POST['cliente'])){
    $where.= "c.ClaveCliente = '".$_POST['cliente']."'";
}

if(isset($_POST['contrato']) && !empty($_POST['contrato'])){
    $where.= "ctt.NoContrato = '".$_POST['contrato']."'";
}

if(isset($_POST['anexo']) && !empty($_POST['anexo'])){
    $where.= "cat.ClaveAnexoTecnico = '".$_POST['anexo']."'";
}

if(isset($_POST['zona']) && !empty($_POST['zona'])){
    if ($having == "") {
        $having = " HAVING ClaveZona = '".$_POST['zona']."' ";
    } else {
        $having .= " AND ClaveZona = '".$_POST['zona']."' ";
    }
}

if(isset($_POST['centro_costo']) && !empty($_POST['centro_costo'])){
    if ($having == "") {
        $having = " HAVING idCen_Costo = '".$_POST['centro_costo']."' ";
    } else {
        $having .= " AND idCen_Costo = '".$_POST['centro_costo']."' ";
    }
}

if(isset($_POST['localidad']) && !empty($_POST['localidad'])){
    if ($having == "") {
        $having = " HAVING ClaveCentroCosto = '".$_POST['localidad']."' ";
    } else {
        $having .= " AND ClaveCentroCosto = '".$_POST['localidad']."' ";
    }
}

$consulta = "SELECT 
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN c.ClaveCliente ELSE (SELECT ClaveCliente FROM c_cliente WHERE c_cliente.ClaveCliente = cc2.ClaveCliente) END) AS ClaveCliente, 	
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN c.NombreRazonSocial ELSE (SELECT NombreRazonSocial FROM c_cliente WHERE c_cliente.ClaveCliente = cc2.ClaveCliente) END) AS NombreCliente, 	
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.Nombre ELSE cc2.Nombre END) AS Localidad, 
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.ClaveCentroCosto ELSE ks.ClaveCentroCosto END) AS ClaveCentroCosto,
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.ClaveZona ELSE cc2.ClaveZona END) AS ClaveZona,
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.ClaveZona ELSE cc2.ClaveZona END) AS ClaveZona,
    (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN ccc1.id_cc ELSE ccc2.id_cc END) AS idCen_Costo,
    cinv.NoSerie AS NoSerie, cinv.NoParteEquipo, 
    l.IdLectura,
    (CONCAT(u2.Nombre,' ',u2.ApellidoPaterno,' ',u2.ApellidoMaterno)) AS EjecutivoAtencionCliente,
    (CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno)) AS EjecutivoCuenta,
    cinv.Ubicacion,
    ctt.NoContrato,
    c_equipo.Modelo AS Modelo,
    c.RFC
    FROM `c_inventarioequipo` AS cinv
    LEFT JOIN c_bitacora AS b ON b.NoSerie = cinv.NoSerie
    LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
    RIGHT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = cinv.IdAnexoClienteCC
    RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC
    LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
    LEFT JOIN c_cen_costo AS ccc1 ON cc.id_cr = ccc1.id_cc
    LEFT JOIN c_cen_costo AS ccc2 ON ks.ClaveCentroCosto = ccc2.id_cc
    LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
    LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
    LEFT JOIN c_usuario AS u2 ON u2.IdUsuario = c.EjecutivoAtencionCliente
    LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
    LEFT JOIN k_equipocaracteristicaformatoservicio AS kecs ON kecs.Id = (SELECT MAX(ID) FROM k_equipocaracteristicaformatoservicio WHERE NoParte = c_equipo.NoParte AND IdTipoServicio = 1)
    LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = kacc.ClaveAnexoTecnico
    LEFT JOIN c_contrato AS ctt ON ctt.NoContrato = cat.NoContrato
    LEFT JOIN c_lectura AS l ON l.NoSerie = cinv.NoSerie AND l.LecturaCorte = 1 AND DATE(l.Fecha) = '$year-$month-01'
    WHERE !ISNULL(cinv.NoSerie) AND b.VentaDirecta = 0 AND ISNULL(l.IdLectura) $where 
    $having
    ORDER BY EjecutivoCuenta,NombreCliente,NoSerie;";

$result = $catalogo->obtenerLista($consulta);
while($rs = mysql_fetch_array($result)){
    $array_valores = array();
    foreach ($cabeceras as $key => $value) {
        array_push($array_valores, $rs[$key]);
    }
    $writer->writeSheetRow($hoja, $array_valores);
}

$writer->writeToStdOut();
exit(0);