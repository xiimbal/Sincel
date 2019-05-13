<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/AlmacenEquipo.class.php");
include_once("../Classes/Configuracion.class.php");
include_once("../Classes/Movimiento.class.php");
include_once("../Classes/Equipo.class.php");
include_once("../Classes/ReporteLectura.class.php");

$obj = new AlmacenEquipo();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setNoSerie($_GET['id']);
    if ($obj->deleteRegistro()) {
        /* if ($obj->deleteBitacora()) */
        echo "El número de serie se eliminó correctamente";
    } else {
        echo "El número de serie no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setNoSerie($parametros['serie']);
    $obj->setIdAlmacen($parametros['almacen']);
    $obj->setNoParteEquipo($parametros['equipo']);
    $obj->setUbicacion($parametros['ubicacion']);
    $obj->setFechaIngreso($parametros['fechaHora']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('PHP Almacen-Equipo');
    
    /*Verificamos que la serie coincida con el prefijo del modelo*/
    $configuracion = new Configuracion();
    if(!$configuracion->validarSerie($obj->getNoSerie(), $obj->getNoParteEquipo())){
        return false;
    }
    
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        $respuesta_bitacora = $obj->newEquipoBitacora();
        if ($respuesta_bitacora != "2") {
            $respuesta_registro = $obj->newRegistro();
            if ($respuesta_registro == "1") {
                if ($obj->newMovimientoEquipo()) {
                    if ($obj->getModeloInventario($obj->getNoSerie())) {
                        echo "El número de serie <b> " . $obj->getNoSerie() . " </b> con el número de parte <b>" . $obj->getNoParteEquipo() . "</b>  y modelo <b>" . $obj->getModelo() . "</b> se registró correctamente";
                    }
                } else {
                    echo "No se pudo registrar el movimiento de equipo.";
                }
            } else if ($respuesta_registro == "2") {
                echo "Error: El número de parte <b> " . $obj->getNoParteEquipo() . " </b> no existe en el catálogo de equipo";
            } else if ($respuesta_registro == "3") {
                echo "Error: El número de serie <b>" . $obj->getNoSerie() . "</b> ya se encuentra registrado en almacén, intenté con otro por favor";
            } else if ($respuesta_registro == "4") {
                echo "Error: el numero de serie <b>" . $obj->getNoSerie() . "</b> ya se encuentra asignado a un cliente";
            } else {
                echo "El número de serie <b>" . $obj->getNoSerie() . "</b> ya se encuentra registrado";
            }
        } else {
            echo "Error: no se pudo registrar la bitácora del equipo porque el No de parte no existe";
        }
    } else {/* Modificar */        
        $obj->setId($parametros['id']);
        $obj->setNoSerie($parametros['serie']);
        $obj2 = new AlmacenEquipo();
        $obj2->getRegistroById($parametros['id']);
        if ($obj->deleteRegistro()) {            
            $respuesta_bitacora = $obj->editBitacora();
            if ($respuesta_bitacora == "1") {
                $respuesta_registro = $obj->newRegistro();
                if ($respuesta_registro == "1") {
                    if($obj2->getApartado() != null && $obj2->getClaveCentroCosto()!=null){/*Volvemos a apartar el equipo en caso de que ya hay estado apartado*/
                        $configuracion = new Configuracion();
                        $configuracion->setNoSerie($obj->getNoSerie());
                        $configuracion->setClaveCentroCosto($obj2->getClaveCentroCosto());
                        $configuracion->apartarEquipoEnAlmacen();
                    }
                    if($obj2->getIdAlmacen() != $obj->getIdAlmacen()){/*Se registra un movimeinto en dado caso que se haya movido de almacen*/
                        $movimiento = new Movimiento();
                        $movimiento->nuevoMovimientoAlmacenAlmacen($obj->getNoSerie(), $obj2->getIdAlmacen(), $obj->getIdAlmacen(), "PHP Almacen Equipo");
                    }                    
                    if ($obj->getModeloInventario($obj->getNoSerie())) {
                        echo "El número de serie <b> " . $obj->getNoSerie() . " </b> con el número de parte <b>" . $obj->getNoParteEquipo() . "</b>  y modelo <b>" . $obj->getModelo() . "</b> se registró correctamente";
                    }                    
                } else if ($respuesta_registro == "2") {
                    echo "Error: El número de parte <b> " . $obj->getNoParteEquipo() . " </b> no existe en el catálogo de equipo";
                } else if ($respuesta_registro == "3") {
                    echo "Error: El número de serie <b>" . $obj->getNoSerie() . "</b> ya se encuentra registrado en almacén, intenté con otro por favor";
                } else if ($respuesta_registro == "4") {
                    echo "Error: el numero de serie <b>" . $obj->getNoSerie() . "</b> ya se encuentra asignado a un cliente";
                } else {
                    echo "El número de serie <b>" . $obj->getNoSerie() . "</b> ya se encuentra registrado";
                }
            }else{
                echo "Error: No se pudo editar la bitácora del equipo";
            }
        } else {
            echo "Error: El número de serie <b>" . $obj->getNoSerie() . "</b> no se pudo modificar correctamente";
        }
    }
}
?>
