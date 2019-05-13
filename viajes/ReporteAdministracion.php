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

$filename = "ReporteAdministracion.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$writer = new XLSXWriter();
$writer->setAuthor('Techra');

$cabeceras = array(' ' => 'string', ' ' => "string", ' ' => "string", 'Reporte de Administracion' => "string", ' ' => "String", ' ' => "string");

$hoja = "Reporte";
$writer->writeSheetHeader($hoja, $cabeceras);

$array_valores_e = array();
array_push($array_valores_e, "");
array_push($array_valores_e, "");
array_push($array_valores_e, "");
array_push($array_valores_e, "");
array_push($array_valores_e, "");
array_push($array_valores_e, "");
$writer->writeSheetRow($hoja, $array_valores_e);  

$array_valores_i = array();
array_push($array_valores_i, "Campaña");
array_push($array_valores_i, "Nombre empleado");
array_push($array_valores_i, "Turno");
array_push($array_valores_i, "Direccion");
array_push($array_valores_i, "Servicio");
array_push($array_valores_i, "Gasto");
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
                $where .= " (ti.NoSerieEquipo = '".$value."'";
            }else{
                $where.= " AND (ti.NoSerieEquipo = '".$value."'";
            }
        }else{
            $where .= " OR ti.NoSerieEquipo = '".$value."'";
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
                $where .= " (ktt.IdUsuario = ".$value;
            }else{
                $where.= " AND (ktt.IdUsuario = ".$value;
            }
        }else{
            $where .= " OR ktt.IdUsuario = ".$value;
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
                $where .= " (t.idTurno = ".$value;
            }else{
                $where.= " AND (t.idTurno = ".$value;
            }
        }else{
            $where .= " OR t.idTurno = ".$value;
        }
        $num++;
    }
    $where .= ")";
}
if(isset($_GET['fechaI']) && $_GET['fechaI'] != ""){
    if($where == ""){
        $where = "WHERE p.Fecha >= ".$_GET['fechaI'];
    }else{
        $where.= "AND p.Fecha >= ".$_GET['fechaI'];
    }
}
if(isset($_GET['fechaF']) && $_GET['fechaF'] != ""){
    if($where == ""){
        $where = "WHERE p.Fecha >= ".$_GET['fechaF'];
    }else{
        $where.= "AND p.Fecha >= ".$_GET['fechaF'];
    }
}

$pagoPorKm = 0;
$consultaParametro = "SELECT Valor FROM c_parametro WHERE IdParametro = 43";
$resultParametro = $catalogo->obtenerLista($consultaParametro);
if($rsPar = mysql_fetch_array($resultParametro))
{
    $pagoPorKm = $rsPar['Valor'];
}

$query = "SELECT a.Descripcion AS campania, t.descripcion, t.horaEntrada, t.horaSalida,
        lt.ContadorBN AS servicio, CONCAT_WS(' ',u2.Nombre,u2.ApellidoPaterno, u2.ApellidoMaterno) AS Loggin, 
        CONCAT_WS(' ','C.',d.Calle,'No.E',d.NoExterior,'No.I',d.NoInterior, 'Col.'
        ,d.Colonia,'C.P.',d.CodigoPostal, d.Estado, d.Delegacion) AS direccion
        FROM c_plantilla p
        LEFT JOIN c_area AS a ON p.idCampania = a.IdArea 
        LEFT JOIN k_plantilla AS kp ON kp.idPlantilla = p.idPlantilla
        LEFT JOIN k_plantilla_asistencia AS kpa ON kpa.idK_Plantilla = kp.idK_Plantilla
        LEFT JOIN c_ticket AS ti ON ti.IdTicket = kpa.IdTicket
        LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdTicket = kpa.IdTicket 
        LEFT JOIN c_domicilio AS d ON d.ClaveEspecialDomicilio = ti.ClaveCentroCosto 
        LEFT JOIN c_usuario AS u ON u.IdUsuario = ktt.IdUsuario 
        LEFT JOIN c_turno AS t ON t.idTurno = p.idTurno 
        LEFT JOIN c_lecturasticket AS lt ON lt.fk_idticket = kpa.IdTicket
        LEFT JOIN c_usuario AS u2 ON u2.Loggin = ti.NoSerieEquipo
        $where";

$result = $catalogo->obtenerLista($query);

while ($rs = mysql_fetch_array($result))
{
    if(isset($rs['Loggin']) && $rs['Loggin'] != "") {
        $array_valores = array();
        array_push($array_valores, $rs['campania']);
        array_push($array_valores, $rs['Loggin']);
        array_push($array_valores, $rs['descripcion']." (".$rs['horaEntrada']." - ".$rs['horaSalida'].")");
        array_push($array_valores, $rs['direccion']);
        array_push($array_valores, $rs['servicio']);
        array_push($array_valores, "$".money_format('%(#10n',$rs['servicio'] * $pagoPorKm));
        $writer->writeSheetRow($hoja, $array_valores); 
    }
}

$writer->writeToStdOut();
exit(0);
