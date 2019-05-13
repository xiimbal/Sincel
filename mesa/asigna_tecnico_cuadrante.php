<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Puesto.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/ParametroGlobal.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "mesa/asigna_tecnico_cuadrante.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$tiene_filtro = false;
//print_r($_POST);
/* Obtenemos el nombre del objeto como se maneja en el sistema (i.e. Ticket, Evento, etc.) */
$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();
$nombre_estado = $permisos_grid2->getNombreTipoReporteSistema();
$latitud = $permisos_grid2->getLatitudSistema();
$longitud = $permisos_grid2->getLongitudSistema();

/* Para mantener los filtros y paginados de la tabla */
if (isset($_GET['page']) && isset($_GET['filter'])) {
    $filter = str_replace("_XX__XX_", " ", $_GET['filter']);
    $page = $_GET['page'];
} else {
    $page = "0";
    $filter = "";
}

$tecnicos = array();
$catalogo = new Catalogo();

$IdEstatusAtencion = "";
$estadoTicket = "";
$tipoCliente = "";
$diferencia = 0;
$IdEstatusCobranza = "";
$TipoReporte = "";
$ahora = "";
$horaTicket = "";
$horaUltimaModificacion = "";
$idPlantilla2 = "";
$NombreCliente = "";
$NombreCentroCosto = "";
$DescripcionReporte = "";
$diasCreacionNota = "";
$estadoNota2 = "";
$diasUltimaNota = "";
$FechaHora = "";
$fila = false;
$especial = false;

$having = " HAVING (IdEstatusAtencion <> 16 AND IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)";
$cerradoTicket = "t.EstadoDeTicket <> 2 AND ";
$checked = "";
$checkedMoroso = "";
$canceladoTicket = "t.EstadoDeTicket <> 4 AND ";
$checkedCancelado = "";
$cliente = "";
$colorPOST = "";
$idTicket = "";
$idPlantilla = "";
$estadoNota = "LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
$morososTicket = "cl.IdEstatusCobranza <> 2 AND ";
$tipoJoin = "LEFT";

$usuario = new Usuario();
$idUsuario = $_SESSION['idUsuario'];

/* Verificamos el puesto del usuario */
if ($usuario->getRegistroById($idUsuario)) {//Buscamos las areas de atencion a las que está asociado este puesto
    $consulta = "SELECT GROUP_CONCAT(CONVERT(IdEstado, CHAR(8)) SEPARATOR ',') estados FROM `k_areapuesto` WHERE IdPuesto = " . $usuario->getPuesto() . ";";
    $result = $catalogo->obtenerLista($consulta);
    if (mysql_numrows($result) > 0) {
        while ($rs = mysql_fetch_array($result)) {
            if (!empty($rs['estados'])) {
                $estado = " e2.IdEstado IN (" . $rs['estados'] . ") AND ";
                $tipoJoin = "INNER";
            } else {
                $estados = "";
            }
        }
    } else {
        $estado = "";
    }
} else {
    $estado = "";
}

//if (isset($_POST['idTicket']) && $_POST['idTicket'] != "") {
//    $tiene_filtro = true;
//    $idTicket = $_POST['idTicket'];
//    /* Si se busco un ticket en particular, habilitamos cerrados, morosos y cancelados */
//    $checked = "checked='checked'";
//    $checkedMoroso = "checked='checked'";
//    $checkedCancelado = "checked='checked'";
//}
//if (isset($_POST['cerrado']) && $_POST['cerrado'] != "false") {
//    $cerradoTicket = "";
//    $checked = "checked='checked'";
//    if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
//        $having = "";
//    } else {
//        $having = " HAVING (IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion) ";
//    }
//} else {
//    if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
//        $having = " HAVING (IdEstatusAtencion <> 16) OR ISNULL(IdEstatusAtencion) ";
//    }
//}
//if (isset($_POST['moroso']) && $_POST['moroso'] != "false") {
//    $morososTicket = "";
//    $checkedMoroso = "checked='checked'";
//}
//
//if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
//    $canceladoTicket = "";
//    $checkedCancelado = "checked='checked'";
//}

if (isset($_POST['cliente']) && $_POST['cliente'] != "") {
    $tiene_filtro = true;
    $cliente = " AND t.NombreCliente IN (" . $_POST['cliente'] . ")";
    $cliente_array = explode("','", $_POST['cliente']);
    $cliente_array[0] = substr($cliente_array[0], 1, strlen($cliente_array[0]));
    $cliente_array[count($cliente_array) - 1] = substr($cliente_array[count($cliente_array) - 1], 0, strlen($cliente_array[count($cliente_array) - 1]) - 1);
}

//if (isset($_POST['color'])) {
//    $colorPOST = $_POST['color'];
//}

if (isset($_POST['estado']) && $_POST['estado'] != "") {
    $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdEstatusAtencion = " . $_POST['estado'] . " AND nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
    if ($_POST['estado'] == "16") {/* Si se selecciona el estado de cerrado, habiliatar el checkbox de cerrado también */
//        $cerradoTicket = "";
//        if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
//            $having = "";
//        } else {
//            $having = " HAVING (IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion) ";
//        }
//        $checked = "checked='checked'";
    }
}

if (isset($_POST['area']) && $_POST['area'] != "") {
    $areas = $_POST['area'];
    $estado = " e2.IdEstado = $areas AND ";
    $tiene_filtro = true;
    $tipoJoin = "INNER";
    $where_chofer = " kap.IdEstado = $areas  AND ";
} else {
    $where_chofer = "";
}

$where = "";
//$parametroGlobal = new ParametroGlobal();
//if($parametroGlobal->getRegistroById(19) && $parametroGlobal->getValor() == "0"){
//    $where = " AND t.TipoReporte <> 15";
//}
$where = " AND t.TipoReporte <> 15 AND t.TipoReporte <> 67 AND t.EstatusAsigna=0";

if (isset($_POST['idPlantilla']) && $_POST['idPlantilla'] != "") {
    $tiene_filtro = true;
    $idPlantilla = $_POST['idPlantilla'];
    $where .= " AND cp.idPlantilla = " . $_POST['idPlantilla'];
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>              
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_ticket.js"></script>       
        <link href="resources/css/mapa/asigna_tecnico.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" language="javascript" src="resources/js/mapas/Label.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/mapas/mapas2.js"></script>  
    </head>
    <body>
        <div class="principal">            
            <br/><br/>
            <table style="width: 100%;">
                <tr>                    
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td> 
                    <td></td> 
<!--                    <td><input type="checkbox" id="ticket_cerrado" name="ticket_cerrado" <?php //echo $checked;                   ?> style="display: none;"/></td>-->
                </tr>
                <tr>      
<!--                    <td><?php //echo $nombre_objeto;                   ?></td>
                    <td><input type="text" id="busqueda_ticket" name="busqueda_ticket" value="<?php //echo $idTicket;                   ?>"/></td>-->
                    <td>Cliente</td>
                    <td>
                        <select id="cliente_ticket" name="cliente_ticket" style="width: 200px;" multiple="multiple">
                            <?php
                            $query = $catalogo->obtenerLista("SELECT DISTINCT(NombreCliente) AS cliente FROM `c_ticket` ORDER BY cliente;");
                            echo "<option value=''>Todos los clientes</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                if ($rs['cliente'] == "") {
                                    continue;
                                }
                                $s = "";
                                if (isset($_POST['cliente']) && $_POST['cliente'] != "" && in_array($rs['cliente'], $cliente_array)) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['cliente'] . "&_X_&' $s>" . $rs['cliente'] . "</option>";
                            }
                            ?> 
                        </select>
                    </td>
                    <td>Estado</td>
                    <td>
                        <select id="estado_ticket" name="estado_ticket" style="width: 200px;" >
                            <?php
                            /* Inicializamos la clase */
                            $query = $catalogo->obtenerLista("SELECT e.IdEstado, e.Nombre FROM c_estado AS e
                                INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND kfe.IdFlujo = 6 ORDER BY Nombre;");
                            echo "<option value=''>Todos los estados</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if (isset($_POST['estado']) && $_POST['estado'] == $rs['IdEstado']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                            }
                            ?> 
                        </select>
                    </td>
                    <!--
                    <td>Color</td>
                    <td>
                        <select id="ticket_color" name="ticket_color" style="width: 200px;" >
                            <option value="">Todos</option>
                            <option value="rojo" style="background: #DC381F;">Urgente</option>
                            <option value="amarillo" style="background: #FFF380;">Importante</option>
                            <option value="verde" style="background: #F7F7DE;">Normal</option>
                        </select>                        
                    </td>
                    -->
                    <!--<td><input type="checkbox" id="ticket_moroso" name="ticket_moroso" <?php //echo $checkedMoroso;                   ?> />Mostrar morosos</td>-->
                </tr>
                <tr>
                    <td>
                        &Aacute;reas de atención <?php echo $nombre_objeto; ?>
                    </td>
                    <td>
                        <select id="area" name="area" class="select">
                            <?php
                            /* Inicializamos la clase */
                            $query = $catalogo->obtenerLista("SELECT DISTINCT(e.IdEstado) AS IdEstado, e.Nombre FROM c_estado AS e
                                    INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND (kfe.IdFlujo = 2 OR e.IdEstado = 2) ORDER BY Nombre;");
                            echo "<option value=''>Todos los cuadrantes</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if (!empty($areas) && $rs['IdEstado'] == $areas) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                            }
                            ?> 
                        </select>
                        <div id="error_area" style="font-size: 12px; color: red;"></div>
                    </td>
                    <td>
                        Plantilla del <?php echo $nombre_objeto; ?>
                    </td>
                    <td><input type="text" id="busqueda_plantilla" name="busqueda_plantilla" value="<?php echo $idPlantilla; ?>"/></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <!--<td><input type="checkbox" id="ticket_cancelado" name="ticket_cancelado" <?php //echo $checkedCancelado;                   ?> />Mostrar cancelados</td>-->
                </tr>
            </table>   
            <input type="button" class="button" onclick="
//                    if (areaObligatoria('area', 'error_area')) {
                    recargarListaTicketTecnico('mesa/asigna_tecnico_cuadrante.php', 'busqueda_ticket', 'ticket_cerrado', 'cliente_ticket', 'ticket_color', 'estado_ticket',
                            'ticket_moroso', 'ticket_cancelado', true, null, null, null, 'area', null, null, null, null, 'busqueda_plantilla');
//                    }
                    return false;" 
                   id="boton_aceptar" name="boton_aceptar" value="Mostrar <?php echo $nombre_objeto; ?>s"/>
            <br/><br/>
            <table style="width: 100%;">
                <tr>
                    <td style="vertical-align: text-top; width: 50%;"><!--Aqui se pone el mapa-->                        
                        <div id="map-canvas" style="height: 600px;">Aquí mapa</div>
                    </td>
                    <td>
                        <?php
                        if (isset($_POST['idPlantilla'])) {
                            $LatitudesTickets = "";
                            $LongitudesTickets = "";
                            $NumeroTicket = "";
                            ?>
                            <table id="tAsigna">
                                <thead>
                                    <tr>
                                        <?php
                                        $cabeceras = array("Plantilla", "Cliente", $nombre_estado, "Cuadrante $nombre_objeto", "Numero de Empleados",
                                            "Seleccionar", "Fecha");
                                        for ($i = 0; $i < (count($cabeceras)); $i++) {
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                                        }
                                        if (!empty($where)) {//Se tiene que mostrar el destino
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">Destino</th>";
                                        }
                                        ?>                                                                      
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($idTicket == "") {
                                        $consulta = "SELECT
                                            cp.idPlantilla,
                                            b.id_bitacora,
                                            t.IdTicket,
                                            t.NoTicketCliente,
                                            cl.Suspendido,
                                            NoTicketDistribuidor,
                                            DATE(t.FechaHora) AS FechaHora,
                                            t.DescripcionReporte,
                                            t.NombreCentroCosto,
                                            t.TipoReporte,
                                            (SELECT CASE WHEN e2.IdEstado = 2 THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
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
                                            ((5 * (DATEDIFF(NOW(), t.FechaHora) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(t.FechaHora) + WEEKDAY(NOW()) + 1, 1))) AS diasCreacionNota,
                                            ((5 * (DATEDIFF(NOW(), nt.FechaHora) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(nt.FechaHora) + WEEKDAY(NOW()) + 1, 1))) AS diasUltimaNota,
                                            DATE_FORMAT(NOW(), '%H') AS ahora,
                                            DATE_FORMAT(t.FechaHora,'%H') AS horaTicket,
                                            DATE_FORMAT(nt.FechaHora, '%H') AS horaUltimaModificacion,
                                            (SELECT CASE WHEN ISNULL(nt.UsuarioUltimaModificacion) THEN t.UsuarioUltimaModificacion ELSE nt.UsuarioUltimaModificacion END) as UltimoUsuarioNota,
                                            t.Resurtido,
                                            (CASE WHEN !ISNULL(dt.Latitud) THEN dt.Latitud WHEN !ISNULL(dcc.Latitud) THEN dcc.Latitud ELSE $latitud END) AS Latitud, 
                                            (CASE WHEN !ISNULL(dt.Longitud) THEN dt.Longitud WHEN !ISNULL(dcc.Longitud) THEN dcc.Longitud  ELSE $longitud END) AS Longitud,
                                            (CASE 
                                            WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 2 
                                            THEN CONCAT(dut.Calle,' ',dut.NoExterior,' No. Int1. ',dut.NoInterior,', ',dut.Colonia,', ',dut.Delegacion,', ',dut.Estado,', ',dut.CodigoPostal,'. Cita <b>origen: ',cp.Fecha,' ',cp.Hora,'</b>')
                                            WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 1 
                                            THEN CONCAT(dcc.Calle,' ',dcc.NoExterior,' No. Int3. ',dcc.NoInterior,', ',dcc.Colonia,', ',dcc.Delegacion,', ',dcc.Estado,', ',dcc.CodigoPostal,'. Cita <b>destino: ',cp.Fecha,' ',cp.Hora,'</b>') 

                                            ELSE NULL END) AS LugarDestino
                                            FROM c_ticket AS t
                                            INNER JOIN c_estadoticket AS e ON $canceladoTicket $cerradoTicket e.IdEstadoTicket = t.EstadoDeTicket $cliente
                                            LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket
                                            LEFT JOIN c_zona AS cgz ON cgz.ClaveZona = dt.ClaveZona
                                            LEFT JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte
                                            INNER JOIN c_cliente AS cl ON $morososTicket cl.ClaveCliente = t.ClaveCliente 
                                            LEFT JOIN c_zona AS cgz2 ON cgz2.ClaveZona = cl.ClaveZona 
                                            LEFT JOIN c_centrocosto AS cc ON t.ClaveCentroCosto = cc.ClaveCentroCosto
                                            LEFT JOIN c_domicilio AS dcc ON dcc.IdDomicilio = (SELECT MIN(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = cc.ClaveCentroCosto)
                                            LEFT JOIN c_zona AS cgz3 ON cgz3.ClaveZona = cc.ClaveZona
                                            LEFT JOIN c_clientegrupo AS clg ON clg.ClaveGrupo = cl.ClaveGrupo
                                            LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
                                            LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdTicket = t.IdTicket 
                                            $tipoJoin JOIN c_estado AS e2 ON $estado e2.IdEstado = t.AreaAtencion                                
                                            $estadoNota
                                            LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
                                            LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo 
                                            LEFT JOIN k_plantilla_asistencia AS kpa ON kpa.idK_Plantilla_asistencia = (SELECT MAX(idK_Plantilla_asistencia) FROM k_plantilla_asistencia WHERE IdTicket = t.IdTicket)
                                            LEFT JOIN k_plantilla AS kp ON kp.idK_Plantilla = kpa.idK_Plantilla
                                            LEFT JOIN c_plantilla AS cp ON cp.idPlantilla = kp.idPlantilla
                                            LEFT JOIN c_usuario AS usu ON usu.Loggin = t.NoSerieEquipo
                                            LEFT JOIN c_domicilio_usturno AS dut ON dut.IdUsuario = usu.IdUsuario 
                                            WHERE ISNULL(ktt.IdTicket) $where 
                                            $having
                                            ORDER BY idPlantilla DESC, e2.IdEstado ASC";
                                        if (!$tiene_filtro) {
                                            $consulta.=" LIMIT 0,300";
                                        }
                                        $consulta.=";";
                                    } else {
                                        $consulta = "SELECT
                                            cp.idPlantilla,
                                            b.id_bitacora,
                                            t.IdTicket,                                
                                            t.NoTicketCliente,
                                            t.NoTicketDistribuidor,
                                            DATE(t.FechaHora) AS FechaHora,
                                            t.DescripcionReporte,
                                            t.NombreCentroCosto,
                                            t.TipoReporte,
                                            (SELECT CASE WHEN e2.IdEstado = 2 
                                            THEN(SELECT group_concat(ClaveEspEquipo SEPARATOR ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket)
                                            ELSE t.NoSerieEquipo END) AS NumSerie,
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
                                            ((5 * (DATEDIFF(NOW(), t.FechaHora) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(t.FechaHora) + WEEKDAY(NOW()) + 1, 1))) AS diasCreacionNota,
                                            ((5 * (DATEDIFF(NOW(), nt.FechaHora) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(nt.FechaHora) + WEEKDAY(NOW()) + 1, 1))) AS diasUltimaNota,
                                            DATE_FORMAT(NOW(), '%H') AS ahora,
                                            DATE_FORMAT(t.FechaHora,'%H') AS horaTicket,
                                            DATE_FORMAT(nt.FechaHora, '%H') AS horaUltimaModificacion,
                                            nt.FechaHora AS FechaNota,
                                            (SELECT CASE WHEN ISNULL(nt.UsuarioUltimaModificacion) THEN t.UsuarioUltimaModificacion ELSE nt.UsuarioUltimaModificacion END) as UltimoUsuarioNota,
                                            t.Resurtido,
                                            (CASE WHEN !ISNULL(dt.Latitud) THEN dt.Latitud WHEN !ISNULL(dcc.Latitud) THEN dcc.Latitud ELSE $latitud END) AS Latitud, 
                                            (CASE WHEN !ISNULL(dt.Longitud) THEN dt.Longitud WHEN !ISNULL(dcc.Longitud) THEN dcc.Longitud ELSE $longitud END) AS Longitud,
                                            (CASE 
                                            WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 2 
                                            THEN CONCAT(dut.Calle,' ',dut.NoExterior,' No. Int1. ',dut.NoInterior,', ',dut.Colonia,', ',dut.Delegacion,', ',dut.Estado,', ',dut.CodigoPostal,'. Cita <b>origen: ',cp.Fecha,' ',cp.Hora,'</b>')
                                            WHEN !ISNULL(cp.idPlantilla) AND cp.TipoEvento = 1 
                                            THEN CONCAT(dcc.Calle,' ',dcc.NoExterior,' No. Int3. ',dcc.NoInterior,', ',dcc.Colonia,', ',dcc.Delegacion,', ',dcc.Estado,', ',dcc.CodigoPostal,'. Cita <b>destino: ',cp.Fecha,' ',cp.Hora,'</b>') 

                                            ELSE NULL END) AS LugarDestino
                                            FROM
                                            c_ticket AS t
                                            INNER JOIN c_estadoticket AS e ON e.IdEstadoTicket = t.EstadoDeTicket
                                            LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket
                                            LEFT JOIN c_zona AS cgz ON cgz.ClaveZona = dt.ClaveZona
                                            LEFT JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte
                                            INNER JOIN c_cliente AS cl ON cl.ClaveCliente = t.ClaveCliente 
                                            LEFT JOIN c_zona AS cgz2 ON cgz2.ClaveZona = cl.ClaveZona
                                            LEFT JOIN c_centrocosto AS cc ON t.ClaveCentroCosto = cc.ClaveCentroCosto
                                            LEFT JOIN c_domicilio AS dcc ON dcc.IdDomicilio = (SELECT MIN(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = cc.ClaveCentroCosto)
                                            LEFT JOIN c_zona AS cgz3 ON cgz3.ClaveZona = cc.ClaveZona
                                            LEFT JOIN c_clientegrupo AS clg ON clg.ClaveGrupo = cl.ClaveGrupo
                                            LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
                                            $tipoJoin JOIN c_estado AS e2 ON $estado e2.IdEstado = t.AreaAtencion                                
                                            LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (
                                            SELECT
                                                    MAX(IdNotaTicket)
                                            FROM
                                                    c_notaticket AS nt2
                                            WHERE
                                                    nt2.IdTicket = t.IdTicket
                                            )
                                            LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
                                            LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo $tecnico 
                                            LEFT JOIN k_plantilla_asistencia AS kpa ON kpa.idK_Plantilla_asistencia = (SELECT MAX(idK_Plantilla_asistencia) FROM k_plantilla_asistencia WHERE IdTicket = t.IdTicket)
                                            LEFT JOIN k_plantilla AS kp ON kp.idK_Plantilla = kpa.idK_Plantilla
                                            LEFT JOIN c_plantilla AS cp ON cp.idPlantilla = kp.idPlantilla
                                            LEFT JOIN c_usuario AS usu ON usu.Loggin = t.NoSerieEquipo
                                            LEFT JOIN c_domicilio_usturno AS dut ON dut.IdUsuario = usu.IdUsuario ";
                                        if (is_numeric($idTicket)) {
                                            $consulta.=" WHERE (t.IdTicket = $idTicket OR NoTicketCliente = '$idTicket' OR NoTicketDistribuidor = '$idTicket') $where ";
                                        } else {
                                            $consulta.=" WHERE (NoTicketCliente = '$idTicket' OR NoTicketDistribuidor = '$idTicket') $where ";
                                        }
                                        $consulta.=" ORDER BY IdTicket;";
                                    }


//                                    echo $consulta;
//                                    $plantillasT = array();
//                                    while($rs11 = mysql_fetch_array($query2)){
//                                        if($rs11['idPlantilla']!= ""){
//                                            if(in_array($rs11['idPlantilla'], $plantillasT)){
//                                                continue;
//                                            }else{
//                                            array_push($plantillasT, $rs11['idPlantilla']);
//                                            }
//                                        }
//                                    }
//                                    for($i=0; $i<count($plantillasT);$i++){
//                                    $plantillasT[0];
//                                    }

                                    $plantillas = array();
                                    $cuadrantesPlantillas = array();
                                    $pl = 0;
                                    $empleadosCP = 0;
                                    $array_tickets = array();
                                    $query2 = $catalogo->obtenerLista($consulta);
                                    $filasTotal = mysql_num_rows($query2);
                                    while ($rs = mysql_fetch_array($query2)) {/* Recorremos todos los tickets resultantes del query */

                                        if ($rs['Suspendido'] != "0") {
                                            echo "<br/><h2>El $nombre_objeto " . $rs['IdTicket'] . " pertenece al cliente suspendido " . $rs['NombreCliente'] . "</h2><br/>";
                                            break;
                                        }
                                        if ($checked == "" && ($rs['IdEstatusAtencion'] == "16" || $rs['IdEstatusAtencion'] == "59")) {/* Si ya esta cerrado por nota, saltamos */
                                            continue;
                                        }

                                        if (in_array($rs['IdTicket'], $array_tickets)) {
                                            continue;
                                        } else {
                                            array_push($array_tickets, $rs['IdTicket']);
                                        }

                                        if ($rs['idPlantilla'] != "") {
                                            $plantillas[$pl] = $rs['idPlantilla'];
                                            $cuadrantesPlantillas[$pl] = $rs['idArea'];

                                            if ($pl == 0) {
                                                $empleadosCP++;
                                                $fila = false;
                                            } else {
                                                if ($plantillas[$pl - 1] == $rs['idPlantilla']) {
                                                    if ($cuadrantesPlantillas[$pl - 1] == $rs['idArea']) {
                                                        $empleadosCP++;
                                                        $fila = false;
                                                    } else {
                                                        $fila = true;
                                                        $finalF = 1;
                                                    }
                                                } else {
                                                    $fila = true;
                                                    $finalF = 1;
                                                }
                                            }

                                            if ($pl == ($filasTotal - 1)) {
                                                if (!$fila) { // Si fila es false indica que el ultimo ticket es de la misma plantilla y cuadrante que el anterior
                                                    if ($empleadosCP > 0) {
                                                        $fila = true;
                                                        if (pl == 0) {
                                                            $IdEstatusAtencion = $rs['IdEstatusAtencion'];
                                                            $estadoTicket = $rs['estadoTicket'];
                                                            $tipoCliente = $rs['tipoCliente'];
                                                            $diferencia = $rs['diferencia'];
                                                            $IdEstatusCobranza = $rs['IdEstatusCobranza'];
                                                            $TipoReporte = $rs['TipoReporte'];
                                                            $ahora = $rs['ahora'];
                                                            $horaTicket = $rs['horaTicket'];
                                                            $horaUltimaModificacion = $rs['horaUltimaModificacion'];
                                                            $idPlantilla2 = $rs['idPlantilla'];
                                                            $NombreCliente = $rs['NombreCliente'];
                                                            $NombreCentroCosto = $rs['NombreCentroCosto'];
                                                            $DescripcionReporte = $rs['DescripcionReporte'];
                                                            $diasCreacionNota = $rs['diasCreacionNota'];
                                                            $estadoNota2 = $rs['estadoNota'];
                                                            $diasUltimaNota = $rs['diasUltimaNota'];
                                                            $FechaHora = $rs['FechaHora'];
                                                            $idArea = $rs['idArea'];
                                                            $area = $rs['area'];
                                                            $idTicketQ = $rs['IdTicket'];
                                                        }
                                                        $finalF = 1;
                                                    }
                                                } else {
                                                    $finalF = 2;
                                                }
                                            }
                                        } else {
                                            $plantillas[$pl] = $pl;
                                            $cuadrantesPlantillas[$pl] = $rs['idArea'];
                                            if ($empleadosCP > 0) {
                                                $fila = true;
                                                $finalF = 2;
                                                $especial = true;
                                            } else {
                                                $especial = true;
                                                $fila = true;
                                                $finalF = 1;
                                                $IdEstatusAtencion = $rs['IdEstatusAtencion'];
                                                $estadoTicket = $rs['estadoTicket'];
                                                $tipoCliente = $rs['tipoCliente'];
                                                $diferencia = $rs['diferencia'];
                                                $IdEstatusCobranza = $rs['IdEstatusCobranza'];
                                                $TipoReporte = $rs['TipoReporte'];
                                                $ahora = $rs['ahora'];
                                                $horaTicket = $rs['horaTicket'];
                                                $horaUltimaModificacion = $rs['horaUltimaModificacion'];
                                                $idPlantilla2 = $rs['idPlantilla'];
                                                $NombreCliente = $rs['NombreCliente'];
                                                $NombreCentroCosto = $rs['NombreCentroCosto'];
                                                $DescripcionReporte = $rs['DescripcionReporte'];
                                                $diasCreacionNota = $rs['diasCreacionNota'];
                                                $estadoNota2 = $rs['estadoNota'];
                                                $diasUltimaNota = $rs['diasUltimaNota'];
                                                $FechaHora = $rs['FechaHora'];
                                                $idArea = $rs['idArea'];
                                                $area = $rs['area'];
                                                $idTicketQ = $rs['IdTicket'];
                                            }
                                        }

//                                        $ticket= array();
//                                        $plantillas=array();
//                                        $cuadranteP = array();
//                                        while ($rs22 = mysql_fetch_array($query2)){
//                                            if($rs['IdTicket'] == $rs22['IdTicket']){
//                                                continue;
//                                            }else{
//                                                if($rs['idPlantilla']!=""){
//                                                    if(in_array($rs['idPlantilla'],$plantillas)){
//                                                        
//                                                        continue 2;
//                                                    }else{
//                                                        array_push($plantillas, $rs['idPlantilla']);
//                                                        $cuadrantePlantilla[$rs['idPlantilla']]=$rs['idArea'];
//                                                    }
//                                                }else{
//                                                    $viajes_especiales;
//                                                }
//                                            }
//                                        }
                                        /*                                         * *********************    Obtenemos el color de la fila   ******************************** */
                                        $color = "#F7F7DE";

                                        if (isset($IdEstatusAtencion)) {/* Si hay estado de la ultima nota */
                                            if ($IdEstatusAtencion != "16" && (isset($estadoTicket) && $estadoTicket != "2")) {/* Si el ticket no esta cerrado */
                                                if (strtoupper($tipoCliente) == "1") {/* Si el cliente es VIP */
                                                    if (number_format($diferencia) >= 2) {/* Si ya van mas de 2 dias que se levanto el ticket */
                                                        if ($colorPOST != "" && $colorPOST != "rojo") {
                                                            continue;
                                                        }
                                                        $color = "#DC381F";
                                                    } else {
                                                        if ($colorPOST != "" && $colorPOST != "amarillo") {
                                                            continue;
                                                        }
                                                        $color = "#FFF380";
                                                    }
                                                } else {/* Si no es cliente VIP */
                                                    if (number_format($diferencia) >= 7) {/* Si ya van mas de 7 dias que se levanto el ticket */
                                                        if ($colorPOST != "" && $colorPOST != "rojo") {
                                                            continue;
                                                        }
                                                        $color = "#DC381F";
                                                    }
                                                }
                                            }
                                        } else {/* Si no hay notas, vemos el estado del ticket */
                                            if ($estadoTicket != "2") {/* Si el ticket no esta cerrado */
                                                if (strtoupper($tipoCliente) == "1") {/* Si el cliente es VIP */
                                                    if (number_format($diferencia) >= 2) {/* Si ya van mas de 2 dias que se levanto el ticket */
                                                        if ($colorPOST != "" && $colorPOST != "rojo") {
                                                            continue;
                                                        }
                                                        $color = "#DC381F";
                                                    } else {
                                                        if ($colorPOST != "" && $colorPOST != "amarillo") {
                                                            continue;
                                                        }
                                                        $color = "#FFF380";
                                                    }
                                                } else {/* Si no es cliente VIP */
                                                    if (number_format($diferencia) >= 7) {/* Si ya van mas de 7 dias que se levanto el ticket */
                                                        if ($colorPOST != "" && $colorPOST != "rojo") {
                                                            continue;
                                                        }
                                                        $color = "#DC381F";
                                                    }
                                                }
                                            }
                                        }



                                        /* En dado caso que se un ticekt verde pero en el filtro se selecciono otro color */
                                        if ($color == "#F7F7DE" && ($colorPOST != "verde" && $colorPOST != "")) {
                                            continue;
                                        }

                                        if ($IdEstatusCobranza == "2") {/* Cliente moroso */
                                            $color = "#D462FF";
                                        }

                                        if ($estadoTicket == "4") {/* Ticket cancelado */
                                            $color = "#D1D0CE";
                                        }

                                        if ($TipoReporte == "26") {/* Si es Mtto preventivo */
                                            $color = "#00FFFF";
                                        }

                                        $actual = $ahora;
                                        $horasCreacion = $horaTicket;
                                        $horasModificacion = $horaUltimaModificacion;
                                        if ($actual >= $horasCreacion) {
                                            $diffT = $actual - $horasCreacion;
                                        } else {
                                            $diasCreacion--;
                                            $diffT = 24 - ($horasCreacion - $actual);
                                        }
                                        if ($actual >= $horasModificacion) {
                                            $diffUT = $actual - $horasModificacion;
                                        } else {
                                            $diasUltimaModificacion--;
                                            $diffUT = 24 - ($horasModificacion - $actual);
                                        }
                                        $LatitudesTickets = $LatitudesTickets . "/" . $rs['Latitud'];
                                        $LongitudesTickets = $LongitudesTickets . "/" . $rs['Longitud'];
                                        $NumeroTicket = $NumeroTicket . "/" . $rs['IdTicket'];

                                        if ($fila) {
                                            for ($j = 0; $j < $finalF; $j++) {
                                                echo "<tr style='background-color: $color; color:black;'>";
//                                                if ($especial) {
//                                                    echo "<td align='center' scope='row'>Viaje Especial</td>";
//                                                } else {
                                                echo "<td align='center' scope='row'>" . $idPlantilla2 . "</td>";
//                                                }

                                                echo "<td align='center' scope='row'>" . $NombreCliente . " - " . $NombreCentroCosto . "</td>";

                                                echo "<td align='center' scope='row' title='" . $DescripcionReporte . "'>";
                                                $descripcion = $DescripcionReporte;
                                                if (strlen($DescripcionReporte) > 37) {
                                                    $descripcion = substr($DescripcionReporte, 0, 37) . " ...";
                                                }
                                                echo "$descripcion ( " . $diasCreacionNota . " dias " . $diffT . " horas)</td>";

//                                                if (isset($estadoNota2)) {
//                                                    echo "<td align='center' scope='row'>" . $estadoNota2 . " (" . $diasUltimaNota . " dias " . $diffUT . " horas) </td>";
//                                                } else {
//                                                    echo "<td align='center' scope='row'></td>";
//                                                }
                                                echo "<td align='center' scope='row'>" . $area . "</td>";

//                                        if ($rs['idArea'] == "2") {
//                                            $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketToner.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Detalle&uguid=" . $_SESSION['user'];
//                                        } else {
//                                            $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketFalla.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Detalle&uguid=" . $_SESSION['user'];
//                                        }
                                                $fecha_limite = strtotime("2014-03-31");
                                                $fecha_ticket = strtotime($FechaHora);
                                                if ($fecha_ticket >= $fecha_limite) {
                                                    $nuevo = true;
                                                } else {
                                                    $nuevo = false;
                                                }
                                                ?>       

                                                                                                                                                                                                                                                                                                    <!--                                    <td align='center' scope='row'> 
                                                <?php //if ($permisos_grid->getConsulta()) {  ?>
                                                <?php
                                                //if ($nuevo) {
                                                ?>
                                                                                                                                                                                                                                                                                                                <a href='#' onclick='detalleTicket("mesa/alta_ticketphp.php", "<?php //echo $rs['IdTicket'];                 ?>", "<?php //echo $rs['TipoReporte'];                 ?>", "1");
                                                                                                                                                                                                                                                                                                                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                                                <?php //} else {  ?>
                                                                                                                                                                                                                                                                                                                <a href='#' onclick='lanzarPopUp("Detalle", "<?php //echo $src;                  ?>");
                                                                                                                                                                                                                                                                                                                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                                                <?php
                                                //}
                                                ?>
                                                <?php //} ?>
                                                                                                                                                                                                                                                                                                    </td>                                  -->

                                            <td align='center' scope='row'><?php
                                                if ($especial) {
                                                    $empleadosCP = 0;
                                                    echo 1;
                                                } else {
                                                    echo $empleadosCP;
                                                    $empleadosCP = 1;
                                                }
                                                ?> </td>

                                            <?php
                                            echo "<td align='center' scope='row'>";

                                            if ($estadoTicket != "2" && $estadoTicket != "4" && $IdEstatusAtencion != "16" && $IdEstatusAtencion != "59") {
                                                echo "<input type='checkbox' id='tecnico_" . $idPlantilla2 . "_" . $idArea . "' name='tecnico_" . $idPlantilla2 . "_" . $idArea . "' value='" . $idPlantilla2 . "_" . $idArea . "' onchange = 'seleccionarTicket();'/>";
                                            }
                                            echo "</td>";

//                                    echo "<td>";   
//                                    $result2 = $catalogo->obtenerLista("SELECT pt.IdPrioridad, pt.Prioridad, tp.TipoPrioridad,  c.Hexadecimal
//                                        FROM `c_prioridadticket` AS pt
//                                        LEFT JOIN c_color AS c ON c.IdColor = pt.IdColor
//                                        LEFT JOIN c_tipoprioridad AS tp ON tp.IdTipoPrioridad = pt.IdTipoPrioridad;");
//                                    echo "<select id='prioridad_".$rs['IdTicket']."' name='prioridad_".$rs['IdTicket']."'>";
//                                    while($rs2 = mysql_fetch_array($result2)){
//                                        echo "<option value='".$rs2['IdPrioridad']."' style='background: #".$rs2['Hexadecimal'].";'>".$rs2['Prioridad']." (".$rs2['TipoPrioridad'].")</option>";
//                                    }
//                                    echo "</select>";
//                                    echo "</td>";
//                                    echo "<td>";  
//                                    echo "<input type='number' id='tiempo_".$rs['IdTicket']."' name='tiempo_".$rs['IdTicket']."' style='width: 30px;' max='999'  min='0'/>";
//                                    $result2 = $catalogo->getListaAltaTodo("c_unidadmedida", "IdUnidad");
//                                    echo "<select id='um_".$rs['IdTicket']."' name='um_".$rs['IdTicket']."'>";
//                                    while($rs2 = mysql_fetch_array($result2)){
//                                        echo "<option value='".$rs2['IdUnidad']."'>".$rs2['Unidad']."</option>";
//                                    }
//                                    echo "</select>";
//                                    echo "</td>";

                                            echo '<td><input type="text" class="datetime" id="fecha_' . $idPlantilla2 . '_' . $idArea . '" name="fecha_' . $idPlantilla2 . '_' . $idArea . '"/></td>';
                                            if (!empty($where)) {
                                                //echo "<td>".$rs['LugarDestino']."</td>"; 
                                                $src_destino = "viajes/ConsultaDestinoCuadrante.php?idPlantilla=" . $idPlantilla2 . "&idArea=" . $idArea . "&idTicket=" . $idTicketQ;
                                                ?>
                                                <td>
                                                    <a href='#' title='Destino' onclick='lanzarPopUpAjustable("Destino de Plantilla <?php echo $idPlantilla2; ?> en el cuadrante <?php echo $area; ?>", "<?php echo $src_destino; ?>", "600", "600");
                                                                                return false;'><img src="resources/images/Textpreview.png" width="28" height="28" style="margin-left: 18%;"/></a>
                                                </td>
                                                <?php
                                            }
                                            echo "</tr>";
                                            //$pl++;
                                            if ($finalF == 2) {
                                                $IdEstatusAtencion = $rs['IdEstatusAtencion'];
                                                $estadoTicket = $rs['estadoTicket'];
                                                $tipoCliente = $rs['tipoCliente'];
                                                $diferencia = $rs['diferencia'];
                                                $IdEstatusCobranza = $rs['IdEstatusCobranza'];
                                                $TipoReporte = $rs['TipoReporte'];
                                                $ahora = $rs['ahora'];
                                                $horaTicket = $rs['horaTicket'];
                                                $horaUltimaModificacion = $rs['horaUltimaModificacion'];
                                                $idPlantilla2 = $rs['idPlantilla'];
                                                $NombreCliente = $rs['NombreCliente'];
                                                $NombreCentroCosto = $rs['NombreCentroCosto'];
                                                $DescripcionReporte = $rs['DescripcionReporte'];
                                                $diasCreacionNota = $rs['diasCreacionNota'];
                                                $estadoNota2 = $rs['estadoNota'];
                                                $diasUltimaNota = $rs['diasUltimaNota'];
                                                $FechaHora = $rs['FechaHora'];
                                                $idArea = $rs['idArea'];
                                                $area = $rs['area'];
                                                $idTicketQ = $rs['IdTicket'];
                                            }
                                        }
                                    }
                                    $IdEstatusAtencion = $rs['IdEstatusAtencion'];
                                    $estadoTicket = $rs['estadoTicket'];
                                    $tipoCliente = $rs['tipoCliente'];
                                    $diferencia = $rs['diferencia'];
                                    $IdEstatusCobranza = $rs['IdEstatusCobranza'];
                                    $TipoReporte = $rs['TipoReporte'];
                                    $ahora = $rs['ahora'];
                                    $horaTicket = $rs['horaTicket'];
                                    $horaUltimaModificacion = $rs['horaUltimaModificacion'];
                                    $idPlantilla2 = $rs['idPlantilla'];
                                    $NombreCliente = $rs['NombreCliente'];
                                    $NombreCentroCosto = $rs['NombreCentroCosto'];
                                    $DescripcionReporte = $rs['DescripcionReporte'];
                                    $diasCreacionNota = $rs['diasCreacionNota'];
                                    $estadoNota2 = $rs['estadoNota'];
                                    $diasUltimaNota = $rs['diasUltimaNota'];
                                    $FechaHora = $rs['FechaHora'];
                                    $idArea = $rs['idArea'];
                                    $area = $rs['area'];
                                    $idTicketQ = $rs['IdTicket'];
                                    $pl++;
                                }
                                ?>
                                </tbody>
                                <input type="hidden" id="color_hidden" name="color_hidden" value="<?php echo $colorPOST; ?>"/>
                                <input type="hidden" id="page" name="page" value="<?php echo $page; ?>"/>
                                <input type="hidden" id="filter" name="filter" value="<?php echo $filter; ?>"/>
                                <input type="hidden" id="regresar" name="regresar" value="<?php echo $same_page; ?>"/>
                            </table>
                            <?php
                            $catalogo1 = new Catalogo();
                            $consulta1 = "SELECT u.IdUsuario, CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Usuario, Loggin,
                                        (CASE WHEN !ISNULL(ubu.IdUbicacion) THEN ubu.Latitud ELSE $latitud END) AS Latitud,
                                        (CASE WHEN !ISNULL(ubu.IdUbicacion) THEN ubu.Longitud ELSE $longitud END) AS Longitud,
                                        (CASE WHEN !ISNULL(ubu.IdUbicacion) THEN ubu.Fecha ELSE 'SR' END) AS FechaUbicacion,
                                        ubu.PorcentajeBateria,
                                        GROUP_CONCAT( CONVERT(CONCAT(t.IdTicket,' [',ktt.FechaHoraInicio,']'), CHAR(100)) SEPARATOR ',<br/>' ) AS Tickets
                                        FROM c_usuario AS u
                                        LEFT JOIN k_areapuesto AS kap ON kap.IdPuesto = u.IdPuesto
                                        LEFT JOIN c_ubicacionusuario AS ubu ON ubu.IdUbicacion = (SELECT MAX(IdUbicacion) FROM c_ubicacionusuario WHERE IdUsuario = u.IdUsuario)
                                        LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdUsuario = u.IdUsuario
                                        LEFT JOIN c_ticket AS t ON t.IdTicket = ktt.IdTicket AND t.EstadoDeTicket NOT IN(2,4)
                                        WHERE $where_chofer u.Activo = 1
                                        GROUP BY u.IdUsuario
                                        ORDER BY Usuario;";
//                            echo $consulta1;
                            $result1 = $catalogo1->obtenerLista($consulta1);
                            $LatitudesTecnicos = "";
                            $LongitudesTecnicos = "";
                            $FechaHoraTecnicos = "";
                            $userTecnico = "";
                            $PorcentajeBateria = "";
                            ?>          
                            <h2><?php echo $nombre_puesto; ?></h2>
                            <table class="tablaUsuarios">
                                <thead>
                                    <tr>
                                        <?php
                                        $cabeceras = array("$nombre_puesto", "$nombre_objeto Asignados", "Selecionar");
                                        for ($i = 0; $i < (count($cabeceras)); $i++) {
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                                        }
                                        ?>                                                                      
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($rs1 = mysql_fetch_array($result1)) {
                                        $LatitudesTecnicos .= ("/" . $rs1['Latitud']);
                                        $LongitudesTecnicos .= ("/" . $rs1['Longitud']);
                                        $FechaHoraTecnicos .= ("/" . $rs1['FechaUbicacion']);
                                        $userTecnico .= ("/" . $rs1['Loggin']);
                                        $PorcentajeBateria .= ("/" . $rs1['PorcentajeBateria']);
                                        echo "<tr>";
                                        echo "<td align='center' scope='row'>" . $rs1['Usuario'] . " (" . $rs1['Loggin'] . ")</td>";
                                        echo "<td align='center' scope='row'>" . $rs1['Tickets'] . "</td>";
                                        echo "<td align='center' scope='row'><input type='radio' id='radio_tec" . $rs1['IdUsuario'] . "' name='radio_tec' value='" . $rs1['IdUsuario'] . "'/></td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <br/>
                            <?php if ($permisos_grid->getModificar() && ($rs1['estadoTicket'] != "2" && $rs1['estadoTicket'] != "4" && $rs1['IdEstatusAtencion'] != "16" && $rs1['IdEstatusAtencion'] != "59")) { ?>                                
                                <button class='boton' id="asigna_tecnicos" onclick='relacionarTecnicoGeneral();
                                                return false;'>Asignar <?php echo $nombre_objeto; ?>s</button>
                                    <?php } ?>
                                    <?php
                                    echo "<div id='error_tecnico' style='color:red;'></div>";
                                    echo "<input type='hidden' id='nombre_ticket' value='" . $nombre_objeto . "' />";
                                    echo "<input type='hidden' id='LatitudesTickets' value='" . $LatitudesTickets . "' />";
                                    echo "<input type='hidden' id='LongitudesTickets' value='" . $LongitudesTickets . "' />";
                                    echo "<input type='hidden' id='NumeroTicket' value='" . $NumeroTicket . "' />";
                                    //Datos del tecnico
                                    echo "<input type='hidden' id='LatitudesTecnico' value='" . $LatitudesTecnicos . "' />";
                                    echo "<input type='hidden' id='LongitudesTecnico' value='" . $LongitudesTecnicos . "' />";
                                    echo "<input type='hidden' id='FechaTecnico' value='" . $FechaHoraTecnicos . "' />";
                                    echo "<input type='hidden' id='UsuarioTecnico' value='" . $userTecnico . "' />";
                                    echo "<input type='hidden' id='PorcentajeBateria' value='" . $PorcentajeBateria . "' />";
                                }
                                ?>
                    </td>
                </tr>
            </table>             
        </div>
    </body>
</html>