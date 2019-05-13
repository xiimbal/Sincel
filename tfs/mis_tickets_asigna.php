<?php
    session_start();
    
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: index.php");
    }
    
    /*Para mantener los filtros y paginados de la tabla*/
    if(isset($_GET['page']) && isset($_GET['filter'])){        
        $filter = str_replace("_XX__XX_", " ",$_GET['filter']);
        $page = $_GET['page'];
    }else{
        $page = "0";
        $filter = "";
    }    
    include_once("../WEB-INF/Classes/Catalogo.class.php"); 
    include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
    $permisos_grid = new PermisosSubMenu();
    $same_page = "tfs/mis_tickets_asigna.php";
    $permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
    
    $tecnicos = array();
    $catalogo = new Catalogo();
    $query = $catalogo->obtenerLista("SELECT IdUsuario, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS tecnico FROM `c_usuario` WHERE IdPuesto = 21;");
        
    $tecnicos["0"] = "Selecciona al TFS";
    while ($rs = mysql_fetch_array($query)) {
        $tecnicos[$rs['IdUsuario']] = $rs['tecnico'];
    }
    
    $having = " HAVING (IdEstatusAtencion <> 16 AND IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)";
    $cerradoTicket = "t.EstadoDeTicket <> 2 AND ";
    $checked = "";
    $morososTicket = "cl.IdEstatusCobranza <> 2 AND ";    
    $checkedMoroso = "";
    $canceladoTicket = "t.EstadoDeTicket <> 4 AND ";
    $checkedCancelado = "";
    $cliente = "";
    $colorPOST = "";
    $estadoNota = "LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
    
    if(isset($_POST['cerrado']) && $_POST['cerrado']!="false"){
        $cerradoTicket = "";
        if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
            $having = "";
        }else{
            $having = " HAVING (IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion) ";
        }
        $checked = "checked='checked'";        
    }else{
        if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
            $having = " HAVING (IdEstatusAtencion <> 16) OR ISNULL(IdEstatusAtencion) ";
        }
    }
    
    if(isset($_POST['moroso']) && $_POST['moroso']!="false"){
        $morososTicket = "";
        $checkedMoroso = "checked='checked'";        
    }
    
    if(isset($_POST['cancelado']) && $_POST['cancelado']!="false"){
        $canceladoTicket = "";
        $checkedCancelado = "checked='checked'";        
    }
    
    if(isset($_POST['cliente']) && $_POST['cliente']){
        $cliente = "t.ClaveCliente = '".$_POST['cliente']."' AND ";
    }
    
    if(isset($_POST['color'])){
        $colorPOST = $_POST['color'];
    }       
    
    if(isset($_POST['estado']) && $_POST['estado']!=""){
        $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdEstatusAtencion = ".$_POST['estado']." AND nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
        if ($_POST['estado'] == "16") {/* Si se selecciona el estado de cerrado, habiliatar el checkbox de cerrado también */
            $cerradoTicket = "";
            if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
                $having = "";
            }else{
                $having = " HAVING (IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion) ";
            }
            $checked = "checked='checked'";
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <!--easyui-->
        <script type="text/javascript" src="resources/js/jquery.easyui.min.js"></script>
        <link rel="stylesheet" type="text/css" href="resources/css/arbol/easyui.css">
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_ticket.js"></script>
        <style>
            .circle {
                border-radius: 50%/50%; 
                width: 30px;
                height: 30px;        
            }
        </style>
    </head>
    <body>
        <div class="principal">            
            <br/><br/>
            <table style="width: 100%;">
                <tr>
                    <td><!--Ticket--></td>
                    <td><!--<input type="text" id="busqueda_ticket" name="tfs/mis_tickets_asigna.php"/>--></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>                    
                    <td><input type="checkbox" id="ticket_cerrado" name="ticket_cerrado" <?php echo $checked; ?> onchange="recargarListaTicket('tfs/mis_tickets_asigna.php','ticket_cerrado','cliente_ticket','ticket_color','estado_ticket','ticket_moroso','ticket_cancelado');"/>Mostrar cerrados</td>
                </tr>
                <tr>                    
                    <td>Cliente</td>
                    <td>
                        <select id="cliente_ticket" name="cliente_ticket" style="width: 200px;" onchange="recargarListaTicket('tfs/mis_tickets_asigna.php','ticket_cerrado','cliente_ticket','ticket_color','estado_ticket','ticket_moroso','ticket_cancelado');">
                           <?php
                                /* Inicializamos la clase */                    
                                $catalogo = new Catalogo();
                                $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                                echo "<option value=''>Todos los clientes</option>";
                                while($rs = mysql_fetch_array($query)){
                                    $s = "";
                                    if(isset($_POST['cliente']) && $_POST['cliente'] == $rs['ClaveCliente']){
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='".$rs['ClaveCliente']."' $s>".$rs['NombreRazonSocial']."</option>";
                                }
                           ?> 
                        </select>
                    </td>
                    <td>Estado</td>
                    <td>
                        <select id="estado_ticket" name="estado_ticket" style="width: 200px;" onchange="recargarListaTicket('tfs/mis_tickets_asigna.php','ticket_cerrado','cliente_ticket','ticket_color','estado_ticket','ticket_moroso','ticket_cancelado');">
                           <?php
                                /* Inicializamos la clase */                                                    
                                $query = $catalogo->getListaAlta("c_estado", "Nombre");
                                echo "<option value=''>Todos los estados</option>";
                                while($rs = mysql_fetch_array($query)){
                                    $s = "";
                                    if(isset($_POST['estado']) && $_POST['estado'] == $rs['IdEstado']){
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='".$rs['IdEstado']."' $s>".$rs['Nombre']."</option>";
                                }
                           ?> 
                        </select>
                    </td>
                    <td>Color</td>
                    <td>
                        <select id="ticket_color" name="ticket_color" style="width: 200px;" onchange="recargarListaTicket('tfs/mis_tickets_asigna.php','ticket_cerrado','cliente_ticket','ticket_color','estado_ticket','ticket_moroso','ticket_cancelado');">
                            <option value="">Todos</option>
                            <option value="rojo" style="background: #DC381F;">Urgente</option>
                            <option value="amarillo" style="background: #FFF380;">Importante</option>
                            <option value="verde" style="background: #F7F7DE;">Normal</option>
                        </select>                        
                    </td>
                    <td><input type="checkbox" id="ticket_moroso" name="ticket_moroso" <?php echo $checkedMoroso; ?> onchange="recargarListaTicket('tfs/mis_tickets_asigna.php','ticket_cerrado','cliente_ticket','ticket_color','estado_ticket','ticket_moroso','ticket_cancelado');"/>Mostrar morosos</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><input type="checkbox" id="ticket_cancelado" name="ticket_cancelado" <?php echo $checkedCancelado; ?> onchange="recargarListaTicket('tfs/mis_tickets_asigna.php','ticket_cerrado','cliente_ticket','ticket_color','estado_ticket','ticket_moroso','ticket_cancelado');"/>Mostrar cancelados</td>
                </tr>
            </table>            
            <br/><br/>
            <table id="tAlmacen">
                <thead>
                    <tr>
                        <?php                        
                        $cabeceras = array("Ticket","Fecha","Cliente","Falla","Último estatus ticket","Última Nota","Fecha nota","","TFS","");
                        for($i=0; $i<(count($cabeceras)); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">".$cabeceras[$i]."</th>";
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
                    INNER JOIN c_cliente AS cl ON $morososTicket $cliente cl.ClaveCliente = t.ClaveCliente
                    LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
                    INNER JOIN c_estado AS e2 ON e2.IdEstado = 7 AND e2.IdEstado = t.AreaAtencion
                    LEFT JOIN c_ubicacionticket AS u ON u.IdUbicacion = t.Ubicacion
                    $estadoNota
                    LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
                    WHERE k_tecnicoticket.IdTicket IS NULL $having 
                    ORDER BY IdTicket;";
                    //echo $consulta;
                    $query = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query)) {/*Recorremos todos los tickets resultantes del query*/
                        if($checked == "" && ($rs['IdEstatusAtencion']=="16" || $rs['IdEstatusAtencion'] == "59")){/*Si ya esta cerrado por nota, saltamos*/
                            continue;
                        }
                        /***********************    Obtenemos el color de la fila   *********************************/                        
                        $color = "#F7F7DE";                                                
                        
                        if(isset($rs['IdEstatusAtencion'])){/*Si hay estado de la ultima nota*/
                            if($rs['IdEstatusAtencion']!= "16" && (isset($rs['estadoTicket']) && $rs['estadoTicket'] != "2")){/*Si el ticket no esta cerrado*/
                                if(strtoupper($rs['tipoCliente']) == "1"){/*Si el cliente es VIP*/                                                                        
                                    if(number_format($rs['diferencia']) >= 2){/*Si ya van mas de 2 dias que se levanto el ticket*/
                                        if($colorPOST!="" && $colorPOST!="rojo"){
                                            continue;
                                        }
                                        $color = "#DC381F";                                        
                                    }else{
                                        if( $colorPOST!="" && $colorPOST!="amarillo"){
                                            continue;
                                        }
                                        $color = "#FFF380";                                        
                                    }
                                }else{/*Si no es cliente VIP*/
                                    if(number_format($rs['diferencia']) >= 7){/*Si ya van mas de 7 dias que se levanto el ticket*/
                                        if( $colorPOST!="" && $colorPOST!="rojo"){
                                            continue;
                                        }
                                        $color = "#DC381F";
                                    }
                                }
                            }
                        }else{/*Si no hay notas, vemos el estado del ticket*/
                            if($rs['estadoTicket'] != "2"){/*Si el ticket no esta cerrado*/
                                if(strtoupper($rs['tipoCliente']) == "1"){/*Si el cliente es VIP*/
                                    if(number_format($rs['diferencia']) >= 2){/*Si ya van mas de 2 dias que se levanto el ticket*/
                                        if( $colorPOST!="" && $colorPOST!="rojo"){
                                            continue;
                                        }
                                        $color = "#DC381F";                                        
                                    }else{
                                        if( $colorPOST!="" && $colorPOST!="amarillo"){
                                            continue;
                                        }
                                        $color = "#FFF380";                                        
                                    }
                                }else{/*Si no es cliente VIP*/
                                    if(number_format($rs['diferencia']) >= 7){/*Si ya van mas de 7 dias que se levanto el ticket*/
                                        if( $colorPOST!="" && $colorPOST!="rojo"){
                                            continue;
                                        }
                                        $color = "#DC381F";                                        
                                    }
                                }
                            }
                        }                                                
                        
                        /*En dado caso que se un ticekt verde pero en el filtro se selecciono otro color*/
                        if($color=="#F7F7DE" && ($colorPOST!="verde" && $colorPOST!="")){
                            continue;
                        }
                        
                        if($rs['IdEstatusCobranza'] == "2"){/*Cliente moroso*/
                            $color = "#D462FF";
                        }
                        
                        if($rs['estadoTicket'] == "4"){/*Ticket cancelado*/
                            $color = "#D1D0CE";
                        }
                        
                        if($rs['TipoReporte'] == "26"){/*Si es Mtto preventivo*/
                            $color = "#00FFFF";
                        }
                        
                        echo "<tr style='background-color: $color; color:black;'>";
                        echo "<td align='center' scope='row'>" .$rs['IdTicket']. "</td>";
                        echo "<td align='center' scope='row'>" .$rs['FechaHora']. "</td>";                        
                        echo "<td align='center' scope='row'>" .$rs['NombreCliente']. " - ".$rs['NombreCentroCosto']."</td>";                        
                        echo "<td align='center' scope='row'>" .$rs['DescripcionReporte']. "</td>";
                        
                        if(isset($rs['estadoNota'])){
                            echo "<td align='center' scope='row'>" .$rs['estadoNota']. "</td>";
                        }else{
                            echo "<td align='center' scope='row'></td>";
                        }
                        if(isset($rs['DiagnosticoSol'])){
                            echo "<td align='center' scope='row'>" .$rs['DiagnosticoSol']. "</td>";
                        }else{
                            echo "<td align='center' scope='row'></td>";
                        }
                        if(isset($rs['FechaNota'])){
                            echo "<td align='center' scope='row'>" .$rs['FechaNota']. "</td>";
                        }else{
                            echo "<td align='center' scope='row'></td>";
                        }                        
                        $fecha_limite = strtotime("2014-03-31");
                        $fecha_ticket = strtotime($rs['FechaHora']);
                        if ($fecha_ticket >= $fecha_limite) {
                            $nuevo = true;
                        }else{
                            $nuevo = false;
                        }   
                        if ($rs['idArea'] == "2") {
                            $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketToner.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Detalle&uguid=" . $_SESSION['user'];
                        } else {
                            $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketFalla.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Detalle&uguid=" . $_SESSION['user'];
                        }
                        //$areaCodificada = str_replace(" ","&&__&&",$rs['area']);
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
                            echo "<select id='tecnico_".$rs['IdTicket']."' name='tecnico_".$rs['IdTicket']."'>";
                            foreach ($tecnicos as $key => $value){
                                echo "<option value=" . $key . ">" .$value . "</option>";
                            }
                            echo "</select>"; 
                        }
                        echo "<div id='error_tecnico_".$rs['IdTicket']."' style='color:red; display:none;'>Selecciona algún técnico</div></td>";
                        echo "<td>"; 
                        if($permisos_grid->getModificar()){
                            echo "<button class='boton' id='boton_".$rs['IdTicket']."' name='boton_".$rs['IdTicket']."' onclick='relacionarTecnico(\"".$rs['IdTicket']."\",\"tecnico_".$rs['IdTicket']."\",\"3\"  ); return false;'>Guardar</button>"; 
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
        </div>
    </body>
</html>