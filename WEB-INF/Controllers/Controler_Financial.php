<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Financial.class.php");
$obj = new Financial();

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdPrestamo($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El registro se eliminó correctamente";
    } else {
        echo "El registro no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }

    $obj->setFecha($parametros['Fecha']); 
    $obj->setIdOperador($parametros['IdOperador']); 
    $obj->setIdEstatus($parametros['IdEstatus']); 
    $obj->setIdTipoRetencion($parametros['IdTipoRetencion']); 
    $obj->setPorcentajeInteres($parametros['PorcentajeInteres']); 
    $obj->setComentario($parametros['Comentario']);        
    
    $date = new DateTime($obj->getFecha());
    $week = $date->format("W");
    $last_monday = date('Y-m-d', strtotime('previous monday', strtotime($obj->getFecha())) );    
    $obj->setSemana($week); 
    $obj->setFechaSemana($last_monday); 
    
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setPantalla('Financial PHP');

    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            $obj->insertarDetalles($parametros);
            echo "El registro " . $obj->getIdPrestamo() . " se registró correctamente";                        
        } else {
            echo "Error:No se pudo registrar el préstamo";
        }
    } else {/* Modificar */
        $obj->setIdPrestamo($parametros['id']);
        if ($obj->updateRegistro()) {
            $obj->insertarDetalles($parametros);
            echo "El registro " . $obj->getIdPrestamo() . " se modificó correctamente";                
        } else {
            echo "Error: El registro no se pudo actualizar";
        }
    }
}
?>