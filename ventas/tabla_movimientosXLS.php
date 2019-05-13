<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
ini_set("memory_limit","1024M");
set_time_limit(0);

include_once("../WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Configuracion.class.php");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "ReporteMovimientos.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$writer = new XLSXWriter();

$catalogo = new Catalogo();
$permiso = new PermisosSubMenu();
$parametros = new Parametros();
$parametros->getRegistroById(8);
$where = "";
$where_pendiente = "WHERE srg.Contestado = 0";
$mostrar_pendientes_retirar = false;

$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/lista_movimientos.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

if(isset($_POST['tipo']) && ($_POST['tipo'] == "" || $_POST['tipo']=="6")){
    $mostrar_pendientes_retirar = true;
}

if (isset($_POST['noserie']) && $_POST['noserie'] != "") {    
    $configuracion = new Configuracion();
    if($configuracion->getRegistroByNoSerie($_POST['noserie'])){
        $mostrar_pendientes_retirar = true;
        if ($where_pendiente == "") {
            $where_pendiente .= " WHERE sr.IdBitacora = '" . $configuracion->getId_bitacora() . "' ";
        } else {
            $where_pendiente .= " AND sr.IdBitacora = '" . $configuracion->getId_bitacora() . "' ";
        }
    }
    if ($where == "") {
        $where.=" WHERE me.NoSerie='" . $_POST['noserie'] . "' ";
    } else {
        $where.=" AND me.NoSerie='" . $_POST['noserie'] . "' ";
    }
}
if (isset($_POST['tipo']) && $_POST['tipo'] != "") {
    if ($where == "") {
        $where.=" WHERE me.IdTipoMovimiento='" . $_POST['tipo'] . "' ";
    } else {
        $where.=" AND me.IdTipoMovimiento='" . $_POST['tipo'] . "' ";
    }   
    if($parametrosExcel == ""){
        $parametrosExcel.="?tipo=". $_POST['tipo'];
    }else{
        $parametrosExcel.="&tipo=". $_POST['tipo'];
    }
}

if (isset($_POST['NoRep']) && $_POST['NoRep'] != "") {
    $mostrar_pendientes_retirar = false;
    if ($where == "") {
        $where.=" WHERE rh.NumReporte='" . $_POST['NoRep'] . "' ";
    } else {
        $where.=" AND rh.NumReporte='" . $_POST['NoRep'] . "' ";
    }
}

if (isset($_POST['cliente']) && $_POST['cliente'] != "") {
    $mostrar_pendientes_retirar = true;
    if ($where == "") {
        $where .= " WHERE (me.clave_cliente_anterior='" . $_POST['cliente'] . "' OR me.clave_cliente_nuevo='" . $_POST['cliente'] . "') ";
    } else {
        $where .= " AND (me.clave_cliente_anterior='" . $_POST['cliente'] . "' OR me.clave_cliente_nuevo='" . $_POST['cliente'] . "') ";
    }
    
    if ($where_pendiente == "") {
        $where_pendiente .= " WHERE c.ClaveCliente = '" . $_POST['cliente'] . "' ";
    } else {
        $where_pendiente .= " AND c.ClaveCliente = '" . $_POST['cliente'] . "' ";
    }
}

if (isset($_POST['localidad']) && $_POST['localidad'] != "") {
    $mostrar_pendientes_retirar = true;
    if ($where == "") {
        $where .= " WHERE (me.clave_centro_costo_anterior='" . $_POST['localidad'] . "' OR me.clave_centro_costo_nuevo='" . $_POST['localidad'] . "') ";
    } else {
        $where .= " AND (me.clave_centro_costo_anterior='" . $_POST['localidad'] . "' OR me.clave_centro_costo_nuevo='" . $_POST['localidad'] . "') ";
    }
    
    if ($where_pendiente == "") {
        $where_pendiente .= " WHERE cc.ClaveCentroCosto = '" . $_POST['localidad'] . "' ";
    } else {
        $where_pendiente .= " AND cc.ClaveCentroCosto = '" . $_POST['localidad'] . "' ";
    }
}

if (isset($_POST['retirado']) && $_POST['retirado'] == "0") {
    //$mostrar_pendientes_retirar = false;
    if ($where == "") {
        $where.= " WHERE rh.Retirado='0' ";
    } else {
        $where.= " AND rh.Retirado='0' ";
    }
}

if (isset($_POST['fecha1']) && $_POST['fecha1'] != "" && isset($_POST['fecha2']) && $_POST['fecha2'] != "") {
    $mostrar_pendientes_retirar = true;
    if ($where == "") {
        $where.=" WHERE me.Fecha BETWEEN '" . $_POST['fecha1'] . " 00:00:00' AND '" . $_POST['fecha2'] . " 23:59:59' ";
    } else {
        $where.=" AND me.Fecha BETWEEN '" . $_POST['fecha1'] . "  00:00:00' AND '" . $_POST['fecha2'] . " 23:59:59' ";
    }
    
    if ($where_pendiente == "") {
        $where_pendiente.=" WHERE srg.FechaCreacion BETWEEN '" . $_POST['fecha1'] . "  00:00:00' AND '" . $_POST['fecha2'] . " 23:59:59' ";
    } else {
        $where_pendiente.=" AND srg.FechaCreacion BETWEEN '" . $_POST['fecha1'] . "  00:00:00' AND '" . $_POST['fecha2'] . " 23:59:59' ";
    }
}

$query = $catalogo->obtenerLista("SELECT 
IF(ISNULL(me.clave_cliente_anterior),CONCAT('Almacén: ',(SELECT nombre_almacen FROM c_almacen WHERE id_almacen=me.almacen_anterior)),CONCAT('Cliente: ',(SELECT NombreRazonSocial FROM c_cliente WHERE ClaveCliente=me.clave_cliente_anterior),' - Localidad: ',(SELECT Nombre FROM c_centrocosto WHERE ClaveCentroCosto=me.clave_centro_costo_anterior))) AS Origen,
IF(ISNULL(me.clave_cliente_nuevo),CONCAT('Almacén: ',(SELECT nombre_almacen FROM c_almacen WHERE id_almacen=me.almacen_nuevo)),CONCAT('Cliente: ',(SELECT NombreRazonSocial FROM c_cliente WHERE ClaveCliente=me.clave_cliente_nuevo),' Localidad: ',(SELECT Nombre FROM c_centrocosto WHERE ClaveCentroCosto=me.clave_centro_costo_nuevo))) AS Destino,
GROUP_CONCAT(me.NoSerie,' (',e.Modelo,')') AS Equipos,
me.tipo_movimiento AS Tipo,
me.causa_movimiento AS Causa,
me.IdTipoMovimiento AS IdTipoMovimiento,
me.UsuarioCreacion AS usuario,
tm.Nombre AS TipoMovNombre,
rh.NumReporte AS NumReporte,
rh.Retirado AS Retirado,
me.Fecha AS FechaMovimiento
FROM
reportes_historicos AS rh
INNER JOIN reportes_movimientos AS rm ON rm.id_reportes=rh.NumReporte
INNER JOIN movimientos_equipo AS me ON me.id_movimientos=rm.id_movimientos
LEFT JOIN c_tipomovimiento AS tm ON tm.IdTipoMovimiento=me.IdTipoMovimiento
LEFT JOIN c_bitacora AS b ON b.NoSerie=me.NoSerie
LEFT JOIN c_equipo AS e ON e.NoParte=b.NoParte
$where
GROUP BY rh.NumReporte ORDER BY me.Fecha DESC");

$writer->setAuthor('Techra');

$cabeceras = array('' => 'string', '' => "string", '' => "string", '' => "string", 'Reporte de Movimientos' => "string",
    ' ' => "string", '' => "string", '' => "string", '' => "string");

$hoja = "Reporte";
$writer->writeSheetHeader($hoja, $cabeceras);

$vacia = array(' ', ' ', ' ', ' ', ' ',' ', ' ', ' ', ' ',' ', ' ');

$hoja = "Reporte";
$writer->writeSheetRow($hoja, $vacia);

$cabeceras2 = array("Fecha","Reporte", "Equipo","Tipo", "Origen", "Destino","Causa","Usuario","Retirados");

$hoja = "Reporte";
$writer->writeSheetRow($hoja, $cabeceras2);

while ($rs = mysql_fetch_array($query)){
    $array_valores = array();
    array_push($array_valores, $rs['FechaMovimiento']);
    array_push($array_valores, $rs['NumReporte']);
    array_push($array_valores, $rs['Equipos']);
    array_push($array_valores, $rs['TipoMovNombre']);
    array_push($array_valores, $rs['Origen']);
    array_push($array_valores, $rs['Destino']);
    array_push($array_valores, $rs['Causa']);
    array_push($array_valores, $rs['usuario']);
    if($rs['Retirado']==1){
        array_push($array_valores,"Retirado");
    }
    array_push($array_valores, "");
    $writer->writeSheetRow($hoja, $array_valores);
}

if($mostrar_pendientes_retirar){
    $query = $catalogo->obtenerLista("SELECT 'Pendiente' AS NumReporte, GROUP_CONCAT(b.NoSerie,' (',e.Modelo,')') AS Equipos, 
        CONCAT('Retiro pendiente de autorizar: ',srg.IdSolicitudRetiroGeneral) AS TipoMovNombre, DATE(srg.FechaReporte) AS FechaMovimiento, 
        CONCAT('Cliente: ', GROUP_CONCAT(c.NombreRazonSocial SEPARATOR ' '),' - Localidad: ',GROUP_CONCAT(cc.Nombre SEPARATOR ' ')) AS Origen,
        CONCAT('Almacén: ',a.nombre_almacen) AS Destino, srg.Causa_Movimiento AS Causa, srg.UsuarioCreacion AS usuario, srg.Clave, srg.IdSolicitudRetiroGeneral
        FROM `c_solictudretirogeneral` AS srg
        LEFT JOIN c_solicitudretiro AS sr ON sr.IdSolicitudRetiroGeneral = srg.IdSolicitudRetiroGeneral
        LEFT JOIN c_bitacora AS b ON b.id_bitacora = sr.IdBitacora
        LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = sr.ClaveLocalidad
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN c_almacen AS a ON a.id_almacen = sr.IdAlmacen
        $where_pendiente GROUP BY srg.IdSolicitudRetiroGeneral;");            
    while($rs = mysql_fetch_array($query)){
        $array_valores = array();
        array_push($array_valores, $rs['FechaMovimiento']);
        array_push($array_valores, $rs['NumReporte']);
        array_push($array_valores, $rs['Equipos']);
        //echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Modelo'] . "</td>";
        array_push($array_valores, $rs['TipoMovNombre']);                
        array_push($array_valores, $rs['Origen']);
        array_push($array_valores, $rs['Destino']);
        array_push($array_valores, $rs['Causa']);
        array_push($array_valores, $rs['usuario']);
        array_push($array_valores, "");
        $writer->writeSheetRow($hoja, $array_valores);
    }
}

$writer->writeToStdOut();
/*$writer->writeToFile('example.xlsx');
echo $writer->writeToString();*/
exit(0);
?>

