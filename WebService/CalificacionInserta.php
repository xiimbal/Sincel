<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/lib/PHPImagen.lib.php");


function insertaCalificacion($titulo, $mensaje, $calificacion, $ClaveCliente, $IdSession, $encoded, $name) {
    $empresa = 3;
    $session = new Session();
    $session->setEmpresa($empresa);

    if (!is_numeric($calificacion) || $calificacion > 10 || $calificacion < 0) {
        return json_encode("La calificación tiene que ser un valor númerico entre 0 y 10");
    }

    $resultadoLoggin = (int) $session->logginWithSession($IdSession);

    if ($resultadoLoggin > 0) {
        $cliente = new Cliente();
        $usuario_obj = new Usuario();
        $cliente->setEmpresa($empresa);
        $usuario_obj->setEmpresa($empresa);
        $usuario_obj->getRegistroById($resultadoLoggin);

        $location = "";
        $location_final = "";
        if ($name != "" && $encoded != "") {
            $this_dir = dirname(__FILE__); // path to admin/
            $parent_dir = realpath($this_dir . '/..'); // admin's parent dir path can be represented by admin/..
            $location = $parent_dir . "/WebService/uploads/$name"; // Mention where to upload the file            
            $location_final = "WebService/uploads/$name";

            $contador = 1;
            while (file_exists($location)) {
                $name_aux = "($contador)" . $name;
                $location = $parent_dir . "/WebService/uploads/$name_aux"; // Mention where to upload the file                     
                $location_final = "WebService/uploads/$name_aux";
                $contador++;
            }
            $fp = fopen($location, "x");
            fclose($fp);
            //$file_get = file_get_contents($location);
            $current = base64_decode($encoded); // Now decode the content which was sent by the client         
            if (file_put_contents($location, $current) == FALSE) {// Write the decoded content in the file mentioned at particular location      
                $location = "";
                $location_final = "";
            } else {//En caso que se haya guardado la imagen correctamente
                // The file
                $filename = "../" .$location_final;                                

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
                $index_last_dot = strrpos($location, ".");
                $location_aux = substr($location, 0, $index_last_dot);
                $location_aux .= "resized_300_techra";
                $location_aux .= substr($location, $index_last_dot);
                imagejpeg($image_p, $location_aux, 75);
            }
        }

        if (!$cliente->getRegistroById($ClaveCliente)) {
            return json_encode("La clave de cliente $ClaveCliente no existe");
        }

        $cliente->setCalificacion($calificacion);
        $cliente->setMensaje($mensaje);
        $cliente->setTitulo($titulo);
        $cliente->setFoto($location_final);
        $cliente->setUsuarioCreacion($usuario_obj->getUsuario());
        $cliente->setUsuarioUltimaModificacion($usuario_obj->getUsuario());
        $cliente->setPantalla("CalificacionInserta WS");

        if ($cliente->agregarCalificacion($resultadoLoggin)) {
            return json_encode(1);
        } else {
            return json_encode(0);
        }
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("calificacioninserta", "urn:calificacioninserta");
$server->register("insertaCalificacion", array("titulo" => "xsd:string", "mensaje" => "xsd:string", "calificacion" => "xsd:int",
    "ClaveCliente" => "xsd:string", "IdSession" => "xsd:string", "file" => "xsd:string", "location" => "xsd:string"), array("return" => "xsd:string"), "urn:calificacioninserta", "urn:calificacioninserta#insertaCalificacion", "rpc", "encoded", "Inserta titulo, mensaje, calificacion y foto");

$server->service($HTTP_RAW_POST_DATA);
/* $server->register("getProd");
  $server->service($HTTP_RAW_POST_DATA); */
?>