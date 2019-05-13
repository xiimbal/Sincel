<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/MovimientoBancario.class.php");
if (isset($_POST['clave']) && $_POST['clave'] != "") {
    $mov = new MovimientoBancario();
    if ($mov->deleteRegistro($_POST['clave'])) {
        echo "Se ha eliminado la cuenta bancaria exitosamente";
    } else {
        echo "Error: la cuenta bancaria tiene valores dependientes";
    }
} else {
    $parametros = "";
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $mov = new MovimientoBancario();
    $mov->setIdBanco($parametros['componenteCopiar']);
    $mov->setComentario($parametros['comentario']);
    $mov->setFactura($parametros['factura']);
    $mov->setFecha($parametros['fecha']);
    $mov->setTipo($parametros['tipo']);
    $mov->setTotal($parametros['total']);
    $mov->setNoCuenta($parametros['noCuenta']);
    $mov->setReferencia($parametros['referencia']);
    $mov->setPantalla("PHP Controller_MovimientoBancario");
    $mov->setUsuarioCreacion($_SESSION['user']);
    $mov->setUsuarioUltimaModificacion($_SESSION['user']);
    if (isset($parametros['id']) && $parametros['id'] != "") {
        $mov->setIdMovimientoBancario($parametros['id']);
        if ($mov->editRegistro()) {
            echo "Se ha editado la cuenta exitosamente";
        } else {
            echo "Error: La cuenta bancaria no se logro editar";
        }
    } else {
        if ($mov->newRegistro()) {
            echo "Se ha registrado la nueva cuenta bancaria";
        } else {
            echo "Error: La cuenta bancaria no se registro";
        }
    }
}
?>
