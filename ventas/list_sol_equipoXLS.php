<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
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

$filename = "ReporteSolicitudes.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$writer = new XLSXWriter();

$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/list_sol_equipo.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$usuario = new Usuario();

$mostrar = 0;
if (isset($_GET['mostrar'])) {
    $mostrar = $_GET['mostrar'];
}

$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT c_puesto.IdPuesto FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario']);
$rs = mysql_fetch_array($query);

if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], "24") || $usuario->isUsuarioPuesto($_SESSION['idUsuario'], "27")) {/* Si es de almacen o gerente de almacen */
    if ($mostrar == 1) {
        $where = "WHERE (c_solicitud.estatus=1 OR c_solicitud.estatus=5) OR (id_crea = " . $_SESSION['idUsuario'] . ")";
    } else {
        $where = "WHERE (c_solicitud.estatus=1) OR (id_crea = " . $_SESSION['idUsuario'] . ")";
    }
} else {/* Si no es de almacen */
    if ($mostrar == 1) {
        $where = "";
    } else {
        $where = " WHERE (c_solicitud.estatus=0 OR c_solicitud.estatus=1 OR c_solicitud.estatus=2) ";
    }

    if ($rs['IdPuesto'] == 11) {
        if ($mostrar == 1) {
            $where = "WHERE c_solicitud.id_crea=" . $_SESSION['idUsuario'];
        } else {
            $where .= "  AND c_solicitud.id_crea=" . $_SESSION['idUsuario'];
        }
    }
}

$consulta = "SELECT
    c_solicitud.fecha_solicitud AS Fecha,
    c_cliente.NombreRazonSocial AS Cliente,
    c_tiposolicitud.Nombre AS TipoSolicitud,
    (CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno)) AS UsuarioAutorizo,
    (CONCAT(u2.Nombre,' ',u2.ApellidoPaterno,' ',u2.ApellidoMaterno)) AS UsuarioCreacion,
    (SELECT CASE WHEN !ISNULL(b2.NoParte) THEN 'Asignado' WHEN !ISNULL(e.Descripcion) THEN 'Asignado' ELSE 'No Asignado' END) AS Asignado,
    c_solicitud.id_tiposolicitud AS IdTipoSolicitud,
    (SELECT CASE WHEN !ISNULL(c_ventadirecta.IdVentaDirecta) THEN c_ventadirecta.IdVentaDirecta ELSE 'N/A' END) AS VentaDirecta,
    (SELECT CASE WHEN !ISNULL(c_ventadirecta.id_prefactura) THEN c_ventadirecta.id_prefactura ELSE '' END) AS Prefactura,
    (SELECT SUM(IF(ks2.tipo=0,IF(ISNULL(cv2.id_solicitud),ks2.cantidad, FORMAT(ks2.cantidad/2,0)),0)) FROM k_solicitud AS ks2 LEFT JOIN c_ventadirecta AS cv2 ON cv2.id_solicitud = ks2.id_solicitud WHERE ks2.id_solicitud = c_solicitud.id_solicitud) AS NumEquipos,
    (SELECT SUM(IF(ks3.tipo=1,IF(ISNULL(cv3.id_solicitud),ks3.cantidad, FORMAT(ks3.cantidad/2,0)),0)) FROM k_solicitud AS ks3 LEFT JOIN c_ventadirecta AS cv3 ON cv3.id_solicitud = ks3.id_solicitud WHERE ks3.id_solicitud = c_solicitud.id_solicitud) AS NumCompo,
    es.NombreEstatus  AS Status,
    c_solicitud.id_solicitud AS ID,
    c_solicitud.estatus AS idEstatus,
    (SELECT group_concat(Nombre separator ', ') FROM c_centrocosto AS cc, k_solicitud AS ks
    WHERE ks.id_solicitud = c_solicitud.id_solicitud AND cc.ClaveCentroCosto = ks.ClaveCentroCosto GROUP BY k_solicitud.id_solicitud) AS localidades,

    GROUP_CONCAT(DISTINCT(CONCAT(b2.NoSerie,' (',eq2.Modelo,')'))) AS Series,
    c_solicitud.comentario
    FROM c_solicitud
    INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
    INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
    INNER JOIN c_tiposolicitud ON c_tiposolicitud.IdTipoMovimiento = c_solicitud.id_tiposolicitud
    LEFT JOIN c_usuario AS u ON u.IdUsuario = c_solicitud.id_autoriza
    LEFT JOIN c_usuario AS u2 ON u2.Loggin = c_solicitud.UsuarioCreacion
    LEFT JOIN c_ventadirecta ON c_ventadirecta.id_solicitud = c_solicitud.id_solicitud
    LEFT JOIN c_estatussolicitud AS es ON es.IdEstatusSolicitud = c_solicitud.estatus

    LEFT JOIN c_componente AS e ON k_solicitud.id_solicitud = c_solicitud.id_solicitud AND k_solicitud.tipo = 1 AND e.NoParte = k_solicitud.Modelo
    LEFT JOIN k_solicitudbitacora AS ksb ON ksb.id_solicitud = c_solicitud.id_solicitud
    LEFT JOIN c_bitacora AS b2 ON b2.id_bitacora = ksb.id_bitacora
    LEFT JOIN c_equipo AS eq2 ON eq2.NoParte = b2.NoParte
    $where
    GROUP BY ID DESC;";
$query = $catalogo->obtenerLista($consulta);

$writer->setAuthor('Techra');

$cabeceras = array('' => 'string', '' => "string", '' => "string", '' => "string", '' => "string",
    'Reporte de Solicitudes' => "string", '' => "string", '' => "string", '' => "string",
    '' => "string", '' => "string", '' => "string", '' => "string");

$hoja = "Reporte";
$writer->writeSheetHeader($hoja, $cabeceras);

$vacia = array(' ', ' ', ' ', ' ', ' ',' ', ' ', ' ', ' ',' ', ' ', ' ', ' ');

$hoja = "Reporte";
$writer->writeSheetRow($hoja, $vacia);

$cabeceras2 = array('Numero de solicitud', 'Fecha', 'Cliente', 'Localidades', 'Número de equipos',
    'Número de componentes', 'Tipo', 'Venta directa', 'Status',
    'Usuario Creación', 'Asignado', 'Series', 'Comentarios');

$hoja = "Reporte";
$writer->writeSheetRow($hoja, $cabeceras2);


while ($rs = mysql_fetch_array($query)) {
    $array_valores = array();
    if ($rs['Status'] != "Surtida" || $rs['Status'] != "Cancelada") {           
            array_push($array_valores, $rs['ID']);
            array_push($array_valores, $rs['Fecha']);
            array_push($array_valores, $rs['Cliente']);
            array_push($array_valores, $rs['localidades']);
            array_push($array_valores, $rs['NumEquipos']);
            array_push($array_valores, $rs['NumCombo']);
            array_push($array_valores, $rs['TipoSolicitud']);
            array_push($array_valores, $rs['VentaDirecta']);
        if ($rs['idEstatus'] != "0") {
            array_push($array_valores, $rs['Status'] . "/" . $rs['UsuarioAutorizo']);
            
        } else {          
            array_push($array_valores, $rs['Status']);
        }
            array_push($array_valores, $rs['UsuarioCreacion']);
            array_push($array_valores, $rs['Asignado']);
            array_push($array_valores, $rs['Series']);
            array_push($array_valores, $rs['comentario']);
    }
    $writer->writeSheetRow($hoja, $array_valores);
}

$writer->writeToStdOut();
/*$writer->writeToFile('example.xlsx');
echo $writer->writeToString();*/
exit(0);
?>


