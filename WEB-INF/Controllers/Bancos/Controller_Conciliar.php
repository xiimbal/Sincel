<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
include_once("../../Classes/Catalogo.class.php");

$catalogo = new Catalogo();

if(isset($_POST['idFactura'])){
    $update = "UPDATE c_movimientoBancario SET id_pago = ".$_POST['idFactura'] .",tipoConciliacion = 'M' WHERE id_movimientoBancario = ".$_POST['idMovimiento'];
}else if(isset($_POST['desconciliar']))
{
    $update = "UPDATE c_movimientoBancario SET id_pago = NULL, tipoConciliacion = NULL WHERE id_pago = ".$_POST['desconciliar']." AND tipo = '".$_POST['tipo']."'";
}
$catalogo->obtenerLista($update);
?>
