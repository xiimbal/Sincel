<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "hardware/rutas.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$catalogo = new Catalogo();
$usuario = new Usuario();
$style = '';
$idHW = $_SESSION['idUsuario'];
$idTicket = "";
$tiene_filtro = false;
$join = "LEFT";
$FechaFin = "";
$FechaInicio = "";
$nota = "";

if (isset($_POST['idUsuario']) && $_POST['idUsuario'] != "todos") {
    $idHW = $_POST['idUsuario'];
    $tiene_filtro = true;
}

if (isset($_POST['idTicket']) && $_POST['idTicket'] != "") {
    $idTicket = $_POST['idTicket'];
    $tiene_filtro = true;
}

if (!empty($idTicket)) {//Si hay filtro de ticket, solo se toma en cuenta eso
    $where = " WHERE t.IdTicket = $idTicket";
} else {
    $where = " WHERE cl.Suspendido = 0 ";
    if ((isset($_POST['FechaInicio']) && $_POST['FechaInicio'] != "") || (isset($_POST['FechaFin']) && $_POST['FechaFin'] != "")) {
        $join = "INNER";
        $tiene_filtro = true;
        if (isset($_POST['FechaInicio']) && $_POST['FechaInicio'] != "" && isset($_POST['FechaFin']) && $_POST['FechaFin'] != "") {
            $FechaInicio = $_POST['FechaInicio'];
            $FechaFin = $_POST['FechaFin'];
            if ($where != "") {
                $where .= " AND nt.FechaHora BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
            } else {
                $where = "WHERE nt.FechaHora BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
            }
        } else if (isset($_POST['FechaInicio']) && $_POST['FechaInicio'] != "") {
            $FechaInicio = $_POST['FechaInicio'];
            if ($where != "") {
                $where .= " AND nt.FechaHora >= '$FechaInicio'";
            } else {
                $where = "WHERE nt.FechaHora >= '$FechaInicio'";
            }
        } else if (isset($_POST['FechaFin']) && $_POST['FechaFin'] != "") {
            $FechaFin = $_POST['FechaFin'];
            if ($where != "") {
                $where .= " AND nt.FechaHora <= '$FechaFin'";
            } else {
                $where = "WHERE nt.FechaHora <= '$FechaFin'";
            }
        }
    }

    if (isset($_POST['estado']) && $_POST['estado'] != "") {
        $join = "INNER";
        $nota = " AND nt2.IdEstatusAtencion = " . $_POST['estado'] . " ";
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
                    (SELECT CASE WHEN e2.Nombre = 'Suministro' THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
                    DATEDIFF(NOW(),t.FechaHora) AS diferencia,
                    t.NombreCliente,
                    cl.Suspendido,
                    e.Nombre AS estado,
                    e1.Nombre AS tipo,
                    t.ClaveCentroCosto,
                    cl.ClaveCliente,	
                    tc.Nombre AS tipoCliente,
                    e2.Nombre AS area,
                    t.TipoReporte,
                    e2.IdEstado AS idArea,
                    u.Nombre AS ubicacion,	
                    (SELECT CONCAT(ce.Nombre,'**__**',nt2.DiagnosticoSol,'**__**',nt2.FechaHora) FROM c_estado AS ce INNER JOIN c_notaticket AS nt2 ON nt2.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket) AND ce.IdEstado = nt2.IdEstatusAtencion AND nt2.IdEstatusAtencion <> 16) AS UltimoEstatus,
                    e.IdEstadoTicket AS estadoTicket,
                    nt.IdEstatusAtencion
                    FROM k_tecnicoticket AS ktt
                    INNER JOIN c_ticket AS t ON ktt.IdUsuario = " . $idHW . " AND ktt.tipo = 1 AND ktt.IdTicket = t.IdTicket                    
                    INNER JOIN c_estadoticket AS e ON e.IdEstadoTicket = t.EstadoDeTicket AND t.EstadoDeTicket <> 2
                    INNER JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte
                    INNER JOIN c_cliente AS cl ON cl.ClaveCliente = t.ClaveCliente
                    INNER JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
                    INNER JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion
                    INNER JOIN c_ubicacionticket AS u ON u.IdUbicacion = t.Ubicacion
                    $join JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket $nota)
                    LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo";
    $consulta .= " $where";
    $consulta .= " ORDER BY IdTicket desc";
    /* if(!$tiene_filtro){
      $consulta.=" LIMIT 0,500;";
      } */
} else {
    $consulta = "SELECT 
        b.id_bitacora,
        t.IdTicket,
        t.FechaHora,
        t.DescripcionReporte,
        t.NombreCentroCosto,                    
        (SELECT CASE WHEN e2.Nombre = 'Suministro' THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
        DATEDIFF(NOW(),t.FechaHora) AS diferencia,
        t.NombreCliente,
        e.Nombre AS estado,       
        e1.Nombre AS tipo,
        t.TipoReporte,
        cl.Suspendido,
        t.ClaveCentroCosto,
        cl.ClaveCliente,	
        tc.Nombre AS tipoCliente,
        e2.Nombre AS area,
        e2.IdEstado AS idArea,
        u.Nombre AS ubicacion,	
        (SELECT CONCAT(ce.Nombre,'**__**',nt2.DiagnosticoSol,'**__**',nt2.FechaHora) FROM c_estado AS ce INNER JOIN c_notaticket AS nt2 ON nt2.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket) AND ce.IdEstado = nt2.IdEstatusAtencion AND nt2.IdEstatusAtencion <> 16) AS UltimoEstatus, 
        e.IdEstadoTicket AS estadoTicket,
        nt.IdEstatusAtencion
        FROM c_ticket AS t                
        INNER JOIN c_estadoticket AS e ON e.IdEstadoTicket = t.EstadoDeTicket AND t.EstadoDeTicket <> 2
        INNER JOIN c_estado AS e1 ON e1.IdEstado = 1 AND e1.IdEstado = t.TipoReporte
        INNER JOIN c_cliente AS cl ON cl.ClaveCliente = t.ClaveCliente
        INNER JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
        INNER JOIN c_estado AS e2 ON e2.IdEstado = 5 AND e2.IdEstado = t.AreaAtencion
        INNER JOIN c_ubicacionticket AS u ON u.IdUbicacion = t.Ubicacion
        $join JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket $nota)
        LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo";
    $consulta .= " $where";
    $consulta .= " ORDER BY IdTicket desc";
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
                    <div class="form-group col-md-4">          
                       <label  for="busqueda_ticket" style="<?php echo $style; ?>">Ticket:</label>
                       <input class="form-control" type="text" id="busqueda_ticket" name="busqueda_ticket" value="<?php echo $idTicket; ?>"/>
                    </div>

                    <div class="form-group col-md-4">          
                       <label   for="usuario_hw" style="<?php echo $style; ?>">T&eacute;cnico HW:</label>
                        <select class="form-control" id="usuario_hw" name="usuario_hw" style="<?php echo $style; ?>">                        
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
                
                   <div class="form-group col-md-4">          
                       <label for="fecha_inicio">Fecha inicial</label>
                       <input class="form-control" type="text" class="fecha" id="FechaInicio" name="FechaInicio" value="<?php echo $FechaInicio; ?>"/>
                   </div>

                    <div class="form-group col-md-4">
                        <label  for="fecha_fin">Fecha final</label>
                        <input class="form-control" type="text" class="fecha" id="FechaFin" name="FechaFin" value="<?php echo $FechaFin; ?>"/>
                    </div>

                    <div class="form-group col-md-4">    
                        <label>Estado</label>
                        <select class="form-control" id="estado_ticket" name="estado_ticket"  >
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
                         <input  type="button" class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3"  id="boton_aceptar" 
                             onclick="recargarListaTicket('hardware/rutas.php', null, null, null, 'estado_ticket', null, null, true,
                                    null, 'FechaInicio', 'FechaFin', null, null, 'usuario_hw');
                           return false;" value="Mostrar tickets"/>
                         <div id="error_ticket" style="float: right; display: none; color: red;"></div>
                   </div> 
            <table id="tAlmacen">
                <thead>
                    <tr>
                        <?php
                        $cabeceras = array("Ticket", "Fecha", "NoSerie", "Cliente", "Falla", "Último estatus ticket", "Última Nota", "Fecha nota", "", "");
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
                    if ($query == "Unknown column 't.IdTicket' in 'where clause'") {
                        //Vacia
                        echo "No tiene los datos para esta tabla";
                    }else{
                        //Llena
                        while ($rs = mysql_fetch_array($query)) {
                        $booleanFecha = FALSE;
                        $fecha_limite = strtotime("2014-03-31");
                        $fecha_ticket = strtotime($rs['FechaHora']);
                        if ($fecha_ticket >= $fecha_limite) {
                            $booleanFecha = TRUE;
                        } else {
                            $booleanFecha = FALSE;                                
                        }
                        
                        
                        if ($rs['Suspendido'] != "0") {
                            echo "<br/><h2>El ticket " . $rs['IdTicket'] . " pertenece al cliente suspendido " . $rs['NombreCliente'] . "</h2><br/>";
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
                        ?>
                        <td align='center' scope='row'> 
                            <?php if ($permisos_grid->getConsulta()) { ?>
                                <?php
                                if ($booleanFecha) {
                                    ?>
                                    <a href='#' onclick='detalleTicket("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "1","", "0");
                                            return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                                   <?php } else { ?>
                                    <a href='#' onclick='detalleTicket("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "1","","1");
                                            return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                                       <?php
                                   }
                                   ?>
                               <?php } ?>
                        </td>
                        
                        <td align='center' scope='row'> 
                            <a href='#' onclick='detalleReporte("reportes/reporte_ticket.php", "<?php echo $rs['IdTicket']; ?>", null, null);
                                            return false;' title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>
                        </td>
                        <?php
                        echo "</tr>";
                    }
                    }
                    
                ?>                                
                </tbody>
            </table>
        </div>
    </body>
</html>