<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Concepto.class.php");
include_once("../WEB-INF/Classes/EnLetras.class.php");
include_once("../WEB-INF/Classes/Factura2.class.php");
$catalogo = new Catalogo();
$concepto = new Concepto();
$factura = new Factura();
if (isset($_POST['concepto']) && $_POST['concepto'] != "") {
    $concepto->setIdConcepto($_POST['concepto']);
    $concepto->getRegistrobyID();
}
$fac_sust = false;
$ndc = false;
$tipo = "factura";
$factura->setIdFactura($_POST['id']);
$factura->getRegistrobyID();

$descuento = $factura->getDescuentos();
$productos_sat = array();
if (isset($_POST['fac_sust']) && $_POST['fac_sust'] == "1") {
    //echo "tipo ". $_POST['fac_sust'];
    $fac_sust = true;
    $tipo = "factura sustitución";
}

if(isset($_POST['ndc']) && $_POST['ndc'] == "1"){
    $ndc = true;
    $tipo = "nota de crédito";
}


?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/alta_Concepto_33.js"></script>
<table class="Tablas">
    <tr>
        <td colspan="2">
            <fieldset>
                <legend>CONCEPTOS</legend>
                <form id="formConceptos">
                    <table class="Tablas" id="ConceptoForm">
                        <tr>
                            <td class="TitulosEncabezados" style="width: 20%;">CANTIDAD</td>
                            <td class="TitulosEncabezados" style="width: 25%;">DESCRIPCIÓN</td>
                            <td class="TitulosEncabezados" style="width: 15%;">P.UNITARIO</td>
                            <td class="TitulosEncabezados" style="width: 25%;">PRODUCTO</td>
                            <td class="TitulosEncabezados" style="width: 15%;">DESCUENTO</td>
                        </tr>
                        <tr>
                            <td style=" vertical-align:top;">
                                <input style="width: 100px;" name="Cantidad" type="number" step="any" id="Cantidad"  value="<?php if ($concepto->getCantidad() != "") echo $concepto->getCantidad(); ?>"/>
                            </td>
                            <td style=" vertical-align:top;">
                                <textarea name="Descripcion" rows="2" cols="15" id="Descripcion" style="height:20px;width:200px;" ><?php if ($concepto->getDescripcion() != "") echo $concepto->getDescripcion(); ?></textarea>
                            </td>
                            <td style=" vertical-align:top;">
                                <input style="width: 100px;" name="PrecioUnitario" type="number" step="any"  id="PrecioUnitario"  value="<?php if ($concepto->getPrecioUnitario() != "") echo $concepto->getPrecioUnitario(); ?>"/>
                            </td>
                            <td style=" vertical-align:top;">
                                <select id="producto" name="producto" style="width: 250px;">
                                    <?php
                                        $where = "";
                                        if($ndc){
                                            //$where .= " WHERE cps.NDC = 1 ";
                                            $where = "";
                                        }
                                        $consulta = "SELECT cps.ClaveProdServ,cps.Descripcion, eps.IdEmpresaProductoSAT 
                                            FROM c_claveprodserv cps
                                            INNER JOIN k_empresaproductosat AS eps ON eps.IdClaveProdServ = cps.IdProdServ 
                                            AND cps.Activo = 1 AND IdDatosFacturacionEmpresa = ".$factura->getRFCEmisor() . $where;
                                        $result = $catalogo->obtenerLista($consulta);
                                        while($rs = mysql_fetch_array($result)){
                                            $productos_sat[$rs['IdEmpresaProductoSAT']] = $rs['ClaveProdServ']." ".$rs['Descripcion'];
                                            echo "<option value='".$rs['IdEmpresaProductoSAT']."'>".$rs['ClaveProdServ']." ".$rs['Descripcion']."</option>";
                                        }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <input style="width: 100px;" type="number" step="any" id="descuento_partida" name="descuento_partida" value=""/>
                                <input type="checkbox" id="porcentaje_partida" name="porcentaje_partida" value="1" style="display: none;"/> <!--Porcentaje--> <!-- Por observaciones de iliana el 22/11/2017 sólo se hacen descuentos por cantidad -->
                            </td>
                        </tr>
                        <tr>                            
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style=" text-align:right;">
                                <input type="submit" name="AgregarConcepto" class="boton" value="Agregar Concepto"  id="MainContent_btnAgregarConcepto" />
                            </td>
                            <td style=" text-align:right">
                                <input type="button" onclick="limpiarConcepto()" name="LimpiarCamposConcepto" class="boton" value="Limpiar" id="LimpiarCamposConcepto" style="width:100px;" /></td>
                        </tr>
                    </table>
                </form>
                <table style=" width:98%" id="tablaConceptos">
                    <tr>
                        <td>
                            <div>
                                <form id="conceptos_form" name="conceptos_form">
                                    <table cellspacing="0" cellpadding="4" align="Right" rules="cols" id="MainContent_grvConceptos" style="color:Black;background-color:White;border-color:#DEDFDE;border-width:1px;border-style:None;width:100%;border-collapse:collapse;">
                                        <tr style="color:White;background-color:#6B696B;font-weight:bold;">
                                            <th scope="col">CANTIDAD</th><th scope="col">DESCRIPCI&#211;N</th><th scope="col">P.UNITARIO</th><th scope="col">PRODUCTO</th><th scope="col">IMPORTE</th><th scope="col">DESC</th><th scope="col"></th><th scope="col"></th>
                                        </tr>
                                        <?php
                                        $result = $catalogo->obtenerLista("
                                            SELECT c.idConcepto,c.Cantidad,c.Descripcion,c.Unidad,c.PrecioUnitario, c.IdEmpresaProductoSAT, c.Descuento, c.Porcentaje
                                            FROM c_factura AS f
                                            INNER JOIN c_conceptos AS c ON c.idFactura=f.IdFactura
                                            WHERE f.IdFactura=" . $_POST['id']);
                                        
                                        $contador = 0;
                                        $total = 0;
                                        $costo_descuento_partida = 0;                                        
                                        while ($rs = mysql_fetch_array($result)) {
                                            $color = "#F7F7DE";
                                            if ($contador % 2 == 0) {
                                                $color = "White";
                                            }
                                            $contador++;
                                            $total = $total + ($rs['Cantidad'] * $rs['PrecioUnitario']);                                            
                                            if(isset($rs['Descuento']) && !empty($rs['Descuento'])){
                                                if($rs['Porcentaje'] == "1"){//Si se maneja porcentaje
                                                    $costo_descuento = number_format(($rs['Cantidad'] * $rs['PrecioUnitario']) * ($rs['Descuento'] / 100),2,".","");
                                                }else{
                                                    $costo_descuento = number_format($rs['Descuento'],2,".","");
                                                }
                                                echo $costo_descuento;
                                                $costo_descuento_partida += $costo_descuento;
                                            }
                                            echo "<tr style='background-color:" . $color . ";'>";
                                                $floatVal = floatval($rs['Cantidad']);
                                                if($floatVal && intval($floatVal) != $floatVal){
                                                    $cantidad = (number_format((float)$floatVal,4));        
                                                }else{
                                                    $cantidad = (number_format((int)$floatVal));        
                                                }
                                                echo "<td style='width:60px;'>"
                                                    . "<input type='text' id='cantidad_" . $rs['idConcepto'] . "' name='cantidad_" . $rs['idConcepto'] . "' value='$cantidad' style='width:50px' onchange = 'calcularImporte(" . $rs['idConcepto'] . ");' disabled='disabled'/>"
                                                    . "</td>";
                                                echo "<td style = 'width:300px;'><input type = 'text' id = 'descripcion_" . $rs['idConcepto'] . "' name = 'descripcion_" . $rs['idConcepto'] . "' value = '" . $rs['Descripcion'] . "' style = 'width:280px' disabled = 'disabled'/></td>";
                                                echo "<td align = 'right' style = 'width:60px;'>
                                                    <input type = 'text' id = 'preciounitario_" . $rs['idConcepto'] . "' name = 'preciounitario_" . $rs['idConcepto'] . "' onchange = 'calcularImporte(" . $rs['idConcepto'] . ");' value = '" . $rs['PrecioUnitario'] . "' style = 'width:100px' disabled = 'disabled'/>
                                                    </td>";
                                                echo "<td style='vertical-align:top;'>
                                                    <select id='producto_" . $rs['idConcepto'] . "' name='producto_" . $rs['idConcepto'] . "' style='width: 250px;'>";
                                                    foreach ($productos_sat as $key => $value) {
                                                        $s = ((int)$key == (int)$rs['IdEmpresaProductoSAT']) ? "selected" : "";
                                                        echo "<option value='$key' $s>$value</option>";
                                                    }
                                                    /*$consulta = "SELECT cps.*,eps.IdEmpresaProductoSAT 
                                                        FROM c_claveprodserv cps
                                                        INNER JOIN k_empresaproductosat AS eps ON eps.IdClaveProdServ = cps.IdProdServ 
                                                        AND IdDatosFacturacionEmpresa = ".$factura->getRFCEmisor();
                                                    $resultP = $catalogo->obtenerLista($consulta);
                                                    while($rsP = mysql_fetch_array($resultP)){
                                                        $s = "";
                                                        if((int)$rsP['IdEmpresaProductoSAT'] == (int)$rs['IdEmpresaProductoSAT'] ){
                                                            $s = "selected";
                                                        }
                                                        echo "<option value='".$rsP['IdEmpresaProductoSAT']."' $s>".$rsP['ClaveProdServ']." ".$rsP['Descripcion']."</option>";
                                                    }*/
                                                echo "</select>
                                                    </td>";
                                                echo "<td align = 'right' style = 'width:100px;'>
                                                    <input type = 'text' id = 'importe_" . $rs['idConcepto'] . "' name = 'importe_" . $rs['idConcepto'] . "' value = '" . number_format($rs['PrecioUnitario'] * $rs['Cantidad'], 2) . "' style = 'width:100px' disabled = 'disabled'/>
                                                    </td>";
                                                echo "<td>
                                                        <input style='width: 60px;' type='number' id='descuento_partida_" . $rs['idConcepto'] . "' name='descuento_partida_" . $rs['idConcepto'] . "' value='".$rs['Descuento']."' disabled = 'disabled'/>";
                                                $checked = "";
                                                if($rs['Porcentaje'] == "1"){
                                                    $checked = "checked='checked'";
                                                }
                                                echo "<input type='checkbox' id='porcentaje_partida_" . $rs['idConcepto'] . "' name='porcentaje_partida_" . $rs['idConcepto'] . "' value='1' $checked disabled = 'disabled' style='display:none;'/> <!--Porcentaje--> <!-- Por observaciones de iliana el 22/11/2017 sólo se hacen descuentos por cantidad -->
                                                    </td>";
                                                echo "<td align = 'center' style = 'width:100px;' id = 'td_concepto_" . $rs['idConcepto'] . "'>
                                                    <a onclick = 'modificarConcepto(" . $rs['idConcepto'] . ")'><img src = 'resources/images/Modify.png' /></a>
                                                    </td><td align = 'center' style = 'width:100px;'>
                                                    <a onclick = 'EliminarConcepto(" . $rs['idConcepto'] . ")'><img src = 'resources/images/Erase.png' /></a>
                                                    </td>
                                        </tr>";
                                        }
                                        ?>
                                    </table>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style=" float:right;
                                 text-align:right;
                                 " id="total_letras_conceptos">
                                <br /> 
                                <table style=" width:100%">
                                    <?php
                                        $total_descuento = number_format((($descuento / 100) * $total) + $costo_descuento_partida,2,".","");
                                        $Suma_iva = ($total - $total_descuento) * 0.16;
                                        if(!empty($descuento) || !empty($costo_descuento_partida)){
                                            echo '<tr>
                                                    <td class="style2"  style="text-align:left;">

                                                    </td>
                                                    <td class="style3">Descuento:</td>
                                                    <td class="style3">$</td>
                                                    <td class="style3" style="text-align:right;
                                                        ">
                                                        <span id="MainContent_lblIVA">'.  number_format($total_descuento, 2).'</span>
                                                    </td>
                                                </tr>';
                                        }
                                    ?>
                                    <tr>
                                        <td class="Etiquetas"  style="width: 750px;text-align:left;">
                                            <span id="MainContent_lblTotalLetra">(<?php
                                                
                                                $total = $total - $total_descuento;
                                                $letras = new EnLetras();
                                                echo $letras->ValorEnLetras($total + $Suma_iva, "PESOS ")
                                                ?>  )</span>
                                        </td>
                                        <td class="Etiquetas">Subtotal:</td>
                                        <td class="Etiquetas">$</td>
                                        <td class="Etiquetas" style=" width:100px;text-align:right;">
                                            <span id="MainContent_lblSubtotal"><?php echo number_format($total, 2) ?></span>
                                        </td>
                                    </tr>                                    
                                    <tr>
                                        <td class="style2"  style="text-align:left;">

                                        </td>
                                        <td class="style3">IVA 16%:</td>
                                        <td class="style3">$</td>
                                        <td class="style3" style="text-align:right;
                                            ">
                                            <span id="MainContent_lblIVA"><?php echo number_format( $Suma_iva, 2) ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="Etiquetas" style="width: 700px;text-align:left;">
                                        </td>
                                        <td class="Etiquetas">Total:</td>
                                        <td class="Etiquetas">$</td>
                                        <td class="Etiquetas" style="text-align:right;">
                                            <span id="MainContent_lblTotal"><?php echo number_format($total + $Suma_iva, 2) ?></span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <span id="MainContent_lblRespuesta"></span>
                        </td>
                    </tr>
                    <tr>
                        <td style=" height:50px;text-align:right;">
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
    <tr >
        <td style=" text-align:right;" colspan="2"> 
            <?php if ($fac_sust && !$ndc) { ?>
              <input type="button" name="Timbrar Factura" class="boton" value="Sustituir Factura" id="Timbrar" onclick="GenerarFacturaSustitucion('<?php echo $_POST['id'] ?>')"/>
                <input type="button" name="Terminar" class="boton" value="Terminar" id="Terminar" onclick="$.post('WEB-INF/Controllers/facturacion/Controller_nueva_Factura.php', {form: $('#altaFacturaform').serialize()}).done(function(data) {});cambiarContenidos('facturacion/ReporteFacturacion.php', 'Facturas CFDI');"/>
            <?php } else if(!$ndc && !$fac_sust){ ?>
                <input type="button" name="Timbrar Factura" class="boton" value="Timbrar Factura" id="Timbrar" onclick="GenerarFactura('<?php echo $_POST['id'] ?>')"/>
                <input type="button" name="Terminar" class="boton" value="Terminar" id="Terminar" onclick="$.post('WEB-INF/Controllers/facturacion/Controller_nueva_Factura.php', {form: $('#altaFacturaform').serialize()}).done(function(data) {});cambiarContenidos('facturacion/ReporteFacturacion.php', 'Facturas CFDI');"/>
            <?php }else{ ?>
                <input type="button" name="Timbrar Factura" class="boton" value="Generar NDC" id="Timbrar" onclick="GenerarFactura('<?php echo $_POST['id'] ?>')"/>
            <?php } ?>
        </td>
    </tr>
</table>
<?php
    $mesnaje_extra = "";
    $res_param = $catalogo->obtenerLista("SELECT cp.* FROM c_parametro AS cp WHERE cp.Descripcion = 'Leyenda Genesis'");

    if ($msx = mysql_fetch_array($res_param)) {
        $mesnaje_extra = $msx['Comentario'];
        $mesnaje_extra = str_replace("-----", $tipo, $mesnaje_extra);
    }

    echo "<div id='dialog' title='Leyenda de ".$tipo."'><textarea id='text_factura' name='text_factura' placeholder='Ingrese su texto'>$mesnaje_extra</textarea></div>";
?>