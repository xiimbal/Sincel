<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/AutorizarEspecial.class.php");
include_once("../WEB-INF/Classes/Bitacora.class.php");
include_once("../WEB-INF/Classes/Inventario.class.php");
include_once("../WEB-INF/Classes/DomicilioTicket.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

function insertaViajeEspecial($idEmpleado, $idCampania, $idTurno, $hora, $origen, $destino, $calle_or, $exterior_or, $interior_or,
                               $colonia_or, $ciudad_or, $delegacion_or, $cp_or, $localidad_or, $estado_or, $latitud_or, $longitud_or,
                               $comentario_or, $cuadrante, $calle_des, $exterior_des, $interior_des, $colonia_des, $ciudad_des,
                               $delegacion_des, $cp_des, $localidad_des, $estado_des, $latitud_des, $longitud_des, $comentario_des, $IdSession)
{
    //return 200;
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);
    
    if ($resultadoLoggin > 0) {
        $user_obj = new Usuario();
        $user_obj->setEmpresa($empresa);
        $usuario = "";
        
        if ($user_obj->getRegistroById($resultadoLoggin)) {
            $usuario = $user_obj->getUsuario();
        }
        $especial = new AutorizarEspecial();
        $especial->setEmpresa($empresa);
        $especial->setIdEmpleado($idEmpleado);
        $especial->setIdCampania($idCampania);
        $especial->setIdTurno($idTurno); 
        $especial->setHora($hora);
        $especial->setOrigen($origen);
        $especial->setDestino($destino); 
        
        $especial->setActivo(1);
        $especial->setUsuarioCreacion($usuario);
        $especial->setUsuarioUltimaModificacion($usuario);
        $especial->setPantalla('WS AgregarViajesEspeciales');
        
        $especial->setCalle_or($calle_or);
        $especial->setExterior_or($exterior_or);
        $especial->setInterior_or($interior_or);
        $especial->setColonia_or($colonia_or);
        $especial->setCiudad_or($ciudad_or);
        $especial->setDelegacion_or($delegacion_or); 
        $especial->setCp_or($cp_or);
        $especial->setLocalidad_or($localidad_or);
        $especial->setEstado_or($estado_or);
        $especial->setLatitud_or($latitud_or);
        $especial->setLongitud_or($longitud_or); 
        $especial->setComentario_or($comentario_or); 
        $especial->setCuadrante($cuadrante);
        
        $especial->setCalle_des($calle_des);
        $especial->setExterior_des($exterior_des);
        $especial->setInterior_des($interior_des); 
        $especial->setColonia_des($colonia_des);
        $especial->setCiudad_des($ciudad_des);
        $especial->setDelegacion_des($delegacion_des);
        $especial->setCp_des($cp_des); 
        $especial->setLocalidad_des($localidad_des); 
        $especial->setEstado_des($estado_des);
        $especial->setLatitud_des($latitud_des);
        $especial->setLongitud_des($longitud_des);
        $especial->setComentario_des($comentario_des);
        if ($especial->newRegistro()) {
            //return 200;
            $idEspecial = $especial->getIdEspecial();
            $catalogo = new Catalogo();
            $catalogo->setEmpresa($empresa);
            $query = $catalogo->obtenerLista("SELECT Loggin FROM c_usuario WHERE IdUsuario='" . $especial->getIdEmpleado() . "'");
            $rs = mysql_fetch_array($query);
            $Loggin = $rs['Loggin'];
            $areaAtencion = $especial->getCuadrante();
            
            $query = $catalogo->obtenerLista("SELECT ca.Descripcion, NombreRazonSocial, ca.ClaveCentroCosto, ccc.ClaveCliente, ccc.Nombre AS NombreCentroCosto FROM c_cliente AS cc INNER JOIN c_centrocosto AS ccc ON cc.ClaveCliente=ccc.ClaveCliente
                                                          JOIN c_area AS ca ON ccc.ClaveCentroCosto=ca.ClaveCentroCosto WHERE IdArea='" . $especial->getIdCampania() . "'");
            $rs = mysql_fetch_array($query);
            $NombreCliente = ($rs['NombreRazonSocial']);
            $ClaveCentroCosto = ($rs['ClaveCentroCosto']);
            $ClaveCliente = ($rs['ClaveCliente']);
            $NombreCentroCosto = ($rs['NombreCentroCosto']);
            $CampaniaU = $rs['Descripcion'];

            $query = $catalogo->obtenerLista("SELECT * FROM c_contacto WHERE ClaveEspecialContacto='" . $ClaveCliente . "';");
            $rs = mysql_fetch_array($query);
            $NombreResp = $rs['Nombre'];
            $TelefonoResp = $rs['Telefono'];
            $CelularResp = $rs['Celular'];
            $CorreoResp = $rs['CorreoElectronico'];

            $query = $catalogo->obtenerLista("SELECT * FROM c_equipo ORDER BY NoParte ASC LIMIT 1");
            $rs = mysql_fetch_array($query);
            $Modelo = $rs['Modelo'];
            $NoParte = $rs['NoParte'];
            
            $bitacora = new Bitacora();
                $bitacora->setNoSerie($Loggin);
                $bitacora->setEmpresa($empresa);
                if (!$bitacora->verficarExistencia()) {//Si se desea crear la serie
                    $Inventario = new Inventario();
                    $Inventario->setEmpresa($empresa);
                    if (!$Inventario->insertarInventarioValidando($Loggin, $NoParte, "", $ClaveCentroCosto, $ClaveCliente, "", FALSE)) {
                        //echo "<br/>Error: NO se registró el Loggin";
                    } else {
                        //echo "<br/>Se registró el Loggin correctamente";
                    }
                }
                
            $consulta = "INSERT INTO c_ticket (
                                FechaHora,Usuario,EstadoDeTicket,TipoReporte,
                                ActualizarInfoEstatCobra, ActualizarInfoCliente,
                                NombreCliente,ClaveCentroCosto,ClaveCliente,NombreCentroCosto,
                                NoSerieEquipo,ModeloEquipo,ActualizarInfoEquipo,
                                 NombreResp,Telefono1Resp,Extension1Resp,Telefono2Resp,Extension2Resp,CelularResp,CorreoEResp,HorarioAtenInicResp,HorarioAtenFinResp,
                                 NombreAtenc,Telefono1Atenc,Extension1Atenc,Telefono2Atenc,Extension2Atenc,CorreoEAtenc,CelularAtenc,HorarioAtenInicAtenc,HorarioAtenFinAtenc,
                                NoTicketCliente,NoTicketDistribuidor,FechHoraInicRep,
                                DescripcionReporte,ObservacionAdicional,AreaAtencion,
                                Activo,UsuarioCreacion,FechaCreacion, FechaUltimaModificacion,UsuarioUltimaModificacion,Pantalla,
                                Ubicacion,UbicacionEmp,FechaCheckIn,FechaCheckOut, Prioridad) 
                                VALUES(NOW(), '" . $especial->getUsuarioUltimaModificacion() . "', 3, 1,
                                     0,0,
                                     '" . $NombreCliente . "','" . $ClaveCentroCosto . "','" . $ClaveCliente . "','" . $NombreCentroCosto . "',
                                     '" . $Loggin . "','" . $Modelo . "',0,
                                     '" . $NombreResp . "','" . $TelefonoResp . "',NULL,0,0,'" . $CelularResp . "','" . $CorreoResp . "',NULL,NULL,
                                     NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,
                                     NULL,NULL,now(),  
                                     'Viaje Especial del Usaurio',NULL," . $areaAtencion . ",
                                     1,'" . $especial->getUsuarioCreacion() . "',NOW(),NOW(),'" . $especial->getUsuarioUltimaModificacion() . "','" . $especial->getPantalla() . "',
                                         1,NULL,0, 0, NULL);";

            $catalogo = new Catalogo();
            $catalogo->setEmpresa($empresa);
            $idTicket = $catalogo->insertarRegistro($consulta);
            if ($idTicket != NULL && $idTicket != 0) {
                    $query = $catalogo->obtenerLista("UPDATE c_especial SET idTicket= '" . $idTicket . "',
                                              UsuarioUltimaModificacion = '" . $especial->getUsuarioUltimaModificacion() . "',FechaUltimaModificacion = now(),
                                              Pantalla = '" . $especial->getPantalla() . "' WHERE idEspecial='" . $especial->getIdEspecial() . "';");
                    if ($query == 1) {
                        //echo "<br/>El Ticket del usuario '" . $Nombre_Usuario . "' se registro correctamente en los datos de Viajes Especiales";

                        $domicilioT = new DomicilioTicket();
                        $domicilioT->setEmpresa($empresa);
                        $domicilioT->setIdTicket($idTicket);
                        $domicilioT->setCalle($especial->getCalle_or());
                        $domicilioT->setActivo(1);
                        $domicilioT->setCiudad($especial->getCiudad_or());
                        $domicilioT->setClaveZona("NULL");
                        $domicilioT->setCodigoPostal($especial->getCp_or());
                        $domicilioT->setColonia($especial->getColonia_or());
                        $domicilioT->setDelegacion($especial->getDelegacion_or());
                        $query = $catalogo->obtenerLista("SELECT Ciudad FROM c_ciudades WHERE IdCiudad='" . $especial->getEstado_or() . "'");
                        $rse = mysql_fetch_array($query);
                        $domicilioT->setEstado($rse['Ciudad']);
                        $domicilioT->setLatitud($especial->getLatitud_or());
                        $domicilioT->setLongitud($especial->getLongitud_or());
                        $domicilioT->setNoExterior($especial->getExterior_or());
                        $domicilioT->setNoInterior($especial->getInterior_or());
                        $domicilioT->setPais("NULL");
                        $domicilioT->setUsuarioCreacion($especial->getUsuarioCreacion());
                        $domicilioT->setUsuarioUltimaModificacion($especial->getUsuarioUltimaModificacion());
                        $domicilioT->setPantalla($especial->getPantalla());
                        if (!$domicilioT->newRegistro()) {
                            //echo "Error: El domicilio del ticket no se pudo registrar";
                        }else{
                            return 1;
                        }
                    }
                
            }
            
        }else{
            //$l=$especial->newRegistro();
            return -3;
        }
    }else{
        return json_encode($resultadoLoggin);
    }
    return -44;
}

$server = new soap_server();
$server->configureWSDL("agregarViajeEspecial", "urn:agregarViajeEspecial");

$server->register("insertaViajeEspecial", 
        array("idEmpleado"=>"xsd:int","idCampania"=>"xsd:int","idTurno"=>"xsd:int","hora"=>"xsd:string","origen"=>"xsd:string","destino"=>"xsd:string","calle_or"=>"xsd:string","exterior_or"=>"xsd:string",
            "interior_or"=>"xsd:string","colonia_or"=>"xsd:string","ciudad_or"=>"xsd:string","delegacion_or"=>"xsd:string","cp_or"=>"xsd:string","localidad_or"=>"xsd:string","estado_or"=>"xsd:string","latitud_or"=>"xsd:float","longitud_or"=>"xsd:float",
            "comentario_or"=>"xsd:string","cuadrante"=>"xsd:int","calle_des"=>"xsd:string","exterior_des"=>"xsd:string","interior_des"=>"xsd:string","colonia_des"=>"xsd:string","ciudad_des"=>"xsd:string","delegacion_des"=>"xsd:string","cp_des"=>"xsd:string",
            "localidad_des"=>"xsd:string","estado_des"=>"xsd:string","latitud_des"=>"xsd:float","longitud_des"=>"xsd:float","comentario_des"=>"xsd:string","IdSession" => "xsd:string"), 
        array("return" => "xsd:string"), "urn:agregarViajeEspecial", "urn:agregarViajeEspecial#insertaViajeEspecial", "rpc", "encoded", "Inserta un viaje especial con ticket");
$server->service($HTTP_RAW_POST_DATA);

?>