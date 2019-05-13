<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/TFSGrupoCliente.class.php");

$obj = new TFSGrupoCliente();
if (isset($_GET['id']) && $_GET['id2']) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdTfs($_GET['id']);
    $obj->setClaveGrupo($_GET['id2']);

    if ($obj->deleteRegistro()) {
        echo "La relación TFS - Grupo fue desasociada correctamente";
    } else {
        echo "La relación TFS - Grupo fue no pudo desasociarse, intente de nuevo o comuníquelo por favor.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    if(isset($parametros['tfs'])){
        $obj->setIdTfs($parametros['tfs']);
    }else{
        $obj->setIdTfs($parametros['id_tfs']);
    }
    $obj->setClaveGrupoAnterior($parametros['ClaveGrupoAnterior']);
    $obj->setClaveGrupo($parametros['grupo']);
    $obj->setActivo(1);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setPantalla("PHP TFS-Grupo");

    if (isset($parametros['ClaveGrupoAnterior']) && $parametros['ClaveGrupoAnterior'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if($obj->newRegistro()){
           echo "La asociación TFS - Cliente se registró correctamente"; 
        }else{
            echo "Error: No se pudo asociar el TFS con el grupo, es probable que está relación ya exista, intente de nuevo o comuníquelo por favor.";
        }
    }else{/* Modificar */
        if($obj->editarRegistro()){
           echo "La asociación TFS - Cliente se modificó correctamente"; 
        }else{
            echo "Error: No se pudo asociar el TFS con el grupo, es probable que está relación ya exista, intente de nuevo o comuníquelo por favor.";
        }
    }
}
?>

