<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
include_once('../WEB-INF/Classes/Factura2.class.php');
if(isset($_GET['id'])&&$_GET['id']!=""){
    $factura = new Factura();
    if($factura->deleteFactura($_GET['id'])){
        echo "La prefactura se elimino correctamente";
    }else{
        echo "La prefactura no se puedo eliminar correctamente";
    }
}else{
    echo "La prefactura no se pudo eliminar no se recibió el ID";
}
?>
