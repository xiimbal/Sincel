<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$tecnicos = array();
$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT IdUsuario, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS tecnico FROM `c_usuario` WHERE IdPuesto = 18;");

$tecnicos["0"] = "Selecciona al técnico";
while ($rs = mysql_fetch_array($query)) {
    $tecnicos[$rs['IdUsuario']] = $rs['tecnico'];
}

$permisos_grid = new PermisosSubMenu();
$same_page = "almacen/lista_refaccionesSolicitadas.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$idTicket = "";
$cliente = "";
$color = "";
$colorPOST = "";
$checked = "";
$cerrados = "";
$cancelados = "";
$moroso = "";
$enviados = "";
$whereCerrado = "AND (t.EstadoDeTicket=1 OR t.EstadoDeTicket=3 OR t.EstadoDeTicket=5)";
$whereMoroso = "AND cl.IdEstatusCobranza=1";
$whereEnviados = " AND (Cantidad - CantidadSurtida) > 0 ";
$whereAreaAtencion = "";
$areaAtencion = "";
$mostrarGrid = "0";
$having = "";

if (isset($_POST["areaAtencion"]) && $_POST["areaAtencion"] != "0") {
    //$whereAreaAtencion = "AND t.AreaAtencion=" . $_POST["areaAtencion"];
    $areaAtencion = $_POST["areaAtencion"];
    $mostrarGrid = "1";
    $having = " HAVING idArea = $areaAtencion ";
}

if (isset($_POST["ticket"]) && $_POST["ticket"] != "") {
    $idTicket = $_POST["ticket"];
    $cerrados = "checked";
    $cancelados = "checked";
    $moroso = "checked";
    $mostrarGrid = "1";
}

if (isset($_POST["cliente"]) && $_POST["cliente"] != "") {
    $cliente = "t.ClaveCliente = '" . $_POST['cliente'] . "' AND ";
    $mostrarGrid = "1";
}
if (isset($_POST["color"]) && $_POST["color"] != "") {
    $colorPOST = $_POST["color"];
    $mostrarGrid = "1";
}
if (isset($_POST["cerrado"]) && $_POST["cerrado"] == "1") {
    $cerrados = "checked";
    $mostrarGrid = "1";
}
if (isset($_POST["cancelado"]) && $_POST["cancelado"] == "1") {
    $cancelados = "checked";
    $mostrarGrid = "1";
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
    $mostrarGrid = "1";
}

if (isset($_POST["enviados"]) && $_POST["enviados"] == "1") {
    $enviados = "checked='checked'";
    $mostrarGrid = "1";
    $whereEnviados = "";
}

$idestatus = array();
$nombreEstatus = array();
$contador = 0;
$estado = $catalogo->obtenerLista("SELECT *FROM c_estado e WHERE e.IdEstado = '20' OR e.IdEstado = '19' OR e.IdEstado = '21' ORDER BY e.Nombre ASC");
while ($rs = mysql_fetch_array($estado)) {
    $idestatus[$contador] = $rs['IdEstado'];
    $nombreEstatus[$contador] = $rs['Nombre'];
    $contador++;
}
$idAlmacen = array();
$nombreAlmacen = array();
$contador = 0;
$idUsuario = "";
$userAlmacen = "";

$almacenPredeterminado = "6";
$almacen1 = $catalogo->obtenerLista("SELECT IdPuesto,IdUsuario,IdAlmacen  FROM c_usuario WHERE IdUsuario='" . $_SESSION['idUsuario'] . "'");
if ($rs = mysql_fetch_array($almacen1)) {
    $idUsuario = $rs['IdPuesto'];
    $userAlmacen = $rs['IdUsuario'];
    if (isset($rs['IdAlmacen'])) {
        $almacenPredeterminado = $rs['IdAlmacen'];
    }
}
//else
//    $consulta = "SELECT * FROM c_almacen a WHERE a.Activo=1 ORDER BY a.nombre_almacen ASC";
if ($idUsuario == '24') {
    $consulta = "SELECT * FROM k_responsablealmacen ra,c_almacen a ,c_usuario us WHERE ra.IdUsuario='" . $userAlmacen . "' AND a.Activo=1 AND ra.IdAlmacen=a.id_almacen AND ra.IdUsuario=us.IdUsuario ORDER BY a.nombre_almacen ASC";
} else {
    $consulta = "SELECT * FROM c_almacen a WHERE a.Activo=1 AND a.TipoAlmacen=1 AND a.id_almacen<>9 ORDER BY a.nombre_almacen ASC";
}


$almacen = $catalogo->obtenerLista($consulta);
while ($rs = mysql_fetch_array($almacen)) {
    $idAlmacen[$contador] = $rs['id_almacen'];
    $nombreAlmacen[$contador] = $rs['nombre_almacen'];
    $contador++;
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <!--easyui-->        

        <script type="text/javascript" language="javascript" src="resources/js/paginas/validadRefaccion.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/listaValidarRefaccion.js"></script>        
        <script>
            $("#busqueda_ticket").keyup(function (event) {
                if (event.keyCode == 13) {
                    $("#boton_aceptar").click();
                }
            });
            $(".filtro").multiselect({
                multiple: false,
                noneSelectedText: "No ha seleccionado",
                selectedList: 1
            }).multiselectfilter();
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
                    <td>Área de atención</td>
                    <td>
                        <select id="slcAreaAtencion" name="slcAreaAtencion" style="width: 200px;">
                            <option value="0">Todas las áreas</option>
                            <?php
                            $queryAreaAtencion = $catalogo->getListaAlta("c_area", "Descripcion");
                            while ($rs = mysql_fetch_array($queryAreaAtencion)) {
                                $s = "";
                                if ($areaAtencion != "" && $areaAtencion == $rs['IdArea']) {
                                    $s = "selected";
                                }
                                echo "<option value='" . $rs['IdArea'] . "' $s>" . $rs['Descripcion'] . "</option>";
                            }
                            ?>
                        </select> 
                    </td>
                    <td></td>
                    <td><input type="checkbox" name="verCerrado" id="verCerrado" <?php echo $cerrados ?>/>Ver cerrados</td>
                </tr>
                <tr>                    
                    <td>Cliente</td>
                    <td>
                        <select id="cliente_ticket" name="cliente_ticket" style="width: 200px;" class="filtro">
                            <?php
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
                    <td><input type="Button" id="boton_aceptar" name="boton_aceptar" value="Buscar ticket" class="boton" 
                               onclick="BuscarTicket('almacen/lista_refaccionesSolicitadas.php');"/></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><input type="checkbox" name="verMoroso" id="verMoroso" <?php echo $moroso ?>/>Ver Morosos</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><input type="checkbox" name="verEnviados" id="verEnviados" <?php echo $enviados; ?>/>Ver enviados</td>
                </tr>
            </table>    

            <?php if (isset($_POST['mostrar']) && $_POST['mostrar'] == "true") { ?>

                <br/><br/>
                <h1>Refacciones solicitadas</h1>    
                <?php
                if ($permisos_grid->getModificar()) {
                    ?>
                    <input type="button" class="boton" id="guardar_seleccionados" value="Guardar seleccionados" onclick="guardarMultiplesSeleccionadas();
                                    return false;" style="float: right;"/>
                           <?php
                       }
                       ?>            
                <br/><br/>
                <div style="display: inline; float: right;"><input type="checkbox" id="slc_todo_solicitado" onclick="seleccionarTodosSolicitadosRef();"/>
                    <div id="mensaje_sel" style="display: inline; ">Seleccionar todo</div></div>
                <a href="#" id="liga_solicitado" onclick="mostrarSolicitados();
                            return false;">Mostrar solicitados</a>            
                <br/><br/>
                <table id="tAlmacen" class="tabla_grid" style="display: none; width: 100%;">
                    <thead>
                        <tr>
                            <?php
                            $cabeceras = array("Ticket", "Fecha", "NoSerie", "Cliente", "Falla", "", "Área Atención", "Refacción", "Último Estado",
                                "", "", "Almacén", "", "Estatus", "", "", "");
                            for ($i = 0; $i < (count($cabeceras)); $i++) {
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                            }
                            ?>                                                                      
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($idTicket == "") {
                            $consulta = "SELECT t.IdTicket,nt.IdNotaTicket,c.Modelo,cl.NombreRazonSocial,nr.CantidadSurtida, t.AreaAtencion, 
                                (SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area, DATE(t.FechaHora) AS FechaHora,
                                CONCAT ('(',nr.Cantidad,') ',c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS refaccion, c.IdTipoComponente, 
                                DATEDIFF(NOW(), t.FechaHora) AS diferencia,nt.IdEstatusAtencion,t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza, 
                                t.TipoReporte,e.Nombre AS ultimoEstatus,nt.DiagnosticoSol,t.NombreCentroCosto,t.DescripcionReporte,nr.Cantidad, 
                                c.NoParte,nt.MostrarCliente,nt.Activo,nt.UsuarioSolicitud , 
                                (SELECT CASE WHEN t.AreaAtencion = 2 THEN (SELECT group_concat(ClaveEspEquipo separator ',') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie, 
                                (SELECT (SELECT CASE WHEN ac.cantidad_existencia IS NULL THEN '0' ELSE ac.cantidad_existencia END) AS cantidade 
                                FROM k_almacencomponente ac WHERE ac.NoParte=nr.NoParteComponente AND ac.id_almacen=$almacenPredeterminado) AS cantidadExistente, 
                                (CASE WHEN !ISNULL(ar.IdArea) THEN ar.Descripcion ELSE ar2.Descripcion END) AS areaAtencion, 
                                (CASE WHEN !ISNULL(ar.IdArea) THEN ar.IdArea ELSE ar2.IdArea END) AS idArea, 
                                lastnt.IdEstatusAtencion AS IdUltimoEstatusNota 
                                FROM k_nota_refaccion nr
                                LEFT JOIN c_notaticket nt ON  nt.IdNotaTicket=nr.IdNotaTicket
                                LEFT JOIN c_ticket t ON t.IdTicket=nt.IdTicket 
                                LEFT JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion 
                                LEFT JOIN c_area AS ar2 ON ar2.IdArea = e2.IdArea                                
                                LEFT JOIN c_componente c ON nr.NoParteComponente=c.NoParte 
                                LEFT JOIN c_cliente cl ON t.ClaveCliente=cl.ClaveCliente 
                                LEFT JOIN c_estado e ON nt.IdEstatusAtencion=e.IdEstado
                                LEFT JOIN c_notaticket AS lastnt ON lastnt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket)
                                LEFT JOIN c_estado AS e3 ON lastnt.IdEstatusAtencion = e3.IdEstado 
                                LEFT JOIN c_area AS ar ON ar.IdArea = e3.IdArea
                                WHERE (nt.IdEstatusAtencion=19 OR nt.IdEstatusAtencion=20 OR nt.IdEstatusAtencion=24) $whereCerrado $whereMoroso $whereAreaAtencion  
                                AND $cliente nr.Cantidad<>0 AND c.IdTipoComponente=1 $having 
                                ORDER BY nt.IdNotaTicket,c.Modelo ASC;";
                        } else {
                            $consulta = "SELECT t.IdTicket,nt.IdNotaTicket,c.Modelo,cl.NombreRazonSocial,nr.CantidadSurtida, t.AreaAtencion, 
                                (SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area,DATE(t.FechaHora) AS FechaHora,
                                CONCAT ('(',nr.Cantidad,') ',c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS refaccion,c.IdTipoComponente, DATEDIFF(NOW(), t.FechaHora) AS diferencia, 
                                nt.IdEstatusAtencion,t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza,t.TipoReporte,e.Nombre AS ultimoEstatus,nt.DiagnosticoSol, 
                                t.NombreCentroCosto,t.DescripcionReporte,nr.Cantidad,c.NoParte,nt.MostrarCliente,nt.Activo,nt.UsuarioSolicitud, 
                                (SELECT CASE WHEN t.AreaAtencion = 2 THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie, 
                                (SELECT (SELECT CASE WHEN ac.cantidad_existencia IS NULL THEN '0' ELSE ac.cantidad_existencia END) AS cantidade FROM k_almacencomponente ac WHERE ac.NoParte=nr.NoParteComponente AND ac.id_almacen=$almacenPredeterminado) AS cantidadExistente, 
                                (CASE WHEN !ISNULL(ar.IdArea) THEN ar.Descripcion ELSE ar2.Descripcion END) AS areaAtencion, 
                                (CASE WHEN !ISNULL(ar.IdArea) THEN ar.IdArea ELSE ar2.IdArea END) AS idArea, 
                                lastnt.IdEstatusAtencion AS IdUltimoEstatusNota  
                                FROM k_nota_refaccion nr 
                                LEFT JOIN c_notaticket nt ON nt.IdNotaTicket=nr.IdNotaTicket
                                LEFT JOIN c_ticket t ON t.IdTicket=nt.IdTicket
                                LEFT JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion 
                                LEFT JOIN c_area AS ar2 ON ar2.IdArea = e2.IdArea                                
                                LEFT JOIN c_componente c ON nr.NoParteComponente=c.NoParte
                                LEFT JOIN c_cliente cl ON t.ClaveCliente=cl.ClaveCliente 
                                LEFT JOIN c_estado e ON nt.IdEstatusAtencion=e.IdEstado
                                LEFT JOIN c_notaticket AS lastnt ON lastnt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket)
                                LEFT JOIN c_estado AS e3 ON lastnt.IdEstatusAtencion = e3.IdEstado 
                                LEFT JOIN c_area AS ar ON ar.IdArea = e3.IdArea
                                WHERE t.IdTicket=$idTicket AND (nt.IdEstatusAtencion=19 OR nt.IdEstatusAtencion=20 OR nt.IdEstatusAtencion=24) 
                                AND nr.Cantidad<>0 AND c.IdTipoComponente=1 
                                ORDER BY nt.IdNotaTicket,c.Modelo ASC;";
                        }
                        $contadorFila = 1;
                        //echo $consulta;
                        $query = $catalogo->obtenerLista($consulta);
                        while ($rs = mysql_fetch_array($query)) {/* Recorremos todos los tickets resultantes del query */
                            $booleanFecha = FALSE;
                            $ticket_abierto = true;
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

                            if ($rs['IdEstatusAtencion'] == "20" || $rs['IdEstatusAtencion'] == "19") {//En backorder o vericando usado, se checa que el componente no se haya cancelado anteriormente
                                $consulta = "SELECT nt.IdNotaTicket, nt.DiagnosticoSol, nt.IdEstatusAtencion, e.Nombre AS Estado, nr.NoParteComponente, nr.Cantidad, c.Modelo,
                                    nt.FechaHora
                                    FROM `c_notaticket` AS nt
                                    LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
                                    LEFT JOIN c_estado AS e ON e.IdEstado = nt.IdEstatusAtencion
                                    LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
                                    LEFT JOIN c_ticket AS t ON t.IdTicket = nt.IdTicket
                                    WHERE nt.IdTicket = " . $rs['IdTicket'] . " AND nt.IdEstatusAtencion = 9 AND nr.NoParteComponente='" . $rs['NoParte'] . "';";

                                $resultCancelado = $catalogo->obtenerLista($consulta);
                                while ($rsCancelado = mysql_fetch_array($resultCancelado)) {
                                    if ($rsCancelado['Cantidad'] == "0") {//Si el componente ya está cancelado
                                        continue 2;
                                    }
                                }
                            }
                            /*                             * *********************    Obtenemos el color de la fila   ******************************** */
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
                            /* Si el ticket esta cerrado */
                            if ($rs['EstadoDeTicket'] == "2" || $rs['EstadoDeTicket'] == "4" || $rs['IdUltimoEstatusNota'] == "16" || $rs['IdUltimoEstatusNota'] == "59") {
                                $ticket_abierto = false;
                                $color = "#F7F7DE";
                                continue;
                            }

                            $series = explode(",", $rs['NumSerie']);
                            $texto = "";
                            foreach ($series as $value) {
                                $texto.= "<a href='#' onclick='
                                cambiarContenidos(\"almacen/alta_bitacora.php?consulta_tiquet=almacen/lista_refaccionesSolicitadas.php&NoSerie=$value\"); 
                                return false;'>" . $value . "</a>,";
                            }
                            $texto = substr($texto, 0, strlen($texto) - 1);

                            echo "<tr style='background-color: $color; color:black;'>";
                            echo "<td align='center' scope='row'><a href='#' title='Filtar ticket " . $rs['IdTicket'] . "'
                            onclick='BuscarRefaccionesByTicket(\"" . $rs['IdTicket'] . "\"); return false;'>" . $rs['IdTicket'] . "</a></td>";
                            echo "<td align='center' scope='row' style='font-size:10px'>" . $rs['FechaHora'] . "</td>";
                            echo "<td align='center' scope='row'>
                                $texto
                                <br/>
                                <a href='#' title='Ver refacciones solicitadas' onclick='lanzarHistoricoRefacciones(\"" . $rs['NumSerie'] . "\"); 
                                    return false;'><img src='resources/images/Textpreview.png' style='width: 20px; height: 20px;' title='Ver historial refacciones'/></a>
                                </td>"; //NoSerie
                            echo "<td align='center' scope='row' style='font-size:10px'>" . $rs['NombreRazonSocial'] . " - " . $rs['NombreCentroCosto'] . "</td>";
                            echo "<td align='center' scope='row' style='font-size:10px'>" . $rs['DescripcionReporte'] . "</td>";
                            ?>
                        <td align='center' scope='row' style='font-size:10px'>
                            <?php if ($booleanFecha) { ?>
                                <a href='#' onclick='detalleTicketAlmacen("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "1");
                                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a> 
                               <?php } else {
                                   ?>
                                <a href='#' onclick='lanzarPopUp("Detalle", "hardware/alta_ticket_detalle_hw.php?tipo=<?php echo $rs['area'] . "&id=" . $rs['IdTicket']; ?>");
                                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                               <?php }
                               ?>

                        </td>                                      
                        <?php
                        echo "<td align='center' scope='row' style='font-size:10px'>" . $rs['areaAtencion'] . "</td>";
                        echo "<td align='center' scope='row' style='font-size:10px'>";
                        echo "Refaccion solicitada: " . $rs['refaccion'];
                        echo "<br/>Refaccion a surtir:";
                        /* Obtenemos los componentes compatibles */
                        $consultaCompatibles = "SELECT c.NoParte, c.Modelo, c.Descripcion 
                        FROM c_bitacora AS b
                        LEFT JOIN k_equipocomponentecompatible AS kecc ON kecc.NoParteEquipo = b.NoParte
                        LEFT JOIN c_componente AS c ON c.NoParte = kecc.NoParteComponente
                        WHERE b.NoSerie = '" . $rs['NumSerie'] . "' AND c.Activo = 1 AND c.IdTipoComponente = " . $rs['IdTipoComponente'] . "
                        GROUP BY c.NoParte
                        ORDER BY c.Modelo;";
                        $resultCompatible = $catalogo->obtenerLista($consultaCompatibles);

                        if (mysql_num_rows($resultCompatible) > 1 && $ticket_abierto) {
                            echo "<select id='cambiarComponente_$contadorFila' name='cambiarComponente_$contadorFila' style='max-width: 180px;' onchange='mostrarExistencias(\"$contadorFila\", \"cambiarComponente_$contadorFila\");'>";
                            while ($rsCompatible = mysql_fetch_array($resultCompatible)) {
                                $s = "";
                                if ($rsCompatible['NoParte'] == $rs['NoParte']) {//Se selcciona el no de parte que se habia pedido originalmente
                                    $s = "selected = 'selected'";
                                }
                                echo "<option value='" . $rsCompatible['NoParte'] . "' $s>" . $rsCompatible['Modelo'] . " / " . $rsCompatible['NoParte'] . " / " . $rsCompatible['Descripcion'] . "</option>";
                            }
                            echo "</select>";
                        } else {
                            echo "<input type='text' value='" . $rs['NoParte'] . "' readonly='readonly' id='cambiarComponente_$contadorFila' name='cambiarComponente_$contadorFila'/>";
                        }

                        echo "</td>";
                        echo "<td align='center' scope='row' style='font-size:10px'>" . $rs['ultimoEstatus'] . "</td>";
                        ?>

                        <td align='center' scope='row'>
                            <?php if ($permisos_grid->getModificar()) { ?>
                                <a href='#' onclick='
                                    AgregarNotaTicketLista("nota","AgregarNota",<?php echo $rs['IdTicket']; ?>,"<?php echo $same_page; ?>");
                                                    return false;' title='Agregar nota' >
                                    <img src="resources/images/notes.ico" style="width:24px; height: 24px; "/>
                                </a>
                            <?php } ?>
                        </td>

                        <?php
                        $cantidadRestante = (int) $rs['Cantidad'] - (int) $rs['CantidadSurtida'];
                        echo "<td align='center' scope='row' style='font-size:10px'>
                        <input type='text' name='cantidadRestante$contadorFila' id='cantidadRestante$contadorFila' size='2' value='$cantidadRestante' style='width: 20px;' maxlength='3'/><br/><div id='errorCantidad" . $contadorFila . "'></div>
                        </td>";
                        echo "<td align='center' scope='row' style='font-size:10px'>";
                        //$contadorFila++;
                        ?>
                        <select style='width: 100px' id="almacen<?php echo $contadorFila ?>" onchange="mostrarExistencias('<?php echo $contadorFila ?>', 'cambiarComponente_<?php echo $contadorFila; ?>');" name="almacen<?php echo $contadorFila ?>">
                            <option value="0">Seleccione un almacén</option>
                            <?php
                            if ($ticket_abierto) {
                                $c = 0;
                                while ($c < count($idAlmacen)) {
                                    $s = "";
                                    if ($almacenPredeterminado != "" && $almacenPredeterminado == $idAlmacen[$c])
                                        $s = "selected";
                                    echo"<option value='" . $idAlmacen[$c] . "' " . $s . ">" . $nombreAlmacen[$c] . "</option>    ";
                                    $c++;
                                }
                            }
                            ?>
                        </select>
                        <?php
                        echo "<br/><div id='errorAlmacen$contadorFila'></div></td>";
                        $cantidadALamcenExistente = "";
                        if ($rs['cantidadExistente'] == "" || $rs['cantidadExistente'] == "0")
                            $cantidadALamcenExistente = 0;
                        else
                            $cantidadALamcenExistente = $rs['cantidadExistente'];
                        echo "<td align='center' scope='row' style='font-size:10px'><div id='cantidadExistente$contadorFila'>$cantidadALamcenExistente</div><input type='hidden' value='$cantidadALamcenExistente' id='cantidadExix$contadorFila'  name=id='cantidadExix$contadorFila' /></td>";
                        echo "<td>
                           <select id='estatus_" . $contadorFila . "' name='estatus_" . $contadorFila . "' style='width: 100px' >
                           <option value='0'>Seleccione un estado</option>";
                        if ($ticket_abierto) {
                            $c1 = 0;
                            while ($c1 < count($idestatus)) {
                                $s = "";
                                $idEstdoSelect = "";
                                if ($cantidadALamcenExistente == 0)
                                    $idEstdoSelect = "20";
                                else
                                    $idEstdoSelect = "21";
                                if ($idEstdoSelect == $idestatus[$c1])
                                    $s = "selected";
                                echo"<option value='" . $idestatus[$c1] . "' " . $s . ">" . $nombreEstatus[$c1] . "</option>    ";
                                $c1++;
                            }
                        }
                        echo" </select>
                            <br/><div id='errorEstado" . $contadorFila . "'></div>
                           </td>";
                        echo "<td align='center' scope='row'>";
                        ?>
                        <?php
                        if ($permisos_grid->getModificar() && $ticket_abierto) {
                            ?>
                            <input type="button" class="boton" id="guardar_refaccion_<?php echo $contadorFila; ?>" value="Guardar" onclick="CambiarEstatusRefaccion('<?php echo $contadorFila ?>', '<?php echo $rs['Cantidad'] ?>',
                                                        '<?php echo $rs['IdTicket'] ?>', '<?php echo $rs['IdEstatusAtencion'] ?>', '<?php echo $rs['Activo'] ?>', '<?php echo $rs['UsuarioSolicitud'] ?>',
                                                        '<?php echo $rs['MostrarCliente'] ?>', '<?php echo $rs['IdNotaTicket'] ?>', '<?php echo $rs['NoParte'] ?>', '<?php echo $rs['Cantidad'] ?>',
                                                        '<?php echo $rs['IdEstatusCobranza'] ?>', true);"/> 
                               <?php } ?>
                               <?php
                               echo "</td>";
                               echo "<td>";
                               if ($ticket_abierto) {
                                   echo "<input type='checkbox' id='check_guardar_$contadorFila' name='check_guardar_$contadorFila' value='$contadorFila'/>";
                               } else {
                                   echo "Ticket cerrado";
                               }
                               echo "<input type='hidden' id='id_ticket_$contadorFila' name='id_ticket_$contadorFila' value='" . $rs['IdTicket'] . "'/>
                                <input type='hidden' id='id_estatus_$contadorFila' name='id_estatus_$contadorFila' value='" . $rs['IdEstatusAtencion'] . "'/>
                                <input type='hidden' id='activo_$contadorFila' name='activo_$contadorFila' value='" . $rs['Activo'] . "'/>
                                <input type='hidden' id='usuarios_$contadorFila' name='usuarios_$contadorFila' value='" . $rs['UsuarioSolicitud'] . "'/>
                                <input type='hidden' id='mostrar_$contadorFila' name='mostrar_$contadorFila' value='" . $rs['MostrarCliente'] . "'/>
                                <input type='hidden' id='nota_$contadorFila' name='nota_$contadorFila' value='" . $rs['IdNotaTicket'] . "'/>
                                <input type='hidden' id='parte_$contadorFila' name='parte_$contadorFila' value='" . $rs['NoParte'] . "'/>
                                <input type='hidden' id='cantidad_$contadorFila' name='cantidad_$contadorFila' value='" . $rs['Cantidad'] . "'/>
                                <input type='hidden' id='estatus_cobranza_$contadorFila' name='estatus_cobranza_$contadorFila' value='" . $rs['IdEstatusCobranza'] . "'/>";
                               echo "</td>";
                               ?>
                        <td align='center' scope='row'> 
                            <?php
                            if ($booleanFecha) {
                                ?>
                                <a href='#' onclick='detalleReporte("reportes/reporte_ticket.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['AreaAtencion']; ?>", "0");
                                                    return false;' title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>
                               <?php } else { ?>
                                <a href='<?php echo $_SESSION['liga']; ?>/operacion/MesaServicio/ReporteTicket.aspx?IdTicket=<?php echo $rs['IdTicket']; ?>&uguid=<?php echo $_SESSION['user']; ?>' target="_blank" title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>
                                <?php
                            }
                            ?>                                                                      
                        </td>
                        <?php
                        echo "</tr>";
                        $contadorFila++;
                    }
                    ?>
                    </tbody>
                    <input type="hidden" id="cantidad_solicitadas" name="cantidad_solicitadas" value="<?php echo $contadorFila; ?>"/>
                    <input type="hidden" id="color_hidden" name="color_hidden" value="<?php echo $colorPOST; ?>"/>
                </table>
                <br/><br/>
                <h1>Env&iacute;o de refacciones</h1>
                <?php
                if ($permisos_grid->getModificar()) {
                    ?>
                    <input type="button" id="enviar" name="enviar" onclick="picking();
                                    return false;" class="boton" style="float: right;" value="Entregar"/>
                       <?php } ?>
                <br/><br/>
                <a href="#" id="liga_envio" onclick="mostrarEnviados();
                            return false;">Mostrar env&iacute;os</a>
                <div style="display: inline; float: right;"><input type="checkbox" id="slc_todo_enviados" onclick="seleccionarTodosEnviadosRef();"/>
                    <div id="mensaje_sel_env" style="display: inline; ">Seleccionar todo</div></div>
                <br/><br/>
                <table id="tAlmacen2" class="tabla_grid" style="display: none; width: 100%;">            
                    <thead>
                        <tr>
                            <?php
                            $cabeceras = array("Ticket", "Cliente", "Falla", "Refecciones", "Surtidas", "", "Ubicacion en almacén", "Área de atención", "", "", "");
                            for ($i = 0; $i < (count($cabeceras)); $i++) {
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                            }
                            ?>                                                                      
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($idTicket == "") {
                            /* $consulta = "SELECT t.IdTicket,nt.IdNotaTicket,c.Modelo,cl.NombreRazonSocial,nr.CantidadSurtida,(SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area,DATE(t.FechaHora) AS FechaHora
                              ,CONCAT ('(',nr.Cantidad,') ',c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS refaccion,DATEDIFF(NOW(), t.FechaHora) AS diferencia,nt.IdEstatusAtencion
                              ,t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza,t.TipoReporte,e.Nombre AS ultimoEstatus,nt.DiagnosticoSol,t.NombreCentroCosto,t.DescripcionReporte,nr.Cantidad,c.NoParte,nt.MostrarCliente,nt.Activo,nt.UsuarioSolicitud,nr.IdAlmacen,cl.ClaveCliente,t.NoSerieEquipo,ClaveCentroCosto,
                              (SELECT e2.Nombre FROM c_estado e2 WHERE e2.IdEstado=t.AreaAtencion)AS areaAtencion, kac.Ubicacion
                              FROM k_nota_refaccion nr,c_notaticket nt,c_ticket t,c_componente c,c_cliente cl,c_estado e,k_almacencomponente kac
                              WHERE t.IdTicket=nt.IdTicket AND nt.IdNotaTicket=nr.IdNotaTicket AND nr.NoParteComponente=c.NoParte AND t.ClaveCliente=cl.ClaveCliente
                              AND $cliente nt.IdEstatusAtencion=e.IdEstado AND (kac.id_almacen = nr.IdAlmacen AND kac.NoParte = c.NoParte)
                              AND nt.IdEstatusAtencion=21
                              AND nr.Cantidad<>0 AND c.IdTipoComponente=1 $whereCerrado $whereMoroso $whereAreaAtencion $whereEnviados
                              ORDER BY nt.IdNotaTicket,c.Modelo ASC"; */
                            $consulta = "SELECT t.IdTicket,nt.IdNotaTicket,c.Modelo,cl.NombreRazonSocial,nr.CantidadSurtida,(SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area,DATE(t.FechaHora) AS FechaHora ,CONCAT ('(',nr.Cantidad,') ',c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS refaccion,DATEDIFF(NOW(), t.FechaHora) AS diferencia,nt.IdEstatusAtencion ,t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza,t.TipoReporte,e.Nombre AS ultimoEstatus,nt.DiagnosticoSol,t.NombreCentroCosto,t.DescripcionReporte,nr.Cantidad,c.NoParte,nt.MostrarCliente,nt.Activo,nt.UsuarioSolicitud,nr.IdAlmacen,cl.ClaveCliente,t.NoSerieEquipo,ClaveCentroCosto, 
                                (CASE WHEN !ISNULL(ar.IdArea) THEN ar.Descripcion ELSE ar2.Descripcion END) AS areaAtencion, 
                                (CASE WHEN !ISNULL(ar.IdArea) THEN ar.IdArea ELSE ar2.IdArea END) AS idArea, 
                                kac.Ubicacion 
                                FROM k_nota_refaccion nr 
                                LEFT JOIN c_notaticket nt ON nt.IdNotaTicket=nr.IdNotaTicket
                                LEFT JOIN c_ticket t ON t.IdTicket=nt.IdTicket
                                LEFT JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion 
                                LEFT JOIN c_area AS ar2 ON ar2.IdArea = e2.IdArea
                                LEFT JOIN c_componente c ON nr.NoParteComponente=c.NoParte
                                LEFT JOIN c_cliente cl ON t.ClaveCliente=cl.ClaveCliente
                                LEFT JOIN c_estado e ON nt.IdEstatusAtencion=e.IdEstado
                                LEFT JOIN k_almacencomponente kac ON (kac.id_almacen = nr.IdAlmacen AND kac.NoParte = c.NoParte) 
                                LEFT JOIN c_notaticket AS nt2 ON nt2.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket) 
                                LEFT JOIN c_estado AS e3 ON nt2.IdEstatusAtencion = e3.IdEstado 
                                LEFT JOIN c_area AS ar ON ar.IdArea = e3.IdArea
                                WHERE  nt.IdEstatusAtencion=21 AND nr.Cantidad<>0 AND c.IdTipoComponente=1  
                                $whereCerrado $whereMoroso $whereAreaAtencion $whereEnviados $having
                                ORDER BY nt.IdNotaTicket,c.Modelo ASC;";
                        } else {
                            $consulta = "SELECT t.IdTicket,nt.IdNotaTicket,c.Modelo,cl.NombreRazonSocial,nr.CantidadSurtida,(SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area,DATE(t.FechaHora) AS FechaHora
                                ,CONCAT ('(',nr.Cantidad,') ',c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS refaccion,DATEDIFF(NOW(), t.FechaHora) AS diferencia,nt.IdEstatusAtencion
                                ,t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza,t.TipoReporte,e.Nombre AS ultimoEstatus,nt.DiagnosticoSol,t.NombreCentroCosto,t.DescripcionReporte,nr.Cantidad,c.NoParte,nt.MostrarCliente,nt.Activo,nt.UsuarioSolicitud,nr.IdAlmacen,cl.ClaveCliente ,t.NoSerieEquipo,ClaveCentroCosto,
                                 (CASE WHEN !ISNULL(ar.IdArea) THEN ar.Descripcion ELSE ar2.Descripcion END) AS areaAtencion, 
                                (CASE WHEN !ISNULL(ar.IdArea) THEN ar.IdArea ELSE ar2.IdArea END) AS idArea,  kac.Ubicacion
                                FROM k_nota_refaccion nr
                                LEFT JOIN c_notaticket nt ON nt.IdNotaTicket=nr.IdNotaTicket 
                                LEFT JOIN c_ticket t ON t.IdTicket=nt.IdTicket 
                                LEFT JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion 
                                LEFT JOIN c_area AS ar2 ON ar2.IdArea = e2.IdArea
                                LEFT JOIN c_componente c ON nr.NoParteComponente=c.NoParte 
                                LEFT JOIN c_cliente cl ON t.ClaveCliente=cl.ClaveCliente 
                                LEFT JOIN c_estado e ON nt.IdEstatusAtencion=e.IdEstado 
                                LEFT JOIN k_almacencomponente kac ON (kac.id_almacen = nr.IdAlmacen AND kac.NoParte = c.NoParte)
                                LEFT JOIN c_notaticket AS nt2 ON nt2.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket) 
                                LEFT JOIN c_estado AS e3 ON nt2.IdEstatusAtencion = e3.IdEstado 
                                LEFT JOIN c_area AS ar ON ar.IdArea = e3.IdArea
                                WHERE t.IdTicket = $idTicket $cliente
                                AND nt.IdEstatusAtencion=21
                                AND nr.Cantidad<>0 AND c.IdTipoComponente=1
                                ORDER BY nt.IdNotaTicket,c.Modelo ASC;";
                        }
                        //echo $consulta;
                        $contador = 0;
                        $query = $catalogo->obtenerLista($consulta);
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
                            /*                             * ********************    Obtenemos el color de la fila   ******************************** */
                            $color = "#F7F7DE";
                            $cerrado = false;
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
                                } else {
                                    $cerrado = true;
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
                                } else {
                                    $cerrado = true;
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
                            echo "<td align='center' scope='row'><a href='#' title='Filtar ticket " . $rs['IdTicket'] . "'
                            onclick='BuscarRefaccionesByTicket(\"" . $rs['IdTicket'] . "\"); return false;'>" . $rs['IdTicket'] . "</a></td>";
                            echo "<td align='center' scope='row' style='font-size:10px'>" . $rs['NombreRazonSocial'] . " - " . $rs['NombreCentroCosto'] . "</td>";
                            echo "<td align='center' scope='row' style='font-size:10px'>" . $rs['DescripcionReporte'] . "</td>";
                            echo "<td align='center' scope='row' style='font-size:10px'>" . $rs['areaAtencion'] . "</td>";
                            echo "<td align='center' scope='row' style='font-size:10px'>" . $rs['refaccion'] . "</td>";
                            echo "<td align='center' scope='row' style='font-size:10px'>" . $rs['CantidadSurtida'] . "</td>";
                            echo "<td align='center' scope='row' style='font-size:10px'>" . $rs['Ubicacion'] . "</td>";
                            ?>
                        <td align='center' scope='row'>
                            <?php if ($booleanFecha) { ?>
                                <a href='#' onclick='detalleTicketAlmacen("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "1");
                                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a> 
                               <?php } else {
                                   ?>
                                <a href='#' onclick='lanzarPopUp("Detalle", "hardware/alta_ticket_detalle_hw.php?tipo=<?php echo $rs['area'] . "&id=" . $rs['IdTicket']; ?>");
                                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                               <?php }
                               ?>

                        </td>
                        <td align='center' scope='row'>
                            <a href='#' onclick='lanzarPopUp("Ticket <?php echo $rs['IdTicket']; ?>", "almacen/lista_crearReporteNota.php?ticket=<?php echo $rs['IdTicket']; ?>");
                                            return false;' title='Generar reporte' ><img src="resources/images/icono_impresora.png" width="24" height="24"/></a>

                        </td>
                        <?php if (((int) $rs['Cantidad'] - (int) $rs['CantidadSurtida'] > 0) && !$cerrado) { ?>
                            <td align='center' scope='row' style='font-size:10px'><input type="text" size="2" id="cantidad<?php echo$contador; ?>" name="cantidad<?php echo$contador; ?>" value="<?php echo $rs['Cantidad'] - $rs['CantidadSurtida'] ?>"/><input type="hidden" id="maximoPeticion<?php echo $contador; ?>" name="maximoPeticion<?php echo $contador; ?>" value="<?php echo $rs['Cantidad'] - $rs['CantidadSurtida'] ?>"/><br/><div id="erroPeticion<?php echo $contador; ?>"></div>
                            <td align='center' scope='row' style='font-size:10px'><input type="checkbox" name="listo<?php echo $contador; ?>" id="listo<?php echo $contador; ?>" value="<?php echo $contador; ?>"</td>
                            <?php
                        } else {
                            echo " <td align='center' scope='row'></td><td align='center' scope='row'></td>";
                        }
                        echo "<input type='hidden' id='ticket$contador' id='ticket$contador' value='" . $rs['IdTicket'] . "'/>";
                        echo "<input type='hidden' id='nota$contador' id='nota$contador' value='" . $rs['IdNotaTicket'] . "'/>";
                        echo "<input type='hidden' id='descripcion$contador' id='descripcion$contador' value='" . $rs['DiagnosticoSol'] . "'/>";
                        echo "<input type='hidden' id='refaccion$contador' id='refaccion$contador' value='" . $rs['NoParte'] . "'/>";
                        echo "<input type='hidden' id='activo$contador' id='activo$contador' value='" . $rs['Activo'] . "'>";
                        echo "<input type='hidden' id='mostrar$contador' id='mostrar$contador' value='" . $rs['MostrarCliente'] . "'/>";
                        echo "<input type='hidden' id='usuarioSolicitud$contador' id='usuarioSolicitud$contador' value='" . $rs['UsuarioSolicitud'] . "'/>";
                        echo "<input type='hidden' id='almacen$contador' id='almacen$contador' value='" . $rs['IdAlmacen'] . "'/>";
                        echo "<input type='hidden' id='cliente$contador' id='cliente$contador' value='" . $rs['ClaveCliente'] . "'/>";
                        echo "<input type='hidden' id='modelo$contador' id='modelo$contador' value='" . $rs['Modelo'] . "'/>";
                        echo "<input type='hidden' id='noSerie$contador' id='noSerie$contador' value='" . $rs['NoSerieEquipo'] . "'/>";
                        echo "<input type='hidden' id='localidad$contador' id='localidad$contador' value='" . $rs['ClaveCentroCosto'] . "'/>";
                    }
                    ?>


                    </td>
                    </tbody>
                    <input type="hidden" id="color_hidden" name="color_hidden" value="<?php echo $colorPOST; ?>"/>
                    <input type="hidden" id="numeroCheck" name="numeroCheck" value="<?php echo $contador; ?>"/>                
                </table>

            <?php } ?>

            <input type="hidden" id="mostrar_grid" name="mostrar_grid" value="<?php echo $mostrarGrid; ?>"/>
        </div>
        <div id="mensajeClienteMoroso" title="Mensaje"></div>
    </body>
</html>