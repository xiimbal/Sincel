<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/DatosFacturacionEmpresa.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Factura2.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Serie.class.php");
include_once("../WEB-INF/Classes/Factura.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("../WEB-INF/Classes/XMLReadAbraham2.class.php");
include_once("../WEB-INF/Classes/XMLReadAbraham.class.php");
include_once("../WEB-INF/Classes/Empresa.class.php");
include_once("../WEB-INF/Classes/Concepto.class.php");
include_once("../WEB-INF/Classes/PagoParcial.class.php");
include_once("../WEB-INF/Classes/AddendaDetalle.class.php");
include_once("../WEB-INF/Classes/RegimenFiscal.class.php");
include_once("../WEB-INF/Classes/UsoCFDI.class.php");

$empresa = new DatosFacturacionEmpresa();
$localidad = new Localidad();
$factura = new Factura();
$permiso = new PermisosSubMenu();
$catalogo = new Catalogo();
$factura_net = new Factura_NET();
$regimenFiscal = new RegimenFiscal();
$iva = 1.16;

$permiso_editar = $permiso->tienePermisoEspecial($_SESSION['idUsuario'], 14);
$disabled = "";
$post_fijo = ""; //Cuando se ponen los select como disabled, se les anexa un post fijo, para que en el post se manden los valores de los hidden

if (isset($_GET['page'])) {
    $page_back = $_GET['page'];
    if (isset($_POST['page']) && isset($_POST['filter'])) {
        $filter = str_replace(" ", "_XX__XX_", $_POST['filter']);
        $page_back.= ("?page=" . $_POST['page'] . "&filter=$filter");
    }
} else {
    $page_back = "facturacion/ReporteFacturacion.php";
}

$ndc = false; //Por default no se genera ndc, sino prefactura.

if (isset($_GET['param1']) && $_GET['param1'] == "egreso") {
    $cat = new CatalogoFacturacion();
    $ndc = true;

    if (isset($_GET['id']) && !isset($_GET['nuevo'])) {
        //Se crea una pre-factura con los datos de la factura a la que se le va a aplicar una NDC
        $query = $cat->obtenerLista("SELECT cfdiTimbrado,PeriodoFacturacion,RFCEmisor,RFCReceptor,TipoArrendamiento,Folio,CFDI33 FROM c_factura WHERE IdFactura=" . $_GET['id']);
        if ($rs = mysql_fetch_array($query)) {
            $factura_net->getRegistroById($_GET['id']);
            if ($rs['CFDI33'] == "1") {
                $xml = new XMLReadAbraham2();
                if (isset($rs['cfdiTimbrado']) && !empty($rs['cfdiTimbrado']) && !$xml->ReadXML(str_replace("cfdi:", "", $rs['cfdiTimbrado']))) {
                    echo "Error: no se pudo leer el XML correctamente";
                    return;
                }
            } else {
                $xml = new XMLReadAbraham();
                if (isset($rs['cfdiTimbrado']) && !empty($rs['cfdiTimbrado']) && !$xml->ReadXMLSinValidacion(str_replace("cfdi:", "", $rs['cfdiTimbrado']))) {
                    echo "Error: no se pudo leer el XML correctamente";
                    return;
                }
            }
            $empresa = new DatosFacturacionEmpresa();
            $empresa->getRegistroByRFC($rs['RFCEmisor']);

            $factura_aux = new Factura();
            $factura_aux->setIdEmpresa($empresa->getIdDatosFacturacionEmpresa());
            if ($rs['CFDI33'] == "1") {
                $factura_aux->setMetodoPago(5);
                $factura_aux->setFormaPago($factura->getIdFormaPago($xml->getFormaDePago()));
                $factura_aux->setIdUsoCFDI($factura->getIdUsoCFDIByClave($xml->getUsoCFDI()));
            } else {

                $factura_aux->setMetodoPago($factura->getIdMetodoPagoPorNombre($xml->getFormaDePago()));
                $factura_aux->setFormaPago($factura->getIdFormaPagoPorNombre($xml->getMetodoDePago()));
                $factura_aux->setIdUsoCFDI(3);
            }
            $factura_aux->setPeriodoFacturacion($rs['PeriodoFacturacion']);
            $factura_aux->setIdSerie(1);
            $factura_aux->setTipoComprobante("egreso");
            $factura_aux->setNdc($ndc);
            $cliente = new Cliente();
            if (!$cliente->getRegistroByRFC($rs['RFCReceptor'])) {
                echo "<br/>Error: No se encontró ningún cliente con el RFC <b>" . $rs['RFCReceptor'] . "</b><br/>";
            }
            $factura_aux->setRFCReceptor($cliente->getClaveCliente());
            $factura_aux->setRFCEmisor($empresa->getIdDatosFacturacionEmpresa());
            $factura_aux->setUsuarioCreacion($_SESSION['user']);
            $factura_aux->setUsuarioUltimaModificacion($_SESSION['user']);
            $factura_aux->setPantalla("PHP Copiar Factura Lectura");
            $factura_aux->setId_TipoFactura(1);
            $factura_aux->setCFDI33(1);
            $factura_aux->setTipoRelacion(2);
            if (isset($rs['TipoArrendamiento']) && $rs['TipoArrendamiento'] != "") {
                $factura_aux->setTipoArrendamiento($rs['TipoArrendamiento']);
            }

            if ($factura_aux->NuevaPreFactura()) {
                $concepto = new Concepto();
                $concepto->setIdFactura($factura_aux->getIdFactura());
                $concepto->setPantalla("PHP Controller_nuevo_concepto");
                $concepto->setFechaCreacion("NOW()");
                $concepto->setFechaUltimaModificacion("NOW()");
                $concepto->setUsuarioCreacion($_SESSION['user']);
                $concepto->setTipo("null");
                $concepto->setId_articulo("null");
                $concepto->setUsuarioUltimaModificacion($_SESSION['user']);
                $subtotal = 0;

                /* Datos de los conceptos, para la version 3.3 ya sólo aplica una partida con conceptos particulares */
                if ($rs['CFDI33'] == "1") {
                    $concepto->setCantidad(1);
                    $concepto->setDescripcion("Servicios de facturación");
                    $concepto->setPrecioUnitario(($factura_net->getTotal() / 1.16));
                    $concepto->setUnidad("");
                    $claveUnidad = "ACT";
                    $claveProducto = "84111506";
                    $IdEmpresaProductoSAT = $factura->getIdProductoSAT($claveUnidad, $claveProducto, $empresa->getIdDatosFacturacionEmpresa());
                    if ($IdEmpresaProductoSAT != NULL && !empty($IdEmpresaProductoSAT)) {
                        $concepto->setIdEmpresaProductoSAT($IdEmpresaProductoSAT);
                    } else {
                        $idClave = 51542;
                        $idUnidad = 263;
                        $insert = "INSERT INTO k_empresaproductosat VALUES(0," . $empresa->getIdDatosFacturacionEmpresa() . ",$idClave,$idUnidad,'sistemas',NOW(),'sistemas',NOW(),'Facturar Reporte Lectura');";
                        //echo $insert;
                        $IdEmpresaProductoSAT = $catalogo->insertarRegistro($insert);
                        $concepto->setIdEmpresaProductoSAT($IdEmpresaProductoSAT);
                    }
                    if (!$concepto->nuevoRegistro()) {
                        echo "<br/>Error: no se registro la partida " . $concepto->getDescripcion();
                    }
                    $subtotal += ($concepto->getPrecioUnitario() * $concepto->getCantidad());
                } else {
                    $concepto->setCantidad(1);
                    $concepto->setDescripcion("Servicios de facturación");
                    $concepto->setPrecioUnitario(($factura_net->getTotal() / 1.16));
                    $concepto->setUnidad("");
                    $claveUnidad = "ACT";
                    $claveProducto = "84111506";
                    $IdEmpresaProductoSAT = $factura->getIdProductoSAT($claveUnidad, $claveProducto, $empresa->getIdDatosFacturacionEmpresa());
                    if ($IdEmpresaProductoSAT != NULL && !empty($IdEmpresaProductoSAT)) {
                        $concepto->setIdEmpresaProductoSAT($IdEmpresaProductoSAT);
                    } else {
                        $idClave = 51542;
                        $idUnidad = 263;
                        $insert = "INSERT INTO k_empresaproductosat VALUES(0," . $empresa->getIdDatosFacturacionEmpresa() . ",$idClave,$idUnidad,'sistemas',NOW(),'sistemas',NOW(),'Facturar Reporte Lectura');";
                        //echo $insert;
                        $IdEmpresaProductoSAT = $catalogo->insertarRegistro($insert);
                        $concepto->setIdEmpresaProductoSAT($IdEmpresaProductoSAT);
                    }
                    if (!$concepto->nuevoRegistro()) {
                        echo "<br/>Error: no se registro la partida " . $concepto->getDescripcion();
                    }
                    $subtotal += ($concepto->getPrecioUnitario() * $concepto->getCantidad());
//                    foreach ($xml->getConceptos() as $val) {
//                        $concepto->setCantidad($val[0]);
//                        $concepto->setDescripcion($val[2]);
//                        $concepto->setPrecioUnitario($val[3]);
//                        $concepto->setUnidad("");
//                        $idProductoEmpresa = 0;
//                        $idClave = 51334;
//                        if (strpos($val[2], 'RENTA') !== false) {
//                            $idClave = 50951;
//                        }
//
//                        $consulta = "SELECT IdEmpresaProductoSAT FROM k_empresaproductosat eps WHERE IdDatosFacturacionEmpresa = " . $empresa->getIdDatosFacturacionEmpresa() .
//                                " AND IdClaveProdServ = $idClave;";
//
//                        $result = $catalogo->obtenerLista($consulta);
//                        if (mysql_num_rows($result) > 0) {
//                            if ($rs = mysql_fetch_array($result)) {
//                                $idProductoEmpresa = $rs['IdEmpresaProductoSAT'];
//                            }
//                        } else {
//                            $insert = "INSERT INTO k_empresaproductosat VALUES(0," . $empresa->getIdDatosFacturacionEmpresa() . ",$idClave,700,'sistemas',NOW(),'sistemas',NOW(),'Facturar Reporte Lectura');";
//                            //echo $insert;
//                            $idProductoEmpresa = $catalogo->insertarRegistro($insert);
//                        }
//                        $concepto->setIdEmpresaProductoSAT($idProductoEmpresa);
//
//                        if (!$concepto->nuevoRegistro()) {
//                            echo "<br/>Error: no se registro la partida " . $concepto->getDescripcion();
//                        }
//                        $subtotal += ($val[0] * $val[3]);
//                    }
                }

                //Verificamos que la suma de la NDC más los pagos parciales no excedan el total de la factura
                $total_ndc = $subtotal * $iva;

                $pago = new PagoParcial();

                if ($pago->verificaPagoMayor(round($total_ndc, 2), $_GET['id'], 0)) {
                    echo "<h2>Atención: El pago por $" . round($total_ndc, 2) . " de ésta NDC supera el monto total de la factura " . $rs['Folio'] . ", revisar los pagos parciales</h2>";
                }

                $_GET['id'] = $factura_aux->getIdFactura();
            } else {
                echo "Error: 2 . La pre-factura no se pudo crear";
            }
        } else {
            //echo "Error: no se encontro el xml";
        }
    }
}

if (isset($_GET['id']) && $_GET['id'] != "") {
    $factura->setIdFactura($_GET['id']);
    $factura->getRegistrobyID();
    if ($factura->getIdDomicilioFiscal() != "") {
        $localidad->getLocalidadById($factura->getIdDomicilioFiscal());
    } else {
        $localidad->getLocalidadByClaveTipo($factura->getRFCReceptor(), "3");
    }

    $empresa->getRegistroById($factura->getRFCEmisor());
    if (!$ndc) {
        echo "<h3>Se está editando la pre-factura " . $factura->getFolio() . "</h3>";
    } else {
        echo "<h3>Se está creando una nota de crédito</h3>";
    }
    if (!$permiso_editar) {
        $disabled = "disabled='disabled'";
        $post_fijo = "2";
    }
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/alta_facturacion_33.js"></script>
<form id="altaFacturaform">  
<div class="container-fluid">
    <div class="form-row">
        <?php if (isset($_GET['id']) && $_GET['id'] != "") { ?>
            <div class="form-group col-md-3">
                Ver PDF<br/>
                <a href='WEB-INF/Controllers/facturacion/Controller_PDF_Factura.php?id=<?php
                echo $_GET['id'];
                if ($ndc) {
                    echo "&ndc=1&IdFacturaNET=" . $factura_net->getIdFactura();
                }
                ?>' target='_blank'>
                    <img src='resources/images/pdf_descarga.png' title='PDF Pre-Factura' style='width: 32px; height: 32px;'/>
                </a>
            </div>
        <?php } ?>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label>LUGAR DE EXPEDICIÓN</label>
                <input class="form-control" name="LugarExpedicion"  id="LugarExpedicion" type="text" value="" id="LugarExpedicion"  disabled="disabled"/>
            </div>
            <div class="form-group col-md-3">
                <label>METODO DE PAGO</label>
                <select class="form-control" name="MetodoPago<?php echo $post_fijo; ?>" id="MetodoPago<?php echo $post_fijo; ?>" <?php
                    if ($factura->getMetodoPago() != "") {
                        echo $disabled;
                    } ?> >
                        <option value="" >Seleccione Metodo de Pago</option>
                        <?php
                        $result = $catalogo->getListaAlta("c_metodopago", "ClaveMetodoPago");
                        while ($rs = mysql_fetch_array($result)) {
                            $s = "";
                            if ($factura->getMetodoPago() == $rs['IdMetodoPago']) {
                                $s = "selected='selected'";
                            }
                            echo "<option value='" . $rs['IdMetodoPago'] . "' $s>" . $rs['ClaveMetodoPago'] . " - " . $rs['MetodoPago'] . "</option>";
                        }
                    ?>
                </select>  
            </div>
            <div class="form-group col-md-3">
                <label>FORMA DE PAGO</label>
                <?php
                if ($disabled != "") {
                    echo "<input type='hidden' id='FormaPago' name='FormaPago' value='" . $factura->getFormaPago() . "'/>";
                }
                ?>
                <select class="form-control" name="FormaPago<?php echo $post_fijo; ?>" id="FormaPago<?php echo $post_fijo; ?>" <?php
                    if ($factura->getFormaPago() != "") {
                        echo $disabled;
                    } ?>>
                    <?php
                    $result = $catalogo->getListaAlta("c_formapago", "Nombre");
                    while ($rs = mysql_fetch_array($result)) {
                        $s = "";
                        if ($factura->getFormaPago() == $rs['IdFormaPago']) {
                            $s = "selected='selected'";
                        }
                        echo "<option value='" . $rs['IdFormaPago'] . "' $s>" . $rs['Nombre'] . " - " . $rs['Descripcion'] . "</option>";
                    }
                    ?>  
                    <!--<option value="Pago de cobrado" <?php //if ($factura->getFormaPago() == "Pago de cobrado") echo "selected"   ?>>Pago de cobrado</option>-->
                </select>
            </div>
            <div class="form-group col-md-3">
                <label>PERÍODO</label>
                <?php
                $result = $factura->getMultiPeriodos();
                $numero_periodos = 1;
                ?>
                <?php
                if ($factura->getIdFactura() == NULL || mysql_num_rows($result) == 0) {//Si no hay multi-periodos
                    ?>
                    <label>Uso CFDI: &nbsp;&nbsp;&nbsp;</label>
                    <select class="form-control" id="usoCFDI" name="usoCFDI">
                        <?php
                        $result = $catalogo->getListaAlta("c_usocfdi", "ClaveCFDI");
                        echo "<option value=''>Selecciona una opción</option>";
                        while ($rs = mysql_fetch_array($result)) {
                            $s = "";
                            if ((int) $factura->getIdUsoCFDI() == (int) $rs['IdUsoCFDI']) {
                                $s = "selected";
                            }
                            echo "<option value='" . $rs['IdUsoCFDI'] . "' $s>" . $rs['ClaveCFDI'] . " " . $rs['Descripcion'] . "</option>";
                        }
                        ?>
                    </select><br/>
                    <?php
                    echo '<input class="form-control" type="text" id="periodo_facturacion_' . $numero_periodos . '" name="periodo_facturacion_' . $numero_periodos . '" value="';
                    if ($factura->getFechaFacturacion() != "") {
                        echo substr($factura->getFechaFacturacion(), 0, 10);
                    }
                    echo '"';
                    if (!$permiso->tienePermisoEspecial($_SESSION['idUsuario'], 14)) {
                        echo "readonly='readonly'";
                    }
                    echo " />";
                    if ($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 14)) {
                        echo '<input type="image" src="resources/images/add.png" title="Agregar otro periodo" onclick="agregarPeriodo(); return false;" style="margin-bottom: -10px;" />';
                    }
                } else {
                    while ($rs = mysql_fetch_array($result)) {
                        if ($numero_periodos > 1) {
                            echo "<tr id='table_row_" . $numero_periodos . "'><td></td><td></td><td></td>";
                        }
                        echo '<td style=" text-align:left; vertical-align:top;" class="Etiquetas" >';
                        if ($numero_periodos == 1) {
                            ?>
                            <label>Uso CFDI:</label>
                            <select class="form-control" id="usoCFDI" name="usoCFDI">
                                <?php
                                $result = $catalogo->getListaAlta("c_usocfdi", "ClaveCFDI");
                                echo "<option value=''>Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($result)) {
                                    $s = "";
                                    if ((int) $factura->getIdUsoCFDI() == (int) $rs['IdUsoCFDI']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['IdUsoCFDI'] . "' $s>" . $rs['ClaveCFDI'] . " " . $rs['Descripcion'] . "</option>";
                                }
                                ?>
                            </select>
                            <?php
                        }
                        echo '<input type="text" class="form-control" id="periodo_facturacion_' . $numero_periodos . '" name="periodo_facturacion_' . $numero_periodos . '" value="';
                        echo $rs['Periodo'];
                        echo '"';
                        if (!$permiso->tienePermisoEspecial($_SESSION['idUsuario'], 14)) {
                            echo "readonly='readonly'";
                        }
                        echo " />";
                        if ($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 14)) {
                            echo '<input type="image" src="resources/images/add.png" title="Agregar otro periodo" onclick="agregarPeriodo(); return false;"  style="margin-bottom: -10px;"/>';
                            if ($numero_periodos > 1) {
                                echo "<input type='image' src='resources/images/Erase.png' title='Eliminar este periodo' onclick='borrarPeriodo(" . $numero_periodos . "); return false;'/>";
                            }
                        }
                        echo '</td>';
                        echo "</tr>";
                            $numero_periodos++;
                        }
                        $numero_periodos--;
                    }
                    ?>
            </div>
            <div class="form-group col-md-3">
                <label>Tipo factura:</label>
                <select class="form-control" id="TipoArrendamiento" name='TipoArrendamiento'>
                                <?php
                                $result = $catalogo->obtenerLista("SELECT IdTipoFactura, TipoFactura FROM `c_tipofacturaexp` WHERE Activo = 1 ORDER BY TipoFactura;");
                                while ($rs = mysql_fetch_array($result)) {
                                    $s = "";
                                    if ($rs['IdTipoFactura'] == $factura->getTipoArrendamiento()) {
                                        $s = "selected = 'selected'";
                                    }
                                    echo "<option value='" . $rs['IdTipoFactura'] . "' $s>" . $rs['TipoFactura'] . "</option>";
                                }
                                ?>                                
                            </select> 
            </div>
            <div class="form-group col-md-3">
                <?php
                if ($disabled != "") {
                    echo "<input type='hidden' id='MetodoPago' name='MetodoPago' value='" . $factura->getMetodoPago() . "'/>";
                }?>
                <label>NumCtaPago:</label>
                <input name="NumCtaPago" type="text" class="form-control" id="NumCtaPago" value="<?php echo $factura->getNumCtaPago() ?>"<?php if ($factura->getMetodoPago() != "") echo $disabled; ?>/>
                <select class="form-control" name="Serie" id="Serie">
                    <option value="" >Seleccione un prefijo (Serie)</option>
                    <?php
                    if (!$ndc) {
                        $result = $catalogo->obtenerLista("SELECT * FROM c_serie WHERE IdSerie <> 1;");
                    } else {
                        $result = $catalogo->obtenerLista("SELECT * FROM c_serie WHERE IdSerie = 1;");
                    }
                    while ($rs = mysql_fetch_array($result)) {
                        $s = "";
                        if (($factura->getIdSerie() == $rs['IdSerie']) || ($ndc && $rs['IdSerie'] == "1")) {
                            $s = "selected='selected'";
                        }
                        echo "<option value='" . $rs['IdSerie'] . "' $s>" . $rs['Prefijo'] . "</option>";
                    }
                    ?>                                    
                </select>
            </div>
            <?php
            if ($ndc) {
                ?>
                <div class="form-group col-md-3">
                <label>Tipo de relación de la NDC:</label>
                    <select class="form-control" id="TipoRelacion" name='TipoRelacion' required="required" style="width: 250px;" onchange="guardarFactura();">
                        <option value="">Selecciona el tipo de relación</option>
                        <?php
                        $result = $catalogo->getListaAltaTodo("c_tiporelacion", "Clave");
                        while ($rs = mysql_fetch_array($result)) {
                            $s = "";
                            if ($rs['IdTipoRelacion'] == $factura->getTipoRelacion()) {
                                $s = "selected = 'selected'";
                            }
                            echo "<option value='" . $rs['IdTipoRelacion'] . "' $s>" . $rs['Clave'] . " - " . $rs['Descripcion'] . "</option>";
                        }
                        ?>                                
                    </select>
                </div>       
                <?php
            }
            ?>
        </div>
    </div>
    <fieldset class="form-group">
        <legend>EMISOR</legend>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label><span style=" font-size:18px; color:Red;">*</span>RFC:</label>
                <input type="text" id="RFCEmisorA" name="RFCEmisorA" disabled="disabled" class="form-control" value="<?php echo $empresa->getRFC(); ?>" />
                <input type="hidden" id="RFCEmisor" name="RFCEmisor"  value="<?php echo $empresa->getIdDatosFacturacionEmpresa(); ?>"/>
                <span style=" font-size:18px; color:Red;"></span>
            </div>
            <div class="form-group col-md-4">
                <label><span style=" font-size:18px; color:Red;">*</span>Nombre:</label>
                <textarea name="NombreEmisor" id="NombreEmisor" disabled="disabled" class="form-control"><?php echo $empresa->getRazonSocial(); ?></textarea>
                <span id="NombreEmisor" style="color:#B40404;display:none;"></span>
            </div>
            <div class="form-group col-md-4">
                <label><span style=" font-size:18px; color:Red;">*</span>Regimen Fiscal:</label>
                <textarea name="RegimenFiscal" id="RegimenFiscal" disabled="disabled" class="form-control"><?php
                $regimenFiscal->getRegistroById($empresa->getRegimenFiscal());
                echo $regimenFiscal->getDescripcion();
                ?></textarea>
            </div>
        </div>
    </fieldset>
    <fieldset class="form-group">
        <legend>RECEPTOR</legend>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label><span style=" font-size:18px; color:Red;">*</span>Cliente:</label>
                <select class="form-control" name="RFCReceptor" type="text" id="RFCReceptor" class="form-control" onchange="cargarEmisor('RFCReceptor');"> <?php if ($factura->getRFCReceptor() == "") { ?>
                        <option value="">Seleccione el Receptor</option>
                    <?php }
                    $consulta = "SELECT * FROM c_cliente AS c 
                    INNER JOIN c_datosfacturacionempresa AS dfe ON dfe.IdDatosFacturacionEmpresa = c.IdDatosFacturacionEmpresa AND dfe.cfdi33 = 1
                    ORDER BY c.NombreRazonSocial";
                    $query = $catalogo->obtenerLista($consulta);
                    if ($factura->getRFCReceptor() != "") {
                        while ($rs = mysql_fetch_array($query)) {
                            if ($rs['ClaveCliente'] == $factura->getRFCReceptor()) {
                                echo "<option value='" . $rs['ClaveCliente'] . "' selected='selected'>" . $rs['NombreRazonSocial'] . " (" . $rs['RFC'] . ")</option>";
                            }
                        }
                    } else {
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value='" . $rs['ClaveCliente'] . "'>" . $rs['NombreRazonSocial'] . " (" . $rs['RFC'] . ")</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <?php if (isset($_GET['id']) && $_GET['id'] != "") { ?>
                <div class="form-group col-md-3">
                    <label>Calle:</label>
                    <input type="text" class="form-control" value="<?php echo $localidad->getCalle(); ?>" disabled="disabled"/>
                </div>
                <div class="form-group col-md-3">
                    <label>No. Ext:</label>
                    <input type="text" class="form-control" value="<?php echo $localidad->getNoExterior(); ?>" disabled="disabled"/>
                </div>
                <div class="form-group col-md-3">
                    <label>No. Int:</label>
                    <input type="text" class="form-control" value="<?php echo $localidad->getNoInterior(); ?>" disabled="disabled"/>
                </div>
                <div class="form-group col-md-3">
                    <label>Colonia:</label>
                    <input type="text" class="form-control" value="<?php echo $localidad->getColonia(); ?>" disabled="disabled"/>
                </div>
                <div class="form-group col-md-3">
                    <label>Delegaci&oacute;n:</label>
                    <input type="text" class="form-control" value="<?php echo $localidad->getDelegacion(); ?>" disabled="disabled"/>
                </div>
                <div class="form-group col-md-3">
                    <label>C.P:</label>
                    <input type="text" class="form-control" value="<?php echo $localidad->getCodigoPostal(); ?>" disabled="disabled"/>
                </div>
                <div class="form-group col-md-3"><!-- Por observaciones de iliana el 22/11/2017 sólo se hacen descuentos por cantidad -->
                    <label>Descuento (%):</label>
                    <input type="number" id="Descuento_general" name="Descuento_general" value="<?php echo $factura->getDescuentos(); ?>" max="100.00" maxlength="6" onblur="guardarFactura();"/>
                </div> 
            <?php } ?>
        </div>          
    </fieldset>
    <div id="cargarRFCinfoReceptor">
    </div>
</div>  
<?php
if ($ndc) {
    echo "<input type='hidden' id='ndc' name='ndc' value='1'/>";
    if ($factura_net->getIdFactura() != NULL) {
        echo "<input type='hidden' id='IdFacturaNET' name='IdFacturaNET' value='" . $factura_net->getIdFactura() . "'/>";
    }
}
?>
<input type="hidden" id="numero_periodos" name="numero_periodos" value="<?php echo $numero_periodos; ?>"/>
<input type="hidden" id="permiso_periodo" name="permiso_periodo" value="<?php
if ($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 14)) {
    echo 1;
} else {
    echo 0;
}
?>"/>
<input type="hidden" id="idFactura" name="idFactura"  value="<?php
if (isset($_GET['id']) && $_GET['id'] != "") {
    echo $_GET['id'];
}
?>"/>
<input type="button" name="Cancelar" class="button btn btn-lg btn-block btn-outline-danger mt-3 mb-3"value="Cancelar" id="Cancelar" onclick="cambiarContenidos('<?php echo $page_back; ?>'); return false;"/>
<input type="submit" name="GuardarPrefactura" class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" value="Guardar" id="GuardarPrefactura" />
<input type="hidden" id="cfdi33" name="cfdi33" value="1">
<?php
if (isset($_GET['id']) && $_GET['id'] != "") {
    $detalle_addenda = new AddendaDetalle();
    $result = $detalle_addenda->getRegistrosByCliente($factura->getRFCReceptor(), true);
    if (mysql_num_rows($result) > 0) {
        echo "<fieldset class='form-group'>";
        echo "<legend>Addenda</legend>";
        echo "<table>";
        $i = 0;
        while ($rs = mysql_fetch_array($result)) {
            echo "<tr>";
            echo "<td>" . $rs['campo'] . "</td>";
            echo "<td>"
            . "<input type='text' id='addenda_" . $rs['id_kaddenda'] . "' name='addenda_" . $rs['id_kaddenda'] . "' maxlength='30'/>"
            . "<input type='hidden' id='kaddenda_$i' name='kaddenda_$i' value='" . $rs['id_kaddenda'] . "'/>"
            . "</td>";
            echo "</tr>";
            $i++;
        }
        echo "</table>";
        echo "</fieldset>";
    }
}
?>

<div id="divConceptos">

</div>
</form>

<script >
<?php if (isset($_GET['id']) && $_GET['id'] != "") { ?>
        CargarInfoReceptor();
        var direccion = "facturacion/ConceptosFacturacion_33.php";
        $("#botones").hide();
        var ndc_1 = 0;
        if ($("#ndc").length && $("#ndc").val() == "1") {
            ndc_1 = 1;
        }
        $("#divConceptos").load(direccion, {id: $("#idFactura").val(), 'ndc': ndc_1}, function () {
            finished();
        });
<?php } ?>
<?php if (isset($_GET['param1']) && $_GET['param1'] != "" && $_GET['param1'] != "egreso") { ?>
        $("#RFCReceptor").val('<?php echo $_GET['param1']; ?>');
        cargarEmisor('RFCReceptor');
<?php } ?>
<?php if (isset($_GET['ClaveCliente']) && $_GET['ClaveCliente'] != "") { ?>
        $("#RFCReceptor").val('<?php echo $_GET['ClaveCliente']; ?>');
        cargarEmisor('RFCReceptor');
<?php } ?>
</script>