<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['idFactura'])) {
    header("Location: ../../../index.php");
}

include_once("../../Classes/Factura2.class.php");
$factura = new Factura();

if($factura->validarDescripcionConceptos($_POST['idFactura'])){    
    return true;
}else{    
    return false;
}
?>
