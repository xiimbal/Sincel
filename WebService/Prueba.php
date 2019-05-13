<?php

include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");

$usuario_nuevo = "victorg";
$password_nuevo = "a725a6e3d52ac16a1856dca8317e8c7a";
$nombre = "Hugo";
$apellido_paterno = "Santiago";
$apellido_materno = "Hdz";
$puesto = "41";
$activo = "1";
$email = "hugosh189@hotmail.com";
$idempresa = "3";
$usuario = "super_guau";
$password = "1ceabe9e7e2f8faf6dee4889b72a0c1d";

$empresa = $idempresa;
$session = new Session();
$session->setEmpresa($empresa);
if (($session->getLogin($usuario, $password)) != "") {
    if (strlen($password_nuevo) < 8) {
        echo -6; //El password no debe de tener menos de 8 caracteres
    }

    $user = "NuevoUsuario WS";
    $pantalla = "NuevoUsuario WS";
    $obj = new Usuario();
    $obj_aux = new Usuario();
    $obj->setEmpresa($empresa);
    $obj_aux->setEmpresa($empresa);
    $obj->setPuesto($puesto);
    $obj->setNombre($nombre);
    $obj->setPaterno($apellido_paterno);
    $obj->setMaterno($apellido_materno);
    $obj->setUsuario($usuario_nuevo);
    $obj->setPassword($password_nuevo);
    $obj->setActivo($activo);
    $obj->setUsuarioCreacion($user);
    $obj->setUsuarioModificacion($user);
    $obj->setPantalla($pantalla);
    $obj->setEmail($email);
    if (!$obj->getUsuarioByUser($usuario_nuevo)) {
        if ($obj_aux->getRegistroByEmail($email)) {
            if ($puesto == "41") {//Si es usuario fb
                echo -4;
            } else {//Si no es usuario fb
                echo -3;
            }
        }

        $obj->setActivo(1);
        if ($obj->newRegistroSinEcriptar()) {
            $respuesta = $session->generarClaveSession(15, "");
            $respuesta['IdUsuario'] = $session->getId_usu();
            $valores = array();
            array_push($valores, $respuesta);
            echo json_encode($valores);
        } else {
            echo -2;
        }
    } else {
        $session->setId_usu($obj->getId());
        if ($obj_aux->getRegistroByEmail($email) && $obj_aux->getId() != $obj->getId()) {
            if ($puesto == "41") {//Si es usuario fb
                echo -4;
            } else {//Si no es usuario fb
                echo -3;
            }
        }

        $obj->setEmpresa($empresa);
        $obj->setPuesto($puesto);
        $obj->setNombre($nombre);
        $obj->setPaterno($apellido_paterno);
        $obj->setMaterno($apellido_materno);
        $obj->setUsuario($usuario_nuevo);
        $obj->setPassword($password_nuevo);
        $obj->setActivo($activo);
        $obj->setUsuarioCreacion($user);
        $obj->setUsuarioModificacion($user);
        $obj->setPantalla($pantalla);
        $obj->setEmail($email);
        if ($obj->editarRegistroConPasswordSinEcriptar()) {
            $respuesta = $session->generarClaveSession(15, "");
            $respuesta['IdUsuario'] = $session->getId_usu();
            $valores = array();
            array_push($valores, $respuesta);
            echo json_encode($valores);
        } else {
            echo -5; //Error al editar el password
        }
    }
} else {
    echo -1;
}