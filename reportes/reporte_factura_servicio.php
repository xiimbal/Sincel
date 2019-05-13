<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

ini_set("memory_limit","512M");
set_time_limit (0);

include_once("../WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "ReporteFactura5-2.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');

$catalogo = new Catalogo();
$writer = new XLSXWriter();//Nuevo libro
$writer->setAuthor('Techra');
$hoja = "5.2 Facturas";
    
$where = "";
$FechaInicio = "";
$FechaFin = "";
$estados = array();
$clientes = array();
$montos = array();
$totales_cliente = array();
$totales_estado = array();
$boleto = array();

if(isset($_GET['FechaInicio']) && $_GET['FechaInicio']!=""){
    $FechaInicio = $_GET['FechaInicio'];
    $where .= "AND t.FechaHora >= '$FechaInicio 00:00:00'";
}

if(isset($_GET['FechFin']) && $_GET['FechFin']!=""){
    $FechaFin = $_GET['FechFin'];
    $where .= "AND t.FechaHora <= '$FechaFin 23:59:59'";
}

$consulta = "SELECT t.IdTicket, c.NombreRazonSocial, CONCAT(u.UsuarioCreacion,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Usuario,
et.Nombre AS EstadoTicket, en.Nombre AS EstadoNota, en.IdEstado, c.ClaveCliente,e.PrecioParticular,u.CostoFijo,
(CASE 
	WHEN !ISNULL(e.PrecioParticular) THEN (e.PrecioParticular) 
	WHEN !ISNULL(u.CostoFijo) THEN (u.CostoFijo) 
	WHEN !ISNULL(tr.IdTarifa) THEN (ktr.Costo) 
	WHEN csve.IdServicioVE = 74 THEN sve.Cantidad 
	ELSE (sve.cantidad * csve.PrecioUnitario) 
END
) AS monto,IF(ISNULL((SELECT nt3.IdNotaTicket FROM c_notaticket nt3 WHERE nt3.IdNotaTicket = sve.IdNotaTicket AND nt3.IdEstatusAtencion IN (274, 275, 276))),
0,1) AS esViatico, nt3.NoBoleto
FROM `c_ticket` AS t
LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdTicket = t.IdTicket
LEFT JOIN c_usuario AS u ON u.IdUsuario = ktt.IdUsuario
LEFT JOIN c_cliente AS c ON c.ClaveCliente = t.ClaveCliente
LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)
LEFT JOIN c_notaticket nt3 ON nt3.IdNotaTicket= (SELECT MAX(IdNotaTicket) FROM c_notaticket nt3 WHERE nt3.IdTicket = t.IdTicket AND nt3.IdEstatusAtencion = 277)
LEFT JOIN c_estado AS et ON et.IdEstado = t.EstadoDeTicket
LEFT JOIN c_estado AS en ON en.IdEstado = nt.IdEstatusAtencion
LEFT JOIN c_especial AS e ON e.idTicket = t.IdTicket
LEFT JOIN k_serviciove AS sve ON sve.IdTicket = t.IdTicket AND (ISNULL(sve.CobrarSiNo) OR sve.CobrarSiNo = 1)
LEFT JOIN c_serviciosve AS csve ON sve.IdServicioVE = csve.IdServicioVE
LEFT JOIN c_tarifarango AS tr ON tr.IdTarifa = csve.IdTarifa
LEFT JOIN k_tarifarango AS ktr ON ktr.IdDetalleTarifa = (SELECT MAX(ktr2.IdDetalleTarifa) FROM k_tarifarango AS ktr2 WHERE ktr2.IdTarifa = tr.IdTarifa AND sve.cantidad >= ktr2.RangoInicial AND sve.cantidad <= ktr2.RangoFinal)
WHERE sve.Validado = 1 $where ORDER BY c.NombreRazonSocial, t.IdTicket";

$result = $catalogo->obtenerLista($consulta);
$ticket_procesado = array();
while($rs = mysql_fetch_array($result)){
    if(in_array($rs['IdTicket'], $ticket_procesado)){//Si el ticket ya se proceso por completo, es decir, que tiene un precio fijo, solo se procesa la primera vez
        continue;
    }
    
    if(!in_array($rs['EstadoNota'], $estados)){//Guardamos nuevos estados
        $estados[$rs['IdEstado']] = $rs['EstadoNota'];
        $totales_estado[$rs['IdEstado']] = 0;
    }
    
    if(!in_array($rs['ClaveCliente'], $clientes)){//Guardamos nuevos clientes
        $clientes[$rs['ClaveCliente']] = $rs['NombreRazonSocial'];
    }
    
    //Sumamos el monto al array[cliente][IdTicket][IdEstado]    
    if(isset($totales_cliente[$rs['ClaveCliente']][$rs['IdTicket']][$rs['IdEstado']])){
        $totales_cliente[$rs['ClaveCliente']][$rs['IdTicket']][$rs['IdEstado']] += (float) $rs['monto'];
    }else{
        $totales_cliente[$rs['ClaveCliente']][$rs['IdTicket']][$rs['IdEstado']] = (float) $rs['monto'];
    }

    $totales_estado[$rs['IdEstado']] += (float)$rs['monto'];
    $boleto[$rs['IdTicket']] = $rs['NoBoleto'];
    /*if(!empty($rs['PrecioParticular']) || !empty($rs['CostoFijo'])){
        array_push($ticket_procesado, $rs['IdTicket']);
    }*/
}

$cabeceras = array("Tipo Fila" => "string","Cliente" => "string", "Servicio" => "number", "No. Boleto" => "number");
foreach ($estados as $value) {
    if(empty($value)){
        $value = "N/D";
    }
    $cabeceras[$value] = "number";    
}
$cabeceras["Total General"] = "number";
$writer->writeSheetHeader($hoja, $cabeceras );

foreach ($totales_cliente as $key => $value) {
    $valores = array();  
    $total_cliente_estado = array(); //Array para guardar el total por cliente-estado
    //Como primer columna se pone el nombre del cliente
    array_push($valores, "C");
    array_push($valores, $clientes[$key]);
    array_push($valores, "");
    array_push($valores, "");//Boleto
    //Recorremos todos los tickets
    foreach ($value as $key2 => $value2) { 
        $valores_ticket = array();
        $total_fila_ticket = 0;
        array_push($valores_ticket, "S");        
        array_push($valores_ticket, "");   
        array_push($valores_ticket, $key2);  
        array_push($valores_ticket, $boleto[$key2]);//Boleto
        foreach ($estados as $key3 => $value3) {
            if(!isset($total_cliente_estado[$key3])){
                $total_cliente_estado[$key3] = (float)$value2[$key3];
            }else{
                $total_cliente_estado[$key3] += (float)$value2[$key3];
            }
            if(isset($value2[$key3]) && !empty($value2[$key3])){
                $total_fila_ticket += (float)$value2[$key3];
                array_push($valores_ticket, $value2[$key3]);//Agregamos el monto del ticket por cada estado
            }else{
                array_push($valores_ticket, "");//Agregamos el monto del ticket por cada estado
            }
        }
        array_push($valores_ticket, $total_fila_ticket);//Agregamos el monto del ticket total
        $writer->writeSheetRow($hoja, $valores_ticket);
    }
    
    $total_fila_cliente = 0;
    foreach ($estados as $key => $value) {
        if(isset($total_cliente_estado[$key])){
            $total_fila_cliente += (float)$total_cliente_estado[$key];
            array_push($valores, $total_cliente_estado[$key]);
        }else{
            array_push($valores, 0);
        }
    }
    array_push($valores, $total_fila_cliente);
    $writer->writeSheetRow($hoja, $valores);
}

//Ponemos totales generales por estado
$valor_totales = array();
array_push($valor_totales, "T");
array_push($valor_totales, "Total general");
array_push($valor_totales, "");
$total_final = 0;
foreach ($estados as $key => $value) {
    if(isset($totales_estado[$key])){
        $total_final += (float)$totales_estado[$key];
        //echo $total_final . "<br>";
        array_push($valor_totales, $totales_estado[$key]);
    }else{
        array_push($valor_totales, 0);
    }
}
array_push($valor_totales, $total_final);
$writer->writeSheetRow($hoja, $valor_totales);

$writer->writeToStdOut();
exit(0);
