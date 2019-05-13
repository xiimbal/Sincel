<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");

$tecnicos = array();
$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT IdUsuario, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS tecnico FROM `c_usuario` WHERE IdPuesto = 18;");

$tecnicos["0"] = "Selecciona al técnico";
while ($rs = mysql_fetch_array($query)) {
    $tecnicos[$rs['IdUsuario']] = $rs['tecnico'];
}

//$cerradoTicket = "t.EstadoDeTicket <> 2 AND ";
//$checked = "";
//$morososTicket = "cl.IdEstatusCobranza <> 2 AND ";
//$checkedMoroso = "";
//$canceladoTicket = "t.EstadoDeTicket <> 4 AND ";
//$checkedCancelado = "";
$ticket = "";
$idTicket = "";
$cliente = "";
$color = "";
$colorPOST = "";
$checked = "";
if (isset($_POST["ticket"]) && $_POST["ticket"] != "")
    $idTicket = $_POST["ticket"];
if (isset($_POST["cliente"]) && $_POST["cliente"] != "")
    $cliente = "t.ClaveCliente = '" . $_POST['cliente'] . "' AND ";
if (isset($_POST["color"]) && $_POST["color"] != "")
    $colorPOST = $_POST["color"];
//if (isset($_POST['idTicket']) && $_POST['idTicket'] != "") {
//    $idTicket = $_POST['idTicket'];
//    /* Si se busco un ticket en particular, habilitamos cerrados, morosos y cancelados */
//    $checked = "checked='checked'";
//    $checkedMoroso = "checked='checked'";
//    $checkedCancelado = "checked='checked'";
//}
//if (isset($_POST['cerrado']) && $_POST['cerrado'] != "false") {
//    $cerradoTicket = "";
//    $checked = "checked='checked'";
//}
//
//if (isset($_POST['moroso']) && $_POST['moroso'] != "false") {
//    $morososTicket = "";
//    $checkedMoroso = "checked='checked'";
//}
//
//if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
//    $canceladoTicket = "";
//    $checkedCancelado = "checked='checked'";
//}
//if (isset($_POST['cliente']) && $_POST['cliente']) {
//    $cliente = "t.ClaveCliente = '" . $_POST['cliente'] . "' AND ";
//}
//
//if (isset($_POST['color'])) {
//    $colorPOST = $_POST['color'];
//}
//if (isset($_POST['estado']) && $_POST['estado'] != "") {
//    $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdEstatusAtencion = " . $_POST['estado'] . " AND nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
//}
$usuario = "";
$puesto = "";
if (isset($_POST['usuario']) && $_POST['usuario'] == "0") {
    $usuario = $_POST['usuario'];
} else if (isset($_POST['usuario']) && $_POST['usuario'] != "") {
    $catalogo1 = new Catalogo();
    $query1 = $catalogo1->obtenerLista("SELECT * FROM c_usuario u WHERE u.IdUsuario='" . $_POST['usuario'] . "'");
    if ($rs = mysql_fetch_array($query1)) {
        $usuario = $rs['Loggin'];
    }
}
$catalogo1 = new Catalogo();
$query1 = $catalogo1->obtenerLista("SELECT * FROM c_usuario u WHERE u.Loggin='" . $_SESSION['user'] . "' ");
if ($rs = mysql_fetch_array($query1)) {
    $puesto = $rs['IdPuesto'];
    if ($puesto == "18")
        $usuario = $rs['Loggin'];
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>    
        <!--easyui-->
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
                </tr>
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
                                if (isset($_POST['cliente']) && $_POST['cliente'] == $rs['ClaveCliente']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . "</option>";
                            }
                            ?> 
                        </select>
                    </td>
                    <?php
                        if ($puesto != "18") {
                            echo "<td>Usuario</td>
                               <td>
                               <select id='usuarioslc' name='usuarioslc' style='width: 200px;' >
                               <option>Seleccione un tecnico</option>";
                            $s = "";
                            if ($usuario == "0")
                                $s = "selected='selected'";
                            echo "<option value='0' $s >Mostrar todos las refacciones</option>";
                            $catalogo = new Catalogo();
                            $query = $catalogo->obtenerLista("SELECT * FROM c_usuario u WHERE u.IdPuesto=18 AND Activo=1 ORDER BY Nombre,ApellidoPaterno");
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if (isset($_POST['usuario']) && $_POST['usuario'] == $rs['IdUsuario']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . "</option>";
                            }
                            echo" </select>
                               </td>
                               ";
                        }
                    ?>
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
                </tr>                
                <tr><td><input type="Button" id="boton_aceptar" name="boton_aceptar" value="Buscar ticket" class="boton" onclick="BuscarTicket('hardware/lista_notasLista.php');"/></td></tr>
            </table>            
            <br/><br/>
            <table id="tAlmacen" class="tabla_grid_length">
                <thead>
                    <tr>
                        <?php
                        $cabeceras = array("Ticket", "Fecha", "Cliente", "Falla", "Última Nota", "Fecha nota", "Refecciones");
                        for ($i = 0; $i < (count($cabeceras)); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        ?>                                                                      
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($usuario == "0") {
                        $consulta = "SELECT t.IdTicket,nt.IdNotaTicket,c.Modelo,cl.NombreRazonSocial,nr.CantidadSurtida,(SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area
                                        ,CONCAT ('(',nr.Cantidad-nr.CantidadSurtida,') ',c.Modelo) AS refaccion,DATEDIFF(NOW(), t.FechaHora) AS diferencia,nt.IdEstatusAtencion
                                        ,t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza,t.TipoReporte,e.Nombre AS ultimoEstatus,nt.DiagnosticoSol,
					t.NombreCentroCosto,t.DescripcionReporte,nr.Cantidad,c.NoParte,nt.MostrarCliente,nt.Activo,nt.UsuarioSolicitud,
					nr.IdAlmacen,cl.ClaveCliente,nt.FechaHora AS fechaNota,t.FechaHora AS fechaTicket,nr.Cantidad-nr.CantidadSurtida AS total                                                         
                                        FROM k_nota_refaccion nr,c_notaticket nt,c_ticket t,c_componente c,c_cliente cl,c_estado e
                                        WHERE t.IdTicket=nt.IdTicket AND nt.IdNotaTicket=nr.IdNotaTicket AND nr.NoParteComponente=c.NoParte AND t.ClaveCliente=cl.ClaveCliente
                                        AND $cliente nt.IdEstatusAtencion=e.IdEstado
                                        AND nt.IdEstatusAtencion=21
                                        AND nr.Cantidad<>0   HAVING(total>0)                                      
                                        ORDER BY nt.IdNotaTicket,c.Modelo ASC";
                    } else if ($idTicket == "") {
                        $consulta = "SELECT t.IdTicket,nt.IdNotaTicket,c.Modelo,cl.NombreRazonSocial,nr.CantidadSurtida,(SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area
                                        ,CONCAT ('(',nr.Cantidad-nr.CantidadSurtida,') ',c.Modelo) AS refaccion,DATEDIFF(NOW(), t.FechaHora) AS diferencia,nt.IdEstatusAtencion
                                        ,t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza,t.TipoReporte,e.Nombre AS ultimoEstatus,nt.DiagnosticoSol,
					t.NombreCentroCosto,t.DescripcionReporte,nr.Cantidad,c.NoParte,nt.MostrarCliente,nt.Activo,nt.UsuarioSolicitud,
					nr.IdAlmacen,cl.ClaveCliente,nt.FechaHora AS fechaNota,t.FechaHora AS fechaTicket,nr.Cantidad-nr.CantidadSurtida AS total                                                         
                                        FROM k_nota_refaccion nr,c_notaticket nt,c_ticket t,c_componente c,c_cliente cl,c_estado e
                                        WHERE t.IdTicket=nt.IdTicket AND nt.IdNotaTicket=nr.IdNotaTicket AND nr.NoParteComponente=c.NoParte AND t.ClaveCliente=cl.ClaveCliente
                                        AND $cliente nt.IdEstatusAtencion=e.IdEstado
                                        AND nt.IdEstatusAtencion=21
                                        AND nr.Cantidad<>0 HAVING(total>0)  
					AND nt.UsuarioSolicitud='" . $usuario . "'                                    
                                        ORDER BY nt.IdNotaTicket,c.Modelo ASC";
                    } else {
                        $consulta = "SELECT t.IdTicket,nt.IdNotaTicket,c.Modelo,cl.NombreRazonSocial,nr.CantidadSurtida,(SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area
                                        ,CONCAT ('(',nr.Cantidad-nr.CantidadSurtida,') ',c.Modelo) AS refaccion,DATEDIFF(NOW(), t.FechaHora) AS diferencia,nt.IdEstatusAtencion
                                        ,t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza,t.TipoReporte,e.Nombre AS ultimoEstatus,nt.DiagnosticoSol,
					t.NombreCentroCosto,t.DescripcionReporte,nr.Cantidad,c.NoParte,nt.MostrarCliente,nt.Activo,nt.UsuarioSolicitud,
					nr.IdAlmacen,cl.ClaveCliente,nt.FechaHora AS fechaNota,t.FechaHora AS fechaTicket ,nr.Cantidad-nr.CantidadSurtida AS total                                                        
                                        FROM k_nota_refaccion nr,c_notaticket nt,c_ticket t,c_componente c,c_cliente cl,c_estado e
                                        WHERE t.IdTicket=nt.IdTicket AND nt.IdNotaTicket=nr.IdNotaTicket AND nr.NoParteComponente=c.NoParte AND t.ClaveCliente=cl.ClaveCliente
                                        AND $cliente nt.IdEstatusAtencion=e.IdEstado
                                        AND nt.IdEstatusAtencion=21
                                        AND nr.Cantidad<>0 HAVING(total>0)  
                                        AND t.IdTicket='" . $idTicket . "'                                       
                                        ORDER BY nt.IdNotaTicket,c.Modelo ASC";
                    }
//echo $consulta;
                    $query = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query)) {/* Recorremos todos los tickets resultantes del query */
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

                        echo "<tr style='background-color: $color; color:black;'>";
                        echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['fechaTicket'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['NombreRazonSocial'] . " - " . $rs['NombreCentroCosto'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['DescripcionReporte'] . "</td>";

                        echo "<td align='center' scope='row'>" . $rs['DiagnosticoSol'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['fechaNota'] . "</td>";
                        ?>

                        <?php
                        echo "<td align='center' scope='row'>";
                        echo $rs['refaccion'];
                        echo "<div id='error_tecnico_" . $rs['IdTicket'] . "' style='color:red; display:none;'>Selecciona algún técnico</div></td>";
                        ?> 
                                                                                                    <!--                    <td><input type="button" value ="Validar" onclick="validarNota('<?php echo $rs['IdNotaTicket'] ?>','<?php echo $rs['IdTicket'] ?>', '<?php echo $rs['DiagnosticoSol'] ?>', '24');" class="boton"/></td>-->
                        <?php
                        //   echo "<td><button class='boton' onclick='validarNota(\"" . $rs['IdNotaTicket'] . "\",\"" . $rs['DescripcionReporte'] . "\",'24')'>Validar</button></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
                <input type="hidden" id="color_hidden" name="color_hidden" value="<?php echo $colorPOST; ?>"/>
            </table>
        </div>
    </body>
</html>