<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/NotaTicket.class.php");
include_once("../WEB-INF/Classes/Almacen.class.php");
include_once("../WEB-INF/Classes/AlmacenConmponente.class.php");
include_once("../WEB-INF/Classes/UbicacionUsuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Ticket.class.php");
include_once("../WEB-INF/Classes/Mail.class.php");
include_once("../WEB-INF/Classes/ParametroGlobal.class.php");
include_once("../WEB-INF/Classes/lib/PHPImagen.lib.php");

function insertaNota($Titulo, $Mensaje, $FotoCodificada, $NombreFoto, $Fecha, $MinutoDesfase, $IdEstatus, $IdTicket, $Latitud, $Longitud, $IdSession) {                
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);
    
    $mail = new Mail();
    $parametroGlobal = new ParametroGlobal();
    $catalogo = new Catalogo();
    
    $parametroGlobal->setEmpresa($empresa);
    $catalogo->setEmpresa($empresa);
    
    /* Obtenemos los correos a quien mandaremos el mail */
    $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 21;");
    if(mysql_num_rows($query4) > 0){    
        if($parametroGlobal->getRegistroById("8")){
            $mail->setFrom($parametroGlobal->getValor());
        }else{
            $mail->setFrom("scg-salida@scgenesis.mx");
        }
        $mail->setSubject("Uso de WS Nueva nota empresa $empresa");
        $cadena_correo = "Los parámetro recibidos en el WS NuevoTicket.php usado por el usuario con id $resultadoLoggin son: Titulo: $Titulo, Mensaje: $Mensaje, NombreFoto: $NombreFoto, Fecha: $Fecha, MinutoDesfase: $MinutoDesfase, IdEstatus: $IdEstatus, IdTicket: $IdTicket, Latitud: $Latitud, Longitud: $Longitud, IdSesion: $IdSession";
        $mail->setBody($cadena_correo);


        $correos = array();    
        while ($rs = mysql_fetch_array($query4)) {
            $value = $rs['correo'];
            if (isset($value) && $value != "" && $value != NULL && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                array_push($correos, $value);
            }
        }
        $mail->setTo($correos);
        $mail->enviarMail();
    }

    if ($resultadoLoggin > 0) { //Id del usuario
        $user_obj = new Usuario();
        $user_obj->setEmpresa($empresa);
        
        
        $usuario = "NuevaNota WS";
        $pantalla = "NuevaNota WS";
        
        if ($user_obj->getRegistroById($resultadoLoggin)) {
            $usuario = $user_obj->getUsuario();
        }
                
        $descripion_extra = "";                    
        if(isset($Latitud) && isset($Longitud) && $Latitud != 0 && $Longitud != 0 && $IdEstatus == 51){
            //$address = urlencode("$Latitud,$Longitud");
            $url = "http://maps.google.com/maps/api/geocode/json?address=".$Latitud.",".$Longitud."&sensor=false";
            //$geocode=file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            $data = curl_exec($ch);
            curl_close($ch);
            $output = json_decode($data);
            $CP = $output->results[0]->address_components[7]->long_name;
            // Get lat and long by address         
            $consulta = "UPDATE c_domicilio d SET d.Latitud = $Latitud, d.Longitud = $Longitud
                WHERE (ISNULL(d.Latitud) OR ISNULL(d.Longitud)) AND CodigoPostal = '$CP' AND 
                d.ClaveEspecialDomicilio = (SELECT ClaveCentroCosto from c_ticket WHERE IdTicket = $IdTicket)";
            //return $consulta;
            $catalogo->insertarRegistro($consulta);
            
        }
        
        if($Latitud == "0" && $Longitud == "0"){                     
            $descripion_extra = "se recibió una coordenada 0,0";
        }
        
        $consulta = "SELECT t.IdTicket, 
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.Latitud ELSE d.Latitud END) AS Latitud,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.Longitud ELSE d.Longitud END) AS Longitud,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) 
            THEN 
            111.1111 *
            DEGREES(ACOS(COS(RADIANS(dt.Latitud))
                            * COS(RADIANS($Latitud))
                            * COS(RADIANS(dt.Longitud - $Longitud))
                            + SIN(RADIANS(dt.Latitud))
                            * SIN(RADIANS($Latitud))))
            ELSE 
            111.1111 *
            DEGREES(ACOS(COS(RADIANS(d.Latitud))
                            * COS(RADIANS($Latitud))
                            * COS(RADIANS(d.Longitud - $Longitud))
                            + SIN(RADIANS(d.Latitud))
                            * SIN(RADIANS($Latitud))))
            END) AS Distancia
            FROM c_ticket AS t
            LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket 
            LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = t.ClaveCentroCosto
            LEFT JOIN c_domicilio AS d ON d.IdDomicilio = (SELECT MIN(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = cc.ClaveCentroCosto)
            WHERE t.IdTicket = $IdTicket;";
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            if($Latitud == "0" && $Longitud == "0"){//Si se reciben coordenadas 0,0 se ponen las coordenadas del ticket                
                $Latitud = $rs['Latitud'];
                $Longitud = $rs['Longitud'];
            }else if( ($IdEstatus == "51" || $IdEstatus == "16" || $IdEstatus == "14") && (float)$rs['Distancia'] > 0.5 ){
                $descripion_extra = "Se recibieron coordenadas a ".number_format($rs['Distancia'],3)." km";
            }
        }
        
        $validarUsuarioTicket = "SELECT IdTicket,IdUsuario FROM k_tecnicoticket WHERE IdTicket = ".$IdTicket;        
        $result = $catalogo->obtenerLista($validarUsuarioTicket);
        
        if(mysql_num_rows($result) == 0){//No está asignado a nadie
            $ticket = new Ticket();
            $ticket->setEmpresa($empresa);
            $ticket->setIdTicket($IdTicket);
            $ticket->setUsuarioCreacion($usuario);
            $ticket->setUsuarioUltimaModificacion($usuario);
            $ticket->setPantalla($pantalla);
            if(!$ticket->asociarTicketTecnicoGeneral($resultadoLoggin, "", "", "", "")){
                return -7;
            }
        }else{
            while($rs = mysql_fetch_array($result)){
                if($rs['IdUsuario'] != $resultadoLoggin){
                    return -5;//El usuario de la sesion es diferente al que está asignado al ticket
                }
            }
        }
        
        $ubicacion = new UbicacionUsuario();
        $ubicacion->setEmpresa($empresa);
        
        $nota = new NotaTicket();
        $nota->setEmpresa($empresa);
        
        $nota->setIdTicket($IdTicket);
        $nota->setDiagnostico("$Mensaje $descripion_extra");
        $nota->setIdEstatus($IdEstatus);
        $nota->setMostrarCliente(1);
        $nota->setActivo(1);
        $nota->setTitulo($Titulo);
        $nota->setNombreImagen($NombreFoto);
        $nota->setFechaHora($Fecha);
        $nota->setLatitud($Latitud);
        $nota->setLongitud($Longitud);
        $nota->setMinutosDefase($MinutoDesfase);
        
        $location = "";
        $location_final = "";
        $name = $NombreFoto;
        $encoded = $FotoCodificada;
        
        if ($NombreFoto != "" && $FotoCodificada != "") {            
            $name = "Nota_$name";
            $this_dir = dirname(__FILE__); // path to admin/
            $parent_dir = realpath($this_dir . '/..'); // admin's parent dir path can be represented by admin/..
            $location = $parent_dir . "/WebService/uploads/notas/$name"; // Mention where to upload the file            
            $location_final = "WebService/uploads/notas/$name";

            $contador = 1;
            while (file_exists($location)) {
                $name_aux = "($contador)" . $name;
                $location = $parent_dir . "/WebService/uploads/notas/$name_aux"; // Mention where to upload the file                     
                $location_final = "WebService/uploads/notas/$name_aux";
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
        
        $nota->setPathImagen($location_final);               
        $ubicacion->setIdUsuario($resultadoLoggin);
        $ubicacion->setLatitud($Latitud);
        $ubicacion->setLongitud($Longitud);
        $ubicacion->setPorcentajeBateria("NULL");
        
        $ubicacion->setUsuarioCreacion($usuario);
        $ubicacion->setUsuarioUltimaModificacion($usuario);
        $nota->setUsuarioSolicitud($usuario);
        $nota->setUsuarioCreacion($usuario);
        $nota->setUsuarioModificacion($usuario);        
        $ubicacion->setPantalla($pantalla);
        $nota->setPantalla($pantalla);
        
        if($nota->newRegistro()){            
            $ubicacion->setIdNotaTicket($nota->getIdNota());
            if($IdEstatus == 14 || $IdEstatus == 9){
                $ticket = new Ticket();
                $ticket->setEmpresa($empresa);
                $ticket->setIdTicket($IdTicket);
                if(!$ticket->eliminarAsignaciones()){
                    return -8;//no se puede desaosociar el 
                }
            }
            
            
            if ($ubicacion->newRegistro()) {
                if((int)$IdEstatus != 17){
                    return 1;
                }
            } else {
                return -4;
            }            
        }else{
            return 0;
        }

        //Solo si es nota con estatus 17 Tipo abrir camión se ejecutará esta parte del código 
        $ClaveCliente = "";
        $consultaPakal = "SELECT ClaveCliente FROM c_cliente c WHERE 'PAKAL' = UPPER(c.NombreRazonSocial)";
        $resultPakal = $catalogo->obtenerLista($consultaPakal);
        if(mysql_num_rows($resultPakal) < 1){
            return -9;
        }else{
            if($rs = mysql_fetch_array($resultPakal)){
                $ClaveCliente = $rs['ClaveCliente'];
            }
        }
        
        //Primero buscaremos el Id del almacén que tenga de cliente "pakal"
        $consultaAlmacen = "SELECT a.id_almacen, (CASE WHEN ISNULL(SUM(ac.cantidad_existencia)) THEN 0 ELSE SUM(ac.cantidad_existencia) END)  AS Existencia1,
            (SELECT (CASE WHEN ISNULL(SUM(act.cantidad_existente)) THEN 0 ELSE SUM(act.cantidad_existente) END) FROM k_almacencomponenteticket act WHERE act.id_almacen = a.id_almacen) AS Existencia2
            FROM c_almacen a 
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = a.Cliente
            LEFT JOIN k_almacencomponente AS ac ON ac.id_almacen = a.id_almacen
            WHERE c.ClaveCliente = '$ClaveCliente'
            GROUP BY ac.id_almacen
            HAVING Existencia1 = 0 AND Existencia2 = 0;";
        
        $IdAlmacen = 0;
        $resultAlmacen = $catalogo->obtenerLista($consultaAlmacen);
        $almacen = new Almacen();
        $almacen->setEmpresa($empresa);
        $almacen->setUsuarioModificacion($usuario);
        $almacen->setUsuarioCreacion($usuario);
        $almacen->setPantalla("WS Nueva Nota");
        if(mysql_num_rows($resultAlmacen) < 1){ //Si no hay ningún almacen que cumpla con esto, vamos a crear uno nuevo.
            $almacen->setNombre($IdTicket);
            $almacen->setActivo(1);
            $almacen->setTipoAlmacen(1);
            $almacen->setPrioridad(1);
            $almacen->setSurtir(1);
            $almacen->setCliente($ClaveCliente);
            if(!$almacen->newRegistro()){
                return -10;
            }
        }else{
            if($rs = mysql_fetch_array($resultAlmacen)){
                $IdAlmacen = $rs['id_almacen'];
                //Cambiamos el nombre del almacén por el id de ticket
                $almacen->setNombre($IdTicket);
                $almacen->setIdAlmacen($IdAlmacen);
                if(!$almacen->editNombreAlmacen()){
                    return -11;
                }
            }
        }
        $consulta = "SELECT p.NombreComercial, co.Id_orden_compra, koc.NoParteComponente, koc.Cantidad
            FROM k_tickets_oc toc
            LEFT JOIN c_orden_compra AS co ON co.Id_orden_compra = toc.IdOrdenCompra
            LEFT JOIN c_proveedor AS p ON p.ClaveProveedor = co.FacturaEmisor
            LEFT JOIN k_orden_compra AS koc ON koc.IdOrdenCompra = co.Id_orden_compra
            WHERE toc.IdTicket = $IdTicket AND !ISNULL(koc.NoParteComponente)";
        $result = $catalogo->obtenerLista($consulta);
        $almacenComponente = new AlmacenComponente();
        $almacenComponente->setEmpresa($empresa);
        $almacenComponente->setUsuarioCreacion($usuario);
        $almacenComponente->setUsuarioModificacion($usuario);
        $almacenComponente->setPantalla("WS NuevaNota");
        while($rs = mysql_fetch_array($result)){
            $almacenComponente->setNoParte($rs['NoParteComponente']);
            $almacenComponente->setIdAlmacen($almacen->getIdAlmacen());
            $almacenComponente->setExistencia($rs['Cantidad']);
            $almacenComponente->setApartados("0");
            $almacenComponente->setMinimo("0");
            $almacenComponente->setMaximo("0");
            $almacenComponente->setUbicacion("");
            $almacenComponente->setIdOrdenCompra($rs['Id_orden_compra']);
            if($almacenComponente->getRegistroById($rs['NoParteComponente'], $almacen->getIdAlmacen())){
                if(!$almacenComponente->editarCantidadAlmacenReusrtir()){
                    return -12;
                }
            }else{
                if(!$almacenComponente->newRegistro()){
                    return -12;
                }
            }
        }
        return 1;
        
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("nuevaNota", "urn:nuevaNota");
$server->register("insertaNota", 
        array("Titulo" => "xsd:string", "Mensaje" => "xsd:string", "FotoCodificada" => "xsd:string", "NombreFoto" => "xsd:string", "Fecha" => "xsd:string", "MinutoDesfase" => "xsd:int", "IdEstatus" => "xsd:int", "IdTicket" => "xsd:int", "Latitud" => "xsd:float", "Longitud" => "xsd:float", "IdSession" => "xsd:string"), 
        array("return" => "xsd:string"), "urn:nuevaNota", "urn:nuevaNota#insertaNota", "rpc", "encoded", "Inserta una nota de un ticket");
$server->service($HTTP_RAW_POST_DATA);
?>