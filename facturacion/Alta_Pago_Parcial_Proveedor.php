<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/PagoParcialProveedor.class.php");
include_once("../WEB-INF/Classes/FacturaProveedor.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$pp = new PagoParcialProveedor();
$factura = new FacturaProveedor();
$pp->setId_factura($_GET['factura']);
$factura->getRegistroById($pp->getId_factura());
$psm = new PermisosSubMenu();

$catalogo = new Catalogo();
$query = "SELECT pp.idCuentaBancaria from c_pagosparciales_proveedor pp WHERE id_pago = " .$_GET['pago'];
$result = $catalogo->obtenerLista($query);
$noCuenta = null;
if($rs = mysql_fetch_array($result))
{
    $noCuenta = $rs['idCuentaBancaria'];
}

if(isset($noCuenta) && $noCuenta != ""){
    $query = "SELECT b.Nombre, c.noCuenta from c_cuentaBancaria c"
        . " LEFT JOIN c_banco b ON b.IdBanco = c.idBanco WHERE c.idCuentaBancaria=". $noCuenta;
}else{
$query = "SELECT b.Nombre, b.IdBanco, c.noCuenta from c_proveedor p"
        . " LEFT JOIN c_cuentaBancaria c ON c.idCuentaBancaria = p.CuentaBancaria"
        . " LEFT JOIN c_banco b ON b.IdBanco = c.idBanco WHERE p.RFC='". $_GET['RFC'] . "'";
}
$result = $catalogo->obtenerLista($query);
$noCuenta = $banco = $idBanco = "";
while(isset($result) && $rs = mysql_fetch_array($result))
{
    $noCuenta = $rs['noCuenta'];
    $banco = $rs['Nombre'];
    $idBanco = $rs['IdBanco'];
}
if(isset($_GET['cxc'])){
    $cxc = true;
    $cxc_liga = "&cxc=true";
}else{
    $cxc = false;
    $cxc_liga = "";
}

if (isset($_GET['pago']) && $_GET['pago'] != "") {
    $pp->setId_pago($_GET['pago']);
    $pp->getRegistrobyID();
}

$result = $pp->getDatosbyFactura();
while ($rs = mysql_fetch_array($result)) {
    ?>
<script type="text/javascript" src="../resources/js/paginas/facturacion/alta_pp_proveedores.js"></script>
<form id="form_pp" name="form_pp">
    <h3>Proveedor:<?php echo $rs['NombreComercial'] ?><br/>Folio:<?php echo $rs['Folio'] ?><br/>
        Importe facturado:$<?php echo number_format($rs['Total'], 2); ?>
        <br/>Importe por pagar:$<?php
        if ($rs['Pagado'] == "") {
            echo "<input type='hidden' id='por_pagar' name='por_pagar' value='".$rs['Total']."'/>";
            echo number_format($rs['Total'], 2);
        } else {
            echo "<input type='hidden' id='por_pagar' name='por_pagar' value='".$rs['Total']."'/>";
            echo number_format($rs['Pagado'],2);
        }
        ?><br/>
        Banco: <?php echo $banco; ?><br/>
        <?php if($psm->tienePermisoEspecial($_SESSION['idUsuario'], 29)){ //Cambiamos la cuenta bancaria hacia la que se dirijirá el pago?>
            No. Cuenta:<select id="cuentaBancarias" name="cuentaBancarias" style="max-width: 250px;">
            <?php                        
                $query = $catalogo->getListaAlta("c_cuentaBancaria", "noCuenta");
                while ($rs = mysql_fetch_array($query)) {
                    $s = "";
                    if (trim($noCuenta) == trim($rs['noCuenta'])) {
                        $s = "selected";
                    }
                    echo "<option value=" . $rs['idCuentaBancaria'] . " " . $s . ">" . $rs['noCuenta'] . "</option>";
                }
            ?>
            </select>
        <?php }else{ ?>
            No Cuenta: <?php echo "XXXXX-".substr($noCuenta,strlen($noCuenta) - 4); ?><br/>
        <?php } ?>
    </h3>
<?php } ?>
    <table>
        <tr>
            <td><label for="referencia">Referencia</label></td>
            <td><input type="text" id="referencia" name="referencia" value="<?php
                if (isset($_GET['pago']) && $_GET['pago'] != "") {
                    echo $pp->getReferencia();
                }
                ?>" style="width: 200px;"/></td>
        </tr>
        <tr>
            <td><label for="observaciones">Observaciones</label></td>
            <td><textarea type="text" id="observaciones" name="observaciones" style="width: 500px; height: 100px; resize: none;">
                <?php
                    if (isset($_GET['pago']) && $_GET['pago'] != "") {
                        echo $pp->getObservaciones();
                    }else{
                        echo "";
                    }
                ?>
                </textarea>
            </td>       
        </tr>
        <tr>
            <td><label for="importe">Importe</label></td>
            <td><input type="text" id="importe" name="importe" value="<?php
                if (isset($_GET['pago']) && $_GET['pago'] != "") {
                    echo $pp->getImporte();
                }
                ?>" style="width: 200px;"/></td>
        </tr>
        <tr>
            <td><label for="fecha">Fecha</label></td>
            <td><input type="text" id="fecha" name="fecha" readonly="readonly" value="<?php
                if (isset($_GET['pago']) && $_GET['pago'] != "") {
                    echo $pp->getFechapago();
                }
                ?>" style="width: 200px;"/></td>
        </tr>
    </table>
    <?php
    if (isset($_GET ['pago']) && $_GET['pago'] != "") {
        echo "<input type=\"hidden\" id=\"pago\" name=\"pago\" value=\"" . $_GET['pago'] . "\"/>";
    }
    ?>
    <input type="hidden" id="factura" name="factura" value="<?php echo $_GET['factura']; ?>"/>
    <input type="hidden" id="RFC" name="RFC" value="<?php echo $_GET['RFC']; ?>"/>
    <input type="hidden" id="banco" name="banco" value="<?php echo $idBanco; ?>"/>
    <input type="hidden" id="cuentaBancaria" name="RFC" value="<?php echo $noCuenta; ?>"/>
    <input type="submit" id="submit_equipo" name="submit_equipo" class="boton" value="Guardar" style="margin-left: 30%;"/>
    <input type="button" id="cancelar" name="cancelar" class="boton" value="Cancelar" style="margin-left: 30%;" 
           onclick="cambiarContenidos('list_pago_parcial_proveedor.php?RFC=<?php echo $_GET['RFC']; ?>&factura=<?php echo $_GET['factura']; ?>', 'Pago Parcial Factura <?php echo $factura->getFolio(); ?>');"/>
</form>
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

