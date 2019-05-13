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
include_once("../WEB-INF/Classes/Menu.class.php");
include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");

$menu = new Menu();
$factura = $menu->tieneSubmenu($_SESSION['idUsuario'], 28); /* Preguntamos si el usuario tiene permiso de facturar */

$permisos_grid = new PermisosSubMenu();
$same_page = "facturacion/pendientes.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$nombre_objeto = $permisos_grid->getNombreTicketSistema();

$parametros = new Parametros();
$parametros->getRegistroById("7");
$liga = $parametros->getDescripcion();
$parametros->getRegistroById("8");
$liga_propia = $parametros->getDescripcion();

$FechaInicio = "";
$FechaFin = "";

$cerradoTicket = "t.EstadoDeTicket <> 2 AND ";
$checked = "";
$morososTicket = "cl.IdEstatusCobranza <> 2 AND ";
$checkedMoroso = "";
$canceladoTicket = "t.EstadoDeTicket <> 4 AND ";
$checkedCancelado = "";
$cliente = "";
$colorPOST = "";
$estadoNota = "LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";

if (isset($_GET['cerrado']) && $_GET['cerrado'] != "false") {
    $cerradoTicket = "";
    $checked = "checked='checked'";
}

//if(isset($_POST['moroso']) && $_POST['moroso']!="false"){
if (true) {//Siempre mostrar los morosos
    $morososTicket = "";
    $checkedMoroso = "checked='checked'";
}

if (isset($_GET['cancelado']) && $_GET['cancelado'] != "false") {
    $canceladoTicket = "";
    $checkedCancelado = "checked='checked'";
}

if (isset($_GET['cliente']) && $_GET['cliente'] != "") {
    $cliente = "t.ClaveCliente = '" . $_GET['cliente'] . "' AND ";
}

if (isset($_GET['color']) && $_GET['color'] != "") {
    $colorPOST = $_GET['color'];
}

if (isset($_GET['estado']) && $_GET['estado'] != "") {
    $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdEstatusAtencion = " . $_GET['estado'] . " AND nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
}

$where = "";

if (isset($_POST['FechaInicio']) && $_POST['FechaInicio'] != "" && isset($_POST['FechaFin']) && $_POST['FechaFin'] != "") {
    $FechaInicio = $_POST['FechaInicio'];
    $FechaFin = $_POST['FechaFin'];
    
    $where = "WHERE t.FechaHora >= '$FechaInicio' AND t.FechaHora <= '$FechaFin' ";
} else if (isset($_POST['FechaInicio']) && $_POST['FechaInicio'] != "") {
    $FechaInicio = $_POST['FechaInicio'];
    $where = "WHERE t.FechaHora >= '$FechaInicio' ";
} else if (isset($_POST['FechaFin']) && $_POST['FechaFin'] != "") {
    $FechaFin = $_POST['FechaFin'];
    $where = "WHERE t.FechaHora <= '$FechaFin' ";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>             
    </head>
    <body>
        <div class="principal">
            <input type="hidden" id="nombreTicket" value="<?php echo $nombre_objeto?>">
            <br/><br/>
            <table style="width: 100%;" id="tabla1">
                <tr>
                    <td>Cliente</td>
                    <td>
                        <select id="cliente_ticket" name="cliente_ticket" style="width: 200px;">
                            <?php
                            /* Inicializamos la clase */
                            $catalogo = new Catalogo();
                            $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                            echo "<option value=''>Todos los clientes</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if (isset($_GET['cliente']) && $_GET['cliente'] == $rs['ClaveCliente']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . "</option>";
                            }
                            ?> 
                        </select>
                    </td>
                    <td>Estado</td>
                    <td>
                        <select id="estado_ticket" name="estado_ticket" style="width: 200px;" disabled="disbaled">
                            <?php
                            /* Inicializamos la clase */
                            $query = $catalogo->getListaAlta("c_estado", "Nombre");
                            while ($rs = mysql_fetch_array($query)) {
                                if ($rs['IdEstado'] == "55") {
                                    echo "<option value='" . $rs['IdEstado'] . "'>" . $rs['Nombre'] . "</option>";
                                }
                            }
                            ?> 
                        </select>
                    </td>
                    <td>Color</td>
                    <td>
                        <select id="ticket_color" name="ticket_color" style="width: 200px;">
                            <option value="">Todos</option>
                            <option value="rojo" style="background: #DC381F;">Urgente</option>
                            <option value="amarillo" style="background: #FFF380;">Importante</option>
                            <option value="verde" style="background: #F7F7DE;">Normal</option>
                        </select>
                    </td>
                    <td>Lecturas de corte</td>
                    <td>
                        <input type="checkbox" value="1" id="lecorte" name="lecorte" <?php
                        if (isset($_GET['lec']) && $_GET['lec'] == "1") {
                            echo "checked";
                        }
                        ?> />
                    </td>
                </tr>
                <tr>
                    <td>Fecha inicio</td>
                    <td><input id="fecha_inicio" name="fecha_inicio" class="fecha" value="<?php echo $FechaInicio; ?>" style="width:196px" /></td>
                    <td>Fecha final</td>
                    <td><input id="fecha_fin" name="fecha_fin" class="fecha" value="<?php echo $FechaFin; ?>" style="width:196px" /></td>
                    <td></td>
                    <td><input type='button' class="button" value='Buscar' onclick="recargarListaPendientes('facturacion/pendientes.php','cliente_ticket','ticket_color',
                        'estado_ticket','lecorte', 'fecha_inicio','fecha_fin')"></td>
                </tr>
            </table>  
            <br/><br/>
            <input id="boton_atender" name="boton_atender" type="button" style=" margin-left: 90%" class="boton2" value="Generar Prefactura"  onclick="generarPrefactura();" />
            <div style="display: inline; float: right;"><input type="checkbox" id="slc_todo_solicitado" onclick="seleccionarTodosSolicitados();"/>
                <div id="mensaje_sel" style="display: inline; ">Seleccionar todo</div></div>
            <br/><a href="#" id="liga_ticket" onclick="mostrarTablaTicket();
                    return false;">Mostrar tickets</a><br/>
            <table class='dataTable' id="tabla2" style="width: 100%; display: none;">
                <thead>
                    <tr>
                        <?php
                        $cabeceras = array($nombre_objeto, "Fecha", "No Serie", "Cliente", "Área de atención", "Falla", "Último estatus ticket", "Última Nota", "Fecha nota","Nota de Remisión" ,"Orden de Compra","Atendió","Facturar", "", "", "","", "Comisión", "% Descuento");
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
                    (SELECT CASE WHEN e2.IdEstado = 2 THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
                    DATEDIFF(NOW(),t.FechaHora) AS diferencia,
                    t.NombreCliente,
                    cl.IdEstatusCobranza,
                    cl.RFC,
                    e.IdEstadoTicket AS estadoTicket,                    
                    e1.Nombre AS tipo,           
                    tc.IdTipoCliente AS tipoCliente,
                    (CASE WHEN !ISNULL(ar.IdArea) THEN ar.Descripcion ELSE ar2.Descripcion END) AS area, 
                    (CASE WHEN !ISNULL(ar.IdArea) THEN ar.IdArea ELSE ar2.IdArea END) AS idArea, 
                    u.Nombre AS ubicacion,
                    cgz.nombre AS ubicacionTicket,
                    e3.Nombre AS estadoNota,
                    nt.IdEstatusAtencion,
                    nt.DiagnosticoSol,
                    cl.ClaveCliente,
                    nt.FechaHora AS FechaNota, (CASE WHEN !ISNULL(usu.PorcentajeDesc) THEN usu.PorcentajeDesc ELSE 50 END) AS PorcentajeDesc,
                    CONCAT(usu.Nombre,' ',usu.ApellidoPaterno,' ',usu.ApellidoMaterno) AS Atendio,ksve.Validado,MAX(tnr.IdNotaRemision) AS NTR, MAX(toc.IdOrdenCompra) AS OC
                    FROM c_ticket AS t
                    INNER JOIN c_estadoticket AS e ON $canceladoTicket $cerradoTicket e.IdEstadoTicket = t.EstadoDeTicket
                    LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket
                    LEFT JOIN c_gzona AS cgz ON cgz.id_gzona = dt.Id_gzona
                    LEFT JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte
                    INNER JOIN c_cliente AS cl ON $morososTicket $cliente cl.ClaveCliente = t.ClaveCliente
                    LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
                    LEFT JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion
                    LEFT JOIN c_area AS ar2 ON ar2.IdArea = e2.IdArea
                    LEFT JOIN c_ubicacionticket AS u ON u.IdUbicacion = t.Ubicacion
                    INNER JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket) AND nt.IdEstatusAtencion = 55
                    LEFT JOIN k_serviciove as ksve ON ksve.IdNotaTicketFacturar = nt.IdNotaTicket
                    LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado 
                    LEFT JOIN c_area AS ar ON ar.IdArea = e3.IdArea 
                    LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdTicket = t.IdTicket
                    LEFT JOIN c_usuario AS usu ON usu.IdUsuario = ktt.IdUsuario
                    LEFT JOIN k_ticketnr AS tnr ON tnr.IdTicket = t.IdTicket
                    LEFT JOIN k_tickets_oc toc ON toc.IdTicket = t.IdTicket
                    $where 
                    GROUP BY IdTicket
                    HAVING (IdEstatusAtencion <> 16 AND IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)
                    ORDER BY IdTicket;";
                    //echo $consulta;
                    $contadorFila = 1;
                    $query = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query)) {/* Recorremos todos los tickets resultantes del query */
                        if ($checked == "" && $rs['IdEstatusAtencion'] == "16") {/* Si ya esta cerrado por nota, saltamos */
                            continue;
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
                        echo "<td align='center' scope='row'>" . $rs['NumSerie'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['NombreCliente'] . " - " . $rs['NombreCentroCosto'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['area'] . "</td>";
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
                        //Lo nuevo
                        echo "<td align='center' scope='row'>";
                        if(isset($rs['NTR']) && !empty($rs['NTR'])){
                            echo $rs['NTR'];
                        }
                        echo "</td>";
                        echo "<td align='center' scope='row'>";
                        if(isset($rs['OC']) && !empty($rs['OC'])){
                            echo $rs['OC'];
                        }
                        echo "</td>";
                        
                        echo "<td align='center' scope='row'>" . $rs['Atendio'] . "</td>";                        

                        //$areaCodificada = str_replace(" ", "&&__&&", $rs['area']);
                        $src = $liga . "/cfdi/datosfactura.aspx?rfc=" . $rs['RFC'] . "&uguid=" . $_SESSION['user'];
                        $fecha_limite = strtotime("2014-03-31");
                        $fecha_ticket = strtotime($rs['FechaHora']);
                        if ($fecha_ticket >= $fecha_limite) {
                            $nuevo = true;
                        } else {
                            $nuevo = false;
                        }
                        if ($rs['idArea'] == "2") {
                            $src2 = $liga . "/Operacion/MesaServicio/ConsultaDetalleTicketToner.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Detalle&uguid=" . $_SESSION['user'];
                        } else {
                            $src2 = $liga . "/Operacion/MesaServicio/ConsultaDetalleTicketFalla.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Detalle&uguid=" . $_SESSION['user'];
                        }
                        echo "<td align='center' scope='row'>"
                        . "<a href='" . $liga_propia . "principal.php?mnu=facturacion&action=alta_factura&param1=" . $rs['ClaveCliente'] . "' target='_blank' title='Facturar'>"
                        . "<img src='resources/images/facturar.png' width='35' height='35'/></a></td>";
                        if ($permisos_grid->getConsulta()) {/* Si tiene permiso para consultar */
                            ?>
                        <td align='center' scope='row'>
                            <?php
                            if ($nuevo) {
                                ?>
                                <a href='#' onclick='detalleTicket("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "1");
                                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                               <?php } else { ?>
                                <a href='#' onclick='lanzarPopUp("Detalle", "<?php echo $src2; ?>");
                                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                                   <?php
                               }
                               ?>
                        </td>                            
                        <?php
                    } else {
                        echo "<td align='center' scope='row'></td>";
                    }
                    if ($rs['estadoTicket'] != "2" && $rs['estadoTicket'] != "4" && $rs['IdEstatusAtencion'] != "16" && $permisos_grid->getModificar()) {/* Si puede modificar */
                        ?>
                        <td align='center' scope='row'>
                            <?php
                            if ($nuevo) {
                                ?>
                                <a href='#' onclick='editarTicket("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "0");
                                                    return false;' title='Modificar' ><img src="resources/images/Modify.png"/></a>
                                   <?php
                               } else {
                                   if ($rs['idArea'] == "2") {
                                       $src2 = $liga . "/Operacion/MesaServicio/ConsultaDetalleTicketToner.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Modificar&uguid=" . $_SESSION['user'];
                                   } else {
                                       $src2 = $liga . "/Operacion/MesaServicio/ConsultaDetalleTicketFalla.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Modificar&uguid=" . $_SESSION['user'];
                                   }
                                   if ($permisos_grid->getModificar()) {
                                       ?>
                                    <a href='#' onclick='lanzarPopUp("Modificar", "<?php echo $src2; ?>");
                                                        return false;' title='Modificar' ><img src="resources/images/Modify.png"/></a>
                                    <?php
                                }
                            }
                            ?> 
                        </td>
                        <?php
                    } else {
                        echo "<td align='center' scope='row'></td>";
                    }
                    ?>
                    <td align='center' scope='row'> 
                        <a href='#' onclick='detalleReporte("reportes/reporte_ticket.php", "<?php echo $rs['IdTicket']; ?>", null, null);
                                    return false;' title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>
                    </td>
                    <td align='center' scope='row'>
                        <input type="checkbox" name="ckTicketSeleccionado<?php echo $contadorFila; ?>" id="ckTicketSeleccionado<?php echo $contadorFila; ?>" value="<?php echo $rs['IdTicket'] ?>" />
                    </td>
                    <td align='center' scope='row'><input type="text" name="pagoTicket<?php echo $contadorFila; ?>" id="pagoTicket<?php echo $contadorFila; ?>" value="" style="width: 60px;"/></td>
                    <td align='center' scope='row'><input type="number" name="PorcentajeDesc<?php echo $contadorFila; ?>" id="PorcentajeDesc<?php echo $contadorFila; ?>" value="<?php echo $rs['PorcentajeDesc']; ?>" maxlength="3" min="0" max="100" style="width: 40px;"/></td>
                    <?php
                    echo "</tr>";
                    $contadorFila++;
                }
                ?>
                </tbody>
                <input type="hidden" id="contador_tickets" name="contador_tickets" value="<?php echo $contadorFila; ?>"/>
                <input type="hidden" id="color_hidden" name="color_hidden" value="<?php echo $colorPOST; ?>"/>           
                <input type="hidden" id="page" name="page" value="<?php echo $page; ?>"/>
                <input type="hidden" id="filter" name="filter" value="<?php echo $filter; ?>"/>
            </table>
                <input id="boton_atender" name="boton_atender" type="button" style=" margin-left: 90%" class="boton2" value="Generar Prefactura"  onclick="generarPrefactura();" />
            <br/>
            <!--?php echo $consulta?-->
            <br/>
            <h2>Ventas directas por facturar</h2><br/>
            <?php
            $consulta = "SET group_concat_max_len = 512;";
            $catalogo->obtenerLista($consulta);
            $consulta = "
                SELECT vd.IdVentaDirecta, c.NombreRazonSocial , c.RFC, c.ClaveCliente,
                (CASE 
                WHEN TipoProducto = 0 THEN (GROUP_CONCAT(CONCAT('(',kvd.Cantidad,') ', (SELECT Modelo FROM c_equipo WHERE NoParte = kvd.IdProduto), ': $', kvd.Costo  ) SEPARATOR ', ')) 
                ELSE (GROUP_CONCAT(CONCAT('(',kvd.Cantidad,') ', (SELECT Modelo FROM c_componente WHERE NoParte = kvd.IdProduto), ': $', kvd.Costo  ) SEPARATOR ', ')) END) AS CantidadProducto,
                SUM(kvd.Cantidad) AS Cantidad, SUM(kvd.Costo*kvd.Cantidad) AS Costo
                FROM c_ventadirecta AS vd
                INNER JOIN k_ventadirectadet AS kvd ON vd.IdVentaDirecta = kvd.IdVentaDirecta
                LEFT JOIN c_cliente AS c ON c.ClaveCliente = vd.ClaveCliente
                WHERE vd.facturada = 0 AND vd.autorizada_alm=1 AND vd.autorizada_vd=1
                GROUP BY vd.IdVentaDirecta;";
            ?>
            <a href="#" id="liga_ventas" onclick="mostrarTablaVentas();
                    return false;">Mostrar ventas</a><br/>
            <table class='dataTable' id="tabla3" style="display: none;">
                <thead>
                    <tr>                        
                        <th width='2%' align='center' scope='col'>No. Venta</th>
                        <th width='2%' align='center' scope='col'>Cliente</th>
                        <th width='2%' align='center' scope='col'>Cantidad</th>
                        <th width='2%' align='center' scope='col'>Monto</th>
                        <th width='2%' align='center' scope='col'>Imprimir</th>
                        <?php
                        if ($factura) {
                            echo "<th width='2%' align='center' scope='col'>Factura</th>";
                        }
                        ?>
                        <th width='2%' align='center' scope='col'>Facturada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $catalogo->obtenerLista($consulta);
                    $result = null;
                    while ($rs = mysql_fetch_array($result)) {
                        echo "<tr>";
                        echo "<td>" . $rs['IdVentaDirecta'] . "</td>";
                        echo "<td>" . $rs['NombreRazonSocial'] . "</td>";
                        echo "<td>" . $rs['CantidadProducto'] . "</td>";
                        echo "<td>$" . number_format($rs['Costo'], 2) . "</td>";
                        echo "<td width=\"2%\" align=\"center\" scope=\"col\"> <a href='ventas/imprimir_ventad.php?id=" . $rs['IdVentaDirecta'] . "'  title='Imprimir' target='_blank'><img src=\"resources/images/icono_impresora.jpg\" width=\"24\" height=\"24\"/></a></td>";
                        if ($factura) {
                            $src = $liga . "/cfdi/datosfactura.aspx?rfc=" . $rs['RFC'] . "&uguid=" . $_SESSION['user'];
                            echo "<td align='center' scope='row'>";
                            if ($permisos_grid->getModificar()) {
                                //echo "<a href='$src' target='_blank' title='Facturar'><img src='resources/images/facturar.png' width='35' height='35'/></a>"; 
                                echo "<a href='principal.php?mnu=facturacion&action=alta_factura&param1=" . $rs['ClaveCliente'] . "' target='_blank' title='Facturar'>
                                    <img src='resources/images/facturar.png' width='35' height='35'/>
                                </a>";
                            }
                            echo "</td>";
                        }
                        echo "<td>";
                        if ($permisos_grid->getModificar()) {
                            echo "<a href='#' onclick='marcarVentaFacturada(\"" . $rs['IdVentaDirecta'] . "\"); return false;'><img src='resources/images/Apply.png' alt='Marcar como facturada'/></a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>

            <h2>Prefacturas</h2><br/>
            <?php
            //$consulta = "SET group_concat_max_len = 512;";
            //$catalogo->obtenerLista($consulta);
            $where = "";
            echo $_GET['cliente'];
            if (isset($_GET['cliente']) && $_GET['cliente'] != "") {
                $where = "AND c.ClaveCliente='" . $_GET['cliente'] . "'";
            }
            if (isset($_GET['lec']) && $_GET['lec'] == "1") {
                $where = "AND f.TipoFactura='1'";
            }
            $consulta = "SELECT
            DATE(f.FechaFacturacion) AS FechaFacturacion,
            f.NombreReceptor,
            f.NombreEmisor,
            f.Serie,
            c.ClaveCliente,
            SUBSTRING(f.RFCEmisor,1,3) AS RFCEmisor,
            FORMAT((f.Total/1.16),2) AS subtotal,
            FORMAT(((f.Total/1.16)*.16),2) AS importe,
            FORMAT(f.Total,2) as Total,            
            f.RFCReceptor,
            f.IdFactura AS IdFactura,
            f.PathPDF AS PDF,
            (SELECT CASE WHEN f.TipoComprobante = 'ingreso' THEN 'F' ELSE 'NDC' END) AS TipoComprobante,
            (SELECT CASE WHEN f.EstadoFactura = 0 THEN 'C' WHEN f.PendienteCancelar = 1 THEN 'Pendiente Cancelar' WHEN f.TipoComprobante <> 'ingreso' THEN 'NDC' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'INC' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFactura,
            (SELECT CASE WHEN f.FacturaEnviada = 1 THEN 'Si' ELSE 'No' END) AS Enviado,            
            f.CanceladaSAT as CanceladaSAT,
            (CASE WHEN !ISNULL((SELECT MAX(FechaPago)FROM c_pagosparciales WHERE IdFactura = f.IdFactura)) 
            THEN (SELECT MAX(FechaPago)FROM c_pagosparciales WHERE IdFactura = f.IdFactura)
            ELSE f.FechaPago END) AS FechaPago,
            f.Folio AS Folio,
            IF(f.TipoFactura=1,'Lectura Corte','') AS TipoFactura
            FROM c_factura AS f 
            LEFT JOIN c_cliente AS c ON TRIM(f.RFCReceptor) = TRIM(c.RFC) AND c.ClaveCliente = (SELECT MIN(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor) LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta 
            WHERE Serie = 'PREF' " . $where . "
            ORDER BY FechaFacturacion DESC;";
            //echo $consulta;
            ?>
            <a href="#" id="liga_pref" onclick="mostrarTablaPrefacturas();
                    return false;">Mostrar prefacturas</a><br/>
            <table class='dataTable' id="tabla4" style="display: none; width: 100%;">
                <thead>
                    <tr>                        
                        <th width='2%' align='center' scope='col'>Folio</th>
                        <th width='2%' align='center' scope='col'>Fecha</th>
                        <th width='2%' align='center' scope='col'>Nombre receptor</th>
                        <th width='2%' align='center' scope='col'>Nombre Emisor</th>
                        <th width='2%' align='center' scope='col'>Subtotal</th>
                        <th width='2%' align='center' scope='col'>Importe</th>
                        <th width='2%' align='center' scope='col'>Total</th>
                        <th width='2%' align='center' scope='col'></th>                        
                        <th width='2%' align='center' scope='col'>Tipo de factura</th>
                        <th width='2%' align='center' scope='col'>Detalle</th>
                        <th width='2%' align='center' scope='col'>Enviado</th>
                        <th width='2%' align='center' scope='col'></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $catalogo = new CatalogoFacturacion();
                    $result = $catalogo->obtenerLista($consulta);
                    $result = null;
                    while ($rs = mysql_fetch_array($result)) {
                        echo "<tr>";
                        echo "<td>" . $rs['Folio'] . "</td>";
                        echo "<td>" . $rs['FechaFacturacion'] . "</td>";
                        echo "<td>" . $rs['NombreReceptor'] . "</td>";
                        echo "<td>" . $rs['NombreEmisor'] . "</td>";
                        echo "<td>$" . $rs['subtotal'] . "</td>";
                        echo "<td>$" . $rs['importe'] . "</td>";
                        echo "<td>$" . $rs['Total'] . "</td>";
                        echo "<td align='center' scope='row'><a href='" . $_SESSION['liga'] . "/cfdi/" . $rs['PDF'] . "?uguid=" . $_SESSION['user'] . "' target='_blank'><img src='resources/images/pdf_descarga.png' title='PDF Factura' style='width: 32px; height: 32px;'/></a></td>";
                        echo "<td>" . $rs['TipoFactura'] . "</td>";
                        echo "<td align='center' scope='row'>";
                        if ($permisos_grid->getModificar()) {
                            if ($rs['TipoComprobante'] == "F") {
                                echo "<a href='" . $liga . "/cfdi/DatosFactura.aspx?IdFactura=" . $rs['IdFactura'] . "&TipoDocumento=ingreso&uguid=" . $_SESSION['user'] . "' target='_blank'><img src='resources/images/Modify.png' title='Generar Factura' style='width: 32px; height: 32px;'/></a>";
                            } else {
                                echo "<a href='" . $liga . "/cfdi/DatosFactura.aspx?IdFactura=" . $rs['IdFactura'] . "&TipoDocumento=egreso&uguid=" . $_SESSION['user'] . "' target='_blank'><img src='resources/images/Modify.png' title='Generar Factura' style='width: 32px; height: 32px;'/></a>";
                            }
                        }
                        echo "</td>";
                        echo "<td align='center' scope='row'>";
                        if ($permisos_grid->getModificar()) {
                            echo "<a href='" . $_SESSION['liga'] . "/cfdi/EnvioCorreos.aspx?IdFactura=" . $rs['IdFactura'] . "&uguid=" . $_SESSION['user'] . "' target='_blank'>";
                        }
                        echo $rs['Enviado'];
                        if ($permisos_grid->getModificar()) {
                            echo "</a>";
                        }
                        echo "</td>";
                        echo "<td></td>";
                        echo "</tr>";
                    }
                    $consulta = "SELECT f.Folio, f.IdFactura,DATE(f.FechaFacturacion) AS FechaFacturacion, c.NombreRazonSocial AS NombreReceptor, 
                        e.RazonSocial AS NombreEmisor,
                        (CASE WHEN !ISNULL(con.idConcepto) THEN FORMAT(SUM(DISTINCT con.PrecioUnitario*con.Cantidad),2) ELSE FORMAT(f.Total/1.16,2) END) AS Subtotal,
                        (CASE WHEN !ISNULL(con.idConcepto) THEN FORMAT((SUM(DISTINCT con.PrecioUnitario*con.Cantidad)*1.16),2) ELSE FORMAT(f.Total,2) END) AS Total,
                        (CASE WHEN !ISNULL(con.idConcepto) THEN FORMAT((SUM(DISTINCT con.PrecioUnitario*con.Cantidad)*0.16),2) ELSE FORMAT(f.Total - (f.Total/1.16),2) END) AS Importe,
                        IF(ISNULL(f.FacturaXML),'Prefactura','Facturada') AS TipoFactura, tf.Nombre AS TipoFacturaNombre,
                        (SELECT CASE WHEN f.FacturaEnviada = 1 THEN 'Si' ELSE 'No' END) AS Enviado
                        FROM `c_factura` AS f
                        LEFT JOIN c_datosfacturacionempresa AS e ON e.IdDatosFacturacionEmpresa = f.RFCEmisor
                        LEFT JOIN c_cliente AS c ON c.ClaveCliente=f.RFCReceptor
                        LEFT JOIN c_conceptos AS con ON con.idFactura=f.IdFactura
                        LEFT JOIN c_pagosparciales AS pp ON pp.id_factura=f.IdFactura
                        LEFT JOIN c_tipofactura AS tf ON f.Id_TipoFactura = tf.Id_TipoFactura
                        WHERE Invisible = 0 GROUP BY f.IdFactura HAVING TipoFactura = 'Prefactura';";
                    $catalogo = new Catalogo();
                    $result = $catalogo->obtenerLista($consulta);
                    $result = null;
                    while ($rs = mysql_fetch_array($result)) {
                        echo "<tr>";
                        echo "<td>" . $rs['Folio'] . "</td>";
                        echo "<td>" . $rs['FechaFacturacion'] . "</td>";
                        echo "<td>" . $rs['NombreReceptor'] . "</td>";
                        echo "<td>" . $rs['NombreEmisor'] . "</td>";
                        echo "<td>$" . $rs['Subtotal'] . "</td>";
                        echo "<td>$" . $rs['Importe'] . "</td>";
                        echo "<td>$" . $rs['Total'] . "</td>";

                        if ($rs['Generada'] == 0) {
                            echo "<td align='center' scope='row'><a href='WEB-INF/Controllers/facturacion/Controller_PDF_Factura.php?id=" . $rs['IdFactura'] . "' target='_blank'><img src='resources/images/pdf_descarga.png' title='PDF Factura' style='width: 32px; height: 32px;'/></a></td>";
                        } else {
                            echo "<td align='center' scope='row'></td>";
                        }
                        echo "<td>" . $rs['TipoFactura'] . " " . $rs['TipoFacturaNombre'] . "</td>";
                        echo "<td align='center' scope='row'>";
                        if ($permisos_grid->getModificar()) {
                            echo "<a href='principal.php?mnu=facturacion&action=alta_factura&id=" . $rs['IdFactura'] . "' target='_blank'>
                                <img src='resources/images/Modify.png' title='Generar Factura' style='width: 32px; height: 32px;'/></a>";
                        }
                        echo "</td>";
                        echo "<td align='center' scope='row'>";
                        if ($permisos_grid->getModificar()) {
                            echo "<a href='facturacion/enviar_factura.php?id=" . $rs['IdFactura'] . "' target='_blank' return false;'>";
                        }
                        echo $rs['Enviado'];
                        if ($permisos_grid->getModificar()) {
                            echo "</a>";
                        }
                        echo "</td>";
                        echo "<td align='center' scope='row'>";
                        if ($permisos_grid->getBaja()) {
                            echo "<a href='#' onclick=\"Eliminarfactura('facturacion/Eliminarfactura.php','" . $rs['IdFactura'] . "'," . $rs['Folio'] . "); return false;\"><img src='resources/images/Erase.png' title='Eliminar Prefactura'/></a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            <input type="hidden" id="same_page" name="same_page" value="<?php echo $same_page ?>"/>
        </div>
    <script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/facturacion_reporte_tabla.js"></script>
    <script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/lista_pendientes.js"></script>   
    </body> 
</html>