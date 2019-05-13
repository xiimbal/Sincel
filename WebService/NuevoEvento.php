<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Evento.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/lib/PHPImagen.lib.php");

function insertaEvento($ClaveCliente, $Nombre, $Descripcion, $FechaInicio, $FechaFin, $Calle, $NoExterior, $NoInterior, $Colonia, $Ciudad, 
        $Estado, $Delegacion, $Pais, $CodigoPostal, $Latitud, $Longitud, $encoded, $name, $IdSession) {    
    $empresa = 3;
    $session = new Session();
    $session->setEmpresa($empresa);    
    
    $resultadoLoggin = (int)$session->logginWithSession($IdSession);
    
    if ($resultadoLoggin > 0) {
        $cliente = new Cliente();        
        $cliente->setEmpresa($empresa);
        $evento = new Evento();
        $evento->setEmpresa($empresa);
        $user_obj = new Usuario();
        $user_obj->setEmpresa($empresa);
        $usuario = "NuevoEvento WS";
        $pantalla = "Aplicación GUAU";

        $location = "";
        $location_final = "";
        if ($name != "" && $encoded != "") {
            $name = "Evento_$name";
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
            } else {
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
            return json_encode(-4);//Clave de cliente no existe
        }

        $evento->setClaveCliente($ClaveCliente);
        $evento->setNombre($Nombre);
        $evento->setDescripcion($Descripcion);
        $evento->setFechaInicio($FechaInicio);
        $evento->setFechaFin($FechaFin);
        $evento->setImagen($location_final);
        $evento->setCalle($Calle);
        $evento->setNoExterior($NoExterior); $evento->setNoInterior($NoInterior);
        $evento->setColonia($Colonia); $evento->setCiudad($Ciudad);
        $evento->setEstado($Estado);
        $evento->setDelegacion($Delegacion); $evento->setPais($Pais);
        $evento->setCodigoPostal($CodigoPostal);
        $evento->setLatitud($Latitud); $evento->setLongitud($Longitud);
        $evento->setActivo(1);
                
        if($user_obj->getRegistroById($resultadoLoggin)){                         
            $evento->setUsuarioCreacion($user_obj->getUsuario()); 
            $evento->setUsuarioUltimaModificacion($user_obj->getUsuario()); 
        }else{                        
            $evento->setUsuarioCreacion($usuario); 
            $evento->setUsuarioUltimaModificacion($usuario); 
        }
        
        $evento->setPantalla($pantalla);        
        if($evento->newRegistro()){
            return 1;
        }else{
            return -5;
        }
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("nuevoevento", "urn:nuevoevento");
$server->register("insertaEvento", 
        array(
            "ClaveCliente" => "xsd:string", "Nombre" => "xsd:string", "Descripcion" => "xsd:string", "FechaInicio" => "xsd:string", "FechaFin" => "xsd:string",
            "Calle" => "xsd:string", "NoExterior" => "xsd:string", "NoInterior" => "xsd:string", "Colonia" => "xsd:string", "Ciudad" => "xsd:string", 
            "Estado" => "xsd:string", "Delegacion" => "xsd:string", "Pais" => "xsd:string", "CodigoPostal" => "xsd:string", "Latitud" => "xsd:float", 
            "Longitud" => "xsd:float", "file" => "xsd:string", "location" => "xsd:string", "IdSession" => "xsd:string"), 
    array("return" => "xsd:string"), 
    "urn:nuevoevento", "urn:nuevoevento#insertaEvento", 
    "rpc", 
    "encoded", 
    "Inserta un nuevo evento");

$server->service($HTTP_RAW_POST_DATA);

?>