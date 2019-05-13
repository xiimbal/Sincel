<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../../Classes/GrupoEmpresas.class.php");
$obj = new GrupoEmpresas();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdGrupoEmpresa($_GET['id']);
    if ($obj->deletebyID()) {
        echo "El grupo se eliminó correctamente";
    } else {
        echo "El grupo no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setDescripcion($parametros['Descripcion']);
    $obj->setEmpresas(explode(",", $_POST['empresa']));
    $obj->setUsuarioCreacion("NOW()");
    $obj->setFechaCreacion("NOW()");
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setFechaModificacion($_SESSION['user']);
    $obj->setPantalla("PHP Controller_GrupoEmpresa.php");
    if(isset($parametros['id'])&&$parametros['id']!=""){//editar
        $obj->setIdGrupoEmpresa($parametros['id']);
        if($obj->editRegistro()){
            echo "Se actualizo correctamente el Grupo";
        }else{
            echo "Error: No se pudo actualizar el grupo intente mas tarde o contacte al administrador";
        }
    }else{
        if($obj->nuevoRegistro()){
            echo "Se registro correctamente el grupo";
        }else{
            echo "Error: No se pudo registrar el grupo intente mas tarde o contacte al administrador";
        }
    }
}
?>
