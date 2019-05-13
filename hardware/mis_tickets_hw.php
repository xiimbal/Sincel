<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/ParametroGlobal.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "hardware/mis_tickets_hw.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();

$catalogo = new Catalogo();
$usuario = new Usuario();
$style = '';
$idHW = $_SESSION['idUsuario'];
$idTicket = "";
$tiene_filtro = false;
$idEjecutivoMA = 18;
$Where = "";

$parametroGlobal = new ParametroGlobal();
$pantalla_edicion = "mesa/alta_ticketphp.php";
if($parametroGlobal->getRegistroById(28) && $parametroGlobal->getActivo() == "1"){
    $pantalla_edicion = $parametroGlobal->getValor();
}

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

if (isset($_POST['idUsuario']) && $_POST['idUsuario'] != "todos") {
    $idHW = $_POST['idUsuario'];
    $tiene_filtro = true;
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
$estadoNota = "LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
$idTicket = "";
$NoSerie = "";
$FechaInicio = "";
$FechaFin = "";
$Where = "";
$NoGuia = "";
$tipo_join_estado = "LEFT";

if ((isset($_POST['idTicket']) && $_POST['idTicket'] != "") || (isset($_GET['id']) && $_GET['id'] != "")) {
    $tiene_filtro = true;
    if (isset($_POST['idTicket'])) {
        $idTicket = $_POST['idTicket'];
    } 
    /* Si se busco un ticket en particular, habilitamos cerrados, morosos y cancelados */
    $checked = "checked='checked'";
    $checkedMoroso = "checked='checked'";
    $checkedCancelado = "checked='checked'";
}

if (isset($_POST['NoSerie']) && $_POST['NoSerie'] != "") {
    $tiene_filtro = true;
    $NoSerie = $_POST['NoSerie'];
    $Where = "WHERE (SELECT CASE WHEN e2.IdEstado = 2 THEN ( SELECT group_concat( ClaveEspEquipo SEPARATOR ', ') 
        FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) LIKE '%$NoSerie%'";
}

if ((isset($_POST['FechaInicio']) && $_POST['FechaInicio'] != "") || (isset($_POST['FechaFin']) && $_POST['FechaFin'] != "")) {
    $tiene_filtro = true;
    if (isset($_POST['FechaInicio']) && $_POST['FechaInicio'] != "" && isset($_POST['FechaFin']) && $_POST['FechaFin'] != "") {
        $FechaInicio = $_POST['FechaInicio'];
        $FechaFin = $_POST['FechaFin'];
        if ($Where != "") {
            $Where .= " AND t.FechaHora BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
        } else {
            $Where = "WHERE t.FechaHora BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
        }
    } else if (isset($_POST['FechaInicio']) && $_POST['FechaInicio'] != "") {
        $FechaInicio = $_POST['FechaInicio'];
        if ($Where != "") {
            $Where .= " AND t.FechaHora >= '$FechaInicio'";
        } else {
            $Where = "WHERE t.FechaHora >= '$FechaInicio'";
        }
    } else if (isset($_POST['FechaFin']) && $_POST['FechaFin'] != "") {
        $FechaFin = $_POST['FechaFin'];
        if ($Where != "") {
            $Where .= " AND t.FechaHora <= '$FechaFin'";
        } else {
            $Where = "WHERE t.FechaHora <= '$FechaFin'";
        }
    }
}

/*Filtrar prioridad*/
if(isset($_POST['Prioridad']) && $_POST['Prioridad'] != 0){
    if($Where != ""){
        $Where .= " AND t.Prioridad = ".$_POST['Prioridad'];
    }else{
        $Where = "WHERE t.Prioridad = ".$_POST['Prioridad'];
    }
}

/*Filtrar estado del ticket*/
if (isset($_POST['estadoT']) && $_POST['estadoT'] != "")
{
    if($Where != ""){
        $Where .= " AND t.EstadoDeTicket = ".$_POST['estadoT'];
    }else{
        $Where = "WHERE t.EstadoDeTicket = ".$_POST['estadoT'];
    }
}

if (!empty($idTicket)) {
    if($Where != ""){
        $Where .= " AND t.IdTicket = $idTicket";
    }else{
        $Where .= " WHERE t.IdTicket = $idTicket";
    }  
} else {
    if($Where == ""){
        $Where .= " WHERE cl.Suspendido = 0";
    }else{
        $Where .= " AND cl.Suspendido = 0";
    }
}

if ((isset($_POST['cerrado']) && $_POST['cerrado'] != "false") || (isset($_POST['estadoT']) && $_POST['estadoT'] == 2)) {
    $cerradoTicket = "";

    if ((isset($_POST['cancelado']) && $_POST['cancelado'] != "false") || (isset($_POST['estadoT']) && $_POST['estadoT'] == 4)) {
        $having = "";
    } else {
        $having = " HAVING ((IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)) ";
    }

    $checked = "checked='checked'";
} else {
    if ((isset($_POST['cancelado']) && $_POST['cancelado'] != "false") || (isset($_POST['estadoT']) && $_POST['estadoT'] == 4)) {
        $having = " HAVING ((IdEstatusAtencion <> 16) OR ISNULL(IdEstatusAtencion)) ";
    }
}

if (isset($_POST['moroso']) && $_POST['moroso'] != "false") {
    $morososTicket = "";
    $checkedMoroso = "checked='checked'";
}

if ((isset($_POST['cancelado']) && $_POST['cancelado'] != "false") || (isset($_POST['estadoT']) && $_POST['estadoT'] == 4)) {
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
    $cliente = " AND t.NombreCliente IN (" . $_POST['cliente'] . ")";
    $cliente_array = explode("','", $_POST['cliente']);
    $cliente_array[0] = substr($cliente_array[0], 1, strlen($cliente_array[0]));
    $cliente_array[count($cliente_array) - 1] = substr($cliente_array[count($cliente_array) - 1], 0, strlen($cliente_array[count($cliente_array) - 1]) - 1);
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
        if ((isset($_POST['cancelado']) && $_POST['cancelado'] != "false") || (isset($_POST['estadoT']) && $_POST['estadoT'] == 4)) {
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

if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 18)) {
    $style = "display:none;";
}

if ($usuario->isUsuarioPuesto($idHW, 18)) {
    $consulta = "SELECT
        b.id_bitacora,
        t.IdTicket,
        t.FechaHora,
        t.DescripcionReporte,
        t.NombreCentroCosto, 
        t.TipoReporte,
        (SELECT CASE WHEN e2.Nombre = 'Suministro' THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
        DATEDIFF(NOW(),t.FechaHora) AS diferencia,
        t.NombreCliente,
        cl.Suspendido,
        e.Nombre AS estado,
        e1.Nombre AS tipo,
        t.ClaveCentroCosto,
        cl.ClaveCliente,	
        tc.Nombre AS tipoCliente,
        col.Hexadecimal,
        e2.Nombre AS area,
        u.Nombre AS ubicacion,	
        (SELECT CONCAT(ce.Nombre,'**__**',nt2.DiagnosticoSol,'**__**',nt2.FechaHora) FROM c_estado AS ce INNER JOIN c_notaticket AS nt2 ON nt2.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket) AND ce.IdEstado = nt2.IdEstatusAtencion AND nt2.IdEstatusAtencion <> 16) AS UltimoEstatus,
        e.IdEstadoTicket AS estadoTicket,
        nt.IdEstatusAtencion
        FROM k_ematicket AS ktt
        INNER JOIN c_ticket AS t ON ktt.IdTicket = t.IdTicket                    
        INNER JOIN c_estadoticket AS e ON $tipoReporte $areaAtencion $canceladoTicket $cerradoTicket e.IdEstadoTicket = t.EstadoDeTicket $cliente
        INNER JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte
        INNER JOIN c_cliente AS cl ON cl.ClaveCliente = t.ClaveCliente
        LEFT JOIN c_prioridadticket AS pt ON t.Prioridad = pt.IdPrioridad
        LEFT JOIN c_color AS col ON pt.IdColor = col.IdColor
        INNER JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
        INNER JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion
        LEFT JOIN c_ubicacionticket AS u ON u.IdUbicacion = t.Ubicacion
        $estadoNota
        LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
        LEFT JOIN c_usuario AS usu ON usu.Loggin = t.Usuario
        ";

    $consulta .= " $Where AND (ktt.IdUsuario = $idHW OR usu.IdUsuario = $idHW) $having ORDER BY IdTicket desc";
    
} else {
    $consulta = "SELECT 
        b.id_bitacora,
        t.IdTicket,
        t.FechaHora,
        t.DescripcionReporte,
        t.NombreCentroCosto, 
        t.TipoReporte,
        (SELECT CASE WHEN e2.Nombre = 'Suministro' THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
        DATEDIFF(NOW(),t.FechaHora) AS diferencia,
        t.NombreCliente,
        e.Nombre AS estado,       
        e1.Nombre AS tipo,
        cl.Suspendido,
        t.ClaveCentroCosto,
        cl.ClaveCliente,	
        tc.Nombre AS tipoCliente,
        e2.Nombre AS area,
        e2.IdEstado AS idArea,
        col.Hexadecimal,
        u.Nombre AS ubicacion,	
        (SELECT CONCAT(ce.Nombre,'**__**',nt2.DiagnosticoSol,'**__**',nt2.FechaHora) FROM c_estado AS ce INNER JOIN c_notaticket AS nt2 ON nt2.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket) AND ce.IdEstado = nt2.IdEstatusAtencion AND nt2.IdEstatusAtencion <> 16) AS UltimoEstatus, 
        e.IdEstadoTicket AS estadoTicket,
        nt.IdEstatusAtencion
        FROM c_ticket AS t                
        INNER JOIN c_estadoticket AS e ON $tipoReporte $areaAtencion $canceladoTicket $cerradoTicket e.IdEstadoTicket = t.EstadoDeTicket $cliente
        INNER JOIN c_estado AS e1 ON e1.IdEstado <> 15 AND e1.IdEstado = t.TipoReporte
        INNER JOIN c_cliente AS cl ON cl.ClaveCliente = t.ClaveCliente
        LEFT JOIN c_prioridadticket AS pt ON t.Prioridad = pt.IdPrioridad
        LEFT JOIN c_color AS col ON pt.IdColor = col.IdColor
        INNER JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
        INNER JOIN c_estado AS e2 ON $estado e2.IdEstado = t.AreaAtencion
        LEFT JOIN c_ubicacionticket AS u ON u.IdUbicacion = t.Ubicacion
        $estadoNota
        LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
        INNER JOIN k_ematicket AS ket ON ket.IdTicket = t.IdTicket ";
    
    $consulta .= " $Where $having ORDER BY IdTicket desc";
    if (!$tiene_filtro) {
        $consulta.=" LIMIT 0,500";
    }
}
//echo $consulta;
?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <!-- FontAwesome para iconos -->
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">
    </head>

    <body>

             <div class="principal"> 
                <div class="container-fluid"> 
                    <div class="form-row">

             <div class="form-group col-md-3">          
                 <label for="usuario_hw"  margin-right: 10px; <?php echo $style; ?>"><?php echo $nombre_puesto; ?>:</label> 
                  <select class="form-control" id="usuario_hw" name="usuario_hw"  <?php echo $style; ?>">                        
                   <?php
                $query = $catalogo->obtenerLista("SELECT IdUsuario, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS tfs FROM `c_usuario` WHERE IdPuesto = 18 AND Activo = 1 ORDER BY tfs;");
                echo "<option value='todos'>Ver todos</option>";
                while ($rs = mysql_fetch_array($query)) {
                    $s = "";
                    if ($rs['IdUsuario'] == $idHW) {
                        $s = "selected='selected'";
                    }
                    echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['tfs'] . "</option>";
                }
                ?>
                </select>
         </div> 

            <div class="form-group col-md-3">
                <label for="usuario_hw" style="<?php echo $style; ?>"><?php echo $nombre_objeto; ?>:</label>            
                <input class="form-control" type="text" id="idTicketHW" name="idTicketHW" value="<?php echo $idTicket; ?>"/>
            </div>

            <div class="form-group col-md-3">
                <label> No. Serie</label>
                <input class="form-control" id="num_serie" name="num_serie" value="<?php echo $NoSerie; ?>"/>
            </div>

            <div class="form-group col-md-3">
                <label>&Aacute;rea de atenci&oacute;n</label>
                <select class="form-control" id="area_ticket" name="area_ticket" >
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

           <div class="form-group col-md-3">
                <label>Fecha inicio</label>
                <input class="form-control" id="fecha_inicio" name="fecha_inicio" class="fecha" value="<?php echo $FechaInicio; ?>" />
           </div>

           <div class="form-group col-md-3">
                <label>Fecha final</label>
                <input class="form-control" id="fecha_fin" name="fecha_fin" class="fecha" value="<?php echo $FechaFin; ?>"  />
            </div>

            <div class="form-group col-md-3">
                <label>Tipo Reporte</label>
                <select class="form-control" id="reporte_ticket" name="reporte_ticket"  >
                            <?php
                            /* Inicializamos la clase */
                            $query = $catalogo->obtenerLista("SELECT e.IdEstado, e.Nombre FROM c_estado AS e
                            INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND kfe.IdFlujo = 1 ORDER BY Nombre;");
                            echo "<option value=''>Todos los tipos</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if (isset($_POST['tipoReporte']) && $_POST['tipoReporte'] == $rs['IdEstado']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                            }
                            ?> 
                </select>
             </div>      

            <div class="form-group col-md-3">
                <label>Estado</label>
                <select class="form-control" id="estadot" name="estadot"  >
                        <?php 
                            echo "<option value=''>Todos los estados</option>";
                            $resultET = $catalogo->getListaAlta("c_estadoticket", "Nombre");
                            while($rsET = mysql_fetch_array($resultET)){
                                $s = "";
                                if (isset($_POST['estadoT']) && $_POST['estadoT'] == $rsET['IdEstadoTicket']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rsET['IdEstadoTicket'] . "' $s>" . $rsET['Nombre'] . "</option>";
                            }
                        ?>
                        </select>
             </div> 
            <div class="form-group col-md-3">
                <label>SubEstado</label>
                        <select class="form-control" id="estado_ticket" name="estado_ticket" >
                            <?php
                            /* Inicializamos la clase */
                            $query = $catalogo->obtenerLista("SELECT e.IdEstado, e.Nombre FROM c_estado AS e
                            INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND kfe.IdFlujo = 6 ORDER BY Nombre;");
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
                <div class="form-group col-md-3">
                <label>Prioridad del ticket: </label>
                        <?php
                        $result2 = $catalogo->obtenerLista("SELECT pt.IdPrioridad, pt.Prioridad, tp.TipoPrioridad,  c.Hexadecimal
                                FROM `c_prioridadticket` AS pt
                                LEFT JOIN c_color AS c ON c.IdColor = pt.IdColor
                                LEFT JOIN c_tipoprioridad AS tp ON tp.IdTipoPrioridad = pt.IdTipoPrioridad WHERE pt.Activo = 1;");
                            echo "<select  class='form-control' id='prioridad' name='prioridad'>";
                            echo "<option value = 0 >Seleccione una prioridad</option>";
                            while($rs2 = mysql_fetch_array($result2)){
                                $s = "";
                                if(isset($_POST['Prioridad']) && $rs2['IdPrioridad'] == $_POST['Prioridad']){
                                    $s = "selected='selected'";
                                }
                                echo "<option value='".$rs2['IdPrioridad']."' style='background: #".$rs2['Hexadecimal'].";' $s>".$rs2['Prioridad']." (".$rs2['TipoPrioridad'].")</option>";
                            }
                        echo "</select>";
                        ?>
                
            </div> 

           <div class="form-group  col-md-3">
                <label>No. Guía</label>
                <input class="form-control" type="text" id="no_guia" name="no_guia" value="<?php echo $NoGuia; ?>" />
           </div>  
             
            <div class="form-group  col-md-3">
                <label>Búsqueda estado:</label><br>
                <input type="radio" id="ultimo_estado0" name="ultimo_estado" value="0" <?php echo $checked_ultimo; ?>/>
                <label>Último estado</label><br>
                <input type="radio" id="ultimo_estado1" name="ultimo_estado" value="1" <?php echo $checked_todo; ?>/>
                <label>Todos los estado del <?php echo $nombre_objeto; ?></label> 
             </div> 
             <div class="form-group col-md-3">
                <label>Cliente</label><br> 
                <select  multiple class="form-control" id="cliente_ticket" name="cliente_ticket[]">
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
           <div class="form-group col-md-3">
                <label>Filtrar:</label><br> 
                <input  type="checkbox" id="ticket_cerrado" name="ticket_cerrado" <?php echo $checked; ?> />
                <label>tickes cerrados</label> <br> 
                <input  type="checkbox" id="ticket_moroso" name="ticket_moroso" <?php echo $checkedMoroso; ?> />
                <label>Clientes morosos</label><br> 
                <input type="checkbox" id="ticket_cancelado" name="ticket_cancelado" <?php echo $checkedCancelado; ?> />
                <label>tickets cancelados</label>
            </div>    
      
            <input type="button" class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" id="boton_aceptar" onclick="recargarListaTicketUsuario('hardware/mis_tickets_hw.php', 'ticket_cerrado', 'cliente_ticket', 'ticket_color',
                        'estado_ticket', 'ticket_moroso', 'ticket_cancelado', true,'idTicketHW','fecha_inicio',
                                            'fecha_fin', 'area_ticket', 'reporte_ticket', 'no_guia','prioridad','estadot', 'usuario_hw');
                    return false;" value="Mostrar <?php echo $nombre_objeto; ?>s"/>
            <br/>
            <div id="error_ticket" style="float: right; display: none; color: red;"></div>
           
 </div>
            <table class="table-responsive" id="tAlmacen" style="width: 100%;">
                <thead>
                    <tr>
                        <?php
                        $cabeceras = array("$nombre_objeto", "Fecha", "NoSerie", "Cliente", "Falla", "Último estatus $nombre_objeto", "Última Nota", "Fecha nota","Días atraso" , "", "", "");
                        for ($i = 0; $i < (count($cabeceras)); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        ?>                        
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $query = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query)) 
                    {
                        if (isset($rs['IdEstatusAtencion']) && ($rs['IdEstatusAtencion'] == "16" || $rs['IdEstatusAtencion'] == "59")) {
                            continue;
                        }
                        if ($rs['Suspendido'] != "0") {
                            echo "<br/><h2>El $nombre_objeto " . $rs['IdTicket'] . " pertenece al cliente suspendido " . $rs['NombreCliente'] . "</h2><br/>";
                            break;
                        }
                        /*                         * *********************    Obtenemos el color de la fila   ******************************** */
                        $datos = explode("**__**", $rs['UltimoEstatus']);
                        $color = "#F7F7DE";
                        if (isset($datos[0]) && isset($datos[1])) {/* Si hay estado de la ultima nota */
                            if (strtoupper($datos[0]) != "CERRADO") {/* Si el ticket no esta cerrado */
                                if (strtoupper($rs['tipoCliente']) == "VIP") {/* Si el cliente es VIP */
                                    if (number_format($rs['diferencia']) >= 2) {/* Si ya van mas de 7 dias que se levanto el ticket */
                                        $color = "#DC381F";
                                    } else {
                                        $color = "#FFF380";
                                    }
                                } else {/* Si no es cliente VIP */
                                    if (number_format($rs['diferencia']) >= 7) {/* Si ya van mas de 7 dias que se levanto el ticket */
                                        $color = "#DC381F";
                                    }
                                }
                            }
                        } else {/* Si no hay notas, vemos el estado del ticket */
                            if (strtoupper($rs['estado']) != "CERRADO") {/* Si el ticket no esta cerrado */
                                if (strtoupper($rs['tipoCliente']) == "VIP") {/* Si el cliente es VIP */
                                    if (number_format($rs['diferencia']) >= 2) {/* Si ya van mas de 7 dias que se levanto el ticket */
                                        $color = "#DC381F";
                                    } else {
                                        $color = "#FFF380";
                                    }
                                } else {/* Si no es cliente VIP */
                                    if (number_format($rs['diferencia']) >= 7) {/* Si ya van mas de 7 dias que se levanto el ticket */
                                        $color = "#DC381F";
                                    }
                                }
                            }
                        }
                        if(isset($rs['Hexadecimal']) && $rs['Hexadecimal']){
                            $color = "#".$rs['Hexadecimal'];
                        }
                        echo "<tr style='background-color: $color; color:black;'>";
                        echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['FechaHora'] . "</td>";
                        if (isset($rs['id_bitacora'])) {
                            echo "<td align='center' scope='row'><a href='#' onclick='editarRegistro(\"almacen/alta_bitacora.php?consulta_tiquet=hardware/mis_tickets_hw.php\",\"" . $rs['id_bitacora'] . "\"); return false;'>" . $rs['NumSerie'] . "</a></td>";
                        } else {
                            $series = explode(",", $rs['NumSerie']);
                            $texto = "";
                            foreach ($series as $value) {
                                $texto.= "<a href='#' onclick='cambiarContenidos(\"almacen/alta_bitacora.php?consulta_tiquet=hardware/mis_tickets_hw.php&NoSerie=$value\"); return false;'>" . $value . "</a>,";
                            }
                            $texto = substr($texto, 0, strlen($texto) - 1);
                            echo "<td align='center' scope='row'>$texto</td>";
                        }
                        echo "<td align='center' scope='row'>" . $rs['NombreCliente'] . " - " . $rs['NombreCentroCosto'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['DescripcionReporte'] . "</td>";

                        if (isset($datos[0])) {
                            echo "<td align='center' scope='row'>" . $datos[0] . "</td>";
                        } else {
                            echo "<td align='center' scope='row'></td>";
                        }
                        if (isset($datos[1])) {
                            echo "<td align='center' scope='row'>" . $datos[1] . "</td>";
                        } else {
                            echo "<td align='center' scope='row'></td>";
                        }
                        if (isset($datos[2])) {
                            echo "<td align='center' scope='row'>" . $datos[2] . "</td>";
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
                                <a href='#' onclick='detalleTicket("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "1", "", "0");
                                                            return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                               <?php } else { ?>
                                <a href='#' onclick='detalleTicket("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "1", "", "1");
                                                            return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                                   <?php
                               }
                               ?>
                           <?php } ?>
                    </td>
                    <?php
                    if ($rs['estadoTicket'] != "2" && $rs['estadoTicket'] != "4" && $rs['IdEstatusAtencion'] != "16" && $rs['IdEstatusAtencion'] != "59" && $permisos_grid->getModificar()) {
                        ?>
                        <td align='center' scope='row'>
                            <?php
                            if ($nuevo) {
                                ?>
                                <a href='#' onclick='editarTicket("<?php echo $pantalla_edicion; ?>", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "0");
                                                    return false;' title='Modificar' ><img src="resources/images/Modify.png"/></a>
                               <?php } else { ?>
                                <a href='#' onclick='lanzarPopUp("Modificar", "<?php echo $src; ?>");
                                                    return false;' title='Modificar' ><img src="resources/images/Modify.png"/></a>
                                   <?php
                               }
                               ?> 
                        </td>
                        <?php
                    } else {
                        echo "<td align='center' scope='row'></td>";
                    }
                    ?>
                    <td align='center' scope='row'>
                        <?php
                        if ($nuevo) {
                            if ((int)$rs['Resurtido'] == 1) {  //104 en produccion, 201 pruebas
                            ?>
                            <td align='center' scope='row'>                             
                                <a href='#' onclick='detalleReporte("reportes/reporte_ticket_resurtido.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['idArea']; ?>", "0");
                                        return false;' title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>

                            </td>
                            <?php
                            }else{
                            ?>
                            <td align='center' scope='row'>                             
                                <a href='#' onclick='detalleReporte("reportes/reporte_ticket.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['idArea']; ?>", "0");
                                        return false;' title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>

                            </td>
                        <?php 
                            }
                        }else { ?>
                            <a href='<?php echo $_SESSION['liga']; ?>/operacion/MesaServicio/ReporteTicket.aspx?IdTicket=<?php echo $rs['IdTicket']; ?>&uguid=<?php echo $_SESSION['user']; ?>' target="_blank" title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>
                            <?php
                        }
                        ?>
                    </td>
                    <?php
                    echo "</tr>";
                }
                ?>                                
                </tbody>
            </table>
        </div>
        <input type="hidden" id="regresar" name="regresar" value="<?php echo $same_page; ?>"/>        
    </body>
</html>