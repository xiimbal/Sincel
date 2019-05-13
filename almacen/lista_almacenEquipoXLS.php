<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
ini_set("memory_limit","1024M");
set_time_limit(0);

include_once("../WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

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

$parametrosExcel = "";
$permisos_grid = new PermisosSubMenu();
$same_page = "almacen/lista_almacenEquipo.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$columnas = array("nombre_almacen", "NoSerie", "Modelo", "tipoInventario" , "Ubicacion" ,"PendienteRetiro");
$alta = "almacen/alta_almacenEquipo.php";
$editar = "almacen/configuracion.php?regresar=$same_page";

$catalogo = new Catalogo();

$idAlmacenes = array();
$consultaAlmacen = "SELECT * FROM k_responsablealmacen ra,c_almacen a ,c_usuario us "
    . " WHERE ra.IdUsuario='" . $_SESSION['idUsuario'] . "' AND a.Activo=1 AND ra.IdAlmacen=a.id_almacen AND ra.IdUsuario=us.IdUsuario 
        ORDER BY a.nombre_almacen ASC";
$queryAlmacen = $catalogo->obtenerLista($consultaAlmacen);
if(mysql_num_rows($queryAlmacen) == 0){
    $consultaAlmacen = "SELECT * FROM c_almacen a WHERE a.Activo=1 AND a.TipoAlmacen = 1 ORDER BY a.nombre_almacen ASC";
    $queryAlmacen = $catalogo->obtenerLista($consultaAlmacen);
    while ($rs = mysql_fetch_array($queryAlmacen)) {
        $idAlmacenes[$rs['id_almacen']] = $rs['nombre_almacen'];
    }
    $id_almacenes = "";
}else{
    while ($rs = mysql_fetch_array($queryAlmacen)) {
        $idAlmacenes[$rs['id_almacen']] = $rs['nombre_almacen'];        
    }
    $id_almacenes = implode(",", array_keys($idAlmacenes));//Id de los almacenes a los que tiene permiso el usuario actual
}

if(isset($_GET['almacenes']) && $_GET['almacenes']!=""){
    $filtro_responsable_almacen = " AND a.id_almacen IN(".$_GET['almacenes'].") ";
}

$modelo = "";
$filtro_modelo = "";
if(isset($_GET['modelo']) && $_GET['modelo']!=""){
    $modelo = $_GET['modelo'];
    $filtro_modelo = " AND e.Modelo LIKE '%$modelo%' ";
}

$serie = "";
$filtro_serie = "";
if(isset($_GET['serie']) && $_GET['serie']!=""){
    $serie = $_GET['serie'];
    $filtro_serie = " AND kae.NoSerie LIKE '%$serie%' ";
}

if ($id_almacenes != "") {
    $consulta = "SELECT kae.NoSerie, a.nombre_almacen, e.Modelo, b.id_bitacora, ti.Nombre AS tipoInventario, kae.Ubicacion,
    IF(ISNULL(csrg.IdSolicitudRetiroGeneral),'No',IF(csr.PendienteRetiro=0 AND csrg.Contestado=0,'Si',IF(csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1,'Si',IF(csrg.Aceptada=1 AND csrg.Contestado=1 AND csr.IdAlmacen!=9 AND a.id_almacen=9,'Si','No')))) AS PendienteRetiro
    FROM k_almacenequipo AS kae
    LEFT JOIN c_almacen AS a ON a.id_almacen = kae.id_almacen
    LEFT JOIN c_equipo AS e ON e.NoParte = kae.NoParte
    LEFT JOIN c_bitacora AS b ON b.NoSerie = kae.NoSerie
    LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora = b.id_bitacora)
    LEFT JOIN c_solictudretirogeneral AS csrg ON csrg.IdSolicitudRetiroGeneral=csr.IdSolicitudRetiroGeneral
    LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = b.IdTipoInventario
    INNER JOIN k_responsablealmacen AS kra ON kra.IdUsuario = ".$_SESSION['idUsuario']." AND kra.IdAlmacen = a.id_almacen
    WHERE a.Activo = 1 AND e.Activo = 1 AND b.Activo = 1 $filtro_responsable_almacen $filtro_modelo $filtro_serie 
    ORDER BY kae.NoSerie;";
} else {
    $consulta = "SELECT kae.NoSerie, a.nombre_almacen, e.Modelo, b.id_bitacora, ti.Nombre AS tipoInventario, kae.Ubicacion,
    IF(ISNULL(csrg.IdSolicitudRetiroGeneral),'No',IF(csr.PendienteRetiro=0 AND csrg.Contestado=0,'Si',IF(csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1,'Si',IF(csrg.Aceptada=1 AND csrg.Contestado=1 AND csr.IdAlmacen!=9 AND a.id_almacen=9,'Si','No')))) AS PendienteRetiro
    FROM k_almacenequipo AS kae
    INNER JOIN c_almacen AS a ON a.id_almacen = kae.id_almacen
    INNER JOIN c_equipo AS e ON e.NoParte = kae.NoParte
    LEFT JOIN c_bitacora AS b ON b.NoSerie = kae.NoSerie
    LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora = b.id_bitacora)
    LEFT JOIN c_solictudretirogeneral AS csrg ON csrg.IdSolicitudRetiroGeneral=csr.IdSolicitudRetiroGeneral
    LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = b.IdTipoInventario
    WHERE a.Activo = 1 AND e.Activo = 1 AND b.Activo = 1 $filtro_responsable_almacen $filtro_modelo $filtro_serie 
    ORDER BY kae.NoSerie;";
}

$writer->setAuthor('Techra');

$cabeceras = array('' => 'string', '' => "string", 'Reporte de Inventario de Equipos' => "string", '' => "string", '' => "string",' ' => "string");

$hoja = "Reporte";
$writer->writeSheetHeader($hoja, $cabeceras);

$vacia = array(' ', ' ', ' ', ' ', ' ',' ', ' ', ' ', ' ',' ', ' ');

$hoja = "Reporte";
$writer->writeSheetRow($hoja, $vacia);

$cabeceras2 = array("Almacen", "No serie", "Equipo", "Tipo Inventario" ,"Ubicacion","Pendiente Retiro");

$hoja = "Reporte";
$writer->writeSheetRow($hoja, $cabeceras2);

/* Inicializamos la clase */
$catalogo = new Catalogo();
$query = $catalogo->obtenerLista($consulta);
while ($rs = mysql_fetch_array($query)) {
    $array_valores = array();
    for ($i = 0; $i < count($columnas); $i++) {
        if(!isset($rs[$columnas[$i]]) || $rs[$columnas[$i]] == ""){
            array_push($array_valores," ");
        }else{
            array_push($array_valores,$rs[$columnas[$i]]);
        }
    }
    $writer->writeSheetRow($hoja, $array_valores);
}

$writer->writeToStdOut();
/*$writer->writeToFile('example.xlsx');
echo $writer->writeToString();*/
exit(0);
?>