<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['tecnico']) || !isset($_POST['idTicket']) || !isset($_POST['IdPrioridad'])) {
    header("Location: ../../index.php");
}

include_once("../Classes/Pedido.class.php");
include_once("../Classes/Ticket.class.php");
include_once("../Classes/NotaTicket.class.php");
include_once("../Classes/Usuario.class.php");
include_once("../Classes/Catalogo.class.php");

$ticket = new Ticket();
$usuario = new Usuario();
$catalogo = new Catalogo();

$usuario->getRegistroById($_POST['tecnico']);

$ticket->setUsuarioUltimaModificacion($_SESSION['user']);
$ticket->setUsuarioCreacion($_SESSION['user']);
$ticket->setPantalla('PHP Asigna Ticket Mapa');
$tipo = "";

$tickets_seleccionado = split(",", $_POST['idTicket']);
$prioridades = split(",", $_POST['IdPrioridad']);
$duraciones = split(",", $_POST['Duracion']);
$unidades = split(",", $_POST['IdUnidadDuracion']);
$fechas = split(",", $_POST['FechaHora']);

$tickets_correctos = array();
$Usuarios = array(); //Loyalty
$idTicketUs = ""; //Loyalty
$Desaforos = 0;
foreach ($tickets_seleccionado as $key => $IdTicket) { //$tickets_seleccionado as $key => $IdTicket) {
    $fechaTicket = str_replace("/", "-", $fechas[$key]);
    $consulta1 = "SELECT cp.TipoEvento, cp.idPlantilla FROM c_plantilla cp INNER JOIN k_plantilla kp ON cp.idPlantilla=kp.idPlantilla
                 INNER JOIN k_plantilla_asistencia kpa ON kp.idK_Plantilla=kpa.idK_Plantilla WHERE kpa.IdTicket = $IdTicket;";
    $result1 = $catalogo->obtenerLista($consulta1);
    if (mysql_num_rows($result1) > 0) {
        $rs1 = mysql_fetch_array($result1);
        $tipoEvento = $rs1['TipoEvento'];
        if ($Desaforos == 0) {
            $consulta = "SELECT IdTicket FROM `k_tecnicoticket` WHERE IdUsuario = " . $usuario->getId() . " AND FechaHoraInicio = '$fechaTicket' AND IdTicket <> $IdTicket;";
            $result = $catalogo->obtenerLista($consulta);
            if (mysql_num_rows($result) > 0) {
                while ($rs = mysql_fetch_array($result)) {
                    echo "<br/>Error: no se puede asignar el ticket $IdTicket porque " . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() .
                    " ya tiene asignado el ticket " . $rs['IdTicket'] . " para la fecha $fechaTicket";
                    continue;
                }
                continue;
            }
        }
        if ($tipoEvento == 2) {
            $Desaforos++;
            $ticket->setIdTicket($IdTicket);
            $ticket->eliminarAsignaciones(); //Eliminamos asignaciones anteriores    
            if ($ticket->asociarTicketTecnicoGeneral($usuario->getId(), $prioridades[$key], $duraciones[$key], $unidades[$key], $fechaTicket)) {
                if ($ticket->crearNota($usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " para atender en fecha $fechaTicket", $tipo)) {
                    array_push($tickets_correctos, $IdTicket);
                    $idTicketUs = $IdTicket; //Loyalty
                    //echo "<br/>El ticket <b>$IdTicket</b> fue asignado al técnico correctamente";
                }
            } else {
                echo "<br/>No se pudo asignar el ticket <b>$IdTicket</b>";
            }
        } else {
            $ticket->setIdTicket($IdTicket);
            $ticket->eliminarAsignaciones(); //Eliminamos asignaciones anteriores    
            if ($ticket->asociarTicketTecnicoGeneral($usuario->getId(), $prioridades[$key], $duraciones[$key], $unidades[$key], $fechaTicket)) {
                if ($ticket->crearNota($usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " para atender en fecha $fechaTicket", $tipo)) {
                    array_push($tickets_correctos, $IdTicket);
                    $idTicketUs = $IdTicket; //Loyalty
                    //echo "<br/>El ticket <b>$IdTicket</b> fue asignado al técnico correctamente";
                }
            } else {
                echo "<br/>No se pudo asignar el ticket <b>$IdTicket</b>";
            }
        }
    } else {
        $consulta = "SELECT IdTicket FROM `k_tecnicoticket` WHERE IdUsuario = " . $usuario->getId() . " AND FechaHoraInicio = '$fechaTicket' AND IdTicket <> $IdTicket;";
        $result = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($result) > 0) {
            while ($rs = mysql_fetch_array($result)) {
                echo "<br/>Error: no se puede asignar el ticket $IdTicket porque " . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() .
                " ya tiene asignado el ticket " . $rs['IdTicket'] . " para la fecha $fechaTicket";
                continue;
            }
            continue;
        }

        $ticket->setIdTicket($IdTicket);
        $ticket->eliminarAsignaciones(); //Eliminamos asignaciones anteriores    
        if ($ticket->asociarTicketTecnicoGeneral($usuario->getId(), $prioridades[$key], $duraciones[$key], $unidades[$key], $fechaTicket)) {
            if ($ticket->crearNota($usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " para atender en fecha $fechaTicket", $tipo)) {
                array_push($tickets_correctos, $IdTicket);
                $idTicketUs = $IdTicket; //Loyalty
                //echo "<br/>El ticket <b>$IdTicket</b> fue asignado al técnico correctamente";
            }
        } else {
            echo "<br/>No se pudo asignar el ticket <b>$IdTicket</b>";
        }
    }
}

if (!empty($tickets_correctos)) {
    echo "<br/> Los Tickets <b>" . implode(", ", $tickets_correctos) . "</b> se asignaron correctamente a <b>" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . "</b>";

    $consulta = "SELECT kp.idPlantilla FROM k_plantilla AS kp LEFT JOIN k_plantilla_asistencia AS kpa ON 
                 kpa.idK_Plantilla=kp.idK_Plantilla WHERE IdTicket = " . $idTicketUs . " ;";
    $result = $catalogo->obtenerLista($consulta);
    $rsp = mysql_fetch_array($result);

    if ($rsp['idPlantilla'] !== "" && mysql_num_rows($result) > 0) {
        
        
        $pedido = new Pedido();
        $datos_ticket = new Ticket();
        $datos_ticket->getTicketByID($idTicketUs); //$tickets_correctos[0]);
        //$datos_ticket->getNoSerieEquipo(); "Multiusuario con ".$usuario->getNombre()." ".$usuario->getPaterno()." ".$usuario->getMaterno().""; 

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
                                VALUES(NOW(), '" . $ticket->getUsuarioCreacion() . "', 3, 15,
                                     0,0,
                                     '" . $datos_ticket->getNombreCliente() . "','" . $datos_ticket->getClaveCentroCosto() . "','" . $datos_ticket->getClaveCliente() . "','" . $datos_ticket->getNombreCentroCosto() . "',
                                     '" . $datos_ticket->getNoSerieEquipo() . "','" . $datos_ticket->getModeloEquipo() . "',0,
                                     '" . $datos_ticket->getNombreResp() . "','" . $datos_ticket->getTelefono1Resp() . "',NULL,0,0,'" . $datos_ticket->getCelularResp() . "','" . $datos_ticket->getCorreoEResp() . "',NULL,NULL,
                                     NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,
                                     NULL,NULL,now(),  
                                     'Viaje Multiusuario',NULL,7,
                                     1,'" . $ticket->getUsuarioCreacion() . "',NOW(),NOW(),'" . $ticket->getUsuarioUltimaModificacion() . "','" . $ticket->getPantalla() . "',
                                         1,NULL,0, 0, NULL);";
        //print_r($consulta);
        $catalogo = new Catalogo();
        //print_r($consulta);
        $idTicketM = $catalogo->insertarRegistro($consulta);
        if ($idTicketM != NULL && $idTicketM != 0) {
            echo "<br/>El Ticket multiusuario se generó correctamente";
            foreach ($tickets_seleccionado as $key => $IdTicketUsuario) {
                $consulta = "SELECT NoSerieEquipo, ModeloEquipo FROM `c_ticket` WHERE IdTicket = $IdTicketUsuario;";
                $result = $catalogo->obtenerLista($consulta);
                $rs = mysql_fetch_array($result);
                $claveEspEquipo = $rs['NoSerieEquipo'];
                $modeloE = $rs['ModeloEquipo'];
                $pedido->setIdTicket($idTicketM);
                $pedido->setActivo(1);
                $pedido->setUsuarioCreacion($_SESSION['user']);
                $pedido->setUsuarioUltimaModificacion($_SESSION['user']);
                $pedido->setPantalla($ticket->getPantalla());
                $pedido->setEstado("Validar Multiusuario");
                $pedido->setClaveEspEquipo($claveEspEquipo);
                $pedido->setModelo($modeloE);
                $pedido->setTonerNegro(0);
                $pedido->setTonerCian(0);
                $pedido->setTonerMagenta(0);
                $pedido->setTonerAmarillo(0);
                $pedido->setIdLecturaTicket(0);
                if (!$pedido->newRegistro()) {
                    echo ""; //"<br/>Error:La relación multiusuario no se registró correctamente";
                } else {
                    array_push($Usuarios, $claveEspEquipo);
                    //echo "<br/>La relación multiusuario con '". $claveEspEquipo ."' se registró correctamente";
                }
            }
            if (!empty($Usuarios)) {
                echo " con <b>" . implode(", ", $Usuarios) . "</b> asignados al Ticket <b>" . $idTicketM . "</b>";
//                                 $updatePlantilla = $catalogo->obtenerLista("UPDATE `c_plantilla` SET idTicket = $idTicketM 
//                                        WHERE idPlantilla = ;");
//                                    if ($update == "1") {
//                                        return true;
//                                    }
            }
        } else {
            echo "<br/>El Ticket multiusuario NO se generó correctamente";
        }
    }
}
?>
