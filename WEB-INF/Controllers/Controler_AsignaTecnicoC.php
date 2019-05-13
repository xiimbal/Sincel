<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['tecnico']) || !isset($_POST['idPlantilla']) || !isset($_POST['idArea'])) {
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
$tipo = "";
print_r($_POST);
$usuario->getRegistroById($_POST['tecnico']);


$tipo = "";

$idPlantilla = $_POST['idPlantilla'];
$idArea = $_POST['idArea'];
$fechaPost = $_POST['FechaHora'];
$fecha = str_replace("/", "-", $fechaPost);
$idTicketP = $_POST['idTicket'];
if (isset($_POST['idPlantilla']) && $_POST['idPlantilla'] !== "") {
    $continue = 0;
    $consulta = "SELECT IdTicket FROM `k_tecnicoticket` WHERE IdUsuario = " . $usuario->getId() . " AND FechaHoraInicio = '$fecha';";
    $result = $catalogo->obtenerLista($consulta);
    if (mysql_num_rows($result) > 0) {
        while ($rs = mysql_fetch_array($result)) {
            echo "<br/>Error: no se puede asignar la palntilla $idPlantilla porque " . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() .
            " ya tiene asignado el ticket " . $rs['IdTicket'] . " para la fecha $fecha";
            continue;
            $continue = 1;
        }
    }
    if ($continue == 0) {
        $consulta = "SELECT Capacidad FROM `c_vehiculo` cv INNER JOIN c_domicilio_usturno cd ON cd.IdVehiculo=cv.IdVehiculo WHERE IdUsuario = " . $usuario->getId() . ";";
        $result = $catalogo->obtenerLista($consulta);
        $capacidad = mysql_fetch_array($result);
        if ($capacidad != "") {
            $empleados = $capacidad;
        } else {
            $empleados = 3;
        }

        $consulta1 = "SELECT kpa.IdTicket, cp.TipoEvento, cp.idPlantilla FROM c_plantilla cp INNER JOIN k_plantilla kp ON cp.idPlantilla=kp.idPlantilla
                 INNER JOIN k_plantilla_asistencia kpa ON kp.idK_Plantilla=kpa.idK_Plantilla INNER JOIN c_ticket ct ON ct.IdTicket=kpa.IdTicket
                 WHERE cp.idPlantilla=" . $idPlantilla . " AND ct.AreaAtencion=" . $idArea . " AND ct.EstadoDeTicket=3 AND cp.Estatus=2 AND ct.EstatusAsigna=0;";
        $result1 = $catalogo->obtenerLista($consulta1);
        $w = 0;
        $updates = 0;
        ;
        while ($rs1 = mysql_fetch_array($result1)) {
            if ($w == 0) {
                $idTicket2 = $rs1['IdTicket'];
            }
            if ($w < $empleados) {
                $idTicketW = $rs1['IdTicket'];
                $update = $catalogo->obtenerLista("UPDATE `c_ticket` SET EstatusAsigna = 1 WHERE IdTicket = $idTicketW;");
                if ($update == "1") {
                    $updates++;
                } else {
                    echo "Asignación sin éxito";
                }
            }

            $w++;
        }
        if ($fecha == "undefined") {
            $fecha = "";
        }

        $ticket->getTicketByID($idTicket2);
        echo "El Ticket es: " . $idTicket2 . ".....";
        $ticket->setUsuario($_SESSION['user']);
        $ticket->setTipoReporte(200);
        $ticket->setDescripcionReporte("Cita de Usuarios por Cuadrante");
        $ticket->setActualizarInfoEstatCobra(0);
        $ticket->setActualizarInfoCliente(0);
        $ticket->setActualizarInfoEquipo(0);
        $ticket->setUbicacionEmp("NULL");
        $ticket->setActivo(1);
        if ($ticket->getObservacionAdicional() == "") {
            $ticket->setObservacionAdicional("NULL");
        }
        if ($ticket->getCorreoEAtenc() == "") {
            $ticket->setCorreoEAtenc("NULL");
        }
        if ($ticket->getNombreAtenc() == "") {
            $ticket->setNombreAtenc("NULL");
        }
        $ticket->setUsuarioUltimaModificacion($_SESSION['user']);
        $ticket->setUsuarioCreacion($_SESSION['user']);
        $ticket->setPantalla('PHP Asigna Ticket Mapa por Cuadrantes');
        $mensaje = "a máximo $empleados";
        if ($empleados > $w) {
            $mensaje = "a $w";
        }

        if ($ticket->newRegistroCompleto()) {
            $idTicketCita = $ticket->getIdTicket();
            $ticket->setIdTicket($idTicketCita);
            if ($ticket->asociarTicketTecnicoGeneral($usuario->getId(), "NULL", "NULL", "NULL", $fecha)) {
                if ($ticket->crearNota($usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " para atender $mensaje de $w empleado(s) en fecha $fecha", $tipo)) {
                    echo "<br/>Se genero el aviso a chofer correctamente";
                }
            } else {
                echo "<br/>No se pudo asignar la plantilla con el chofer";
            }
        } else {
            echo "<br/>No se pudo generar aviso para chofer";
        }
    }
} else {
    $continue = 0;
    $consulta = "SELECT IdTicket FROM `k_tecnicoticket` WHERE IdUsuario = " . $usuario->getId() . " AND FechaHoraInicio = '$fecha' AND IdTicket <> $idTicketP;";
    $result = $catalogo->obtenerLista($consulta);
    if (mysql_num_rows($result) > 0) {
        while ($rs = mysql_fetch_array($result)) {
            echo "<br/>Error: no se puede asignar el ticket $idTicketP porque " . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() .
            " ya tiene asignado el ticket " . $rs['IdTicket'] . " para la fecha $fechaTicket";
            continue;
            $continue = 1;
        }
    }
    if ($continue == 0) {
        $ticket->setIdTicket($idTicketP);
        $ticket->eliminarAsignaciones(); //Eliminamos asignaciones anteriores    
        if ($ticket->asociarTicketTecnicoGeneral($usuario->getId(), "NULL", "NULL", "NULL", $fecha)) {
            if ($ticket->crearNota($usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " para atender en fecha $fecha", $tipo)) {
                echo "<br/> El Ticket <b>" . $idTicketP . "</b> se asignó correctamente a <b>" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . "</b>";
            }
        } else {
            echo "<br/>No se pudo asignar el ticket <b>$idTicketP</b>";
        }
    }
}
