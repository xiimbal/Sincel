<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../Classes/Usuario.class.php");

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
    $usuario = new Usuario();
    $usuario->setNombre($parametros['Nombre']);
    $usuario->setPaterno($parametros['Appat']);
    $usuario->setMaterno($parametros['Apmat']);
    $usuario->setEmail($parametros['Correo']);
    $usuario->setUsuario($parametros['Username']);
    $usuario->setId($_SESSION['idUsuario']);
    $usuario->setUsuarioModificacion($_SESSION['user']);
    if (isset($parametros['Checkc']) && $parametros['Checkc'] == 1) {
        $usuario->setPassword($parametros['Contra']);
        if ($usuario->editarRegistroSimplePassword()) {
            echo "Datos actualizados correctamente";
        } else {
            echo "Ocurrió un error al cambiar el password";
        }
    } else {
        if ($usuario->editarRegistroSimple()) {
            echo "Datos actualizados correctamente";
        } else {
            echo "Ocurrió un error al actualizar";
        }
    }
}
?>
