<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Empresa.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Factura2.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Serie.class.php");
include_once("../WEB-INF/Classes/Factura.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("../WEB-INF/Classes/XMLReadAbraham.class.php");
include_once("../WEB-INF/Classes/Empresa.class.php");
include_once("../WEB-INF/Classes/Concepto.class.php");
include_once("../WEB-INF/Classes/PagoParcial.class.php");
include_once("../WEB-INF/Classes/AddendaDetalle.class.php");

$empresa = new Empresa();
$localidad = new Localidad();
$factura = new Factura();
$permiso = new PermisosSubMenu();
$catalogo = new Catalogo();
$factura_net = new Factura_NET();
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
            
    if(isset($_GET['id']) && !isset($_GET['nuevo'])){
        //Se crea una pre-factura con los datos de la factura a la que se le va a aplicar una NDC
        $query = $cat->obtenerLista("SELECT FacturaXML,PeriodoFacturacion,RFCEmisor,RFCReceptor,TipoArrendamiento,Folio FROM c_factura WHERE IdFactura=" .$_GET['id']);
        if ($rs = mysql_fetch_array($query)) {
            $factura_net->getRegistroById($_GET['id']);
            //echo $rs['FacturaXML'];
            $xml = new XMLReadAbraham();
            if (isset($rs['FacturaXML']) && !empty($rs['FacturaXML']) && !$xml->ReadXMLSinValidacion(str_replace("cfdi:", "", $rs['FacturaXML']))) {
                echo "Error: no se pudo leer el XML correctamente";
                return;
            }
            $empresa = new Empresa();
            $empresa->setRFC($rs['RFCEmisor']);
            $empresa->getRegistrobyRFC();
            $factura_aux = new Factura();
            $factura_aux->setIdEmpresa($empresa->getId());
            $factura_aux->setMetodoPago($xml->getMetodoDePago());
            $factura_aux->setFormaPago($xml->getFormaDePago());
            $factura_aux->setPeriodoFacturacion($rs['PeriodoFacturacion']);
            $factura_aux->setIdSerie(1);
            $factura_aux->setTipoComprobante("egreso");
            $factura_aux->setNdc($ndc);
            $cliente = new Cliente();
            if(!$cliente->getRegistroByRFC($rs['RFCReceptor'])){
                echo "<br/>Error: No se encontró ningún cliente con el RFC <b>".$rs['RFCReceptor']."</b><br/>";
            }
            $factura_aux->setRFCReceptor($cliente->getClaveCliente());
            $factura_aux->setRFCEmisor($empresa->getId());
            $factura_aux->setUsuarioCreacion($_SESSION['user']);
            $factura_aux->setUsuarioUltimaModificacion($_SESSION['user']);
            $factura_aux->setPantalla("PHP Copiar Factura Lectura");
            $factura_aux->setId_TipoFactura(1);
            $factura_aux->setCFDI33(0);
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
                foreach ($xml->getConceptos() as $val) {
                    $concepto->setCantidad($val[0]);
                    if ($val[1] == 0) {
                        $concepto->setUnidad("Servicio");
                    } else {
                        $concepto->setUnidad($val[1]);
                    }
                    $concepto->setDescripcion($val[2]);
                    $concepto->setPrecioUnitario($val[3]);
                    $concepto->nuevoRegistro();
                    $subtotal += ($val[0] * $val[3]);
                }

                //Verificamos que la suma de la NDC más los pagos parciales no excedan el total de la factura
                $total_ndc = $subtotal * $iva;

                $pago = new PagoParcial();

                if($pago->verificaPagoMayor(round($total_ndc,2), $_GET['id'], 0)){
                    echo "<h2>Atención: El pago por $".round($total_ndc,2)." de ésta NDC supera el monto total de la factura ".$rs['Folio'].", revisar los pagos parciales</h2>";

                }

                $_GET['id'] = $factura_aux->getIdFactura();
            } else {
                echo "Error: La pre-factura no se pudo crear";
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
    $empresa->setId($factura->getRFCEmisor());
    $empresa->getRegistrobyID();
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
<script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/alta_facturacion.js"></script>
<form id="altaFacturaform">    
    <table class="Tablas">
        <tr>
            <td style=" vertical-align:top; ">
                <?php if (isset($_GET['id']) && $_GET['id'] != "") { ?>
                    Ver PDF<br/>
                    <a href='WEB-INF/Controllers/facturacion/Controller_PDF_Factura.php?id=<?php
                    echo $_GET['id'];
                    if ($ndc) {
                        echo "&ndc=1";
                    }
                    ?>' target='_blank'>
                        <img src='resources/images/pdf_descarga.png' title='PDF Pre-Factura' style='width: 32px; height: 32px;'/>
                    </a>
                <?php } ?>
            </td>
            <td style=" vertical-align:top; text-align:right;">
                <table style=" width:100%" id="t_datos_grales">
                    <tr>
                        <td class="TitulosEncabezados">LUGAR DE EXPEDICIÓN</td>
                        <td class="TitulosEncabezados">METODO DE PAGO</td>
                        <td class="TitulosEncabezados">FORMA DE PAGO</td>
                        <td class="TitulosEncabezados">PERÍODO</td>
                    </tr>
                    <tr id="table_row_1">
                        <td style=" text-align:left; vertical-align:top" class="Etiquetas">
                            <input name="LugarExpedicion"  id="LugarExpedicion" type="text" value="" id="LugarExpedicion" style="width:200px;" disabled="disabled"/>
                            <br/><br/> Tipo factura:                           
                            <select id="TipoArrendamiento" name='TipoArrendamiento'>
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
                        </td>
                        <td style=" text-align:left;" class="Etiquetas">   
                            <?php
                            if ($disabled != "") {
                                echo "<input type='hidden' id='MetodoPago' name='MetodoPago' value='" . $factura->getMetodoPago() . "'/>";
                            }
                            ?>
                                <select name="MetodoPago<?php echo $post_fijo; ?>" id="MetodoPago<?php echo $post_fijo; ?>" <?php
                                    if ($factura->getMetodoPago() != "") {
                                        echo $disabled;
                                    }?> >
                                    <option value="" >Seleccione Metodo de Pago</option>
                                    <?php
                                        $result = $catalogo->getListaAlta("c_formapago", "IdFormaPago");
                                        while($rs = mysql_fetch_array($result)){
                                            $s = "";
                                            
                                            if ($factura->getMetodoPago() == $rs['Nombre']){
                                                $s = "selected='selected'";
                                            }
                                            echo "<option value='".$rs['Nombre']."' $s>".$rs['Nombre']." - ".$rs['Descripcion']."</option>";
                                        }
                                    ?>                                    
                                </select>                                
                            <span id="MetodoPago2" style="color:#B40404;display:none;"></span>
                            <br />
                            <br /> NumCtaPago: <input name="NumCtaPago" type="text" id="NumCtaPago" value="<?php echo $factura->getNumCtaPago() ?>" style="width:140px;" <?php if ($factura->getMetodoPago() != "") echo $disabled; ?>/>
                        </td>
                        <td style=" text-align:left; vertical-align:top;" class="Etiquetas" >
                            <?php
                            if ($disabled != "") {
                                echo "<input type='hidden' id='FormaPago' name='FormaPago' value='" . $factura->getFormaPago() . "'/>";
                            }
                            ?>
                            <select name="FormaPago<?php echo $post_fijo; ?>" id="FormaPago<?php echo $post_fijo; ?>" <?php
                            if ($factura->getFormaPago() != "") {
                                echo $disabled;
                            }
                            ?>>
                                <option value="Pago en una sola exhibicion" <?php if ($factura->getFormaPago() == "Pago en una sola exhibicion") echo "selected" ?>>Pago en una sola exhibicion</option>
                                <option value="Pago en parcialidades" <?php if ($factura->getFormaPago() == "Pago en parcialidades") echo "selected" ?>>Pago en parcialidades</option>
                                <!--<option value="Pago de cobrado" <?php //if ($factura->getFormaPago() == "Pago de cobrado") echo "selected" ?>>Pago de cobrado</option>-->
                            </select>
                            <br/><br/>
                            <select name="Serie" id="Serie">
                                <option value="" >Seleccione un prefijo (Serie)</option>
                                <?php
                                    if(!$ndc){
                                        $result = $catalogo->obtenerLista("SELECT * FROM c_serie WHERE IdSerie <> 1;");
                                    }else{
                                        $result = $catalogo->obtenerLista("SELECT * FROM c_serie WHERE IdSerie = 1;");
                                    }
                                    while($rs = mysql_fetch_array($result)){                                        
                                        $s = "";                                              
                                        if ( ($factura->getIdSerie() == $rs['IdSerie']) || ($ndc && $rs['IdSerie'] == "1")){
                                            $s = "selected='selected'";
                                        }                                        
                                        echo "<option value='".$rs['IdSerie']."' $s>".$rs['Prefijo']."</option>";
                                    }
                                ?>                                    
                            </select>
                        </td> 
                        <?php
                        $result = $factura->getMultiPeriodos();
                        $numero_periodos = 1;

                        if ($factura->getIdFactura() == NULL || mysql_num_rows($result) == 0) {//Si no hay multi-periodos
                            echo '<td style=" text-align:left; vertical-align:top;" class="Etiquetas" >
                                    <input type="text" id="periodo_facturacion_' . $numero_periodos . '" name="periodo_facturacion_' . $numero_periodos . '" value="';
                            if ($factura->getFechaFacturacion() != "") {
                                echo substr($factura->getFechaFacturacion(), 0, 10);
                            }
                            echo '"';
                            if (!$permiso->tienePermisoEspecial($_SESSION['idUsuario'], 14)) {
                                echo "readonly='readonly'";
                            }
                            echo " />";
                            if ($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 14)) {
                                echo '<input type="image" src="resources/images/add.png" title="Agregar otro periodo" onclick="agregarPeriodo(); return false;" />';
                            }
                            echo '</td>';
                        } else {
                            while ($rs = mysql_fetch_array($result)) {
                                if ($numero_periodos > 1) {
                                    echo "<tr><tr id='table_row_" . $numero_periodos . "'><td></td><td></td><td></td>";
                                }
                                echo '<td style=" text-align:left; vertical-align:top;" class="Etiquetas" >
                                        <input type="text" id="periodo_facturacion_' . $numero_periodos . '" name="periodo_facturacion_' . $numero_periodos . '" value="';
                                echo $rs['Periodo'];
                                echo '"';
                                if (!$permiso->tienePermisoEspecial($_SESSION['idUsuario'], 14)) {
                                    echo "readonly='readonly'";
                                }
                                echo " />";
                                if ($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 14)) {
                                    echo '<input type="image" src="resources/images/add.png" title="Agregar otro periodo" onclick="agregarPeriodo(); return false;" />';
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
                    </tr>                    
                </table>
            </td>
        </tr>        
        <tr>
            <td colspan="2">
                <fieldset style=" padding-top:0;">
                    <legend style=" padding-bottom:0px;">EMISOR</legend>
                    <table style=" width:100%">
                        <tr>
                            <td class="Etiquetas"><span style=" font-size:18px; color:Red;">*</span>RFC:</td>
                            <td>
                                <input type="text" id="RFCEmisorA" name="RFCEmisorA" disabled="disabled" style="width:200px;" value="<?php echo $empresa->getRFC(); ?>" />
                                <input type="hidden" id="RFCEmisor" name="RFCEmisor"  value="<?php echo $empresa->getId(); ?>"/>
                            </td>
                            <td class="Etiquetas" ><div style="width:150px;"></div><span style=" font-size:18px; color:Red;"></span></td>
                            <td>
                            </td>
                        </tr>
                        <tr>
                            <td class="Etiquetas"><span style=" font-size:18px; color:Red;">*</span>Nombre:</td>
                            <td class="Etiquetas">
                                <textarea name="NombreEmisor" rows="2" cols="20" id="NombreEmisor" disabled="disabled" class="aspNetDisabled" style="width:250px;"><?php echo $empresa->getRazonSocial(); ?></textarea>
                                <span id="NombreEmisor" style="color:#B40404;display:none;"></span>

                            <td class="Etiquetas"><span style=" font-size:18px; color:Red;">*</span>Regimen Fiscal:</td>
                            <td>
                                <textarea name="RegimenFiscal" rows="2" cols="20" id="RegimenFiscal" disabled="disabled" class="aspNetDisabled" style="width:250px;"><?php echo $empresa->getRegimenFiscal(); ?></textarea>
                            </td>
                        </tr>

                    </table>
                </fieldset>
            </td>
        </tr>
        <tr>
            <td colspan="2" style=" vertical-align:top;">
                <fieldset>
                    <legend>RECEPTOR</legend>
                    <table style=" width:100%">
                        <tr>
                            <td class="Etiquetas"><span style=" font-size:18px; color:Red;">*</span>Cliente:</td>
                            <td>
                                <select name="RFCReceptor" type="text" id="RFCReceptor" style="width:250px;" onchange="cargarEmisor('RFCReceptor');">
                                    <?php if ($factura->getRFCReceptor() == "") { ?>
                                        <option value="">Seleccione el Receptor</option>
                                        <?php
                                    }

                                    $consulta = "SELECT * FROM c_cliente AS c ORDER BY c.NombreRazonSocial";
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
                            </td>
                        </tr>
                        <?php if (isset($_GET['id']) && $_GET['id'] != "") { ?>
                            <tr>
                                <td>

                                </td>
                                <td>
                                    <table style="width: 100%;">
                                        <tr>
                                            <td>Calle:</td>
                                            <td><input type="text" value="<?php echo $localidad->getCalle(); ?>" disabled="disabled"/></td>
                                            <td>No. Ext:</td>
                                            <td><input type="text" value="<?php echo $localidad->getNoExterior(); ?>" disabled="disabled"/></td>
                                            <td>No. Int:</td>
                                            <td><input type="text" value="<?php echo $localidad->getNoInterior(); ?>" disabled="disabled"/></td>                        
                                        </tr>
                                        <tr>
                                            <td>Colonia:</td>
                                            <td><input type="text" value="<?php echo $localidad->getColonia(); ?>" disabled="disabled"/></td>
                                            <td>Delegaci&oacute;n:</td>
                                            <td><input type="text" value="<?php echo $localidad->getDelegacion(); ?>" disabled="disabled"/></td>
                                            <td>C.P:</td>
                                            <td><input type="text" value="<?php echo $localidad->getCodigoPostal(); ?>" disabled="disabled"/></td>
                                        </tr>
                                    </table>
                                </td>            
                            </tr>                    
                        <?php } ?>
                        <div id="cargarRFCinfoReceptor">

                        </div>
                    </table>
                </fieldset>
            </td>
        </tr>
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
        <tr id="botones">
            <td style=" text-align:right;" colspan="2">                
                <input type="button" name="Cancelar" class="boton" value="Cancelar" id="Cancelar" onclick="cambiarContenidos('<?php echo $page_back; ?>');
                        return false;"/>                
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="submit" name="GuardarPrefactura" class="boton" value="Guardar" id="GuardarPrefactura" />
            </td>
        </tr>
    </table>

    <?php
        if (isset($_GET['id']) && $_GET['id'] != "") {
            $detalle_addenda = new AddendaDetalle();
            $result = $detalle_addenda->getRegistrosByCliente($factura->getRFCReceptor(), true);
            if(mysql_num_rows($result) > 0){
                echo "<fieldset>";
                echo "<legend>Addenda</legend>";
                echo "<table>";
                $i=0;
                while ($rs = mysql_fetch_array($result)){
                    echo "<tr>";
                    echo "<td>".$rs['campo']."</td>";
                    echo "<td>"
                        . "<input type='text' id='addenda_".$rs['id_kaddenda']."' name='addenda_".$rs['id_kaddenda']."' maxlength='150'/>"
                        . "<input type='hidden' id='kaddenda_$i' name='kaddenda_$i' value='".$rs['id_kaddenda']."'/>"
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
        var direccion = "facturacion/ConceptosFacturacion.php";
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