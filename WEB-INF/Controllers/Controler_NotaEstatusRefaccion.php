<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/NotaEstatusRefaccion.class.php");
include_once("../Classes/AgregarNota.class.php");
$obj = new NotaEstatusRefaccion();
$obj1 = new AgregarNota();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdArea($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El Área se eliminó correctamente";
    } else {
        echo "El Área no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    $obj->setNota($_POST['nota']);
    $obj->setNoParte($_POST['noParte']);
    $obj->setCantidad($_POST['cantidadAtendida']);
    $obj->setEstatus($_POST['idEstatus']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Cabio de estado');
    $obj->setId($_POST['idAtencion']);
    $obj1->setIdTicket($_POST['ticket']);
    $obj1->setIdestatusAtencion($_POST['idEstatus']);
    $obj1->setDiagnosticoSolucion($_POST['diagnostico']);
    $obj1->setUsuarioCreacion($_SESSION['user']);
    $obj1->setUsuarioModificacion($_SESSION['user']);
    $obj1->setPantalla('Refacciones solicitadas');
    $obj->setUsuarioSolicitud($_POST['usuario']);

    $obj1->setActivo(1);
    //echo $_POST['idAtencion'];
    if (isset($_POST['nota']) && $_POST['nota'] != "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            $restante = $obj->obtenerCantidad() - $_POST['cantidadAtendida'];
            $obj->setCantidad($restante);
            if ($obj->editRegistro()) {
                if ($obj1->newRegistro())
                    echo "El componente  <b>" . $obj->getNoParte() . "</b> se atendio correctamente";
            }
        } else {
            echo "Error: El área <b>" . $obj->getNoParte() . "</b> ya se encuentra registrado";
        }
    } else {/* Modificar */
        echo "modificar";
    }
}
?>
