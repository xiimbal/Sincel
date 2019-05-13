<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
$controlador = $_SESSION['ruta_controler'] . "Controler_Almacen.php";

$tecnicos = array();
$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT IdUsuario, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS tecnico FROM `c_usuario` WHERE IdPuesto = 18;");

$tecnicos["0"] = "Selecciona al técnico";
while ($rs = mysql_fetch_array($query)) {
    $tecnicos[$rs['IdUsuario']] = $rs['tecnico'];
}
$idTicket = "";
$cliente = "";
$color = "";
$colorPOST = "";
$checked = "";
$cerrados = "";
$cancelados = "";
$moroso = "";
$whereCerrado = "AND (t.EstadoDeTicket=1 OR t.EstadoDeTicket=3 OR t.EstadoDeTicket=5)";
$whereMoroso = "AND cl.IdEstatusCobranza=1";

if (isset($_POST["ticket"]) && $_POST["ticket"] != "") {
    $idTicket = $_POST["ticket"];
    $cerrados = "checked";
    $cancelados = "checked";
    $moroso = "checked";
}
if (isset($_POST["cliente"]) && $_POST["cliente"] != "")
    $cliente = "t.ClaveCliente = '" . $_POST['cliente'] . "' AND ";
if (isset($_POST["color"]) && $_POST["color"] != "")
    $colorPOST = $_POST["color"];
if (isset($_POST["cerrado"]) && $_POST["cerrado"] == "1") {
    $cerrados = "checked";
}
if (isset($_POST["cancelado"]) && $_POST["cancelado"] == "1") {
    $cancelados = "checked";
}
if ($cerrados != "" && $cancelados != "") {
    $whereCerrado = "";
} else {
    if ($cerrados != "")
        $whereCerrado = "AND t.EstadoDeTicket<>4";
    else if ($cancelados != "")
        $whereCerrado = "AND t.EstadoDeTicket<>2";
}
if (isset($_POST["moroso"]) && $_POST["moroso"] == "1") {
    $moroso = "checked";
    $whereMoroso = "AND (cl.IdEstatusCobranza=1 OR cl.IdEstatusCobranza=2)";
}


$idMensajeria = array();
$nombreMensajeria = array();
$contador = 0;
$idUsuario = "";
$userAlmacen = "";
$almacen1 = $catalogo->obtenerLista("SELECT * FROM c_usuario WHERE Loggin='" . $_SESSION['user'] . "'");
if ($rs = mysql_fetch_array($almacen1)) {
    $idUsuario = $rs['IdPuesto'];
    $userAlmacen = $rs['IdUsuario'];
}
//else
//    $consulta = "SELECT * FROM c_almacen a WHERE a.Activo=1 ORDER BY a.nombre_almacen ASC";

$mensajeria = "SELECT * FROM c_mensajeria m WHERE m.Activo=1 ORDER BY m.Nombre ASC";
$query1 = $catalogo->obtenerLista($mensajeria);
while ($rs = mysql_fetch_array($query1)) {
    $idMensajeria[$contador] = $rs['IdMensajeria'];
    $nombreMensajeria[$contador] = $rs['Nombre'];
    $contador++;
}
$mostrarEstatus = "";
if (isset($_POST['estadoMostar']))
    $mostrarEstatus = $_POST['estadoMostar'];
?>

<!DOCTYPE html>
<html lang="es">
    <head>        

        <script type="text/javascript" language="javascript" src="resources/js/paginas/validadRefaccion.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/listaValidarRefaccion.js"></script>
        <style>
            .circle {
                border-radius: 50%/50%; 
                width: 30px;
                height: 30px;        
            }
        </style>
        <script>
            $("#busqueda_ticket").keyup(function(event) {
                if (event.keyCode == 13) {
                    $("#boton_aceptar").click();
                }
            });
        </script>
    </head>
    <body>
        <div class="principal">            
            <br/><br/>           
            <table style="width: 100%;">
                <tr>
                    <td>Ticket</td>
                    <td>
                        <div>
                            <input id="busqueda_ticket" name="busqueda_ticket"  style="width:200px" value="<?php echo $idTicket ?>"/>  
                        </div>
                        <div id="error_busqueda_ticket" style="display: none; color:red;">Ingresa s&oacute;lo n&uacute;meros por favor</div>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>                    
                    <td></td>
                    <td><input type="checkbox" name="verCerrado" id="verCerrado" <?php echo $cerrados ?>/>Ver cerrados</td>
                </tr>
                <tr>                    
                    <td>Cliente</td>
                    <td>
                        <select id="cliente_ticket" name="cliente_ticket" style="width: 200px;" >
                            <?php
                            /* Inicializamos la clase */
                            $catalogo = new Catalogo();
                            $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                            echo "<option value=''>Todos los clientes</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if (isset($_POST['cliente']) && $_POST['cliente'] == $rs['ClaveCliente']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . "</option>";
                            }
                            ?> 
                        </select>
                    </td>
                    <td></td>
                    <td>                        
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
                    <td></td>
                    <td><input type="checkbox" name="verCancelado" id="verCancelado" <?php echo $cancelados ?>/>Ver cancelados</td>
                </tr>
                <tr>
                    <td><input type="Button"  id="boton_aceptar" name="boton_aceptar" value="Buscar ticket" class="boton" onclick="BuscarTicket('almacen/toner_listo_entregar.php');"/></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><input type="checkbox" name="verMoroso" id="verMoroso" <?php echo $moroso ?>/>Ver Morosos</td>
                </tr>
            </table>            
            <br/><br/>
            <table id="tAlmacen" class="tabla_grid_length">
                <thead>
                    <tr>
                        <?php
                        $cabeceras = array("Ticket", "Cliente", "Falla", "Toner", "Surtidas", "Detalle", "Reporte", "Tipo Envio");
                        for ($i = 0; $i < (count($cabeceras)); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        ?>                                                                      
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($idTicket == "") {
                        $consulta = "SELECT t.IdTicket,nt.IdNotaTicket,c.Modelo,cl.NombreRazonSocial,nr.CantidadSurtida,(SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area,DATE(t.FechaHora) AS FechaHora
                                        ,CONCAT ('(',nr.Cantidad,') ',c.Modelo) AS refaccion,DATEDIFF(NOW(), t.FechaHora) AS diferencia,nt.IdEstatusAtencion
                                        ,t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza,t.TipoReporte,e.Nombre AS ultimoEstatus,nt.DiagnosticoSol,t.NombreCentroCosto,t.DescripcionReporte,nr.Cantidad,c.NoParte,nt.MostrarCliente,nt.Activo,nt.UsuarioSolicitud,nr.IdAlmacen,cl.ClaveCliente                                                       
                                        FROM k_nota_refaccion nr,c_notaticket nt,c_ticket t,c_componente c,c_cliente cl,c_estado e
                                        WHERE t.IdTicket=nt.IdTicket AND nt.IdNotaTicket=nr.IdNotaTicket AND nr.NoParteComponente=c.NoParte AND t.ClaveCliente=cl.ClaveCliente
                                        AND $cliente nt.IdEstatusAtencion=e.IdEstado
                                        AND nt.IdEstatusAtencion=21
                                        AND nr.Cantidad<>0 AND c.IdTipoComponente=2 $whereCerrado $whereMoroso                                    
                                        ORDER BY nt.IdNotaTicket,c.Modelo ASC";
                    } else {
                        $consulta = "SELECT t.IdTicket,nt.IdNotaTicket,c.Modelo,cl.NombreRazonSocial,nr.CantidadSurtida,(SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area,DATE(t.FechaHora) AS FechaHora
                                            ,CONCAT ('(',nr.Cantidad,') ',c.Modelo) AS refaccion,DATEDIFF(NOW(), t.FechaHora) AS diferencia,nt.IdEstatusAtencion
                                            ,t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza,t.TipoReporte,e.Nombre AS ultimoEstatus,nt.DiagnosticoSol,t.NombreCentroCosto,t.DescripcionReporte,nr.Cantidad,c.NoParte,nt.MostrarCliente,nt.Activo,nt.UsuarioSolicitud,nr.IdAlmacen,cl.ClaveCliente                                                      
                                            FROM k_nota_refaccion nr,c_notaticket nt,c_ticket t,c_componente c,c_cliente cl,c_estado e
                                            WHERE t.IdTicket=nt.IdTicket AND nt.IdNotaTicket=nr.IdNotaTicket AND nr.NoParteComponente=c.NoParte AND t.ClaveCliente=cl.ClaveCliente
                                            AND $cliente nt.IdEstatusAtencion=e.IdEstado
                                            AND t.IdTicket=" . $idTicket . "
                                            AND nt.IdEstatusAtencion=21
                                            AND nr.Cantidad<>0 AND c.IdTipoComponente=2                                       
                                            ORDER BY nt.IdNotaTicket,c.Modelo ASC";
                    }
                    $contador = 0;
                    $query = $catalogo->obtenerLista($consulta);
                    $contadorFila = 0;
                    while ($rs = mysql_fetch_array($query)) {/* Recorremos todos los tickets resultantes del query */
                        $booleanFecha = FALSE;
                        $fecha_limite = strtotime("2014-03-31");
                        $fecha_ticket = strtotime($rs['FechaHora']);
                        if ($fecha_ticket >= $fecha_limite) {
                            $booleanFecha = TRUE;
                        } else {
                            $booleanFecha = FALSE;
                        }
                        if ($checked == "" && $rs['IdEstatusAtencion'] == "16") {/* Si ya esta cerrado por nota, saltamos */
                            continue;
                        }
                        /*                         * *********************    Obtenemos el color de la fila   ******************************** */
                        $color = "#F7F7DE";

                        if (isset($rs['IdEstatusAtencion'])) {/* Si hay estado de la ultima nota */
                            if ($rs['IdEstatusAtencion'] != "16" && (isset($rs['EstadoDeTicket']) && $rs['EstadoDeTicket'] != "2")) {/* Si el ticket no esta cerrado */
                                if (strtoupper($rs['IdTipoCliente']) == "1") {/* Si el cliente es VIP */
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

                        if ($rs['EstadoDeTicket'] == "4") {/* Ticket cancelado */
                            $color = "#D1D0CE";
                        }

                        if ($rs['TipoReporte'] == "26") {/* Si es Mtto preventivo */
                            $color = "#00FFFF";
                        }
                        $contador++;
                        echo "<tr style='background-color: $color; color:black;'>";
                        echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['NombreRazonSocial'] . " - " . $rs['NombreCentroCosto'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['DescripcionReporte'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['refaccion'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['CantidadSurtida'] . "</td>";
                        ?>
                    <td align='center' scope='row'>
                        <?php if ($booleanFecha) { ?>
                            <a href='#' onclick='detalleTicketAlmacen("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo "15"; ?>", "1");
                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a> 
                           <?php } else {
                               ?>
                            <a href='#' onclick='lanzarPopUp("Detalle", "hardware/alta_ticket_detalle_hw.php?tipo=<?php echo "SUMINISTRO" . "&id=" . $rs['IdTicket']; ?>");
                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                           <?php }
                           ?>

                    </td>
                    <td align='center' scope='row'>
                        <a href='#' onclick='lanzarPopUp("Generar reporte", "almacen/lista_crearReporteNotaToner.php?ticket=<?php echo$rs['IdTicket']; ?>");
                                return false;' title='Generar reporte' ><img src="resources/images/icono_impresora.png" width="24" height="24"/></a>

                    </td>  
                    <?php
                    $totalRestantes = (int) $rs['Cantidad'] - (int) $rs['CantidadSurtida'];
                    if ($totalRestantes != 0) {
                        ?>
                        <td align='center' scope='row'> <a href='#' onclick='VerEnvioToner("almacen/altaEnvioToner.php", "<?php echo $rs['IdNotaTicket'] ?>");
                                return false;' title='Envío de toner' ><img src="resources/images/Apply.png" width="24" height="24"/></a></td>
                            <?php
                        } else {
                            echo "<td align='center' scope='row'></td>";
                        }
//                    } else {
//                        echo " <td align='center' scope='row'></td><td align='center' scope='row'></td>";
//                        echo " <td align='center' scope='row'></td><td align='center' scope='row'></td>";
//                    }
                        echo "<input type='hidden' id='ticket$contador' id='ticket$contador' value='" . $rs['IdTicket'] . "'/>";
                        echo "<input type='hidden' id='nota$contador' id='nota$contador' value='" . $rs['IdNotaTicket'] . "'/>";
                        echo "<input type='hidden' id='descripcion$contador' id='descripcion$contador' value='" . $rs['DiagnosticoSol'] . "'/>";
                        echo "<input type='hidden' id='refaccion$contador' id='refaccion$contador' value='" . $rs['NoParte'] . "'/>";
                        echo "<input type='hidden' id='cantidad$contador' id='cantidad$contador' value='" . $rs['Cantidad'] . "'>";
                        echo "<input type='hidden' id='mostrar$contador' id='mostrar$contador' value='" . $rs['MostrarCliente'] . "'/>";
                        echo "<input type='hidden' id='usuarioSolicitud$contador' id='usuarioSolicitud$contador' value='" . $rs['UsuarioSolicitud'] . "'/>";
                        echo "<input type='hidden' id='almacen$contador' id='almacen$contador' value='" . $rs['IdAlmacen'] . "'/>";
                        echo "<input type='hidden' id='cliente$contador' id='cliente$contador' value='" . $rs['ClaveCliente'] . "'/>";
                        echo "<input type='hidden' id='modelo$contador' id='modelo$contador' value='" . $rs['Modelo'] . "'/>";
                        $contadorFila++;
                    }
                    ?>


                </td>
                </tbody>
                <input type="hidden" id="color_hidden" name="color_hidden" value="<?php echo $colorPOST; ?>"/>
                <input type="hidden" id="numeroCheck" name="numeroCheck" value="<?php echo $contador; ?>"/>
            </table>
        </div>
    </body>
</html>