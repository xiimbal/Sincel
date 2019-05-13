<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
ini_set("memory_limit", "256M");
set_time_limit(0);

include_once("../WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "Layout Excel Facturacion TE.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$writer = new XLSXWriter();
$writer->setAuthor('Techra');

$cabeceras = array('Fecha de captura' => 'string', 'Nombre completo' => "string", 'Compañia' => "string", 'Departamento o Centro de Costos' => 'string', 'Teléfono celular' => 'string',
    'Correo electrónico' => 'string', '¿Por qué medio prefiere que le hagamos llegar la información del servicio?' => 'string', 'Origen' => 'string', 'Referencias' => 'string', 
    'Fecha del servicio' => 'string', 'Destino' => 'string', 'Forma de pago' => 'string', 'Servicio Id' => 'string', 'Registró' => 'string',
    'Turno' => 'string', 'CVE Cia' => 'string', 'Cve Op' => 'string', 'Operador Nombre' => 'string', 'Estatus' => 'string', 'Boleto No' => 'string', 'Km recorridos' => 'number',
    'Tiempo de espera' => 'string', 'Casetas' => 'string', 'Estacionamiento' => 'string', 'Incidencias' => 'string', 'Importe' => 'string', 'TU- Tarifa Unidad de Apoyo' => 'string',
    'Tipo de servicio' => 'string', 'Hora Origen' => 'string', 'Hora Destino' => 'string', 'Comentarios' => 'string', 'Envió foto' => 'int',
    'Vale Fisico' => "string", 'Factura No.' => "string", 'Se debe pagar?' => 'string', 'Id Pago' => 'string', 'Gross Fac' => 'string', 'Gross Opr' => 'string', 'Gross Lyl' => 'string', 'Customer' => 'string', 'Comentarios Validación' => 'string',
    'Entregó vale?' => "string", 'Empresa Nombre' => "string", 'Empresa Clave' => "string", 'HORA DE LLEGADA' => "string", 'TIEMPO DE ESPERA' => 'string');

$hoja = "BaseServicio";
$writer->writeSheetHeader($hoja, $cabeceras);
$catalogo = new Catalogo();
$tipoReporte = "";
$areaAtencion = "";

$cliente = "";
$estado_falla = "";
$estado = "";
$tecnico = "";
$idTicket = "";

$cerradoTicket = "t.EstadoDeTicket <> 2 AND ";
$having = " HAVING ((IdEstatusAtencion <> 16 AND IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion))";
$morososTicket = "cl.IdEstatusCobranza <> 2 AND ";
$canceladoTicket = "t.EstadoDeTicket <> 4 AND ";
$estadoNota = "LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion!=92)";
$tipo_join_estado = "LEFT";
$tipo_join = "LEFT";
$vendedor = "";
$tfs = "";

$where = "";
if (isset($_GET['idTicket']) && $_GET['idTicket'] != "" && $_GET['idTicket'] != 0) {
    $idTicket = $_GET['idTicket'];
}

if (isset($_GET['NoSerie']) && $_GET['NoSerie'] != "" && $_GET['NoSerie'] != 0) {
    $NoSerie = $_GET['NoSerie'];
    if ($where === "") {
        $where = "WHERE (SELECT CASE WHEN e2.IdEstado = 2 THEN ( SELECT group_concat( ClaveEspEquipo SEPARATOR ', ') 
        FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) LIKE '%$NoSerie%'";
    } else {
        $where .= " AND cb.IdLineaNegocio=" . $_GET['IdLN'];
    }
}

if ((isset($_GET['FechaInicio']) && $_GET['FechaInicio'] != "") || (isset($_GET['FechaFin']) && $_GET['FechaFin'] != "")) {
    if (isset($_GET['FechaInicio']) && $_GET['FechaInicio'] != "" && isset($_GET['FechaFin']) && $_GET['FechaFin'] != "") {
        $FechaInicio = $_GET['FechaInicio'];
        $FechaFin = $_GET['FechaFin'];
        if ($where != "") {
            $where .= " AND t.FechaHora BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
        } else {
            $where = "WHERE t.FechaHora BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
        }
    } else if (isset($_GET['FechaInicio']) && $_GET['FechaInicio'] != "") {
        $FechaInicio = $_GET['FechaInicio'];
        if ($where != "") {
            $where .= " AND t.FechaHora >= '$FechaInicio'";
        } else {
            $where = "WHERE t.FechaHora >= '$FechaInicio'";
        }
    } else if (isset($_GET['FechaFin']) && $_GET['FechaFin'] != "") {
        $FechaFin = $_GET['FechaFin'];
        if ($where != "") {
            $where .= " AND t.FechaHora <= '$FechaFin'";
        } else {
            $where = "WHERE t.FechaHora <= '$FechaFin'";
        }
    }
}

if (isset($_GET['Prioridad']) && $_GET['Prioridad'] != 0) {
    if ($where != "") {
        $where .= " AND t.Prioridad = " . $_GET['Prioridad'];
    } else {
        $where = "WHERE t.Prioridad = " . $_GET['Prioridad'];
    }
}

if (isset($_GET['estadoT']) && $_GET['estadoT'] != "") {
    if ($where != "") {
        $where .= " AND t.EstadoDeTicket = " . $_GET['estadoT'];
    } else {
        $where = "WHERE t.EstadoDeTicket = " . $_GET['estadoT'];
    }
}

if ($where != "") {
    $where .= " AND cl.Suspendido = 0";
} else {
    $where = " WHERE cl.Suspendido = 0";
}

if ((isset($_GET['cerrado']) && $_GET['cerrado'] != 0) || (isset($_GET['estadoT']) && $_GET['estadoT'] == 2)) {
    $cerradoTicket = "";

    if (isset($_GET['cancelado']) && $_GET['cancelado'] != 0) {
        $having = "";
    } else {
        $having = " HAVING ((IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)) ";
    }
} else {
    if (isset($_GET['cancelado']) && $_GET['cancelado'] != 0) {
        $having = " HAVING ((IdEstatusAtencion <> 16) OR ISNULL(IdEstatusAtencion)) ";
    }
}

if (isset($_GET['moroso']) && $_GET['moroso'] != 0) {
    $morososTicket = "";
}

if ((isset($_GET['cancelado']) && $_GET['cancelado'] != 0) || (isset($_GET['estadoT']) && $_GET['estadoT'] == 4)) {
    $canceladoTicket = "";
}

if (isset($_GET['area']) && $_GET['area'] != "") {
    $areaAtencion = " AreaAtencion = " . $_GET['area'] . " AND ";
}

if (isset($_GET['tipoReporte']) && $_GET['tipoReporte'] != "") {
    $tipoReporte = " TipoReporte = " . $_GET['tipoReporte'] . " AND ";
}

if (isset($_GET['cliente']) && $_GET['cliente'] != "") {
    $cliente = $_GET['cliente'];
}

if (isset($_GET['estado']) && $_GET['estado'] != "") {
    if (isset($_GET['tipo_busqueda_estado']) && $_GET['tipo_busqueda_estado'] == "0") {//Se busca en la ultima nota
        $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdEstatusAtencion = " . $_GET['estado'] . " AND nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
    } else {//Se busca en todos los tickets
        $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = " . $_GET['estado'] . ")";
    }

    if ($_GET['estado'] == "16") {/* Si se selecciona el estado de cerrado, habiliatar el checkbox de cerrado también */
        $cerradoTicket = "";
        if ((isset($_GET['cancelado']) && $_GET['cancelado'] != 0) || (isset($_GET['estadoT']) && $_GET['estadoT'] == 4)) {
            $having = "";
        } else {
            $having = " HAVING ((IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)) ";
        }
    }
}

if (isset($_GET['NoGuia']) && $_GET['NoGuia'] != "") {
    $NoGuia = $_GET['NoGuia'];
    if ($having != "") {
        $having .= " AND NoGuia LIKE '%$NoGuia%' ";
    } else {
        $having = " HAVING NoGuia LIKE '%$NoGuia%' ";
    }
}

$idCampanias = "(CASE WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 2 THEN CONCAT('Origen: ',e4.Nombre ,' Destino:',e5.Nombre) WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 1 THEN CONCAT('Origen: ',e5.Nombre ,' Destino:',e4.Nombre) 
                     WHEN !ISNULL (ces.idEspecial) THEN CONCAT('Origen: ',e6.Nombre,'(',ces.Origen,') Destino:(',ces.Destino,')')ELSE NULL END) AS LugarDestino, 
                     
                     ces.Origen AS Referencia, t.UsuarioCreacion, t.ClaveCliente,fp.Descripcion AS FormaPago,fpre.FolioTimbrado,
                     (CASE WHEN ces.TipoServicio = 0 THEN 'Reservado'  WHEN ces.TipoServicio = 1 THEN 'Al momento' ELSE NULL END) AS TipoServicio,
                     (CASE WHEN !ISNULL(cutkk.IdUsuario) THEN cutkk.Loggin ELSE 'No Asignado' END) AS CveOperador,
                     CONCAT(cutkk.Nombre,' ',cutkk.ApellidoPaterno,' ',cutkk.ApellidoMaterno) AS Operador,
                     
                     
                     (CASE WHEN !ISNULL(cp.idPlantilla) THEN cturp.descripcion WHEN !ISNULL(ces.idEspecial) THEN ctur.descripcion ELSE NULL END) AS Turno,
                     
                     (CASE WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 2 THEN 
                      CONCAT(dut.Calle ,' ',dut.NoExterior,' Col. ', dut.Colonia,' ',dut.Estado,' ',dut.Estado,' ',dut.Delegacion,' ',dut.CodigoPostal) WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 1 THEN 
                      CONCAT(dcc.Calle,' ',dcc.NoExterior,'',dcc.Colonia,' ',dcc.Estado,' ',dcc.Estado,' ',dcc.Delegacion) WHEN !ISNULL (ces.idEspecial) THEN 
                      CONCAT(ces.Calle_or,' ',ces.NoExterior_or,', Col. ',ces.Colonia_or,', ',cciu_or.Ciudad,', ',ces.Delegacion_or,',  C.P.',ces.CodigoPostal_or)ELSE NULL END) AS Origen,

                     (CASE WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 2 THEN 
                      CONCAT(dcc.Calle,' ',dcc.NoExterior,'',dcc.Colonia,' ',dcc.Estado,' ',dcc.Estado,' ',dcc.Delegacion) WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 1 THEN 
                      CONCAT(dut.Calle ,' ',dut.NoExterior,' Col. ', dut.Colonia,' ',dut.Estado,' ',dut.Estado,' ',dut.Delegacion,' ',dut.CodigoPostal) WHEN !ISNULL (ces.idEspecial) THEN 
                      CONCAT(ces.Calle_des,' ',ces.NoExterior_des,', Col. ',ces.Colonia_des,', ',cciu_des.Ciudad,', ',ces.Delegacion_des,', C.P.',ces.CodigoPostal_des)ELSE NULL END) AS Destino,

                     (CASE WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 2 THEN dcc.Latitud WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 1 THEN dut.Latitud  WHEN !ISNULL (ces.idEspecial) THEN ces.Latitud_or ELSE NULL END) AS LatitudO,
                     (CASE WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 2 THEN dut.Latitud WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 1 THEN dcc.Latitud WHEN !ISNULL (ces.idEspecial) THEN ces.Latitud_des ELSE NULL END) AS LatitudD,
                     (CASE WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 2 THEN dcc.Longitud WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 1 THEN dut.Longitud WHEN !ISNULL (ces.idEspecial) THEN ces.Longitud_or ELSE NULL END) AS LongitudO,
                     (CASE WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 2 THEN dut.Longitud WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 1 THEN dcc.Longitud WHEN !ISNULL (ces.idEspecial) THEN ces.Longitud_des ELSE NULL END) AS LongitudD,
                     CONCAT(usu.Nombre,' ',usu.ApellidoPaterno,' ',usu.ApellidoMaterno) AS DatosUsuario, usu.Telefono AS Telefono, usu.correo AS Correo,usu.CostoFijo,
                     ca.Descripcion AS CampaniaP, ca2.Descripcion AS CampaniaE, t.FechaHora AS FechaHoraCompleta,
                     cfc.Nombre AS FormaContacto, 
                    (SELECT GROUP_CONCAT(DISTINCT(cnest7.NoBoleto)) FROM c_notaticket AS cnest7 WHERE cnest7.IdTicket=t.IdTicket GROUP BY cnest7.IdTicket) AS NoBoleto,
                    (SELECT cnest3.Km FROM c_notaticket AS cnest3 WHERE cnest3.IdNotaTicket=(SELECT MAX(auxcn.IdNotaTicket) FROM c_notaticket AS auxcn WHERE auxcn.IdTicket=t.IdTicket AND !ISNULL(auxcn.Km))) AS Km,
                    (SELECT cnest4.TiempoEsperaReal FROM c_notaticket AS cnest4 WHERE cnest4.IdNotaTicket=(SELECT MAX(auxcn1.IdNotaTicket) FROM c_notaticket AS auxcn1 WHERE auxcn1.IdTicket=t.IdTicket AND !ISNULL(auxcn1.TiempoEsperaReal))) AS TiempoEspera,
                    (SELECT SUM(kve.cantidad) AS suma FROM `c_notaticket` AS nt LEFT JOIN k_serviciove AS kve ON kve.IdNotaTicket = nt.IdNotaTicket WHERE nt.IdTicket = t.IdTicket AND nt.IdViatico = 1) AS Casetas,
                    (SELECT SUM(kve.cantidad) AS suma FROM `c_notaticket` AS nt LEFT JOIN k_serviciove AS kve ON kve.IdNotaTicket = nt.IdNotaTicket WHERE nt.IdTicket = t.IdTicket AND nt.IdViatico = 2) AS Gasolina,
                    (SELECT SUM(kve.cantidad) AS suma FROM `c_notaticket` AS nt LEFT JOIN k_serviciove AS kve ON kve.IdNotaTicket = nt.IdNotaTicket WHERE nt.IdTicket = t.IdTicket AND nt.IdViatico = 3) AS Estacionamiento,
                    (SELECT TIME(cnest5.FechaHora) FROM c_notaticket AS cnest5 WHERE cnest5.IdNotaTicket=(SELECT MAX(cnot.IdNotaTicket) FROM c_notaticket AS cnot WHERE cnot.IdTicket=t.IdTicket AND cnot.IdEstatusAtencion=241)) AS HoraOrigen,
                    (SELECT TIME(cnest5.FechaHora) FROM c_notaticket AS cnest5 WHERE cnest5.IdNotaTicket=(SELECT MAX(cnot.IdNotaTicket) FROM c_notaticket AS cnot WHERE cnot.IdTicket=t.IdTicket AND cnot.IdEstatusAtencion=284)) AS HoraDestino,
                    (SELECT cnest5.DiagnosticoSol FROM c_notaticket AS cnest5 WHERE cnest5.IdNotaTicket=(SELECT MAX(cnot.IdNotaTicket) FROM c_notaticket AS cnot WHERE cnot.IdTicket=t.IdTicket AND cnot.IdEstatusAtencion=288)) AS TU,
                    (SELECT ( CASE WHEN COUNT(DISTINCT PathImagen, 'NULL') > 0 THEN 'SI' ELSE 'NO' END) FROM c_notaticket WHERE IdTicket=t.IdTicket) AS NumFoto,
                    (SELECT (CASE WHEN COUNT(cnest6.IdNotaTicket)>0 THEN 'SI' ELSE 'NO' END) FROM c_notaticket AS cnest6 WHERE cnest6.IdTicket=t.IdTicket AND cnest6.IdEstatusAtencion=278) AS Vale,
                    (SELECT GROUP_CONCAT(cnest6.DiagnosticoSol) FROM c_notaticket AS cnest6 WHERE cnest6.IdTicket=t.IdTicket AND cnest6.IdEstatusAtencion=92 GROUP BY cnest6.IdTicket) AS Comentario,
                    clg.Tarifa,";
$campaniasLEFT = "LEFT JOIN k_plantilla_asistencia AS kpa ON kpa.idK_Plantilla_asistencia = (SELECT MAX(idK_Plantilla_asistencia) FROM k_plantilla_asistencia WHERE IdTicket = t.IdTicket)
                      LEFT JOIN k_plantilla AS kp ON kp.idK_Plantilla = kpa.idK_Plantilla
                      LEFT JOIN c_plantilla AS cp ON cp.idPlantilla = kp.idPlantilla
                      LEFT JOIN c_especial AS ces ON ces.idTicket = t.IdTicket
                      LEFT JOIN c_ciudades AS cciu_des ON cciu_des.IdCiudad = ces.Estado_des
                      LEFT JOIN c_ciudades AS cciu_or ON cciu_or.IdCiudad = ces.Estado_or
                      LEFT JOIN c_area AS ca ON cp.idCampania=ca.IdArea
                      LEFT JOIN c_area AS ca2 ON ces.idCampania=ca2.IdArea
                      LEFT JOIN c_usuario AS usu ON usu.Loggin = t.NoSerieEquipo
                      LEFT JOIN c_formapago AS fp ON fp.IdFormaPago = usu.IdFormaPago
                      LEFT JOIN c_domicilio_usturno AS dut ON dut.IdUsuario = usu.IdUsuario
                      LEFT JOIN c_estado AS e4 ON ca.IdEstado = e4.IdEstado
                      LEFT JOIN c_estado AS e5 ON dut.IdArea = e5.IdEstado
                      LEFT JOIN c_estado AS e6 ON ces.Cuadrante = e6.IdEstado
                      LEFT JOIN c_ciudades AS cciu ON cciu.IdCiudad = dut.Estado
                      LEFT JOIN c_domicilio AS dcc ON dcc.IdDomicilio = (SELECT MIN(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = cc.ClaveCentroCosto)
                      LEFT JOIN c_turno AS ctur ON ces.idTurno = ctur.idTurno
                      LEFT JOIN c_turno AS cturp ON cp.idTurno = cturp.idTurno
                      LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdTicket = t.IdTicket
                      LEFT JOIN c_usuario AS cutkk ON ktt.IdUsuario = cutkk.IdUsuario
                      LEFT JOIN c_formacontacto AS cfc ON ces.IdFormaContacto=cfc.IdFormaContacto
                      LEFT JOIN c_notaticket AS cnest ON cnest.IdTicket=t.IdTicket AND cnest.IdEstatusAtencion=277
                        ";

if ($idTicket == "") {
    $consulta = "SELECT
                                $idCampanias
                                b.id_bitacora,
                                t.IdTicket,
                                t.NoTicketCliente,
                                cl.Suspendido,
                                NoTicketDistribuidor,
                                DATE(t.FechaHora) AS FechaHora,
                                t.DescripcionReporte,
                                t.NombreCentroCosto,
                                cc.Nombre AS NombreCC,
                                t.TipoReporte,
                                (CASE WHEN (!ISNULL(nt.IdNotaTicket) AND nt.IdEstatusAtencion = 16) THEN DATEDIFF(nt.FechaHora,t.FechaCreacion) ELSE DATEDIFF(NOW(),t.FechaCreacion) END) AS DiferenciaDias,
                                (SELECT CASE WHEN e2.IdEstado = 2 THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
                                DATEDIFF(NOW(),t.FechaHora) AS diferencia,
                                t.NombreCliente,
                                cl.NombreRazonSocial,
                                clg.Nombre AS NombreGrupo,
                                cl.IdEstatusCobranza,
                                e.IdEstadoTicket AS estadoTicket,                                         
                                tc.IdTipoCliente AS tipoCliente,
                                e2.Nombre AS area,
                                e2.IdEstado AS idArea,                                
                                (SELECT CASE WHEN !ISNULL(cgz.NombreZona) THEN cgz.NombreZona WHEN !ISNULL(cgz3.NombreZona) THEN cgz3.NombreZona ELSE cgz2.NombreZona END) AS ubicacionTicket,
                                e3.Nombre AS estadoNota,
                                nt.IdEstatusAtencion,
                                nt.DiagnosticoSol,
                                cee.color,
                                col.Hexadecimal,
                                t.NoGuia AS NoGuia2,
                                nt.FechaHora AS FechaNota,
                                (SELECT CASE WHEN ISNULL(nt.UsuarioUltimaModificacion) THEN t.UsuarioUltimaModificacion ELSE nt.UsuarioUltimaModificacion END) as UltimoUsuarioNota,
                                t.Resurtido,
                                (SELECT GROUP_CONCAT(DISTINCT(k_enviotoner.NoGuia) SEPARATOR ', ') AS NoGuia FROM `k_enviotoner`
                                INNER JOIN c_pedido ON c_pedido.IdPedido = k_enviotoner.IdSolicitud
                                INNER JOIN c_ticket ON c_ticket.IdTicket = c_pedido.IdTicket
                                WHERE c_ticket.IdTicket = t.IdTicket GROUP BY c_ticket.IdTicket) AS NoGuia
                                FROM c_ticket AS t
                                INNER JOIN c_estadoticket AS e ON $tipoReporte $areaAtencion $canceladoTicket $cerradoTicket e.IdEstadoTicket = t.EstadoDeTicket $cliente
                                LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket
                                LEFT JOIN c_zona AS cgz ON cgz.ClaveZona = dt.ClaveZona
                                $tipo_join JOIN c_estado AS e1 ON $estado_falla e1.IdEstado = t.TipoReporte
                                INNER JOIN c_cliente AS cl ON $morososTicket cl.ClaveCliente = t.ClaveCliente $vendedor $tfs
                                LEFT JOIN c_zona AS cgz2 ON cgz2.ClaveZona = cl.ClaveZona 
                                LEFT JOIN c_centrocosto AS cc ON t.ClaveCentroCosto = cc.ClaveCentroCosto
                                LEFT JOIN c_zona AS cgz3 ON cgz3.ClaveZona = cc.ClaveZona
                                LEFT JOIN c_clientegrupo AS clg ON clg.ClaveGrupo = cl.ClaveGrupo
                                LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
                                $tipo_join_estado JOIN c_estado AS e2 ON $estado e2.IdEstado = t.AreaAtencion                                
                                $estadoNota
                                LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
                                LEFT JOIN c_escalamientoEstado AS cee ON (cee.idEstado = nt.IdEstatusAtencion AND cee.prioridad = t.Prioridad)
                                LEFT JOIN c_prioridadticket AS pt ON t.Prioridad = pt.IdPrioridad
                                LEFT JOIN c_color AS col ON pt.IdColor = col.IdColor
                                $campaniasLEFT
                                LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
                                LEFT JOIN k_ticketnr AS knr ON knr.IdTicket = t.IdTicket
                                LEFT JOIN c_factura AS nr ON nr.IdFactura = knr.IdNotaRemision
                                LEFT JOIN c_folio_prefactura AS fpre ON fpre.Folio = nr.Folio AND fpre.IdEmisor = nr.RFCEmisor AND !ISNULL(fpre.FolioTimbrado)
                                $tecnico 
                                $where
                                GROUP BY t.IdTicket
                                $having
                                ORDER BY IdTicket";
//    if (!$tiene_filtro) {
//        $consulta.=" DESC LIMIT 0,500";
//    }
    $consulta.=";";
} else {
    $consulta = "SELECT
                                $idCampanias
                                b.id_bitacora,
                                t.IdTicket,                                
                                t.NoTicketCliente,
                                t.NoTicketDistribuidor,
                                DATE(t.FechaHora) AS FechaHora,
                                t.DescripcionReporte,
                                t.NombreCentroCosto,
                                cc.Nombre AS NombreCC,
                                t.TipoReporte,
                                t.NoGuia AS NoGuia2,
                                (SELECT CASE WHEN e2.IdEstado = 2 
                                THEN(SELECT group_concat(ClaveEspEquipo SEPARATOR ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket)
                                ELSE t.NoSerieEquipo END) AS NumSerie,
                                DATEDIFF(NOW(), t.FechaHora) AS diferencia,
                                t.NombreCliente,
                                cl.NombreRazonSocial,
                                clg.Nombre AS NombreGrupo,
                                cl.IdEstatusCobranza,
                                cl.Suspendido,
                                e.IdEstadoTicket AS estadoTicket,                                
                                tc.IdTipoCliente AS tipoCliente,
                                e2.Nombre AS area,
                                e2.IdEstado AS idArea,
                                (SELECT CASE WHEN !ISNULL(cgz.NombreZona) THEN cgz.NombreZona WHEN !ISNULL(cgz3.NombreZona) THEN cgz3.NombreZona ELSE cgz2.NombreZona END) AS ubicacionTicket,
                                e3.Nombre AS estadoNota,
                                nt.IdEstatusAtencion,
                                nt.DiagnosticoSol,
                                nt.FechaHora AS FechaNota,
                                cee.color,
                                col.Hexadecimal,
                                (SELECT CASE WHEN ISNULL(nt.UsuarioUltimaModificacion) THEN t.UsuarioUltimaModificacion ELSE nt.UsuarioUltimaModificacion END) as UltimoUsuarioNota,
                                t.Resurtido,
                                (SELECT GROUP_CONCAT(DISTINCT(k_enviotoner.NoGuia) SEPARATOR ', ') AS NoGuia FROM `k_enviotoner`
                                INNER JOIN c_pedido ON c_pedido.IdPedido = k_enviotoner.IdSolicitud
                                INNER JOIN c_ticket ON c_ticket.IdTicket = c_pedido.IdTicket
                                WHERE c_ticket.IdTicket = t.IdTicket GROUP BY c_ticket.IdTicket) AS NoGuia
                                FROM
                                c_ticket AS t
                                INNER JOIN c_estadoticket AS e ON e.IdEstadoTicket = t.EstadoDeTicket
                                LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket
                                LEFT JOIN c_zona AS cgz ON cgz.ClaveZona = dt.ClaveZona
                                LEFT JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte
                                INNER JOIN c_cliente AS cl ON cl.ClaveCliente = t.ClaveCliente $vendedor $tfs
                                LEFT JOIN c_zona AS cgz2 ON cgz2.ClaveZona = cl.ClaveZona
                                LEFT JOIN c_centrocosto AS cc ON t.ClaveCentroCosto = cc.ClaveCentroCosto
                                LEFT JOIN c_zona AS cgz3 ON cgz3.ClaveZona = cc.ClaveZona
                                LEFT JOIN c_clientegrupo AS clg ON clg.ClaveGrupo = cl.ClaveGrupo
                                LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
                                $tipo_join_estado JOIN c_estado AS e2 ON $estado e2.IdEstado = t.AreaAtencion                                
                                LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (
                                SELECT
                                        MAX(IdNotaTicket)
                                FROM
                                        c_notaticket AS nt2
                                WHERE
                                        nt2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion!=92
                                )
                                LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
                                LEFT JOIN c_escalamientoEstado AS cee ON (cee.idEstado = nt.IdEstatusAtencion AND cee.prioridad = t.Prioridad)
                                LEFT JOIN c_prioridadticket AS pt ON t.Prioridad = pt.IdPrioridad
                                LEFT JOIN c_color AS col ON pt.IdColor = col.IdColor
                                $campaniasLEFT
                                LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
                                LEFT JOIN k_ticketnr AS knr ON knr.IdTicket = t.IdTicket
                                LEFT JOIN c_factura AS nr ON nr.IdFactura = knr.IdNotaRemision
                                LEFT JOIN c_folio_prefactura AS fpre ON fpre.Folio = nr.Folio AND fpre.IdEmisor = nr.RFCEmisor AND !ISNULL(fpre.FolioTimbrado)
                                $tecnico ";
    if (is_numeric($idTicket)) {
        $consulta.=" WHERE (t.IdTicket = $idTicket OR NoTicketCliente = '$idTicket' OR NoTicketDistribuidor = '$idTicket') ";
    } else {
        $consulta.=" WHERE (NoTicketCliente = '$idTicket' OR NoTicketDistribuidor = '$idTicket') ";
    }
    $consulta.=" GROUP BY t.IdTicket ORDER BY IdTicket;";
}

$query = $catalogo->obtenerLista($consulta);
$tickets = array();
while ($rs = mysql_fetch_array($query)) {
    $array_valores = array();
    $sinvalor = '';
    $nota = '';
    $consulta = "SELECT * FROM c_tarifa WHERE Tarifa='" . $rs['Tarifa'] . "'";
    array_push($tickets, $rs['IdTicket']);
    array_push($array_valores, $rs['FechaHoraCompleta']);
    array_push($array_valores, $rs['DatosUsuario']);
    array_push($array_valores, $rs['NombreRazonSocial']);
    array_push($array_valores, $rs['NombreCC']);
    array_push($array_valores, $rs['Telefono']);

    array_push($array_valores, $rs['Correo']);
    array_push($array_valores, $rs['FormaContacto']);
    array_push($array_valores, $rs['Origen']);
    array_push($array_valores, $rs['Referencia']);
    array_push($array_valores, $rs['FechaHoraCompleta']);
    array_push($array_valores, $rs['Destino']);
    array_push($array_valores, $rs['FormaPago']);
    array_push($array_valores, $rs['IdTicket']);
    array_push($array_valores, $rs['UsuarioCreacion']);

    array_push($array_valores, $rs['Turno']);
    array_push($array_valores, $rs['ClaveCliente']);
    array_push($array_valores, $rs['CveOperador']);
    array_push($array_valores, $rs['Operador']);
    array_push($array_valores, $rs['estadoNota']);
    array_push($array_valores, $rs['NoBoleto']);
    if(isset($rs['Km']) && !empty($rs['Km'])){
        array_push($array_valores, number_format($rs['Km'],2));
    }else{
        array_push($array_valores, 0);
    }
    if(isset($rs['TiempoEspera']) && !empty($rs['TiempoEspera'])){
        array_push($array_valores, number_format($rs['TiempoEspera'],0));
    }else{
        array_push($array_valores, 0);
    }
    
    if(isset($rs['Casetas']) && !empty($rs['Casetas'])){
        array_push($array_valores, $rs['Casetas']);
    }else{
        array_push($array_valores, 0);
    }
        
    if(isset($rs['Estacionamiento']) && !empty($rs['Estacionamiento'])){
        array_push($array_valores, $rs['Estacionamiento']);
    }else{
        array_push($array_valores, 0);
    }
    
    if(isset($rs['Gasolina']) && !empty($rs['Gasolina'])){
        array_push($array_valores, $rs['Gasolina']);
    }else{
        array_push($array_valores, 0);
    }
    
    array_push($array_valores, $rs['CostoFijo']); //Importe
    array_push($array_valores, $rs['TU']); //TU- Tarifa Unidad de Apoyo
    array_push($array_valores, $rs['TipoServicio']); //TU- Tarifa Unidad de Apoyo
    array_push($array_valores, $rs['HoraOrigen']); //HoraOrigen
    array_push($array_valores, $rs['HoraDestino']); //HoraDestino
    array_push($array_valores, $rs['Comentario']); //Comentarios
    array_push($array_valores, $rs['NumFoto']); //EnvioFoto

    array_push($array_valores, $rs['Vale']); //ValeFisico
    array_push($array_valores, $rs['FolioTimbrado']);//Factura No.
    
    if($rs['NumFoto'] == "SI" && $rs['IdEstatusAtencion'] == "51"){
        array_push($array_valores, "SI");
    }else{
        array_push($array_valores, "NO");
    }
    
    array_push($array_valores, "");    
    $writer->writeSheetRow($hoja, $array_valores);
}

$cabeceras = array('Servicio' => 'string', 'Nota/Comentario' => "string", 'Evento' => "string", 'FechaHora Nota' => "datehora", 'Registró' => "string");
$hoja = "EventosServicio";
$writer->writeSheetHeader($hoja, $cabeceras);

foreach ($tickets as $value) {
    $consulta = "SELECT cn.IdTicket, cn.DiagnosticoSol, ce.Nombre, cn.FechaHora, cn.UsuarioUltimaModificacion AS Registro FROM c_notaticket AS cn LEFT JOIN c_estado AS ce ON cn.IdEstatusAtencion=ce.IdEstado WHERE IdTicket=" . $value . ";";
    $query = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($query)) {
        $array_valores2 = array();
        array_push($array_valores2, $rs['IdTicket']);
        array_push($array_valores2, $rs['DiagnosticoSol']);
        array_push($array_valores2, $rs['Nombre']);
        array_push($array_valores2, $rs['FechaHora']);
        array_push($array_valores2, $rs['Registro']);
        $writer->writeSheetRow($hoja, $array_valores2);
    }
}

$writer->writeToStdOut();
exit(0);
