<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "hardware/lista_validarRefaccion.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

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
$whereCerrado = "AND (t.EstadoDeTicket<>2 AND t.EstadoDeTicket<>4)";
$whereMoroso = "AND cc.IdEstatusCobranza=1";
$whereAreaAtencion = "";
$areaAtencion = "";
$having = " AND (ISNULL(Validada) OR NumValidada < NumSolicitada OR isValidada = 0) ";

if (isset($_POST["areaAtencion"]) && $_POST["areaAtencion"] != "0") {
    //$whereAreaAtencion = "AND t.AreaAtencion=" . $_POST["areaAtencion"];    
    $having .= " AND idArea = " . $_POST['areaAtencion'] . " ";
    $areaAtencion = $_POST["areaAtencion"];
}

if (isset($_POST["ticket"]) && $_POST["ticket"] != "") {
    $idTicket = $_POST["ticket"];
    $cerrados = "checked";
    $cancelados = "checked";
    $moroso = "checked";
    $having = "";
}
if (isset($_POST["cliente"]) && $_POST["cliente"] != "")
    $cliente = "t.ClaveCliente = '" . $_POST['cliente'] . "' AND ";

if (isset($_POST["color"]) && $_POST["color"] != "")
    $colorPOST = $_POST["color"];

if (isset($_POST["cerrado"]) && $_POST["cerrado"] == "1") {
    $cerrados = "checked";
    $having = "";
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
    $whereMoroso = " ";
}
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
                            <!--<option value="rojo" style="background: #DC381F;">Urgente</option>-->
                            <option value="amarillo" style="background: #FFF380;">Importante</option>
                            <option value="verde" style="background: #F7F7DE;">Normal</option>
                        </select>                        
                    </td>
                    <td></td>
                    <td><input type="checkbox" name="verCancelado" id="verCancelado" <?php echo $cancelados ?>/>Ver cancelados</td>
                </tr>
                <tr>
                    <td><input type="Button" id="boton_aceptar" name="boton_aceptar" value="Buscar ticket" class="boton" onclick="BuscarTicket('hardware/lista_validarRefaccion.php');"/></td>
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
                        $cabeceras = array("Ticket", "Usuario", "Serie", "Modelo", "Fecha", "Cliente", "Falla", "Última Nota", "Fecha nota", "",
                            "Área de atención", "Refacciones", "");
                        for ($i = 0; $i < (count($cabeceras)); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        ?>                                                                      
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($idTicket == "") {
                        $consulta = "SELECT nt.IdNotaTicket,t.IdTicket,t.NoSerieEquipo,t.ModeloEquipo, t.FechaHora,nr.Validada AS isValidada,
                            ((5 * (DATEDIFF(NOW(), t.FechaHora) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(t.FechaHora) + WEEKDAY(NOW()) + 1, 1))) AS diasCreacionNota,
                           ((5 * (DATEDIFF(NOW(), nt.FechaHora) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(nt.FechaHora) + WEEKDAY(NOW()) + 1, 1))) AS diasUltimaNota, 
                           DATE_FORMAT(NOW(), '%H') AS ahora,
                            DATE_FORMAT(t.FechaHora,'%H') AS horaTicket,
                            DATE_FORMAT(nt.FechaHora, '%H') AS horaUltimaModificacion,
                            CONCAT_WS(' ',u.Nombre,u.ApellidoMaterno,u.ApellidoPaterno) AS NombreUsuario,
                            GROUP_CONCAT( DISTINCT (CONVERT(nr.Validada, CHAR(8))) ) AS isValidadaConcatenada,                         
                            
                            (SELECT SUM(k_nota_refaccion.Cantidad) FROM k_nota_refaccion 
                            INNER JOIN c_notaticket ON k_nota_refaccion.IdNotaTicket = c_notaticket.IdNotaTicket 
                            WHERE c_notaticket.IdTicket = t.IdTicket AND (c_notaticket.IdEstatusAtencion = 21 OR c_notaticket.IdEstatusAtencion = 20)
                            AND k_nota_refaccion.NoParteComponente IN(SELECT (NoParteComponente) FROM k_nota_refaccion WHERE IdNotaTicket = nr.IdNotaTicket)) AS CantidadListaEntregar,		
                            (SELECT SUM(nr.Cantidad) AS Solicitada) AS CantidadSolicitadaNota,
                           cc.NombreRazonSocial,t.DescripcionReporte,nt.DiagnosticoSol,DATE(t.FechaHora) AS FechaHora,
                           nt.FechaHora AS fechaNota, group_concat('(',Cantidad,') ',Modelo separator ', ')AS refacciones, DATEDIFF(NOW(), t.FechaHora) AS diferencia,
                           nt.IdEstatusAtencion,nt.UsuarioCreacion, cc.IdEstatusCobranza,t.EstadoDeTicket AS estadoTicket,cc.IdTipoCliente,t.TipoReporte,t.NombreCentroCosto ,
                           (SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion) AS area,
                           (CASE WHEN !ISNULL(ar.IdArea) THEN ar.IdArea ELSE ar2.IdArea END) AS idArea, 
                           nt.UsuarioSolicitud, (CASE WHEN !ISNULL(ar.IdArea) THEN ar.Descripcion ELSE ar2.Descripcion END) AS areaAtencion, 
                           
                           (SELECT SUM(knr3.Cantidad) AS Solicitada FROM c_ticket AS t2
                           LEFT JOIN c_notaticket AS nt2 ON nt2.IdTicket = t2.IdTicket
                           LEFT JOIN k_nota_refaccion AS knr3 ON knr3.IdNotaTicket = nt2.IdNotaTicket
                           WHERE t2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = 9) AS Solicitada,
                           
                           (SELECT COUNT(knr3.IdNotaTicket) AS Solicitada FROM c_ticket AS t2
                           LEFT JOIN c_notaticket AS nt2 ON nt2.IdTicket = t2.IdTicket
                           LEFT JOIN k_nota_refaccion AS knr3 ON knr3.IdNotaTicket = nt2.IdNotaTicket
                           WHERE t2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = 9) AS NumSolicitada,

                          (SELECT COUNT(knr4.IdNotaTicket)
                           FROM c_ticket AS t2
                           LEFT JOIN c_notaticket AS nt2 ON nt2.IdTicket = t2.IdTicket
                           LEFT JOIN k_nota_refaccion AS knr4 ON knr4.IdNotaTicket = nt2.IdNotaTicket
                           WHERE t2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = 24) AS NumValidada,

                           (SELECT (CASE WHEN (NumSolicitada = NumValidada) THEN Solicitada ELSE SUM(knr4.Cantidad) END)
                           
                           FROM c_ticket AS t2
                           LEFT JOIN c_notaticket AS nt2 ON nt2.IdTicket = t2.IdTicket                           
                           LEFT JOIN k_nota_refaccion AS knr4 ON knr4.IdNotaTicket = nt2.IdNotaTicket
                           WHERE t2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = 24) AS Validada
                           FROM `c_ticket` AS t
                           LEFT JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket
                           LEFT JOIN c_usuario AS u ON u.Loggin = nt.UsuarioSolicitud
                           LEFT JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion 
                           LEFT JOIN c_area AS ar2 ON ar2.IdArea = e2.IdArea
                           LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
                           LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
                           LEFT JOIN c_cliente AS cc ON cc.ClaveCliente = t.ClaveCliente
                           
                           LEFT JOIN c_notaticket AS nt2 ON nt2.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket) 
                           LEFT JOIN c_estado AS e3 ON nt2.IdEstatusAtencion = e3.IdEstado 
                           LEFT JOIN c_area AS ar ON ar.IdArea = e3.IdArea

                           WHERE $cliente nt.IdEstatusAtencion = 9  AND !ISNULL(Modelo)  $whereCerrado $whereMoroso $whereAreaAtencion 
                            AND nr.NoParteComponente NOT IN(SELECT k_nota_refaccion.NoParteComponente FROM k_nota_refaccion 
                            INNER JOIN c_notaticket ON k_nota_refaccion.IdNotaTicket = c_notaticket.IdNotaTicket
                            WHERE c_notaticket.IdTicket = t.IdTicket AND c_notaticket.IdEstatusAtencion = 24)
                           GROUP BY IdNotaTicket
                           HAVING !ISNULL(Solicitada) $having
                           ORDER BY t.IdTicket DESC;";
                    } else {
                        $consulta = "SELECT nt.IdNotaTicket,t.IdTicket,t.ModeloEquipo, t.NoSerieEquipo,t.FechaHora,cc.NombreRazonSocial,t.DescripcionReporte,nr.Validada AS isValidada,
                            ((5 * (DATEDIFF(NOW(), t.FechaHora) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(t.FechaHora) + WEEKDAY(NOW()) + 1, 1))) AS diasCreacionNota,
                           ((5 * (DATEDIFF(NOW(), nt.FechaHora) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(nt.FechaHora) + WEEKDAY(NOW()) + 1, 1))) AS diasUltimaNota,
                           DATE_FORMAT(NOW(), '%H') AS ahora,
                            DATE_FORMAT(t.FechaHora,'%H') AS horaTicket,
                            DATE_FORMAT(nt.FechaHora, '%H') AS horaUltimaModificacion,
                           CONCAT_WS(' ',u.Nombre,u.ApellidoMaterno,u.ApellidoPaterno) AS NombreUsuario,
                            GROUP_CONCAT( DISTINCT (CONVERT(nr.Validada, CHAR(8))) ) AS isValidadaConcatenada,                            
                            
                           (SELECT SUM(k_nota_refaccion.Cantidad) FROM k_nota_refaccion 
                           INNER JOIN c_notaticket ON k_nota_refaccion.IdNotaTicket = c_notaticket.IdNotaTicket 
                           WHERE c_notaticket.IdTicket = t.IdTicket AND (c_notaticket.IdEstatusAtencion = 21 OR c_notaticket.IdEstatusAtencion = 20)
                           AND k_nota_refaccion.NoParteComponente IN(SELECT (NoParteComponente) FROM k_nota_refaccion WHERE IdNotaTicket = nr.IdNotaTicket)) AS CantidadListaEntregar,		
                           (SELECT SUM(nr.Cantidad) AS Solicitada) AS CantidadSolicitadaNota,

                           nt.DiagnosticoSol,DATE(t.FechaHora) AS FechaHora,
                           nt.FechaHora AS fechaNota, group_concat('(',Cantidad,') ',Modelo separator ', ')AS refacciones, DATEDIFF(NOW(), t.FechaHora) AS diferencia,
                           nt.IdEstatusAtencion,nt.UsuarioCreacion, cc.IdEstatusCobranza,t.EstadoDeTicket AS estadoTicket,cc.IdTipoCliente,t.TipoReporte,t.NombreCentroCosto ,
                           (SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion) AS area,
                           (CASE WHEN !ISNULL(ar.IdArea) THEN ar.IdArea ELSE ar2.IdArea END) AS idArea, 
                           nt.UsuarioSolicitud, (CASE WHEN !ISNULL(ar.IdArea) THEN ar.Descripcion ELSE ar2.Descripcion END) AS areaAtencion, 
                           (SELECT SUM(knr3.Cantidad) AS Solicitada FROM c_ticket AS t2
                           LEFT JOIN c_notaticket AS nt2 ON nt2.IdTicket = t2.IdTicket
                           LEFT JOIN k_nota_refaccion AS knr3 ON knr3.IdNotaTicket = nt2.IdNotaTicket
                           WHERE t2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = 9) AS Solicitada,
                           
                           (SELECT COUNT(knr3.IdNotaTicket) AS Solicitada FROM c_ticket AS t2
                           LEFT JOIN c_notaticket AS nt2 ON nt2.IdTicket = t2.IdTicket
                           LEFT JOIN k_nota_refaccion AS knr3 ON knr3.IdNotaTicket = nt2.IdNotaTicket
                           WHERE t2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = 9) AS NumSolicitada,

                           (SELECT COUNT(knr4.IdNotaTicket)
                           FROM c_ticket AS t2
                           LEFT JOIN c_notaticket AS nt2 ON nt2.IdTicket = t2.IdTicket
                           LEFT JOIN k_nota_refaccion AS knr4 ON knr4.IdNotaTicket = nt2.IdNotaTicket
                           WHERE t2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = 24) AS NumValidada,

                           (SELECT (CASE WHEN (NumSolicitada = NumValidada) THEN Solicitada ELSE SUM(knr4.Cantidad) END)
                           FROM c_ticket AS t2
                           LEFT JOIN c_notaticket AS nt2 ON nt2.IdTicket = t2.IdTicket
                           LEFT JOIN k_nota_refaccion AS knr4 ON knr4.IdNotaTicket = nt2.IdNotaTicket
                           WHERE t2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = 24) AS Validada
                           FROM `c_ticket` AS t
                           LEFT JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket
                           LEFT JOIN c_usuario AS u ON u.Loggin = nt.UsuarioSolicitud
                           LEFT JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion 
                           LEFT JOIN c_area AS ar2 ON ar2.IdArea = e2.IdArea
                           LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
                           LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
                           LEFT JOIN c_cliente AS cc ON cc.ClaveCliente = t.ClaveCliente

                           LEFT JOIN c_notaticket AS nt2 ON nt2.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket) 
                           LEFT JOIN c_estado AS e3 ON nt2.IdEstatusAtencion = e3.IdEstado 
                           LEFT JOIN c_area AS ar ON ar.IdArea = e3.IdArea

                           WHERE nt.IdEstatusAtencion = 9 AND !ISNULL(Modelo) AND t.IdTicket= $idTicket 
                           
                           GROUP BY IdNotaTicket
                           HAVING !ISNULL(Solicitada) $having
                           ORDER BY t.IdTicket DESC;";
                    }
                    //echo $consulta;                               
                    $query = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query)) {/* Recorremos todos los tickets resultantes del query */
                        $booleanFecha = FALSE;
                        $fecha_limite = strtotime("2014-03-31");
                        $fecha_ticket = strtotime($rs['FechaHora']);
                        $diasCreacion = $rs['diasCreacionNota']; 
                        $diasUltimaModificacion = $rs['diasUltimaNota'];
                        $usuarioTicket = $rs['NombreUsuario'];
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
                        if ($fecha_ticket >= $fecha_limite) {
                            $booleanFecha = TRUE;
                        } else {
                            $booleanFecha = FALSE;
                        }
                        $dejar_validar = true;
                        $editar_ticket_validado = false;
                        /**                         * ********************    Obtenemos el color de la fila   ******************************** */
                        $color = "#F7F7DE";

                        if (isset($rs['IdEstatusAtencion'])) {/* Si hay estado de la ultima nota */
                            if ($rs['IdEstatusAtencion'] != "16" && (isset($rs['EstadoDeTicket']) && $rs['EstadoDeTicket'] != "2")) {/* Si el ticket no esta cerrado */
                                if (strtoupper($rs['IdTipoCliente']) == "1") {/* Si el cliente es VIP */
                                    if (number_format($rs['diferencia']) >= 2) {/* Si ya van mas de 2 dias que se levanto el ticket */
                                        if ($colorPOST != "" && $colorPOST != "rojo") {
                                            continue;
                                        }
                                        //$color = "#DC381F";
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
                                        //$color = "#DC381F";
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
                                        //$color = "#DC381F";
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
                                        //$color = "#DC381F";
                                    }
                                }
                            }
                        }

                        if ($rs['NumValidada'] >= $rs['NumSolicitada']) {
                            $dejar_validar = false;
                        }


                        if ($rs['isValidadaConcatenada'] == "0" && $rs['fechaNota'] >= '2015-01-01 00:00:00') {//Si esta marcada como no validad y el ticket es mayor a la actualizacion que se hizo
                            $dejar_validar = true;
                        }

                        /* En dado caso que se un ticket verde pero en el filtro se selecciono otro color */
                        if ($color == "#F7F7DE" && ($colorPOST != "verde" && $colorPOST != "")) {
                            continue;
                        }

                        if ($rs['IdEstatusCobranza'] == "2") {/* Cliente moroso */
                            $color = "#D462FF";
                        }

                        if ( 
                                (!isset($rs['CantidadListaEntregar']) || $rs['CantidadListaEntregar'] < $rs['CantidadSolicitadaNota']) 
                                && !$dejar_validar 
                                && $rs['CantidadSolicitadaNota'] > "0"
                            ) {
                            $editar_ticket_validado = false;
                            $dejar_validar = false;
                        }

                        if ($rs['estadoTicket'] == "2" || $rs['IdEstatusAtencion'] == "16") {/* Ticket cerrado */
                            $color = "#F7F7DE";
                            $dejar_validar = false;
                            $editar_ticket_validado = false;
                        }

                        if ($rs['estadoTicket'] == "4" || $rs['IdEstatusAtencion'] == "59") {/* Ticket cancelado */
                            $color = "#D1D0CE";
                            $dejar_validar = false;
                            $editar_ticket_validado = false;
                        }

                        if ($rs['TipoReporte'] == "26") {/* Si es Mtto preventivo */
                            $color = "#00FFFF";
                        }

                        echo "<tr style='background-color: $color; color:black;'>";
                        echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>";
                        echo "<td align='center' scope='row'>" . $usuarioTicket . "</td>";
                        echo "<td align='center' scope='row'><a href='#' title='Ver refacciones solicitadas' 
                            onclick='lanzarHistoricoRefacciones(\"" . $rs['NoSerieEquipo'] . "\"); return false;'>" . $rs['NoSerieEquipo'] . "</a></td>";
                        echo "<td align='center' scope='row'>" . $rs['ModeloEquipo'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['FechaHora'] . " (". $diasCreacion. " días ".$diffT ." horas)"."</td>";
                        echo "<td align='center' scope='row'>" . $rs['NombreRazonSocial'] . " - " . $rs['NombreCentroCosto'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['DescripcionReporte'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['DiagnosticoSol'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['fechaNota'] . " (". $diasUltimaModificacion. " días ".$diffUT ." horas)" . "</td>";
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
                    <?php
                    echo "<td align='center' scope='row'>" . $rs['areaAtencion'] . "</td>";
                    echo "<td align='center' scope='row'>";
                    if ($permisos_grid->getModificar() && ($dejar_validar || $editar_ticket_validado)) {
                        ?>
                        <a href="#" 
                           onclick="editarRefacciones('<?php echo$rs['IdNotaTicket']; ?>', '<?php echo $rs['UsuarioSolicitud']; ?>',
                                                   '<?php
                           if ($editar_ticket_validado) {
                               echo 1;
                           } else {
                               echo 0;
                           }
                           ?>');
                                           return false;"> <?php
                            echo $rs['refacciones'];
                            if ($editar_ticket_validado) {
                                echo " [Ya válidado]";
                            }
                            ?></a>
                            <?php
                        }
                        echo "<div id='error_tecnico_" . $rs['IdTicket'] . "' style='color:red; display:none;'>Selecciona algún técnico</div></td>";
                        ?>
                    <td>
                    <?php if ($permisos_grid->getModificar() && $dejar_validar) { ?>
                            <input type="button" id="valida_<?php echo $rs['IdNotaTicket'] ?>" value ="Validar" onclick="validarNota('hardware/lista_validarRefaccion.php', '<?php echo $rs['IdNotaTicket'] ?>', '1');" class="boton"/>
                    <?php } ?>
                    </td>
    <?php
    echo "</tr>";
}
?>
                </tbody>
                <input type="hidden" id="color_hidden" name="color_hidden" value="<?php echo $colorPOST; ?>"/>
            </table>
        </div>
    </body>
</html>