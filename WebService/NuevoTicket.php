<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Ticket.class.php");
include_once("../WEB-INF/Classes/Inventario.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/CentroCosto.class.php");
include_once("../WEB-INF/Classes/Configuracion.class.php");
include_once("../WEB-INF/Classes/NotaTicket.class.php");
include_once("../WEB-INF/Classes/NotaRefaccion.class.php");
include_once("../WEB-INF/Classes/Pedido.class.php");
include_once("../WEB-INF/Classes/LecturaTicket.class.php");
include_once("../WEB-INF/Classes/Equipo.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("../WEB-INF/Classes/SolicitudToner.class.php");
include_once("../WEB-INF/Classes/DomicilioTicket.class.php");
include_once("../WEB-INF/Classes/CompCompatiblesEq.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Bitacora.class.php");
include_once("../WEB-INF/Classes/Mail.class.php");
include_once("../WEB-INF/Classes/ParametroGlobal.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/lib/PHPImagen.lib.php");

function insertaTicket($ClaveCliente, $ClaveLocalidad, $Serie, $DescripcionReporte, $Calle, $NoExterior, $NoInterior, $Colonia, $Ciudad, $Estado, 
        $Delegacion, $Pais, $CP, $Contacto1, $TelefonoContacto1, $EmailContacto1, $Contacto2, $TelefonoContacto2, $EmailContacto2, 
        $Latitud, $Longitud, $FechaCheckIn, $FechaCheckOut, $TipoReporte, $AreaAtencion, $ActualizarDir, $NoParte, $IdSession, $IdTecnico, $IdTicket) {

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
    
    if($parametroGlobal->getRegistroById("8")){
        $mail->setFrom($parametroGlobal->getValor());
    }else{
        $mail->setFrom("scg-salida@scgenesis.mx");
    }
    $mail->setSubject("Uso de WS Nuevo Ticket empresa $empresa");
    $cadena_correo = "Los parámetro recibidos en el WS NuevoTicket.php usado por el usuario con id $resultadoLoggin son: Cliente: $ClaveCliente, Localidad: $ClaveLocalidad, Serie: $Serie, Descripción: $DescripcionReporte, Calle: $Calle, No. Exterior: $NoExterior, No. Interior: $NoInterior, Colonia: $Colonia, Ciudad: $Ciudad, Estado: $Estado, "
            . "Delegacion: $Delegacion, Pais: $Pais, CP: $CP, Contacto1: $Contacto1, Telefonocontacto1:  $TelefonoContacto1, EmailContacto1: $EmailContacto1, Contacto2: $Contacto2, TelefonoContacto2: $TelefonoContacto2, EmailContacto2: $EmailContacto2, "
            . "Latitud: $Latitud, Longitud: $Longitud, FechaCheckIn: $FechaCheckIn, FechaCheckout: $FechaCheckOut, TipoReporte: $TipoReporte, AreaAtencion: $AreaAtencion, ActualizarDir: $ActualizarDir, No.Parte: $NoParte, IdSesion: $IdSession, IdTecnico: $IdTecnico, IdTicket: $IdTicket";
    $mail->setBody($cadena_correo);
    /* Obtenemos los correos a quien mandaremos el mail */
    $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 21;");
    $correos = array();    
    while ($rs = mysql_fetch_array($query4)) {
        $value = $rs['correo'];
        if (isset($value) && $value != "" && $value != NULL && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
            array_push($correos, $value);
        }
    }
    $mail->setTo($correos);
    $mail->enviarMail();

    if ($resultadoLoggin > 0) {                
        $bitacora = new Bitacora();
        $bitacora->setEmpresa($empresa);
        $bitacora->setNoSerie($Serie);
        if(!$bitacora->verficarExistencia()){//Si se desea crear la serie
            $Inventario = new Inventario();
            $Inventario->setEmpresa($empresa);
            if(!$Inventario->insertarInventarioValidando($Serie, $NoParte, "", $ClaveLocalidad, $ClaveCliente, "", FALSE)){
                return -13;
            }
        }
        
        $obj = new Ticket();
        $obj->setEmpresa($empresa);
        $cliente = new Cliente();
        $cliente->setEmpresa($empresa);
        $cc = new CentroCosto();
        $cc->setEmpresa($empresa);
        $user_obj = new Usuario();
        $user_obj->setEmpresa($empresa);
        $configuracion = new Configuracion();
        $configuracion->setEmpresa($empresa);
        $equipo = new Equipo();
        $equipo->setEmpresa($empresa);
        $notaTicket = new NotaTicket();
        $notaTicket->setEmpresa($empresa);
        $pedido = new Pedido();
        $pedido->setEmpresa($empresa);
        $notaRefaccion = new NotaRefaccion();
        $notaRefaccion->setEmpresa($empresa);
        $lecturaTicket = new LecturaTicket();
        $lecturaTicket->setEmpresa($empresa);
        $caracteristicas = new EquipoCaracteristicasFormatoServicio();
        $caracteristicas->setEmpresa($empresa);
        $solicitudToner = new SolicitudToner();
        $solicitudToner->setEmpresa($empresa);

        if ($user_obj->getRegistroById($resultadoLoggin)) {
            $usuario = $user_obj->getUsuario();
        } else {
            $usuario = "User WS";
        }
        $pantalla = "Nuevo Ticket WS";
        
        if(!empty($IdTicket)){
            $domicilioTicket = new DomicilioTicket();
            $domicilioTicket->setEmpresa($empresa);
            $domicilioTicket->setIdTicket($IdTicket);
            $domicilioTicket->setClaveZona("");
            $domicilioTicket->setCalle($Calle);
            $domicilioTicket->setNoInterior($NoInterior);
            $domicilioTicket->setNoExterior($NoExterior);
            $domicilioTicket->setColonia($Colonia);
            $domicilioTicket->setCiudad($Ciudad);
            $domicilioTicket->setEstado($Estado);
            $domicilioTicket->setDelegacion($Delegacion);
            $domicilioTicket->setPais($Pais);
            $domicilioTicket->setCodigoPostal($CP);
            $domicilioTicket->setLatitud($Latitud);
            $domicilioTicket->setLongitud($Longitud);
            $domicilioTicket->setUsuarioCreacion($usuario);
            $domicilioTicket->setUsuarioUltimaModificacion($usuario);
            $domicilioTicket->setPantalla($pantalla);
            if($domicilioTicket->updateDomicilioTicket()){
                return 1;
            }else{
                return -17;
            }
        }

        $obj->setClaveCentroCosto($ClaveLocalidad);
        $obj->setClaveCliente($ClaveCliente);
        if ($cliente->getRegistroById($ClaveCliente)) {
            $obj->setNombreCliente($cliente->getNombreRazonSocial());
        } else {
            $obj->setNombreCliente("");
        }

        if ($cc->getRegistroById($ClaveLocalidad)) {
            $obj->setNombreCentroCosto($cc->getNombre());
        } else {
            $obj->setNombreCentroCosto("");
        }

        if($ActualizarDir == "1"){
            $obj->setActualizarInfoCliente(1);
        }else{
            $obj->setActualizarInfoCliente(0);
        }
        $obj->setActualizarInfoEquipo(0);
        $obj->setActualizarInfoEstatCobra(0);
        $obj->setUsuario($usuario);
        $obj->setEstadoDeTicket(3);
        $obj->setTipoReporte($TipoReporte);


        if ($TipoReporte != "15") {//falla            
            $obj->setNoSerieEquipo($Serie);
            if ($configuracion->getRegistroByNoSerie($Serie) && $equipo->getRegistroById($configuracion->getNoParte())) {
                $obj->setModeloEquipo($equipo->getModelo());
            }
        } else {//toner
            $array_serie = explode(",", $Serie);
            if (is_array($array_serie) && isset($array_serie[0])) {
                $obj->setNoSerieEquipo($array_serie[0]);
                if ($configuracion->getRegistroByNoSerie($array_serie[0]) && $equipo->getRegistroById($configuracion->getNoParte())) {
                    $obj->setModeloEquipo($equipo->getModelo());
                }
            }
        }//otros valores

        $obj->setNombreResp($Contacto1);
        $obj->setTelefono1Resp($TelefonoContacto1);
        $obj->setExtension1Resp("");
        $obj->setTelefono2Resp($TelefonoContacto1);
        $obj->setExtension2Resp("");
        $obj->setCelularResp($TelefonoContacto1);
        $obj->setCorreoEResp($EmailContacto1);
        $obj->setHorarioAtenInicResp("");
        $obj->setHorarioAtenFinResp("");

        $obj->setNombreAtenc($Contacto2);
        $obj->setTelefono1Atenc($TelefonoContacto2);
        $obj->setExtension1Atenc("");
        $obj->setTelefono2Atenc($TelefonoContacto2);
        $obj->setExtension2Atenc("");
        $obj->setCelularAtenc($TelefonoContacto2);
        $obj->setCorreoEAtenc($EmailContacto2);
        $obj->setHorarioAtenInicAtenc("");
        $obj->setHorarioAtenFinAtenc("");

        $obj->setNoTicketCliente("");
        $obj->setNoTicketDistribuidor("");
        $obj->setDescripcionReporte($DescripcionReporte);
        $obj->setObservacionAdicional("");
        $obj->setAreaAtencion($AreaAtencion);
        $obj->setUbicacion("1");
        $obj->setUbicacionEmp("");
        //datos de auditoria
        $obj->setActivo(1);
        $obj->setUsuarioCreacion($usuario);
        $obj->setUsuarioUltimaModificacion($usuario);
        $obj->setPantalla($pantalla);
        $obj->setFechaCheckIn($FechaCheckIn);
        $obj->setFechaCheckOut($FechaCheckOut);
        
        if ($obj->newRegistroCompleto()) {
            if($ActualizarDir == "1"){
                $domicilioTicket = new DomicilioTicket();
                $domicilioTicket->setEmpresa($empresa);
                $domicilioTicket->setIdTicket($obj->getIdTicket());
                $domicilioTicket->setClaveZona("");
                $domicilioTicket->setCalle($Calle);
                $domicilioTicket->setNoInterior($NoInterior);
                $domicilioTicket->setNoExterior($NoExterior);
                $domicilioTicket->setColonia($Colonia);
                $domicilioTicket->setCiudad($Ciudad);
                $domicilioTicket->setEstado($Estado);
                $domicilioTicket->setDelegacion($Delegacion);
                $domicilioTicket->setPais($Pais);
                $domicilioTicket->setCodigoPostal($CP);
                $domicilioTicket->setLatitud($Latitud);
                $domicilioTicket->setLongitud($Longitud);
                $domicilioTicket->setUsuarioCreacion($usuario);
                $domicilioTicket->setUsuarioUltimaModificacion($usuario);
                $domicilioTicket->setPantalla($pantalla);
                if (!$domicilioTicket->newRegistro()) {
                    return -12; //Error al insertar domicilio
                }
            }
            
            if(!empty($IdTecnico)){//En caso que se reciba un id de tecnico se tiene que asociar el ticket a dicho técnico
                $usuario_obj = new Usuario();
                $usuario_obj->setEmpresa($empresa);
                if(!$usuario_obj->getRegistroById($IdTecnico)){
                    return -14;//No se encuentra el id del tecnico
                }
                
                if ($obj->asociarTicketTecnicoGeneral($IdTecnico, "", "", "", "")) {
                    if (!$obj->crearNota($usuario_obj->getNombre() . " " . $usuario_obj->getPaterno() . " " . $usuario_obj->getMaterno(), "")) {
                        return -15;//No se pudo crear la nota de asignacion
                    }
                }else{
                    return -16;//No se pudo asociar el tecnico con el ticket
                }
            }
            
            //agregar Lectura 
            if ($TipoReporte == "15") {
                $notaTicket->setIdTicket($obj->getIdTicket());
                $notaTicket->setDiagnostico("Solicitud de toner");
                $notaTicket->setIdEstatus(67);
                $notaTicket->setUsuarioSolicitud($usuario);
                $notaTicket->setMostrarCliente(1);
                $notaTicket->setActivo(1);
                $notaTicket->setUsuarioCreacion($usuario);
                $notaTicket->setUsuarioModificacion($usuario);
                $notaTicket->setPantalla($pantalla);
                if ($notaTicket->newRegistro()) {//agregar la nota de solicitud de toner               
                    $pedido->setIdTicket($obj->getIdTicket());
                    $pedido->setActivo(1);
                    $pedido->setUsuarioCreacion($usuario);
                    $pedido->setUsuarioUltimaModificacion($usuario);
                    $pedido->setPantalla($pantalla);
                    $pedido->setEstado("Validar Existencia");
                    $contadorTabla = 0;
                    //datos de la nota refaccion
                    $notaRefaccion->setIdNota($notaTicket->getIdNota());
                    $notaRefaccion->setNoParte($notaTicket->getIdNota());
                    $notaRefaccion->setCantidad(1);
                    $notaRefaccion->setCantidadSurtidas(0);
                    $notaRefaccion->setIdAlmacen("null");
                    $notaRefaccion->setUsuarioCreacion($usuario);
                    $notaRefaccion->setUsuarioModificacion($usuario);
                    $notaRefaccion->setPantalla($pantalla);

                    $array_series = explode(",", $Serie);

                    while ($contadorTabla < count($array_series)) {
                        $pedido->setClaveEspEquipo($array_series[$contadorTabla]);
                        $notaRefaccion->setNoSerie($array_series[$contadorTabla]);
                        $modelo = "";
                        if ($configuracion->getRegistroByNoSerie($array_series[$contadorTabla]) && $equipo->getRegistroById($configuracion->getNoParte())) {
                            $modelo = $equipo->getModelo();
                        }
                        $pedido->setModelo($modelo);

                        $negro = 0;
                        $cian = 0;
                        $magenta = 0;
                        $amarillo = 0;

                        $lecturaTicket->setClaveEspEquipo($array_series[$contadorTabla]);
                        $lecturaTicket->setModeloEquipo($modelo);
                        $lecturaTicket->setContadorBN(0);
                        $lecturaTicket->setNivelNegro(0);


                        if ($caracteristicas->isColor($configuracion->getNoParte())) {
                            $lecturaTicket->setContadorColor(0);
                            $lecturaTicket->setNivelCia(0);
                            $lecturaTicket->setNivelMagenta(0);
                            $lecturaTicket->setNivelAmarillo(0);
                        } else {
                            $lecturaTicket->setContadorColor("");
                            $lecturaTicket->setNivelCia("");
                            $lecturaTicket->setNivelMagenta("");
                            $lecturaTicket->setNivelAmarillo("");
                        }
                        $lecturaTicket->setIdTicket($obj->getIdTicket());

                        $lecturaTicket->setFechaA(date('Y') . "-" . date('m') . "-" . date('d'));
                        $lecturaTicket->setContadorBNA(0);
                        $lecturaTicket->setNivelNegroA(0);

                        if ($caracteristicas->isColor($configuracion->getNoParte())) {
                            $lecturaTicket->setContadorColorA(0);
                            $lecturaTicket->setNivelCiaA(0);
                            $lecturaTicket->setNivelMagentaA(0);
                            $lecturaTicket->setNivelAmarilloA(0);
                        } else {
                            $lecturaTicket->setContadorColorA("");
                            $lecturaTicket->setNivelCiaA("");
                            $lecturaTicket->setNivelMagentaA("");
                            $lecturaTicket->setNivelAmarilloA("");
                        }

                        $lecturaTicket->setComentario("");
                        $lecturaTicket->setActivo(1);
                        $lecturaTicket->setUsuarioCreacion($usuario);
                        $lecturaTicket->setUsuarioUltimaModificacion($usuario);
                        $lecturaTicket->setPantalla($pantalla);
                        $idLecturaEquipo = 0;
                        if ($lecturaTicket->NewRegistro()) {
                            $idLecturaEquipo = $lecturaTicket->getIdLectura();
                        } else {
                            
                        }
                        $pedido->setIdLecturaTicket($idLecturaEquipo);

                        //if (isset($parametros['ckbNegro_' . $contadorTabla]) && $parametros['ckbNegro_' . $contadorTabla] == "on" && isset($parametros["txtTonerNegro" . $contadorTabla])) {
                        if (true) {
                            $negro = 1;
                            $componenteComp = new CompCompatiblesEq();
                            $componenteComp->setEmpresa($empresa);
                            $compatibles = $componenteComp->getComponentesCompatibles($configuracion->getNoParte(), 2);
                            if (!empty($compatibles)) {
                                $notaRefaccion->setNoParte($compatibles[0]);                                
                                if ($notaRefaccion->newRegistroSerie()) {//agregar en detalle nota refaccion
                                    if (!$notaRefaccion->newRegistroDetalle()) {
                                        return -5; //echo "<br/>Error: El detalle no se agregó correctamente";
                                    }
                                } else {
                                    if ($notaRefaccion->VerificarExistencia()) {
                                        if ($notaRefaccion->editarCantidadTonerRepetido()) {
                                            if (!$notaRefaccion->newRegistroDetalle()) {
                                                return -6; //echo "<br/>Error: El detalle no se agregó correctamente";
                                            }
                                        } else {
                                            return -7; //echo "<br/>Error: El toner no se registro correctamente";
                                        }
                                    } else {
                                        return -8; //echo "<br/>Error: El toner no se registro correctamente";
                                    }
                                }
                            }
                        }

                        //if (isset($parametros['ckbCian_' . $contadorTabla]) && $parametros['ckbCian_' . $contadorTabla] == "on" && isset($parametros["txtTonerCian" . $contadorTabla])) {
                        if (false) {
                            $cian = 1;
                            $componenteComp = new CompCompatiblesEq();
                            $componenteComp->setEmpresa($empresa);
                            $compatibles = $componenteComp->getComponentesCompatibles($configuracion->getNoParte(), 2);
                            if (!empty($compatibles)) {
                                $notaRefaccion->setNoParte($compatibles[0]);
                                if ($notaRefaccion->newRegistroSerie()) {
                                    if (!$notaRefaccion->newRegistroDetalle()) {
                                        return -5; //echo "<br/>Error: El detalle no se agregó correctamente";
                                    }
                                } else {
                                    if ($notaRefaccion->VerificarExistencia()) {
                                        if ($notaRefaccion->editarCantidadTonerRepetido()) {
                                            if (!$notaRefaccion->newRegistroDetalle()) {
                                                return -6; //echo "<br/>Error: El detalle no se agregó correctamente";
                                            }
                                        } else {
                                            return -7; //echo "<br/>Error: El toner no se registró correctamente";
                                        }
                                    } else {
                                        return -8; //echo "<br/>Error: El toner no se registró correctamente";
                                    }
                                }
                            }
                        }

                        //if (isset($parametros['ckbMagenta_' . $contadorTabla]) && $parametros['ckbMagenta_' . $contadorTabla] == "on" && isset($parametros["txtTonerMagenta" . $contadorTabla])) {
                        if (false) {
                            $magenta = 1;
                            $componenteComp = new CompCompatiblesEq();
                            $componenteComp->setEmpresa($empresa);
                            $compatibles = $componenteComp->getComponentesCompatibles($configuracion->getNoParte(), 2);
                            if (!empty($compatibles)) {
                                $notaRefaccion->setNoParte($compatibles[0]);
                                if ($notaRefaccion->newRegistroSerie()) {
                                    if (!$notaRefaccion->newRegistroDetalle()) {
                                        return -5; //echo "<br/>Error: El detalle no se agregó correctamente";
                                    }
                                } else {
                                    if ($notaRefaccion->VerificarExistencia()) {
                                        if ($notaRefaccion->editarCantidadTonerRepetido()) {
                                            if (!$notaRefaccion->newRegistroDetalle()) {
                                                return -6; //echo "<br/>Error: El detalle no se agregó correctamente";
                                            }
                                        } else {
                                            return -7; //echo "<br/>Error: El toner no se registro correctamente";
                                        }
                                    } else {
                                        return -8; //echo "<br/>Error: El toner no se registro correctamente";
                                    }
                                }
                            }
                        }

                        //if (isset($parametros['ckbAmarillo_' . $contadorTabla]) && $parametros['ckbAmarillo_' . $contadorTabla] == "on" && isset($parametros["txtTonerAmarillo" . $contadorTabla])) {
                        if (false) {
                            $amarillo = 1;
                            $componenteComp = new CompCompatiblesEq();
                            $componenteComp->setEmpresa($empresa);
                            $compatibles = $componenteComp->getComponentesCompatibles($configuracion->getNoParte(), 2);
                            if (!empty($compatibles)) {
                                $notaRefaccion->setNoParte($compatibles[0]);
                                if ($notaRefaccion->newRegistroSerie()) {
                                    if (!$notaRefaccion->newRegistroDetalle()) {
                                        return -5; //echo "<br/>Error: El detalle no se agregó correctamente";
                                    }
                                } else {
                                    if ($notaRefaccion->VerificarExistencia()) {
                                        if ($notaRefaccion->editarCantidadTonerRepetido()) {
                                            if (!$notaRefaccion->newRegistroDetalle()) {
                                                return -6; //echo "<br/>Error: El detalle no se agregó correctamente";
                                            }
                                        } else {
                                            return -7; //echo "<br/>Error: El toner no se registro correctamente";
                                        }
                                    } else {
                                        return -8; //echo "<br/>Error: El toner no se registro correctamente";
                                    }
                                }
                            }
                        }

                        $pedido->setTonerNegro($negro);
                        $pedido->setTonerCian($cian);
                        $pedido->setTonerMagenta($magenta);
                        $pedido->setTonerAmarillo($amarillo);
                        if (!$pedido->newRegistro()) {
                            return -9; //echo "<br/>Error: El pedido no se registró correctamente";
                        }

                        $contadorTabla++;
                    }//fin while
                    //copiar la nota 
                    //return -1051;
                    $solicitudToner->setNotaAnterior($notaTicket->getIdNota());
                    $solicitudToner->setIdEstadoNota(65);
                    $solicitudToner->setMostrarCliente(0);                    
                    $notaTicket->setDiagnostico("Solicitud de toner");
                    $solicitudToner->setUsuarioCreacion($usuario);
                    $solicitudToner->setUsuarioModificacion($usuario);
                    $solicitudToner->setPantalla($pantalla);
                                        
                    if ($solicitudToner->newNotaSolicitudTonerTicket()) {                         
                        if ($solicitudToner->copyTonerNota()) {
                            
                        }
                    }
                    $obj->setEmpresa($empresa);
                    if ($obj->editTicketDescripcion()) {
                        return 1;
                    } else {
                        return -10; //echo "<br/>Error: EL ticket <b>" . $obj->getIdTicket() . "</b> no se registró correctamente";
                    }
                } else {
                    return -11; //echo "<br/>Error: La nota no se agregó correctamente";
                }
            } else {
                $lecturaTicket->setClaveEspEquipo($Serie);
                $modelo = "";
                if ($configuracion->getRegistroByNoSerie($array_series[$contadorTabla]) && $equipo->getRegistroById($configuracion->getNoParte())) {
                    $modelo = $equipo->getModelo();
                }

                $lecturaTicket->setModeloEquipo($modelo);
                $lecturaTicket->setContadorBN(0);
                if ($caracteristicas->isColor($configuracion->getNoParte())) {
                    $lecturaTicket->setContadorColor(0);
                } else {
                    $lecturaTicket->setContadorColor("");
                }
                $lecturaTicket->setNivelNegro("");
                $lecturaTicket->setNivelCia("");
                $lecturaTicket->setNivelMagenta("");
                $lecturaTicket->setNivelAmarillo("");
                $lecturaTicket->setIdTicket($obj->getIdTicket());
//                    $lecturaTicket->setFecha($parametros['fechaContador']);
                $lecturaTicket->setFechaA(date('Y') . "-" . date('m') . "-" . date('d'));
                $lecturaTicket->setContadorBNA(0);
                if ($caracteristicas->isColor($configuracion->getNoParte())) {
                    $lecturaTicket->setContadorColorA(0);
                } else {
                    $lecturaTicket->setContadorColorA("");
                }
                $lecturaTicket->setNivelNegroA("");
                $lecturaTicket->setNivelCiaA("");
                $lecturaTicket->setNivelMagentaA("");
                $lecturaTicket->setNivelAmarilloA("");
                $lecturaTicket->setComentario("");
                $lecturaTicket->setActivo(1);
                $lecturaTicket->setUsuarioCreacion($usuario);
                $lecturaTicket->setUsuarioUltimaModificacion($usuario);
                $lecturaTicket->setPantalla($pantalla);
                $idLecturaEquipo = 0;
                if ($lecturaTicket->NewRegistro()) {
                    $idLecturaEquipo = $lecturaTicket->getIdLectura();
                } else {
                    
                }
                return 1;
            }
        } else {
            return -4; //No se pudo registrar el ticket
        }
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("nuevoTicket", "urn:nuevoTicket");
$server->register("insertaTicket", array(
    "ClaveCliente" => "xsd:string", "ClaveLocalidad" => "xsd:string", "Serie" => "xsd:string", "DescripcionReporte" => "xsd:string",
    "Calle" => "xsd:string", "NoExterior" => "xsd:string", "NoInterior" => "xsd:string", "Colonia" => "xsd:string", "Ciudad" => "xsd:string", "Estado" => "xsd:string", "Delegacion" => "xsd:string", "Pais" => "xsd:string", "CP" => "xsd:int",
    "Contacto1" => "xsd:string", "TelefonoContacto1" => "xsd:string", "EmailContacto1" => "xsd:string",
    "Contacto2" => "xsd:string", "TelefonoContacto2" => "xsd:string", "EmailContacto2" => "xsd:string",
    "Latitud" => "xsd:float", "Longitud" => "xsd:float", "FechaCheckIn" => "xsd:string", "FechaCheckOut" => "xsd:string", 
    "TipoReporte" => "xsd:int", "AreaAtencion" => "xsd:int", "ActualizarDir" => "xsd:int", "NoParte" => "xsd:string", "IdSession" => "xsd:string", 
    "IdTecnico" => "xsd:int", "IdTicket" => "xsd:int"), array("return" => "xsd:string"), "urn:nuevoTicket", "urn:nuevoTicket#insertaTicket", "rpc", "encoded", "Inserta un nuevo ticket");

$server->service($HTTP_RAW_POST_DATA);
?>