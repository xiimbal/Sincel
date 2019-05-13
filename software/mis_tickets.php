<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Puesto.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "software/mis_tickets.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$tiene_filtro = false;

$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();

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
$query = $catalogo->obtenerLista("SELECT IdUsuario, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS tecnico "
        . "FROM `c_usuario` "
        . "WHERE Activo = 1 AND (IdPuesto = 19 OR IdPuesto = 20) ORDER BY tecnico;");

$tecnicos["0"] = "Selecciona al $nombre_puesto";
while ($rs = mysql_fetch_array($query)) {
    $tecnicos[$rs['IdUsuario']] = $rs['tecnico'];
}

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

if (isset($_POST['idTicket']) && $_POST['idTicket'] != "") {
    $tiene_filtro = true;
    $idTicket = $_POST['idTicket'];
    /* Si se busco un ticket en particular, habilitamos cerrados, morosos y cancelados */
    $checked = "checked='checked'";
    $checkedMoroso = "checked='checked'";
    $checkedCancelado = "checked='checked'";
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
    $checkedMoroso = "checked='checked'";
}

if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
    $canceladoTicket = "";
    $checkedCancelado = "checked='checked'";
}

if (isset($_POST['cliente']) && $_POST['cliente'] != "") {
    $tiene_filtro = true;
    $cliente = " AND t.NombreCliente IN (" . $_POST['cliente'] . ")";
    $cliente_array = explode("','", $_POST['cliente']);
    $cliente_array[0] = substr($cliente_array[0], 1, strlen($cliente_array[0]));
    $cliente_array[count($cliente_array) - 1] = substr($cliente_array[count($cliente_array) - 1], 0, strlen($cliente_array[count($cliente_array) - 1]) - 1);
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
?>
<!DOCTYPE html>
<html lang="es">
    <head>              
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_ticket.js"></script>       
    </head>
    <body>
        <div class="principal">            
            <br/><br/>
            <table style="width: 100%;">
                <tr>
                    <td><?php echo $nombre_objeto; ?></td>
                    <td><input type="text" id="busqueda_ticket" name="busqueda_ticket" value="<?php echo $idTicket; ?>"/></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>                    
                    <td><input type="checkbox" id="ticket_cerrado" name="ticket_cerrado" <?php echo $checked; ?>/>Mostrar cerrados</td>
                </tr>
                <tr>                    
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
                    <td>Color</td>
                    <td>
                        <select id="ticket_color" name="ticket_color" style="width: 200px;" >
                            <option value="">Todos</option>
                            <option value="rojo" style="background: #DC381F;">Urgente</option>
                            <option value="amarillo" style="background: #FFF380;">Importante</option>
                            <option value="verde" style="background: #F7F7DE;">Normal</option>
                        </select>                        
                    </td>
                    <td><input type="checkbox" id="ticket_moroso" name="ticket_moroso" <?php echo $checkedMoroso; ?> />Mostrar morosos</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><input type="checkbox" id="ticket_cancelado" name="ticket_cancelado" <?php echo $checkedCancelado; ?> />Mostrar cancelados</td>
                </tr>
            </table>   
            <input type="button" class="button" onclick="recargarListaTicket('software/mis_tickets.php', 'ticket_cerrado', 'cliente_ticket', 'ticket_color', 'estado_ticket', 'ticket_moroso', 'ticket_cancelado');
                    return false;" id="boton_aceptar" name="boton_aceptar" value="Mostrar <?php echo $nombre_objeto; ?>s"/>
            <br/><br/>
            <table id="tAlmacen">
                <thead>
                    <tr>
                        <?php
                        $cabeceras = array("$nombre_objeto", "Fecha", "Cliente", "Falla", "Último estatus $nombre_objeto", "Última Nota", "Fecha nota", "", "$nombre_puesto", "");
                        for ($i = 0; $i < (count($cabeceras)); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        ?>                                                                      
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $union = "SELECT t.IdTicket, t.FechaHora, t.DescripcionReporte, t.NombreCentroCosto,
                    t.TipoReporte, 
                    (SELECT CASE
                    WHEN !ISNULL(p.IdPedido) THEN (SELECT group_concat(ClaveEspEquipo SEPARATOR ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket)
                    ELSE t.NoSerieEquipo
                    END ) AS NumSerie,
                    DATEDIFF(NOW(), t.FechaHora) AS diferencia,
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
                    nt.FechaHora AS FechaNota
                    FROM c_ticket AS t
                    INNER JOIN c_notaticket AS nt ON $canceladoTicket $cerradoTicket nt.IdEstatusAtencion = 48 AND nt.IdEstadoNota = 1 AND nt.IdTicket = t.IdTicket
                    LEFT JOIN c_pedido AS p ON p.IdTicket = t.IdTicket
                    INNER JOIN c_cliente AS cl ON cl.ClaveCliente = t.ClaveCliente $cliente
                    LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente AND cl.Suspendido = 0
                    INNER JOIN c_estadoticket AS e ON e.IdEstadoTicket = t.EstadoDeTicket
                    INNER JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte
                    INNER JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion
                    LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
                    LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket
                    LEFT JOIN c_gzona AS cgz ON cgz.id_gzona = dt.Id_gzona
                    LEFT JOIN c_ubicacionticket AS u ON u.IdUbicacion = t.Ubicacion";
                    if ($idTicket != "") {
                        $union .= " WHERE t.IdTicket = $idTicket ";
                    }
                    $union .= " $having UNION ";

                    $consulta = "$union SELECT
                    t.IdTicket,
                    t.FechaHora,
                    t.DescripcionReporte,
                    t.NombreCentroCosto,
                    t.TipoReporte,
                    (SELECT CASE WHEN e2.IdEstado = 2 THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
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
                    nt.FechaHora AS FechaNota
                    FROM c_ticket AS t
                    LEFT JOIN k_tecnicoticket ON t.IdTicket = k_tecnicoticket.IdTicket
                    INNER JOIN c_estadoticket AS e ON $canceladoTicket $cerradoTicket e.IdEstadoTicket = t.EstadoDeTicket
                    LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket
                    LEFT JOIN c_gzona AS cgz ON cgz.id_gzona = dt.Id_gzona
                    INNER JOIN c_estado AS e1 ON e1.IdEstado = 1 AND e1.IdEstado = t.TipoReporte
                    INNER JOIN c_cliente AS cl ON cl.ClaveCliente = t.ClaveCliente
                    LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
                    INNER JOIN c_estado AS e2 ON e2.IdEstado = 6 AND e2.IdEstado = t.AreaAtencion
                    LEFT JOIN c_ubicacionticket AS u ON u.IdUbicacion = t.Ubicacion
                    $estadoNota
                    LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado                    
                    WHERE ";

                    if ($idTicket != "") {
                        $consulta.=" t.IdTicket = $idTicket $cliente";
                    } else {
                        $consulta .= " cl.Suspendido = 0 AND k_tecnicoticket.IdTicket IS NULL $cliente ";
                    }

                    $consulta.= " $having ORDER BY IdTicket";
                    if (!$tiene_filtro) {
                        $consulta.=" DESC LIMIT 0,500";
                    }
                    $consulta.=";";

                    $array_tickets = array();
                    $query2 = $catalogo->obtenerLista($consulta);
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

                        /*                         * *********************    Obtenemos el color de la fila   ******************************** */
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

                        echo "<tr style='background-color: $color; color:black;'>";
                        echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['FechaHora'] . "</td>";
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
                            echo "<td align='center' scope='row'>" . $rs['FechaNota'] . "</td>";
                        } else {
                            echo "<td align='center' scope='row'></td>";
                        }
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
                        echo "<button class='boton' id='boton_" . $rs['IdTicket'] . "' name='boton_" . $rs['IdTicket'] . "' onclick='relacionarTecnico(\"" . $rs['IdTicket'] . "\",\"tecnico_" . $rs['IdTicket'] . "\",\"2\"  ); return false;'>Guardar</button>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
                </tbody>
                <input type="hidden" id="color_hidden" name="color_hidden" value="<?php echo $colorPOST; ?>"/>
                <input type="hidden" id="page" name="page" value="<?php echo $page; ?>"/>
                <input type="hidden" id="filter" name="filter" value="<?php echo $filter; ?>"/>
                <input type="hidden" id="regresar" name="regresar" value="<?php echo $same_page; ?>"/>
            </table>
        </div>
    </body>
</html>