<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/Ticket.class.php");
include_once("../Classes/Incidencia.class.php");
include_once("../Classes/Pedido.class.php");
include_once("../Classes/LecturaTicket.class.php");
include_once("../Classes/NotaTicket.class.php");
include_once("../Classes/Usuario.class.php");
include_once("../Classes/NotaRefaccion.class.php");
include_once("../Classes/Catalogo.class.php");
include_once("../Classes/Contacto.class.php");
include_once("../Classes/SolicitudToner.class.php");
include_once("../Classes/ParametroGlobal.class.php");
include_once("../Classes/Mail.class.php");
include_once("../Classes/Parametros.class.php");
include_once("../Classes/Almacen.class.php");
include_once("../Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("../Classes/Componente.class.php");

$catalogo = new Catalogo();
$obj = new Ticket();
$pedido = new Pedido();
$lecturaTicket = new LecturaTicket();
$notaTicket = new NotaTicket();
$notaRefaccion = new NotaRefaccion();
$contacto = new Contacto();
$solicitudToner = new SolicitudToner();
$parametroGlobal = new ParametroGlobal();
$caracteristicas = new EquipoCaracteristicasFormatoServicio();
$mensajes1 = "";

if ($parametroGlobal->getRegistroById("8")) {
    $correo_emisor = ($parametroGlobal->getValor());
} else {
    $correo_emisor = ("scg-salida@scgenesis.mx");
}

$parametroSistema = new Parametros();
$url = "http://genesis2.techra.com.mx/genesis2/";
if ($parametroSistema->getRegistroById(8)) {
    $url = $parametroSistema->getDescripcion();
}

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$obj->setClaveCentroCosto($parametros['slcLocalidad']);
$obj->setClaveCliente($parametros['slcCliente']);
$obj->setNombreCentroCosto($parametros['nombreCC']);
$obj->setNombreCliente($parametros['nombreCliente']);
$obj->setActualizarInfoCliente(0);
$obj->setActualizarInfoEquipo(0);
$obj->setActualizarInfoEstatCobra(0);
if ($parametros['prioridad'] == 0) {
    $obj->setPrioridad("NULL");
} else {
    $obj->setPrioridad($parametros['prioridad']);
}
$obj->setUsuario($_SESSION['user']);
if (isset($parametros['sltEstadoTicket']))
    $obj->setEstadoDeTicket($parametros['sltEstadoTicket']);
else
    $obj->setEstadoDeTicket(3);
$obj->setTipoReporte($parametros['sltTipoReporte']);
$contadorFalla = "";

$hay_color = false;
$tamanoTabla = "";
$noSerieConsulta = "";
if ($parametros['sltTipoReporte'] != "15") {//falla
    $equipo = $_POST['tabla'];
    list($contadorFalla, $noSerie, $modelo) = explode(" / ", $equipo);
    $obj->setNoSerieEquipo($noSerie);
    $obj->setModeloEquipo($modelo);
    $noSerieConsulta = $obj->getNoSerieEquipo();
} else if ($parametros['sltTipoReporte'] == "15") {//toner
    $tamanoTabla = $_POST['tabla'];
    $contadorTabla = 0;
    while ($contadorTabla < (int) ($tamanoTabla)) {
        if (isset($parametros['activar_' . $contadorTabla]) && $parametros['activar_' . $contadorTabla] == "on") {
            $hay_toner = false;
            if (isset($parametros['ckbNegro_' . $contadorTabla]) && $parametros['ckbNegro_' . $contadorTabla] == "on" && isset($parametros["txtTonerNegro" . $contadorTabla])) {
                $hay_toner = true;
            }
            if (isset($parametros['ckbCian_' . $contadorTabla]) && $parametros['ckbCian_' . $contadorTabla] == "on" && isset($parametros["txtTonerCian" . $contadorTabla])) {
                $hay_toner = true;
                $hay_color = true;
            }
            if (isset($parametros['ckbMagenta_' . $contadorTabla]) && $parametros['ckbMagenta_' . $contadorTabla] == "on" && isset($parametros["txtTonerMagenta" . $contadorTabla])) {
                $hay_toner = true;
                $hay_color = true;
            }
            if (isset($parametros['ckbAmarillo_' . $contadorTabla]) && $parametros['ckbAmarillo_' . $contadorTabla] == "on" && isset($parametros["txtTonerAmarillo" . $contadorTabla])) {
                $hay_toner = true;
                $hay_color = true;
            }
            if (!$hay_toner) {
                echo "<br/>Error: No hay toners seleccionado para el equipo " . $parametros["txtNoSerieE_" . $contadorTabla];
                return;
            }
            $noSerieConsulta = $parametros["txtNoSerieE_" . $contadorTabla];
        }
        $contadorTabla++;
    }
}//otros valores

if ($parametros['rdContacto'] == "1") {
    $contacto->setClaveEspecialContacto($parametros['slcLocalidad']);
    $contacto->setIdTipoContacto(14);
    $contacto->setNombre($parametros['txtNombre1']);
    if (isset($parametros['txtExtencion1']) && !empty($parametros['txtExtencion1'])) {
        $contacto->setTelefono($parametros['txtTelefono1'] . " ext. " . $parametros['txtExtencion1']);
    } else {
        $contacto->setTelefono($parametros['txtTelefono1']);
    }
    $contacto->setCelular($parametros['txtCelular']);
    $contacto->setCorreoElectronico($parametros['correoElectronico']);
    $contacto->setActivo(1);
    $contacto->setUsuarioCreacion($_SESSION['user']);
    $contacto->setUsuarioUltimaModificacion($_SESSION['user']);
    $contacto->setPantalla("Alta ticket php");
    if (!$contacto->newRegistroCompleto()) {
        echo "<br/>Error: El contacto no se registró correctamente";
    }
    $obj->setNombreResp($parametros['txtNombre1']);
} else if ($parametros['rdContacto'] == "0") {
    $id = split(" // ", $parametros["txtNombre"]);

    $contacto->setIdContacto($id[4]);
    $contacto->setNombre($id[0]);
    $contacto->setClaveEspecialContacto($id[6]);
    $contacto->setIdTipoContacto($id[5]);
    if (isset($parametros['txtExtencion1']) && !empty($parametros['txtExtencion1'])) {
        $contacto->setTelefono($parametros['txtTelefono1'] . " ext. " . $parametros['txtExtencion1']);
    } else {
        $contacto->setTelefono($parametros['txtTelefono1']);
    }
    $contacto->setCelular($parametros['txtCelular']);
    $contacto->setCorreoElectronico($parametros['correoElectronico']);
    $contacto->setActivo(1);
    $contacto->setUsuarioCreacion($_SESSION['user']);
    $contacto->setUsuarioUltimaModificacion($_SESSION['user']);
    $contacto->setPantalla("Alta ticket php");
    if (!$contacto->editRegistro()) {
        echo "<br/>Error: El contacto no se editó correctamente";
    }
    $obj->setNombreResp($id[0]);
}
$obj->setTelefono1Resp($parametros['txtTelefono1']);
$obj->setExtension1Resp($parametros['txtExtencion1']);
$obj->setTelefono2Resp($parametros['txtTelefono2']);
$obj->setExtension2Resp($parametros['txtExtencion2']);
$obj->setCelularResp($parametros['txtCelular']);
$obj->setCorreoEResp($parametros['correoElectronico']);
$obj->setHorarioAtenInicResp($parametros['lstHR'] . "," . $parametros['lstMR'] . "," . $parametros['lstTA']);
$obj->setHorarioAtenFinResp($parametros['lstFinHR'] . "," . $parametros['lstFinMR'] . "," . $parametros['lstFinTR']);

$obj->setNombreAtenc($parametros['txtNombreAtencion']);
$obj->setTelefono1Atenc($parametros['txtTelefono1Atencion']);
$obj->setExtension1Atenc($parametros['txtExtencion1Atencion']);
$obj->setTelefono2Atenc($parametros['txtTelefono2Atencion']);
$obj->setExtension2Atenc($parametros['txtExtencion2Atencion']);
$obj->setCelularAtenc($parametros['txtCelularAtencion']);
$obj->setCorreoEAtenc($parametros['txtCorreoElectronico']);
$obj->setHorarioAtenInicAtenc($parametros['lstHA'] . "," . $parametros['lstMA'] . "," . $parametros['lstTA']);
$obj->setHorarioAtenFinAtenc($parametros['lstFinHA'] . "," . $parametros['lstFinMA'] . "," . $parametros['lstFinTA']);

$obj->setNoTicketCliente($parametros['txtNoTicketClienteGral']);
$obj->setNoTicketDistribuidor($parametros['txtNoTicketDistribucionGral']);
$obj->setNoGuia($parametros['noGuia']);
$obj->setDescripcionReporte(trim(str_replace("'", "´", $parametros['descripcion'])));
$obj->setObservacionAdicional(trim(str_replace("'", "´", $parametros['observacion'])));
$obj->setAreaAtencion($parametros['areaAtencionGral']);
$obj->setUbicacion($parametros["sltUbicacionToner"]);
$obj->setUbicacionEmp("ubicacionEMpresa");
//datos de auditoria
$obj->setActivo(1);
$obj->setUsuarioCreacion($_SESSION['user']);
$obj->setUsuarioUltimaModificacion($_SESSION['user']);
$obj->setPantalla("Alta ticket php");

if($obj->BuscarTicketAbierto($noSerieConsulta, $parametros['sltTipoReporte'], 1) != ""){
    echo "Error: Se intentó duplicar datos.";
    return;
}
if (isset($parametros['idTicket']) && $parametros['idTicket'] == "") {//nuevo registró
    if ($obj->newRegistroCompleto()) {
        
        $almacen = new Almacen();
        $message = "Hay una solicitud de cambio de tóner que no cumplen con el rendimiento registrado en el sistema.<br/>"
                . "<h2>Cliente: " . $obj->getNombreCliente() . "</h2>"
                . "<h2>Localidad: " . $obj->getNombreCentroCosto() . "</h2>";
        if ($almacen->getRegistrByLocalidad($obj->getClaveCentroCosto())) {
            $message .= "<h2>Mini-almacén de la localidad: " . $almacen->getNombre() . "</h2>";
        }
        if ($hay_color) {
            $message .= ("<table style='border-collapse: collapse;'><thead>"
                    . "<tr>"
                    . "<th style='border: 1px solid black;'>Serie</th><th style='border: 1px solid black;'>Contador BN</th>"
                    . "<th style='border: 1px solid black;'>Contador Color</th>"
                    . "<th style='border: 1px solid black;'>Contador BN Anterior</th>"
                    . "<th style='border: 1px solid black;'>Contador Color Anterior</th>"
                    . "<th style='border: 1px solid black;'>Toner solicitado (rendimiento)</th>"
                    . "<th style='border: 1px solid black;'>Impresas negro</th>"
                    . "<th style='border: 1px solid black;'>Impresas color</th>"
                    . "</tr>"
                    . "</thead><tbody>");
        } else {
            $message .= ("<table style='border-collapse: collapse;'><thead>"
                    . "<tr>"
                    . "<th style='border: 1px solid black;'>Serie</th><th style='border: 1px solid black;'>Contador BN</th>"
                    . "<th style='border: 1px solid black;'>Contador BN Anterior</th>"
                    . "<th style='border: 1px solid black;'>Toner solicitado (rendimiento)</th>"
                    . "<th style='border: 1px solid black;'>Impresas negro</th>"
                    . "</tr>"
                    . "</thead><tbody>");
        }
        //Buscamos si hay escañamientos con tiempo de espera 0 para enviar correo
        if (isset($parametros['sltEstadoTicket']) && $parametros['sltEstadoTicket'] != "") {
            $consulta = "SELECT cee.*, e.Nombre from c_escalamientoEstado cee LEFT JOIN c_estado AS e ON e.IdEstado = cee.idEstado "
                    . "WHERE e.IdEstadoTicket = '" . $parametros['sltEstadoTicket'] . "' AND cee.tiempoEnvio = 0; ";
            $result = $catalogo->obtenerLista($consulta);
            while ($row = mysql_fetch_array($result)) {
                if ($row['prioridad'] < $parametros['prioridad']) {
                    $updatePrioridad = "UPDATE c_ticket t SET t.Prioridad = (SELECT pt.IdPrioridad from c_prioridadticket pt WHERE pt.Prioridad = " . $row['prioridad'] . ")
                        WHERE t.IdTicket = " . $obj->getIdTicket();
                    $rsUpdate = $catalogo->obtenerLista($updatePrioridad);
                    if ($rsUpdate) {
                        //echo "Se ha modificado la prioridad del ticket " . $obj->getIdTicket() . " debido a que el escalamiento tenía una prioridad mayor";
                    }
                }
                $correos = array();
                $mail = new Mail();
                $NombreCliente = "";
                $mail->setSubject("Atención al ticket " . $obj->getIdTicket());
                $message = "<br/>Es importante que se atienda el ticket <b>" . $obj->getIdTicket() . "</b> del cliente " . $obj->getNombreCliente() . " se
                encuentra en el estado " . $row['Nombre'] . "<br/>";
                if ($parametros['sltTipoReporte'] != "15" && $obj->getNoSerieEquipo() !== "") {
                    $message .= ("Serie: " . $obj->getNoSerieEquipo());
                }
                $mail->setBody($message . "<br/>Mensaje: " . $row['mensaje']);

                $mail->setFrom($correo_emisor);

                /* Obtenemos los correos a los que le enviaremos la informacion */
                $queryCorreos = "SELECT correo from c_escalamientoCorreo ec WHERE idEscalamiento = " . $row['idEscalamiento'];
                $resultCorreos = $catalogo->obtenerLista($queryCorreos);
                while ($rsCorreo = mysql_fetch_array($resultCorreos)) {
                    $tipo = substr($rsCorreo['correo'], 0, 2);
                    $queryFinal = "";
                    if (strcmp($tipo, "cl") == 0) {
                        $queryFinal = "SELECT CorreoElectronicoEnvioFact1, CorreoElectronicoEnvioFact2, CorreoElectronicoEnvioFact3,CorreoElectronicoEnvioFact4
                            from c_cliente WHERE ClaveCliente = " . $parametros['slcCliente'];
                    } else if (strcmp($tipo, "co") == 0) {
                        $queryFinal = "SELECT CorreoElectronico from c_contacto WHERE IdTipoContacto = " . substr($rsCorreo['correo'], 2);
                    } else if (strcmp($tipo, "us") == 0) {
                        $queryFinal = "SELECT correo from c_usuario WHERE idUsuario = " . substr($rsCorreo['correo'], 2);
                    } else if (strcmp($tipo, "tf") == 0) {
                        $queryFinal = "SELECT u.correo from k_tfscliente ktc 
                        LEFT JOIN c_usuario u ON ktc.IdUsuario = u.IdUsuario 
                        LEFT JOIN c_ticket t ON t.ClaveCliente = ktc.ClaveCliente
                        WHERE t.IdTicket = " . $obj->getIdTicket();
                    }
                    $resultFinal = $catalogo->obtenerLista($queryFinal);
                    while ($rsFinal = mysql_fetch_array($resultFinal)) {
                        if (isset($rsFinal['CorreoElectronicoEnvioFact1']) && $rsFinal['CorreoElectronicoEnvioFact1'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact1'], FILTER_VALIDATE_EMAIL)) {
                            array_push($correos, $rsFinal['CorreoElectronicoEnvioFact1']);
                        }
                        if (isset($rsFinal['CorreoElectronicoEnvioFact2']) && $rsFinal['CorreoElectronicoEnvioFact2'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact2'], FILTER_VALIDATE_EMAIL)) {
                            array_push($correos, $rsFinal['CorreoElectronicoEnvioFact2']);
                        }
                        if (isset($rsFinal['CorreoElectronicoEnvioFact3']) && $rsFinal['CorreoElectronicoEnvioFact3'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact3'], FILTER_VALIDATE_EMAIL)) {
                            array_push($correos, $rsFinal['CorreoElectronicoEnvioFact3']);
                        }
                        if (isset($rsFinal['CorreoElectronicoEnvioFact4']) && $rsFinal['CorreoElectronicoEnvioFact4'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact4'], FILTER_VALIDATE_EMAIL)) {
                            array_push($correos, $rsFinal['CorreoElectronicoEnvioFact4']);
                        }
                        if (isset($rsFinal['CorreoElectronico']) && $rsFinal['CorreoElectronico'] != "" && filter_var($rsFinal['CorreoElectronico'], FILTER_VALIDATE_EMAIL)) {
                            array_push($correos, $rsFinal['CorreoElectronico']);
                        }
                        if (isset($rsFinal['correo']) && $rsFinal['correo'] != "" && filter_var($rsFinal['correo'], FILTER_VALIDATE_EMAIL)) {
                            array_push($correos, $rsFinal['correo']);
                        }
                    }
                }
                foreach ($correos as $value) {/* Lo mandamos a los correos de los usuarios de cuentas por cobrar */
                    $mail->setTo($value);
                    if ($mail->enviarMail() == "1") {
                        //echo "<br/>Un correo fue enviado por escalamientos a $value. <br/>";
                    } else {
                        echo "<br/>Error: No se pudo enviar el correo de escalamiento a $value. <br/>";
                    }
                }
            }
        }

        //agregar Lectura 
        if ($parametros['sltTipoReporte'] == "15") {
            $notaTicket->setIdTicket($obj->getIdTicket());
            $notaTicket->setDiagnostico("Solicitud de toner");
            $notaTicket->setIdEstatus(67);
            $notaTicket->setUsuarioSolicitud($_SESSION['user']);
            $notaTicket->setMostrarCliente(1);
            $notaTicket->setActivo(1);
            $notaTicket->setUsuarioCreacion($_SESSION['user']);
            $notaTicket->setUsuarioModificacion($_SESSION['user']);
            $notaTicket->setPantalla("Alta ticket php");
            if ($notaTicket->newRegistro()) {//agregar la nota de solicitud de toner               
                $pedido->setIdTicket($obj->getIdTicket());
                $pedido->setActivo(1);
                $pedido->setUsuarioCreacion($_SESSION['user']);
                $pedido->setUsuarioUltimaModificacion($_SESSION['user']);
                $pedido->setPantalla("Alta ticket php");
                $pedido->setEstado("Validar Existencia");
                $contadorTabla = 0;
//datos de la nota refaccion
                $notaRefaccion->setIdNota($notaTicket->getIdNota());
                $notaRefaccion->setNoParte($notaTicket->getIdNota());
                $notaRefaccion->setCantidad(1);
                $notaRefaccion->setCantidadSurtidas(0);
                $notaRefaccion->setIdAlmacen("null");
                $notaRefaccion->setUsuarioCreacion($_SESSION['user']);
                $notaRefaccion->setUsuarioModificacion($_SESSION['user']);
                $notaRefaccion->setPantalla("Alta ticket php");
                while ($contadorTabla < (int) ($tamanoTabla)) {
                    //Guardamos una incidencia en caso de que el contador actual supere en más de 100,000 al contadorAnterior
                    if (((int) $parametros['txtContadorNegroAnterior_' . $contadorTabla] + 100000) < (int) $parametros['txtContadorNegro_' . $contadorTabla]) {
                        $incidencia = new Incidencia();
                        $incidencia->setNoSerie($parametros["txtNoSerieE_" . $contadorTabla]);
                        $incidencia->setFecha(date("Y-m-d"));
                        $incidencia->setFechaFin(date("Y-m-d"));
                        $incidencia->setDescripcion("El contador negro actual supero por más de 100,000 al anterior");
                        $incidencia->setStatus(1);
                        $incidencia->setClaveCentroCosto($parametros['slcLocalidad']);
                        $incidencia->setId_Ticket($obj->getIdTicket());
                        $incidencia->setIdTipoIncidencia(9);
                        $incidencia->setActivo(1);
                        $incidencia->setUsuarioCreacion($_SESSION['user']);
                        $incidencia->setUsuarioUltimaModificacion($_SESSION['user']);
                        $incidencia->setPantalla("Alta ticket");
                        if ($incidencia->newRegistro()) {
                            $mensajes1 .= "<br/>Se agrego una incidencia con el No.Serie " . $parametros["txtNoSerieE_" . $contadorTabla] . " porque el contador actual supera en más de 100,000 al anterior";
                        }
                    }
                    if (((int) $parametros['txtContadorColorAnterior_' . $contadorTabla] + 100000) < (int) $parametros['txtContadorColor_' . $contadorTabla]) {
                        $incidencia = new Incidencia();
                        $incidencia->setNoSerie($parametros["txtNoSerieE_" . $contadorTabla]);
                        $incidencia->setFecha(date("Y-m-d"));
                        $incidencia->setFechaFin(date("Y-m-d"));
                        $incidencia->setDescripcion("El contador color actual supero por más de 100,000 al anterior");
                        $incidencia->setStatus(1);
                        $incidencia->setClaveCentroCosto($parametros['slcLocalidad']);
                        $incidencia->setId_Ticket($obj->getIdTicket());
                        $incidencia->setIdTipoIncidencia(9);
                        $incidencia->setActivo(1);
                        $incidencia->setUsuarioCreacion($_SESSION['user']);
                        $incidencia->setUsuarioUltimaModificacion($_SESSION['user']);
                        $incidencia->setPantalla("Alta ticket");
                        if ($incidencia->newRegistro()) {
                            $mensajes1 .= "<br/>Se agrego una incidencia con el No.Serie " . $parametros["txtNoSerieE_" . $contadorTabla] . " porque el contador actual supera en más de 100,000 al anterior";
                        }
                    }
                    $pedido->setClaveEspEquipo($parametros["txtNoSerieE_" . $contadorTabla]);
                    $pedido->setModelo($parametros["txtModeloE_" . $contadorTabla]);
                    $notaRefaccion->setNoSerie($parametros["txtNoSerieE_" . $contadorTabla]);
                    $negro = 0;
                    $cian = 0;
                    $magenta = 0;
                    $amarillo = 0;

                    if (isset($parametros['activar_' . $contadorTabla]) && $parametros['activar_' . $contadorTabla] == "on") {
                        //if (isset($parametros['ckbNegro_' . $contadorTabla]) && $parametros['ckbNegro_' . $contadorTabla] == "on" || isset($parametros['ckbCian_' . $contadorTabla]) && $parametros['ckbCian_' . $contadorTabla] == "on" || isset($parametros['ckbMagenta_' . $contadorTabla]) && $parametros['ckbMagenta_' . $contadorTabla] == "on" || isset($parametros['ckbAmarillo_' . $contadorTabla]) && $parametros['ckbAmarillo_' . $contadorTabla] == "on") {
                        $lecturaTicket->setClaveEspEquipo($parametros["txtNoSerieE_" . $contadorTabla]);
                        $lecturaTicket->setModeloEquipo($parametros["txtModeloE_" . $contadorTabla]);
                        $lecturaTicket->setContadorBN($parametros['txtContadorNegro_' . $contadorTabla]);
                        $lecturaTicket->setNivelNegro($parametros['txtNivelNegro_' . $contadorTabla]);
                        if (isset($parametros['txtContadorColor_' . $contadorTabla])) {
                            $lecturaTicket->setContadorColor($parametros['txtContadorColor_' . $contadorTabla]);
                            $lecturaTicket->setNivelCia($parametros['txtNivelCian_' . $contadorTabla]);
                            $lecturaTicket->setNivelMagenta($parametros['txtNivelMagenta_' . $contadorTabla]);
                            $lecturaTicket->setNivelAmarillo($parametros['txtNivelAmarillo_' . $contadorTabla]);
                        } else {
                            $lecturaTicket->setContadorColor("");
                            $lecturaTicket->setNivelCia("");
                            $lecturaTicket->setNivelMagenta("");
                            $lecturaTicket->setNivelAmarillo("");
                        }
                        $lecturaTicket->setIdTicket($obj->getIdTicket());
                        //$lecturaTicket->setFecha($parametros['fechaContador']);
                        $lecturaTicket->setFechaA($parametros['txtfechaAnterior_' . $contadorTabla]);
                        $lecturaTicket->setContadorBNA($parametros['txtContadorNegroAnterior_' . $contadorTabla]);
                        $lecturaTicket->setNivelNegroA($parametros['txtNivelNegroAnterior_' . $contadorTabla]);
                        if (isset($parametros['txtContadorColorAnterior_' . $contadorTabla])) {
                            $lecturaTicket->setContadorColorA($parametros['txtContadorColorAnterior_' . $contadorTabla]);
                            $lecturaTicket->setNivelCiaA($parametros['txtNivelCianAnterior_' . $contadorTabla]);
                            $lecturaTicket->setNivelMagentaA($parametros['txtNivelMagentaAnterior_' . $contadorTabla]);
                            $lecturaTicket->setNivelAmarilloA($parametros['txtNivelAmarilloAnterior_' . $contadorTabla]);
                        } else {
                            $lecturaTicket->setContadorColorA("");
                            $lecturaTicket->setNivelCiaA("");
                            $lecturaTicket->setNivelMagentaA("");
                            $lecturaTicket->setNivelAmarilloA("");
                        }
                        $lecturaTicket->setComentario($parametros['comentario_' . $contadorTabla]);
                        $lecturaTicket->setActivo(1);
                        $lecturaTicket->setUsuarioCreacion($_SESSION['user']);
                        $lecturaTicket->setUsuarioUltimaModificacion($_SESSION['user']);
                        $lecturaTicket->setPantalla("Alta ticket php");
                        $idLecturaEquipo = 0;
                        if ($lecturaTicket->NewRegistro()) {
                            $idLecturaEquipo = $lecturaTicket->getIdLectura();
                        } else {
                            
                        }

                        $color = false;
                        if ($caracteristicas->isColor($parametros["txtNoParteE_$contadorTabla"])) {
                            $color = true;
                        }
                        $rendimiento_negro = "";
                        $rendimiento_color = "";
                        $totalContadores = "";
                        $totalContadoresCL = "";

                        $message .= ("<tr><td style='border: 1px solid black;'>" . $lecturaTicket->getClaveEspEquipo() . " / " . $lecturaTicket->getModeloEquipo() . "</td>"
                                . "<td style='border: 1px solid black;'>" . number_format($lecturaTicket->getContadorBN()) . "</td>");
                        if ($hay_color) {
                            if ($color) {
                                $message .= "<td style='border: 1px solid black;'>" . number_format($lecturaTicket->getContadorColor()) . "</td>";
                            } else {
                                $message .= "<td style='border: 1px solid black;'></td>";
                            }
                        }
                        if ($lecturaTicket->getContadorBNA() != "") {
                            $totalContadores = $lecturaTicket->getContadorBN() - $lecturaTicket->getContadorBNA();
                            $message .= "<td style='border: 1px solid black;'>" . number_format($lecturaTicket->getContadorBNA()) . "</td>";
                        } else {
                            $message .= "<td style='border: 1px solid black;'></td>";
                        }
                        if ($hay_color) {
                            if ($color && $lecturaTicket->getContadorColorA() != "") {
                                $totalContadoresCL = $lecturaTicket->getContadorColor() - $lecturaTicket->getContadorColorA();
                                $message .= "<td style='border: 1px solid black;'>" . number_format($lecturaTicket->getContadorColorA()) . "</td>";
                            } else {
                                $message .= "<td style='border: 1px solid black;'></td>";
                            }
                        }


                        //Rendimiento
                        $rendimiento_message = "";
                        $componente = new Componente();

                        //Negro
                        if (isset($parametros['ckbNegro_' . $contadorTabla]) && $parametros['ckbNegro_' . $contadorTabla] == "on" &&
                                isset($parametros["txtTonerNegro$contadorTabla"]) && !empty($parametros["txtTonerNegro$contadorTabla"])) {
                            if ($componente->getRegistroById($parametros["txtTonerNegro$contadorTabla"])) {
                                if ($componente->getRendimiento() != "") {
                                    $rendimiento_negro = $componente->getRendimiento();
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero() . ": " . number_format($componente->getRendimiento());
                                } else {
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero();
                                }
                            }
                        }
                        //Amarillo
                        if (isset($parametros['ckbAmarillo_' . $contadorTabla]) && $parametros['ckbAmarillo_' . $contadorTabla] == "on" &&
                                isset($parametros["txtTonerAmarillo$contadorTabla"]) && !empty($parametros["txtTonerAmarillo$contadorTabla"])) {
                            if ($componente->getRegistroById($parametros["txtTonerAmarillo$contadorTabla"])) {
                                if ($componente->getRendimiento() != "") {
                                    $rendimiento_color = $componente->getRendimiento();
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero() . ": " . number_format($componente->getRendimiento());
                                } else {
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero();
                                }
                            }
                        }
                        //Magenta
                        if (isset($parametros['ckbMagenta_' . $contadorTabla]) && $parametros['ckbMagenta_' . $contadorTabla] == "on" &&
                                isset($parametros["txtTonerMagenta$contadorTabla"]) && !empty($parametros["txtTonerMagenta$contadorTabla"])) {
                            if ($componente->getRegistroById($parametros["txtTonerMagenta$contadorTabla"])) {
                                if ($componente->getRendimiento() != "") {
                                    $rendimiento_color = $componente->getRendimiento();
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero() . ": " . number_format($componente->getRendimiento());
                                } else {
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero();
                                }
                            }
                        }
                        //Cian
                        if (isset($parametros['ckbCian_' . $contadorTabla]) && $parametros['ckbCian_' . $contadorTabla] == "on" &&
                                isset($parametros["txtTonerCian$contadorTabla"]) && !empty($parametros["txtTonerCian$contadorTabla"])) {
                            if ($componente->getRegistroById($parametros["txtTonerCian$contadorTabla"])) {
                                if ($componente->getRendimiento() != "") {
                                    $rendimiento_color = $componente->getRendimiento();
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero() . ": " . number_format($componente->getRendimiento());
                                } else {
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero();
                                }
                            }
                        }

                        $message .= "<td style='border: 1px solid black;'>$rendimiento_message</td>";

                        $texto = "";
                        if (!empty($rendimiento_negro) && !empty($totalContadores)) {
                            $texto = "(" . number_format(($totalContadores * 100) / $rendimiento_negro) . "%)";
                        }
                        $message .= "<td style='border: 1px solid black;'>" . number_format($lecturaTicket->getContadorBN() - $lecturaTicket->getContadorBNA()) . " $texto</td>";

                        if ($hay_color) {
                            if ($color && $lecturaTicket->getContadorColorA() != "") {
                                $texto = "";
                                if (!empty($rendimiento_color) && !empty($totalContadoresCL)) {
                                    $texto = "(" . number_format(($totalContadoresCL * 100) / $rendimiento_color) . "%)";
                                }
                                $message .= "<td style='border: 1px solid black;'>" . number_format($lecturaTicket->getContadorColor() - $lecturaTicket->getContadorColorA()) . " $texto</td>";
                            } else {
                                $message .= "<td style='border: 1px solid black;'></td>";
                            }
                        }
                        $message .= "</tr>";

                        $pedido->setIdLecturaTicket($idLecturaEquipo);
                        if (isset($parametros['activar_' . $contadorTabla]) && $parametros['activar_' . $contadorTabla] == "on") {
                            if (isset($parametros['ckbNegro_' . $contadorTabla]) && $parametros['ckbNegro_' . $contadorTabla] == "on" && isset($parametros["txtTonerNegro" . $contadorTabla])) {
                                $negro = 1;
                                $notaRefaccion->setNoParte($parametros["txtTonerNegro" . $contadorTabla]);
                                if ($notaRefaccion->newRegistroSerie()) {//agregar en detalle nota refaccion
                                    if (!$notaRefaccion->newRegistroDetalle()) {
                                        echo "<br/>Error: El detalle no se agregó correctamente";
                                    }
                                } else {
                                    if ($notaRefaccion->VerificarExistencia()) {
                                        if ($notaRefaccion->editarCantidadTonerRepetido()) {
                                            if (!$notaRefaccion->newRegistroDetalle()) {
                                                echo "<br/>Error: El detalle no se agregó correctamente";
                                            }
                                        } else {
                                            echo "<br/>Error: El toner no se registro correctamente";
                                        }
                                    } else {
                                        echo "<br/>Error: El toner no se registro correctamente";
                                    }
                                }
                            }
                            if (isset($parametros['ckbCian_' . $contadorTabla]) && $parametros['ckbCian_' . $contadorTabla] == "on" && isset($parametros["txtTonerCian" . $contadorTabla])) {
                                $cian = 1;
                                $notaRefaccion->setNoParte($parametros["txtTonerCian" . $contadorTabla]);
                                if ($notaRefaccion->newRegistroSerie()) {
                                    if (!$notaRefaccion->newRegistroDetalle()) {
                                        echo "<br/>Error: El detalle no se agregó correctamente";
                                    }
                                } else {
                                    if ($notaRefaccion->VerificarExistencia()) {
                                        if ($notaRefaccion->editarCantidadTonerRepetido()) {
                                            if (!$notaRefaccion->newRegistroDetalle()) {
                                                echo "<br/>Error: El detalle no se agregó correctamente";
                                            }
                                        } else {
                                            echo "<br/>Error: El toner no se registró correctamente";
                                        }
                                    } else {
                                        echo "<br/>Error: El toner no se registró correctamente";
                                    }
                                }
                            }
                            if (isset($parametros['ckbMagenta_' . $contadorTabla]) && $parametros['ckbMagenta_' . $contadorTabla] == "on" && isset($parametros["txtTonerMagenta" . $contadorTabla])) {
                                $magenta = 1;
                                $notaRefaccion->setNoParte($parametros["txtTonerMagenta" . $contadorTabla]);
                                if ($notaRefaccion->newRegistroSerie()) {
                                    if (!$notaRefaccion->newRegistroDetalle()) {
                                        echo "<br/>Error: El detalle no se agregó correctamente";
                                    }
                                } else {
                                    if ($notaRefaccion->VerificarExistencia()) {
                                        if ($notaRefaccion->editarCantidadTonerRepetido()) {
                                            if (!$notaRefaccion->newRegistroDetalle()) {
                                                echo "<br/>Error: El detalle no se agregó correctamente";
                                            }
                                        } else {
                                            echo "<br/>Error: El toner no se registro correctamente";
                                        }
                                    } else {
                                        echo "<br/>Error: El toner no se registro correctamente";
                                    }
                                }
                            }
                            if (isset($parametros['ckbAmarillo_' . $contadorTabla]) && $parametros['ckbAmarillo_' . $contadorTabla] == "on" && isset($parametros["txtTonerAmarillo" . $contadorTabla])) {
                                $amarillo = 1;
                                $notaRefaccion->setNoParte($parametros["txtTonerAmarillo" . $contadorTabla]);
                                if ($notaRefaccion->newRegistroSerie()) {
                                    if (!$notaRefaccion->newRegistroDetalle()) {
                                        echo "<br/>Error: El detalle no se agregó correctamente";
                                    }
                                } else {
                                    if ($notaRefaccion->VerificarExistencia()) {
                                        if ($notaRefaccion->editarCantidadTonerRepetido()) {
                                            if (!$notaRefaccion->newRegistroDetalle()) {
                                                echo "<br/>Error: El detalle no se agregó correctamente";
                                            }
                                        } else {
                                            echo "<br/>Error: El toner no se registro correctamente";
                                        }
                                    } else
                                        echo "<br/>Error: El toner no se registro correctamente";
                                }
                            }
                        }
                        $pedido->setTonerNegro($negro);
                        $pedido->setTonerCian($cian);
                        $pedido->setTonerMagenta($magenta);
                        $pedido->setTonerAmarillo($amarillo);
                        if (!$pedido->newRegistro()) {
                            echo "<br/>Error: El pedido no se registró correctamente";
                        }
                    }//fin if
                    $contadorTabla++;
                }//fin while
                $message .= "</tbody></table>";
                //copiar la nota 
                if (isset($_GET['pendiente_autorizar']) && $_GET['pendiente_autorizar'] == "1") {
                    $mail = new Mail();
                    $mail->setFrom($correo_emisor);
                    $mail->setSubject("Solicitud de cambio de tóner del ticket " . $obj->getIdTicket() . " del cliente " . $obj->getNombreCliente());

                    // Guardamos y creamos la liga para aceptar/rechazar la solicitud directamente 
                    $clave = $mail->generaPass();
                    $idMail = $catalogo->insertarRegistro("INSERT INTO c_mailpedidotoner(idTicket, contestada, clave, IdUsuario, Activo, UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                                            VALUES(" . $obj->getIdTicket() . ",0,MD5('$clave'),2,1,'" . $_SESSION['user'] . "',now(),'" . $_SESSION['user'] . "',now(),'nuevo ticket.php');");

                    $liga = "$url/aceptarPedidoToner.php?clv=$clave&idTicket=" . $obj->getIdTicket() . "&idMail=$idMail&idNota=" . $notaTicket->getIdNota() . "&tipo";
                    $message = $message . "<br/>Autorizar solicitud: " . $liga . "=1&uguid=" . $_SESSION['idEmpresa'] . " <br/><br/>";
                    $message = $message . "<br/>Rechazar solicitud: " . $liga . "=3&uguid=" . $_SESSION['idEmpresa'] . " <br/><br/>";
                    $mail->setBody($message);
                    $consultaCorreos = "SELECT correo FROM c_correossolicitud WHERE TipoSolicitud = 20 AND Activo = 1;";
                    $resultCorreos = $catalogo->obtenerLista($consultaCorreos);
                    while ($rsCorreo = mysql_fetch_array($resultCorreos)) {
                        $value = $rsCorreo['correo'];
                        if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $mail->setTo($value);
                            if ($mail->enviarMail() == "1") {
                                // echo "Un correo fue enviado para la autorización.";
                            } else {
                                echo "<br/>No se envió correo de autorización de cambio d etoner a $value.<br/>";
                            }
                        }
                    }
                } else {
                    $solicitudToner->setNotaAnterior($notaTicket->getIdNota());
                    $solicitudToner->setIdEstadoNota(65);
                    $solicitudToner->setMostrarCliente(0);
                    $notaTicket->setDiagnostico("Solicitud de toner");
                    $solicitudToner->setUsuarioCreacion($_SESSION['user']);
                    $solicitudToner->setUsuarioModificacion($_SESSION['user']);
                    $solicitudToner->setPantalla("Alta ticket php");
                    if ($solicitudToner->newNotaSolicitudTonerTicket()) {
                        if ($solicitudToner->copyTonerNota()) {
                            
                        }
                    }
                }

                if ($obj->editTicketDescripcion()) {
                    echo $obj->getIdTicket();
                } else {
                    echo "<br/>Error: EL ticket <b>" . $obj->getIdTicket() . "</b> no se registró correctamente";
                }
            } else {
                echo "<br/>Error: La nota no se agregó correctamente";
            }
        } else {
            list($fila, $noSerie, $modelo) = explode(" / ", $equipo);
            if ($parametros['sltTipoReporte'] != "15") {
                if (isset($parametros['ubicacionNoDomicilio']) && $parametros['ubicacionNoDomicilio'] != "") {//cambiar la ubicacion del equipo
                    if ($obj->actualizarUbicacion($parametros["txtNoSerieE_" . $fila], $parametros['ubicacionNoDomicilio'])) {
                        //echo "ubicacion modificadad";
                    } else {
                        //echo "Ubicacion sin modificar";
                    }
                }
                //Guardamos una incidencia en caso de que el contador actual supere en más de 100,000 al contadorAnterior
                if (((int) $parametros['txtContadorNegroAnterior_' . $fila] + 100000) < (int) $parametros['txtContadorNegro_' . $fila]) {
                    $incidencia = new Incidencia();
                    $incidencia->setNoSerie($parametros["txtNoSerieE_" . $fila]);
                    $incidencia->setFecha(date("Y-m-d"));
                    $incidencia->setFechaFin(date("Y-m-d"));
                    $incidencia->setDescripcion("El contador negro actual supero por más de 100,000 al anterior");
                    $incidencia->setStatus(1);
                    $incidencia->setClaveCentroCosto($parametros['slcLocalidad']);
                    $incidencia->setId_Ticket($obj->getIdTicket());
                    $incidencia->setIdTipoIncidencia(9);
                    $incidencia->setActivo(1);
                    $incidencia->setUsuarioCreacion($_SESSION['user']);
                    $incidencia->setUsuarioUltimaModificacion($_SESSION['user']);
                    $incidencia->setPantalla("Alta ticket");
                    if ($incidencia->newRegistro()) {
                        $mensajes1 .= "<br/>Se agrego una incidencia con el No.Serie " . $parametros["txtNoSerieE_" . $fila] . " porque el contador actual supera en más de 100,000 al anterior";
                    }
                }
                if (((int) $parametros['txtContadorColorAnterior_' . $fila] + 100000) < (int) $parametros['txtContadorColor_' . $fila]) {
                    $incidencia = new Incidencia();
                    $incidencia->setNoSerie($parametros["txtNoSerieE_" . $fila]);
                    $incidencia->setFecha(date("Y-m-d"));
                    $incidencia->setFechaFin(date("Y-m-d"));
                    $incidencia->setDescripcion("El contador color actual supero por más de 100,000 al anterior");
                    $incidencia->setStatus(1);
                    $incidencia->setClaveCentroCosto($parametros['slcLocalidad']);
                    $incidencia->setId_Ticket($obj->getIdTicket());
                    $incidencia->setIdTipoIncidencia(9);
                    $incidencia->setActivo(1);
                    $incidencia->setUsuarioCreacion($_SESSION['user']);
                    $incidencia->setUsuarioUltimaModificacion($_SESSION['user']);
                    $incidencia->setPantalla("Alta ticket");
                    if ($incidencia->newRegistro()) {
                        $mensajes1 .= "<br/>Se agrego una incidencia con el No.Serie " . $parametros["txtNoSerieE_" . $contadorTabla] . " porque el contador actual supera en más de 100,000 al anterior";
                    }
                }
                $lecturaTicket->setClaveEspEquipo($parametros["txtNoSerieE_" . $fila]);
                $lecturaTicket->setModeloEquipo($parametros["txtModeloE_" . $fila]);
                $lecturaTicket->setContadorBN($parametros['txtContadorNegro_' . $fila]);
                if (isset($parametros['txtContadorColor_' . $fila])) {
                    $lecturaTicket->setContadorColor($parametros['txtContadorColor_' . $fila]);
                } else {
                    $lecturaTicket->setContadorColor("");
                }
                $lecturaTicket->setNivelNegro("");
                $lecturaTicket->setNivelCia("");
                $lecturaTicket->setNivelMagenta("");
                $lecturaTicket->setNivelAmarillo("");
                $lecturaTicket->setIdTicket($obj->getIdTicket());
//                    $lecturaTicket->setFecha($parametros['fechaContador']);
                $lecturaTicket->setFechaA($parametros['txtfechaAnterior_' . $fila]);
                $lecturaTicket->setContadorBNA($parametros['txtContadorNegroAnterior_' . $fila]);
                if (isset($parametros['txtContadorColorAnterior_' . $fila]))
                    $lecturaTicket->setContadorColorA($parametros['txtContadorColorAnterior_' . $fila]);
                else
                    $lecturaTicket->setContadorColorA("");
                $lecturaTicket->setNivelNegroA("");
                $lecturaTicket->setNivelCiaA("");
                $lecturaTicket->setNivelMagentaA("");
                $lecturaTicket->setNivelAmarilloA("");
                $lecturaTicket->setComentario($parametros['comentario_' . $fila]);
                $lecturaTicket->setActivo(1);
                $lecturaTicket->setUsuarioCreacion($_SESSION['user']);
                $lecturaTicket->setUsuarioUltimaModificacion($_SESSION['user']);
                $lecturaTicket->setPantalla("Alta ticket php");
                $idLecturaEquipo = 0;
                if ($lecturaTicket->NewRegistro()) {
                    $idLecturaEquipo = $lecturaTicket->getIdLectura();
                } else {
                    
                }
            }
            echo $obj->getIdTicket();
        }
    } else {
        echo "<br/>Error: El ticket no se registró correctamente, reportarlo con el administrador por favor. ";
    }
} else {//editar ticket
    $idTicket = $parametros['idTicket'];
    $pantalla = "Edita ticket";

    $almacen = new Almacen();
    $message = "Hay una solicitud de cambio de tóner que no cumplen con el rendimiento registrado en el sistema.<br/>"
            . "<h2>Cliente: " . $obj->getNombreCliente() . "</h2>"
            . "<h2>Localidad: " . $obj->getNombreCentroCosto() . "</h2>";
    if ($almacen->getRegistrByLocalidad($obj->getClaveCentroCosto())) {
        $message .= "<h2>Mini-almacén de la localidad: " . $almacen->getNombre() . "</h2>";
    }

    if ($hay_color) {
        $message .= ("<table style='border-collapse: collapse;'><thead>"
                . "<tr>"
                . "<th style='border: 1px solid black;'>Serie</th><th style='border: 1px solid black;'>Contador BN</th>"
                . "<th style='border: 1px solid black;'>Contador Color</th>"
                . "<th style='border: 1px solid black;'>Contador BN Anterior</th>"
                . "<th style='border: 1px solid black;'>Contador Color Anterior</th>"
                . "<th style='border: 1px solid black;'>Toner solicitado (rendimiento)</th>"
                . "<th style='border: 1px solid black;'>Impresas negro</th>"
                . "<th style='border: 1px solid black;'>Impresas color</th>"
                . "</tr>"
                . "</thead><tbody>");
    } else {
        $message .= ("<table style='border-collapse: collapse;'><thead>"
                . "<tr>"
                . "<th style='border: 1px solid black;'>Serie</th><th style='border: 1px solid black;'>Contador BN</th>"
                . "<th style='border: 1px solid black;'>Contador BN Anterior</th>"
                . "<th style='border: 1px solid black;'>Toner solicitado (rendimiento)</th>"
                . "<th style='border: 1px solid black;'>Impresas negro</th>"
                . "</tr>"
                . "</thead><tbody>");
    }

    if ($parametros['sltTipoReporte'] == "15") {
        $pedido->setIdTicket($idTicket);

        if ($pedido->deleteRegitro()) {
            $lecturaTicket->setIdTicket($idTicket);
            $lecturaTicket->deleteRegitro();
            $notaRefaccion->setIdTicket($idTicket);
            $notaRefaccion->deleteRegitro();
            $notaRefaccion->deleteDetalleRefaccion();
            $notaTicket->setIdTicket($idTicket);
            $notaTicket->deleteRegitro();

            //ticket
            $notaTicket->setIdTicket($idTicket);
            $notaTicket->setDiagnostico("Solicitud de toner");
            $notaTicket->setIdEstatus(67);
            $notaTicket->setMostrarCliente(0);
            $notaTicket->setUsuarioSolicitud($_SESSION['user']);
            $notaTicket->setActivo(1);
            $notaTicket->setUsuarioCreacion($_SESSION['user']);
            $notaTicket->setUsuarioModificacion($_SESSION['user']);
            $notaTicket->setPantalla($pantalla);
            if ($notaTicket->newRegistro()) {//agregar la nota de solicitud de toner               
                $pedido->setIdTicket($idTicket);
                $pedido->setActivo(1);
                $pedido->setUsuarioCreacion($_SESSION['user']);
                $pedido->setUsuarioUltimaModificacion($_SESSION['user']);
                $pedido->setPantalla($pantalla);
                $pedido->setEstado("Validar Existencia");
                $contadorTabla = 0;
                //datos de la nota refaccion
                $notaRefaccion->setIdNota($notaTicket->getIdNota());
                $notaRefaccion->setNoParte($notaTicket->getIdNota());
                $notaRefaccion->setCantidad(1);
                $notaRefaccion->setCantidadSurtidas(0);
                $notaRefaccion->setIdAlmacen("null");
                $notaRefaccion->setUsuarioCreacion($_SESSION['user']);
                $notaRefaccion->setUsuarioModificacion($_SESSION['user']);
                $notaRefaccion->setPantalla($pantalla);
                while ($contadorTabla < (int) $tamanoTabla) {
                    //Guardamos una incidencia en caso de que el contador actual supere en más de 100,000 al contadorAnterior
                    if (((int) $parametros['txtContadorNegroAnterior_' . $contadorTabla] + 100000) < (int) $parametros['txtContadorNegro_' . $contadorTabla]) {
                        $incidencia = new Incidencia();
                        $incidencia->setNoSerie($parametros["txtNoSerieE_" . $contadorTabla]);
                        $incidencia->setFecha(date("Y-m-d"));
                        $incidencia->setFechaFin(date("Y-m-d"));
                        $incidencia->setDescripcion("El contador negro actual supero por más de 100,000 al anterior");
                        $incidencia->setStatus(1);
                        $incidencia->setClaveCentroCosto($parametros['slcLocalidad']);
                        $incidencia->setId_Ticket($obj->getIdTicket());
                        $incidencia->setIdTipoIncidencia(9);
                        $incidencia->setActivo(1);
                        $incidencia->setUsuarioCreacion($_SESSION['user']);
                        $incidencia->setUsuarioUltimaModificacion($_SESSION['user']);
                        $incidencia->setPantalla("Alta ticket");
                        if ($incidencia->newRegistro()) {
                            $mensajes1 .= "<br/>Se agrego una incidencia con el No.Serie " . $parametros["txtNoSerieE_" . $contadorTabla] . " porque el contador actual supera en más de 100,000 al anterior";
                        }
                    }
                    if (((int) $parametros['txtContadorColorAnterior_' . $contadorTabla] + 100000) < (int) $parametros['txtContadorColor_' . $contadorTabla]) {
                        $incidencia = new Incidencia();
                        $incidencia->setNoSerie($parametros["txtNoSerieE_" . $contadorTabla]);
                        $incidencia->setFecha(date("Y-m-d"));
                        $incidencia->setFechaFin(date("Y-m-d"));
                        $incidencia->setDescripcion("El contador color actual supero por más de 100,000 al anterior");
                        $incidencia->setStatus(1);
                        $incidencia->setClaveCentroCosto($parametros['slcLocalidad']);
                        $incidencia->setId_Ticket($obj->getIdTicket());
                        $incidencia->setIdTipoIncidencia(9);
                        $incidencia->setActivo(1);
                        $incidencia->setUsuarioCreacion($_SESSION['user']);
                        $incidencia->setUsuarioUltimaModificacion($_SESSION['user']);
                        $incidencia->setPantalla("Alta ticket");
                        if ($incidencia->newRegistro()) {
                            $mensajes1 .= "<br/>Se agrego una incidencia con el No.Serie " . $parametros["txtNoSerieE_" . $contadorTabla] . " porque el contador actual supera en más de 100,000 al anterior";
                        }
                    }
                    $pedido->setClaveEspEquipo($parametros["txtNoSerieE_" . $contadorTabla]);
                    $pedido->setModelo($parametros["txtModeloE_" . $contadorTabla]);
                    $notaRefaccion->setNoSerie($parametros["txtNoSerieE_" . $contadorTabla]);
                    $negro = 0;
                    $cian = 0;
                    $magenta = 0;
                    $amarillo = 0;

                    if (isset($parametros['activar_' . $contadorTabla]) && $parametros['activar_' . $contadorTabla] == "on") {
                        //if (isset($parametros['ckbNegro_' . $contadorTabla]) && $parametros['ckbNegro_' . $contadorTabla] == "on" || isset($parametros['ckbCian_' . $contadorTabla]) && $parametros['ckbCian_' . $contadorTabla] == "on" || isset($parametros['ckbMagenta_' . $contadorTabla]) && $parametros['ckbMagenta_' . $contadorTabla] == "on" || isset($parametros['ckbAmarillo_' . $contadorTabla]) && $parametros['ckbAmarillo_' . $contadorTabla] == "on") {
                        $lecturaTicket->setClaveEspEquipo($parametros["txtNoSerieE_" . $contadorTabla]);
                        $lecturaTicket->setModeloEquipo($parametros["txtModeloE_" . $contadorTabla]);
                        $lecturaTicket->setContadorBN($parametros['txtContadorNegro_' . $contadorTabla]);
                        $lecturaTicket->setNivelNegro($parametros['txtNivelNegro_' . $contadorTabla]);
                        if (isset($parametros['txtContadorColor_' . $contadorTabla])) {
                            $lecturaTicket->setContadorColor($parametros['txtContadorColor_' . $contadorTabla]);
                            $lecturaTicket->setNivelCia($parametros['txtNivelCian_' . $contadorTabla]);
                            $lecturaTicket->setNivelMagenta($parametros['txtNivelMagenta_' . $contadorTabla]);
                            $lecturaTicket->setNivelAmarillo($parametros['txtNivelAmarillo_' . $contadorTabla]);
                        } else {
                            $lecturaTicket->setContadorColor("");
                            $lecturaTicket->setNivelCia("");
                            $lecturaTicket->setNivelMagenta("");
                            $lecturaTicket->setNivelAmarillo("");
                        }
                        $lecturaTicket->setIdTicket($idTicket);

                        $lecturaTicket->setFechaA($parametros['txtfechaAnterior_' . $contadorTabla]);
                        $lecturaTicket->setContadorBNA($parametros['txtContadorNegroAnterior_' . $contadorTabla]);
                        $lecturaTicket->setNivelNegroA($parametros['txtNivelNegroAnterior_' . $contadorTabla]);
                        if (isset($parametros['txtContadorColorAnterior_' . $contadorTabla])) {
                            $lecturaTicket->setContadorColorA($parametros['txtContadorColorAnterior_' . $contadorTabla]);
                            $lecturaTicket->setNivelCiaA($parametros['txtNivelCianAnterior_' . $contadorTabla]);
                            $lecturaTicket->setNivelMagentaA($parametros['txtNivelMagentaAnterior_' . $contadorTabla]);
                            $lecturaTicket->setNivelAmarilloA($parametros['txtNivelAmarilloAnterior_' . $contadorTabla]);
                        } else {
                            $lecturaTicket->setContadorColorA("");
                            $lecturaTicket->setNivelCiaA("");
                            $lecturaTicket->setNivelMagentaA("");
                            $lecturaTicket->setNivelAmarilloA("");
                        }
                        $lecturaTicket->setComentario($parametros['comentario_' . $contadorTabla]);
                        $lecturaTicket->setActivo(1);
                        $lecturaTicket->setUsuarioCreacion($_SESSION['user']);
                        $lecturaTicket->setUsuarioUltimaModificacion($_SESSION['user']);
                        $lecturaTicket->setPantalla($pantalla);
                        $idLecturaEquipo = 0;
                        if ($lecturaTicket->NewRegistro()) {
                            $idLecturaEquipo = $lecturaTicket->getIdLectura();
                        } else {
                            
                        }

                        $color = false;
                        if ($caracteristicas->isColor($parametros["txtNoParteE_$contadorTabla"])) {
                            $color = true;
                        }

                        $rendimiento_negro = "";
                        $rendimiento_color = "";
                        $totalContadores = "";
                        $totalContadoresCL = "";

                        $message .= ("<tr><td style='border: 1px solid black;'>" . $lecturaTicket->getClaveEspEquipo() . " / " . $lecturaTicket->getModeloEquipo() . "</td>"
                                . "<td style='border: 1px solid black;'>" . number_format($lecturaTicket->getContadorBN()) . "</td>");
                        if ($hay_color) {
                            if ($color) {
                                $message .= "<td style='border: 1px solid black;'>" . number_format($lecturaTicket->getContadorColor()) . "</td>";
                            } else {
                                $message .= "<td style='border: 1px solid black;'></td>";
                            }
                        }
                        if ($lecturaTicket->getContadorBNA() != "") {
                            $totalContadores = $lecturaTicket->getContadorBN() - $lecturaTicket->getContadorBNA();
                            $message .= "<td style='border: 1px solid black;'>" . number_format($lecturaTicket->getContadorBNA()) . "</td>";
                        } else {
                            $message .= "<td style='border: 1px solid black;'></td>";
                        }
                        if ($hay_color) {
                            if ($color && $lecturaTicket->getContadorColorA() != "") {
                                $totalContadoresCL = $lecturaTicket->getContadorColor() - $lecturaTicket->getContadorColorA();
                                $message .= "<td style='border: 1px solid black;'>" . number_format($lecturaTicket->getContadorColorA()) . "</td>";
                            } else {
                                $message .= "<td style='border: 1px solid black;'></td>";
                            }
                        }


                        //Rendimiento
                        $rendimiento_message = "";
                        $componente = new Componente();

                        //Negro
                        if (isset($parametros['ckbNegro_' . $contadorTabla]) && $parametros['ckbNegro_' . $contadorTabla] == "on" &&
                                isset($parametros["txtTonerNegro$contadorTabla"]) && !empty($parametros["txtTonerNegro$contadorTabla"])) {
                            if ($componente->getRegistroById($parametros["txtTonerNegro$contadorTabla"])) {
                                if ($componente->getRendimiento() != "") {
                                    $rendimiento_negro = $componente->getRendimiento();
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero() . ": " . number_format($componente->getRendimiento());
                                } else {
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero();
                                }
                            }
                        }
                        //Amarillo
                        if (isset($parametros['ckbAmarillo_' . $contadorTabla]) && $parametros['ckbAmarillo_' . $contadorTabla] == "on" &&
                                isset($parametros["txtTonerAmarillo$contadorTabla"]) && !empty($parametros["txtTonerAmarillo$contadorTabla"])) {
                            if ($componente->getRegistroById($parametros["txtTonerAmarillo$contadorTabla"])) {
                                if ($componente->getRendimiento() != "") {
                                    $rendimiento_color = $componente->getRendimiento();
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero() . ": " . number_format($componente->getRendimiento());
                                } else {
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero();
                                }
                            }
                        }
                        //Magenta
                        if (isset($parametros['ckbMagenta_' . $contadorTabla]) && $parametros['ckbMagenta_' . $contadorTabla] == "on" &&
                                isset($parametros["txtTonerMagenta$contadorTabla"]) && !empty($parametros["txtTonerMagenta$contadorTabla"])) {
                            if ($componente->getRegistroById($parametros["txtTonerMagenta$contadorTabla"])) {
                                if ($componente->getRendimiento() != "") {
                                    $rendimiento_color = $componente->getRendimiento();
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero() . ": " . number_format($componente->getRendimiento());
                                } else {
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero();
                                }
                            }
                        }
                        //Cian
                        if (isset($parametros['ckbCian_' . $contadorTabla]) && $parametros['ckbCian_' . $contadorTabla] == "on" &&
                                isset($parametros["txtTonerCian$contadorTabla"]) && !empty($parametros["txtTonerCian$contadorTabla"])) {
                            if ($componente->getRegistroById($parametros["txtTonerCian$contadorTabla"])) {
                                if ($componente->getRendimiento() != "") {
                                    $rendimiento_color = $componente->getRendimiento();
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero() . ": " . number_format($componente->getRendimiento());
                                } else {
                                    $rendimiento_message .= "<br/><b>(1) " . $componente->getModelo() . "</b>/" . $componente->getNumero();
                                }
                            }
                        }

                        $message .= "<td style='border: 1px solid black;'>$rendimiento_message</td>";

                        $texto = "";
                        if (!empty($rendimiento_negro) && !empty($totalContadores)) {
                            $texto = "(" . number_format(($totalContadores * 100) / $rendimiento_negro) . "%)";
                        }
                        $message .= "<td style='border: 1px solid black;'>" . number_format($lecturaTicket->getContadorBN() - $lecturaTicket->getContadorBNA()) . " $texto</td>";

                        if ($hay_color) {
                            if ($color && $lecturaTicket->getContadorColorA() != "") {
                                $texto = "";
                                if (!empty($rendimiento_color) && !empty($totalContadoresCL)) {
                                    $texto = "(" . number_format(($totalContadoresCL * 100) / $rendimiento_color) . "%)";
                                }
                                $message .= "<td style='border: 1px solid black;'>" . number_format($lecturaTicket->getContadorColor() - $lecturaTicket->getContadorColorA()) . " $texto</td>";
                            } else {
                                $message .= "<td style='border: 1px solid black;'></td>";
                            }
                        }
                        $message .= "</tr>";

                        $pedido->setIdLecturaTicket($idLecturaEquipo);
                        if (isset($parametros['activar_' . $contadorTabla]) && $parametros['activar_' . $contadorTabla] == "on") {
                            if (isset($parametros['ckbNegro_' . $contadorTabla]) && $parametros['ckbNegro_' . $contadorTabla] == "on" && isset($parametros["txtTonerNegro" . $contadorTabla])) {
                                $negro = 1;
                                $notaRefaccion->setNoParte($parametros["txtTonerNegro" . $contadorTabla]);
                                if ($notaRefaccion->newRegistroSerie()) {//agregar en detalle nota refaccion
                                    if (!$notaRefaccion->newRegistroDetalle()) {
                                        echo "<br/>Error: El detalle no se agregó correctamente";
                                    }
                                } else {
                                    if ($notaRefaccion->VerificarExistencia()) {
                                        if ($notaRefaccion->editarCantidadTonerRepetido()) {
                                            if (!$notaRefaccion->newRegistroDetalle()) {
                                                echo "<br/>Error: El detalle no se agregó correctamente";
                                            }
                                        } else {
                                            echo "<br/>Error: El toner no se registró correctamente";
                                        }
                                    } else
                                        echo "<br/>Error: >El toner no se registró correctamente";
                                }
                            }

                            if (isset($parametros['ckbCian_' . $contadorTabla]) && $parametros['ckbCian_' . $contadorTabla] == "on" && isset($parametros["txtTonerCian" . $contadorTabla])) {
                                $cian = 1;
                                $notaRefaccion->setNoParte($parametros["txtTonerCian" . $contadorTabla]);
                                if ($notaRefaccion->newRegistroSerie()) {
                                    if (!$notaRefaccion->newRegistroDetalle()) {
                                        echo "<br/>Error: El detalle no se agregó correctamente";
                                    }
                                } else {
                                    if ($notaRefaccion->VerificarExistencia()) {
                                        if ($notaRefaccion->editarCantidadTonerRepetido()) {
                                            if (!$notaRefaccion->newRegistroDetalle()) {
                                                echo "<br/>Error: El detalle no se agregó correctamente";
                                            }
                                        } else {
                                            echo "<br/>Error: El toner no se registró correctamente";
                                        }
                                    } else {
                                        echo "<br/>El toner no se registró correctamente";
                                    }
                                }
                            }

                            if (isset($parametros['ckbMagenta_' . $contadorTabla]) && $parametros['ckbMagenta_' . $contadorTabla] == "on" && isset($parametros["txtTonerMagenta" . $contadorTabla])) {
                                $magenta = 1;
                                $notaRefaccion->setNoParte($parametros["txtTonerMagenta" . $contadorTabla]);
                                if ($notaRefaccion->newRegistroSerie()) {
                                    if (!$notaRefaccion->newRegistroDetalle()) {
                                        echo "<br/>Error: El detalle no se agregó correctamente";
                                    }
                                } else {
                                    if ($notaRefaccion->VerificarExistencia()) {
                                        if ($notaRefaccion->editarCantidadTonerRepetido()) {
                                            if (!$notaRefaccion->newRegistroDetalle()) {
                                                echo "<br/>Error: El detalle no se agregó correctamente";
                                            }
                                        } else {
                                            echo "<br/>Error: El toner no se registró correctamente";
                                        }
                                    } else {
                                        echo "<br/>Error: El toner no se registró correctamente";
                                    }
                                }
                            }
                            if (isset($parametros['ckbAmarillo_' . $contadorTabla]) && $parametros['ckbAmarillo_' . $contadorTabla] == "on" && isset($parametros["txtTonerAmarillo" . $contadorTabla])) {
                                $amarillo = 1;
                                $notaRefaccion->setNoParte($parametros["txtTonerAmarillo" . $contadorTabla]);
                                if ($notaRefaccion->newRegistroSerie()) {
                                    if (!$notaRefaccion->newRegistroDetalle()) {
                                        echo "<br/>Error: El detalle no se agregó correctamente";
                                    }
                                } else {
                                    if ($notaRefaccion->VerificarExistencia()) {
                                        if ($notaRefaccion->editarCantidadTonerRepetido()) {
                                            if (!$notaRefaccion->newRegistroDetalle()) {
                                                echo "<br/>Error: El detalle no se agregó correctamente";
                                            }
                                        } else {
                                            echo "<br/>Error: El toner no se registro correctamente";
                                        }
                                    } else
                                        echo "<br/>Error: El toner no se registro correctamente";
                                }
                            }
                        }
                        $pedido->setTonerNegro($negro);
                        $pedido->setTonerCian($cian);
                        $pedido->setTonerMagenta($magenta);
                        $pedido->setTonerAmarillo($amarillo);
                        if (!$pedido->newRegistro()) {
                            echo "<br/>Error: EL pedido no se registró correctamente";
                        }
                    }//fin if
                    $contadorTabla++;
                }//fin while
                $message .= "</tbody></table>";
                if (isset($_GET['pendiente_autorizar']) && $_GET['pendiente_autorizar'] == "1") {
                    $mail = new Mail();
                    $mail->setFrom($correo_emisor);
                    $mail->setSubject("Solicitud de cambio de tóner del ticket " . $idTicket . " del cliente " . $obj->getNombreCliente());

                    // Guardamos y creamos la liga para aceptar/rechazar la solicitud directamente 
                    $clave = $mail->generaPass();
                    $idMail = $catalogo->insertarRegistro("INSERT INTO c_mailpedidotoner(idTicket, contestada, clave, IdUsuario, Activo, UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                                            VALUES(" . $idTicket . ",0,MD5('$clave'),2,1,'" . $_SESSION['user'] . "',now(),'" . $_SESSION['user'] . "',now(),'nuevo ticket.php');");

                    $liga = "$url/aceptarPedidoToner.php?clv=$clave&idTicket=" . $idTicket . "&idMail=$idMail&idNota=" . $notaTicket->getIdNota() . "&tipo";
                    $message .= "<br/>Autorizar solicitud: " . $liga . "=1&uguid=" . $_SESSION['idEmpresa'] . " <br/><br/>";
                    $message .= "<br/>Rechazar solicitud: " . $liga . "=3&uguid=" . $_SESSION['idEmpresa'] . " <br/><br/>";
                    $mail->setBody($message);
                    $consultaCorreos = "SELECT correo FROM c_correossolicitud WHERE TipoSolicitud = 20 AND Activo = 1;";
                    $resultCorreos = $catalogo->obtenerLista($consultaCorreos);
                    while ($rsCorreo = mysql_fetch_array($resultCorreos)) {
                        $value = $rsCorreo['correo'];
                        if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $mail->setTo($value);
                            if ($mail->enviarMail() == "1") {
                                // echo "Un correo fue enviado para la autorización.";
                            } else {
                                echo "<br/>No se envió correo de autorización de cambio d etoner a $value.<br/>";
                            }
                        }
                    }
                } else {
                    //copiar la nota 
                    $solicitudToner->setNotaAnterior($notaTicket->getIdNota());
                    $solicitudToner->setIdEstadoNota(65);
                    $solicitudToner->setMostrarCliente(0);
                    $notaTicket->setDiagnostico("Solicitud de toner");
                    $solicitudToner->setUsuarioCreacion($_SESSION['user']);
                    $solicitudToner->setUsuarioModificacion($_SESSION['user']);
                    $solicitudToner->setPantalla($pantalla);
                    if ($solicitudToner->newNotaSolicitudTonerTicket()) {
                        if ($solicitudToner->copyTonerNota()) {
                            
                        }
                    }
                }

                $obj->setIdTicket($idTicket);
                if ($obj->editTicketDescripcion()) {
                    echo $idTicket;
                } else {
                    echo "<br/>Error: EL tiquet <b>" . $obj->getIdTicket() . "</b> no se modificó correctamente";
                }
            } else {
                echo "<br/>Error: La nota no se agregó correctamente";
            }
        } else {
            $obj->setIdTicket($idTicket);
            if ($obj->editTicketDescripcion()) {
                echo $idTicket;
            } else {
                echo "<br/>Error: EL tiquet <b>" . $obj->getIdTicket() . "</b> no se modificó correctamente";
            }
        }
    } else {
        $obj->setIdTicket($idTicket);
        list($fila, $noSerie, $modelo) = explode(" / ", $equipo);
        if ($parametros['sltTipoReporte'] != "15") {
            $lecturaTicket->setIdTicket($idTicket);
            $lecturaTicket->deleteRegitro();
            if ($obj->EditarEquipoTicket()) {
                if (isset($parametros['ubicacionNoDomicilio']) && $parametros['ubicacionNoDomicilio'] != "") {//cambiar la ubicacion del equipo
                    if ($obj->actualizarUbicacion($parametros["txtNoSerieE_" . $fila], $parametros['ubicacionNoDomicilio'])) {
                        //echo "ubicacion modificadad";
                    }
                }
                //Guardamos una incidencia en caso de que el contador actual supere en más de 100,000 al contadorAnterior
                if (((int) $parametros['txtContadorNegroAnterior_' . $fila] + 100000) < (int) $parametros['txtContadorNegro_' . $fila]) {
                    $incidencia = new Incidencia();
                    $incidencia->setNoSerie($parametros["txtNoSerieE_" . $fila]);
                    $incidencia->setFecha(date("Y-m-d"));
                    $incidencia->setFechaFin(date("Y-m-d"));
                    $incidencia->setDescripcion("El contador negro actual supero por más de 100,000 al anterior");
                    $incidencia->setStatus(1);
                    $incidencia->setClaveCentroCosto($parametros['slcLocalidad']);
                    $incidencia->setId_Ticket($obj->getIdTicket());
                    $incidencia->setIdTipoIncidencia(9);
                    $incidencia->setActivo(1);
                    $incidencia->setUsuarioCreacion($_SESSION['user']);
                    $incidencia->setUsuarioUltimaModificacion($_SESSION['user']);
                    $incidencia->setPantalla("Alta ticket");
                    if ($incidencia->newRegistro()) {
                        $mensajes1 .= "<br/>Se agrego una incidencia con el No.Serie " . $parametros["txtNoSerieE_" . $fila] . " porque el contador actual supera en más de 100,000 al anterior";
                    }
                }
                if (((int) $parametros['txtContadorColorAnterior_' . $fila] + 100000) < (int) $parametros['txtContadorColor_' . $fila]) {
                    $incidencia = new Incidencia();
                    $incidencia->setNoSerie($parametros["txtNoSerieE_" . $fila]);
                    $incidencia->setFecha(date("Y-m-d"));
                    $incidencia->setFechaFin(date("Y-m-d"));
                    $incidencia->setDescripcion("El contador color actual supero por más de 100,000 al anterior");
                    $incidencia->setStatus(1);
                    $incidencia->setClaveCentroCosto($parametros['slcLocalidad']);
                    $incidencia->setId_Ticket($obj->getIdTicket());
                    $incidencia->setIdTipoIncidencia(9);
                    $incidencia->setActivo(1);
                    $incidencia->setUsuarioCreacion($_SESSION['user']);
                    $incidencia->setUsuarioUltimaModificacion($_SESSION['user']);
                    $incidencia->setPantalla("Alta ticket");
                    if ($incidencia->newRegistro()) {
                        $mensajes1 .= "<br/>Se agrego una incidencia con el No.Serie " . $parametros["txtNoSerieE_" . $fila] . " porque el contador actual supera en más de 100,000 al anterior";
                    }
                }
                $lecturaTicket->setClaveEspEquipo($parametros["txtNoSerieE_" . $fila]);
                $lecturaTicket->setModeloEquipo($parametros["txtModeloE_" . $fila]);
                $lecturaTicket->setContadorBN($parametros['txtContadorNegro_' . $fila]);
                if (isset($parametros['txtContadorColor_' . $fila])) {
                    $lecturaTicket->setContadorColor($parametros['txtContadorColor_' . $fila]);
                } else {
                    $lecturaTicket->setContadorColor("");
                }
                $lecturaTicket->setNivelNegro("");
                $lecturaTicket->setNivelCia("");
                $lecturaTicket->setNivelMagenta("");
                $lecturaTicket->setNivelAmarillo("");
                $lecturaTicket->setIdTicket($idTicket);
                $lecturaTicket->setFechaA($parametros['txtfechaAnterior_' . $fila]);
                $lecturaTicket->setContadorBNA($parametros['txtContadorNegroAnterior_' . $fila]);
                if (isset($parametros['txtContadorColorAnterior_' . $fila])) {
                    $lecturaTicket->setContadorColorA($parametros['txtContadorColorAnterior_' . $fila]);
                } else {
                    $lecturaTicket->setContadorColorA("");
                }
                $lecturaTicket->setNivelNegroA("");
                $lecturaTicket->setNivelCiaA("");
                $lecturaTicket->setNivelMagentaA("");
                $lecturaTicket->setNivelAmarilloA("");
                $lecturaTicket->setComentario($parametros['comentario_' . $fila]);
                $lecturaTicket->setActivo(1);
                $lecturaTicket->setUsuarioCreacion($_SESSION['user']);
                $lecturaTicket->setUsuarioUltimaModificacion($_SESSION['user']);
                $lecturaTicket->setPantalla($pantalla);
                $idLecturaEquipo = 0;
                if ($lecturaTicket->NewRegistro()) {
                    echo $idTicket;
                } else {
                    echo "<br/>Error: La lectura del equipo no se modificó correctamente";
                }
            } else {
                echo "<br/>Error: El ticket <b>" . $idTicket . "</b> no se modificó correctamente, reportarlo con el administrador por favor.";
                echo $mensajes1;
            }
        }
    }
}
?>