<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/NotaTicket.class.php");
include_once("../WEB-INF/Classes/UbicacionUsuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Pedido.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Ticket.class.php");
include_once("../WEB-INF/Classes/lib/PHPImagen.lib.php");

function insertaNota($Titulo, $Mensaje, $FotoCodificada, $NombreFoto, $Fecha, $MinutoDesfase, $IdEstatus, $IdTicket, $Latitud, $Longitud, $IdSession) {
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);
    $TM = 0;
    $Tickets = array();
    if ($resultadoLoggin > 0) { //Id del usuario
        if ($empresa == 5) {
            $ticketLoyal = false;
            $catalogo = new Catalogo();
            $catalogo->setEmpresa($empresa);

            $IdTecnico = $resultadoLoggin;
            $consulta1 = "SELECT cp.*, kpa.Asistencia FROM c_plantilla cp INNER JOIN k_plantilla kp ON cp.idPlantilla=kp.idPlantilla
                 INNER JOIN k_plantilla_asistencia kpa ON kp.idK_Plantilla=kpa.idK_Plantilla WHERE kpa.IdTicket = $IdTicket;";
            $result1 = $catalogo->obtenerLista($consulta1);
            if (mysql_num_rows($result1) > 0) {
                $ticketLoyal = true;
                $rs = mysql_fetch_array($result1);
                $estatusPlantilla = $rs['Estatus'];
            }
//            else {
//                $consulta1 = "SELECT * FROM c_especial WHERE idTicket = $IdTicket;";
//                $result1 = $catalogo->obtenerLista($consulta1);
//                if (mysql_num_rows($result1) > 0) {
//                $ticketLoyal=true;
//            }
//            }

            $user_obj = new Usuario();
            $user_obj->setEmpresa($empresa);
            if ($user_obj->getRegistroById($resultadoLoggin)) { //$resultadoLoggin = $IdTecnico
                $usuario = $user_obj->getUsuario();
            } else {
                return -33; //No se encuentra el id del tecnico
            }


            if ($ticketLoyal) {

                $consulta = "SELECT IdTicket FROM `k_tecnicoticket` WHERE IdUsuario = $IdTecnico AND FechaHoraInicio = '$Fecha'";
                $result = $catalogo->obtenerLista($consulta);
                if (mysql_num_rows($result) > 0) {
                    while ($rs = mysql_fetch_array($result)) {
                        return -13; //El técnico ya tiene asignado un ticket en la misma fecha/hora
                    }
                }

                $datos_ticket = new Ticket();
                $datos_ticket->setEmpresa($empresa);
                $datos_ticket->getTicketByID($IdTicket);
                $datos_ticket->setUsuarioCreacion($usuario);
                $datos_ticket->setUsuarioUltimaModificacion($usuario);
                $datos_ticket->setPantalla("WS NuevaNota (Asignacion TicketTecnico)");
                $estadoTicket = $datos_ticket->getEstadoDeTicket();

                if ($estatusPlantilla == 2 && $estadoTicket == 3) {
                    $consulta = "SELECT * FROM `k_tecnicoticket` WHERE IdTicket = $IdTicket;";
                    $result = $catalogo->obtenerLista($consulta);
                    if (mysql_num_rows($result) > 0) {
                        $rs = mysql_fetch_array($result);
                        if ($rs['IdUsuario'] != $IdTecnico) {
                            return -11; //El empleado(Ticket) esta asignado con otro Tecnico
                            //$datos_ticket->eliminarAsignaciones(); //Eliminamos asignaciones anteriores    
                        }//Si el tecnico esta asignado al ticket se comienza a generar nota
                        array_push($Tickets, $IdTicket);
                    } else {
                        if ($IdEstatus == 16) {
                            $consulta1 = "SELECT * FROM k_relacion_tickets WHERE IdTicketSimple = $IdTicket;";
                            $result1 = $catalogo->obtenerLista($consulta1);
                            if (mysql_num_rows($result1) <= 0) {
                                return -55; //El ticket no se ecnuntra registrado con un ticket multiusuario
                            } else {
                                $rs = mysql_fetch_array($result1);
                                $EstatusT = $rs['Estatus'];
                                $idTicketMultiple = $rs['IdTicketMultiple'];
                                if ($EstatusT == 2) {
                                    return -67; //El ticket ya tiene checkout // si se muestra es porque el ticket sigue abierto 
                                } else {
                                    $consulta = ("UPDATE k_relacion_tickets SET Estatus = 2 WHERE IdTicketSimple = " . $IdTicket . ";");
                                    $query = $catalogo->obtenerLista($consulta);
                                    if ($query != 1) {
                                        return -23; //Estatus de Ticket no se pudo modificar
                                    }

                                    $consulta = ("UPDATE c_ticket SET EstadoDeTicket = 2 WHERE IdTicket = " . $IdTicket . ";");
                                    $query = $catalogo->obtenerLista($consulta);
                                    if ($query != 1) {
                                        return -28; //No se actualizo el estado de ticket de Ticket Simple perteneciente a uno Multiusuario
                                    }

                                    $TM = 2;
                                    //array_push($Tickets, $idTicketMultiple);
                                    array_push($Tickets, $IdTicket);

                                    $consulta = "SELECT * FROM k_relacion_tickets WHERE IdTicketMultiple=$idTicketMultiple;";
                                    $result = $catalogo->obtenerLista($consulta);
                                    $total_ticketMultiple = mysql_num_rows($result);
                                    $checkins = 0;
                                    $checkouts = 0;
                                    while ($rs = mysql_fetch_array($result)) {
                                        if ($rs['Estatus'] == 1) {
                                            $checkins++;
                                        } else {
                                            if ($rs['Estatus'] == 2) {
                                                $checkouts++;
                                            }
                                        }
                                    }
                                    if ($checkins == 0) {
                                        array_push($Tickets, $idTicketMultiple);
                                        $consulta = ("UPDATE c_ticket SET EstadoDeTicket = 2 WHERE IdTicket = " . $idTicketMultiple . ";");
                                        $query = $catalogo->obtenerLista($consulta);
                                        if ($query != 1) {
                                            return -24; //No se actualizo el estado de ticket de Ticket Multiusuario al ser completados sus checkouts
                                        }
                                    }
                                }
                            }
                        }
                        if ($IdEstatus == 51) {
                            $consulta = "SELECT * FROM k_relacion_tickets WHERE IdTicketSimple = $IdTicket;";
                            $result = $catalogo->obtenerLista($consulta);
                            if (mysql_num_rows($result) > 0) {
                                return -15; //El ticket ya tiene relacion multiusuario
                            }

                            $consulta = "SELECT ct.EstadoDeTicket, kt.* FROM `k_tecnicoticket` kt LEFT JOIN c_ticket ct ON kt.IdTicket=ct.IdTicket
                                     WHERE ct.EstadoDeTicket=3 AND ct.TipoReporte=15 AND Activo=1 AND kt.IdUsuario = $IdTecnico;";
                            $result = $catalogo->obtenerLista($consulta); //Comprobar si el tecnico esta relacinado con ticket multiusuario
                            if (mysql_num_rows($result) > 0) {
                                $rs = mysql_fetch_array($result);
                                $IdTicketMulti = $rs['IdTicket'];
                                $consulta = "SELECT cv.Capacidad FROM c_vehiculo cv LEFT JOIN c_domicilio_usturno cdu ON cdu.IdVehiculo=cv.IdVehiculo WHERE cdu.IdUsuario = $IdTecnico;";
                                $result = $catalogo->obtenerLista($consulta);
                                $rs = mysql_fetch_array($result);
                                if ($rs['Capacidad'] != null && !empty($rs['Capacidad'])) {
                                    $capacidadV = $rs['Capacidad'];
                                } else {
                                    $capacidadV = 3;
                                }

                                $consulta = "SELECT * FROM k_relacion_tickets WHERE IdTicketMultiple=$IdTicketMulti;";
                                $result = $catalogo->obtenerLista($consulta);
                                $total_ticketM = mysql_num_rows($result);
                                if ($total_ticketM == $capacidadV) {
                                    return -70; //Responde con 70 si se ha cubierto con la capacidad
                                }
                                $checkins = 0;
                                $checkouts = 0;
                                while ($rs = mysql_fetch_array($result)) {
                                    if ($rs['Estatus'] == 1) {
                                        $checkins++;
                                    } else {
                                        if ($rs['Estatus'] == 2) {
                                            $checkouts++;
                                        }
                                    }
                                }
                                if ($checkouts > 0) {
                                    return -30; //No es posible hacer un checkin debido a que ya se encuentra un checkout de un ticket en el multiusuario
                                } else {
                                    $ticketS = new Ticket();
                                    $ticketS->setEmpresa($empresa);
                                    $ticketS->setUsuarioCreacion($usuario);
                                    $ticketS->setUsuarioUltimaModificacion($usuario);
                                    $ticketS->setPantalla("WS NuevaNota para Asignar Tecnico");
                                    $ticketS->setIdTicket($IdTicket);
                                    if (!$ticketS->crearNota($user_obj->getNombre() . " " . $user_obj->getPaterno() . " " . $user_obj->getMaterno(), "Ticket Multiusuario " . $IdTicketMulti . "")) {
                                        return -6; //No se pudo crear la nota de asignacion
                                    } else {
                                        $consulta = "INSERT INTO k_relacion_tickets(IdTicketMultiple,IdTicketSimple,Estatus,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                                                            VALUES(" . $IdTicketMulti . "," . $IdTicket . ",1,'" . $usuario . "',now(),'" . $usuario . "',now(),'WS NuevaNota');";
                                        $idRelacionTicket = $catalogo->insertarRegistro($consulta);
                                        if ($idRelacionTicket != NULL && $idRelacionTicket != 0) {
                                            $consulta = "SELECT NoSerieEquipo, ModeloEquipo FROM `c_ticket` WHERE IdTicket = $IdTicket;";
                                            $result = $catalogo->obtenerLista($consulta);
                                            $rs = mysql_fetch_array($result);
                                            $claveEspEquipo = $rs['NoSerieEquipo'];
                                            $modeloE = $rs['ModeloEquipo'];
                                            $pedido = new Pedido();
                                            $pedido->setEmpresa($empresa);
                                            $pedido->setIdTicket($IdTicket);
                                            $pedido->setActivo(1);
                                            $pedido->setUsuarioCreacion($datos_ticket->getUsuarioCreacion());
                                            $pedido->setUsuarioUltimaModificacion($datos_ticket->getUsuarioUltimaModificacion());
                                            $pedido->setPantalla($datos_ticket->getPantalla());
                                            $pedido->setEstado("Validar WS NuevaNota Checkin con Multiusuario");
                                            $pedido->setClaveEspEquipo($claveEspEquipo);
                                            $pedido->setModelo($modeloE);
                                            $pedido->setTonerNegro(0);
                                            $pedido->setTonerCian(0);
                                            $pedido->setTonerMagenta(0);
                                            $pedido->setTonerAmarillo(0);
                                            $pedido->setIdLecturaTicket(0);
                                            if (!$pedido->newRegistro()) {
                                                return -17; //"<br/>Error:La relación multiusuario en c_pedido no se registró correctamente";
                                            }
                                            $TM = 2;
                                            //array_push($Tickets, $IdTicketMulti);
                                            array_push($Tickets, $IdTicket);
//                                        else {
//                                            //echo "<br/>La relación multiusuario con '". $claveEspEquipo ."' se registró correctamente";
//                                        }
                                        } else {
                                            return -21; //relacion ticket multiple y ticket simple no se realizó
                                        }
                                    }
                                }
                            } else {
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
                                VALUES(NOW(), '" . $datos_ticket->getUsuarioCreacion() . "', 3, 15,
                                     0,0,
                                     '" . $datos_ticket->getNombreCliente() . "','" . $datos_ticket->getClaveCentroCosto() . "','" . $datos_ticket->getClaveCliente() . "','" . $datos_ticket->getNombreCentroCosto() . "',
                                     '" . $datos_ticket->getNoSerieEquipo() . "','" . $datos_ticket->getModeloEquipo() . "',0,
                                     '" . $datos_ticket->getNombreResp() . "','" . $datos_ticket->getTelefono1Resp() . "',NULL,0,0,'" . $datos_ticket->getCelularResp() . "','" . $datos_ticket->getCorreoEResp() . "',NULL,NULL,
                                     NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,
                                     NULL,NULL,now(),  
                                     'Viaje Multiusuario',NULL,7,
                                     1,'" . $datos_ticket->getUsuarioCreacion() . "',NOW(),NOW(),'" . $datos_ticket->getUsuarioUltimaModificacion() . "','" . $datos_ticket->getPantalla() . "',
                                         1,NULL,0, 0, NULL);";
                                //print_r($consulta);
                                $catalogo = new Catalogo();
                                $catalogo->setEmpresa($empresa);
                                //print_r($consulta);
                                $idTicketM = $catalogo->insertarRegistro($consulta);
                                if ($idTicketM != NULL && $idTicketM != 0) {
                                    $obj = new Ticket();
                                    $obj->setEmpresa($empresa);
                                    $obj->setUsuarioCreacion($usuario);
                                    $obj->setUsuarioUltimaModificacion($usuario);
                                    $obj->setPantalla("WS AsignaTecnico");

                                    $obj->setIdTicket($idTicketM);
                                    if ($obj->asociarTicketTecnicoGeneral($IdTecnico, $IdPrioridad, $Duracion, $IdUnidadDuracion, $FechaHora)) {
                                        if (!$obj->crearNota($user_obj->getNombre() . " " . $user_obj->getPaterno() . " " . $user_obj->getMaterno(), "Ticket de tipo Multiusuario")) {
                                            return -60; //No se pudo crear la nota de asignacion multíusuario con tecnico
                                        }
                                        $obj->setIdTicket($IdTicket);
                                        if (!$obj->crearNota($user_obj->getNombre() . " " . $user_obj->getPaterno() . " " . $user_obj->getMaterno(), "Ticket Multiusuario " . $idTicketM . "")) {
                                            return -6; //No se pudo crear la nota de asignacion de ticket simple con ticket multiusuario
                                        } else {
                                            $consulta = "INSERT INTO k_relacion_tickets(IdTicketMultiple,IdTicketSimple,Estatus,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                                                            VALUES(" . $idTicketM . "," . $IdTicket . ",1,'" . $usuario . "',now(),'" . $usuario . "',now(),'WS NuevaNota');";
                                            $idRelacionTicket = $catalogo->insertarRegistro($consulta);
                                            if ($idRelacionTicket != NULL && $idRelacionTicket != 0) {
                                                for ($i = 0; $i < 2; $i++) {
                                                    if ($i == 0) {
                                                        $IDT = $idTicketM;
                                                    } else {
                                                        $IDT = $IdTicket;
                                                    }
                                                    $consulta = "SELECT NoSerieEquipo, ModeloEquipo FROM `c_ticket` WHERE IdTicket = $IdTicket;";
                                                    $result = $catalogo->obtenerLista($consulta);
                                                    $rs = mysql_fetch_array($result);
                                                    $claveEspEquipo = $rs['NoSerieEquipo'];
                                                    $modeloE = $rs['ModeloEquipo'];
                                                    $pedido = new Pedido();
                                                    $pedido->setEmpresa($empresa);
                                                    $pedido->setIdTicket($IDT);
                                                    $pedido->setActivo(1);
                                                    $pedido->setUsuarioCreacion($datos_ticket->getUsuarioCreacion());
                                                    $pedido->setUsuarioUltimaModificacion($datos_ticket->getUsuarioUltimaModificacion());
                                                    $pedido->setPantalla($datos_ticket->getPantalla());
                                                    $pedido->setEstado("Validar WS NuevaNota Checkin con Multiusuario");
                                                    $pedido->setClaveEspEquipo($claveEspEquipo);
                                                    $pedido->setModelo($modeloE);
                                                    $pedido->setTonerNegro(0);
                                                    $pedido->setTonerCian(0);
                                                    $pedido->setTonerMagenta(0);
                                                    $pedido->setTonerAmarillo(0);
                                                    $pedido->setIdLecturaTicket(0);
                                                    if (!$pedido->newRegistro()) {
                                                        return -17; //"<br/>Error:La relación multiusuario en c_pedido no se registró correctamente";
                                                    }
//                                                else {
//                                                    //echo "<br/>La relación multiusuario con '". $claveEspEquipo ."' se registró correctamente";
//                                                }
                                                }
                                                $TM = 2;
                                                array_push($Tickets, $idTicketM);
                                                array_push($Tickets, $IdTicket);
                                            } else {
                                                return -21; //relacion ticket multiple y ticket simple no se realizó
                                            }
                                        }
                                    } else {
                                        return -7; //No se pudo asociar el tecnico con el ticket
                                    }
                                    //return 1;
                                } else {
                                    return -12; //No se registro ticket Multiusuario
                                }
                            }
                        }
                    }
                } else {
                    return -10; //Responde -10 sí ticket no esta autorizado en plantillas o tickets/ o si es idEstatus = 16 el ticket podria ya estar en estatus checkout
                }
            } else {
                $consulta1 = "SELECT * FROM c_especial WHERE idTicket = $IdTicket;";
                $result1 = $catalogo->obtenerLista($consulta1);
                if (mysql_num_rows($result1) <= 0) {
                    return -19; //Ticket no se encuehtra registrado en Plantillas o Viajes Especiales
                }
                array_push($Tickets, $IdTicket);
            }
        } else {
            array_push($Tickets, $IdTicket);
        }

        $return1 = 0;
        for ($j = 0; $j < count($Tickets); $j++) {
            $IdTicket = $Tickets[$j];
            $user_obj = new Usuario();
            $user_obj->setEmpresa($empresa);
            $catalogo = new Catalogo();
            $catalogo->setEmpresa($empresa);

            $usuario = "NuevaNota WS";
            $pantalla = "NuevaNota WS";

            if ($user_obj->getRegistroById($resultadoLoggin)) {
                $usuario = $user_obj->getUsuario();
            }

            $descripion_extra = "";
            if (isset($Latitud) && isset($Longitud) && $Latitud != 0 && $Longitud != 0 && $IdEstatus == 51) {
                //$address = urlencode("$Latitud,$Longitud");
                $url = "http://maps.google.com/maps/api/geocode/json?address=" . $Latitud . "," . $Longitud . "&sensor=false";
                //$geocode=file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false');
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
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

            if ($Latitud == "0" && $Longitud == "0") {
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
            while ($rs = mysql_fetch_array($result)) {
                if ($Latitud == "0" && $Longitud == "0") {//Si se reciben coordenadas 0,0 se ponen las coordenadas del ticket                
                    $Latitud = $rs['Latitud'];
                    $Longitud = $rs['Longitud'];
                } else if (($IdEstatus == "51" || $IdEstatus == "16" || $IdEstatus == "14") && (float) $rs['Distancia'] > 0.5) {
                    $descripion_extra = "Se recibieron coordenadas a " . number_format($rs['Distancia'], 3) . " km";
                }
            }

            $validarUsuarioTicket = "SELECT IdTicket,IdUsuario FROM k_tecnicoticket WHERE IdTicket = " . $IdTicket;
            $result = $catalogo->obtenerLista($validarUsuarioTicket);

            if (mysql_num_rows($result) == 0) {//No está asignado a nadie
                if ($TM != 2) {
                    $ticket = new Ticket();
                    $ticket->setEmpresa($empresa);
                    $ticket->setIdTicket($IdTicket);
                    $ticket->setUsuarioCreacion($usuario);
                    $ticket->setUsuarioUltimaModificacion($usuario);
                    $ticket->setPantalla($pantalla);
                    if (!$ticket->asociarTicketTecnicoGeneral($resultadoLoggin, "", "", "", "")) {
                        return -7;
                    }
                }
            } else {
                while ($rs = mysql_fetch_array($result)) {
                    if ($rs['IdUsuario'] != $resultadoLoggin) {
                        return -5; //El usuario de la sesion es diferente al que está asignado al ticket
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

            if ($nota->newRegistro()) {
                $ubicacion->setIdNotaTicket($nota->getIdNota());
                if ($IdEstatus == 14 || $IdEstatus == 9) {
                    $ticket = new Ticket();
                    $ticket->setEmpresa($empresa);
                    $ticket->setIdTicket($IdTicket);
                    if (!$ticket->eliminarAsignaciones()) {
                        return -8; //no se puede desasociar el 
                    }
                }


                if ($ubicacion->newRegistro()) {
                    //return 1;
                    $return1++;
                } else {
                    return -4;
                }
            } else {
                return 0;
            }
        }
        if ($return1 == count($Tickets)) {
            return 1;
        }
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("nuevaNota", "urn:nuevaNota");
$server->register("insertaNota", array("Titulo" => "xsd:string", "Mensaje" => "xsd:string", "FotoCodificada" => "xsd:string", "NombreFoto" => "xsd:string", "Fecha" => "xsd:string", "MinutoDesfase" => "xsd:int", "IdEstatus" => "xsd:int", "IdTicket" => "xsd:int", "Latitud" => "xsd:float", "Longitud" => "xsd:float", "IdSession" => "xsd:string"), array("return" => "xsd:string"), "urn:nuevaNota", "urn:nuevaNota#insertaNota", "rpc", "encoded", "Inserta una nota de un ticket");
$server->service($HTTP_RAW_POST_DATA);
?>