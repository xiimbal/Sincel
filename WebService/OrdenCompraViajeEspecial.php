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
include_once("../WEB-INF/Classes/Orden_Compra.class.php");

function insertaOrdenCompraViajeEspecial($idEmpleado, $idCampania, $idTurno, $origen, $destino, $calle_or, $exterior_or, $interior_or,
                                $colonia_or, $ciudad_or, $delegacion_or, $cp_or, $localidad_or, $estado_or, $latitud_or, $longitud_or,
                                $descripcionReporte, $contacto1, $telefonoContacto1, $emailContacto, $contacto2, $telefonoContacto2, 
                                $emailContacto2, $fechaLlegada, $fechaRegreso, $tipoReporte, $areaAtencion, $actualizarTiquet,
                                $calle_des, $exterior_des, $interior_des, $colonia_des, $ciudad_des, $delegacion_des,
                                $cp_des, $localidad_des, $estado_des, $latitud_des, $longitud_des, $comentario_des, $IdSession, $OCs)
{
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
        if(!empty($telefono_chofer)){
            $especial->setComentario_or($especial->getComentario_or(). "Teléfono chofer: ".$telefono_chofer);
        }
        
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
        
        //return $especial->newRegistro();
        if ($especial->newRegistro()) {
            //return 200;
            $idEspecial = $especial->getIdEspecial();
            $catalogo = new Catalogo();
            $catalogo->setEmpresa($empresa);
            $query = $catalogo->obtenerLista("SELECT Loggin FROM c_usuario WHERE IdUsuario='" . $especial->getIdEmpleado() . "'");
            $rs = mysql_fetch_array($query);
            $Loggin = $rs['Loggin'];
            $areaAtencion2 = $especial->getCuadrante();
            
            $query = $catalogo->obtenerLista("SELECT ca.Descripcion, NombreRazonSocial, ca.ClaveCentroCosto, ccc.ClaveCliente, ccc.Nombre AS NombreCentroCosto FROM c_cliente AS cc INNER JOIN c_centrocosto AS ccc ON cc.ClaveCliente=ccc.ClaveCliente
                        JOIN c_area AS ca ON ccc.ClaveCentroCosto=ca.ClaveCentroCosto WHERE IdArea='" . $especial->getIdCampania() . "'");
            $rs = mysql_fetch_array($query);
            if(empty($rs)){
                return -11;
            }
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
                } 
            }
             
            $actualizarTiquet;
            
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
                VALUES(NOW(), '" . $especial->getUsuarioUltimaModificacion() . "', 3, $tipoReporte, $actualizarTiquet,$actualizarTiquet,
                '" . $NombreCliente . "','" . $ClaveCentroCosto . "','" . $ClaveCliente . "','" . $NombreCentroCosto . "',
                '" . $Loggin . "','" . $Modelo . "',0,
                '" . $contacto1 . "','" . $telefonoContacto1 . "',NULL,0,0,'','" . $emailContacto . "',NULL,NULL,
                '$contacto2','$telefonoContacto2',NULL,NULL,NULL,'$emailContacto2',NULL,NULL,NULL,
                NULL,NULL,now(),  
                '$descripcionReporte',NULL," . $areaAtencion . ",
                1,'" . $especial->getUsuarioCreacion() . "',NOW(),NOW(),'" . $especial->getUsuarioUltimaModificacion() . "','" . $especial->getPantalla() . "',
                1,NULL,'$fechaLlegada', '$fechaRegreso', NULL);";

            $catalogo = new Catalogo();
            $catalogo->setEmpresa($empresa);
            //return $consulta;
            $idTicket = $catalogo->insertarRegistro($consulta);
            if ($idTicket != NULL && $idTicket != 0) {
                $query = $catalogo->obtenerLista("UPDATE c_especial SET idTicket= '" . $idTicket . "',
                    UsuarioUltimaModificacion = '" . $especial->getUsuarioUltimaModificacion() . "',FechaUltimaModificacion = now(),
                    Pantalla = '" . $especial->getPantalla() . "' WHERE idEspecial='" . $especial->getIdEspecial() . "';");
                if ($query == 1) {
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
                        return -8;
                    }else{
                        $arrayOC = split(",", $OCs);
                        $ordenCompra = new Orden_Compra();
                        $ordenCompra->setEmpresa($empresa);
                        $descripcionTicket = "";
                        $contador = 0;
                        foreach($arrayOC AS $idOC){
                            $contador++;
                            $ordenCompra->getRegistroById($idOC);
                            if(!$ordenCompra->registrarRelacionTyF($idTicket,"0")){
                                return -9;
                            }
                            $descripcionTicket .= "Proveedor $contador: ". $ordenCompra->getNombreProveedor()." - OC: ".$ordenCompra->getNo_pedido()."<br/>"; 
                        }
                        $query = $catalogo->obtenerLista("UPDATE c_ticket SET DescripcionReporte = '$descripcionTicket' WHERE IdTicket = $idTicket;");
                        if ($query != 1) {
                            return -10;
                        }
                        return $idTicket;
                    }
                }else{
                    return -7;
                }
            }else{
                return -6; 
            }
            
            
            
        }else{
            return -5;
        }
    }else{
        return $resultadoLoggin;
    }
}

$server = new soap_server();
$server->configureWSDL("OrdenCompraViajeEspecial", "urn:OrdenCompraViajeEspecial");

$server->register("insertaOrdenCompraViajeEspecial", 
        array("idEmpleado"=>"xsd:int","idCampania"=>"xsd:int","idTurno"=>"xsd:int","origen"=>"xsd:string","destino"=>"xsd:string","calle_or"=>"xsd:string","exterior_or"=>"xsd:string",
            "interior_or"=>"xsd:string","colonia_or"=>"xsd:string","ciudad_or"=>"xsd:string","delegacion_or"=>"xsd:string","cp_or"=>"xsd:string","localidad_or"=>"xsd:string","estado_or"=>"xsd:string","latitud_or"=>"xsd:float","longitud_or"=>"xsd:float",
            "descripcionReporte"=>"xsd:string","contacto1"=>"xsd:string","telefonoContacto1"=>"xsd:string","emailContacto"=>"xsd:string","contacto2"=>"xsd:string","telefonoContacto2"=>"xsd:string",
            "emailContacto2"=>"xsd:string","fechaLlegada"=>"xsd:string","fechaRegreso"=>"xsd:string","tipoReporte"=>"xsd:int","areaAtencion"=>"xsd:int","actualizarTiquet"=>"xsd:int",
            "calle_des"=>"xsd:string","exterior_des"=>"xsd:string","interior_des"=>"xsd:string","colonia_des"=>"xsd:string","ciudad_des"=>"xsd:string","delegacion_des"=>"xsd:string","cp_des"=>"xsd:string",
            "localidad_des"=>"xsd:string","estado_des"=>"xsd:string","latitud_des"=>"xsd:float","longitud_des"=>"xsd:float","comentario_des"=>"xsd:string","IdSession" => "xsd:string","OCs" => "xsd:string"), 
        array("return" => "xsd:string"), "urn:OrdenCompraViajeEspecial", "urn:OrdenCompraViajeEspecial#insertaOrdenCompraViajeEspecial", "rpc", "encoded", "Inserta un viaje especial con oc");
$server->service($HTTP_RAW_POST_DATA);

?>