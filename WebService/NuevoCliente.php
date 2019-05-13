<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Ticket.class.php");
include_once("../WEB-INF/Classes/Configuracion.class.php");
include_once("../WEB-INF/Classes/Equipo.class.php");
include_once("../WEB-INF/Classes/DomicilioTicket.class.php");
include_once("../WEB-INF/Classes/ccliente.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/ParametroGlobal.class.php");
include_once("../WEB-INF/Classes/CentroCosto.class.php");
include_once("../WEB-INF/Classes/lib/PHPImagen.lib.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Incidencia.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");

function insertarNuevoRegistro($nombreRazonSocial, $idGiro, $latitud, $longitud, 
        $telefono, $email, $IdSession, $encoded, $name, $web, $rfc_cliente, 
        $facebook, $twitter, $horario, $calle1, $noExterior1, $noInterior1, 
        $colonia1, $ciudad1, $estado1, $delegacion1, $cp1, $idFacturacionEmpresa, 
        $ClaveCliente) {

    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);
    $psm = new PermisosSubMenu();
    $psm->setEmpresa($empresa);

    $session->setEmpresa($empresa);
    $user_obj = new Usuario();
    $user_obj->setEmpresa($empresa);
    
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);
    
    $incidencia = new Incidencia();
    $incidencia->setEmpresa($empresa);
    $incidencia->setFecha("2015-11-18");
    $incidencia->setFechaFin("2015-11-18");
    $incidencia->setDescripcion("$nombreRazonSocial, $idGiro, $latitud, $longitud, $telefono, $email, $IdSession, $web, $rfc_cliente, $facebook, $twitter, $horario, $calle1, $noExterior1, $noInterior1, $colonia1, $ciudad1, $estado1, $delegacion1, $cp1, $idFacturacionEmpresa, $ClaveCliente");
    $incidencia->setStatus(1); $incidencia->setId_Ticket("NULL");$incidencia->setActivo(1);$incidencia->setIdTipoIncidencia(1);
    $incidencia->newRegistro();
        
    if($psm->tienePermisoEspecial($resultadoLoggin, 30) && $resultadoLoggin > 0){   //Se hace la inserción directa del cliente
        
        $idTipoCliente = 1;
        $idModalidad = 3;
        if (isset($idFacturacionEmpresa) && !empty($idFacturacionEmpresa)) {
            $id_facturacion_empresa = $idFacturacionEmpresa;
        } else {
            $id_facturacion_empresa = 1000;
        }

        if (isset($rfc_cliente) && !empty($rfc_cliente)) {
            $rfc = $rfc_cliente;
        } else {
            
            $cliente_aux = new Cliente();
            $cliente_aux->setEmpresa($empresa);
            $rfc_aux = $session->generarClavealeatoria(12);
            
            while($cliente_aux->getRegistroByRFC($rfc_aux)){
                $rfc_aux = $session->generarClavealeatoria(12);
            }
            $rfc = $rfc_aux;
        }
        
        if (isset($calle1) && !empty($calle1)) {
            $calle = $calle1;
        } else {
            $calle = "S/I";
        }

        if (isset($noExterior1) && !empty($noExterior1)) {
            $noExterior = $noExterior1;
        } else {
            $noExterior = "0";
        }
        if (isset($noInterior1) && !empty($noInterior1)) {
            $noInterior = $noInterior1;
        } else {
            $noInterior = "0";
        }
        if (isset($colonia1) && !empty($colonia1)) {
            $colonia = $colonia1;
        } else {
            $colonia = "S/I";
        }
        if (isset($ciudad1) && !empty($ciudad1)) {
            $ciudad = $ciudad1;
        } else {
            $ciudad = "S/I";
        }
        if (isset($estado1) && !empty($estado1)) {
            $estado = $estado1;
        } else {
            $estado = "Ciudad de México";
        }
        if (isset($delegacion1) && !empty($delegacion1)) {
            $delegacion = $delegacion1;
        } else {
            $delegacion = "S/I";
        }

        $pais = "S/I";

        if (isset($cp1) && !empty($cp1)) {
            $codigo_postal = $cp1;
        } else {
            $codigo_postal = "S/I";
        }

        $tamanoperro = 1;
        $sitioweb = $web;
        $comentario = "";
        $calificacion = "null";
        $resultadoLoggin = (int) $session->logginWithSession($IdSession);
        
        if ($resultadoLoggin > 0) {
            $location = "";
            $location_final = "";
            if (empty($ClaveCliente)) {//Si hay que insertar cliente
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
                    } else {
                        // The file
                        $filename = "../" . $location_final;

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
                $usuario = "NuevoCliente WS";
                $pantalla = "Aplicación GUAU";
                $cliente = new ccliente();
                $cliente->setEmpresa($empresa);
                $cliente->setFacebook($facebook);
                $cliente->setTwitter($twitter);
                $cliente->setHorario($horario);
                $cliente->setTipoFacturacion(1);
                $cliente->setIdDatosFacturacionEmpresa($id_facturacion_empresa);
                $cliente->setRazonSocial($nombreRazonSocial);
                $cliente->setRFCD($rfc);
                $cliente->setEstatusCobranza(1);
                $cliente->setTipoCliente($idTipoCliente);
                $cliente->setGiro($idGiro);
                $cliente->setModalidad($idModalidad);
                $cliente->setTelefono($telefono);
                
                if (!is_numeric($tamanoperro)) {
                    return json_encode("Error: El tamaño del perro tiene que ser númerico");
                }
                $cliente->setTamanoPerro($tamanoperro);
                if ((isset($email) && $email != "") && !filter_var($email, FILTER_VALIDATE_EMAIL)) {// Si el correo es valido
                    return json_encode("Error: El email $email no tiene un formato válido.");
                }
                $cliente->setEmail($email);
                $cliente->setSitioweb($sitioweb);
                $cliente->setNivelCliente(1);
                $cliente->setIdTipoMorosidad(1);
                $cliente->setActivo(1);
                if ($user_obj->getRegistroById($resultadoLoggin)) {
                    if ($user_obj->getPuesto() == "11") {
                        $cliente->setEjecutivoCuenta($user_obj->getId());
                        $cliente->setEjecutivoAtencionCliente($user_obj->getId());
                    }
                    $cliente->setUsuarioCreacion($user_obj->getUsuario());
                    $cliente->setUsuarioModificacion($user_obj->getUsuario());
                } else {
                    $cliente->setUsuarioCreacion($usuario);
                    $cliente->setUsuarioModificacion($usuario);
                }
                $cliente->setPantalla($pantalla);
                $cliente->setComentario($comentario);
                $cliente->setImagen($location_final);
                $cliente->setCalificacion($calificacion);
                $cliente->setTipoDomicilio(3);
                $cliente->setCalleF($calle);
                $cliente->setNoExtF($noExterior);
                $cliente->setNoIntF($noInterior);
                $cliente->setColoniaF($colonia);
                $cliente->setCiudadF($ciudad);
                $cliente->setEstadoF($estado);
                $cliente->setPais($pais);
                $cliente->setDelegacionF($delegacion);
                $cliente->setCPF($codigo_postal);
                $cliente->setLatitud($latitud);
                $cliente->setLongitud($longitud);
                $cliente->setLocalidad("");
                $cliente->setRegresar_boleano(true);
                
                if ($cliente->nuevoRegistro()) {
                    return json_encode(1);
                }
                return json_encode(0);
            } else {
                $obj = new CentroCosto();
                $obj->setEmpresa($empresa);
                $obj->setClaveCliente($ClaveCliente);
                $obj->setNombre($nombreRazonSocial);
                $obj->setMoroso("0"); //Por default es 0            
                $obj->setTipoDomicilioFiscal(0);
                $obj->setActivo(1);

                $localidad = new Localidad();
                $localidad->setEmpresa($empresa);
                $localidad->setCalle($calle);
                $localidad->setNoExterior($noExterior);
                $localidad->setNoInterior($noInterior);
                $localidad->setEstado($estado);
                $localidad->setColonia($colonia);
                $localidad->setCiudad($ciudad);
                $localidad->setDelegacion($delegacion);
                $localidad->setPais("México");
                $localidad->setCodigoPostal($codigo_postal);
                $localidad->setLocalidad("");
                $localidad->setIdTipoDomicilio(5);
                $localidad->setActivo(1);
                $localidad->setLatitud($latitud);
                $localidad->setLongitud($longitud);

                if ($user_obj->getRegistroById($resultadoLoggin)) {
                    $obj->setUsuarioCreacion($user_obj->getUsuario());
                    $obj->setUsuarioUltimaModificacion($user_obj->getUsuario());
                    $localidad->setUsuarioCreacion($user_obj->getUsuario());
                    $localidad->setUsuarioUltimaModificacion($user_obj->getUsuario());
                } else {
                    $obj->setUsuarioCreacion($usuario);
                    $obj->setUsuarioUltimaModificacion($usuario);
                    $localidad->setUsuarioCreacion($usuario);
                    $localidad->setUsuarioUltimaModificacion($usuario);
                }
                $obj->setPantalla($pantalla);
                $localidad->setPantalla($pantalla);

                $result = $obj->getCentroCostoByClienteYNombre($ClaveCliente, $nombreRazonSocial);
                if (mysql_num_rows($result) > 0) {//Si ya existe una localidad con este nombre                
                    return -6;
                } else {
                    if ($obj->newRegistro()) {
                        if ($obj->getTipoDomicilioFiscal() == "2") {//Si se asocia al cliente
                            $localidad->setClaveEspecialDomicilio($obj->getClaveCliente());
                            $localidad2 = new Localidad();
                            $localidad2->setEmpresa($empresa);
                            if ($localidad2->getLocalidadByClaveTipo($localidad->getClaveEspecialDomicilio(), "3")) {
                                $localidad->setIdDomicilio($localidad2->getIdDomicilio());
                                $localidad->editRegistro();
                            } else {
                                $localidad->newRegistro(3);
                            }
                        } else {
                            $localidad->setClaveEspecialDomicilio($obj->getClaveCentroCosto());
                            $localidad2 = new Localidad();
                            $localidad2->setEmpresa($empresa);
                            if ($localidad2->getLocalidadByClaveTipo($localidad->getClaveEspecialDomicilio(), "5")) {
                                $localidad->setIdDomicilio($localidad2->getIdDomicilio());
                                $localidad->editRegistro();
                            } else {
                                $localidad->newRegistro(5);
                            }
                        }
                        return 1;
                    } else {
                        return -7; //echo "Error: El centro de costo no se pudo registrar, intenta más tarde por favor";
                    }
                }
            }
        } else {
            return json_encode($resultadoLoggin);
        }
    }else{  //Hay que crear el ticket del nuevo cliente
        $parametro = new ParametroGlobal();
        $parametro->setEmpresa($empresa);
        $configuracion = new Configuracion();
        $configuracion->setEmpresa($empresa);

        $claveCC = "2";
        $nombreCC = "Matriz";
        $claveCliente = "10001";
        $NoSerie = "";
        $Modelo = "";

        if ($parametro->getRegistroById(13)) {
            $claveCC = $parametro->getValor();
        }

        if ($parametro->getRegistroById(12)) {
            $claveCliente = $parametro->getValor();
        }

        if ($parametro->getRegistroById(6)) {
            $nombreCC = $parametro->getValor();
        }

        if ($parametro->getRegistroById(14)) {
            $NoSerie = $parametro->getValor();
            if ($configuracion->getRegistroByNoSerie($NoSerie)) {
                $equipo = new Equipo();
                $equipo->setEmpresa($empresa);
                if ($equipo->getRegistroById($configuracion->getNoParte())) {
                    $Modelo = $equipo->getModelo();
                }
            }
        }
        //$idTipoCliente = 1;
        //$idModalidad = 2;
        if (isset($idFacturacionEmpresa) && !empty($idFacturacionEmpresa)) {
            $id_facturacion_empresa = $idFacturacionEmpresa;
        } else {
            $id_facturacion_empresa = 1000;
        }

        if (isset($rfc_cliente) && !empty($rfc_cliente)) {
            $rfc = $rfc_cliente;
        } else {
            $cliente_aux = new Cliente();
            $cliente_aux->setEmpresa($empresa);
            $rfc_aux = $session->generarClavealeatoria(12);
            while($cliente_aux->getRegistroByRFC($rfc_aux)){
                $rfc_aux = $session->generarClavealeatoria(12);
            }
            $rfc = $rfc_aux;
        }
        if (isset($calle1) && !empty($calle1)) {
            $calle = $calle1;
        } else {
            $calle = "S/I";
        }

        if (isset($noExterior1) && !empty($noExterior1)) {
            $noExterior = $noExterior1;
        } else {
            $noExterior = "0";
        }
        if (isset($noInterior1) && !empty($noInterior1)) {
            $noInterior = $noInterior1;
        } else {
            $noInterior = "0";
        }
        if (isset($colonia1) && !empty($colonia1)) {
            $colonia = $colonia1;
        } else {
            $colonia = "S/I";
        }
        if (isset($ciudad1) && !empty($ciudad1)) {
            $ciudad = $ciudad1;
        } else {
            $ciudad = "S/I";
        }
        if (isset($estado1) && !empty($estado1)) {
            $estado = $estado1;
        } else {
            $estado = "Ciudad de México";
        }
        if (isset($delegacion1) && !empty($delegacion1)) {
            $delegacion = $delegacion1;
        } else {
            $delegacion = "S/I";
        }

        $pais = "S/I";

        if (isset($cp1) && !empty($cp1)) {
            $codigo_postal = $cp1;
        } else {
            $codigo_postal = "S/I";
        }

        //$tamanoperro = 1;
        $sitioweb = $web;
        $comentario = "";
        $calificacion = "null";

        if ($resultadoLoggin > 0) {
            $usuario = "NuevoCliente WS";
            $pantalla = "NuevoCliente WS";
            if (empty($ClaveCliente)) {//Si hay que insertar cliente
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
                    } else {
                        // The file
                        $filename = "../" . $location_final;

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

                $ticket = new Ticket();
                $ticket->setEmpresa($empresa);
                $domicilio_ticket = new DomicilioTicket();
                $domicilio_ticket->setEmpresa($empresa);

                $ticket->setNombreCliente($nombreRazonSocial);
                $ticket->setTelefono1Atenc($telefono);
                $ticket->setCorreoEAtenc($email);
                $ticket->setPantalla($pantalla);
                $ticket->setObservacionAdicional($comentario);

                $ticket->setEstadoDeTicket(2); //ticket cerrado
                $ticket->setTipoReporte(1);
                $ticket->setActualizarInfoCliente(1);
                $ticket->setActualizarInfoEquipo(1);
                $ticket->setActualizarInfoEstatCobra(1);
                $ticket->setClaveCentroCosto($claveCC);
                $ticket->setClaveCliente($claveCliente);
                $ticket->setNombreCentroCosto($nombreCC);
                $ticket->setNoSerieEquipo($NoSerie);
                $ticket->setModeloEquipo($Modelo);
                $ticket->setAreaAtencion(5);
                $ticket->setActivo(1);

                if ($user_obj->getRegistroById($resultadoLoggin)) {
                    if ($user_obj->getPuesto() == "11") {
                        $domicilio_ticket->setEjecutivoCuenta($user_obj->getId());
                    }
                    $ticket->setUsuario($user_obj->getUsuario());
                    $ticket->setUsuarioCreacion($user_obj->getUsuario());
                    $ticket->setUsuarioUltimaModificacion($user_obj->getUsuario());
                } else {
                    $ticket->setUsuario($usuario);
                    $ticket->setUsuarioCreacion($usuario);
                    $ticket->setUsuarioUltimaModificacion($usuario);
                }

                $domicilio_ticket->setFoto($location_final);
                $domicilio_ticket->setCalificacion($calificacion);
                $domicilio_ticket->setCalle($calle);
                $domicilio_ticket->setNoExterior($noExterior);
                $domicilio_ticket->setNoInterior($noInterior);
                $domicilio_ticket->setColonia($colonia);
                $domicilio_ticket->setCiudad($ciudad);
                $domicilio_ticket->setEstado($estado);
                $domicilio_ticket->setPais($pais);
                $domicilio_ticket->setDelegacion($delegacion);
                $domicilio_ticket->setCodigoPostal($codigo_postal);
                $domicilio_ticket->setLatitud($latitud);
                $domicilio_ticket->setLongitud($longitud);
                $domicilio_ticket->setFacebook($facebook);
                $domicilio_ticket->setTwitter($twitter);
                $domicilio_ticket->setHorario($horario);
                $domicilio_ticket->setRFC($rfc);
                $domicilio_ticket->setIdGiro($idGiro);
                $domicilio_ticket->setSitioweb($sitioweb);
                
                if ($ticket->newRegistroCompleto()) {
                    $domicilio_ticket->setIdTicket($ticket->getIdTicket());
                    if ($domicilio_ticket->newRegistro()) {
                        if ($domicilio_ticket->ticketDetalleCliente()) {
                            return json_encode(1);
                        } else {
                            return json_encode(-5); //No se inserto el detalle del ticket
                        }
                    } else {
                        return json_encode(-4); //No se inserto el domicilio del ticket
                    }
                } else {
                    return json_encode(0);
                }
            } else {
                $obj = new CentroCosto();
                $obj->setEmpresa($empresa);
                $obj->setClaveCliente($ClaveCliente);
                $obj->setNombre($nombreRazonSocial);
                $obj->setMoroso("0"); //Por default es 0            
                $obj->setTipoDomicilioFiscal(0);
                $obj->setActivo(1);

                $localidad = new Localidad();
                $localidad->setEmpresa($empresa);
                $localidad->setCalle($calle);
                $localidad->setNoExterior($noExterior);
                $localidad->setNoInterior($noInterior);
                $localidad->setEstado($estado);
                $localidad->setColonia($colonia);
                $localidad->setCiudad($ciudad);
                $localidad->setDelegacion($delegacion);
                $localidad->setPais("México");
                $localidad->setCodigoPostal($codigo_postal);
                $localidad->setLocalidad("");
                $localidad->setIdTipoDomicilio(5);
                $localidad->setActivo(1);

                if ($user_obj->getRegistroById($resultadoLoggin)) {
                    $obj->setUsuarioCreacion($user_obj->getUsuario());
                    $obj->setUsuarioUltimaModificacion($user_obj->getUsuario());
                    $localidad->setUsuarioCreacion($user_obj->getUsuario());
                    $localidad->setUsuarioUltimaModificacion($user_obj->getUsuario());
                } else {
                    $obj->setUsuarioCreacion($usuario);
                    $obj->setUsuarioUltimaModificacion($usuario);
                    $localidad->setUsuarioCreacion($usuario);
                    $localidad->setUsuarioUltimaModificacion($usuario);
                }
                $obj->setPantalla($pantalla);
                $localidad->setPantalla($pantalla);

                $result = $obj->getCentroCostoByClienteYNombre($ClaveCliente, $nombreRazonSocial);
                if (mysql_num_rows($result) > 0) {//Si ya existe una localidad con este nombre                
                    return -6;
                } else {
                    if ($obj->newRegistro()) {
                        if ($obj->getTipoDomicilioFiscal() == "2") {//Si se asocia al cliente
                            $localidad->setClaveEspecialDomicilio($obj->getClaveCliente());
                            $localidad2 = new Localidad();
                            $localidad2->setEmpresa($empresa);
                            if ($localidad2->getLocalidadByClaveTipo($localidad->getClaveEspecialDomicilio(), "3")) {
                                $localidad->setIdDomicilio($localidad2->getIdDomicilio());
                                $localidad->editRegistro();
                            } else {
                                $localidad->newRegistro(3);
                            }
                        } else {
                            $localidad->setClaveEspecialDomicilio($obj->getClaveCentroCosto());
                            $localidad2 = new Localidad();
                            $localidad2->setEmpresa($empresa);
                            if ($localidad2->getLocalidadByClaveTipo($localidad->getClaveEspecialDomicilio(), "5")) {
                                $localidad->setIdDomicilio($localidad2->getIdDomicilio());
                                $localidad->editRegistro();
                            } else {
                                $localidad->newRegistro(5);
                            }
                        }
                        return 1;
                    } else {
                        return -7;//echo "Error: El centro de costo no se pudo registrar, intenta más tarde por favor";
                    }
                }
            }
        } else {
            return json_encode($resultadoLoggin);
        }
    }
}

$server = new soap_server();
$server->configureWSDL("nuevocliente", "urn:nuevocliente");
$server->register("insertarNuevoRegistro", array("NombreRazonSocial" => "xsd:string", "idGiro" => "xsd:int", "latitud" => "xsd:float", "longitud" => "xsd:float",
    "telefono" => "xsd:string", "email" => "xsd:string", "IdSession" => "xsd:string",
    'file' => 'xsd:string', 'location' => 'xsd:string', 'web' => 'xsd:string',
    'rfc_cliente' => "xsd:string", 'facebook' => "xsd:string", 'twitter' => "xsd:string", 'horario' => "xsd:string", 'calle1' => "xsd:string",
    'noExterior1' => "xsd:string", 'noInterior1' => "xsd:string", 'colonia1' => "xsd:string", 'ciudad1' => "xsd:string",
    'estado1' => "xsd:string", 'delegacion1' => "xsd:string", 'cp1' => "xsd:string", 'idFacturacionEmpresa' => "xsd:int", "ClaveCliente" => "xsd:string"), array("return" => "xsd:string"), "urn:nuevocliente", "urn:nuevocliente#insertarNuevoRegistro", "rpc", "encoded", "Inserta un nuevo cliente");

$server->service($HTTP_RAW_POST_DATA);
/* $server->register("getProd");
  $server->service($HTTP_RAW_POST_DATA); */
?>