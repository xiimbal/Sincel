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

$filename = "ReporteViajes.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$writer = new XLSXWriter();
$writer->setAuthor('Techra');

$cabeceras = array('' => 'string', '' => "string", 'Reporte Viajes por Campaña' => "string", '' => "string", '' => "string");

$hoja = "Reporte";
$writer->writeSheetHeader($hoja, $cabeceras);

$array_valores_e = array();
array_push($array_valores_e, "");
array_push($array_valores_e, "");
array_push($array_valores_e, "");
array_push($array_valores_e, "");
array_push($array_valores_e, "");
$writer->writeSheetRow($hoja, $array_valores_e);  

$array_valores_i = array();
array_push($array_valores_i, "Campaña");
array_push($array_valores_i, "Nombre Empleado");
array_push($array_valores_i, "Tipo");
array_push($array_valores_i, "Origen");
array_push($array_valores_i, "Destino");
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
$where2 = $where;
if (isset($_GET['nombreE']) && $_GET['nombreE'] != "") {
    $nombreE = explode(",", $_GET['nombreE']);
    if($where == ""){
        $where = "WHERE";
    }
    $num = 0;
    foreach ($nombreE as $value) {
        if($num == 0){
            if(strcmp($where, "WHERE") == 0){
                $where .= " (t.NoSerieEquipo = '".$value."'";
            }else{
                $where.= " AND (t.NoSerieEquipo = '".$value."'";
            }
        }else{
            $where .= " OR t.NoSerieEquipo = '".$value."'";
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
                $where .= " (dut.IdTurno = ".$value;
            }else{
                $where.= " AND (dut.IdTurno = ".$value;
            }
        }else{
            $where .= " OR dut.IdTurno = ".$value;
        }
        $num++;
    }
    $where .= ")";
}
if(isset($_GET['fechaI']) && $_GET['fechaI'] != ""){
    if($where == ""){
        $where = "WHERE t.FechHoraInicRep >= ".$_GET['fechaI'];
    }else{
        $where.= "AND t.FechHoraInicRep >= ".$_GET['fechaI'];
    }
}
if(isset($_GET['fechaF']) && $_GET['fechaF'] != ""){
    if($where == ""){
        $where = "WHERE t.FechHoraInicRep >= ".$_GET['fechaF'];
    }else{
        $where.= "AND t.FechHoraInicRep >= ".$_GET['fechaF'];
    }
}
if($where == ""){
    $where = "WHERE t.TipoReporte = 1";
}else{
    $where.= "AND t.TipoReporte = 1";
}

$query = "SELECT a.Descripcion, CONCAT_WS(' ',u.Nombre,u.ApellidoPaterno, u.ApellidoMaterno) AS nombreEmpleado, ccc.Nombre,
        (CASE WHEN p.TipoEvento = 1 THEN 'Aforo' WHEN p.TipoEvento = 2 THEN 'Desaforo' END) AS tipo,
        CONCAT_WS(' ','C.',dut.Calle,'No.E',dut.NoExterior,'No.I',dut.NoInterior, 'Col.', dut.Colonia,'C.P.',dut.CodigoPostal, dut.Estado, dut.Delegacion) AS domicilioUsuario,
        CONCAT_WS(' ','C.',d.Calle,'No.E',d.NoExterior,'No.I',d.NoInterior, 'Col.', d.Colonia,'C.P.',d.CodigoPostal, d.Estado, d.Delegacion) AS centroCosto
        FROM c_ticket t
        LEFT JOIN k_plantilla_asistencia AS kpa ON kpa.IdTicket = t.IdTicket
        LEFT JOIN k_plantilla AS kp ON kp.idK_Plantilla = kpa.idK_Plantilla
        LEFT JOIN c_plantilla AS p ON kp.idPlantilla = p.idPlantilla
        LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdTicket = kpa.IdTicket 
        LEFT JOIN c_area AS a ON a.IdArea = p.idCampania
        LEFT JOIN c_usuario as u ON t.NoSerieEquipo = u.Loggin
        LEFT JOIN c_domicilio_usturno as dut ON dut.IdUsuario = u.IdUsuario
        LEFT JOIN c_centrocosto AS ccc ON a.ClaveCentroCosto = ccc.ClaveCentroCosto
        LEFT JOIN c_domicilio AS d ON d.ClaveEspecialDomicilio = a.ClaveCentroCosto
        $where";
//echo $query;

$result = $catalogo->obtenerLista($query);

while ($rs = mysql_fetch_array($result))
{
    if(isset($rs['nombreEmpleado']) && $rs['nombreEmpleado'] != ""){
        $array_valores = array();
        array_push($array_valores, $rs['Descripcion']);
        array_push($array_valores, $rs['nombreEmpleado']);
        array_push($array_valores, $rs['tipo']);
        if(strcmp($rs['tipo'], "Aforo") == 0)
        {
            array_push($array_valores, $rs['domicilioUsuario']);
            array_push($array_valores, $rs['Nombre']." Domicilio. ".$rs['centroCosto']);
        }else{
            array_push($array_valores, $rs['Nombre']." Domicilio. ".$rs['centroCosto']);
            array_push($array_valores, $rs['domicilioUsuario']);
        }
        $writer->writeSheetRow($hoja, $array_valores); 
    } 
}

$queryViajesEspeciales = "SELECT a.Descripcion, e.Origen, e.Destino, CONCAT_WS(' ',u.Nombre,u.ApellidoPaterno, u.ApellidoMaterno) AS Loggin,
        CONCAT_WS(' ','C.',e.Calle_or,'No.E',e.NoExterior_or,'No.I',e.NoInterior_or, 'Col.', e.Colonia_or,'C.P.',e.CodigoPostal_or, e.Estado_or, e.Delegacion_or) AS domicilioOrigen,
        CONCAT_WS(' ','C.',e.Calle_des,'No.E',e.NoExterior_des,'No.I',e.NoInterior_des, 'Col.', e.Colonia_des,'C.P.',e.CodigoPostal_des, e.Estado_des, e.Delegacion_des) AS domicilioDestino
        FROM c_especial e
        LEFT JOIN c_usuario AS u ON u.IdUsuario = e.idUsuario
        LEFT JOIN c_area AS a ON e.idCampania = a.IdArea
        $where2";

$result = $catalogo->obtenerLista($queryViajesEspeciales);

while ($rs = mysql_fetch_array($result))
{
    $array_valores = array();
    array_push($array_valores, $rs['Descripcion']);
    array_push($array_valores, $rs['Loggin']);
    array_push($array_valores, "Viaje Especial");
    array_push($array_valores, $rs['Origen']." Domicilio. ".$rs['domicilioOrigen']);
    array_push($array_valores, $rs['Destino']." Domicilio. ".$rs['domicilioDestino']);
    $writer->writeSheetRow($hoja, $array_valores);  
}

$writer->writeToStdOut();
exit(0);

