<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../../Classes/PAC.class.php");
$obj = new PAC();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setId_pac($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El PAC se eliminó correctamente";
    } else {
        echo "El PAC no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setNombre($parametros['nombre']);
    $obj->setUsuario($parametros['usuario']);
    $obj->setPassword($parametros['password']);
    $obj->setDireccion_cancelacion($parametros['dir_cancelacion']);
    $obj->setDireccion_timbrado($parametros['dir_timbre']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setPantalla("PHP Alta PAC");
    if(isset($parametros['id'])&&$parametros['id']!=""){
        $obj->setId_pac($parametros['id']);
        if($obj->editRegistro()){
            echo "Se actualizo correctamente el PAC";
        }else{
            echo "Error: No se pudo actualizar el PAC intente mas tarde o contacte al administrador";
        }
    }else{
        if($obj->nuevoRegistro()){
            echo "Se registro correctamente el PAC";
        }else{
            echo "Error: No se pudo registrar el PAC intente mas tarde o contacte al administrador";
        }
    }
}
?>
