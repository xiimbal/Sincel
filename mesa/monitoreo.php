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
include_once("../WEB-INF/Classes/Parametros.class.php");

//print_r($_POST);

$permisos_grid = new PermisosSubMenu();
$same_page = "mesa/monitoreo.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$tiene_filtro = false;

date_default_timezone_set("America/Mexico_City");

$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();
$latitud = $permisos_grid2->getLatitudSistema();
$longitud = $permisos_grid2->getLongitudSistema();

$parametros = new Parametros();
$parametros->getRegistroById("8");
$liga = $parametros->getDescripcion();

$parametroGlobal = new ParametroGlobal();
$tiene_permisoTicket = false;
$tiene_permisoFotografico = false;
$muestra_links = false;

if ($parametroGlobal->getRegistroById(15) && $parametroGlobal->getActivo() == "1") {
    $tiene_permisoFotografico = true;
}
if ($parametroGlobal->getRegistroById(16) && $parametroGlobal->getActivo() == "1") {
    $tiene_permisoTicket = true;
}
if ($parametroGlobal->getRegistroById(26) && $parametroGlobal->getValor()) {
    $muestra_links = true;
}

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

$having = " HAVING (IdEstatusAtencion <> 16 AND IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)";
$cerradoTicket = "t.EstadoDeTicket <> 2 AND ";
$checked = "";
$checkedMoroso = "";
$canceladoTicket = "t.EstadoDeTicket <> 4 AND ";
$checkedCancelado = "";
$cliente = "";
$colorPOST = "";
$idTicket = "";
$estadoNota = "LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
$morososTicket = "cl.IdEstatusCobranza <> 2 AND ";
$tipoJoin = "LEFT";

$usuario = new Usuario();
$idUsuario = $_SESSION['idUsuario'];
$where = "";
$viatico = "";

/* Verificamos el puesto del usuario */
if ($usuario->getRegistroById($idUsuario)) {//Buscamos las areas de atencion a las que está asociado este puesto
    $consulta = "SELECT GROUP_CONCAT(CONVERT(IdEstado, CHAR(8)) SEPARATOR ',') estados FROM `k_areapuesto` WHERE IdPuesto = " . $usuario->getPuesto() . ";";
    $result = $catalogo->obtenerLista($consulta);
    if (mysql_numrows($result) > 0) {
        while ($rs = mysql_fetch_array($result)) {
            if (!empty($rs['estados'])) {
                $estado = " t.AreaAtencion IN (" . $rs['estados'] . ") AND ";
                $tipoJoin = "INNER";
            } else {
                $estado = "";
            }
        }
    } else {
        $estado = "";
    }
} else {
    $estado = "";
}

if (isset($_POST['idTicket']) && $_POST['idTicket'] != "") {
    $tiene_filtro = true;
    $idTicket = $_POST['idTicket'];
    /* Si se busco un ticket en particular, habilitamos cerrados, morosos y cancelados */
    //$checked = "checked='checked'";
    $checkedMoroso = "checked='checked'";
    $where .= " t.IdTicket = $idTicket AND ";
    //$checkedCancelado = "checked='checked'";
}

if (isset($_POST['cerrado']) && $_POST['cerrado'] != "false") {
    $cerradoTicket = "";
    $checked = "checked='checked'";
    if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
        $having = "";
    } else {
        $having = " HAVING (IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion) ";
    }
} else {
    if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
        $having = " HAVING (IdEstatusAtencion <> 16) OR ISNULL(IdEstatusAtencion) ";
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

if (isset($_POST['cliente']) && $_POST['cliente'] != "") {
    $tiene_filtro = true;
    $cliente = " t.NombreCliente IN (" . $_POST['cliente'] . ") AND ";
    $cliente_array = explode("','", $_POST['cliente']);
    $cliente_array[0] = substr($cliente_array[0], 1, strlen($cliente_array[0]));
    $cliente_array[count($cliente_array) - 1] = substr($cliente_array[count($cliente_array) - 1], 0, strlen($cliente_array[count($cliente_array) - 1]) - 1);
    $where .= " $cliente ";
}

if (isset($_POST['color'])) {
    $colorPOST = $_POST['color'];
}

if (isset($_POST['estado']) && $_POST['estado'] != "") {
    $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdEstatusAtencion = " . $_POST['estado'] . " AND nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
    if ($_POST['estado'] == "16") {/* Si se selecciona el estado de cerrado, habiliatar el checkbox de cerrado también */
        $cerradoTicket = "";
        if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
            $having = "";
        } else {
            $having = " HAVING (IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion) ";
        }
        $checked = "checked='checked'";
    }
}

$areas = array();
if (isset($_POST['area']) && !empty($_POST['area'])) {
    $areas = $_POST['area'];
    $estado = " t.AreaAtencion IN (" . implode(",", $areas) . ") AND ";
    $tiene_filtro = true;
    $tipoJoin = "INNER";
}

$usuarios = array();
if (isset($_POST['tecnico']) && !empty($_POST['tecnico'])) {
    $usuarios = $_POST['tecnico'];
    $where .= " ktt.IdUsuario IN (" . implode(",", $usuarios) . ") AND ";
}

$prioridades = "";
if (isset($_POST['prioridad']) && $_POST['prioridad'] != "") {
    $prioridades = $_POST['prioridad'];
    $where .= " ktt.IdPrioridad = $prioridades AND ";
}

$ticketMensaje = "";
if (isset($_POST['ticketMensaje']) && $_POST['ticketMensaje'] != "") {
    $ticketMensaje = $_POST['ticketMensaje'];
}
if ($idTicket != "") {
    $ticketMensaje = $idTicket;
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>              
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_ticket.js"></script>       
        <link href="resources/css/mapa/asigna_tecnico.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" language="javascript" src="resources/js/mapas/Label.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/mapas/mapas.js"></script>  
        <style>
            .circleRed{width:25px;height:25px;border-radius:50px;font-size:20px;color:#fff;line-height:100px;text-align:center;background:#ED1C24}
            .circleBlue{width:25px;height:25px;border-radius:50px;font-size:20px;color:#fff;line-height:100px;text-align:center;background:#00A2E8}
            .circleGreen{width:25px;height:25px;border-radius:50px;font-size:20px;color:#fff;line-height:100px;text-align:center;background:#22B14C}
            .circleOrange{width:25px;height:25px;border-radius:50px;font-size:20px;color:#fff;line-height:100px;text-align:center;background:#FF7F27}
        </style>
        <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <!-- FontAwesome para iconos -->
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="principal">            
            <?php if ($muestra_links) { ?>

                <table style="width: 40%;">
                    <tr>
                        <td><?php echo "<a title='Nuevo Servicio' href='principal.php?mnu=viajes&action=alta_autoriza_especial&id='' target='_blank'><button id='nuevo_servicio' class='button'>Nuevo Servicio</button></a>" ?> </td>
                        <td><?php echo "<a title='Asignar Chofer' href='principal.php?mnu=mesa&action=orden_operador&id='' target='_blank'><button id='asigna_chofer' class='button'>Asignar Chofer</button></a>" ?></td>
                        <td><?php echo "<a title='Consulta Ticket' href='principal.php?mnu=mesa&action=lista_ticket&id='' target='_blank'><button id='consulta_ticket' class='button'>Consulta de Ticket</button></a>" ?></td>
                    </tr>
                </table>

            <?php } ?>


                        <?php
                        $parametroGlobal = new ParametroGlobal();
                        for ($i = 20; $i < 26; $i++) {
                            if ($parametroGlobal->getRegistroById($i)) {
                                $valor = $parametroGlobal->getValor();
                                echo "<input type='hidden' id='valor_" . $i . "' value='" . $valor . "' />";
                            }
                        }
                        ?>

                <input type="checkbox" id="ticket_cerrado" name="ticket_cerrado" <?php echo $checked; ?> style="display: none;"/>

                <div class="container-fluid"> 
                   <div class="form-row">     
                      <div class="form-group col-md-4">
                        <label>Ticket</label>
                        <input class="form-control"  type="text" id="busqueda_ticket" name="busqueda_ticket" value="<?php echo $idTicket; ?>"/></td>
                   </div>
                   <div class="form-group col-md-4">
                       <label>Cliente</label><br>
                       <select class="form-control" id="cliente_ticket" name="cliente_ticket" " multiple="multiple">
                            <?php
                            $query = $catalogo->obtenerLista("SELECT DISTINCT(NombreCliente) AS cliente FROM `c_ticket` ORDER BY cliente;");
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
                     <label>Estado</label>
                        <select class="form-control" id="estado_ticket" name="estado_ticket" >
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
                    </div> 

                <div class="form-group col-md-4">
                     <label>Áreas de atención <?php echo $nombre_objeto; ?></label>
                        <select class="form-control" id="area" name="area" class="multiselect" multiple="multiple" >                            
                            <?php
                            /* Inicializamos la clase */
                            $query = $catalogo->obtenerLista("SELECT DISTINCT(e.IdEstado) AS IdEstado, e.Nombre FROM c_estado AS e
                                    INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND (kfe.IdFlujo = 2 OR e.IdEstado = 2) WHERE e.Activo = 1 ORDER BY Nombre;");
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if (!empty($areas) && in_array($rs['IdEstado'], $areas)) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                            }
                            ?> 
                        </select>
                        <div id="error_area" style="font-size: 12px; color: red;"></div>
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label><?php echo $nombre_puesto; ?></label><br>
                        <select class="form-control" id="tecnico" name="tecnico" class="multiselect" multiple="multiple" ">                            
                            <?php
                            /* Inicializamos la clase */
                            $query = $catalogo->getListaAlta("c_usuario", "Nombre");
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if (!empty($usuarios) && in_array($rs['IdUsuario'], $usuarios)) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . " " . $rs['ApellidoMaterno'] . " (" . $rs['Loggin'] . ")</option>";
                            }
                            ?> 
                        </select>
                        <div id="error_area" style="font-size: 12px; color: red;"></div>
                    </div>

                    <div class="form-group col-md-4">
                        <label> Prioridades</label>
                        <select class="form-control" id="prioridad" name="prioridad" class="select">
                            <option value="">Todas las prioridades</option>
                            <?php
                            /* Inicializamos la clase */
                            $query = $catalogo->obtenerLista("select  pt.IdPrioridad,pt.Prioridad, tp.TipoPrioridad, c.Hexadecimal 
                                from c_prioridadticket AS pt
                                LEFT JOIN c_tipoprioridad AS tp ON pt.IdTipoPrioridad = tp.IdTipoPrioridad
                                LEFT JOIN c_color AS c ON c.IdColor = pt.IdColor WHERE pt.Activo = 1;");
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if (!empty($prioridades) && $rs['IdPrioridad'] == $prioridades) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['IdPrioridad'] . "' style='background=\"#" . $rs['Hexadecimal'] . "\"' $s>" . $rs['Prioridad'] . " (" . $rs['TipoPrioridad'] . ")</option>";
                            }
                            ?> 
                        </select>
                        <div id="error_area" style="font-size: 12px; color: red;"></div>
                    </div>

                
            <div class="form-group col-12 col-md-3">
                    <center><label> Programados</label><div class="circleRed"></div></center>
             </div>  
            <div class="form-group col-12 col-md-3">
                    <center><label> Check in</label><div class="circleBlue"></div></center>
             </div>        
            <div class="form-group col-12 col-md-3">        
                    <center><label> Check-out éxitosos</label><div class="circleGreen"></div></center>
             </div>  
            <div class="form-group col-12 col-md-3">
                    <center><label> Check-out fallidos</label><div class="circleOrange"></div></center>
             </div>
            

                 <input type="button" class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" onclick="
                    recargarListaTicketTecnico('mesa/monitoreo.php', 'busqueda_ticket', null, 'cliente_ticket', 'ticket_color',
                            'estado_ticket', null, null, true, null, null, null, 'area', null, null, 'tecnico', 'prioridad');
                    return false;" 
                   id="boton_aceptar" name="boton_aceptar" value="Mostrar / Actualizar"/>
            <br/><br/>
            </div> 
            </div>

            <div class="form-row">
                <div class="form-group col-md-12"><!--Aqui se pone el mapa--> 
                    <td style="vertical-align: text-top; width: 70%;"><!--Aqui se pone el mapa-->
                        <div id="map-canvas" style="height: 600px;">Aquí mapa</div>
                    </td>
                </div>
            </div>
            
            <div class="form-row">
    <div class="form-group col-md-12">
        <?php
        if (isset($_POST['idTicket'])) {
            $consulta = "SELECT t.IdTicket, u.IdUsuario, u.Loggin, CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Usuario,
            nt.IdEstatusAtencion, t.TipoReporte,
            (CASE WHEN !ISNULL(dt.Latitud) THEN dt.Latitud WHEN !ISNULL(dcc.Latitud) THEN dcc.Latitud  ELSE $latitud END) AS Latitud, 
            (CASE WHEN !ISNULL(dt.Longitud) THEN dt.Longitud WHEN !ISNULL(dcc.Longitud) THEN dcc.Longitud  ELSE $longitud END) AS Longitud,
            GROUP_CONCAT(DISTINCT nt3.DiagnosticoSol SEPARATOR '<br/>*') AS Mensajes,
            (CASE WHEN !ISNULL(ubu.IdUbicacion) THEN ubu.Fecha ELSE 'SR' END) AS FechaUbicacion,
            (CASE WHEN !ISNULL(ubu.IdUbicacion) THEN ubu.Latitud ELSE $latitud END) AS LatitudUser,
            (CASE WHEN !ISNULL(ubu.IdUbicacion) THEN ubu.Longitud ELSE $longitud END) AS LongitudUser,
            ubu.PorcentajeBateria, pt.Prioridad, ktt.Duracion, um.Unidad, 
            (CASE WHEN !ISNULL(nt4.FechaHora) THEN nt4.FechaHora ELSE ktt.FechaHoraInicio END) AS FechaHoraInicio,
            (CASE WHEN !ISNULL(nt4.IdEstatusAtencion) THEN nt4.IdEstatusAtencion ELSE 'null' END) AS IdEstatusAtencion2
            FROM k_tecnicoticket AS ktt
            LEFT JOIN c_usuario AS u ON u.IdUsuario = ktt.IdUsuario
            LEFT JOIN c_prioridadticket AS pt ON ktt.IdPrioridad = pt.IdPrioridad
            LEFT JOIN c_unidadmedida AS um ON um.IdUnidad = ktt.IdUnidadDuracion
            $tipoJoin JOIN c_ticket AS t ON $estado t.IdTicket = ktt.IdTicket
            $estadoNota 
            LEFT JOIN c_notaticket AS nt3 ON nt3.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket)
            LEFT JOIN c_notaticket AS nt4 ON nt4.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket AND IdEstatusAtencion IN(51,16,14))
            LEFT JOIN c_cliente AS c ON t.ClaveCliente = c.ClaveCliente
            LEFT JOIN c_centrocosto AS cc ON t.ClaveCentroCosto = cc.ClaveCentroCosto
            LEFT JOIN c_domicilio AS dcc ON dcc.IdDomicilio = (SELECT MIN(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = cc.ClaveCentroCosto)
            LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket
            LEFT JOIN c_ubicacionusuario AS ubu ON ubu.IdUbicacion = (SELECT MAX(IdUbicacion) FROM c_ubicacionusuario WHERE IdUsuario = u.IdUsuario)
            WHERE $where   
            ( ( ((nt.IdEstatusAtencion <> 16 AND nt.IdEstatusAtencion <> 59) OR ISNULL(nt.IdEstatusAtencion)) AND t.EstadoDeTicket NOT IN(2,4) ) OR DATE(ktt.FechaHoraInicio) >= DATE(NOW()) )
            GROUP BY t.IdTicket
            ORDER BY u.IdUsuario,t.IdTicket;";
            //echo $consulta;
            $result = $catalogo->obtenerLista($consulta);
            $LatitudesTecnicos = "";
            $LongitudesTecnicos = "";
            $LatitudesTecnicosDia = "";
            $LongitudesTecnicosDia = "";
            $FechaHoraTecnicos = "";
            $userTecnico = "";
            $EstatusAtencion = "";
            $LatitudesTickets = "";
            $LongitudesTickets = "";
            $NumeroTicket = "";
            $PrioridadesTicket = "";
            $Tiempo = "";
            $FechaInicio = "";
            $PorcentajeBateria = "";
            ?>
            <h2>Mensajes</h2>
            <table class="tablaUsuarios">|
                <thead>
                    <tr>
                        <?php
                        $cabeceras = array("$nombre_puesto", $nombre_objeto, "Mensaje", "");
                        $tecnicos_procesados = array();
                        $tickets_mostrados = array();
                        if ($tiene_permisoTicket) {//Si tiene permiso para reporte de tickets
                            array_push($cabeceras, "");
                        }

                        if ($tiene_permisoFotografico) {//Si tiene permiso para reporte fotografico
                            array_push($cabeceras, "");
                        }

                        for ($i = 0; $i < (count($cabeceras)); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        ?> 
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $inactivo30T = "";
                    while ($rs = mysql_fetch_array($result)) {
                        array_push($tickets_mostrados, $rs['IdTicket']);
                        $LatitudesTickets .= ("/" . $rs['Latitud']);
                        $LongitudesTickets .= ("/" . $rs['Longitud']);
                        $NumeroTicket .= ("/" . $rs['IdTicket']);
                        $PrioridadesTicket .= ("/" . $rs['Prioridad']);
                        $Tiempo .= ("/" . $rs['Duracion'] . " " . $rs['Unidad']);
                        $FechaInicio .= ("/" . $rs['FechaHoraInicio']);
                        $EstatusAtencion .= ("/" . $rs['IdEstatusAtencion']);
                        $today = date("Y-m-d");
                        $fecha = $rs['FechaUbicacion'];
                        $comentarioAdd = "";
                        if (strcmp($rs['IdEstatusAtencion'], "null")) {
                            if (strcmp($rs['IdEstatusAtencion'], "51") == 0) {
                                $comentarioAdd = "Check-in " . $rs['FechaHoraInicio'];
                            } else if (strcmp($rs['IdEstatusAtencion'], "14") == 0) {
                                $comentarioAdd = "Check-out fallido " . $rs['FechaHoraInicio'];
                            }
                        }
                        //Tecnicos
                        if (!in_array($rs['Loggin'], $tecnicos_procesados)) {
                            if (!isset($rs['IdUsuario']) || empty($rs['IdUsuario'])) {
                                continue;
                            }
                            $inactivo30 = "false";
                            if (strcmp($today, substr($fecha, 0, 10)) == 0) {
                                $horaActual = date("H");
                                $minutoActual = date("i");
                                $horaSis = substr($fecha, 11, 2);
                                $minSis = substr($fecha, 14, 2);
                                if ($horaActual - $horaSis > 1) {
                                    $inactivo30 = "true";
                                } else if ($horaActual - $horaSis) {
                                    $difSis = 60 - $minSis;
                                    if ($minutoActual + $difSis > 30) {
                                        $inactivo30 = "true";
                                    }
                                } else if ($horaActual == $horaSis) {
                                    if ($minutoActual - $minSis > 30) {
                                        $inactivo30 = "true";
                                    }
                                } else {
                                    $inactivo30 = "true";
                                }
                            } else {
                                $inactivo30 = "true";
                            }
                            $inactivo30T.=("/" . $inactivo30);
                            $LatitudesTecnicos .= ("/" . $rs['LatitudUser']);
                            $LongitudesTecnicos .= ("/" . $rs['LongitudUser']);
                            $FechaHoraTecnicos .= ("/" . $rs['FechaUbicacion']);
                            $userTecnico .= ("/" . $rs['Loggin']);
                            array_push($tecnicos_procesados, $rs['Loggin']);
                            $consulta = "SELECT Latitud,Longitud,PorcentajeBateria FROM `c_ubicacionusuario` WHERE IdUsuario = " . $rs['IdUsuario'] . " AND DATE(Fecha) >= DATE(NOW());";
                            $result2 = $catalogo->obtenerLista($consulta);
                            $PorcentajeBateria .= ("/");
                            $LatitudesTecnicosDia .= ("/");
                            $LongitudesTecnicosDia .= ("/");
                            if (mysql_num_rows($result2) > 0) {
                                while ($rs2 = mysql_fetch_array($result2)) {
                                    $PorcentajeBateria .= ($rs2['PorcentajeBateria'] . ",");
                                    $LatitudesTecnicosDia .= ($rs2['Latitud'] . ",");
                                    $LongitudesTecnicosDia .= ($rs2['Longitud'] . ",");
                                }
                                $PorcentajeBateria = substr($PorcentajeBateria, 0, strlen($PorcentajeBateria) - 1);
                                $LatitudesTecnicosDia = substr($LatitudesTecnicosDia, 0, strlen($LatitudesTecnicosDia) - 1);
                                $LongitudesTecnicosDia = substr($LongitudesTecnicosDia, 0, strlen($LongitudesTecnicosDia) - 1);
                            }
                            /* $PorcentajeBateria .= ("/".$rs['PorcentajeBateria']);
                            $LatitudesTecnicosDia .= ("/".$rs['LatitudesDia']);
                            $LongitudesTecnicosDia .= ("/".$rs['LongitudesDia']); */
                        }

                        $catalogoes = new Catalogo();
                        $notes = "";
                        $numes = 0;
                        $consultaes = "SELECT * FROM c_notaticket cn INNER JOIN c_especial ce ON ce.idTicket=cn.IdTicket WHERE ce.idTicket=" . $rs['IdTicket'] . ";";
                        $resultes = $catalogoes->obtenerLista($consultaes);
                        if (mysql_num_rows($resultes) > 0) {
                            while ($rses = mysql_fetch_array($resultes)) {
                                $numes++;
                                $notes = $notes . $numes . ". " . $rses['DiagnosticoSol'] . "  ";
                            }
                        }
                        echo "<tr>";
                        echo "<td align='center' scope='row' style='width: 25%;'>" . $rs['Usuario'] . " (" . $rs['Loggin'] . ")</td>";
                        echo "<td align='center' scope='row' style='width: 15%;'>" . $rs['IdTicket'] . "</td>";
                        echo "<td title='" . $notes . "' align='center' scope='row'>" . $rs['Mensajes'] . " " . $comentarioAdd . "</td>";
                        echo "<td align='center' scope='row' style='width: 10%;'>";
                        if ($permisos_grid->getConsulta()) {
                            echo "<a href='#' onclick='detalleTicket(\"mesa/alta_ticketphp.php\"," . $rs['IdTicket'] . ", " . $rs['TipoReporte'] . ", 1);
                            return false;' title='Detalle' ><img src=\"resources/images/Textpreview.png\"/></a>";
                        }

                        if ($tiene_permisoTicket) {
                            ?>
                            <td align='center' scope='row'>
                                <a href='#' onclick='detalleReporte("reportes/reporte_ticket.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['idArea']; ?>", "0"); return false;' title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>
                            </td>
                            <?php
                        }
                        ?>
                        <?php
                        if ($tiene_permisoFotografico) {
                            ?>
                            <td align='center' scope='row'>
                                <a href='#' onclick='detalleReporte("reportes/reporte_fotografico.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['idArea']; ?>", "0"); return false;' title='Reporte' ><img src="resources/images/camera2.png" width="35" height="35"/></a>
                            </td>
                            <?php
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            <br/>
            <?php if ($permisos_grid->getModificar() && ($rs['estadoTicket'] != "2" && $rs['estadoTicket'] != "4" && $rs['IdEstatusAtencion'] != "16" && $rs['IdEstatusAtencion'] != "59")) { ?>
                <form id="formEnviarMensaje" name="formEnviarMensaje" action="/" method="POST" enctype="multipart/form-data">
                    <table style="width: 100%;">
                        <?php if ($muestra_links) { //Links mostrados para Loyalty
                            ?> 
                            <tr>
                                <td><?php
                                echo $nombre_objeto;
                                echo "<input type='hidden' id='otraNota' name='otraNota' value=$muestra_links />";
                                ?></td>
                                <td>
                                    <select id="ticket_mensaje2" name="ticket_mensaje2" onchange="recargarListaTicketTecnico('mesa/monitoreo.php', 'busqueda_ticket', null, 'cliente_ticket', 'ticket_color', 'estado_ticket', null, null, true, null, null, null, 'area', null, null, 'tecnico', 'prioridad');">
                                        <option value="0">Selecciona el <?php echo $nombre_objeto; ?> para mensaje</option>
                                        <?php
                                        foreach ($tickets_mostrados as $value) {
                                            $s = "";
                                            if ($ticketMensaje != "" && $ticketMensaje == $value) {
                                                $s = "selected";
                                            }
                                            echo "<option value='$value' $s>$value</option>";
                                        }
                                        ?>
                                    </select>
                                    <span id="error_ticket2" style="color: red;"></span>
                                </td>
                            </tr>
                            <?php
                            $FechaNota = date('Y') . "-" . date('m') . "-" . date('d');
                            $HoraNota = date('H') . ":" . date('i') . ":" . date('s');
                            ?>
                            <tr>
                                <td><label for="fecha">Fecha</label><span class="obligatorio"> *</span></td>
                                <td><input type="text" id="fecha" name="fecha" value="<?php echo $FechaNota; ?>"/></td>
                                <td><label for="orden"></label>Hora<span class="obligatorio"> *</span></td>
                                <td><input type="text" id="hora" name="hora" value="<?php echo $HoraNota; ?>"/></td>
                            </tr>
                            <tr>
                                <td>Eventos</td>
                                <td>
                                    <select id="estatusN" name="estatusN" style="width: 200px" onchange="mostrarRefacciones();" onkeyup="copia('5');">
                                        <?php
                                        $catalogo = new Catalogo();
                                        $consul1 = "SELECT cu.IdPuesto, cnt.IdNotaTicket AS IdNota, cnt.DiagnosticoSol AS DS, IdEstatusAtencion AS IdEA FROM k_tecnicoticket AS ktt LEFT JOIN c_usuario AS cu ON ktt.IdUsuario=cu.IdUsuario
                                        LEFT JOIN c_notaticket AS cnt ON cnt.IdTicket=ktt.IdTicket AND cnt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket)
                                        WHERE ktt.IdTicket=" . $ticketMensaje . ";";

                                        $query = $catalogo->obtenerLista($consul1);
                                        $rs = mysql_fetch_array($query);
                                        $idArea = "";
                                        if ($rs['IdPuesto'] == 101) {
                                            $idArea = 101;
                                        } else {
                                            if ($rs['IdPuesto'] == 108) {
                                                $idArea = 102;
                                            } else {
                                                if ($rs['IdPuesto'] == 109) {
                                                    $idArea = 103;
                                                }
                                            }
                                        }
                                        $EstatusAc = $rs['IdEA'];
                                        $consul2 = "SELECT e.IdEstado, e.Nombre FROM c_estado AS e
                                        INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND kfe.IdFlujo = 6 
                                        WHERE e.IdEstado IN(92,51,274,275,276,277,278,59,16) OR e.IdArea=" . $idArea . " ORDER BY e.Nombre;";
                                        $query = $catalogo->obtenerLista($consul2);
                                        echo "<option value=0>Selecciona un evento</option>";
                                        while ($rs = mysql_fetch_array($query)) {
                                            $s = "";
                                            if ($EstatusAc != "" && $EstatusAc == $rs['IdEstado']) {
                                                $s = "selected='selected'";
                                            }
                                            echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                        }
                                        ?> 
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Subir imagen:</td>
                                <td><input type='file' name='file' id='file'></td>
                            </tr>
                            <tr>
                                <td valign="top">
                                    <div id="viatico">
                                        <table>
                                            <tr>
                                                <td>Tipo de viático:</td>
                                                <td>
                                                    <select id='tipo_viatico' name='tipo_viatico' style='max-width: 300px'>
                                                        <option value='0'>Seleccione una opción</option>
                                                        <?php
                                                        $catalogo1 = new Catalogo();
                                                        $query1 = $catalogo1->obtenerLista("SELECT * FROM c_tipoviatico WHERE activo=1  ORDER BY nombre;");
                                                        while ($rs = mysql_fetch_array($query1)) {
                                                            $s = "";
                                                            if ($viatico != "" && $viatico == $rs['idTipoViatico']) {
                                                                $s = "selected";
                                                            }
                                                            echo "<option value='" . $rs['idTipoViatico'] . "' $s>" . $rs['nombre'] . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td> Monto:</td>
                                                <td>
                                                    <input type='text' style='max-width: 100px' id='monto' name='monto' value="<?php $monto = "";echo $monto; ?>" onkeyup="copia('4');"/>
                                                </td>
                                                <td><input type="checkbox" id="cobrar" name="cobrar" value="1" checked="checked"/> Cobrar al cliente</td>
                                                <td><input type="checkbox" id="pagar" name="pagar" value="1"/> Pagar a operador</td>
                                            </tr>
                                        </table>        
                                    </div>
                                    <div id="kmdiv">
                                        <table>
                                            <tr>
                                                <td> Kilómetro:</td>
                                                <td><input type='text' style='max-width: 100px' id='km' name='km' value="<?php $km = ""; echo $km; ?>" onkeyup="copia('1');"/></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div id="tiempoE">
                                        <table>
                                            <tr>
                                                <td> Tiempo Real m:</td>
                                                <td><input type='text' style='max-width: 100px' id='tiempo_esperaR' name='tiempo_esperaR' value="<?php $tiempoER = ""; echo $tiempoER; ?>" onkeyup="copia('3');"/></td>
                                                <td> Tiempo Restado m:</td>
                                                <td><input type='text' style='max-width: 100px' id='tiempo_esperaM' name='tiempo_esperaM' value="<?php $tiempoEM = 0; echo $tiempoEM; ?>"/></td>
                                            </tr>
                                        </table>        
                                    </div>
                                    <div id="noBoleto">
                                        <table>
                                            <tr>
                                                <td> No Boleto:</td>
                                                <td><input type='text' style='max-width: 100px' id='no_boleto' name='no_boleto' value="<?php $noBoleto = ""; echo $noBoleto; ?>" onkeyup="copia('2');"/></td>
                                            </tr>
                                        </table>        
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><label>Mensaje</label></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <input type="text" class="form-control" id="mensaje_enviar2" name="mensaje_enviar2" maxlength="500" style="width: 99%;"/>
                                    <span id="error_mensaje2" style="color: red;"></span>
                                </td>
                            </tr>
                            <tr>                    
                                <td colspan="2">
                                    <input type="submit" id="enviar_mensaje2" class="boton" value="Enviar Nota" />
                                </td>
                            </tr>
                    </table>
                        <?php } else { ?>
                    </table>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label><?php echo $nombre_objeto; ?></label>
                            <select class="form-control" id="ticket_mensaje">
                                <option value="">Selecciona el <?php echo $nombre_objeto; ?> para mensaje</option>
                                <?php
                                foreach ($tickets_mostrados as $value) {
                                    echo "<option value='$value'>$value</option>";
                                }
                                ?>
                            </select>
                            <span id="error_ticket" style="color: red;"></span>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Mensaje</label>
                            <input type="text" class="form-control" id="mensaje_enviar" name="mensaje_enviar" maxlength="500"/>
                            <span id="error_mensaje" style="color: red;"></span>
                            <button id="enviar_mensaje" class="button btn btn-lg btn-block btn-outline-primary mt-3 mb-3" onclick="enviarMensaje('ticket_mensaje', 'mensaje_enviar', 'error_ticket', 'enviar_mensaje', 'error_mensaje'); return false;">Enviar mensaje</button>
                        </div>
                    </div>
                        <?php } ?>
                </form> 
            <?php } ?>
            <?php
            echo "<input type='hidden' id='nombre_ticket' value='" . $nombre_objeto . "' />";
            echo "<div id='error_tecnico' style='color:red;'></div>";
            echo "<input type='hidden' id='LatitudesTickets' value='" . $LatitudesTickets . "' />";
            echo "<input type='hidden' id='LongitudesTickets' value='" . $LongitudesTickets . "' />";
            echo "<input type='hidden' id='NumeroTicket' value='" . $NumeroTicket . "' />";
            echo "<input type='hidden' id='EstatusTicket' value='" . $EstatusAtencion . "' />";
            //Datos del tecnico
            echo "<input type='hidden' id='LatitudesTecnico' value='" . $LatitudesTecnicos . "' />";
            echo "<input type='hidden' id='LongitudesTecnico' value='" . $LongitudesTecnicos . "' />";
            echo "<input type='hidden' id='FechaTecnico' value='" . $FechaHoraTecnicos . "' />";
            echo "<input type='hidden' id='UsuarioTecnico' value='" . $userTecnico . "' />";
            echo "<input type='hidden' id='PrioridadesT' value='" . $PrioridadesTicket . "' />";
            echo "<input type='hidden' id='TiempoT' value='" . $Tiempo . "' />";
            echo "<input type='hidden' id='FechaInicioT' value='" . $FechaInicio . "' />";
            echo "<input type='hidden' id='PorcentajeBateria' value='" . $PorcentajeBateria . "' />";
            echo "<input type='hidden' id='LatitudesTecnicoDia' value='" . $LatitudesTecnicosDia . "' />";
            echo "<input type='hidden' id='LongitudesTecnicoDia' value='" . $LongitudesTecnicosDia . "' />";
            echo "<input type='hidden' id='inactivo30' value='" . $inactivo30T . "' />";
        }
        ?>
    </div>
</div>            
        </div>
    </body>
</html>