<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Orden_Compra.class.php");
include_once("../WEB-INF/Classes/AlmacenConmponente.class.php");
$catalogo = new Catalogo();
$ordenCompra = new Orden_Compra();
$obj = new AlmacenComponente();
$pagina_listaRegresar = "compras/lista_orden_compra.php";
$idOrdenCompra = "";
$facturaEmisor = "";
$facturaReceptors = "";
$fecha = "";
$noOrden = "";
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
$observacion = "";
$direccionProv = "";
$direccionRazon = "";
$consultaComponente = "";
$copiado = "";
$almacen = "";
$tipoCambio = "";
$desactivarEstatus = 'disabled';
$no_pedido = "";
$ckDolar = "checked='checked'";
$FacturaTicket = "";
$DescripcionTicket = "";
$SubtotalTicket = "";
$TotalTicket = "";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/file_upload/jquery.iframe-transport.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/file_upload/jquery.fileupload.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/compras/alta_orden_compra.js"></script>
        <style>
            .border{border: 1px solid black;text-align: center}
        </style>

    </head>
    <body>
        <?php
        if (isset($_POST['id']) || $_GET['id']) {
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
            $observacion = $ordenCompra->getObservacion();
            $tipoCambio = $ordenCompra->getTipoCambio();
            $almacen = $ordenCompra->getAlmacen();
            $FacturaTicket = $ordenCompra->getFactura_Ticket();
            $DescripcionTicket = $ordenCompra->getDescripcion_Ticket();
            $SubtotalTicket = $ordenCompra->getSubtotal_Ticket();
            $TotalTicket = $ordenCompra->getTotal_Ticket();
            $desactivarEstatus = "";
            if (isset($_POST['copiado'])) {
                $estatus = "71";
                $copiado = "1";
            }
            if ($estatus != "71") {
                $disabled = "";
            }
            if ($peso == "0") {
                $peso = "";
            }
            if ($metros == "0") {
                $metros = "";
            }
            $no_pedido = $ordenCompra->getNo_pedido();

            $queryDolar = $catalogo->obtenerLista("SELECT DISTINCT(Dolar) AS Dolar FROM `k_orden_compra` WHERE IdOrdenCompra = $idOrdenCompra;");
            while ($rsDolar = mysql_fetch_array($queryDolar)) {
                if ($rsDolar['Dolar'] != "1") {
                    $ckDolar = "";
                }
            }

            $queryFactura = $catalogo->obtenerLista("SELECT df.RazonSocial,CONCAT(df.Calle,' ',df.NoExterior,', ',df.Colonia,', ',df.Delegacion,', ',df.Estado,', ',df.CP) AS direccion FROM c_datosfacturacionempresa df WHERE df.IdDatosFacturacionEmpresa='$facturaReceptors'");
            while ($rs = mysql_fetch_array($queryFactura)) {
                $direccionRazon = $rs['direccion'];
            }
        }
        ?>
        <br/><br/>
        <form id="frmOrdenCompra" name="frmOrdenCompra" action="/" method="POST">
            <table style="width: 100% "> 
                <tr>
                    <td style="width: 55% ">
                        <?php
                        if ($idOrdenCompra != "") {
                            echo "<h2>Orden de compra $idOrdenCompra</h2>";
                        }
                        ?>                        
                    </td>
                    <td style="width: 15% ">Copiar orden de compra</td>
                    <td style="width: 20% ">
                        <select id="slOrdenCompra" name="slOrdenCompra">
                            <option value="0">Seleccione una opción</option>   
                            <?php
                            $queryOc = $catalogo->obtenerLista("SELECT oc.Id_orden_compra FROM c_orden_compra oc ORDER BY oc.Id_orden_compra DESC");
                            while ($rs = mysql_fetch_array($queryOc)) {
                                $s = "";
                                if ($copiado != "" && $idOrdenCompra == $rs['Id_orden_compra']) {
                                    $s = "selected";
                                }
                                echo "<option value='" . $rs['Id_orden_compra'] . "' $s>" . $rs['Id_orden_compra'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td style="width: 10% "><input type="button" class="button" value="Copiar" onclick="CopiarOrdenCompra();
                            return false;"/></td>
                </tr>

                <tr>
                    <td></td>
                    <td>Importar archivo CSV</td>
                    <td>                        
                        <input id="fileupload" type="file" name="files[]" data-url="compras/php/" multiple class="button fileupload">
                        <div id="progress">
                            <div class="bar" style="width: 0%;"></div>
                        </div>
                    </td>
                    <td>                        
                        <input type="hidden" id="file_name" name="file_name" />
                    </td>
                </tr>    

            </table>
            <br/><br/>
            <table style="width: 100% ">                
                <tr>
                <tr>
                    <td style="width:10%">No. pedido:<span style="color: red">*</span></td>
                    <td style="width:20%"><input type="text" id="txt_pedido" name="txt_pedido" value="<?php echo $no_pedido; ?>" style="width: 100%"/></td>
                    <td style="width:70%"></td>
                </tr> <td style="width:10%">Proveedor:<span style="color: red">*</span></td>
                <td style="width:20%">
                    <select id="slProveedor" name="slProveedor" style="width: 100%" <?php echo $disabled; ?> onchange="mostrarDireccionProveedor(this.value)">
                        <option value="0">Selecione un opción</option>
                        <?php
                        $queryProveedor = $catalogo->getListaAlta("c_proveedor", "NombreComercial");
                        while ($rs = mysql_fetch_array($queryProveedor)) {
                            $s = "";
                            if ($facturaEmisor != "" && "'" . $facturaEmisor . "'" == "'" . $rs['ClaveProveedor'] . "'") {
                                $s = "selected";
                            }
                            echo "<option value='" . $rs['ClaveProveedor'] . "' $s>" . $rs['NombreComercial'] . "</option>";
                        }
                        ?>
                    </select>
                </td><td style="width:80%"><input type="hidden" id="txtdireccionProv" name="txtdireccionProv" value="<?php echo $direccionProv; ?>" style="width:100%" readonly/></td>
                </tr>
                <tr>
                    <td style="width:10%">Factura a:<span style="color: red">*</span></td>
                    <td style="width:20%">
                        <select id="slRazonSocial" name="slRazonSocial" style="width: 100%" <?php echo $disabled; ?> onchange="mostrarDireccionFacturacion(this.value)">
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
                    </td> <td style="width:80%"><input type="text" id="txtdireccionFactra" name="txtdireccionFactra" style="width:100%" readonly value="<?php echo $direccionRazon; ?>"/></td>
                </tr>
                <tr>
                    <td style="width:10%">Embarca a :<span style="color: red">*</span></td>
                    <td style="width:20%">
                        <select id="slAlmacen" name="slAlmacen" style="width: 100%" <?php echo $disabled; ?> onchange="mostrarDireccionAlmacen(this.value)">
                            <option value="0">Selecione un opción</option>
                            <?php
                            $queryAlamcen = $catalogo->obtenerLista("SELECT a.id_almacen,a.nombre_almacen FROM c_almacen a "
                                    . "WHERE a.Activo=1 AND a.id_almacen<>9 AND (TipoAlmacen=1 OR Surtir = 1) "
                                    . "ORDER BY a.nombre_almacen ASC");
                            while ($rs = mysql_fetch_array($queryAlamcen)) {
                                $s = "";
                                if ($almacen != "" && $almacen == $rs['id_almacen']) {
                                    $s = "selected";
                                }
                                echo "<option value='" . $rs['id_almacen'] . "' $s>" . $rs['nombre_almacen'] . "</option>";
                            }
                            ?>
                        </select>
                    </td><td style="width:80%"><input type="text" id="txtdireccionEmbarca" name="txtdireccionEmbarca" style="width:100%" value="<?php echo $embarque; ?>"/></td>
                </tr>
            </table>
            <table style="width:100%">
                <tr>
                    <td>Fecha pedido:<span style="color: red">*</span></td><td><input type="text" id="txtfechaOrden" name="txtfechaOrden" value="<?php echo $fecha; ?>" <?php echo $disabled; ?>/></td>
                    <td>Forma de pago:<span style="color: red">*</span></td>
                    <td>
                        <select id="slFormaPago" name="slFormaPago" style="width:175px" <?php echo $disabled; ?>>
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
                    <td>Estatus:<span style="color: red">*</span></td>
                    <td>
                        <select id="slEstatus" name="slEstatus" style="width: 175px" <?php echo $desactivarEstatus ?>>
                            <option value="0">Selecione un opción</option>
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
                    </td>
                </tr>
                <tr>
                    <td>No. cliente:</td><td><input type="text" readonly id="txtNoCliente" name="txtNoCliente" value="<?php echo $noCliente; ?>" <?php echo $disabled; ?>/></td>
                    <td>No. pedido distribuidor:</td><td><input type="text" id="txtPedidoProv" name="txtPedidoProv" value="<?php echo $noprov; ?>" <?php echo $disabled; ?>/></td>
                    <td>Notas:</td><td><input type="text" id="txtNotas" name="txtNotas" value="<?php echo $notas; ?>" <?php echo $disabled; ?>/></td>
                </tr>
                <tr>
                    <td>Transportista:</td>
                    <td>
                        <select id="slMensajeria" name="slMensajeria" style="width: 175px">
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
                    <td>Peso Kg:</td><td><input type="text" id="txtPeso" name="txtPeso" value="<?php echo $peso; ?>" <?php echo $disabled; ?>/></td>
                    <td>Metros cúbicos:</td><td><input type="text" id="txtMetros" name="txtMetros" value="<?php echo $metros; ?>" <?php echo $disabled; ?>/></td>
                </tr>
                <tr>
                    <td>Origen:</td><td><input type="text" id="txtOrigen" name="txtOrigen" value="<?php echo $origen; ?>" <?php echo $disabled; ?>/></td>
                    <td>Método de entrega:</td><td><input type="text" id="txtMetodo" name="txtMetodo" value="<?php echo $metodo; ?>" <?php echo $disabled; ?>/></td>
                    <td></td><td></td>
                </tr>
                <tr>      
                    <td>Dólar</td><td><input type="checkbox" id="ck_dolar" name="ck_dolar" <?php echo $ckDolar; ?>/></td>
                    <td style="width:10%">Tipo de cambio:</td><td style="width:10%"><input type="text" id="txtTipoCambio" name="txtTipoCambio" value="<?php echo $tipoCambio; ?>"/><br/><div id='div_err_tipo'></div></td>
                    <td></td><td></td>
                </tr>
            </table>
            <br/>        
            <table style="width:40%">

            </table>
            <br/>
            <fieldset>
                <legend>Componentes</legend>               
                <img class="imagenMouse" src="resources/images/add.png" title="Agregar componente" onclick="agregarComponenteOC('', '', '');" style="float: right; cursor: pointer;" />  
                <input type="button" id="btnImport" name="btnImport" class="button" 
                       value="Importar componentes backorder" title="Importar componentes backorder" style="float: left" onclick="importarComponentes();"/>
                <br/>
                <table id="tbComponente" style="width: 100%">
                    <tr>
                        <th class="border" style="width:15%">Tipo Componente</th>
                        <th class="border"  style="width:35%">Componente</th>
                        <th class="border" style="width:10%" >Entregadas</th>
                        <th class="border" style="width:10%" >Cantidad</th>
                        <th class="border"  style="width:10%">Precio venta</th>
                        <th class="border"  style="width:10%">Precio unitario</th> 
                        <th class="border"  style="width:10%">Eliminar</th>
                    </tr>
                    <?php
                    $contFila = 0;
                    if ($idOrdenCompra != "") {
                        $idTipoComp = Array();
                        $nomTipoComp = Array();
                        $queryTipoComp = $catalogo->obtenerLista("SELECT tc.IdTipoComponente,tc.Nombre FROM c_tipocomponente tc  ORDER BY tc.Orden ASC");
                        $Z = 0;
                        while ($rs = mysql_fetch_array($queryTipoComp)) {
                            $idTipoComp[$Z] = $rs['IdTipoComponente'];
                            $nomTipoComp[$Z] = $rs['Nombre'];
                            $Z++;
                        }
                        $noParteResC = Array();
                        $componenteResC = Array();
                        $queryEquipos = $catalogo->obtenerLista("SELECT c.NoParte,CONCAT(c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS componente FROM c_componente c ORDER BY componente ASC");
                        $x = 0;
                        while ($rs = mysql_fetch_array($queryEquipos)) {
                            $noParteResC[$x] = $rs['NoParte'];
                            $componenteResC[$x] = $rs['componente'];
                            $x++;
                        }
                        $queryDetalleComponente = $catalogo->obtenerLista("SELECT koc.IdDetalleOC,koc.NoParteComponente,koc.Cantidad,c.IdTipoComponente,CONCAT(c.Modelo,' // ',c.NoParte,' // ',c.Descripcion) AS componente,koc.PrecioUnitario,koc.PrecioTotal,koc.Dolar,c.PrecioDolares,koc.CantidadEntregada AS entregadas  FROM k_orden_compra koc INNER JOIN c_componente c ON koc.NoParteComponente=c.NoParte WHERE koc.IdOrdenCompra=$idOrdenCompra AND koc.NoParteComponente IS NOT NULL ORDER BY koc.NoParteEquipo ASC");
                        while ($rs = mysql_fetch_array($queryDetalleComponente)) {
                            $ck = "";
                            if ($rs['Dolar'] == "1") {
                                $ck = "checked";
                            }
                            ?>
                            <tr id="filaComponenteOC<?php echo $contFila; ?>">
                                <td align='center' scope='row'>
                                    <input type='hidden' id='txtidApartado<?php echo $contFila; ?>' name='txtidApartado<?php echo $contFila; ?>' style='width:100%' value=""/>
                                    <input type='hidden' id='txtidDetalleC<?php echo $contFila; ?>' name='txtidDetalleC<?php echo $contFila; ?>' style='width:100%' value="<?php echo $rs['IdDetalleOC']; ?>"/>
                                    <select style='width:100%' id="slTipoComponente<?php echo $contFila; ?>" name="slTipoComponente<?php echo $contFila; ?>" onchange="cargarSelectComponente('<?php echo $contFila; ?>', this.value)">
                                        <option value="0">Selecciona un tipo componente</option>
                                        <?php
                                        for ($y = 0; $y < count($idTipoComp); $y++) {
                                            $s = "";
                                            if ($rs['IdTipoComponente'] == $idTipoComp[$y]) {
                                                $s = "selected";
                                            }
                                            echo "<option value='" . $idTipoComp[$y] . "' $s>" . $nomTipoComp[$y] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </td> 
                                <?php
                                $precioComp = 0;
                                if ($rs['Dolar'] == "0") {
                                    $precioComp = (float) $rs['PrecioDolares'] * (float) $tipoCambio;
                                } else {
                                    $precioComp = (float) $rs['PrecioDolares'];
                                }
                                ?>
                                <td align='center' scope='row'><input type='text' id='txtComponentesOC<?php echo $contFila; ?>' name='txtComponentesOC<?php echo $contFila; ?>' style='width:99%' value="<?php echo $rs['componente']; ?>" onblur='costoComponente("<?php echo $contFila; ?>");'/></td>
                                <td align='center' scope='row'><input type='text' id='txtCantidad_entregada_comp<?php echo $contFila; ?>' name='txtCantidad_entregada_comp<?php echo $contFila; ?>' style='width:99%' value="<?php echo $rs['entregadas']; ?>" fila='<?php echo $contFila; ?>' readonly /></td>
                                <td align='center' scope='row'><input type='text' id='txtCantidadComponente<?php echo $contFila; ?>' name='txtCantidadComponente<?php echo $contFila; ?>' style='width:99%' value="<?php echo $rs['Cantidad']; ?>" fila='<?php echo $contFila; ?>' class='onckeyCantidad'/></td>
                                <td align='center' scope='row'><input type='text' id='txtPrecioVentaC<?php echo $contFila; ?>' name='txtPrecioVentaC<?php echo $contFila; ?>' style='width:100%' value="<?php echo $precioComp; ?>"/></td>
                                <td align='center' scope='row'><input type='text' id='txtPrecioUnitarioC<?php echo $contFila; ?>' name='txtPrecioUnitarioC<?php echo $contFila; ?>' style='width:99%' value="<?php echo $rs['PrecioUnitario']; ?>" class='onckeyC' fila='<?php echo $contFila; ?>'  onBlur='elimnarRulsPrecio("<?php echo $fila; ?>");'/></td>
                                <td align='center' scope='row'><img class='imagenMouse' src='resources/images/Erase.png' title='Eliminar componente' onclick='deleteRowComponenteOC(<?php echo $contFila; ?>)' style='cursor: pointer;' /></td>
                            </tr>
                            <?php
                            $contFila++;
                        }
                    }
                    ?>
                </table>

                <input type="hidden" id="txttamanoComponentes" name="txttamanoComponentes" value="<?php echo $contFila; ?>"/>
            </fieldset>
            <fieldset>
                <legend>Equipos</legend>              
                <img class="imagenMouse" src="resources/images/add.png" title="Agregar componente" onclick="agregarEquipoOC();" style="float: right; cursor: pointer;" />  
                <br/>
                <table id="tbEquipo" style="width: 100%">
                    <tr>
                        <th class="border" style="width:50%">Equipo</th>
                        <th class="border" style="width:10%">Entregadas</th>
                        <th class="border" style="width:10%">Cantidad</th>
                        <th class="border"  style="width:10%">Precio Venta</th> 
                        <th class="border"  style="width:10%">Precio unitario</th> 
                        <th class="border"  style="width:10%">Eliminar</th>
                    </tr>
                    <?php
                    $contFilaE = 0;
                    if ($idOrdenCompra != "") {
                        $queryEquipos = $catalogo->obtenerLista("SELECT e.NoParte,CONCAT(e.Modelo,' / ',e.NoParte,' / ',e.Descripcion) AS equipo FROM c_equipo e ORDER BY equipo ASC");
                        $noParteRes = Array();
                        $equipoRes = Array();
                        $x = 0;
                        while ($rs = mysql_fetch_array($queryEquipos)) {
                            $noParteRes[$x] = $rs['NoParte'];
                            $equipoRes[$x] = $rs['equipo'];
                            $x++;
                        }
                        $queryDetalleEquipo = $catalogo->obtenerLista("SELECT koc.IdDetalleOC,koc.NoParteEquipo,koc.Cantidad,CONCAT(e.Modelo,' / ',e.NoParte,' / ',e.Descripcion) AS equipo,koc.PrecioUnitario,
                                koc.PrecioTotal,koc.Dolar,e.PrecioDolares,koc.CantidadEntregada AS entregadas FROM k_orden_compra koc INNER JOIN c_equipo e ON koc.NoParteEquipo=e.NoParte 
                                WHERE koc.IdOrdenCompra=$idOrdenCompra AND koc.NoParteEquipo IS NOT NULL ORDER BY koc.NoParteEquipo ASC");
                        while ($rs = mysql_fetch_array($queryDetalleEquipo)) {
                            $s = "";
                            if ($rs['Dolar'] == "1") {
                                $s = "checked";
                            }
                            ?>
                            <tr id="filaEquipoOC<?php echo $contFilaE; ?>">
                                <td align='center' scope='row'>
                                    <input type='hidden' id='txtidDetalleE<?php echo $contFilaE; ?>' name='txtidDetalleE<?php echo $contFilaE; ?>' value="<?php echo $rs['IdDetalleOC']; ?>"/>
                                    <input type='text' id='txtEquipoOC<?php echo $contFilaE; ?>' name='txtEquipoOC<?php echo $contFilaE; ?>' style='width:100%' value="<?php echo $rs['equipo']; ?>" onBlur='costoEquipo("<?php echo $contFilaE; ?>")'/>
                                </td>
                                <td align='center' scope='row'><input type='text' readonly id='txtCantidad_entregada_eq<?php echo $contFilaE; ?>' name='txtCantidad_entregada_eq<?php echo $contFilaE; ?>' style='width:99%' value="<?php echo $rs['entregadas']; ?>"/></td>
                                <td align='center' scope='row'><input type='text'  id='txtCantidadEquipo<?php echo $contFilaE; ?>' name='txtCantidadEquipo<?php echo $contFilaE; ?>' style='width:99%' value="<?php echo $rs['Cantidad']; ?>" fila='<?php echo $contFilaE; ?>' class="onckeyCantidad_eq" /></td>
                                <?php
                                $precioComp = 0;
                                if ($rs['Dolar'] == "0") {
                                    $precioComp = (float) $rs['PrecioDolares'] * (float) $tipoCambio;
                                } else {
                                    $precioComp = (float) $rs['PrecioDolares'];
                                }
                                ?>
                                <td align='center' scope='row'><input type='text' id='txtPrecioVentaE<?php echo $contFilaE; ?>' name='txtPrecioVentaE<?php echo $contFilaE; ?>' value='<?php echo $precioComp; ?>'style='width:100%' /></td>
                                <td align='center' scope='row'><input type='text' id='txtPrecioUnitarioE<?php echo $contFilaE; ?>' name='txtPrecioUnitarioE<?php echo $contFilaE; ?>' style='width:99%' value="<?php echo $rs['PrecioUnitario']; ?>" class='onckeyE' fila='<?php echo $contFilaE; ?>' /></td>
                                <td align='center' scope='row'><img class='imagenMouse' src='resources/images/Erase.png' title='Eliminar componente' onclick='deleteRowEquipoOC(<?php echo $contFilaE; ?>)' style='cursor: pointer;'  /></td>
                            </tr>
                            <?php
                            $contFilaE++;
                        }
                    }
                    ?>
                </table>
                <input type="hidden" id="txttamanoEquipos" name="txttamanoEquipos" value="<?php echo $contFilaE; ?>"/>
            </fieldset>
            <?php if ($FacturaTicket == "1") { ?>
                <fieldset>
                    <legend>Ticket</legend>
                    <table style="width: 98%">
                        <tr>
                            <td><label for="descripcionTicket">Descripción</label></td>
                            <td><textarea cols="30" rows="4" id="descripcionTicket" readonly name="descripcionTicket" style="resize:none"><?php echo $DescripcionTicket ?></textarea></td>
                            <td><label for="subtotalTicket">Subtotal Ticket</label></td>
                            <td><input type="text" id="subtotalTicket" name="subtotalTicket" value="<?php echo $SubtotalTicket ?>" readonly></td>
                            <td><label for="totalTicket">Total Ticket</label></td>
                            <td><input type="text" id="totalTicket" name="totalTicket" value="<?php echo $TotalTicket ?>" readonly></td>
                        </tr>
                    </table>
                </fieldset>
            <?php } ?>
            <br/><br/>
            <table style="width:100%">
                <tr>
                    <td style="width:10%">Observaciones:</td>
                    <td style="width:90%"><textarea style="width:100%;height: 50px" id='txtObservaciones' name="txtObservaciones"><?php echo $observacion; ?></textarea></td>
                </tr>
            </table>
            <br/><br/>
            <input type="hidden" id="idEstatusAnterior" name="idEstatusAnterior" value="<?php echo $estatus; ?>"/>
            <input type="hidden" id="idOrden_compra" name="idOrden_compra" value="<?php echo $idOrdenCompra; ?>"/>
            <input type="hidden" id="copiado" name="copiado" value="<?php echo $copiado; ?>"/>
            <input type="submit" id="guardar" class="button" value="Guardar"/>
            <input type="button" class="button" value="Cancelar" onclick="window.location = 'principal.php?mnu=compras&action=lista_orden_compra';
                    return false;"/>          
        </form>
    </body>
</html>