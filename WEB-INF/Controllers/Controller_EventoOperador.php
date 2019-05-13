<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/EventoOperador.class.php");
include_once("../Classes/Usuario.class.php");
include_once("../Classes/Catalogo.class.php");

$obj = new EventoOperador;
$catalogo = new Catalogo;
$oper = "";
//print_r($_POST);
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdBitacora($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El evento se elimino correctamente.";
    } else {
        echo "El evento no se pudo eliminar.";
    }
} else {
    $obj->setFecha($_POST['fecha']);
    $obj->setHora($_POST['hora']);
    $obj->setLineaNegocio($_POST['ln']);
    $obj->setEvento($_POST['evento']);
    $obj->setOperador($_POST['operador']);
    $obj->setComentario($_POST['comentario']);
    $obj->setDato($_POST['dato']);
    $obj->setServicio($_POST['servicio']);

    if (isset($_POST['activo']) && $_POST['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Catálogo de Bitácora (Evento de Operador)');

    $usuario = new Usuario();
    if ($_POST['operador'] != 0) {
        $usuario->getRegistroById($_POST['operador']);
        $nombre = $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno();
        $oper = " con el operador <b>$nombre</b>";
    }

    if (isset($_POST['id']) && $_POST['id'] == "") {
        if ($obj->newRegistro()) {
            echo "El evento se registró correctamente" . $oper;
            //print_r($_FILES);
            if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") {
                echo "<br/><b>Se subió el archivo seleccionado</b><br/>";
                $rutaArchivo = "../../nota/uploads/" . $obj->getIdBitacora() . "-" . $obj->getLineaNegocio() . $_FILES['file']['name'];
                move_uploaded_file($_FILES['file']['tmp_name'], $rutaArchivo);
                $rutaArchivo = "nota/uploads/" . $obj->getIdBitacora() . "-" . $obj->getLineaNegocio() . $_FILES['file']['name'];
                $obj->setPathImagen($rutaArchivo);
                $updateI = $obj->updateImagen();
            }
        } else {
            echo "El evento NO se registró correctamente";
        }
    } else {
        $obj->setIdBitacora($_POST['id']);
        if ($obj->editRegistro()) {
            echo "El evento se modificó correctamente" . $oper;
        } else {
            echo "El evento del operador NO se modificó correctamente";
        }
    }
}
?>

