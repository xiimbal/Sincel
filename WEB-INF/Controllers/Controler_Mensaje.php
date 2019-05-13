<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/AgregarNota.class.php");
include_once("../Classes/ViaticoTicket.class.php");
include_once("../Classes/Catalogo.class.php");
include_once("../Classes/ServiciosVE.class.php");
include_once("../Classes/TicketRelacion.class.php");
include_once("../Classes/TipoViatico.class.php");

$obj = new AgregarNota();
$tipoViatico = new TipoViatico();
$catalogo = new Catalogo();

//print_r($_POST);
//print_r($_GET);
if (isset($_POST['otraNota']) && $_POST['otraNota'] == true) {
    $ticketSelec = "";
    $tickets_correctos = array();
    $tickets_seleccionado = array();
    if (isset($_GET['IdTickets'])) {
        if (isset($_POST['ticket_mensaje2']) && $_POST['ticket_mensaje2'] != "" && $_POST['ticket_mensaje2'] != 0) {
            $ticketSelec = $_POST['ticket_mensaje2'];
        }
        if ($_GET['IdTickets'] != "") {
            $tickets_seleccionado = split(",", $_GET['IdTickets']);
            if ($ticketSelec != "") {
                if (!in_array($ticketSelec, $tickets_seleccionado)) {
                    array_push($tickets_seleccionado, $ticketSelec);
                }
            }
        } else {
            array_push($tickets_seleccionado, $ticketSelec);
        }
    }

    foreach ($tickets_seleccionado as $key => $idTicket) {        
//    $idTicket = $_POST['ticket_mensaje2'];
        $diagnostico = addslashes(str_replace("\r\n", " ", $_POST['mensaje_enviar2']));

        $obj->setIdTicket($idTicket);
        $obj->setFechaHora("");
        $obj->setDiagnosticoSolucion($diagnostico);
        $obj->setIdestatusAtencion($_POST['estatusN']);
        $obj->setShow(1);
        $obj->setActivo(1);
        $obj->setFechaHora($_POST['fecha'] . " " . $_POST['hora']);
        $obj->setUsuarioSolicitud($_SESSION['user']);
        $obj->setUsuarioCreacion($_SESSION['user']);
        $obj->setUsuarioModificacion($_SESSION['user']);
        $obj->setPantalla('Alta Mensaje');

        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") {
            echo "<br/><b>Se subió el archivo seleccionado</b><br/>";
            $rutaArchivo = "../../nota/uploads/" . $obj->getIdTicket() . "-" . $obj->getIdNotaTicket() . $_FILES['file']['name'];
            move_uploaded_file($_FILES['file']['tmp_name'], $rutaArchivo);
            $rutaArchivo = "../nota/uploads/" . $obj->getIdTicket() . "-" . $obj->getIdNotaTicket() . $_FILES['file']['name'];
            $obj->setPathImagen($rutaArchivo);
        }

        if ($obj->newRegistro2()) {
            if ($tipoViatico->tieneEstadoViatico ($_POST['estatusN']) || $_POST['estatusN'] == "275" || $_POST['estatusN'] == "276") {//Aunque no tenga estado de viatico, se tiene que actualizar
                $viatico = new ViaticoTicket();                    
                $viatico->setIdTicket($idTicket);
                /*Obtenemos el tipo de viatico*/
                if (isset($_POST['tipo_viatico']) && !empty($_POST['tipo_viatico'])) {                    
                    $viatico->setIdTipoViatico($_POST['tipo_viatico']);                    
                }else{
                    if($tipoViatico->getIdTipoViatico() != ""){
                        $viatico->setIdTipoViatico($tipoViatico->getIdTipoViatico());
                    }else{
                        $viatico->setIdTipoViatico(4);
                    }
                }                
                
                $query1 = $catalogo->obtenerLista("SELECT IdUsuario FROM k_tecnicoticket WHERE IdTicket= " . $idTicket . " ;");
                if (mysql_num_rows($query1) > 0) {
                    $rs1 = mysql_fetch_array($query1);
                    $viatico->setIdUsuario($rs1['IdUsuario']);
                } else {
                    $viatico->setIdUsuario($_SESSION['idUsuario']);
                }

                $viatico->setFecha($fecha);
                if($_POST['estatusN'] == '275'){//Kilometros por servicio
                    $viatico->setCosto($_POST['km']);                    
                    $update = "UPDATE c_notaticket SET Km =  ".$_POST['km']." WHERE IdNotaTicket = " . $obj->getIdNotaTicket();
                    $rsUpdate = $catalogo->obtenerLista($update);
                    if ($rsUpdate) {
                        echo "Se han almacenado los Km recorridos para el Ticket <b>" . $idTicket ."</b><br/>";
                    }
                }else if($_POST['estatusN'] == "276"){//Tiempo de espera
                    $viatico->setCosto($_POST['tiempo_esperaR']);
                    $update = "UPDATE c_notaticket SET TiempoEsperaReal =  ".$_POST['tiempo_esperaR'].", TiempoEsperaMenor =  ".$_POST['tiempo_esperaM']." WHERE IdNotaTicket = " . $obj->getIdNotaTicket();
                    $rsUpdate = $catalogo->obtenerLista($update);
                    if ($rsUpdate) {
                        echo "Se ha almacenado tiempo de espera para el Ticket <b>" . $idTicket ."</b><br/>";
                    }
                }else{//Otros viaticos
                    if (isset($_POST['monto'])) {
                        $viatico->setCosto($_POST['monto']);
                    }else{
                        $viatico->setCosto(0);
                    }
                }
                $viatico->setComentario($diagnostico);                
                if(isset($_POST['cobrar']) && $_POST['cobrar'] == "1"){
                    $viatico->setCobrarSiNo(1); 
                }else{
                    $viatico->setCobrarSiNo(0);
                }

                if(isset($_POST['pagar']) && $_POST['pagar'] == "1"){
                    $viatico->setPagarSiNo(1); 
                }else{
                    $viatico->setPagarSiNo(0); 
                }
                $viatico->setUsuarioCreacion($_SESSION['user']);
                $viatico->setUsuarioUltimaModificacion($_SESSION['user']);
                $viatico->setPantalla("Controler_AgregarNota"); 
                if($viatico->insertarNuevoViatico($obj->getIdNotaTicket())){
                    echo "<br/>Se registro viático del Ticket  <b>" . $obj->getIdTicket() . "</b><br/>";
                }else{
                    echo "<br/>No se pudo registrar el costo del viatico<br/>";
                }
            } /*else if ($_POST['estatusN'] == '275') {
                $serviciosVE = new ServiciosVE();
                $serviciosVE->setIdTicket($idTicket);
                $serviciosVE->setCantidad($_POST['km']);
                $serviciosVE->setFecha($fecha);
                $serviciosVE->buscarServicioByEstatusNota(275);
                $serviciosVE->setUsuarioCreacion($_SESSION['user']);
                $serviciosVE->setUsuarioUltimaModificacion($_SESSION['user']);
                $serviciosVE->setPantalla("Controler_AgregarNota");
                $serviciosVE->newRegistroKServicio();
                
                $update = "UPDATE c_notaticket SET Km =  " . $_POST['km'] . " WHERE IdNotaTicket = " . $obj->getIdNotaTicket();
                $rsUpdate = $catalogo->obtenerLista($update);
                if ($rsUpdate) {
                    echo "Se han almacenado los Km recorridos para el Servicio <b>" . $idTicket . "</b><br/>";
                }                                       
            } else if ($_POST['estatusN'] == '276') {                
                $serviciosVE = new ServiciosVE();
                $serviciosVE->setIdTicket($idTicket);
                $serviciosVE->setCantidad($_POST['tiempo_esperaR']);
                $serviciosVE->setFecha($fecha);
                $serviciosVE->buscarServicioByEstatusNota(276);
                $serviciosVE->setUsuarioCreacion($_SESSION['user']);
                $serviciosVE->setUsuarioUltimaModificacion($_SESSION['user']);
                $serviciosVE->setPantalla("Controler_AgregarNota");
                $serviciosVE->newRegistroKServicio();
                
                $update = "UPDATE c_notaticket SET TiempoEsperaReal =  " . $_POST['tiempo_esperaR'] . ", TiempoEsperaMenor =  " . $_POST['tiempo_esperaM'] . " WHERE IdNotaTicket = " . $obj->getIdNotaTicket();
                $rsUpdate = $catalogo->obtenerLista($update);
                if ($rsUpdate) {
                    echo "Se ha almacenado tiempo de espera para el Servicio <b>" . $idTicket . "</b><br/>";
                }
            }*/ else if ($_POST['estatusN'] == '277') {
                $update = "UPDATE c_notaticket SET NoBoleto =  " . $_POST['no_boleto'] . " WHERE IdNotaTicket = " . $obj->getIdNotaTicket();
                $rsUpdate = $catalogo->obtenerLista($update);
                if ($rsUpdate) {
                    echo "Se ha almacenado No. de Boleto para el Servicio <b>" . $idTicket . "</b><br/>";
                }
            } else if ($_POST['estatusN'] == '16') {
                if ($obj->EditEstadoTicket($idTicket, "2")){
                    $ticket_relacion = new TicketRelacion();
                    $ticket_relacion->setIdTicketMultiple($obj->getIdTicket());
                    $result = $ticket_relacion->getRegistroTicketMultiple();
                    while($rs = mysql_fetch_array($result)){
                        $obj->setIdTicket($rs['IdTicketSimple']);
                        if(!$obj->newRegistro2()){
                            echo "<br/>No se pudo cerrar el ticket ".$obj->getIdTicket().", registrado como escala del ticket ".$ticket_relacion->getIdTicketMultiple();
                        }
                    }
                    echo "El ticket se cerró correctamente<br/>";
                }else{
                    echo "El ticket no se cerró exitosamente<br/>";
                }
            }else if ($_POST['estatusN'] == '59') {
                if ($obj->EditEstadoTicket($_POST['idTicket'], "4"))
                    echo "El ticket se canceló correctamente<br/>";
                else
                    echo "El ticket no se canceló exitosamente<br/>";
            } else if ($_POST['estatusN'] == '278') {
                if ($_POST['km'] != "") {
                    $update = "UPDATE c_notaticket SET Km =  " . $_POST['km'] . " WHERE IdNotaTicket = " . $obj->getIdNotaTicket();
                    $rsUpdate = $catalogo->obtenerLista($update);
                    if ($rsUpdate) {
                        echo "Se han almacenado los Km recorridos para el Servicio <b>" . $idTicket . "</b><br/>";
                    }
                }
                if ($_POST['tiempo_esperaR'] != "") {
                    if ($_POST['tiempo_esperaM'] != "") {
                        $update = "UPDATE c_notaticket SET TiempoEsperaReal =  " . $_POST['tiempo_esperaR'] . ", TiempoEsperaMenor =  " . $_POST['tiempo_esperaM'] . " WHERE IdNotaTicket = " . $obj->getIdNotaTicket();
                        $rsUpdate = $catalogo->obtenerLista($update);
                        if ($rsUpdate) {
                            echo "Se ha almacenado tiempo de espera para el Servicio <b>" . $idTicket . "</b><br/>";
                        }
                    } else {
                        $update = "UPDATE c_notaticket SET TiempoEsperaReal =  " . $_POST['tiempo_esperaR'] . ", TiempoEsperaMenor = 0 WHERE IdNotaTicket = " . $obj->getIdNotaTicket();
                        $rsUpdate = $catalogo->obtenerLista($update);
                        if ($rsUpdate) {
                            echo "Se ha almacenado tiempo de espera para el Servicio <b>" . $idTicket . "</b><br/>";
                        }
                    }
                }
                if ($_POST['no_boleto'] != "") {
                    $update = "UPDATE c_notaticket SET NoBoleto =  " . $_POST['no_boleto'] . " WHERE IdNotaTicket = " . $obj->getIdNotaTicket();
                    $rsUpdate = $catalogo->obtenerLista($update);
                    if ($rsUpdate) {
                        echo "Se ha almacenado No. de Boleto para el Servicio <b>" . $idTicket . "</b><br/>";
                    }
                }
            }
//            echo "<br/>El mensaje se envió correctamente";
            array_push($tickets_correctos, $idTicket);
        } else {
            echo "<br/>Error: el mensaje del Servicio <b>" . $idTicket . "</b> no se pudo enviar correctamente";
        }
    }

    if (!empty($tickets_correctos)) {
        echo "<br/> El mensaje para Servicio(s) <b>" . implode(", ", $tickets_correctos) . "</b> se ha enviado";
    }
} else {
    $idTicket = $_POST['idTicket'];
    $diagnostico = addslashes(str_replace("\r\n", " ", $_POST['diagnostico']));
    if (isset($_POST['estatusNota']) && $_POST['estatusNota'] != "") {
        $obj->setIdestatusAtencion($_POST['estatusNota']);
        $obj->setShow(1);
        $obj->setActivo(1);
    } else {
        $obj->setIdestatusAtencion(92);
        $obj->setShow(0);
        $obj->setActivo(0);
    }

    $obj->setIdTicket($idTicket);
    $obj->setFechaHora("");
    $obj->setDiagnosticoSolucion($diagnostico);
    $obj->setUsuarioSolicitud($_SESSION['user']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Alta Mensaje');

    if ($obj->newRegistro()) {
        echo "<br/>El mensaje se envió correctamente";
    } else {
        echo "<br/>Error: el mensaje no se pudo enviar correctamente";
    }
}