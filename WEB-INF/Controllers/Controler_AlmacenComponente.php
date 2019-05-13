<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/AlmacenConmponente.class.php");
include_once("../Classes/MovimientoComponente.class.php");
include_once("../Classes/CambiosMiniAlmacen.class.php");

$obj = new AlmacenComponente();
$movientoAlmacen = new MovimientoComponente();
if (isset($_GET['id']) && isset($_GET['id2'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setNoParte($_GET['id']);
    $obj->setIdAlmacen($_GET['id2']);
    if ($obj->deleteRegistro()) {
        echo "El componente <b>" . $_GET['modelo'] . "</b> se eliminó correctamente";
    } else {
        echo "El componente <b>" . $_GET['modelo'] . "</b> no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    $tipoAlmacen = "";
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }


    $obj->setExistencia($parametros['cantidad']);
    $obj->setApartados($parametros['apartados']);
    $obj->setUbicacion($parametros['txtUbicacion']);
    $modelo = "";
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('PHP Almacen-Componente');

    if (isset($parametros['noParte'])) {
        $pos = strpos($parametros['noParte'], " / ");
        if ($pos == true) {
            list($modelo, $noParte) = explode(" / ", $parametros['noParte']);
            $obj->setNoParte($noParte);
            $obj->setModeloComp($modelo);
        }
    }

    if (isset($parametros['almacen'])) {
        $obj->setIdAlmacen($parametros['almacen']);
        if ($obj->getTipoAlmacenById()) {//obtener tipo de almacen seleccionado
            $tipoAlmacen = $obj->getTipoAlmacen();
        }
        if ($tipoAlmacen == "0") {
            if ($parametros['minima'] == "") {
                echo "Error: La cantidad mínima es obligatoria";
                return;
            } else if ($parametros['maxima'] == "") {
                echo "Error: La cantidad máxima es obligatoria";
                return;
            } else {
                $obj->setMinimo($parametros['minima']);
                $obj->setMaximo($parametros['maxima']);
                $pos = strpos($parametros['noParte'], " / ");
                if ($pos == true) {
                    if ($obj->newRegistro()) {
                        $movientoAlmacen->setNoParteComponente($noParte);
                        $movientoAlmacen->setIdAlmacenNuevo($parametros['almacen']);
                        $movientoAlmacen->setIdAlmacenAnterior($parametros['almacen']);
                        $movientoAlmacen->setUsuarioCreacion($_SESSION['user']);
                        $movientoAlmacen->setUsuarioModificacion($_SESSION['user']);
                        $movientoAlmacen->setPantalla("Entrada al almacén");
                        $movientoAlmacen->setEntradaSalida(0);
                        $movientoAlmacen->setComentario("Entrada al almacén");
                        $movientoAlmacen->setCantidadMovimiento($parametros['cantidad']);
                        if ($movientoAlmacen->newRegistroMovimientoAlmacen()) {
                            // echo "agregado";
                        } else {
                            // echo "no agregado";
                        }

                        echo "El componente <b>" . $obj->getModeloComp() . "</b> se registró correctamente";
                    } else {
                        echo "Error: El componente <b>" . $obj->getModeloComp() . "</b> ya se encuentra registrado";
                    }
                } else {
                    echo "Error: Seleccione un componente existente";
                }
            }
        } else {
            if ($parametros['minima'] == "") {
                $obj->setMinimo(0);
            } else {
                $obj->setMinimo($parametros['minima']);
            }

            if ($parametros['maxima'] == "") {
                $obj->setMaximo(0);
            } else {
                $obj->setMaximo($parametros['maxima']);
            }

            $pos = strpos($parametros['noParte'], " / ");
            if ($pos == true) {
                if ($obj->newRegistro()) {
                    $movientoAlmacen->setNoParteComponente($noParte);
                    $movientoAlmacen->setIdAlmacenNuevo($parametros['almacen']);
                    $movientoAlmacen->setIdAlmacenAnterior($parametros['almacen']);
                    $movientoAlmacen->setUsuarioCreacion($_SESSION['user']);
                    $movientoAlmacen->setUsuarioModificacion($_SESSION['user']);
                    $movientoAlmacen->setPantalla("Entrada al almacén");
                    $movientoAlmacen->setEntradaSalida(0);
                    $movientoAlmacen->setComentario("Entrada al almacén");
                    $movientoAlmacen->setCantidadMovimiento($parametros['cantidad']);
                    if ($movientoAlmacen->newRegistroMovimientoAlmacen()) {
                        //echo "agregado";
                    } else {
                        // echo "no agregado";
                    }
                    echo "El componente <b>" . $obj->getModeloComp() . "</b> se registró correctamente";
                } else {
                    echo "Error: El componente <b>" . $obj->getModeloComp() . "</b> ya se encuentra registrado";
                }
            } else {
                echo "Error: Seleccione un componente existente";
            }
//            echo "Normal";
        }
    } else {
        $obj->setNoParte($parametros['id']);
        $obj->setIdAlmacen($parametros['id2']);
        $obj->setModeloComp($parametros['modelo']);
        $comentario = $parametros['comentario'];
        /**/
        $cantidad = $parametros['cantidad'];
        $cantidadExist = $parametros['cantidadExis'];
        $apartadosExist = $parametros['apartadoExis'];
        $minimoExist = $parametros['minimoExis'];
        $maximoExis = $parametros['maximoExis'];
        $ubicacionExist = $parametros['ubicacionExis'];

        if ($parametros['tipoAlmacen'] == "0") {
            if ($parametros['minima'] == "") {
                echo "Error: La cantidad mínima es obligatoria";
            } else if ($parametros['maxima'] == "") {
                echo "Error: La cantidad máxima es obligatoria";
            } else {
                $obj->setMinimo($parametros['minima']);
                $obj->setMaximo($parametros['maxima']);
                if ($obj->editRegistro()) {
                    if ($minimoExist != $obj->getMinimo() || $maximoExis != $obj->getMaximo()) {
                        $cambios = new CambiosMiniAlmacen();
                        $cambios->setIdAlmacen($obj->getIdAlmacen());
                        $cambios->setNoParte($obj->getNoParte());
                        $cambios->setExistenciaAnterior($cantidadExist);
                        $cambios->setApartadoAnterior($apartadosExist);
                        $cambios->setMinimoAnterior($minimoExist);
                        $cambios->setMaximoAnterior($maximoExis);
                        $cambios->setUbicacionAnterior($ubicacionExist);
                        $cambios->setExistenciaNuevo($obj->getExistencia());
                        $cambios->setApartadoNuevo($obj->getApartados());
                        $cambios->setMinimoNuevo($obj->getMinimo());
                        $cambios->setMaximoNuevo($obj->getMaximo());
                        $cambios->setUbicacionNuevo($obj->getUbicacion());
                        $cambios->setComentario($comentario);
                        $cambios->setUsuarioCreacion($obj->getUsuarioCreacion());
                        $cambios->setUsuarioUltimaModificacion($obj->getUsuarioModificacion());
                        $cambios->setPantalla($obj->getPantalla());
                        if (!$cambios->newRegistro()) {
                            echo "<br/>Error: no se pudo guardar la información de cambios de mínimos y máximos<br/>";
                        }
                    }
                    $movientoAlmacen->setNoParteComponente($parametros['id']);
                    $movientoAlmacen->setIdAlmacenNuevo($parametros['id2']);
                    $movientoAlmacen->setIdAlmacenAnterior($parametros['id2']);
                    $movientoAlmacen->setUsuarioCreacion($_SESSION['user']);
                    $movientoAlmacen->setUsuarioModificacion($_SESSION['user']);
                    if ($cantidadExist > $cantidad) {//agergar salida del almacen
                        $movientoAlmacen->setPantalla("Salida del almacén");
                        $movientoAlmacen->setEntradaSalida(1);
                        $movientoAlmacen->setComentario($comentario);
                        $movientoAlmacen->setCantidadMovimiento((int) $cantidadExist - (int) $cantidad);
                        if ($movientoAlmacen->newRegistroMovimientoAlmacen()) {
                            //   echo "agregado";
                        } else {
                            // echo "no agregado";
                        }
                    } else if ($cantidadExist < $cantidad) {//agregar el entrada del almacen
                        $movientoAlmacen->setPantalla("Entrada al almacén");
                        $movientoAlmacen->setEntradaSalida(0);
                        $movientoAlmacen->setComentario($comentario);
                        $movientoAlmacen->setCantidadMovimiento((int) $cantidad - (int) $cantidadExist);
                        if ($movientoAlmacen->newRegistroMovimientoAlmacen()) {
                            // echo "agregado";
                        } else {
                            //echo "no agregado";
                        }
                    }
                    echo "El componente <b>" . $obj->getModeloComp() . "</b> se modificó correctamente";
                } else {
                    echo "Error: El componente <b>" . $obj->getModeloComp() . "</b> ya se encuentra registrado";
                }
            }
        } else {
            if ($parametros['minima'] == "") {
                $obj->setMinimo(0);
            } else {
                $obj->setMinimo($parametros['minima']);
            }

            if ($parametros['maxima'] == "") {
                $obj->setMaximo(0);
            } else {
                $obj->setMaximo($parametros['maxima']);
            }

            if ($obj->editRegistro()) {
                if ($minimoExist != $obj->getMinimo() || $maximoExis != $obj->getMaximo()) {
                    $cambios = new CambiosMiniAlmacen();
                    $cambios->setIdAlmacen($obj->getIdAlmacen());
                    $cambios->setNoParte($obj->getNoParte());
                    $cambios->setExistenciaAnterior($cantidadExist);
                    $cambios->setApartadoAnterior($apartadosExist);
                    $cambios->setMinimoAnterior($minimoExist);
                    $cambios->setMaximoAnterior($maximoExis);
                    $cambios->setUbicacionAnterior($ubicacionExist);
                    $cambios->setExistenciaNuevo($obj->getExistencia());
                    $cambios->setApartadoNuevo($obj->getApartados());
                    $cambios->setMinimoNuevo($obj->getMinimo());
                    $cambios->setMaximoNuevo($obj->getMaximo());
                    $cambios->setUbicacionNuevo($obj->getUbicacion());
                    $cambios->setComentario($comentario);
                    $cambios->setUsuarioCreacion($obj->getUsuarioCreacion());
                    $cambios->setUsuarioUltimaModificacion($obj->getUsuarioModificacion());
                    $cambios->setPantalla($obj->getPantalla());
                    if (!$cambios->newRegistro()) {
                        echo "<br/>Error: no se pudo guardar la información de cambios de mínimos y máximos<br/>";
                    }
                }
                $movientoAlmacen->setNoParteComponente($parametros['id']);
                $movientoAlmacen->setIdAlmacenNuevo($parametros['id2']);
                $movientoAlmacen->setIdAlmacenAnterior($parametros['id2']);
                $movientoAlmacen->setUsuarioCreacion($_SESSION['user']);
                $movientoAlmacen->setUsuarioModificacion($_SESSION['user']);
                if ($cantidadExist > $cantidad) {//agergar salida del almacen
                    $movientoAlmacen->setPantalla("Salida del almacén");
                    $movientoAlmacen->setEntradaSalida(1);
                    $movientoAlmacen->setComentario($comentario);
                    $movientoAlmacen->setCantidadMovimiento((int) $cantidadExist - (int) $cantidad);
                    if ($movientoAlmacen->newRegistroMovimientoAlmacen()) {
                        //echo "agregado";
                    } else {
                        //echo "no agregado";
                    }
                } else if ($cantidadExist < $cantidad) {//agregar el entrada del almacen
                    $movientoAlmacen->setPantalla("Entrada al almacén");
                    $movientoAlmacen->setEntradaSalida(0);
                    $movientoAlmacen->setComentario($comentario);
                    $movientoAlmacen->setCantidadMovimiento((int) $cantidad - (int) $cantidadExist);
                    if ($movientoAlmacen->newRegistroMovimientoAlmacen()) {
                        // echo "agregado";
                    } else {
                        // echo "no agregado";
                    }
                }
                echo "El componente <b>" . $obj->getModeloComp() . "</b> se modificó correctamente";
            } else {
                echo "Error: El componente <b>" . $obj->getModeloComp() . "</b> ya se encuentra registrado";
            }
        }
    }
}
?>