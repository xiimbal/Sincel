<?php

//session_start();
if (!isset($_GET['clv']) || !isset($_GET['idTicket']) || !isset($_GET['tipo']) || !isset($_GET['idMail'])) {
    header("Location: index.php");
}

if (!isset($_GET['uguid'])) {
    /* echo "La liga no está completa, favor de comunicarlo a soporte.";
      return; */
    $empresa = 1; //Temporalmente, se toma por default la empresa 1, que es genesis.
} else {
    $empresa = $_GET['uguid'];
}

include_once("WEB-INF/Classes/SolicitudToner.class.php");
include_once("WEB-INF/Classes/TicketAuxiliar.class.php");
include_once("WEB-INF/Classes/NotaTicket.class.php");
include_once("WEB-INF/Classes/Catalogo.class.php");

$obj = new SolicitudToner();
$ticketA = new TicketAuxiliar();
$catalogo = new Catalogo();
$notaTicket = new NotaTicket();
$catalogo->setEmpresa($empresa);
$obj->setEmpresa($catalogo->getEmpresa());
$ticketA->setEmpresa($catalogo->getEmpresa());
$notaTicket->setEmpresa($catalogo->getEmpresa());
$estatusMensaje = "";
$query = $catalogo->obtenerLista("SELECT * FROM c_mailpedidotoner mpt WHERE mpt.IdMail='" . $_GET['idMail'] . "'");
while ($rs = mysql_fetch_array($query)) {
    $estatusMensaje = $rs['Contestada'];
}

if ($estatusMensaje != 1) {//comprobar estatus de mensaje
    if ($_GET['tipo'] == "1") {//crear nota de aceptacion de pedido de toner nota
        $idTicket = $_GET['idTicket'];
        // if ($obj->getNotaByIdTicket($idTicket)) {
        $obj->setNotaAnterior($_GET['idNota']);
        $obj->setUsuarioCreacion("sistema");
        $obj->setUsuarioModificacion("sistema");
        $obj->setIdEstadoNota(65);
        $obj->setMostrarCliente("1");
        $obj->setPantalla("Validacion de pedido de toner");
        if ($obj->newNotaSolicitudToner()) {
            if ($obj->copyTonerNota()) {
                if ($obj->EditarEstatusMail($_GET['tipo'], $_GET['idMail']))
                    echo "La validaci&oacute;n se gener&oacute; exitosamente";
            }
        } else {
            echo "La validaci&oacute;n no se gener&oacute;";
        }
        //}
    } else if ($_GET['tipo'] == "3") {//crear nota de rechazo de toner  
        $idTicket = $_GET['idTicket'];
        $obj->setNotaAnterior($_GET['idNota']);
        $obj->setUsuarioCreacion("sistema");
        $obj->setUsuarioModificacion("sistema");
        $obj->setIdEstadoNota(59);
        $obj->setMostrarCliente("0");
        $obj->setPantalla("Validacion de pedido de toner");
        if ($obj->newNotaSolicitudToner()) {
            echo "La solicitud de toner se cancel&oacute; exitosamente";
        }
    } else if ($_GET['tipo'] == "11") {//continua flujo de cambio de toner 
        $idTicket = $_GET['idTicket'];
        $obj->setUsuarioCreacion("sistema");
        $obj->setUsuarioModificacion("sistema");
        $obj->setPantalla("Validacion de pedido de toner");
        if ($ticketA->getRegistroByIdTicket($idTicket)) {
            $obj->EditarEstatusMail(1, $_GET['idMail']);
            $_POST['IdTicket'] = $idTicket;
            $_POST['autorizar'] = true;
            $_POST['empresa'] = $empresa;
            $_POST['form'] = $ticketA->getForm();
            $_POST['idUsuario'] = $_GET['idUs'];
            include_once ("WEB-INF/Controllers/Controler_ReportarCambioToner.php");
        }
    } else if ($_GET['tipo'] == "13") {//detiene flujo de cambio de toner
        $idTicket = $_GET['idTicket'];
        $notaTicket->setIdTicket($idTicket);
        $notaTicket->setDiagnostico("Solicitud de cambio de toners:");
        $notaTicket->setIdEstatus(67);
        $notaTicket->setUsuarioSolicitud($user);
        $notaTicket->setMostrarCliente(1);
        $notaTicket->setActivo(1);
        $notaTicket->setUsuarioCreacion($user);
        $notaTicket->setUsuarioModificacion($user);
        $notaTicket->setPantalla("Solicitud de toner del mini almacén");
        if ($notaTicket->newRegistro()) {//agregar nota refaccion
            $obj->setNotaAnterior($notaTicket->getIdNota());
            $obj->setDescripcion("CANCELADO PORQUE NO CUMPLE CON EL RENDIMIENTO");
            $obj->setUsuarioCreacion("sistema");
            $obj->setUsuarioModificacion("sistema");
            $obj->setIdEstadoNota(59);
            $obj->setMostrarCliente("0");
            $obj->setPantalla("Validacion de pedido de toner");
            if ($obj->newNotaSolicitudToner()) {
                echo "La solicitud de cambio de toner se cancel&oacute; exitosamente";
            }
        }
    }
} else {
    echo "La solicitud ya fue validada";
}
?>
