<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/Arrendamiento.class.php");

$obj = new Arrendamiento();
if (isset($_GET['id']) && isset($_GET['id2'])) {
    $obj->setIdArrendamiento($_GET['id']);
    $obj->setIdModalidad($_GET['id2']);
    if ($obj->deleteRegistro()) {
        echo "El arrendamiento se eliminó correctamente";
    } else {
        echo "EL arrendamiento no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setIdModalidad($parametros['idM']);
    $obj->setNombre($parametros['nombre']);
    $obj->setTipo($parametros['tipo']);
    
    if (isset($parametros['rentaMensual']) && $parametros['rentaMensual'] == "on"){
        $obj->setRenta(1);
    }else{
        $obj->setRenta(0);
    }
    
    if (isset($parametros['incluidaBN']) && $parametros['incluidaBN'] == "on"){
        $obj->setIncluidoBN(1);
    }else{
        $obj->setIncluidoBN(0);
    }
    
    if (isset($parametros['incluidaColor']) && $parametros['incluidaColor'] == "on"){
        $obj->setIncluidoColor(1);
    }else{
        $obj->setIncluidoColor(0);
    }
    
    if (isset($parametros['excedenteBN']) && $parametros['excedenteBN'] == "on"){
        $obj->setExcedenteBN(1);
    }else{
        $obj->setExcedenteBN(0);
    }
    
    if (isset($parametros['excedenteColor']) && $parametros['excedenteColor'] == "on"){
        $obj->setExcedenteColor(1);
    }else{
        $obj->setExcedenteColor(0);
    }
    
    if (isset($parametros['costoBN']) && $parametros['costoBN'] == "on"){
        $obj->setCostoBN(1);
    }else{
        $obj->setCostoBN(0);
    }
    
    if (isset($parametros['costoColor']) && $parametros['costoColor'] == "on"){
        $obj->setCostoColor(1);
    }else{
        $obj->setCostoColor(0);
    }
    
    if (isset($parametros['activo']) && $parametros['activo'] == "on"){
        $obj->setActivo(1);
    }else{
        $obj->setActivo(0);
    }
    
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('c_arrendamiento');
    
    if (isset($parametros['idA']) && $parametros['idA'] == "") {
        if ($obj->newRegistro()){
            echo "El arrendamiento se registró correctamente";
            if(isset($parametros['servicios'])){
                $obj->actualizarServicios($parametros['servicios']);
            }
        }else{
            echo "Error: El arrendamiento ya se encuentra registrado";
        }
    }else {
        $obj->setIdArrendamiento($parametros['idA']);
        if ($obj->editRegistro()) {
            echo "El arrendamiento se modificó correctamente";
            if(isset($parametros['servicios'])){
                $obj->actualizarServicios($parametros['servicios']);
            }
        } else {
            echo "Error: El arrendamiento ya se encuentra registrado";
        }
    }
}
?>
