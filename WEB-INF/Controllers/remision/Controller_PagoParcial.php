<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../../Classes/PagoParcial.class.php");
include_once("../../Classes/SaldosAFavor.class.php");

if(isset ($_POST['comprueba_pago']) && isset($_POST['idFactura']) && isset($_POST['pago']) ){
    $pago = new PagoParcial();
    if($pago->verificaPagoMayorNotaRemision($_POST['pago'], $_POST['idFactura'], $_POST['idPago'])){
        echo "El pago por ".$_POST['pago']." supera el monto total de la factura, desea continuar?";
        return;
    }
    echo "";
}else if (isset($_POST['pago']) && $_POST['pago'] != "") {  
    $pp = new PagoParcial();
    $pp->setId_pago($_POST['pago']);
    $pp->getRegistrobyID(false);
    if ($pp->deleteRegistro(false)) {
        echo "Se ha eliminado el pago parcial exitosamente";
    } else {
        echo "Error: el pago parcial tiene valores dependientes";
    }
} else {
    $saf = new SaldosAFavor();
    $parametros = "";
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $pago = new PagoParcial();
    $cxc = false;
    $importeTotal = 0;
    if(isset($parametros['importe']) && $parametros['importe'] != "" && $parametros['importe'] > 0){
        $importeTotal += (double)$parametros['importe'];
    }
    if(isset($parametros['cantidadSaldo']) && $parametros['cantidadSaldo'] != "" && $parametros['cantidadSaldo'] > 0){
        $importeTotal += (double)$parametros['cantidadSaldo'];
    }
    $pago->setIdEmpresaFactura($parametros['idEmpresaFactura']);
    $pago->setReferencia($parametros['referencia']);
    $pago->setId_factura($parametros['factura']);
    $pago->setObservaciones($parametros['observaciones']);
    $pago->setFechapago($parametros['fecha']);    
    $pago->setCuentaBancaria($parametros['cuentaBancarias']);
    $pago->setImporte($importeTotal);
    $pago->setPantalla("PHP Controller_PagoParcial");
    $pago->setUsuarioCreacion($_SESSION['user']);
    $pago->setUsuarioUltimaModificacion($_SESSION['user']);
    
    $catalogo = new Catalogo();
    $query = "SELECT b.IdBanco, c.noCuenta from c_cuentaBancaria c"
            . " LEFT JOIN c_banco b ON b.IdBanco = c.idBanco WHERE RFC='". $parametros['RFC'] . "'";
    $result = $catalogo->obtenerLista($query);
    $noCuenta = $banco = "";
    while($rs = mysql_fetch_array($result))
    {
        $noCuenta = $rs['noCuenta'];
        $banco = $rs['IdBanco'];
    }
    
    if (isset($parametros['pago']) && $parametros['pago'] != "") {
        $pago->setId_pago($parametros['pago']);
        if ($pago->updateRegistro($cxc)) {
            echo "<h2>Se ha editado el pago parcial exitosamente</h2>";
        } else {
            echo "Error: El pago parcial no se logro editar";
        }
    } else {
        if ($pago->nuevoRegistro($cxc)) {
            echo "<h2>Se ha registrado el nuevo pago parcial</h2>";
        } else {
            echo "Error: El pago parcial no se registro";            
        }
    }
}
?>