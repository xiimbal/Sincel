<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Concepto.class.php");
include_once("../WEB-INF/Classes/EnLetras.class.php");
$catalogo = new Catalogo();
$concepto = new Concepto();
if (isset($_POST['concepto']) && $_POST['concepto'] != "") {
    $concepto->setIdConcepto($_POST['concepto']);
    $concepto->getRegistrobyID();
}

$ndc = false;
$tipo = "factura";
if(isset($_POST['ndc']) && $_POST['ndc'] == "1"){
    $ndc = true;
    $tipo = "nota de crédito";
}


?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/alta_Concepto.js"></script>
<table class="Tablas">
    <tr>
        <td colspan="2">
            <fieldset>
                <legend>CONCEPTOS</legend>
                <form id="formConceptos">
                    <table class="Tablas" id="ConceptoForm">
                        <tr>
                            <td class="TitulosEncabezados">CANTIDAD</td>
                            <td class="TitulosEncabezados">UNIDAD</td>
                            <td class="TitulosEncabezados">DESCRIPCIÓN</td>
                            <td class="TitulosEncabezados">P.UNITARIO</td>
                        </tr>
                        <tr>
                            <td style=" vertical-align:top;">
                                <input style="width: 100px;" name="Cantidad" type="text" id="Cantidad"  value="<?php if ($concepto->getCantidad() != "") echo $concepto->getCantidad(); ?>"/>
                            </td>
                            <td style=" vertical-align:top;">
                                <select style="width: 100px;" name="Unidad"  id="Unidad">
                                    <option value="Servicio" selected>Servicio</option>
                                    <option value="Pieza">Pieza</option>
                                    <option value="No aplica">No aplica</option>
                                </select>
                            </td>
                            <td style=" vertical-align:top;">
                                <textarea name="Descripcion" rows="2" cols="15" id="Descripcion" style="height:20px;width:200px;" ><?php if ($concepto->getDescripcion() != "") echo $concepto->getDescripcion(); ?></textarea>
                            </td>
                            <td style=" vertical-align:top;">
                                <input style="width: 100px;" name="PrecioUnitario" type="text"  id="PrecioUnitario"  value="<?php if ($concepto->getPrecioUnitario() != "") echo $concepto->getPrecioUnitario(); ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
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
                                            <th scope="col">CANTIDAD</th><th scope="col">UND.MEDIDA</th><th scope="col">DESCRIPCI&#211;N</th><th scope="col">P.UNITARIO</th><th scope="col">IMPORTE</th><th scope="col">MODIFICAR</th><th scope="col">ELIMINAR</th>
                                        </tr>
                                        <?php
                                        $result = $catalogo->obtenerLista("SELECT c.idConcepto,c.Cantidad,c.Descripcion,c.Unidad,c.PrecioUnitario FROM c_factura AS f
                                            INNER JOIN c_conceptos AS c ON c.idFactura=f.IdFactura
                                            WHERE f.IdFactura=" . $_POST['id']);
                                        $contador = 0;
                                        $total = 0;
                                        while ($rs = mysql_fetch_array($result)) {
                                            $color = "#F7F7DE";
                                            if ($contador % 2 == 0) {
                                                $color = "White";
                                            }
                                            $contador++;
                                            $total = $total + ($rs['Cantidad'] * $rs['PrecioUnitario']);
                                            echo "<tr style='background-color:" . $color . ";'>";
                                                $floatVal = floatval($rs['Cantidad']);
                                                if($floatVal && intval($floatVal) != $floatVal){
                                                    $cantidad = (number_format((float)$floatVal,4));        
                                                }else{
                                                    $cantidad = (number_format((int)$floatVal));        
                                                }
                                                echo "<td style='width:100px;'>"
                                                    . "<input type='text' id='cantidad_" . $rs['idConcepto'] . "' name='cantidad_" . $rs['idConcepto'] . "' value='$cantidad' style='width:50px' onchange = 'calcularImporte(" . $rs['idConcepto'] . ");' disabled='disabled'/>"
                                                    . "</td>";
                                                echo "<td style='width:100px;'><select style='width: 100px;' name='Unidad_" . $rs['idConcepto'] . "'  id='Unidad_" . $rs['idConcepto'] . "' disabled='disabled'>";
                                                $arr = array("Servicio", "Pieza", "No aplica");
                                                foreach ($arr as $value) {
                                                    if ($rs['Unidad'] == $value) {
                                                        echo "<option value='$value' selected>$value</option>";
                                                    } else {
                                                        echo "<option value='$value' >$value</option>";
                                                    }
                                                }
                                                echo "</select></td>";
                                                echo "<td style = 'width:300px;'><input type = 'text' id = 'descripcion_" . $rs['idConcepto'] . "' name = 'descripcion_" . $rs['idConcepto'] . "' value = '" . $rs['Descripcion'] . "' style = 'width:280px' disabled = 'disabled'/></td>";
                                                echo "<td align = 'right' style = 'width:100px;'>
                                                    <input type = 'text' id = 'preciounitario_" . $rs['idConcepto'] . "' name = 'preciounitario_" . $rs['idConcepto'] . "' onchange = 'calcularImporte(" . $rs['idConcepto'] . ");' value = '" . number_format($rs['PrecioUnitario'], 2) . "' style = 'width:100px' disabled = 'disabled'/>
                                                    </td>";
                                                        echo "<td align = 'right' style = 'width:100px;'>
                                                    <input type = 'text' id = 'importe_" . $rs['idConcepto'] . "' name = 'importe_" . $rs['idConcepto'] . "' value = '" . number_format($rs['PrecioUnitario'] * $rs['Cantidad'], 2) . "' style = 'width:100px' disabled = 'disabled'/>
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
                                    <tr>
                                        <td class="Etiquetas"  style="width: 750px;
                                            text-align:left;
                                            ">
                                            <span id="MainContent_lblTotalLetra">(<?php
                                                $letras = new EnLetras();
                                                echo $letras->ValorEnLetras($total * 1.16, "PESOS ")
                                                ?>  )</span>
                                        </td>
                                        <td class="Etiquetas">Subtotal:</td>
                                        <td class="Etiquetas">$</td>
                                        <td class="Etiquetas" style=" width:100px;
                                            text-align:right;
                                            ">
                                            <span id="MainContent_lblSubtotal"><?php echo number_format($total, 2) ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="style2"  style="text-align:left;
                                            ">

                                        </td>
                                        <td class="style3">IVA 16%:</td>
                                        <td class="style3">$</td>
                                        <td class="style3" style="text-align:right;
                                            ">
                                            <span id="MainContent_lblIVA"><?php echo number_format($total * .16, 2) ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="Etiquetas" style="width: 700px;
                                            text-align:left;
                                            ">
                                        </td>
                                        <td class="Etiquetas">Total:</td>
                                        <td class="Etiquetas">$</td>
                                        <td class="Etiquetas" style="text-align:right;
                                            ">
                                            <span id="MainContent_lblTotal"><?php echo number_format($total * 1.16, 2) ?></span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <span id="MainContent_lblRespuesta"></span>
                        </td>
                    </tr>
                    <tr>
                        <td style=" height:50px;
                            text-align:right;
                            ">

                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
    <tr >
        <td style=" text-align:right;
            " colspan="2">            
            <?php if(!$ndc){ ?>
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
    $res_param = $catalogo->obtenerLista("SELECT cp.* FROM c_parametro AS cp WHERE cp.Descripcion = 'Parametro Genesis'");

    if ($msx = mysql_fetch_array($res_param)) {
        $mesnaje_extra = $msx['Comentario'];
    }

    echo '<div id="dialog" title="Leyenda de '.$tipo.'">
            <textarea id="text_factura" name="text_factura">Esta '.$tipo.' deberá ser pagada en una sola exhibición. Esta '.$tipo.' no libera al cliente de adeudos anteriores o consumos no incluidos en la misma los títulos de crédito dados por el cliente, en los casos autorizados, serán recibidos bajo condición \'salvo buen cobro\' con base en el Articulo de la Ley General de Títulos y Operaciones de Crédito, de no verificarse el pago del importe que ampare este documento al vencimiento, el cliente se obliga a pagar el 10% mensual de intereses moratorios, sobre saldos insolutos. '.$mesnaje_extra.'
            </textarea>
        </div>';
?>