<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

/* Para mantener los filtros y paginados de la tabla */
if (isset($_GET['page']) && isset($_GET['filter'])) {
    $filter = str_replace("_XX__XX_", " ", $_GET['filter']);
    $page = $_GET['page'];
} else {
    $page = "0";
    $filter = "";
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Puesto.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "hardware/mis_tickets.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$tiene_filtro = false;

$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();

$idEjecutivoMA = "18,17,16";

$tecnicos = array();
$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT IdUsuario, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS tecnico "
        . "FROM `c_usuario` "
        . "WHERE Activo = 1 AND (IdPuesto = 18) ORDER BY tecnico;");

$tecnicos["0"] = "Selecciona al $nombre_puesto";
while ($rs = mysql_fetch_array($query)) {
    $tecnicos[$rs['IdUsuario']] = $rs['tecnico'];
}

$cerradoTicket = "t.EstadoDeTicket <> 2 AND ";
$having = " HAVING ((IdEstatusAtencion <> 16 AND IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion))";
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
$Where = " WHERE cl.Suspendido = 0 ";
$NoGuia = "";

if (isset($_POST['idTicket']) && $_POST['idTicket'] != "") {
    $tiene_filtro = true;
    $idTicket = $_POST['idTicket'];
    /* Si se busco un ticket en particular, habilitamos cerrados, morosos y cancelados */
    $checked = "checked='checked'";
    $checkedMoroso = "checked='checked'";
    $checkedCancelado = "checked='checked'";
} else {    
    if (isset($_POST['NoSerie']) && $_POST['NoSerie'] != "") {
        $tiene_filtro = true;
        $NoSerie = $_POST['NoSerie'];
        if ($Where != "") {
            $Where .= "AND (SELECT CASE WHEN e2.Suministro = 1 THEN ( SELECT group_concat( ClaveEspEquipo SEPARATOR ', ') 
            FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) LIKE '%$NoSerie%'";
        }else{
            $Where .= "WHERE (SELECT CASE WHEN e2.Suministro = 1 THEN ( SELECT group_concat( ClaveEspEquipo SEPARATOR ', ') 
            FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) LIKE '%$NoSerie%'";
        }
    }

    if ((isset($_POST['FechaInicio']) && $_POST['FechaInicio'] != "") || (isset($_POST['FechaFin']) && $_POST['FechaFin'] != "")) {
        $tiene_filtro = true;
        if (isset($_POST['FechaInicio']) && $_POST['FechaInicio'] != "" && isset($_POST['FechaFin']) && $_POST['FechaFin'] != "") {
            $FechaInicio = $_POST['FechaInicio'];
            $FechaFin = $_POST['FechaFin'];
            if ($Where != "") {
                $Where .= " AND t.FechaHora BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
            } else {
                $Where .= " t.FechaHora BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
            }
        } else if (isset($_POST['FechaInicio']) && $_POST['FechaInicio'] != "") {
            $FechaInicio = $_POST['FechaInicio'];
            if ($Where != "") {
                $Where .= " AND t.FechaHora >= '$FechaInicio'";
            } else {
                $Where .= " t.FechaHora >= '$FechaInicio'";
            }
        } else if (isset($_POST['FechaFin']) && $_POST['FechaFin'] != "") {
            $FechaFin = $_POST['FechaFin'];
            if ($Where != "") {
                $Where .= " AND t.FechaHora <= '$FechaFin'";
            } else {
                $Where .= " t.FechaHora <= '$FechaFin'";
            }
        }
    }    

    if (isset($_POST['cerrado']) && $_POST['cerrado'] != "false") {
        $cerradoTicket = "";

        if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
            $having = "";
        } else {
            $having = " HAVING ((IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)) ";
        }

        $checked = "checked='checked'";
    } else {
        if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
            $having = " HAVING ((IdEstatusAtencion <> 16) OR ISNULL(IdEstatusAtencion)) ";
        }
    }

    if (isset($_POST['moroso']) && $_POST['moroso'] != "false") {
        $morososTicket = "";
        $checkedMoroso = "checked='checked'";
    }

    if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
        $canceladoTicket = "";
        $checkedCancelado = "checked='checked'";
    }

    if (isset($_POST['area']) && $_POST['area'] != "") {
        $tiene_filtro = true;
        $areaAtencion = " AreaAtencion = " . $_POST['area'] . " AND ";
    }

    if (isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != "") {
        $tiene_filtro = true;
        $tipoReporte = " TipoReporte = " . $_POST['tipoReporte'] . " AND ";
    }

    if (isset($_POST['cliente']) && $_POST['cliente'] != "") {
        $tiene_filtro = true;
        $cliente = " AND t.NombreCliente IN (" . $_POST['cliente'] . ") ";
        $cliente_array = explode("','", $_POST['cliente']);
        $cliente_array[0] = substr($cliente_array[0], 1, strlen($cliente_array[0]));
        $cliente_array[count($cliente_array) - 1] = substr($cliente_array[count($cliente_array) - 1], 0, strlen($cliente_array[count($cliente_array) - 1]) - 1);
    }

    if (isset($_POST['color']) && $_POST['color'] != "") {
        $tiene_filtro = true;
        $colorPOST = $_POST['color'];
    }

    $checked_ultimo = "checked='checked'";
    $checked_todo = "";
    if (isset($_POST['estado']) && $_POST['estado'] != "") {
        $tiene_filtro = true;
        if (isset($_POST['tipo_busqueda_estado']) && $_POST['tipo_busqueda_estado'] == "0") {//Se busca en la ultima nota
            $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdEstatusAtencion = " . $_POST['estado'] . " AND nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
        } else {//Se busca en todos los tickets
            $checked_todo = "checked='checked'";
            $checked_ultimo = "";
            $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = " . $_POST['estado'] . ")";
        }

        if ($_POST['estado'] == "16") {/* Si se selecciona el estado de cerrado, habiliatar el checkbox de cerrado también */
            $cerradoTicket = "";
            if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
                $having = "";
            } else {
                $having = " HAVING ((IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)) ";
            }
            $checked = "checked='checked'";
        }
    }

    if (isset($_POST['NoGuia']) && $_POST['NoGuia'] != "") {
        $NoGuia = $_POST['NoGuia'];
        if ($having != "") {
            $having .= " AND NoGuia LIKE '%$NoGuia%' ";
        } else {
            $having = " HAVING NoGuia LIKE '%$NoGuia%' ";
        }
    }
}

$usuario = new Usuario();
$idUsuario = $_SESSION['idUsuario'];

if ($usuario->getRegistroById($idUsuario)) {//Buscamos las areas de atencion a las que está asociado este puesto
    $consulta = "SELECT GROUP_CONCAT(CONVERT(IdEstado, CHAR(8)) SEPARATOR ',') estados FROM `k_areapuesto` WHERE IdPuesto = " . $usuario->getPuesto() . ";";
    $result = $catalogo->obtenerLista($consulta);
    if (mysql_numrows($result) > 0) {
        while ($rs = mysql_fetch_array($result)) {
            if (!empty($rs['estados'])) {
                $estado = " e2.IdEstado IN (" . $rs['estados'] . ") AND ";
            } else {
                $estado = " e2.IdEstado = 5 AND ";
            }
        }
    } else {
        $estado = "";
    }
} else {
    $estado = "";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_ticket.js"></script>  
        <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <!-- FontAwesome para iconos -->
        <!--link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet"--->  
    </head>

    <body>
        <div class="principal">
            <div class="container-fluid"> 
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label><?php echo $nombre_objeto; ?></label>
                    <input class="form-control"  id="busqueda_ticket" name="busqueda_ticket" value="<?php echo $idTicket; ?>"  />
                    <label id="error_busqueda_ticket" style="display: none; color:red;">Ingresa s&oacute;lo n&uacute;meros por favor</label>
                </div>
                <div class="form-group col-md-4">
                    <label>No. Serie</label>
                    <input class="form-control" id="num_serie" name="num_serie" value="<?php echo $NoSerie; ?>" />
                </div>
                <div class="form-group col-md-4">
                    <label>&Aacute;rea de atenci&oacute;n</label>
                    <select class="form-control" id="area_ticket" name="area_ticket"  >
                        <?php
                        /* Inicializamos la clase */
                        $query = $catalogo->obtenerLista("SELECT DISTINCT(e.IdEstado) AS IdEstado, e.Nombre FROM c_estado AS e
                                INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND (kfe.IdFlujo = 2 OR e.IdEstado = 2) ORDER BY Nombre;");
                        echo "<option value=''>Todos las áreas</option>";
                        while ($rs = mysql_fetch_array($query)) {
                            $s = "";
                            if (isset($_POST['area']) && $_POST['area'] == $rs['IdEstado']) {
                                $s = "selected='selected'";
                            }
                            echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                        }
                        ?>
                    </select>
                </div> 
                <div class="form-group col-md-4">
                    <label>Fecha inicio</label>
                    <input class="form-control" id="fecha_inicio" name="fecha_inicio" class="fecha" value="<?php echo $FechaInicio; ?>"  />
                </div> 
                <div class="form-group col-md-4">
                    <label>Fecha final</label>
                    <input class="form-control" id="fecha_fin" name="fecha_fin" class="fecha" value="<?php echo $FechaFin; ?>"  />
                </div>
                <div class="form-group col-md-4">
                    <label>Tipo Reporte</label>
                    <?php
                    /* Inicializamos la clase */
                    $query = $catalogo->obtenerLista("SELECT e.IdEstado, e.Nombre FROM c_estado AS e
                                INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND kfe.IdFlujo = 1 ORDER BY Nombre;");
                    $numeros = array();
                    $estados = array();
                    $nombres = array();
                    $total = 0;
                    while ($rs = mysql_fetch_array($query)) {
                        $s = "";
                        $numero = "";
                        array_push($nombres, $rs['Nombre']);
                        array_push($estados, $rs['IdEstado']);
                        $queryNumero = "SELECT COUNT(*) AS numero FROM c_ticket t LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket) WHERE t.TipoReporte = ".$rs['IdEstado']. " AND t.EstadoDeTicket != 2 AND t.EstadoDeTicket != 4 AND nt.IdEstatusAtencion != 16 AND nt.IdEstatusAtencion != 59 ";
                        $result = $catalogo->obtenerLista($queryNumero);
                        if($rs2 = mysql_fetch_array($result)){
                            array_push($numeros, $rs2['numero']);
                            $total += $rs2['numero'];
                        }
                    }
                    ?> 
                    <select class="form-control" id="reporte_ticket" name="reporte_ticket"  >
                        <?php
                        echo "<option value=''>Todos los tipos (" . $total . ")</option>";
                        for($i = 0; $i < count($numeros); $i++){
                            if (isset($_POST['tipoReporte']) && $_POST['tipoReporte'] == $estados[$i]) {
                                $s = "selected='selected'";
                            }
                            echo "<option value='" . $estados[$i] . "' $s>" . $nombres[$i] . "<font color='orange'> (".$numeros[$i].")</font></option>";
                        }
                        ?>
                    </select>
                </div>
                <!--<input type="checkbox" id="ticket_cerrado" name="ticket_cerrado" <?php //echo $checked; ?> /><?php //echo $nombre_objeto; ?>s cerrados-->
                <div class="form-group col-12 col-md-4">
                    <label class="m-0">No. Guía</label>
                    <input class="form-control" type="text" id="no_guia" name="no_guia" value="<?php echo $NoGuia; ?>" />
                </div>
                <div class="form-group col-12 col-md-4">
                    <label class="m-0">Estado</label>
                    <select class="form-control" id="estado_ticket" name="estado_ticket"  >
                        <?php
                        /* Inicializamos la clase */
                        $query = $catalogo->obtenerLista("SELECT e.IdEstado, e.Nombre FROM c_estado AS e INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND kfe.IdFlujo = 6 ORDER BY Nombre;");
                        echo "<option value=''>Todos los estados</option>";
                        while ($rs = mysql_fetch_array($query)) {
                            if ($rs['IdEstado'] == "2") {
                                continue;
                            }
                            $s = "";
                            if (isset($_POST['estado']) && $_POST['estado'] == $rs['IdEstado']) {
                                $s = "selected='selected'";
                            }
                            echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                        }
                        ?> 
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Color</label>
                    <select class="form-control" id="ticket_color" name="ticket_color"  >
                        <option value="">Todos</option>
                        <option value="rojo" style="background: #DC381F;">Urgente</option>
                        <option value="amarillo" style="background: #FFF380;">Importante</option>
                        <option value="verde" style="background: #F7F7DE;">Normal</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label >Cliente:</label><br>
                    <select multiple  class="form-control" id="cliente_ticket" name="cliente_ticket[]">
                        <?php
                        if (empty($clientes_permitidos)) {
                            $query = $catalogo->obtenerLista("SELECT DISTINCT(NombreCliente) AS cliente FROM `c_ticket` ORDER BY cliente;");
                        } else {
                            $query = $catalogo->obtenerLista("SELECT DISTINCT(NombreRazonSocial) AS cliente FROM `c_cliente` WHERE ClaveCliente IN($array_clientes) ORDER BY cliente;");
                        }
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
                </div>
                <div class="form-group col-md-4">
                    <label>Búsqueda estado:</label><br>
                    <input type="radio" id="ultimo_estado0" name="ultimo_estado" value="0" <?php echo $checked_ultimo; ?>/>
                    <label>Último estado</label><br>
                    <input type="radio" id="ultimo_estado1" name="ultimo_estado" value="1" <?php echo $checked_todo; ?>/> 
                    <label>Todos los estado del <?php echo $nombre_objeto; ?></label> 
                </div>
                <div class="form-group col-md-4">
                    <label>Filtrar:</label><br>
                    <input type="checkbox" id="ticket_moroso" name="ticket_moroso" <?php echo $checkedMoroso; ?>>
                    <label>Clientes morosos</label>
                </div>
                
                    <input type="button" class="button btn btn-lg btn-block btn-outline-success" onclick="recargarListaTicket('hardware/mis_tickets.php', 'ticket_cerrado', 'cliente_ticket', 'ticket_color', 'estado_ticket', 'ticket_moroso', 'ticket_cancelado', true, 'num_serie', 'fecha_inicio', 'fecha_fin', 'area_ticket', 'reporte_ticket', 'no_guia'); return false;" id="boton_aceptar" name="boton_aceptar" value="Mostrar <?php echo $nombre_objeto; ?>s"/>
    
                <?php
                if ((isset($_POST['mostrar']) && $_POST['mostrar'] == "true") || $idTicket != "") {/* Si se quiere mostrar el grid */
                ?>
                </div>
                <table id="tAlmacen" class="table table-responsive">
                    <thead>
                        <tr>
                            <?php
                            $cabeceras = array("$nombre_objeto", "Fecha", "Cliente", "Falla", "Último estatus $nombre_objeto", "Última Nota", "Fecha nota", "Días atraso", "", "$nombre_puesto", "");
                            for ($i = 0; $i < (count($cabeceras)); $i++) {
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                            }
                            ?>                                                                      
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $consulta = "SELECT
                        t.IdTicket,
                        t.FechaHora,
                        t.DescripcionReporte,
                        t.NombreCentroCosto,
                        t.TipoReporte,
                        (SELECT CASE WHEN e2.Suministro = 1 THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
                        DATEDIFF(NOW(),t.FechaHora) AS diferencia,
                        t.NombreCliente,
                        cl.IdEstatusCobranza,
                        cl.Suspendido,
                        e.IdEstadoTicket AS estadoTicket,                    
                        e1.Nombre AS tipo,           
                        tc.IdTipoCliente AS tipoCliente,
                        e2.Nombre AS area,
                        e2.IdEstado AS idArea,
                        u.Nombre AS ubicacion,
                        cgz.nombre AS ubicacionTicket,
                        e3.Nombre AS estadoNota,
                        nt.IdEstatusAtencion,
                        nt.DiagnosticoSol,
                        nt.FechaHora AS FechaNota,
                        ((5 * (DATEDIFF(NOW(), t.FechaHora) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(t.FechaHora) + WEEKDAY(NOW()) + 1, 1))) AS diasCreacionNota,
                        ((5 * (DATEDIFF(NOW(), nt.FechaHora) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(nt.FechaHora) + WEEKDAY(NOW()) + 1, 1))) AS diasUltimaNota,
                        DATE_FORMAT(NOW(), '%H') AS ahora,
                        DATE_FORMAT(t.FechaHora,'%H') AS horaTicket,
                        DATE_FORMAT(nt.FechaHora, '%H') AS horaUltimaModificacion,
                        (SELECT GROUP_CONCAT(DISTINCT(k_enviotoner.NoGuia) SEPARATOR ', ') AS NoGuia FROM `k_enviotoner`
                        INNER JOIN c_pedido ON c_pedido.IdPedido = k_enviotoner.IdSolicitud
                        INNER JOIN c_ticket ON c_ticket.IdTicket = c_pedido.IdTicket
                        WHERE c_ticket.IdTicket = t.IdTicket GROUP BY c_ticket.IdTicket) AS NoGuia
                        FROM c_ticket AS t                        
                        INNER JOIN c_estadoticket AS e ON $canceladoTicket $cerradoTicket $areaAtencion $tipoReporte e.IdEstadoTicket = t.EstadoDeTicket
                        LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket
                        LEFT JOIN c_gzona AS cgz ON cgz.id_gzona = dt.Id_gzona
                        INNER JOIN c_estado AS e1 ON e1.IdEstado <> 15 AND e1.IdEstado = t.TipoReporte
                        INNER JOIN c_cliente AS cl ON $morososTicket cl.ClaveCliente = t.ClaveCliente
                        LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
                        INNER JOIN c_estado AS e2 ON $estado e2.IdEstado = t.AreaAtencion
                        LEFT JOIN c_ubicacionticket AS u ON u.IdUbicacion = t.Ubicacion
                        $estadoNota
                        LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
                        LEFT JOIN c_usuario AS usu ON usu.Loggin = t.Usuario
                        LEFT JOIN k_ematicket AS ket ON ket.IdTicket = t.IdTicket
                        $Where";

                        if ($idTicket != "") {
                            $consulta.=" AND t.IdTicket = $idTicket ";
                        }
                        
                        $consulta .= " AND (usu.IdPuesto NOT IN($idEjecutivoMA) OR ISNULL(usu.IdPuesto)) AND ket.IdTicket IS NULL $cliente ";                        
                        $consulta.= " $having ORDER BY IdTicket";
                        
                        if (!$tiene_filtro) {
                            $consulta.=" DESC LIMIT 0,500";
                        }
                        $consulta.=";";
                        //echo $consulta;
                        $query2 = $catalogo->obtenerLista($consulta);
                        $array_tickets = array();
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
                                ;
                            } else {
                                array_push($array_tickets, $rs['IdTicket']);
                            }

                            /*                             * *********************    Obtenemos el color de la fila   ******************************** */
                            $color = "#F7F7DE";

                            if (isset($rs['IdEstatusAtencion'])) {/* Si hay estado de la ultima nota */
                                if ($rs['IdEstatusAtencion'] != "16" && (isset($rs['estadoTicket']) && $rs['estadoTicket'] != "2")) {/* Si el ticket no esta cerrado */
                                    if (strtoupper($rs['tipoCliente']) == "1") {/* Si el cliente es VIP */
                                        if (number_format($rs['diferencia']) >= 2) {/* Si ya van mas de 2 dias que se levanto el ticket */
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
                                        if (number_format($rs['diferencia']) >= 7) {/* Si ya van mas de 7 dias que se levanto el ticket */
                                            if ($colorPOST != "" && $colorPOST != "rojo") {
                                                continue;
                                            }
                                            $color = "#DC381F";
                                        }
                                    }
                                }
                            } else {/* Si no hay notas, vemos el estado del ticket */
                                if ($rs['estadoTicket'] != "2") {/* Si el ticket no esta cerrado */
                                    if (strtoupper($rs['tipoCliente']) == "1") {/* Si el cliente es VIP */
                                        if (number_format($rs['diferencia']) >= 2) {/* Si ya van mas de 2 dias que se levanto el ticket */
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
                                        if (number_format($rs['diferencia']) >= 7) {/* Si ya van mas de 7 dias que se levanto el ticket */
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

                            if ($rs['IdEstatusCobranza'] == "2") {/* Cliente moroso */
                                $color = "#D462FF";
                            }

                            if ($rs['estadoTicket'] == "4") {/* Ticket cancelado */
                                $color = "#D1D0CE";
                            }

                            if ($rs['TipoReporte'] == "26") {/* Si es Mtto preventivo */
                                $color = "#00FFFF";
                            }

                            $actual = $rs['ahora'];
                            $horasCreacion = $rs['horaTicket'];
                            $horasModificacion = $rs['horaUltimaModificacion'];
                            if($actual >= $horasCreacion){
                                $diffT = $actual - $horasCreacion;
                            }else{
                                $diasCreacion--;
                                $diffT = 24 - ($horasCreacion - $actual);
                            }
                            if($actual >= $horasModificacion){
                                $diffUT = $actual - $horasModificacion;
                            }else{
                                $diasUltimaModificacion--;
                                $diffUT = 24 - ($horasModificacion - $actual);
                            }
                            
                            echo "<tr style='background-color: $color; color:black;'>";
                            echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['FechaHora'] . " ( " .$rs['diasCreacionNota'] . " días ".$diffT ." horas) </td>";
                            echo "<td align='center' scope='row'>" . $rs['NombreCliente'] . " - " . $rs['NombreCentroCosto'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['DescripcionReporte'] . "</td>";

                            if (isset($rs['estadoNota'])) {
                                echo "<td align='center' scope='row'>" . $rs['estadoNota'] . "</td>";
                            } else {
                                echo "<td align='center' scope='row'></td>";
                            }
                            if (isset($rs['DiagnosticoSol'])) {
                                echo "<td align='center' scope='row'>" . $rs['DiagnosticoSol'] . "</td>";
                            } else {
                                echo "<td align='center' scope='row'></td>";
                            }
                            if (isset($rs['FechaNota'])) {
                                echo "<td align='center' scope='row'>" . $rs['FechaNota'] . " ( ".$rs['diasUltimaNota'] . " días ".$diffUT ." horas)</td>";
                            } else {
                                echo "<td align='center' scope='row'></td>";
                            }
                            echo "<td align='center' scope='row'>" . $rs['diferencia'] . "</td>";

                            if ($rs['idArea'] == "2") {
                                $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketToner.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Detalle&uguid=" . $_SESSION['user'];
                            } else {
                                $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketFalla.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Detalle&uguid=" . $_SESSION['user'];
                            }
                            $fecha_limite = strtotime("2014-03-31");
                            $fecha_ticket = strtotime($rs['FechaHora']);
                            if ($fecha_ticket >= $fecha_limite) {
                                $nuevo = true;
                            } else {
                                $nuevo = false;
                            }
                            ?>

                        <td align='center' scope='row'> 
                            <?php if ($permisos_grid->getConsulta()) { ?>
                                <?php
                                if ($nuevo) {
                                    ?>
                                    <a href='#' onclick='detalleTicket("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "1");
                                                        return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                                   <?php } else { ?>
                                    <a href='#' onclick='lanzarPopUp("Detalle", "<?php echo $src; ?>");
                                                        return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                                       <?php
                                   }
                                   ?>
                               <?php } ?>
                        </td>                                       
                        <?php
                        echo "<td align='center' scope='row'>";
                        if ($rs['estadoTicket'] != "2" && $rs['estadoTicket'] != "4" && $rs['IdEstatusAtencion'] != "16" && $rs['IdEstatusAtencion'] != "59") {
                            echo "<select id='tecnico_" . $rs['IdTicket'] . "' name='tecnico_" . $rs['IdTicket'] . "'>";
                            foreach ($tecnicos as $key => $value) {
                                echo "<option value=" . $key . ">" . $value . "</option>";
                            }
                            echo "</select><div id='error_tecnico_" . $rs['IdTicket'] . "' style='color:red; display:none;'>Selecciona algún $nombre_puesto</div>";
                        }
                        echo "</td>";
                        echo "<td>";
                        if ($permisos_grid->getModificar() && ($rs['estadoTicket'] != "2" && $rs['estadoTicket'] != "4" && $rs['IdEstatusAtencion'] != "16" && $rs['IdEstatusAtencion'] != "59")) {
                            echo "<button class='boton' id='boton_" . $rs['IdTicket'] . "' name='boton_" . $rs['IdTicket'] . "' onclick='relacionarEjecutivo(\"" . $rs['IdTicket'] . "\",\"tecnico_" . $rs['IdTicket'] . "\"); return false;'>Guardar</button>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                    <input type="hidden" id="color_hidden" name="color_hidden" value="<?php echo $colorPOST; ?>"/>
                    <input type="hidden" id="page" name="page" value="<?php echo $page; ?>"/>
                    <input type="hidden" id="filter" name="filter" value="<?php echo $filter; ?>"/>
                </table>
                <?php } ?>
            </div>
        </div>
        <input type="hidden" id="regresar" name="regresar" value="<?php echo $same_page; ?>"/>
    </div>
    </body>
</html>