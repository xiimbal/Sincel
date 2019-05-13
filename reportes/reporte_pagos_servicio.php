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

$filename = "ReportePagos4-4.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');

$catalogo = new Catalogo();
$writer = new XLSXWriter();//Nuevo libro
$writer->setAuthor('Techra');
$hoja = "4.4 Reporte de pagos";

$where = "WHERE t.Activo = 1";
$FechaInicio = "";
$FechaFin = "";

if(isset($_GET['FechaInicio']) && $_GET['FechaInicio']!=""){
    $FechaInicio = $_GET['FechaInicio'];
    if($where!=""){
        $where.=" AND ";
    }else{
        $where = "WHERE ";
    }
    $where .= "t.FechaHora >= '$FechaInicio 00:00:00'";
}

if(isset($_GET['FechFin']) && $_GET['FechFin']!=""){
    $FechaFin = $_GET['FechFin'];
    if($where!=""){
        $where.=" AND ";
    }else{
        $where = "WHERE ";
    }
    $where .= "t.FechaHora <= '$FechaFin 23:59:59'";
}

$consulta = "SELECT t.IdTicket, c.NombreRazonSocial, CONCAT(u.UsuarioCreacion,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Usuario,
c.ClaveCliente,t.FechaHora, e.PrecioParticular,u.CostoFijo,u.IdUsuario,tv.nombre AS TipoViatico, tv.idTipoViatico,
(CASE 
	WHEN !ISNULL(e.PrecioParticular) THEN (e.PrecioParticular) 
	WHEN !ISNULL(u.CostoFijo) THEN (u.CostoFijo) 
	WHEN !ISNULL(tr.IdTarifa) THEN (ktr.Costo) 
	WHEN csve.IdServicioVE = 74 THEN sve.Cantidad 
	ELSE (sve.cantidad * csve.PrecioUnitario) 
END
) AS monto
FROM `c_ticket` AS t
LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdTicket = t.IdTicket
LEFT JOIN c_usuario AS u ON u.IdUsuario = ktt.IdUsuario
LEFT JOIN c_cliente AS c ON c.ClaveCliente = t.ClaveCliente
LEFT JOIN c_especial AS e ON e.idTicket = t.IdTicket
LEFT JOIN k_serviciove AS sve ON sve.IdTicket = t.IdTicket AND (ISNULL(sve.CobrarSiNo) OR sve.CobrarSiNo = 1)
LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = sve.IdNotaTicket
LEFT JOIN c_tipoviatico AS tv ON tv.idTipoViatico = nt.IdViatico
LEFT JOIN c_serviciosve AS csve ON sve.IdServicioVE = csve.IdServicioVE
LEFT JOIN c_tarifarango AS tr ON tr.IdTarifa = csve.IdTarifa
LEFT JOIN k_tarifarango AS ktr ON ktr.IdDetalleTarifa = (SELECT MAX(ktr2.IdDetalleTarifa) FROM k_tarifarango AS ktr2 WHERE ktr2.IdTarifa = tr.IdTarifa AND sve.cantidad >= ktr2.RangoInicial AND sve.cantidad <= ktr2.RangoFinal)
$where AND !ISNULL(u.IdUsuario)
ORDER BY Usuario, t.IdTicket;";//Consulta para los egresos

$consulta2 = "SELECT f.IdPrestamo,t.Fecha,c.Concepto,c.IdConcepto, u.IdUsuario, 
(CASE WHEN c.IdTipo = 2 THEN t.Importe ELSE (-1 * t.Importe) END) AS Importe,
CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Operador
FROM c_financial AS f 
LEFT JOIN c_usuario AS u ON u.IdUsuario = f.IdOperador
LEFT JOIN k_financial AS t ON t.IdFinancial = f.IdPrestamo
LEFT JOIN c_conceptofinancial AS c ON c.IdConcepto = t.IdConcepto $where;"; //Consulta para los egresos

//Ingreos - Costos de servicios
$tipos_viaticos = array();
$totales_tipo_viatico = array();
$usuarios = array();
$clientes = array();
$arreglos_datos_egresos = array();

$result = $catalogo->obtenerLista($consulta);
while($rs = mysql_fetch_array($result)){
    if(in_array($rs['IdTicket'], $ticket_procesado)){//Si el ticket ya se proceso por completo, es decir, que tiene un precio fijo, solo se procesa la primera vez
        continue;
    }
    
    if(!in_array($rs['TipoViatico'], $tipos_viaticos)){//Guardamos nuevos estados
        $tipos_viaticos[$rs['idTipoViatico']] = $rs['TipoViatico'];
        $totales_estado[$rs['TipoViatico']] = 0;
    }
    
    if(!in_array($rs['IdUsuario'], $clientes)){//Guardamos nuevos clientes
        $usuarios[$rs['IdUsuario']] = $rs['Usuario'];
    }
    
    if(!in_array($rs['ClaveCliente'], $clientes)){//Guardamos nuevos clientes
        $clientes[$rs['ClaveCliente']] = $rs['NombreRazonSocial'];
    }
    
    //Procesar aqui
    //Sumamos el monto al array[cliente][IdTicket][IdEstado]    
    if(isset($arreglos_datos_egresos[$rs['IdUsuario']][$rs['FechaHora']][$rs['IdTicket']][$rs['ClaveCliente']])){
        $arreglos_datos_egresos[$rs['IdUsuario']][$rs['FechaHora']][$rs['IdTicket']][$rs['ClaveCliente']] += (float) $rs['monto'];
    }else{
        $arreglos_datos_egresos[$rs['IdUsuario']][$rs['FechaHora']][$rs['IdTicket']][$rs['ClaveCliente']] = (float) $rs['monto'];
    }

    $totales_estado[$rs['idTipoViatico']] += (float)$rs['monto'];    
    
    if(!empty($rs['PrecioParticular']) || !empty($rs['CostoFijo'])){
        array_push($ticket_procesado, $rs['IdTicket']);
    }
}

//Egresos - Financial
$conceptos = array();
$arreglos_datos_financial = array();
$result = $catalogo->obtenerLista($consulta2);
while($rs = mysql_fetch_array($result)){
    if(!in_array($rs['Concepto'], $conceptos)){//Guardamos nuevos estados
        $conceptos[$rs['IdConcepto']] = $rs['Concepto'];        
    }
    //Sumamos el monto al array[cliente][IdTicket][IdEstado]    
    if(!isset($arreglos_datos_financial[$rs['IdUsuario']][$rs['Fecha']][$rs['IdConcepto']][$rs['IdPrestamo']])){
        $arreglos_datos_financial[$rs['IdUsuario']][$rs['Fecha']][$rs['IdConcepto']][$rs['IdPrestamo']] = (float) $rs['monto'];
    }else{
        $arreglos_datos_financial[$rs['IdUsuario']][$rs['Fecha']][$rs['IdConcepto']][$rs['IdPrestamo']] += (float) $rs['monto'];        
    }    
}



$writer->writeToStdOut();
exit(0);