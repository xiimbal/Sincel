<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/ValidarRefacciones.class.php");

$obj = new ValidarRefaccion();
if (isset($_POST['idNota']) && $_POST['idNota'] != "") {
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Validar Refaccion');
    $obj->setIdNotaTicket($_POST['idNota']);
    if ($obj->CambiarEstatus()) {
        echo "El estado de la nota se modificó correctamene";
    }
    else
    {
        echo "Error: El Estatus no se modificó correctamene";
    }
}
?>
