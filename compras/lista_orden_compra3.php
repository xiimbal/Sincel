<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$catalogo = new Catalogo();
$alta = "compras/alta_orden_compra.php";

$same_page = "compras/lista_orden_compra.php";
$permisos_grid = new PermisosSubMenu();
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$nombre_objeto = $permisos_grid->getNombreTicketSistema();

$controlador = "WEB-INF/Controllers/compras/Controler_Orden_Compra.php";
$proveedor = "";
$modelo = "";
$fechaInicio = "";
$fechaFin = "";
$oc = "";
$estatus = "";
$where = "";
$surtido = "";
$cancelados = "";
$no_pedido = "";
$tickets = "";
$where_prin = "";
$reporte = "compras/reporte_orden_compra.php";
$reportePakal = "compras/reporte_orden_compra_pakal.php";
if (isset($_POST['no_pedido']) && $_POST['no_pedido'] != "") {
    $no_pedido = $_POST['no_pedido'];
    $where_prin .= " AND oc.NoPedido='$no_pedido'";
    $surtido = "checked";
    $cancelados = "checked";
} else if (isset($_POST['oc']) && $_POST['oc'] != "") {
    $oc = $_POST['oc'];
    $where_prin.= " AND oc.Id_orden_compra='$oc'";
    $surtido = "checked";
    $cancelados = "checked";
}
if (isset($_POST['proveedor']) && $_POST['proveedor'] != "0") {
    $proveedor = $_POST['proveedor'];
    $where .= " AND oc.FacturaEmisor='$proveedor'";
}
if (isset($_POST['modelo']) && $_POST['modelo'] != "") {
    $modelo = $_POST['modelo'];
    $where .= " AND (c.Modelo LIKE '%$modelo%' OR eq.Modelo LIKE '%$modelo%' )";
}
if (isset($_POST['fechaInicio']) && $_POST['fechaInicio'] != "" && isset($_POST['fechaFin']) && $_POST['fechaFin'] != "") {
    $fechaInicio = $_POST['fechaInicio'];
    $fechaFin = $_POST['fechaFin'];
    $where .= " AND oc.FechaOrdenCompra BETWEEN '$fechaInicio' AND '$fechaFin'";
}
if (isset($_POST['estatus']) && $_POST['estatus'] != "0") {
    $estatus = $_POST['estatus'];
    $where .= " AND oc.Estatus='$estatus'";
}
if (isset($_POST['surtido']) && $_POST['surtido'] == "1") {
    $surtido = "checked";
} else {
    if ($estatus != "70") {
        $where .= " AND oc.Estatus<>70";
    }
}
if (isset($_POST['cancelados']) && $_POST['cancelados'] == "1") {
    $cancelados = "checked";
} else {
    if ($estatus != "59") {
        $where .= " AND oc.Estatus<>59";
    }
}

if(isset($_POST['tickets']) && $_POST['tickets'] == "1"){
    $tickets = "checked";
}
$excel = "compras/XML_orden_compra.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head> 
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/compras/alta_orden_compra.js"></script>
        <script>
            $(".button").button();
        </script>
    </head>
    <div class="p-4 bg-light rounded">
    <body>
        <div class="principal bg-inverse">       
            <form action="compras/XML_orden_compra.php" method="POST" target="_blank" id="FormularioExportacion">
                <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="txt_no_ped">Número de pedido:</label>
                            <input class="form-control" type="text" id="txt_no_ped" name="txt_no_ped" value="<?php echo $no_pedido ?>"/>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="txtOrdenCompraL">Orden de compra:</label>
                            <input class="form-control" type="text" id="txtOrdenCompraL" name="txtOrdenCompraL" value="<?php echo $oc ?>"/>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="txtModeloL">Modelo:</label>
                            <input class="form-control" type="text" id="txtModeloL" name="txtModeloL" value="<?php echo $modelo; ?>"/>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="slProveedorL">Proveedor:</label>
                            <select class="form-control" id="slProveedorL" name="slProveedorL">
                                <option value="0">Todos los proveedores</option>
                                <?php
                                $queryProveedor = $catalogo->getListaAlta("c_proveedor", "NombreComercial");
                                while ($rs = mysql_fetch_array($queryProveedor)) {
                                    $s = "";
                                    if ($proveedor != "" && $proveedor == $rs['ClaveProveedor']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['ClaveProveedor'] . "' $s>" . $rs['NombreComercial'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="ckSurtido">Surtidos</label>
                            <input type="checkbox" id="ckSurtido" name="ckSurtido" <?php echo $surtido; ?> />
                        </div>
                        <div class="form-group col-md-3">
                            <label for="txtFechaInicioL">Fecha inicio:</label>
                            <input class="form-control" type="text" id="txtFechaInicioL" name="txtFechaInicioL" value="<?php echo $fechaInicio; ?>"/>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="txtFechaFinL">Fecha fin:</label>
                            <input class="form-control" type="text" id="txtFechaFinL" name="txtFechaFinL" value="<?php echo $fechaFin; ?>"/>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="slEstatusL">Estatus:</label>
                            <select class="form-control" id="slEstatusL" name="slEstatusL">
                                <option value="0">Todos los estatus</option>
                                <?php
                                $queryEsatus = $catalogo->obtenerLista("SELECT e.IdEstado,e.Nombre FROM c_estado e INNER JOIN k_flujoestado fe ON e.IdEstado=fe.IdEstado INNER JOIN c_flujo f ON fe.IdFlujo=f.IdFlujo WHERE f.IdFlujo=9 AND e.Activo=1 ORDER BY e.Nombre ASC");
                                while ($rs = mysql_fetch_array($queryEsatus)) {
                                    $s = "";
                                    if ($estatus != "" && $estatus == $rs['IdEstado']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="ckTickets"><?php echo $nombre_objeto?></label>
                            <input type="checkbox" id="ckTickets" name="ckTickets" <?php echo $tickets?>>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="ckCancelados">Cancelados</label>
                            <input type="checkbox" id="ckCancelados" name="ckCancelados" <?php echo $cancelados; ?>/>
                        </div>
                        <div class="form-group col-md-3">
                            <button class="btn btn-success" title="Exportar a excel" id="excelSubmit" name="excelSubmit" title="Exportar a excel" onclick="ExportarOCExcel()">Exportar a excel</button>
                            <!--input type="button" class="botonExcel button" title="Exportar a excel" id="excelSubmit" name="excelSubmit" value="Exportar a excel" onclick="ExportarOCExcel()"/-->
                            <input type="hidden" id="datos_a_enviar" name="datos_a_enviar" /> 
                        </div>
                    
                </div>
                <!--table class="table table-responsive">
                    
                    <tr>
                        <td></td><td></td> 
                        <td></td>
                        <td>
                            
                        </td>
                        <td></td>
                        <td></td>
                        <td></td><td></td>

                    </tr>
                </table>
                <div style="float: right">
                      
                </div-->
            </form>
            <input type="button" class="btn btn-success" style="float: left" value="Buscar" onclick="buscarOrdenCompra();"/>
            <?php if ($permisos_grid->getAlta()) { ?>
                <button class="btn btn-info" title="Nuevo" onclick='window.location = "principal.php?mnu=compras&action=alta_orden_compra";' style="float: right; cursor: pointer;" ><i class="fal fa-plus-circle"></i></button>
                <!--img class="imagenMouse" src="resources/images/add.png" title="Nuevo" 
                     onclick='window.location = "principal.php?mnu=compras&action=alta_orden_compra";' style="float: right; cursor: pointer;" /-->  
                 <?php } ?>
            <div class="table-responsive">
                <table id="tAlmacen" class="table">
                <thead class="thead-dark">
                    <tr>
                        <th align='center' scope='row' style="width: 9%">Orden de compra</th>
                        <th align='center' scope='row' style="width: 9%">No. pedido</th>
                        <th align='center' scope='row' style="width: 8%">Fecha</th>
                        <th align='center' scope='row' style="width: 15%">Proveedor</th>
                        <th align='center' scope='row' style="width: 7%">Estatus</th>
                        <th align='center' scope='row' style="width: 15%">Cantidad Eq,Comp,Servicios o Productos</th>
                        <th align='center' scope='row' style='width: 15%'>Viáticos</th>
                        <th align='center' scope='row' style="width: 6%">Editar</th>
                        <th align='center' scope='row' style="width: 8%">Eliminar</th>
                        <th align='center' scope='row' style="width: 8%">Imprimir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if(!empty($tickets)){
                        $consulta = "SELECT oc.Id_orden_compra, oc.FechaOrdenCompra, p.NombreComercial, e.Nombre AS estatus,
                        oc.Descripcion_Ticket AS cantModelo, oc.NoPedido AS NoPedido,k.IdTicket FROM c_orden_compra oc 
                        LEFT JOIN c_proveedor p ON p.ClaveProveedor = oc.FacturaEmisor LEFT JOIN c_estado e ON e.IdEstado = oc.Estatus 
                        LEFT JOIN k_tickets_oc k ON k.IdOrdenCompra = oc.Id_orden_compra WHERE oc.Factura_Ticket = 1";
                    }else{
                        if ($oc == "" && $no_pedido == "") {
                            $consulta = "SELECT oc.Id_orden_compra,oc.NoPedido,oc.FechaOrdenCompra,p.NombreComercial,e.Nombre AS estatus,SUM(koc.Cantidad) as total,
                                            GROUP_CONCAT('(',koc.Cantidad,')',if(ISNULL(koc.NoParteComponente),eq.NoParte,c.Modelo)) AS cantModelo,	
                                            SUM(koc.CantidadEntregada) AS entregada, oc.Factura_Ticket FROM c_orden_compra oc LEFT JOIN k_orden_compra koc ON oc.Id_orden_compra=koc.IdOrdenCompra 
                                            LEFT JOIN c_proveedor p ON p.ClaveProveedor=oc.FacturaEmisor LEFT JOIN c_estado e ON oc.Estatus=e.IdEstado 
                                            LEFT JOIN c_componente c ON c.NoParte=koc.NoParteComponente LEFT JOIN c_equipo eq ON eq.NoParte=koc.NoParteEquipo 
                                            WHERE oc.Activo=1 $where GROUP BY koc.IdOrdenCompra";
                        }else {
                            $consulta = "SELECT oc.Id_orden_compra,oc.NoPedido,oc.FechaOrdenCompra,p.NombreComercial,e.Nombre AS estatus,SUM(koc.Cantidad) as total,
                                            GROUP_CONCAT('(',koc.Cantidad,')',if(ISNULL(koc.NoParteComponente),eq.NoParte,c.Modelo)) AS cantModelo,
                                            SUM(koc.CantidadEntregada) AS entregada, oc.Factura_Ticket FROM c_orden_compra oc LEFT JOIN k_orden_compra koc ON oc.Id_orden_compra=koc.IdOrdenCompra 
                                            LEFT JOIN c_proveedor p ON p.ClaveProveedor=oc.FacturaEmisor LEFT JOIN c_estado e ON oc.Estatus=e.IdEstado 
                                            LEFT JOIN c_componente c ON c.NoParte=koc.NoParteComponente LEFT JOIN c_equipo eq ON eq.NoParte=koc.NoParteEquipo 
                                            WHERE oc.Activo=1 $where_prin GROUP BY koc.IdOrdenCompra";
                        }                        
                    }
                    $query = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs['Id_orden_compra'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['NoPedido'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['FechaOrdenCompra'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['NombreComercial'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['estatus'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['cantModelo'] . "</td>";
                        echo "<td align='center' scope='row'>";
                        if(isset($rs['IdTicket'])){
                            $consulta2 = "SELECT IF(ISNULL(nt.IdViatico),e.Nombre, tv.nombre) AS viatico, nt.DiagnosticoSol FROM k_tickets_oc k
                            INNER JOIN c_ticket t ON t.IdTicket = k.IdTicket INNER JOIN c_notaticket nt ON nt.IdTicket = t.IdTicket
                            LEFT JOIN c_tipoviatico tv ON tv.idTipoViatico = nt.IdViatico INNER JOIN c_estado e ON e.IdEstado = nt.IdEstatusAtencion 
                            INNER JOIN k_serviciove ve ON ve.IdNotaTicket = nt.IdNotaTicket WHERE k.IdOrdenCompra = " . $rs['Id_orden_compra'] . " AND k.FacturoViaticos = 1 AND ve.Validado = 1 AND k.IdTicket = " . $rs['IdTicket'];
                            $query2 = $catalogo->obtenerLista($consulta2);
                            $viaticos = "";
                            while($rs2 = mysql_fetch_array($query2)){
                                $viaticos .= "<b>" . $rs2['viatico'] . "</b> (" . $rs2['DiagnosticoSol'] . "),";
                            }
                            echo trim($viaticos, ",");
                        }
                        echo "</td>";
                        ?>
                    <td align='center' scope='row'>
                        <?php if ($permisos_grid->getModificar()) { 
                                //if($rs['FacturaTicket'] != "1"){
                            ?>
                            <a href='#' onclick='
                                    window.location = "principal.php?mnu=compras&action=alta_orden_compra&id=<?php echo $rs['Id_orden_compra']; ?>";
                                    return false;
                               ' title='Reporte' class='text-warning' style="font-size: 1.5rem;"><i class="fal fa-pencil"></i></a>
                           <?php
                                //}
                           } ?>
                    </td>
                    <td align='center' scope='row'>
                        <?php
                        if ($permisos_grid->getBaja()) {
                            if ((int) $rs['entregada'] < 1) {
                                ?>
                                <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs['Id_orden_compra']; ?>", "<?php echo $same_page; ?>");
                                        return false;' title='Reporte' class="text-danger" style="font-size: 1.5rem;"><i class="fal fa-trash"></i></a>
                                   <?php
                               }
                           }
                           ?>
                    </td>
                    <?php
                    if ($nombre_objeto == "Camion") { ?>
                    
                    <td align='center' scope='row'>
                        <a href='#' onclick='imprimirReporteOC("<?php echo $reportePakal; ?>", "<?php echo $rs['Id_orden_compra']; ?>");
                                return false;' title='Reporte' class="text-info" style="font-size: 1.5rem;"><i class="fal fa-print"></i></a>
                    </td> 
                    <?php } else { ?>

                     <td align='center' scope='row'>
                        <a href='#' onclick='imprimirReporteOC("<?php echo $reporte; ?>", "<?php echo $rs['Id_orden_compra']; ?>");
                                return false;' title='Reporte' class="text-info" style="font-size: 1.5rem;"><i class="fal fa-print"></i></a>
                    </td>
                    <?php }
                    
                    echo "</tr>";
                }
                ?>                    
                </tbody>
                </table>
            </div>
        </div>
    </body>
</div>
</html>