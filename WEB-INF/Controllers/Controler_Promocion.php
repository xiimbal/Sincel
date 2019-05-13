<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/Promocion.class.php");
$obj = new Promocion();

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdPromocion($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "La promoción se eliminó correctamente";
    } else {
        echo "La promoción no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }

    $obj->setTitulo($parametros['titulo']);
    $obj->setLocalidad($parametros['localidad']);
    $obj->setIdUsuario($parametros['contacto']);
    $obj->setClaveCliente($parametros['cliente']);
    $obj->setDescripcion($parametros['descripcion']);
    $obj->setVigencia($parametros['vigencia_inicio']);
    $obj->setVigencia_Fin($parametros['vigencia_fin']);
    $obj->setCodigoPromocion($parametros['codigo_promocion']);

    //$obj->setIdGiro($parametros['giro']);
    if (isset($parametros['cupon']) && $parametros['cupon'] == "on") {
        $obj->setManejaCupon(1);
        $obj->setNumeroCupones($parametros['numero_cupones']);
    } else {
        $obj->setManejaCupon(0);
        $obj->setNumeroCupones("NULL");
    }

    $obj->setCuponesUsados($parametros['numero_cupones_usados']);    
    $obj->setActivo(1);        
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setPantalla('Controler_Promocion');

    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "La promoción " . $obj->getTitulo() . " / " . $obj->getCodigoPromocion() . " se registró correctamente";
            foreach ($_FILES as $key) {
                $ruta = "../../resources/images/promociones/";
                if ($key['error'] == UPLOAD_ERR_OK) {//Verificamos si se subio correctamente
                    $nombre = $key['name']; //Obtenemos el nombre del archivo
                    $nomb = explode(".", $nombre);
                    if(!isset($nomb[1]) || ($nomb[1]!="jpg" && $nomb[1]!="png" && $nomb[1]!="jpeg")){
                        echo "<br/>Warning: no se pudo subir la imagen, necesita tener una extensión jpg, jpeg o png";
                        return;
                    }
                    $temporal = $key['tmp_name']; //Obtenemos el nombre del archivo temporal
                    $nombre_concatenado = "";
                    while (file_exists($ruta . $obj->getIdPromocion() . $nombre_concatenado . "_promocion_logo." . $nomb[1])) {
                        $nombre_concatenado .= "(1)";
                    }
                    if(!move_uploaded_file($temporal, $ruta . $obj->getIdPromocion() . $nombre_concatenado . "_promocion_logo." . $nomb[1])){ //Movemos el archivo temporal a la ruta especificada
                        echo "<br/>Warinig: la imagen no se pudo mover al directorio";
                    }
                    $obj->actualizarImagen($obj->getIdPromocion() . $nombre_concatenado . "_promocion_logo." . $nomb[1]);
                    //$obj->setArchivoLogo($parametros['id'] . $nombre_concatenado . "_empresa_logo." . $nomb[1]);
                    //$obj->actualizarLogo();
                } else {
                    echo $key['error']; //Si no se cargo mostramos el error
                }
            }
        } else {
            echo "Error: La promoción no se pudo registrar";
        }
    } else {/* Modificar */
        $obj->setIdPromocion($parametros['id']);
        if ($obj->editRegistro()) {
            foreach ($_FILES as $key) {
                $ruta = "../../resources/images/promociones/";
                if ($key['error'] == UPLOAD_ERR_OK) {//Verificamos si se subio correctamente
                    $nombre = $key['name']; //Obtenemos el nombre del archivo
                    $nomb = explode(".", $nombre);
                    if(!isset($nomb[1]) || ($nomb[1]!="jpg" && $nomb[1]!="png" && $nomb[1]!="jpeg")){
                        echo "<br/>Warning: no se pudo subir la imagen, necesita tener una extensión jpg, jpeg o png";
                        return;
                    }
                    $temporal = $key['tmp_name']; //Obtenemos el nombre del archivo temporal
                    $nombre_concatenado = "";
                    while (file_exists($ruta . $obj->getIdPromocion() . $nombre_concatenado . "_promocion_logo." . $nomb[1])) {
                        $nombre_concatenado .= "(1)";
                    }
                    if(!move_uploaded_file($temporal, $ruta . $obj->getIdPromocion() . $nombre_concatenado . "_promocion_logo." . $nomb[1])){ //Movemos el archivo temporal a la ruta especificada
                        echo "<br/>Warinig: la imagen no se pudo mover al directorio";
                    }
                    $obj->actualizarImagen($obj->getIdPromocion() . $nombre_concatenado . "_promocion_logo." . $nomb[1]);                    
                } else {
                    echo $key['error']; //Si no se cargo mostramos el error
                }
            }
            echo "La promoción " . $obj->getTitulo() . " / " . $obj->getCodigoPromocion() . " se modificó correctamente";
        } else {
            echo "Error: la promoción no se pudo editar";
        }
    }
}
?>