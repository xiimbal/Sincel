<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/EntregaRefaccion.class.php");
if (isset($_POST['parametros'])) {
    $obj = new EntregaRefaccion();
    $paramentro = $_POST['parametros'];
    $cantidadEntregar = $_POST['cantidadEntrega'];
    $variable = explode("*", $paramentro);
    $nota = $variable[1];
    $noParte = $variable[2];
    $cliente = $variable[4];
    $idalmacen = $_POST['almacen'];
    $obj->setId($_POST['idAtendido']);
    $obj->setIdAlmacenAnterior($idalmacen);
    $cantidadExistente = $obj->getCantidadALmacen($noParte);
    if ($cantidadEntregar <= $cantidadExistente) {
        $total = $cantidadEntregar + $obj->getRegistroById($_POST['idAtendido']);
        $obj->setNota($nota);
        $obj->setNoParte($noParte);
        $obj->setCantidadEntregar($total);
        if ($obj->editRegistro()) {
            $idTicket = $variable[3];
            $obj->setIdTicket($idTicket);
            $obj->setCantidadEntregar($cantidadEntregar);
            $obj->setUsuarioCreacion($_SESSION['user']);
            $obj->setUsuarioModificacion($_SESSION['user']);
            $obj->setPantalla("Entrega de componentes");

            $obj->setClaveClienteNuevo($cliente);
            if ($obj->newMovimiento()) {
                //  $cantidadExistente = $obj->getCantidadALmacen($noParte);
                $cantidadTotal = $cantidadExistente - $cantidadEntregar;
                $obj->setCantidad($cantidadTotal);
                if ($obj->editAlmacenComponentes()) {
                    echo "El movimiento se creo correctamente";
                }
            }
        } else {
            echo "El movimiento no se creo correctamente";
        }
    } else {
        echo "EL almacen <b> ".$obj->getIdAlmacenAnterior()." </b> no cuenta con la cantidad solicitada";
    }
}
?>
