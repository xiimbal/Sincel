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

$filename = "LayoutFacturas5-3.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');

$catalogo = new Catalogo();
$writer = new XLSXWriter();//Nuevo libro
$writer->setAuthor('Techra');
$hoja = "Relación Servicios";

$where = "WHERE !ISNULL(u.IdUsuario)";
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

$cabeceras = array("Servicio" => "number", "Usuario" => "string", "Cliente" => "string", "Fecha" => "string", "Hora Origen" => "string", "Origen" => "string",
    "Hora destino" => "string", "Destino" => "string", "Tarifa Letra" => "string", "Tarifa Pesos" => "number", "Km" => "number", 
    "Casetas" => "number", "Tiempo de Espera" => "number", "Estacionamiento" => "number", "Subtotal" => "number", 
    "Evaluación Unidad" => "string", "Evaluación Asociado Loyalty" => "string", "Evaluación de la puntualidad" => "string", "Evaluación Gral. Del Servicio" => "string", "Comentarios" => "string");
$writer->writeSheetHeader($hoja, $cabeceras );

//Todos los viaticos de los tickets de servicio
$consulta = "SELECT c.RFC AS RFCReceptor, dfe.RFC AS RFCEmisor,
t.IdTicket,tv.nombre AS TipoViatico,
e.PrecioParticular,u.CostoFijo,tr.IdTarifa,
(CASE 
WHEN !ISNULL(e.PrecioParticular) THEN (e.PrecioParticular) 
WHEN !ISNULL(u.CostoFijo) THEN (u.CostoFijo) 
WHEN !ISNULL(tr.IdTarifa) THEN (ktr.Costo) 
WHEN csve.IdServicioVE = 74 THEN sve.Cantidad 
ELSE (sve.cantidad * csve.PrecioUnitario)
END
) AS monto, u2.RFC AS RFCProveedor, sve.CobrarSiNo, sve.PagarSiNo
FROM c_ticket t
INNER JOIN c_cliente AS c ON c.ClaveCliente = t.ClaveCliente
INNER JOIN c_datosfacturacionempresa AS dfe ON dfe.IdDatosFacturacionEmpresa = c.IdDatosFacturacionEmpresa
INNER JOIN k_serviciove AS sve ON sve.IdTicket = t.IdTicket AND (ISNULL(sve.CobrarSiNo) || sve.CobrarSiNo = 1)
INNER JOIN c_serviciosve AS csve ON sve.IdServicioVE = csve.IdServicioVE
LEFT JOIN c_tarifarango AS tr ON tr.IdTarifa = csve.IdTarifa
LEFT JOIN k_tarifarango AS ktr ON ktr.IdDetalleTarifa = 
(SELECT MAX(ktr2.IdDetalleTarifa) FROM k_tarifarango AS ktr2 WHERE ktr2.IdTarifa = tr.IdTarifa AND sve.cantidad >= ktr2.RangoInicial AND sve.cantidad <= ktr2.RangoFinal)
LEFT JOIN c_especial AS e ON e.idTicket = t.IdTicket
LEFT JOIN c_usuario AS u ON u.IdUsuario = e.idUsuario
LEFT JOIN k_tecnicoticket AS tt ON tt.IdTicket = t.IdTicket
LEFT JOIN c_usuario AS u2 ON u2.IdUsuario = tt.IdUsuario
LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = sve.IdNotaTicket
LEFT JOIN c_tipoviatico AS tv ON tv.idTipoViatico = nt.IdViatico
$where GROUP BY sve.IdPartida ORDER BY t.IdTicket;";

//Todos los tickets de servicio
$consulta2 = "SELECT t.IdTicket, c.NombreRazonSocial, CONCAT(u.UsuarioCreacion,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Usuario,
t.FechaHora AS Fecha, e.Origen, e.Destino, e.FechaHora AS FechaHoraOrigen, fs.FechaHora AS FechaHoraDestino,
tr.IdTarifa, ktr.Costo, sve.CobrarSiNo
FROM `c_ticket` AS t
LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdTicket = t.IdTicket
LEFT JOIN c_usuario AS u ON u.IdUsuario = ktt.IdUsuario
LEFT JOIN c_cliente AS c ON c.ClaveCliente = t.ClaveCliente
LEFT JOIN c_especial AS e ON e.idTicket = t.IdTicket
LEFT JOIN k_serviciove AS sve ON sve.IdTicket = t.IdTicket AND (ISNULL(sve.CobrarSiNo) OR sve.CobrarSiNo = 1)
LEFT JOIN c_serviciosve AS csve ON sve.IdServicioVE = csve.IdServicioVE
LEFT JOIN c_tarifarango AS tr ON tr.IdTarifa = csve.IdTarifa
LEFT JOIN k_tarifarango AS ktr ON ktr.IdDetalleTarifa = (SELECT MAX(ktr2.IdDetalleTarifa) FROM k_tarifarango AS ktr2 WHERE ktr2.IdTarifa = tr.IdTarifa AND sve.cantidad >= ktr2.RangoInicial AND sve.cantidad <= ktr2.RangoFinal)
LEFT JOIN c_notaticket AS fs ON fs.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = 284)
WHERE !ISNULL(ktt.IdUsuario) GROUP BY t.IdTicket;";

$writer->writeToStdOut();
exit(0);