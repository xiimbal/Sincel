<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

ini_set("memory_limit","512M");
set_time_limit (0);

include_once("../WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/EnLetras.class.php");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "ReporteFactura5-3.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');

$enLetras = new EnLetras();
$catalogo = new Catalogo();
$permisos_grid = new PermisosSubMenu();
$writer = new XLSXWriter();//Nuevo libro
$writer->setAuthor('Techra');
$hoja = "5.3 Layout Factura";
$nombre_objeto = $permisos_grid->getNombreTicketSistema();

if(isset($_GET['FechaInicio']) && $_GET['FechaInicio']!=""){
    $FechaInicio = $_GET['FechaInicio'];
    $where .= "AND t.FechaHora >= '$FechaInicio 00:00:00'";
}

if(isset($_GET['FechFin']) && $_GET['FechFin']!=""){
    $FechaFin = $_GET['FechFin'];
    $where .= "AND t.FechaHora <= '$FechaFin 23:59:59'";
}

$cabeceras = array($nombre_objeto => "number","Boletos" => "string", "Usuario" => "string", "Cliente" => "string", "Fecha" => "string", 
"Hora Origen" => "string", "Origen" => "string", "Hora Destino" => "string", "Destino" => "string", "Tarifa (con letra)" => "string", "Tarifa (pesos)" => "money");
$query = "SELECT t.IdTicket,(SELECT GROUP_CONCAT(DISTINCT x.NoBoleto SEPARATOR ',') FROM c_notaticket x WHERE x.IdTicket = t.IdTicket) AS Boletos,
CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS usuario,DATE_FORMAT(t.FechaHora, '%Y-%m-%d') AS Fecha,DATE_FORMAT(e.FechaHora, '%H:%i') AS HoraOrigen,
e.Origen,DATE_FORMAT(e.FechaHora, '%H:%i') AS HoraDestino,e.Destino,(CASE WHEN nt.IdEstatusAtencion = 275 THEN (6) WHEN nt.IdEstatusAtencion = 276 THEN (1)
ELSE nt.IdViatico END) AS IdViatico, (CASE WHEN nt.IdEstatusAtencion = 275 THEN (SELECT x.nombre FROM c_tipoviatico x WHERE x.IdEstado = nt.IdEstatusAtencion)
WHEN nt.IdEstatusAtencion = 276 THEN (SELECT x.nombre FROM c_tipoviatico x WHERE x.IdEstado = nt.IdEstatusAtencion) ELSE tv.nombre END) AS Estado,(CASE WHEN ! ISNULL(e.PrecioParticular) THEN (e.PrecioParticular)
WHEN ! ISNULL(u.CostoFijo) THEN (u.CostoFijo) WHEN ! ISNULL(tr.IdTarifa) THEN (ktr.Costo) WHEN csve.IdServicioVE = 74 THEN ksve.Cantidad ELSE (ksve.cantidad * csve.PrecioUnitario)
END) AS monto, ksve.cantidad, cl.NombreRazonSocial FROM c_ticket t INNER JOIN c_cliente cl ON cl.ClaveCliente = t.ClaveCliente INNER JOIN k_tecnicoticket ktt ON ktt.IdTicket = t.IdTicket INNER JOIN c_usuario u ON u.IdUsuario = ktt.IdUsuario INNER JOIN c_especial e ON e.idTicket = t.IdTicket
INNER JOIN k_serviciove ksve ON ksve.IdTicket = t.IdTicket INNER JOIN c_serviciosve csve ON csve.IdServicioVE = ksve.IdServicioVE LEFT JOIN c_tarifarango AS tr ON tr.IdTarifa = csve.IdTarifa
INNER JOIN c_notaticket nt ON nt.IdNotaTicket = ksve.IdNotaTicket LEFT JOIN c_tipoviatico tv ON tv.idTipoViatico = nt.IdViatico LEFT JOIN k_tarifarango AS ktr ON ktr.IdDetalleTarifa = (
SELECT MAX(ktr2.IdDetalleTarifa) FROM k_tarifarango AS ktr2 WHERE ktr2.IdTarifa = tr.IdTarifa AND ksve.cantidad >= ktr2.RangoInicial AND ksve.cantidad <= ktr2.RangoFinal)
WHERE ksve.Validado = 1 $where ORDER BY t.IdTicket;";
$result = $catalogo->obtenerLista($query);
$tickets = array();
$boletos = array();
$usuario = array();
$cliente = array();
$fecha = array();
$horaOrigen = array();
$origen = array();
$horaDestino = array();
$destino = array();
$tarifaLetra = array();
$tarifaPesos = array();
$estado = array();
$montos = array();
$valores = array();
while($rs = mysql_fetch_array($result)){
    if(!in_array($rs['IdTicket'], $tickets)){
        array_push($tickets, $rs['IdTicket']);
        $boletos[$rs['IdTicket']] = $rs['Boletos'];
        $usuario[$rs['IdTicket']] = $rs['usuario'];
        $cliente[$rs['IdTicket']] = $rs['NombreRazonSocial'];
        $fecha[$rs['IdTicket']] = $rs['Fecha'];
        $horaOrigen[$rs['IdTicket']] = $rs['HoraOrigen'];
        $origen[$rs['IdTicket']] = $rs['Origen'];
        $horaDestino[$rs['IdTicket']] = $rs['HoraDestino'];
        $destino[$rs['IdTicket']] = $rs['Destino'];
    }
    if($rs['IdViatico'] == 6){//Si es viatico
        if(empty($rs['monto'])){
            $tarifaPesos[$rs['IdTicket']] += 0;  
        }else{
            $tarifaPesos[$rs['IdTicket']] += $rs['monto'];            
        }
    }
    if(!empty($rs['Estado']) && !in_array($rs['Estado'], $estado)){
        $estado[$rs['IdViatico']] = $rs['Estado'];
    }
    if(isset($montos[$rs['IdTicket']][$rs['IdViatico']])){
        if($rs['IdViatico'] == 6){
            $montos[$rs['IdTicket']][$rs['IdViatico']] += $rs['cantidad'];
        }else{
            $montos[$rs['IdTicket']][$rs['IdViatico']] += $rs['monto'];
        }
    }else{
        if($rs['IdViatico'] == 6){
            $montos[$rs['IdTicket']][$rs['IdViatico']] = $rs['cantidad'];
        }else{
            $montos[$rs['IdTicket']][$rs['IdViatico']] = $rs['monto'];
        }
    }
}
foreach ($estado as $key => $value) {
    if($key == 6){
        $cabeceras[$value] = "number";
    }else{
        $cabeceras[$value] = "money";    
    }
}
$cabeceras["Subtotal"] = "money";
$writer->writeSheetHeader($hoja, $cabeceras );
foreach($tickets AS $valor){
    $valores = array();
    $subtotal = $tarifaPesos[$valor];
    array_push($valores, $valor);
    array_push($valores, $boletos[$valor]);   
    array_push($valores, $usuario[$valor]);
    array_push($valores, $cliente[$valor]);   
    array_push($valores, $fecha[$valor]);   
    array_push($valores, $horaOrigen[$valor]);   
    array_push($valores, $origen[$valor]);
    array_push($valores, $horaDestino[$valor]);   
    array_push($valores, $destino[$valor]);   
    array_push($valores, $enLetras->ValorEnLetras($tarifaPesos[$valor], "pesos"));   
    array_push($valores, $tarifaPesos[$valor]);  
    foreach ($estado as $key => $value) {
        if(isset($montos[$valor][$key])){
            if($key != 6){
                $subtotal += $montos[$valor][$key];
            }
            array_push($valores, $montos[$valor][$key]);
        }else{
            array_push($valores, "");
        }
    }
    array_push($valores, $subtotal);
    $writer->writeSheetRow($hoja, $valores);
    unset($valores);
}

























$writer->writeToStdOut();
exit(0);