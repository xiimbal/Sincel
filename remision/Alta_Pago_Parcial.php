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
$catalogo = new Catalogo();

$saf->setRFC($_GET['RFC']);
$factura = new Factura_NET();
$pp->setId_factura($_GET['factura']);
$factura->getRegistroById($pp->getId_factura());
$psm = new PermisosSubMenu();

$noCuenta = null;
$noCuenta2 = 0;

$saldoAnteriorUsado = 0;
if (isset($_GET['pago'])) { //Cuando editan el pago
    $query = "SELECT pp.idCuentaBancaria from c_pagosparciales pp WHERE id_pago = " . $_GET['pago'];
    $result = $catalogo->obtenerLista($query);
    $noCuenta = null;
    if ($rs = mysql_fetch_array($result)) {
        $noCuenta = $rs['idCuentaBancaria'];
    }
    
    $noCuenta2 = $noCuenta;
}

$query = "SELECT b.Nombre, c.noCuenta from c_cliente cl"
        . " LEFT JOIN c_cuentaBancaria c ON c.idCuentaBancaria = cl.idCuentaBancaria"
        . " LEFT JOIN c_banco b ON b.IdBanco = c.idBanco WHERE cl.RFC='" . $_GET['RFC'] . "'";
//echo $query;
$result = $catalogo->obtenerLista($query);
$noCuenta = $banco = "";
while ($rs = mysql_fetch_array($result)) {
    $noCuenta = $rs['noCuenta'];
    $banco = $rs['Nombre'];
}

if (isset($_GET['pago']) && $_GET['pago'] != "") {
    $pp->setId_pago($_GET['pago']);
    $pp->getRegistrobyID(false);
}

?>
<script type="text/javascript" src="../resources/js/paginas/remision/alta_pp.js"></script>
<form id="form_pp" name="form_pp">
    <?php
    $empresa->setRFC($factura->getRFCEmisor());
    if ($empresa->getRegistrobyRFC()) {
        echo "<input type='hidden' name='idEmpresaFactura' value='" . $empresa->getId() . "'/>";
    }
    $result = $pp->getDatosbyFactura($cxc);
    while ($rs = mysql_fetch_array($result)) {
        ?>

        <h3>
            Cliente:<?php echo $rs['NombreRazonSocial'] ?><br/>
            Folio:<?php echo $rs['Folio'] ?><br/>
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
            <td><label for="referencia"># de operación (referencia)</label></td>
            <td><input type="text" id="referencia" name="referencia" value="<?php
                    if (isset($_GET['pago']) && $_GET['pago'] != "") {
                        echo $pp->getReferencia();
                    }
                    ?>" style="width: 200px;"/></td>
        </tr>
        <tr>
            <td><label for="observaciones">Observaciones</label></td>
            <td><textarea type="text" id="observaciones" name="observaciones" style="width: 500px; height: 100px; resize: none;"><?php
                    if (isset($_GET['pago']) && $_GET['pago'] != "") {
                        echo $pp->getObservaciones();
                    } else {
                        echo "";
                    }
                    ?></textarea>
            </td>       
        </tr>
        <tr>
            <td><label for="importe">Importe</label></td>
            <td><input type="text" id="importe" name="importe" value="<?php
                    if (isset($_GET['pago']) && $_GET['pago'] != "") {
                        echo $pp->getImporte() - $saldoAnteriorUsado;
                    }
                    ?>" style="width: 200px;"/></td>
        </tr>
        <tr>
            <td><label for="fecha">Fecha</label></td>
            <td><input type="text" id="fecha" name="fecha" readonly="readonly" value="<?php
                if (isset($_GET['pago']) && $_GET['pago'] != "") {
                    echo $pp->getFechapago();
                }
                    ?>" style="width: 200px;"/>
            </td>
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
           onclick="cambiarContenidos('lista_pago_parcial.php?RFC=<?php echo $_GET['RFC']; ?>&factura=<?php echo $_GET['factura'] . "" . $cxc_liga; ?>', 'Pago Parcial Factura <?php echo $factura->getFolio(); ?>');"/>
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