<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/Equipo.class.php");
$obj = new Equipo();
if (isset($_GET['id']) && $_GET['id2']) {/* Para eliminar el registro con el id recibido por get */
    $obj->setNoParte($_GET['id']);
    
    if ($obj->deleteEquipoSimiliar($_GET['id2'])) {
        echo "El equipo similar se eliminó correctamente";
    } else {
        echo "El equipo similar no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setNoParte($parametros['idE']);    
    
    if (isset($parametros['idES']) && $parametros['idES'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        
        if($obj->agregarEquipoSimilar($parametros['equipoSimilar'])){
            echo "Se registro el equipo similiar ".$parametros['equipoSimilar']." correctamente";
        }else{
            echo "<br/>Error: no se pudo registrar el equipo similar";
        }        
    } else {/* Modificar */
        if($obj->actualizarEquipoSimilar($parametros['idES'],$parametros['equipoSimilar'])){
            echo "Se registro el equipo similiar ".$parametros['equipoSimilar']." correctamente";
        }else{
            echo "<br/>Error: no se pudo registrar el equipo similar";
        } 
    }
}
?>