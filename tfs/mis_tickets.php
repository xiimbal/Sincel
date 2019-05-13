<?php
    session_start();
    
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: index.php");
    }
    
    include_once("../WEB-INF/Classes/Catalogo.class.php");
    include_once("../WEB-INF/Classes/Usuario.class.php");
    include_once("../WEB-INF/Classes/TFSGrupoCliente.class.php");
    include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
    $permisos_grid = new PermisosSubMenu();
    $same_page = "tfs/mis_tickets.php";
    $permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
    
    $tecnicos = array();
    $catalogo = new Catalogo();
    $query = $catalogo->obtenerLista("SELECT IdUsuario, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS tecnico FROM `c_usuario` WHERE IdPuesto = 19 OR IdPuesto = 20;");
        
    $usuario = new Usuario();
    $style = '';
    $idTFS = $_SESSION['idUsuario'];
    
    if($usuario->isUsuarioPuesto($idTFS, 21)){
        $style= "display:none;";
    }
    
    $tecnicos["0"] = "Selecciona al técnico";
    while ($rs = mysql_fetch_array($query)) {
        $tecnicos[$rs['IdUsuario']] = $rs['tecnico'];
    }        
    
    if(isset($_POST['idUsuario'])){
        $idTFS = $_POST['idUsuario'];
    }
?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class="principal">            
            <br/>
            <select id="usuario_tfs" name="usuario_tfs" style="float: right; <?php echo $style; ?>" onchange="recargarTicketConUsuario('tfs/mis_tickets.php',this.value); return false;">                        
                <?php
                    $query = $catalogo->obtenerLista("SELECT IdUsuario, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS tfs FROM `c_usuario` WHERE IdPuesto = 21 AND Activo = 1 ORDER BY tfs;");
                    echo "<option value=''>Selecciona al TFS</option>";
                    while ($rs = mysql_fetch_array($query)) { 
                        $s = "";
                        if($rs['IdUsuario'] == $idTFS){
                            $s = "selected='selected'";
                        }
                        echo "<option value='".$rs['IdUsuario']."' $s>".$rs['tfs']."</option>";
                    }
                ?>
            </select>
            <label for="usuario_tfs" style="float: right; margin-right: 10px; <?php echo $style; ?>">TFS:</label>
            <br/><br/>
            <table id="tAlmacen">
                <thead>
                    <tr>
                        <?php
                        $cabeceras = array("Ticket","Fecha","NoSerie","ClaveCliente Ticket","Cliente","Falla","Último estatus ticket","Última Nota","Fecha nota","","","");
                        for($i=0; $i<(count($cabeceras)); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">".$cabeceras[$i]."</th>";
                        }                        
                        ?>                        
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $TFSGrupo = new TFSGrupoCliente();
                    if($TFSGrupo->tieneGrupo($idTFS)){
                        $consulta = "SELECT
                        b.id_bitacora,
                        t.IdTicket,
                        t.ClaveCliente AS ClaveClienteTicket,
                        t.FechaHora,
                        t.DescripcionReporte,
                        t.NombreCentroCosto,
                        t.TipoReporte,
                        (SELECT CASE WHEN e2.Nombre = 'Suministro' THEN (SELECT group_concat(ClaveEspEquipo SEPARATOR ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
                        DATEDIFF(NOW(), t.FechaHora) AS diferencia,
                        t.NombreCliente,
                        e.Nombre AS estado,
                        e1.Nombre AS tipo,
                        t.ClaveCentroCosto,
                        cl.ClaveCliente,
                        tc.Nombre AS tipoCliente,
                        e2.Nombre AS area,                        
                        nt.IdEstatusAtencion,
                        e.IdEstadoTicket AS estadoTicket,
                        (SELECT CONCAT(ce.Nombre,'**__**',nt2.DiagnosticoSol,'**__**',nt2.FechaHora) FROM c_estado AS ce
                                INNER JOIN c_notaticket AS nt2 ON nt2.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket) AND ce.IdEstado = nt2.IdEstatusAtencion
                        ) AS UltimoEstatus
                        FROM c_ticket AS t
                        INNER JOIN c_estadoticket AS e ON e.IdEstadoTicket = t.EstadoDeTicket AND t.EstadoDeTicket <> 2
                        INNER JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte
                        INNER JOIN c_cliente AS cl ON cl.ClaveCliente = t.ClaveCliente
                        INNER JOIN k_tfsgrupo AS ktg ON ktg.IdTfs = $idTFS AND cl.ClaveGrupo = ktg.ClaveGrupo
                        INNER JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
                        INNER JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion                        
                        LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)
                        LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
                        WHERE ((nt.IdEstatusAtencion <> 16 AND nt.IdEstatusAtencion <> 59) OR ISNULL(nt.IdEstatusAtencion)) AND cl.Suspendido = 0
                        ORDER BY IdTicket;";
                    }else{
                        $consulta = "SELECT
                        b.id_bitacora,
                        t.IdTicket,
                        t.FechaHora,
                        t.ClaveCliente AS ClaveClienteTicket,
                        t.DescripcionReporte,
                        t.NombreCentroCosto,    
                        t.TipoReporte,
                        (SELECT CASE WHEN e2.Nombre = 'Suministro' THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
                        DATEDIFF(NOW(),t.FechaHora) AS diferencia,
                        t.NombreCliente,
                        e.Nombre AS estado,
                        e1.Nombre AS tipo,
                        t.ClaveCentroCosto,
                        cl.ClaveCliente,	
                        tc.Nombre AS tipoCliente,
                        e2.Nombre AS area,   
                        e2.IdEstado AS idArea,
                        nt.IdEstatusAtencion,
                        e.IdEstadoTicket AS estadoTicket,
                        (SELECT CONCAT(ce.Nombre,'**__**',nt2.DiagnosticoSol,'**__**',nt2.FechaHora) FROM c_estado AS ce INNER JOIN c_notaticket AS nt2 ON nt2.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket) AND ce.IdEstado = nt2.IdEstatusAtencion) AS UltimoEstatus
                        FROM c_ticket AS t
                        INNER JOIN c_estadoticket AS e ON e.IdEstadoTicket = t.EstadoDeTicket AND t.EstadoDeTicket <> 2
                        INNER JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte
                        INNER JOIN c_cliente AS cl ON cl.ClaveCliente = t.ClaveCliente
                        INNER JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
                        INNER JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion                        
                        INNER JOIN k_tfscliente AS tfs ON tfs.IdUsuario = ".$idTFS." AND tfs.Tipo = 1 AND tfs.ClaveCliente = t.ClaveCliente
                        LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)
                        LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
                        WHERE ((nt.IdEstatusAtencion <> 16 AND nt.IdEstatusAtencion <> 59) OR ISNULL(nt.IdEstatusAtencion)) AND cl.Suspendido = 0
                        ORDER BY IdTicket;";
                    }
                    $query = $catalogo->obtenerLista($consulta);
                    //echo $consulta;
                    while ($rs = mysql_fetch_array($query)) {                                                
                        /***********************    Obtenemos el color de la fila   *********************************/
                        $datos = explode("**__**", $rs['UltimoEstatus']);
                        $color = "#F7F7DE";                                                
                        if(isset($datos[0]) && isset($datos[1])){/*Si hay estado de la ultima nota*/
                            if(strtoupper($datos[0]) != "CERRADO"){/*Si el ticket no esta cerrado*/
                                if(strtoupper($rs['tipoCliente']) == "VIP"){/*Si el cliente es VIP*/
                                    if(number_format($rs['diferencia']) >= 2){/*Si ya van mas de 7 dias que se levanto el ticket*/
                                        $color = "#DC381F";                                        
                                    }else{
                                        $color = "#FFF380";                                        
                                    }
                                }else{/*Si no es cliente VIP*/
                                    if(number_format($rs['diferencia']) >= 7){/*Si ya van mas de 7 dias que se levanto el ticket*/
                                        $color = "#DC381F";                                        
                                    }
                                }
                            }
                        }else{/*Si no hay notas, vemos el estado del ticket*/
                            if(strtoupper($rs['estado']) != "CERRADO"){/*Si el ticket no esta cerrado*/
                                if(strtoupper($rs['tipoCliente']) == "VIP"){/*Si el cliente es VIP*/
                                    if(number_format($rs['diferencia']) >= 2){/*Si ya van mas de 7 dias que se levanto el ticket*/
                                        $color = "#DC381F";                                        
                                    }else{
                                        $color = "#FFF380";                                        
                                    }
                                }else{/*Si no es cliente VIP*/
                                    if(number_format($rs['diferencia']) >= 7){/*Si ya van mas de 7 dias que se levanto el ticket*/
                                        $color = "#DC381F";                                        
                                    }
                                }
                            }
                        }                        
                        
                        echo "<tr style='background-color: $color; color:black;'>";
                        echo "<td align='center' scope='row'>" .$rs['IdTicket']. "</td>";
                        echo "<td align='center' scope='row'>" .$rs['FechaHora']. "</td>";  
                        if(isset($rs['id_bitacora'])){
                            echo "<td align='center' scope='row'><a href='#' onclick='editarRegistro(\"almacen/alta_bitacora.php?consulta_tiquet=tfs/mis_tickets.php\",\"".$rs['id_bitacora']."\"); return false;'>" . $rs['NumSerie'] . "</a></td>";
                        }else{
                            $series = explode(",", $rs['NumSerie']);
                            $texto = "";
                            foreach ($series as $value) {
                                $texto.= "<a href='#' onclick='cambiarContenidos(\"almacen/alta_bitacora.php?consulta_tiquet=mesa/lista_ticket.php&NoSerie=$value\"); return false;'>" . $value . "</a>,";
                            }
                            $texto = substr($texto, 0, strlen($texto) - 1);
                            echo "<td align='center' scope='row'>$texto</td>";
                        }
                        echo "<td align='center' scope='row'>" .$rs['ClaveClienteTicket']. "</td>";                          
                        echo "<td align='center' scope='row'>" .$rs['NombreCliente']. " - ".$rs['NombreCentroCosto']."</td>";                        
                        echo "<td align='center' scope='row'>" .$rs['DescripcionReporte']. "</td>";
                        
                        if(isset($datos[0])){
                            echo "<td align='center' scope='row'>" .$datos[0]. "</td>";
                        }else{
                            echo "<td align='center' scope='row'></td>";
                        }
                        if(isset($datos[1])){
                            echo "<td align='center' scope='row'>" .$datos[1]. "</td>";
                        }else{
                            echo "<td align='center' scope='row'></td>";
                        }
                        if(isset($datos[2])){
                            echo "<td align='center' scope='row'>" .$datos[2]. "</td>";
                        }else{
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
                        }else{
                            $nuevo = false;
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
                        <?php if ($permisos_grid->getModificar()) { ?>
                            <a href='#' onclick='cambiarContenidos("nota/AgregarNota.php?idTicket1=<?php echo $rs['IdTicket']; ?>&pagina_anterior=tfs/mis_tickets.php");
                                    return false;' title='Agregar nota' >
                                <img src="resources/images/notes.ico" style="width:24px; height: 24px; "/>
                            </a>
                        <?php } ?>
                    </td>
                    
                    <td align='center' scope='row'> 
                        <a href='#' onclick='detalleReporte("reportes/reporte_ticket.php", "<?php echo $rs['IdTicket']; ?>", null, null);
                                        return false;' title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>
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