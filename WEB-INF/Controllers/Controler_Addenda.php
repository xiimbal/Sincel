<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Addenda.class.php");
$obj = new Addenda();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setId_addenda($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "La addenda se eliminó correctamente";
    } else {
        echo "La addenda no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setNombre_addenda($parametros['nombre_addenda']);
    $obj->setActivo(1);    
    $nombres = array();
    $valores = array();
    $dinamicos = array();
    $numero_conceptos = $parametros['numero_conceptos'];
    for($i=1; $i<=$numero_conceptos; $i++){        
        array_push($nombres, $parametros['nombre_'.$i]);
        array_push($valores, $parametros['valor_'.$i]);
        if(isset($parametros['dinamico_'.$i]) && $parametros['dinamico_'.$i] == "on"){
            array_push($dinamicos, 1);
        }else{
            array_push($dinamicos, 0);
        }        
    }
    $obj->setNombres($nombres); $obj->setValores($valores);
    $obj->setDinamicos($dinamicos);
    
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setPantalla('PHP Controller_Addenda');
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "La addenda <b>" . $obj->getNombre_addenda() . "</b> se registró correctamente";            
        } else {
            echo "Error: La addenda <b> " . $obj->getNombre_addenda() . "</b> no se pudo registrar, comunicalo con el administrador por favor";
        }
    } else {/* Modificar */
        $obj->setId_addenda($parametros['id']);
        if ($obj->editRegistro()) {
            echo "La addenda <b> " . $obj->getNombre_addenda() . "</b> se modificó correctamente";            
        } else {
            echo "Error: La addenda <b> " . $obj->getNombre_addenda() . "</b> no se pudo modificar, comunicalo con el administrador por favor";
        }
    }
}
?>
