<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Orden_Compra.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$catalogo = new Catalogo();
$ordenCompra = new Orden_Compra();
$parametros = new Parametros();
$permisos = new PermisosSubMenu();

$idOrdenCompra = "";
$facturaEmisor = "";
$facturaReceptors = "";
$fecha = "";
$estatus = "71";
$condicion = "";
$disabled = "";
$noCliente = "";
$noprov = "";
$notas = "";
$trasp = "";
$peso = "";
$metros = "";
$origen = "";
$metodo = "";
$embarque = "";
$idAlmacen = "";
$no_pedido = "";

$dividir_recepcion = false;
if ($parametros->getRegistroById("24") && $parametros->getValor() != "0") {
    $dividir_recepcion = true;
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/file_upload/jquery.iframe-transport.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/file_upload/jquery.fileupload.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/compras/alta_entrada_orden_compra.js"></script>
        <style>
            .celda {border: 1px black solid;text-align:center;font-size: 10px;}
        </style>
        <script>
            $(".button").button();
        </script>
    </head>
    <body>
        <div class="pricipal">
            <?php
            if (isset($_POST['id']) || isset($_GET['id'])) {
                if (isset($_POST['id'])) {
                    $idOrdenCompra = $_POST['id'];
                } else {
                    $idOrdenCompra = $_GET['id'];
                }
                $ordenCompra->getRegistroById($idOrdenCompra);
                $facturaEmisor = $ordenCompra->getFacturaEmisor();
                $facturaReceptors = $ordenCompra->getFacturaRecptor();
                $fecha = $ordenCompra->getFechaOC();
                $condicion = $ordenCompra->getCondicionPago();
                $estatus = $ordenCompra->getEstatus();
                $embarque = $ordenCompra->getEmbarca();
                $noCliente = $ordenCompra->getNoCliente();
                $noprov = $ordenCompra->getNoPedidoProv();
                $notas = $ordenCompra->getNotas();
                $trasp = $ordenCompra->getTransportista();
                $peso = $ordenCompra->getPeso();
                $metros = $ordenCompra->getMetros();
                $origen = $ordenCompra->getOrigen();
                $metodo = $ordenCompra->getMetodoEntrega();
                $idAlmacen = $ordenCompra->getAlmacen();
                $no_pedido = $ordenCompra->getNo_pedido();
                if ($estatus != "71") {
                    $disabled = "disabled";
                }
                if ($peso == "0") {
                    $peso = "";
                }
                if ($metros == "0") {
                    $metros = "";
                }
            }
            ?>
            <br/><br/>
            <form id="frmOrdenCompra" name="frmOrdenCompra" action="/" method="POST">
                <table style="width: 100% ">
                    <tr>
                        <td>Orden de compra:</td>
                        <td>
                            <select id="slOrdenCompra" name="slOrdenCompra" style="width: 155px" onchange="mostrarDatosOC(this.value)">
                                <option value="0">Selecione un opción</option>
                                <?php
                                $queryOC = $catalogo->obtenerLista("SELECT oc.Id_orden_compra FROM c_orden_compra oc ORDER BY oc.Id_orden_compra DESC");
                                while ($rs = mysql_fetch_array($queryOC)) {
                                    $s = "";
                                    if ($idOrdenCompra != "" && $idOrdenCompra == $rs['Id_orden_compra']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['Id_orden_compra'] . "' $s>" . $rs['Id_orden_compra'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:10%">No. pedido:<span style="color: red">*</span></td><td style="width:20%"><input type="text" id="txt_pedido" name="txt_pedido" value="<?php echo $no_pedido; ?>" readonly/></td>
                        <td>Proveedor:</td>
                        <td>
                            <select id="slProveedor" name="slProveedor" style="width: 155px" <?php echo $disabled; ?>  disabled onchange="mostrarDireccionProveedor(this.value)">
                                <option value="0">Selecione un opción</option>
                                <?php
                                $queryProveedor = $catalogo->getListaAlta("c_proveedor", "NombreComercial");
                                while ($rs = mysql_fetch_array($queryProveedor)) {
                                    $s = "";
                                    if ($facturaEmisor != "" && $facturaEmisor == $rs['ClaveProveedor']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['ClaveProveedor'] . "' $s>" . $rs['NombreComercial'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>                 
                        <td>Factura a:</td>
                        <td>
                            <select id="slRazonSocial" name="slRazonSocial" style="width: 155px" <?php echo $disabled; ?>  disabled onchange="mostrarDireccionFacturacion(this.value)">
                                <option value="0">Selecione un opción</option>
                                <?php
                                $queryFactura = $catalogo->getListaAlta("c_datosfacturacionempresa", "RazonSocial");
                                while ($rs = mysql_fetch_array($queryFactura)) {
                                    $s = "";
                                    if ($facturaReceptors != "" && $facturaReceptors == $rs['IdDatosFacturacionEmpresa']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['IdDatosFacturacionEmpresa'] . "' $s>" . $rs['RazonSocial'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>              

                    <tr>
                        <td>Fecha pedido:</td><td><input type="text" id="txtfechaOrden" name="txtfechaOrden"  disabled value="<?php echo $fecha; ?>" <?php echo $disabled; ?>/></td>
                        <td>Condiciones de pago:</td>
                        <td>
                            <select id="slFormaPago" name="slFormaPago" style="width:155px" disabled="">
                                <option value="0">Selecciona una opción</option>
                                <?php
                                $queryForma = $catalogo->getListaAlta("c_formapago", "Nombre");
                                while ($rs = mysql_fetch_array($queryForma)) {
                                    $s = "";
                                    if ($condicion == $rs['IdFormaPago']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['IdFormaPago'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select> 
                        </td>
                        <td>Estatus:</td>
                        <td>
                            <select id="slEstatus" name="slEstatus" style="width: 155px"  disabled>
                                <option value="0">Selecione un opción</option>
                                <?php
                                $queryEsatus = $catalogo->obtenerLista("SELECT e.IdEstado,e.Nombre FROM c_estado e INNER JOIN k_flujoestado fe ON e.IdEstado=fe.IdEstado INNER JOIN c_flujo f ON fe.IdFlujo=f.IdFlujo WHERE f.IdFlujo=9 ORDER BY e.Nombre ASC");
                                while ($rs = mysql_fetch_array($queryEsatus)) {
                                    $s = "";
                                    if ($estatus != "" && $estatus == $rs['IdEstado']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>No. cliente:</td><td><input type="text" id="txtNoCliente" name="txtNoCliente" disabled value="<?php echo $noCliente; ?>" <?php echo $disabled; ?>/></td>
                        <td>No. pedido proveedor:</td><td><input type="text" id="txtPedidoProv" name="txtPedidoProv" disabled value="<?php echo $noprov; ?>" <?php echo $disabled; ?>/></td>
                        <td>Notas:</td><td><input type="text" id="txtNotas" name="txtNotas" disabled value="<?php echo $notas; ?>" <?php echo $disabled; ?>/></td>
                    </tr>
                    <tr>
                        <td>Transportista:</td>
                        <td>
                            <select id="slMensajeria" name="slMensajeria" style="width: 155px" disabled>
                                <option value="0">Selecione un opción</option>
                                <?php
                                $queryTransp = $catalogo->getListaAlta("c_mensajeria", "Nombre");
                                while ($rs = mysql_fetch_array($queryTransp)) {
                                    $s = "";
                                    if ($trasp != "" && $trasp == $rs['IdMensajeria']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['IdMensajeria'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>Peso Kg:</td><td><input type="text" id="txtPeso" name="txtPeso" disabled value="<?php echo $peso; ?>" <?php echo $disabled; ?>/></td>
                        <td>Metros cúbicos:</td><td><input type="text" id="txtMetros" disabled name="txtMetros" value="<?php echo $metros; ?>" <?php echo $disabled; ?>/></td>
                    </tr>
                    <tr>
                        <td>Origen:</td><td><input type="text" id="txtOrigen" name="txtOrigen" disabled value="<?php echo $origen; ?>" <?php echo $disabled; ?>/></td>
                        <td>Método de entrega:</td><td><input type="text" id="txtMetodo" name="txtMetodo" disabled value="<?php echo $metodo; ?>" <?php echo $disabled; ?>/></td>
                        <td>Almacén:</td>
                        <td>
                            <select id="slAlmacen" name="slAlmacen" style="width:155px">
                                <option value="0">Seleccione un almacén</option>
                                <?php
                                $x = 0;
                                $queryAlmacen = $catalogo->getListaAlta("c_almacen", "nombre_almacen");
                                while ($rs = mysql_fetch_array($queryAlmacen)) {
                                    $s = "";
                                    if ($idAlmacen == $rs['id_almacen']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['id_almacen'] . "' $s>" . $rs['nombre_almacen'] . "</option>";
                                }
                                ?>
                            </select><div id="errorAlamcen"></div>
                        </td>
                    </tr> 
                    <tr>
                        <td>Folio factura:</td><td><input type='text' id="txtFolioFactura" name="txtFolioFactura" value=''><br/><div id='errorFolio'></div></td>
                    </tr>
                </table>
                <br/><br/>
                <?php
                if ($permisos->tienePermisoEspecial($_SESSION['idUsuario'], 20)) {
                    ?>
                    <div id="divEntrada">
                        <div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em; width: 98%;">
                            <p>
                                <span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
                                Equipos y componentes a recibir
                            </p>
                        </div>
                        <table style="margin-left: 55%; width: 40%;">
                            <tr>
                                <td>Importar archivo CSV</td>
                                <td>
                                    <input id="fileupload" type="file" name="files[]" data-url="compras/php/" multiple class="button fileupload">
                                    <div id="progress">
                                        <div class="bar" style="width: 0%;"></div>
                                    </div>
                                </td>                            
                                <td><input type='button' value='Recibir' id="recibir_oc" onclick=' RecibirOC(0);' class='button' style="float: right"/></td>
                                <td><input type='button' value='Cancelar pedido' id="cancelar_oc" onclick='RecibirOC(1);' class='button' style="float: right"/></td>
                            </tr>
                        </table>                                        

                        <table style="border: 1px solid black;border-collapse: collapse;width:100%">
                            <tr>
                                <th align='center' scope='row' class="celda" style="width:20%">No parte / Modelo</th>
                                <th align='center' scope='row' class="celda" style="width:10%">No parte anterior</th>
                                <th align='center' scope='row' class="celda" style="width:30%">Descripción</th>
                                <th align='center' scope='row' class="celda" style="width:5%">Cantidad</th>
                                <th align='center' scope='row' class="celda" style="width:5%">C/recibida</th>
                                <th align='center' scope='row' class="celda" style="width:15%">No serie</th>
                                <th align='center' scope='row' class="celda" style="width:10%">Ubicación</th>
                                <th align='center' scope='row' class="celda" style="width:5%">Recibir</th>
                            </tr>  
                            <?php
                            if ($idOrdenCompra != "") {
                                $consulta = "SELECT koc.IdDetalleOC,(koc.Cantidad - koc.CantidadEntregada) AS restantes,koc.Cantidad,e.NoParte AS equipo,c.NoParte AS componente,
                                            c.NoParteAnterior,c.Modelo AS modeloC,e.Modelo AS modeloE,SUBSTRING(e.Descripcion,1,40) AS desEquipo,c.Descripcion AS desComp,
                                            (SELECT CASE WHEN koc.NoParteComponente IS NOT NULL THEN 'C' ELSE 'E' END) AS tipo 
                                            FROM k_orden_compra koc LEFT JOIN c_equipo e ON koc.NoParteEquipo=e.NoParte LEFT JOIN c_componente c ON koc.NoParteComponente=c.NoParte 
                                            WHERE koc.IdOrdenCompra='$idOrdenCompra' HAVING restantes > 0";
                                $queryEntrega = $catalogo->obtenerLista($consulta);
                                $fila = 0;
                                while ($rs = mysql_fetch_array($queryEntrega)) {
                                    if ($rs['restantes'] > "0") {
                                        if ($rs['tipo'] == "C") {
                                            echo "<tr>";
                                            echo "<td align='center' scope='row' class='celda'>" . $rs['componente'] . " / " . $rs['modeloC'] . "</td>";
                                            echo "<td align='center' scope='row' class='celda'>" . $rs['NoParteAnterior'] . "</td>";
                                            echo "<td align='left' scope='row' class='celda'>" . $rs['desComp'] . "</td>";
                                            echo "<td align='center' scope='row' class='celda'><input type='hidden' id='txtCantidad$fila' name='txtCantidad$fila' value='" . $rs['restantes'] . "'/>" . $rs['restantes'] . "</td>";
                                            echo "<td align='center' scope='row' class='celda'><input type='text' id='txtCantidadEntrada$fila' name='txtCantidadEntrada$fila' value='" . $rs['restantes'] . "' style='width:95%' /><div style='max-width:100%' id='errorCantidad$fila'></div></td>";
                                            echo "<td align='center' scope='row' class='celda'></td>";
                                            echo "<td align='center' scope='row' class='celda'><input type='text' id='txtUbicacion$fila' name='txtUbicacion$fila' value='' style='width:95%' /></td>";
                                            echo "<td align='center' scope='row' class='celda'><input type='checkbox' id='ckEntrada$fila' name='ckEntrada$fila' value='$fila' />" .
                                            "<input type='hidden' id='txtIdDetalle$fila' name='txtIdDetalle$fila' value='" . $rs['IdDetalleOC'] . "'/></td>";
                                            echo "</tr>";
                                            $fila++;
                                        } else if ($rs['tipo'] == "E") {
                                            $x = 0;
                                            while ($x < (int) $rs['restantes']) {
                                                echo "<tr>";
                                                echo "<td align='center' scope='row' class='celda'>" . $rs['equipo'] . " / " . $rs['modeloE'] . "</td>";
                                                echo "<td align='center' scope='row' class='celda'></td>";
                                                echo "<td align='left' scope='row' class='celda'>" . $rs['desEquipo'] . "</td>";
                                                echo "<td align='center' scope='row' class='celda'><input type='hidden' id='txtCantidad$fila' name='txtCantidad$fila' value='1'/>1</td>";
                                                echo "<td align='center' scope='row' class='celda'><input type='text' id='txtCantidadEntrada$fila' name='txtCantidadEntrada$fila' value='1' style='width:95%' readonly/></td>";
                                                echo "<td align='center' scope='row' class='celda'><input type='text' id='txtNoSerie$fila' name='txtNoSerie$fila' style='width:95%'/><div id='errorSerie$fila'></div></td>";
                                                echo "<td align='center' scope='row' class='celda'><input type='text' id='txtUbicacion$fila' name='txtUbicacion$fila' value='' style='width:95%' /></td>";
                                                echo "<td align='center' scope='row' class='celda'><input type='checkbox' id='ckEntrada$fila' name='ckEntrada$fila' value='$fila' />" .
                                                "<input type='hidden' id='txtIdDetalle$fila' name='txtIdDetalle$fila' value='" . $rs['IdDetalleOC'] . "'/></td>";
                                                echo "</tr>";
                                                $x++;
                                                $fila++;
                                            }
                                        }
                                    }
                                }
                            }
                            ?> 
                        </table>   
                        <br/>
                        <table style="margin-left: 55%; width: 20%;float:right">
                            <tr>                                              
                                <td><input type='button' value='Recibir' id="recibir_oc1" onclick=' RecibirOC(0);' class='button' style="float: right"/></td>
                                <td><input type='button' value='Cancelar pedido' id="cancelar_oc1" onclick='RecibirOC(1);' class='button' style="float: right"/></td>
                            </tr>
                        </table>           
                    </div>
                    <?php
                }
                if ($permisos->tienePermisoEspecial($_SESSION['idUsuario'], 21)) {
                    ?>
                    <br/><br/>
                    <div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em; width: 98%;">
                        <p>
                            <span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
                            Equipos y componentes recibidos para almac&eacute;n
                        </p>
                    </div>
                    <br/><br/>
                    <?php if ($dividir_recepcion) { ?>
                        <table style="margin-left: 80%; width: 20%;">
                            <tr>           
                                <td>
                                    <input type="button" value="Seleccionar todo" onclick="seleccionaTodo();" class='button' style="float: right"/>
                                </td>
                                <td>
                                    <input type='button' value='Recibir en almacén' 
                                           onclick= "recibirAlmacen(<?php echo $idOrdenCompra; ?>);
                                                           return false;"
                                           class='button' style="float: right"/>
                                </td>                        
                            </tr>
                        </table>
                    <?php } ?>
                    <div id="divListaEntrega">
                        <table style="border: 1px solid black;border-collapse: collapse;width:100%">
                            <tr>
                                <th align='center' scope='row' class="celda" style="width:12%" rowspan="2">No parte / Modelo</th>
                                <th align='center' scope='row' class="celda" style="width:8%" rowspan="2">No parte anterior</th>
                                <th align='center' scope='row' class="celda" style="width:15%" rowspan="2">Descripción</th>
                                <th align='center' scope='row' class="celda" style="width:8%" rowspan="2">NoSerie</th>
                                <th align='center' scope='row' class="celda" style="width:15%" colspan="3">cantidad</th>
                                <th align='center' scope='row' class="celda" style="width:8%" rowspan="2">Almacén</th>
                                <th align='center' scope='row' class="celda" style="width:18%" colspan="2">Ubicación</th>
                                <th align='center' scope='row' class="celda" style="width:8%" rowspan="2">Folio</th>
                                <?php
                                if ($dividir_recepcion) {
                                    echo "<th align='center' scope='row' class='celda' style='width:8%' rowspan='2'>Aceptar almacén</th>";
                                }
                                ?>
                            </tr>   
                            <tr>
                                <th  align='center' scope='row' class="celda">Recibida</th>
                                <th  align='center' scope='row' class="celda">Disponible</th>
                                <th  align='center' scope='row' class="celda">Entrada</th>
                                <th  align='center' scope='row' class="celda">Anterior</th>
                                <th  align='center' scope='row' class="celda">Nueva</th>
                            </tr>
                            <?php
                            if ($idOrdenCompra != "") {
                                $consultaEntregas = "SELECT de.Id_detalle_entrada AS ID,e.NoParte AS eNoparte,e.Modelo AS modeloE,c.NoParte AS cNoParte,c.Modelo AS modeloC,c.NoParteAnterior,c.Descripcion AS desComp,SUBSTRING(e.Descripcion,1,40) AS desEquipo,de.FolioFactura,
                                    de.CantidadEntrada,de.Almacen AS idAlmacen,a.nombre_almacen,de.ubicacion,(SELECT CASE WHEN koc.NoParteComponente IS NOT NULL THEN 'C' ELSE 'E' END) AS tipo, de.NoSerie, de.Cancelado, de.RecibidoAlmacen,
                                    IF(ISNULL((SELECT SUM(kdoc.Cantidad) FROM k_det_entr_oc_almacen kdoc WHERE kdoc.Id_detalle_entrada=de.Id_detalle_entrada)),0,(SELECT SUM(kdoc.Cantidad) FROM k_det_entr_oc_almacen kdoc WHERE kdoc.Id_detalle_entrada=de.Id_detalle_entrada)) AS cantidad_recibida,
                                    (SELECT GROUP_CONCAT(DISTINCT(kdoc.Ubicacion) SEPARATOR ' ; ') FROM k_det_entr_oc_almacen kdoc WHERE kdoc.Id_detalle_entrada=de.Id_detalle_entrada) AS ubicacion_gral,
                                    IF(de.RecibidoAlmacen=1,0,(CantidadEntrada-IF(ISNULL((SELECT SUM(kdoc.Cantidad) FROM k_det_entr_oc_almacen kdoc WHERE kdoc.Id_detalle_entrada=de.Id_detalle_entrada)),0,(SELECT SUM(kdoc.Cantidad) FROM k_det_entr_oc_almacen kdoc WHERE kdoc.Id_detalle_entrada=de.Id_detalle_entrada)))) AS restantes
                                    FROM k_detalle_entrada_orden_compra de LEFT JOIN k_orden_compra koc ON de.idKOrdenTrabajo=koc.IdDetalleOC LEFT JOIN c_almacen a ON de.Almacen=a.id_almacen LEFT JOIN c_equipo e ON koc.NoParteEquipo=e.NoParte 
                                    LEFT JOIN c_componente c ON koc.NoParteComponente=c.NoParte WHERE koc.IdOrdenCompra='$idOrdenCompra' ORDER BY restantes DESC;";
                                $queryEntregados = $catalogo->obtenerLista($consultaEntregas);
                                $i = 1;
                                while ($rs = mysql_fetch_array($queryEntregados)) {
                                    echo " <tr>";
                                    if ($rs['tipo'] == "C") {
                                        echo " <td align='center' scope='row' class='celda'>" . $rs['cNoParte'] . " / " . $rs['modeloC'] . "</td>";
                                        echo " <td align='center' scope='row' class='celda'>" . $rs['NoParteAnterior'] . "</td>";
                                        echo " <td align='center' scope='row' class='celda'>" . $rs['desComp'] . "</td>";
                                        echo " <td align='center' scope='row' class='celda'></td>";
                                    } else if ($rs['tipo'] == "E") {
                                        echo " <td align='center' scope='row' class='celda'>" . $rs['eNoparte'] . " / " . $rs['modeloE'] . "</td>";
                                        echo " <td align='center' scope='row' class='celda'></td>";
                                        echo " <td align='center' scope='row' class='celda'>" . $rs['desEquipo'] . "</td>";
                                        echo " <td align='center' scope='row' class='celda'>" . $rs['NoSerie'] . "</td>";
                                    }
                                    echo " <td align='center' scope='row'  class='celda'><input type='text' id='txt_cant_recibo_$i' name='txt_cant_recibo_$i' value='" . $rs['CantidadEntrada'] . "' readonly style='width:95%'/></td>";
                                    echo " <td align='center' scope='row'  class='celda'><input type='text' id='txt_cant_disponible_$i' name='txt_cant_disponible_$i' value='" . $rs['restantes'] . "' readonly style='width:95%'/></td>";


                                    echo " <td align='center' scope='row' class='celda'><input type='text' id='txt_cant_entrada_$i' name='txt_cant_entrada_$i' value='" . $rs['restantes'] . "' style='width:95%' onkeyup='validafila($i)'/><br/><div id='div_error_cantidad_$i'></div></td>";
                                    echo " <td align='center' scope='row' class='celda'>" . $rs['nombre_almacen'] . "</td>";
                                    echo " <td align='center' scope='row' class='celda' style='width:9%'>" . $rs['ubicacion_gral'] . "</td>";
                                    echo " <td align='center' scope='row' class='celda' style='width:9%'><input type='text' id='txt_ub_entrada_$i' name='txt_ub_entrada_$i' value='' style='width:95%'/></td>";
                                    echo " <td align='center' scope='row' class='celda'>" . $rs['FolioFactura'] . "</td>";

                                    if ($dividir_recepcion) {
                                        if ($rs['Cancelado'] == "1") {
                                            echo " <td align='center' scope='row'>Cancelado</td>";
                                        } else {
                                            if ($rs['restantes'] != "0" && $rs['RecibidoAlmacen'] != "1") {
                                                if ($rs['tipo'] == "C") {
                                                    echo "<input type='hidden' id='serie_$i' name='serie_$i' value='' />";
                                                    echo "<input type='hidden' id='parte_$i' name='parte_$i' value='" . $rs['cNoParte'] . "' />";
                                                } else {
                                                    echo "<input type='hidden' id='serie_$i' name='serie_$i' value='" . $rs['NoSerie'] . "' />";
                                                    echo "<input type='hidden' id='parte_$i' name='parte_$i' value='" . $rs['eNoparte'] . "' />";
                                                }
                                                echo "<input type='hidden' id='id_detalle_$i' name='id_detalle_$i' value='" . $rs['ID'] . "' />";
                                                echo "<input type='hidden' id='tipo_$i' name='tipo_$i' value='" . $rs['tipo'] . "' />";
                                                echo "<input type='hidden' id='cantidad_$i' name='cantidad_$i' value='" . $rs['CantidadEntrada'] . "' />";
                                                echo "<input type='hidden' id='almacen_$i' name='almacen_$i' value='" . $rs['idAlmacen'] . "' />";
                                                echo "<input type='hidden' id='ubicacion_$i' name='ubicacion_$i' value='" . $rs['ubicacion'] . "' />";

                                                echo " <td align='center' scope='row' class='celda'>
                                                    <input type='checkbox' id='recibido_$i' name='recibido_$i' fila='$i' value='$i1' onclick='validafila($i)'/>
                                                   </td>";
                                               // $i++;
                                            } else {
                                                echo " <td align='center' scope='row' class='celda'>Recibido</td>";
                                            }
                                        }
                                    }
                                    echo " </tr>";
                                     $i++;
                                }
                            }
                            ?> 
                        </table>
                        <?php if ($dividir_recepcion) { ?>
                            <table style="margin-left: 80%; width: 20%;">
                                <tr>           
                                    <td><input type="button" value="Seleccionar todo" onclick="seleccionaTodo();" class='button' style="float: right" /></td>
                                    <td><input type='button' value='Recibir en almacén' onclick= "recibirAlmacen(<?php echo $idOrdenCompra; ?>);
                                                    return false;" class='button' style="float: right"/></td>                        
                                </tr>
                            </table>
                        <?php } ?>
                    </div>
                    <br/>
                    <?php
                }
                ?>
                <input type="button" class="button" style="margin-left: 92%;" value="Regresar" onclick="cambiarContenidos('compras/lista_entrada_orden_compra.php', 'Entrada orden');"/>
        </div>
    </body>
</html>