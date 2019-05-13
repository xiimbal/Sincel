<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/PagoParcial.class.php");
include_once("../WEB-INF/Classes/SaldosAFavor.class.php");
include_once("../WEB-INF/Classes/Factura.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Empresa.class.php");
include_once("../WEB-INF/Classes/Contrato.class.php");
include_once("../WEB-INF/Classes/ccliente.class.php");

$pp = new PagoParcial();
$saf = new SaldosAFavor();
$empresa = new Empresa();
$contrato = new Contrato();
$cliente = new ccliente();

$saf->setRFC($_GET['RFC']);
$factura = new Factura_NET();
$pp->setId_factura($_GET['factura']);
$factura->getRegistroById($pp->getId_factura());
$psm = new PermisosSubMenu();
$catalogo2 = new CatalogoFacturacion();

$noCuenta = null;
$noCuenta2 = 0;

$saldoAnteriorUsado = 0;
$catalogo1 = new CatalogoFacturacion();
if (isset($_GET['pago'])) { //Cuando editan el pago
    $query = "SELECT pp.idCuentaBancaria from c_pagosparciales pp WHERE IdPagoParcial = " . $_GET['pago'];
    $result = $catalogo1->obtenerLista($query);
    
    $consulta2 = "UPDATE c_pagosparciales SET PathPDFPre=NULL WHERE IdPagoParcial= " . $_GET['pago'];                // Se borra la ruta del PDF Prepago  *JT
    $catalogo2->obtenerLista($consulta2);
    
    $noCuenta = null;
    if ($rs = mysql_fetch_array($result)) {
        $noCuenta = $rs['idCuentaBancaria'];
    }
    $noCuenta2 = $noCuenta;
    $saf->setIdPagoParcial($_GET['pago']);
    $saldoAnteriorUsado = $saf->obtenerPagadoConSaldoAFavorPorPago();
}

$catalogo = new Catalogo();
$query = "SELECT b.Nombre, c.noCuenta from c_cliente cl"
        . " LEFT JOIN c_cuentaBancaria c ON c.idCuentaBancaria = cl.idCuentaBancaria"
        . " LEFT JOIN c_banco b ON b.IdBanco = c.idBanco WHERE cl.RFC='" . $_GET['RFC'] . "'";
$result = $catalogo->obtenerLista($query);

$noCuenta = $banco = "";
while ($rs = mysql_fetch_array($result)) {
    $noCuenta = $rs['noCuenta'];
    $banco = $rs['Nombre'];
}
if (isset($_GET['cxc'])) {
    $cxc = true;
    $cxc_liga = "&cxc=true";
} else {
    $cxc = false;
    $cxc_liga = "";
}

$FormaPago = "";
$cliente->getregistrobyRFC($factura->getRFCReceptor());
$result = $contrato->getRegistroValidacion($cliente->getClaveCliente());
while($rs = mysql_fetch_array($result)){
    $FormaPago = $rs['IdFormaComprobantePago'];
}

$CtaOrdenante = "";
$RfcEmisorCtaOrd = "";
$NomBancoEmisorOrd = "";

if (isset($_GET['pago']) && $_GET['pago'] != "") {
    $pp->setId_pago($_GET['pago']);
    $pp->getRegistrobyID($cxc);
    $CtaOrdenante = $pp->getCtaOrdenante();
    $RfcEmisorCtaOrd = $pp->getRFCBancoEmisorOrd();
    $NomBancoEmisorOrd = $pp->getNomBancoEmisorOrd();
}

$datosCuentas = $pp->getCuentasFiscales($pp->getId_factura());
if($datosCuentas != false){
    $CtaOrdenante = $datosCuentas['CtaOrdenante'];
    $RfcEmisorCtaOrd = $datosCuentas['RfcEmisorCtaOrd'];
    $NomBancoEmisorOrd = $datosCuentas['NomBancoEmisorOrd'];
}

?>
<script type="text/javascript" src="../resources/js/paginas/facturacion/alta_pp.js"></script>
<form id="form_pp" name="form_pp" onsubmit="return validar()">
    <?php
    $empresa->setRFC($factura->getRFCEmisor());
    if ($empresa->getRegistrobyRFC()) {
        echo "<input type='hidden' name='idEmpresaFactura' value='" . $empresa->getId() . "'/>";
    }
    $result = $pp->getDatosbyFactura($cxc);
    while ($rs = mysql_fetch_array($result)) {
        $saf->setRFC($rs['RFC']);
        $saldoAFavor = $saf->getSaldoByCliente() + $saldoAnteriorUsado;
        ?>

        <h3>Cliente:<?php echo $rs['NombreRazonSocial'] ?><br/>Folio:<?php echo $rs['Folio'] ?><br/>
            Importe facturado:$<?php echo number_format($rs['Total'], 2); ?>
            <br/>Importe por pagar:$<?php
            if ($rs['Pagado'] == "") {
                echo "<input type='hidden' id='por_pagar' name='por_pagar' value='" . $rs['Total'] . "'/>";
                echo number_format($rs['Total'], 2);
            } else {
                echo "<input type='hidden' id='por_pagar' name='por_pagar' value='" . $rs['Total'] . "'/>";
                echo number_format($rs['Pagado'], 2);
            }
            ?><br/>
            <?php if ($psm->tienePermisoEspecial($_SESSION['idUsuario'], 28)) { //Cambiamos la cuenta bancaria hacia la que se dirijirá el pago?>
                Banco - No. Cuenta:<select id="cuentaBancarias" name="cuentaBancarias" style="max-width: 250px;">
                    <?php
                    $query = $catalogo->obtenerLista("SELECT *,b.Nombre from c_cuentaBancaria cb 
                        LEFT JOIN c_banco AS b ON b.IdBanco = cb.idBanco WHERE cb.RFC = '" . $rs['IdDatosFacturacionEmpresa'] . "'");
                    while ($rs = mysql_fetch_array($query)) {
                        $s = "";
                        if (trim($noCuenta2) == trim($rs['idCuentaBancaria'])) {
                            $s = "selected";
                        }
                        echo "<option value=" . $rs['idCuentaBancaria'] . " " . $s . ">" . $rs['Nombre'] . " - " . $rs['noCuenta'] . "</option>";
                    }
                    ?>
                </select>
            <?php } else { ?>
                Banco - No Cuenta: <?php echo "$banco - XXXXX-" . substr($noCuenta, strlen($noCuenta) - 4); ?><br/>
            <?php } ?>
        </h3>
    <?php } ?>
    <table>
        <tr>
            <td style="width: 20%;"><label for="forma_pago">Forma de pago</label></td>
            <td style="width: 30%;">
                <select id="forma_pago" name="forma_pago" style="width: 200px;" onchange="verificarCtaOrdenante();">
                    <?php
                    $result = $catalogo->obtenerLista("SELECT IdFormaPago, Nombre, Descripcion FROM `c_formapago` WHERE Activo = 1 AND Nombre <> 'Por definir' ORDER BY Nombre;");
                    while ($rs = mysql_fetch_array($result)) {
                        if ($pp->getIdFormaPago() != "") {
                            $s = ($rs['IdFormaPago'] == $pp->getIdFormaPago()) ? "selected" : "";
                        } else if(!empty ($FormaPago)){
                            $s = ($rs['IdFormaPago'] == $FormaPago) ? "selected" : "";
                        } else {
                            $s = ($rs['IdFormaPago'] == $factura->getFormaPago()) ? "selected" : "";
                        }
                        echo '<option value=' . $rs['IdFormaPago'] . ' ' . $s . '>' . $rs['Nombre'] . ' - ' . $rs['Descripcion'] . '</option>';
                    }
                    ?>
                </select>
            </td>
            <td style="width: 15%;"><label for="serie">Serie</label></td>
            <td style="width: 35%;">
                <select id="serie" name="serie" style="width: 200px;">
                    <option value="">Selecciona una serie</option>
                    <?php
                    $result = $catalogo->obtenerLista("SELECT IdSerie, Prefijo FROM c_seriepago WHERE Activo = 1 ORDEr BY Prefijo;");
                    while ($rs = mysql_fetch_array($result)) {
                        if ($pp->getIdSerie() != "") {
                            $s = ($rs['IdSerie'] == $pp->getIdSerie()) ? "selected" : "";
                        } else {
                            $s = ($rs['IdSerie'] == $factura->getSerie()) ? "selected" : "";
                        }
                        echo '<option value=' . $rs['IdSerie'] . ' ' . $s . '>' . $rs['Prefijo'] . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td style="width: 20%;"><label for="referencia"># de operación (referencia)</label></td>
            <td colspan="3"><input type="text" id="referencia" name="referencia"  value="<?php
                    if (isset($_GET['pago']) && $_GET['pago'] != "") {
                        echo $pp->getReferencia();
                    }
                    ?>" style="width: 200px;" /></td>
        </tr>
        <tr>
            <td style="width: 20%;"><label for="observaciones">Observaciones</label></td>
            <td colspan="3"><textarea type="text" id="observaciones" name="observaciones" style="width: 500px; height: 100px; resize: none;"><?php
                    if (isset($_GET['pago']) && $_GET['pago'] != "") {
                        echo $pp->getObservaciones();
                    } else {
                        echo "";
                    }
                    ?></textarea>
            </td>       
        </tr>
        <tr>
            <td style="width: 20%;"><label for="importe">Importe</label></td>
            <td style="width: 30%;"><input type="text" id="importe" name="importe" value="<?php
                    if (isset($_GET['pago']) && $_GET['pago'] != "") {
                        echo $pp->getImporte() - $saldoAnteriorUsado;
                    }
                    ?>" style="width: 200px;"/></td>
            <td style="width: 15%;"><label for="fecha">Fecha</label></td>
            <td style="width: 35%;"><input type="text" id="fecha" name="fecha" readonly="readonly" value="<?php
                if (isset($_GET['pago']) && $_GET['pago'] != "") {
                    echo $pp->getFechapago();
                }
                    ?>" style="width: 200px;"/>
            </td>
        </tr>
        <tr>
            <td style="width: 20%;">Tipo de cadena de pago</td>
            <td style="width: 30%;">
                <select id="TipoCadPago" name="TipoCadPago" onchange="cambiarTipoCadena();">
                    <option value="">Seleccione un tipo</option>
                    <?php
                    $result = $catalogo1->getListaAlta("c_tipocadenapago", "TipoCadena");
                    while ($rs = mysql_fetch_array($result)) {
                        $s = ($pp->getIdTipoCadena() == $rs['IdTipoCadena']) ? "selected" : "";
                        echo "<option value='" . $rs['IdTipoCadena'] . "' $s>" . $rs['TipoCadena'] . " - " . $rs['Descripcion'] . "</option>";
                    }
                    ?>
                </select>
            </td>
            <td style="width: 15%;" class="tipo_cadena">Certificado Pago (Base 64) <span class="obligatorio">*</span></td>
            <td style="width: 35%;" class="tipo_cadena">
                <input type="text" id="CertPago" name="CertPago" value="<?php echo $pp->getCertPago(); ?>"/>
            </td>
        </tr>
        <tr class="tipo_cadena">
            <td style="width: 20%;">Cadena original del comprobante de pago <span class="obligatorio">*</span></td>
            <td style="width: 30%;">
                <input type="text" id="CadPago" name="CadPago" value="<?php echo $pp->getCadPago(); ?>"/>
            </td>
            <td style="width: 15%;">Sello de pago (Base 64) <span class="obligatorio">*</span></td>
            <td style="width: 35%;">
                <input type="text" id="SelloPago" name="SelloPago" value="<?php echo $pp->getSelloPago(); ?>"/>
            </td>
        </tr>
        <tr>
            <td style="width: 20%;">RFC Banco Ordenante</td>
            <td style="width: 30%;">
                <input type="text" id="RFCBancoEmisor" name="RFCBancoEmisor" value="<?php echo $RfcEmisorCtaOrd ?>" onblur="verificarRFCEmisor();"/>
            </td>
            <td style="width: 15%;">Nombre Banco Ordenante</td>
            <td style="width: 35%;">
                <input type="text" id="NombreBancoEmisor" name="NombreBancoEmisor" value="<?php echo $NomBancoEmisorOrd ?>"/>
            </td>
        </tr>
        <tr>
            <td style="width: 20%;">Cuenta Ordenante</td>
            <td style="width: 30%;">
                <input type="text" id="ClaveInterbancariaEmisor" name="ClaveInterbancariaEmisor" value="<?php echo $CtaOrdenante ?>"/>
            </td>
        </tr>
        <tr>
            <?php
            if ((int) $saldoAFavor > 0) {
                echo '<input type="hidden" id="saldoFavor" name="saldoFavor" value="' . $saldoAFavor . '"/>';
                echo '<td>Saldo a favor: </td><td>$' . number_format($saldoAFavor, 2) . ' </td>';
                echo "<tr>";
                echo '<td>Importe saldo a favor: </td><td><input type="text" id="cantidadSaldo" name="cantidadSaldo" value="' . $saldoAnteriorUsado . '"/></td>';
            }
            ?>
        </tr>
    </table>
    <?php
    if (isset($_GET ['pago']) && $_GET['pago'] != "") {
        echo "<input type=\"hidden\" id=\"pago\" name=\"pago\" value=\"" . $_GET['pago'] . "\"/>";
    }
    ?>
    <input type="hidden" id="factura" name="factura" value="<?php echo $_GET['factura']; ?>"/>
    <input type="hidden" id="RFC" name="RFC" value="<?php echo $saf->getRFC(); ?>"/>
    <input type="submit" id="submit_equipo" name="submit_equipo" class="boton" value="Guardar" style="margin-left: 30%;"/>
    <input type="button" id="cancelar" name="cancelar" class="boton" value="Cancelar" style="margin-left: 30%;" 
           onclick="cambiarContenidos('list_pago_parcial.php?RFC=<?php echo $_GET['RFC']; ?>&factura=<?php echo $_GET['factura'] . "" . $cxc_liga; ?>', 'Pago Parcial Factura <?php echo $factura->getFolio(); ?>');"/>
           <?php
           if ($cxc) {
               echo '<input type="hidden" id="cxc_activo" name="cxc_activo" value="1"/>';
           }
           ?>
</form>
<div id = "dialog" ></div>
<script>
    addrange(<?php
           if (isset($_GET['pago']) && $_GET['pago'] != "") {
               if ($rs['Pagado'] == "") {
                   echo $rs['Total'];
               } else {
                   echo $rs['Pagado'] + $pp->getImporte();
               }
           } else {
               if ($rs['Pagado'] == "") {
                   echo $rs['Total'];
               } else {
                   echo $rs['Pagado'];
               }
           }
           ?>);
</script>