<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
ini_set("memory_limit","512M");
set_time_limit (0);

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/TFSGrupoCliente.class.php");
require_once('../WEB-INF/Classes/PHPExcel/IOFactory.php');
require_once('../WEB-INF/Classes/PHPExcel.php');

function cellColor($objPHPExcel, $cells, $color) {
    $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()
            ->applyFromArray(array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => $color)
    ));
}

function getStyle($bold, $color, $size, $name, $cursive) {
    $styleArray = array(
        'font' => array(
            'bold' => $bold,
            'italic' => $cursive,
            'color' => array('rgb' => $color),
            'size' => $size,
            'name' => $name
        ),
        'alignment' => array(
            'wrap' => true,
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    ));
    return $styleArray;
}


$dias = array("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado");
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

$fecha = $dias[date('w')] . ", " . date('j') . " de " . $meses[date('n') - 1] . " del " . date('Y');
/* Inicializamos la clase */
$catalogo = new Catalogo();
$tiene_filtro = false;
$usuario = new Usuario();

$cerradoTicket = "t.EstadoDeTicket <> 2 AND ";
$having = " HAVING (IdEstatusAtencion <> 16 AND IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)";
$checked = "";
$morososTicket = "cl.IdEstatusCobranza <> 2 AND ";
$checkedMoroso = "";
$canceladoTicket = "t.EstadoDeTicket <> 4 AND ";
$checkedCancelado = "";
$tipoReporte = "";
$areaAtencion = "";
$cliente = "";
$colorPOST = "";
$estadoNota = "LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
$idTicket = "";
$NoSerie = "";
$FechaInicio = "";
$FechaFin = "";
$Where = "";

$usuario->setId($_SESSION['idUsuario']);

$clientes_permitidos = $usuario->obtenerNegociosDeUsuario();
$array_clientes = implode("','", $clientes_permitidos);
if (!empty($array_clientes)) {
    $array_clientes = "'$array_clientes'";
}


if (isset($_POST['busqueda_ticket']) && $_POST['busqueda_ticket'] != "") {
    $tiene_filtro = true;
    $idTicket = $_POST['busqueda_ticket'];
    /* Si se busco un ticket en particular, habilitamos cerrados, morosos y cancelados */
    $checked = "checked='checked'";
    $checkedMoroso = "checked='checked'";
    $checkedCancelado = "checked='checked'";
}

if (isset($_POST['num_serie']) && $_POST['num_serie'] != "") {
    $tiene_filtro = true;
    $NoSerie = $_POST['num_serie'];
    $Where = "WHERE (SELECT CASE WHEN e2.Suministro = 1 THEN ( SELECT group_concat( ClaveEspEquipo SEPARATOR ', ') 
        FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) LIKE '%$NoSerie%'";
}

if ((isset($_POST['fecha_inicio']) && $_POST['fecha_inicio'] != "") || (isset($_POST['fecha_fin']) && $_POST['fecha_fin'] != "")) {
    $tiene_filtro = true;
    if (isset($_POST['fecha_inicio']) && $_POST['fecha_inicio'] != "" && isset($_POST['fecha_fin']) && $_POST['fecha_fin'] != "") {
        $FechaInicio = $_POST['fecha_inicio'];
        $FechaFin = $_POST['fecha_fin'];
        if ($Where != "") {
            $Where .= " AND t.FechaHora BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
        } else {
            $Where = "WHERE t.FechaHora BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
        }
    } else if (isset($_POST['fecha_inicio']) && $_POST['fecha_inicio'] != "") {
        $FechaInicio = $_POST['fecha_inicio'];
        if ($Where != "") {
            $Where .= " AND t.FechaHora >= '$FechaInicio'";
        } else {
            $Where = "WHERE t.FechaHora >= '$FechaInicio'";
        }
    } else if (isset($_POST['fecha_fin']) && $_POST['fecha_fin'] != "") {
        $FechaFin = $_POST['fecha_fin'];
        if ($Where != "") {
            $Where .= " AND t.FechaHora <= '$FechaFin'";
        } else {
            $Where = "WHERE t.FechaHora <= '$FechaFin'";
        }
    }
}

/* No mostrar suspendidos */
if ($Where != "") {
    $Where .= " AND cl.Suspendido = 0";
} else {
    $Where = " WHERE cl.Suspendido = 0";
}

if (isset($_POST['ticket_cerrado']) && $_POST['ticket_cerrado'] != "false") {
    $cerradoTicket = "";

    if (isset($_POST['ticket_cancelado']) && $_POST['ticket_cancelado'] != "false") {
        $having = "";
    } else {
        $having = " HAVING (IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion) ";
    }

    $checked = "checked='checked'";
} else {
    if (isset($_POST['ticket_cancelado']) && $_POST['ticket_cancelado'] != "false") {
        $having = " HAVING (IdEstatusAtencion <> 16) OR ISNULL(IdEstatusAtencion) ";
    }
}

if (isset($_POST['ticket_moroso']) && $_POST['ticket_moroso'] != "false") {
    $morososTicket = "";
    $checkedMoroso = "checked='checked'";
}

if (isset($_POST['ticket_cancelado']) && $_POST['ticket_cancelado'] != "false") {
    $canceladoTicket = "";
    $checkedCancelado = "checked='checked'";
}

if (isset($_POST['area_ticket']) && $_POST['area_ticket'] != "") {
    $tiene_filtro = true;
    $areaAtencion = " AreaAtencion = " . $_POST['area_ticket'] . " AND ";
}

if (isset($_POST['reporte_ticket']) && $_POST['reporte_ticket'] != "") {
    $tiene_filtro = true;
    $tipoReporte = " TipoReporte = " . $_POST['reporte_ticket'] . " AND ";
}

if (isset($_POST['cliente_ticket']) && $_POST['cliente_ticket'] != "") {
    $tiene_filtro = true;
    $array = $_POST['cliente_ticket'];
    $cli = "";
    foreach ($array as $value) {
        $value = substr($value, 0, strlen($value) - 5);
        $value = ltrim($value);
        $value = rtrim($value);
        $cli.="'" . $value . "',";
    }
    $cli = substr($cli, 0, strlen($cli) - 1);
    $cliente = " AND t.NombreCliente IN (" . $cli . ")";
    $cliente_array = $_POST['cliente_ticket'];
    $cliente_array[0] = substr($cliente_array[0], 1, strlen($cliente_array[0]));
    $cliente_array[count($cliente_array) - 1] = substr($cliente_array[count($cliente_array) - 1], 0, strlen($cliente_array[count($cliente_array) - 1]) - 1);
}else if (!empty($clientes_permitidos)) {
    $tiene_filtro = true;
    $cliente = " AND t.ClaveCliente IN ($array_clientes)";
    $cliente_array = explode("','", $_POST['cliente']);
    $cliente_array[0] = substr($cliente_array[0], 1, strlen($cliente_array[0]));
    $cliente_array[count($cliente_array) - 1] = substr($cliente_array[count($cliente_array) - 1], 0, strlen($cliente_array[count($cliente_array) - 1]) - 1);
}

if (isset($_POST['ticket_color']) && $_POST['ticket_color'] != "") {
    $tiene_filtro = true;
    $colorPOST = $_POST['ticket_color'];
}

if (isset($_POST['estado_ticket']) && $_POST['estado_ticket'] != "") {
    $tiene_filtro = true;
    //$estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdEstatusAtencion = " . $_POST['estado_ticket'] . " AND nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
    
    if (isset($_POST['ultimo_estado']) && $_POST['ultimo_estado'] == "0") {//Se busca en la ultima nota
        $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdEstatusAtencion = " . $_POST['estado_ticket'] . " AND nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
    } else {//Se busca en todos los tickets
        $checked_todo = "checked='checked'";
        $checked_ultimo = "";
        $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = " . $_POST['estado_ticket'] . ")";
    }
    
    
    if ($_POST['estado_ticket'] == "16") {/* Si se selecciona el estado de cerrado, habiliatar el checkbox de cerrado también */
        $cerradoTicket = "";
        if (isset($_POST['ticket_cancelado']) && $_POST['ticket_cancelado'] != "false") {
            $having = "";
        } else {
            $having = " HAVING (IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion) ";
        }
        $checked = "checked='checked'";
    }
}

$idUsuario = $_SESSION['idUsuario'];
if($usuario->getRegistroById($idUsuario)){//Buscamos las areas de atencion a las que está asociado este puesto
    $consulta = "SELECT GROUP_CONCAT(CONVERT(IdEstado, CHAR(8)) SEPARATOR ',') estados FROM `k_areapuesto` WHERE IdPuesto = ".$usuario->getPuesto().";";
    $result = $catalogo->obtenerLista($consulta);
    if(mysql_numrows($result) > 0){
        while($rs = mysql_fetch_array($result)){
            if(!empty($rs['estados'])){
                $estado = " e2.IdEstado IN (".$rs['estados'].") AND ";
            }else{
                $estados = "";
            }
        }
    }else{
        $estado = "";
    }
}else{
    $estado = "";
}

/* Verificamos el puesto del usuario */
//$estado = "";
$estado_falla = "";
$tipo_join = "LEFT";
$tecnico = "";

//GERENTE DE SW
if ($usuario->isUsuarioPuesto($idUsuario, 19)) {
    //$estado = "e2.IdEstado = 6 AND ";
    $estado_falla = "e1.IdEstado <> 15 AND ";
    $tipo_join = "INNER";
}

//GERENTE DE HW
if ($usuario->isUsuarioPuesto($idUsuario, 17)) {
    //$estado = "e2.IdEstado = 5 AND ";
    $estado_falla = "e1.IdEstado <> 15 AND ";
    $tipo_join = "INNER";
}

//TECNICO SW
if ($usuario->isUsuarioPuesto($idUsuario, 20)) {
    //$estado = "e2.IdEstado = 6 AND ";
    $estado_falla = "e1.IdEstado <> 15 AND ";
    $tipo_join = "INNER";
    $tecnico = "INNER JOIN k_tecnicoticket AS ktt ON ktt.IdUsuario = $idUsuario AND ktt.tipo = 2 AND ktt.IdTicket = t.IdTicket";
}

//TECNICO HW
if ($usuario->isUsuarioPuesto($idUsuario, 18)) {
    //$estado = "e2.IdEstado = 5 AND ";
    $estado_falla = "e1.IdEstado <> 15 AND ";
    $tipo_join = "INNER";
    $tecnico = "INNER JOIN k_tecnicoticket AS ktt ON ktt.IdUsuario = $idUsuario AND ktt.tipo = 1 AND ktt.IdTicket = t.IdTicket";
}

//Vendedor
$vendedor = "";
if ($usuario->isUsuarioPuesto($idUsuario, 11)) {
    $vendedor = " AND EjecutivoCuenta = $idUsuario ";
}

//TFS
$tfs = "";
if ($usuario->isUsuarioPuesto($idUsuario, 21)) {
    $TFSGrupo = new TFSGrupoCliente();
    if ($TFSGrupo->tieneGrupo($idUsuario)) {
        $tfs = " INNER JOIN k_tfsgrupo AS ktg ON ktg.IdTfs = $idUsuario AND cl.ClaveGrupo = ktg.ClaveGrupo ";
    } else {
        $tfs = " INNER JOIN k_tfscliente AS tfs ON tfs.IdUsuario = " . $idUsuario . " AND tfs.Tipo = 1 AND tfs.ClaveCliente = t.ClaveCliente ";
    }
}
$consulta2 = "";
if ($idTicket == "") {
    $consulta = "SELECT
        b.id_bitacora,
        t.IdTicket,
        t.NoTicketCliente,
        cl.Suspendido,
        NoTicketDistribuidor,
        e1.Nombre AS NomTipoRep,
        t.FechaHora AS FechaHora,
        (CASE WHEN (!ISNULL(nt.IdNotaTicket) AND nt.IdEstatusAtencion = 16) THEN DATEDIFF(nt.FechaHora,t.FechaCreacion) ELSE DATEDIFF(NOW(),t.FechaCreacion) END) AS DiferenciaDias,
        t.DescripcionReporte,
        t.NombreCentroCosto,
        t.TipoReporte,cc.Nombre AS Localidad,
        (SELECT CASE WHEN e2.Suministro = 1 
        THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) 
        ELSE t.NoSerieEquipo END) AS NumSerie,
         
        (SELECT CASE WHEN e2.Suministro = 1 
        THEN (
        SELECT group_concat(Modelo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) 
        ELSE eq.Modelo END) AS Modelo,
        dcc.Estado,
        CONCAT(dcc.Calle,' No. Ext. ',dcc.NoExterior,' No. Int. ',dcc.NoInterior,' ,COL. ',dcc.Colonia,' ',dcc.Delegacion,', ',dcc.Pais,', ',dcc.Estado,' C.P. ',dcc.CodigoPostal) AS direccion,
        DATEDIFF(NOW(),t.FechaHora) AS diferencia,
        t.NombreCliente,
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
        nt.FechaHora AS FechaNota,
        (SELECT CASE WHEN ISNULL(nt.UsuarioUltimaModificacion) THEN t.UsuarioUltimaModificacion ELSE nt.UsuarioUltimaModificacion END) as UltimoUsuarioNota,
        t.Resurtido
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
        $tipo_join JOIN c_estado AS e2 ON $estado e2.IdEstado = t.AreaAtencion                                
        $estadoNota
        LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
        LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
        LEFT JOIN c_equipo AS eq ON eq.NoParte = b.NoParte
        LEFT JOIN c_domicilio AS dcc ON dcc.ClaveEspecialDomicilio = t.ClaveCentroCosto 
        $tecnico 
        $Where
        $having
        ORDER BY IdTicket";
    if (!$tiene_filtro) {
        $consulta.=" DESC ";
    }
    $consulta.=";";
    $consulta2 = "SELECT
        b.id_bitacora,
	t.IdTicket,
	cl.Suspendido,
	e2.Nombre AS area,
	e2.IdEstado AS idArea,
	nt.IdEstatusAtencion,
    t.FechaHora AS FechaHora,
	COUNT(e2.Nombre) AS Cuenta
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
        $tipo_join JOIN c_estado AS e2 ON $estado e2.IdEstado = t.AreaAtencion                                
        $estadoNota
        LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
        LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
        LEFT JOIN c_equipo AS eq ON eq.NoParte = b.NoParte
        $tecnico 
        $Where";

    if ($having != "") {
        $tr = str_replace("HAVING", "", $having);
        $consulta2.= " AND (" . $tr . ")";
    }
    $consulta2.=" GROUP BY e2.Nombre ORDER BY IdTicket";
    if (!$tiene_filtro) {
        $consulta2.=" DESC ";
    }
    $consulta2.=";";
} else {
    $consulta = "SELECT
        b.id_bitacora,
        t.IdTicket,                                
        t.NoTicketCliente,
        t.NoTicketDistribuidor,
        t.FechaHora AS FechaHora,
        (CASE WHEN (!ISNULL(nt.IdNotaTicket) AND nt.IdEstatusAtencion = 16) THEN DATEDIFF(nt.FechaHora,t.FechaCreacion) ELSE DATEDIFF(NOW(),t.FechaCreacion) END) AS DiferenciaDias,
        t.DescripcionReporte,
        t.NombreCentroCosto,
        t.TipoReporte,cc.Nombre AS Localidad,
        
        (SELECT CASE WHEN e2.Suministro = 1
        THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) 
        ELSE t.NoSerieEquipo END) AS NumSerie,
         
        (SELECT CASE WHEN e2.Suministro = 1
        THEN (SELECT group_concat(Modelo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) 
        ELSE eq.Modelo END) AS Modelo,
        dcc.Estado,
        CONCAT(dcc.Calle,' No. Ext. ',dcc.NoExterior,' No. Int. ',dcc.NoInterior,' ,COL. ',dcc.Colonia,' ',dcc.Delegacion,', ',dcc.Pais,', ',dcc.Estado,' C.P. ',dcc.CodigoPostal) AS direccion,
        DATEDIFF(NOW(), t.FechaHora) AS diferencia,
        t.NombreCliente,
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
        (SELECT CASE WHEN ISNULL(nt.UsuarioUltimaModificacion) THEN t.UsuarioUltimaModificacion ELSE nt.UsuarioUltimaModificacion END) as UltimoUsuarioNota,
        t.Resurtido,
        COUNT(e2.Nombre) AS Cuenta
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
        LEFT JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion                                
        LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)
        LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
        LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo 
        LEFT JOIN c_domicilio AS dcc ON dcc.ClaveEspecialDomicilio = t.ClaveCentroCosto  $tecnico ";
    if (is_numeric($idTicket)) {
        $consulta.=" WHERE (t.IdTicket = $idTicket OR NoTicketCliente = '$idTicket' OR NoTicketDistribuidor = '$idTicket') ";
    } else {
        $consulta.=" WHERE (NoTicketCliente = '$idTicket' OR NoTicketDistribuidor = '$idTicket') ";
    }
    $consulta.=" ORDER BY IdTicket;";
}
//echo $consulta;
$query = $catalogo->obtenerLista($consulta);

$objPHPExcel = new PHPExcel();
// Establecer propiedades
$objPHPExcel->getProperties()
        ->setCreator("")
        ->setLastModifiedBy("")
        ->setTitle("Documento Excel")
        ->setSubject("Documento Excel")
        ->setDescription("Reporte de facturación")
        ->setKeywords("Excel Office 2007 openxml php")
        ->setCategory("Reportes");

$fila_inicial = 2;
$fila_inicial_backup = $fila_inicial;
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . (1), 'REPORTE DE TICKETS')->mergeCells('A1:G1')
        ->setCellValue('H' . (1), $fecha)->mergeCells('H1:J1')
        ->setCellValue('A' . ($fila_inicial), 'Ticket')
        ->setCellValue('B' . ($fila_inicial), 'Tipo reporte')
        ->setCellValue('C' . ($fila_inicial), 'Fecha y Hora')
        ->setCellValue('D' . ($fila_inicial), 'Días de atraso')
        ->setCellValue('E' . ($fila_inicial), 'Cliente')
        ->setCellValue('F' . ($fila_inicial), 'Localidad')
        ->setCellValue('G' . ($fila_inicial), 'No de Serie')
        ->setCellValue('H' . ($fila_inicial), 'Modelo')
        ->setCellValue('I' . ($fila_inicial), 'Área de atención')
        ->setCellValue('J' . ($fila_inicial), 'Zona')
        ->setCellValue('K' . ($fila_inicial), 'Falla')
        ->setCellValue('L' . ($fila_inicial), 'Último estatus ticket')
        ->setCellValue('M' . ($fila_inicial), 'Última nota')
        ->setCellValue('N' . ($fila_inicial), 'Fecha nota')
        ->setCellValue('O' . ($fila_inicial), 'Técnico')
        ->setCellValue('P' . ($fila_inicial), 'Dirección')
        ->setCellValue('Q' . ($fila_inicial), 'No. Ticket cliente')
        ->setCellValue('R' . ($fila_inicial), 'No. Ticket distribuidor');
$fila_inicial++;
$bool = TRUE;

while ($rs = mysql_fetch_array($query)) {/* Recorremos todos los tickets resultantes del query */
    $fecha1 = strtotime($rs['FechaHora']);
    $fecha2 = strtotime(date('Y-m-d'));
    $total = 0;
    for($fecha1;$fecha1<=$fecha2;$fecha1=strtotime('+1 day ' . date('Y-m-d',$fecha1))){ 
        if((strcmp(date('D',$fecha1),'Sun')!=0) and (strcmp(date('D',$fecha1),'Sat')!=0)){
            $total++;
        }
    }    
    $total = $total-1;
    if ($total == "-1") {
        $total = 0;
    }
    
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . ($fila_inicial), $rs['IdTicket'])
            ->setCellValue('B' . ($fila_inicial), $rs['NomTipoRep'])
            ->setCellValue('C' . ($fila_inicial), $rs['FechaHora'])
            //->setCellValue('D' . ($fila_inicial), $rs['DiferenciaDias'])
            ->setCellValue('D' . ($fila_inicial), $total)
            ->setCellValue('E' . ($fila_inicial), $rs['NombreCliente'])
            ->setCellValue('F' . ($fila_inicial), $rs['Localidad'])
            ->setCellValue('G' . ($fila_inicial), $rs['NumSerie'])
            ->setCellValue('H' . ($fila_inicial), $rs['Modelo'])
            ->setCellValue('I' . ($fila_inicial), $rs['area'])
            ->setCellValue('J' . ($fila_inicial), $rs['Estado'])
            ->setCellValue('K' . ($fila_inicial), str_replace("=", " - ",$rs['DescripcionReporte']))
            ->setCellValue('L' . ($fila_inicial), $rs['estadoNota'])
            ->setCellValue('M' . ($fila_inicial), str_replace("=", " - ", $rs['DiagnosticoSol']))
            ->setCellValue('N' . ($fila_inicial), $rs['FechaNota'])
            ->setCellValue('O' . ($fila_inicial), $rs['UltimoUsuarioNota'])
            ->setCellValue('P' . ($fila_inicial), $rs['direccion'])
            ->setCellValue('Q' . ($fila_inicial), $rs['NoTicketCliente'])
            ->setCellValue('R' . ($fila_inicial), $rs['NoTicketDistribuidor']);
    if ($bool) {
        cellColor($objPHPExcel, 'A' . $fila_inicial . ':R' . $fila_inicial, 'ddebf7'); //TITULO REPORTE
        $bool = FALSE;
    } else {
        $bool = TRUE;
    }
    $fila_inicial++;
}

$fila_inicial = 2;
$bool = FALSE;
if ($consulta2 != "") {//Tabla resumen
    //echo $consulta2;
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('S' . ($fila_inicial), 'Area')
            ->setCellValue('T' . ($fila_inicial), 'Casos');
    cellColor($objPHPExcel, 'S2:T2', '5b9bd5'); 
    $fila_inicial++;
    $query2 = $query = $catalogo->obtenerLista($consulta2);
    $suma = 0;
    while ($rs = mysql_fetch_array($query2)) {/* Recorremos todos los tickets resultantes del query */
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('S' . ($fila_inicial), $rs['area'])
                ->setCellValue('T' . ($fila_inicial), $rs['Cuenta']);
        $suma = $suma + $rs['Cuenta'];
        if ($bool) {
            cellColor($objPHPExcel, 'S' . $fila_inicial . ':T' . $fila_inicial, 'ddebf7');
            $bool = FALSE;
        } else {
            $bool = TRUE;
        }
        $fila_inicial++;
    }
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('S' . ($fila_inicial), 'Total')
            ->setCellValue('T' . ($fila_inicial), $suma);
    cellColor($objPHPExcel, 'S' . $fila_inicial . ':T' . $fila_inicial, '5b9bd5');
}
cellColor($objPHPExcel, 'A1:Q1', '5b9bd5'); //TITULO REPORTE
cellColor($objPHPExcel, 'A' . $fila_inicial_backup . ':Q' . $fila_inicial_backup, '5b9bd5'); //TITULO REPORTE
$styleArray = getStyle(true, "000000", 12, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->applyFromArray($styleArray); /* TITULO */
$styleArray = getStyle(true, "000000", 10, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inicial_backup . ':Q' . $fila_inicial_backup)->applyFromArray($styleArray); /* Cabeceras de la tabla */
$styleArray = getStyle(true, "000000", 9, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('H1:J1')->applyFromArray($styleArray); /* Fecha y hora */

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(50);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(75);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(150);
$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
//$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);

// Renombrar Hoja
$objPHPExcel->getActiveSheet()->setTitle('Tickets');

// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
$objPHPExcel->setActiveSheetIndex(0);

// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Tickets.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>