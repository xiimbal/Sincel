<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/AlmacenConmponente.class.php");
include_once("../Classes/MovimientoComponente.class.php");
include_once("../Classes/Componente.class.php");
include_once("../Classes/Ticket.class.php");

$obj = new AlmacenComponente();
$movientoAlmacen = new MovimientoComponente();
$ticket = new Ticket();

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}
$usuario = $_SESSION['user'];
$pantalla = "Entrada componentes o devoluciones";

if (!isset($parametros['ticket_devolucion']) || $parametros['ticket_devolucion'] == "" || $ticket->getTicketByID($parametros['ticket_devolucion'])) {
    $obj->setCantidadSalida($parametros['cantidadExis']);
    $obj->setIdAlmacen($parametros['almacen']);
    $obj->setNoParte($parametros['parte']);
    $obj->setUsuarioCreacion($usuario);
    $obj->setUsuarioModificacion($usuario);
    $obj->setPantalla($pantalla);

    if ($obj->editarCantidadAlmacenReusrtir()) {
        $movientoAlmacen->setNoParteComponente($obj->getNoParte());
        $movientoAlmacen->setCantidadMovimiento($obj->getCantidadSalida());
        $movientoAlmacen->setIdAlmacenNuevo($obj->getIdAlmacen());
        $movientoAlmacen->setUsuarioCreacion($usuario);
        $movientoAlmacen->setUsuarioModificacion($usuario);
        $movientoAlmacen->setPantalla($pantalla);
        $movientoAlmacen->setEntradaSalida(0);
        $movientoAlmacen->setComentario($parametros['comentario']);
        $movientoAlmacen->setIdTicketDevolucion($parametros['ticket_devolucion']);
        if ($movientoAlmacen->newRegistroCompraComponente()) {
            $componente = new Componente();
            if ($componente->getRegistroById($obj->getNoParte())) {
                echo "Se aumentó las existencias en " . $obj->getCantidadSalida() . " del componente " . $componente->getModelo() . " correctamente";
            } else {
                echo "Se aumentó las existencias en " . $obj->getCantidadSalida() . " del componente correctamente";
            }
        } else {
            echo "Error: no se pudo registrar el movimiento de componente";
        }
    } else {
        echo "Error: no se pudo ingresar la cantidad al almacén";
    }
}else{
    echo "Error: Ingresa un folio válido y existente en el sistema";
}
?>