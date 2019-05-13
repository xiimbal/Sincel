<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Usuario.class.php");
include_once("../Classes/Conductor.class.php");
$obj = new Usuario();
$obj_aux = new Usuario();

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setId($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El usuario se eliminó correctamente";
    } else {
        echo "El usuario no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }

    //print_r($parametros);

    $obj->setUsuario($parametros['usuario']);
    $obj->setNombre($parametros['nombre']);
    $obj->setPaterno($parametros['paterno']);
    $obj->setMaterno($parametros['materno']);
    $obj->setPassword($parametros['pass1']);
    $obj->setEmail($parametros['correo']);
    $obj->setPuesto($parametros['puesto']);
    $obj->setIdAlmacen($parametros['almacen']);
    $obj->setIdUsuarioMultiBD($parametros['idUsuarioMBD']);
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('PHP admin usuario');
    $obj->setProveedorFactura($parametros['proveedorF']);
    if(isset($parametros['rfc'])){
        $obj->setRFC($parametros['rfc']);
    }

    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if($obj_aux->getUsuarioByUser($obj->getUsuario())){
            echo "Error: El usuario <b>".$obj->getUsuario()."</b> ya está registrado, intenta con otro nombre de usuario";
            return;
        }
        if ($obj->newRegistro()) {
            echo "El usuario <b>" . $obj->getUsuario() . "</b> se registró correctamente";
            $obj->registrarNegociosDeUsuario($parametros['negocios']);
            
            $conductor = new Conductor();
            /* Verificamos si se va a guardar como mensajero */
            if (isset($parametros['mensajero']) && $parametros['mensajero'] == "Si") {
                $conductor->setNombre($obj->getNombre());
                $conductor->setApellidoPaterno($obj->getPaterno());
                $conductor->setApellidoMaterno($obj->getMaterno());
                $conductor->setIdUsuario($obj->getId());
                $conductor->setActivo($obj->getActivo());
                $conductor->setUsuarioCreacion($obj->getUsuarioCreacion());
                $conductor->setUsuarioUltimaModificacion($obj->getUsuarioModificacion());
                $conductor->setPantalla($obj->getPantalla());
                if ($conductor->newRegistro()) {
                    echo " y se registró como mensajero";
                } else {
                    echo "Error: no se pudo insertar el usuario como mensajero, intenta de nuevo o repórtalo por favor.";
                }
            }
        } else {
            echo "Error: El usuario " . $obj->getUsuario() . " ya se encuentra registrado";
        }
    } else {/* Modificar */
        $obj->setId($parametros['id']);
        if($obj_aux->getUsuarioByUser($obj->getUsuario()) && $obj_aux->getId() != $obj->getId()){
            echo "Error: El usuario <b>".$obj->getUsuario()."</b> ya está registrado, intenta con otro nombre de usuario";
            return;
        }
        
        if (isset($parametros['cambiar']) && $parametros['cambiar'] == "on") {/* Si se acciono el boton de modificar password */            
            if ($obj->editarRegistroConPassword()) {
                echo "El usuario <b>" . $obj->getUsuario() . "</b> se modificó correctamente";
                $obj->registrarNegociosDeUsuario($parametros['negocios']);
            } else {
                echo "Error: El usuario " . $obj->getUsuario() . " ya se encuentra registrado";
            }
        } else {            
            if ($obj->editRegistro()) {
                echo "El usuario <b>" . $obj->getUsuario() . "</b> se modificó correctamente";
                $obj->registrarNegociosDeUsuario($parametros['negocios']);
            } else {
                echo "Error: El usuario " . $obj->getUsuario() . " ya se encuentra registrado";
            }
        }
        $conductor = new Conductor();
        /* Verificamos si se va a guardar como mensajero */
        if (isset($parametros['mensajero']) && $parametros['mensajero'] == "Si") {
            $conductor->setNombre($obj->getNombre());
            $conductor->setApellidoPaterno($obj->getPaterno());
            $conductor->setApellidoMaterno($obj->getMaterno());
            $conductor->setIdUsuario($obj->getId());
            $conductor->setActivo($obj->getActivo());
            $conductor->setUsuarioCreacion($obj->getUsuarioCreacion());
            $conductor->setUsuarioUltimaModificacion($obj->getUsuarioModificacion());
            $conductor->setPantalla($obj->getPantalla());
            if ($obj->isMensajeroConductor()) {
                $conductor->setIdConductor($conductor->getIdConductorByIdUsuario());
                if ($conductor->editRegistro()) {
                    echo " y se editó como mensajero";
                } else {
                    echo "Error: no se pudo editar el usuario como mensajero, intenta de nuevo o repórtalo por favor.";
                }
            } else {
                if ($conductor->newRegistro()) {
                    echo " y se registró como mensajero";
                } else {
                    echo "Error: no se pudo insertar el usuario como mensajero, intenta de nuevo o repórtalo por favor.";
                }
            }
        } else {
            if (!$conductor->deleteRegistroByIdUsuario($obj->getId())) {
                echo "<br/>Error: no se pudo eliminar al usuario como mensajero";
            } else {
                echo " y no se registró como mensajero";
            }
        }
    }
}
?>