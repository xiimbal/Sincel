<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
ini_set("memory_limit","256M");
set_time_limit (0);

include_once("../WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

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
$writer->setAuthor('Techra');

$cabeceras = array(' ' => 'string', ' ' => "string", ' Reporte de campaña' => "string", '' => "string");

$hoja = "Reporte";
$writer->writeSheetHeader($hoja, $cabeceras);

$array_valores_e = array();
array_push($array_valores_e, "");
array_push($array_valores_e, "");
array_push($array_valores_e, "");
array_push($array_valores_e, "");
$writer->writeSheetRow($hoja, $array_valores_e);  

$array_valores_i = array();
array_push($array_valores_i, "Campaña");
array_push($array_valores_i, "Movimiento");
array_push($array_valores_i, "Nombre");
array_push($array_valores_i, "Duración (días hábiles)");
$writer->writeSheetRow($hoja, $array_valores_i); 

$catalogo = new Catalogo();
$where = "";

if (isset($_GET['Campana']) && $_GET['Campana'] != "") {
    $campania = explode(",", $_GET['Campana']);
    foreach ($campania as $value) {
        if($where == ""){
            $where = "WHERE (a.IdArea = ".$value;
        }else{
            $where.= " OR a.IdArea = ".$value;
        }
    }
    $where .= ")";
}
if (isset($_GET['nombreE']) && $_GET['nombreE'] != "") {
    $nombreE = explode(",", $_GET['nombreE']);
    if($where == ""){
        $where = "WHERE";
    }
    $num = 0;
    foreach ($nombreE as $value) {
        if($num == 0){
            if(strcmp($where, "WHERE") == 0){
                $where .= " (u.Loggin = '".$value."'";
            }else{
                $where.= " AND (u.Loggin = '".$value."'";
            }
        }else{
            $where .= " OR u.Loggin = '".$value."'";
        }
        $num++;
    }
    $where .= ")";
}
if (isset($_GET['operador']) && $_GET['operador'] != "") {
    $operador = explode(",", $_GET['operador']);
    if($where == ""){
        $where = "WHERE";
    }
    $num = 0;
    foreach($operador as $value){
        if($num == 0){
            if(strcmp($where, "WHERE") == 0){
                $where .= " (ccc.IdUsuario = ".$value;
            }else{
                $where.= " AND (ccc.IdUsuario = ".$value;
            }
        }else{
            $where .= " OR ccc.IdUsuario = ".$value;
        }
        $num++;
    }
    $where .= ")";
}
if (isset($_GET['turno']) && $_GET['turno'] != "") {
    $turno = explode(",", $_GET['turno']);
    if($where == ""){
        $where = "WHERE";
    }
    $num = 0;
    foreach($turno as $value){
        if($num == 0){
            if(strcmp($where, "WHERE") == 0){
                $where .= " (cdu.IdTurno = ".$value;
            }else{
                $where.= " AND (cdu.IdTurno = ".$value;
            }
        }else{
            $where .= " OR cdu.IdTurno = ".$value;
        }
        $num++;
    }
    $where .= ")";
}
if(isset($_GET['fechaI']) && $_GET['fechaI'] != ""){
    if($where == ""){
        $where = "WHERE ccc.FechaCreacion >= ".$_GET['fechaI'];
    }else{
        $where.= "AND ccc.FechaCreacion >= ".$_GET['fechaI'];
    }
}
if(isset($_GET['fechaF']) && $_GET['fechaF'] != ""){
    if($where == ""){
        $where = "WHERE ccc.FechaCreacion >= ".$_GET['fechaF'];
    }else{
        $where.= "AND ccc.FechaCreacion >= ".$_GET['fechaF'];
    }
}

$query = "SELECT a.Descripcion AS Descr	, ccc.IdCambioCampania,
        CONCAT_WS(' ',u.Nombre,u.ApellidoPaterno,u.ApellidoMaterno) AS nombre, 
        (CASE WHEN (ISNULL(ccc2.IdUsuario)) THEN 
        ((5 * (DATEDIFF(ccc.FechaCreacion, cdu.FechaCreacion) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(cdu.FechaCreacion) + WEEKDAY(ccc.FechaCreacion) + 1, 1))) 
        WHEN (!ISNULL(ccc2.IdUsuario)) THEN 
        ((5 * (DATEDIFF(ccc.FechaCreacion, ccc2.FechaCreacion) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(ccc2.FechaCreacion) + WEEKDAY(ccc.FechaCreacion) + 1, 1))) 
        END)AS Salida 
        FROM c_cambio_campania ccc 
        LEFT JOIN c_area AS a ON ccc.IdCampania_antes = a.IdArea 
        LEFT JOIN c_domicilio_usturno AS cdu ON ccc.IdUsuario = cdu.IdUsuario
        LEFT JOIN c_usuario AS u ON ccc.IdUsuario = u.IdUsuario 
        LEFT JOIN c_cambio_campania AS ccc2 ON (ccc2.IdCambioCampania = (SELECT IdCambioCampania FROM c_cambio_campania WHERE IdUsuario = ccc.IdUsuario AND IdCambioCampania < ccc.IdCambioCampania ORDER BY IdCambioCampania DESC LIMIT 1))  
        $where";
//echo $query;

$result = $catalogo->obtenerLista($query);

while ($rs = mysql_fetch_array($result))
{
    $array_valores = array();
    array_push($array_valores, $rs['Descr']);
    array_push($array_valores, "Salida");
    array_push($array_valores, $rs['nombre']);
    array_push($array_valores, $rs['Salida']);
    $writer->writeSheetRow($hoja, $array_valores);  
}

$queryEntrada = "SELECT a.Descripcion AS Descr,
        CONCAT_WS(' ',u.Nombre,u.ApellidoPaterno,u.ApellidoMaterno) AS nombre, 
        (CASE WHEN (ISNULL(ccc3.IdUsuario)) THEN 
        ((5 * (DATEDIFF(NOW(), ccc.FechaCreacion) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(ccc.FechaCreacion) + WEEKDAY(NOW()) + 1, 1)))
        WHEN (!ISNULL(ccc3.IdUsuario)) THEN 
        ((5 * (DATEDIFF(ccc3.FechaCreacion, ccc.FechaCreacion) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(ccc.FechaCreacion) + WEEKDAY(ccc3.FechaCreacion) + 1, 1)))
        END)AS Entrada 
        FROM c_cambio_campania ccc 
        LEFT JOIN c_area AS a ON ccc.IdCampania_despues = a.IdArea 
        LEFT JOIN c_domicilio_usturno AS cdu ON (cdu.IdCampania = ccc.IdCampania_despues AND cdu.IdUsuario = ccc.IdUsuario) 
        LEFT JOIN c_usuario AS u ON ccc.IdUsuario = u.IdUsuario 
        LEFT JOIN c_cambio_campania AS ccc3 ON (ccc3.IdCambioCampania = (SELECT IdCambioCampania FROM c_cambio_campania WHERE IdUsuario = ccc.IdUsuario AND IdCambioCampania > ccc.IdCambioCampania ORDER BY IdCambioCampania ASC LIMIT 1))
        $where";
//echo $query;

$result2 = $catalogo->obtenerLista($queryEntrada);

while ($rs = mysql_fetch_array($result2))
{
    $array_valores = array();
    array_push($array_valores, $rs['Descr']);
    array_push($array_valores, "Entrada");
    array_push($array_valores, $rs['nombre']);
    array_push($array_valores, $rs['Entrada']);
    $writer->writeSheetRow($hoja, $array_valores);  
}

$writer->writeToStdOut();
exit(0);

