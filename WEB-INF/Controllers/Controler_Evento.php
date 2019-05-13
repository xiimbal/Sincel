<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/Evento.class.php");
include_once("../Classes/lib/PHPImagen.lib.php");
$obj = new Evento();

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdEvento($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El evento se eliminó correctamente";
    } else {
        echo "El evento no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }

    foreach ($_FILES as $key) {
        $ruta = "../../WebService/uploads/Evento_";
        $ruta_final = "WebService/uploads/Evento_";
        if ($key['error'] == UPLOAD_ERR_OK) {//Verificamos si se subio correctamente
            $nombre = $key['name']; //Obtenemos el nombre del archivo
            $nomb = explode(".", $nombre);
            if (!isset($nomb[1]) || ($nomb[1] != "jpg" && $nomb[1] != "png" && $nomb[1] != "jpeg")) {
                echo "<br/>Warning: no se pudo subir la imagen, necesita tener una extensión jpg, jpeg o png";
                return;
            }
            $temporal = $key['tmp_name']; //Obtenemos el nombre del archivo temporal
            $nombre_concatenado = "";
            while (file_exists($ruta . $nomb[0] . $nombre_concatenado . "." . $nomb[1])) {
                $nombre_concatenado .= "(1)";
            }
            
            // The file
            $filename = $ruta . $nomb[0] . $nombre_concatenado . "." . $nomb[1];
            $location_final = $ruta_final . $nomb[0] . $nombre_concatenado . "." . $nomb[1];
            
            if (!move_uploaded_file($temporal, $ruta . $nomb[0] . $nombre_concatenado . "." . $nomb[1])) { //Movemos el archivo temporal a la ruta especificada
                echo "<br/>Warinig: la imagen no se pudo mover al directorio";
            }
           
            // Get new dimensions
            //Se trata de ajustar la imagen para que no se distorsione
            list($width, $height) = getimagesize($filename);
            $imagen = new Imagen($filename);
            $imagen->resize(300, 300);
            $new_width = $imagen->getRw();
            $new_height = $imagen->getRh();

            // Resample
            $image_p = imagecreatetruecolor($new_width, $new_height);
            $image = imagecreatefromjpeg($filename);
            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

            // Output
            $index_last_dot = strrpos($filename, ".");
            $location_aux = substr($filename, 0, $index_last_dot);
            $location_aux .= "resized_300_techra";
            $location_aux .= substr($filename, $index_last_dot);
            imagejpeg($image_p, $location_aux, 75);
            //$obj->actualizarImagen($obj->getIdPromocion() . $nombre_concatenado . "_promocion_logo." . $nomb[1]);                    
        } else {
            echo $key['error']; //Si no se cargo mostramos el error
        }
    }    

    $obj->setClaveCliente($parametros['cliente']);
    $obj->setNombre($parametros['nombre']);
    $obj->setDescripcion($parametros['descripcion']);
    $obj->setFechaInicio($parametros['fecha_inicio']);
    $obj->setFechaFin($parametros['fecha_fin']);
    $obj->setImagen($location_final);
    $obj->setCalle($parametros['calle']);
    $obj->setNoExterior($parametros['no_exterior']);
    $obj->setNoInterior($parametros['no_interior']);
    $obj->setColonia($parametros['colonia']);
    $obj->setCiudad($parametros['ciudad']);
    $obj->setEstado($parametros['estado']);
    $obj->setDelegacion($parametros['delegacion']);
    $obj->setPais('México');
    $obj->setCodigoPostal($parametros['codigo_postal']);
    $obj->setLatitud($parametros['latitud']);
    $obj->setLongitud($parametros['longitud']);
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    }else{
        $obj->setActivo(0);
    }    
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);        
    $obj->setPantalla("Controler_Evento PHP");
    
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "El evento <b>".$obj->getNombre()."</b> se registró correctamente";
        } else {
            echo "Error: El evento <b>".$obj->getNombre()."</b> no se registró";
        }
    }else{
        $obj->setIdEvento($parametros['id']);
        if($obj->updateRegistro()){
            echo "El evento <b>".$obj->getNombre()."</b> se editó correctamente";
        }else{
            echo "Error: El evento <b>".$obj->getNombre()."</b> no se editó";
        }
    }
}
?>