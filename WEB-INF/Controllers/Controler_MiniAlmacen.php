<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/MiniAlmacen.class.php");
$obj = new MiniAlmacen();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdminiAlmacen($_GET['id']);
    if ($obj->deleteRegistro())
        echo "El mini almacen se eliminó correctamente";
    else
        echo "El mini almacen no se pudo eliminar, ya que contiene datos asociados.";
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setNombre($parametros['nombre']);
    $obj->setDescripcion($parametros['descripcion']);
    $obj->setClaveCentroCosto($parametros['localidad']);
    $obj->setClaveEncargado($parametros['encargado']);
    if (isset($parametros['activo']) && $parametros['activo'] == "on")
        $obj->setActivo(1);
    else
        $obj->setActivo(0);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Alta mini almacén');
    if (isset($parametros['id']) && $parametros['id'] == "") {
        if ($obj->verificar()) {
            if ($obj->newRegistro())
                echo "El mini almacén <b>" . $obj->getNombre() . "</b> se registró correctamente";
            else
                echo "El mini almacén <b>" . $obj->getNombre() . "</b> no se registro correctamente, intenté de nuevo por favor";
        } else {
            echo "Error: La localidad ya cuenta con un mini almacén, intenté con otro por favor";
        }
    } else {
        $obj->setIdminiAlmacen($parametros["id"]);
        if ($parametros["idlocalidad"] == $parametros["localidad"]) {
            if ($obj->editRegistro())
                echo "El mini almacen <b>" . $obj->getNombre() . "</b> se modificó correctamente";
            else
                echo "El mini almacen <b>" . $obj->getNombre() . "</b> no se modificó correctamente";
        } else {
            if ($obj->verificar()) {
                if ($obj->editRegistro())
                    echo "El mini almacen <b>" . $obj->getNombre() . "</b> se modificó correctamente";
                else
                    echo "El mini almacen <b>" . $obj->getNombre() . "</b> no se modificó correctamente";
            } else {
                echo "Error: La localidad ya cuenta con un mini almacén, intenté con otro por favor";
            }
        }
    }
}
?>
