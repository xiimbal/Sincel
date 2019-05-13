<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../../Classes/MovimientoBancario.class.php");
include_once("../../Classes/PagoParcialProveedor.class.php");
if(isset ($_POST['comprueba_pago']) && isset($_POST['idFactura']) && isset($_POST['pago']) ){
    $pago = new PagoParcialProveedor();
    if($pago->verificaPagoMayor($_POST['pago'], $_POST['idFactura'], $_POST['idPago'])){
        echo "El pago por ".$_POST['pago']." supera el monto total de la factura, desea continuar?";
        return;
    }
    echo "";
}else if (isset($_POST['pago']) && $_POST['pago'] != "") {  
    $pp = new PagoParcialProveedor();
    $pp->setId_pago($_POST['pago']);
    if ($pp->deleteRegistro()) {
        echo "Se ha eliminado el pago parcial exitosamente";
    } else {
        echo "Error: el pago parcial tiene valores dependientes";
    }
} else {
    
    $parametros = "";
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $pago = new PagoParcialProveedor();
    $pago->setReferencia($parametros['referencia']);
    $pago->setId_factura($parametros['factura']);
    $pago->setObservaciones($parametros['observaciones']);
    $pago->setCuentaBancaria($parametros['cuentaBancarias']);
    $pago->setFechapago($parametros['fecha']);
    $pago->setImporte($parametros['importe']);
    $pago->setPantalla("PHP Controller_PagoParcial");
    $pago->setUsuarioCreacion($_SESSION['user']);
    $pago->setUsuarioUltimaModificacion($_SESSION['user']);

    $noCuenta = $parametros['noCuenta'];
    $banco = $parametros['banco'];
    $pago->setPantalla("PHP Controller_PagoParcialProveedor");
    $pago->setUsuarioCreacion($_SESSION['user']);
    $pago->setUsuarioUltimaModificacion($_SESSION['user']);
    
    if (isset($parametros['pago']) && $parametros['pago'] != "") {
        $pago->setId_pago($parametros['pago']);
        if ($pago->updateRegistro()) {
            echo "<h2>Se ha editado el pago parcial exitosamente</h2>";
        } else {
            echo "Error: El pago parcial no se logro editar";
        }
    } else {
        if ($pago->nuevoRegistro()) {
            echo "<h2>Se ha registrado el nuevo pago parcial</h2>";
        } else {
            echo "Error: El pago parcial no se registro";            
        }
    }
}

