<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/Catalogo.class.php");
include_once("../Classes/Ticket.class.php");
include_once("../Classes/LecturaTicket.class.php");
include_once("../Classes/Mail.class.php");
include_once("../Classes/NotaTicket.class.php");
include_once("../Classes/ParametroGlobal.class.php");
include_once("../Classes/Parametros.class.php");

$parametroGlobal = new ParametroGlobal();
$catalogo = new Catalogo();
$ticket = new Ticket();
$lecturaTicket = new LecturaTicket();
$mail = new Mail();
$notaTicket = new NotaTicket();
if (isset($_POST['buscar']) && $_POST['buscar'] == "BuuscarByNoSerie") {
    $noSerie = $_POST['NoSerie'];
    $tipo = $_POST['tipo'];
    $idTicket = $ticket->BuscarTicketAbierto($noSerie, $tipo);
    echo $idTicket;
} else if (isset($_POST['buscar']) && $_POST['buscar'] == "LecturaNoSerie") {
    $noSerie = $_POST['NoSerie'];
    $lecturaTicket->setNoSerie($noSerie);
    $lecturaTicket->getLecturaTonerBNColorBYNoSerie();
    $cadena = $lecturaTicket->getContadorBNA() . " / " . $lecturaTicket->getContadorColorA() . " / " . $lecturaTicket->getNivelNegroA() . " / " . $lecturaTicket->getNivelCiaA() . " / " . $lecturaTicket->getNivelMagentaA() . " / " . $lecturaTicket->getNivelAmarilloA() . " / " . $lecturaTicket->getFechaA();
    $lecturaTicket2 = new LecturaTicket();
    $lecturaTicket2->setNoSerie($noSerie);
    $lecturaTicket2->getUltimaLecturaCorte();
    if ($lecturaTicket2->getContadorBNA() != null && $lecturaTicket2->getContadorBNA() != "") {
        $cadena.= " / " . $lecturaTicket2->getContadorBNA();
    } else {
        $cadena.= " /  ";
    }
    if ($lecturaTicket2->getContadorColorA() != null && $lecturaTicket2->getContadorColorA() != "") {
        $cadena.= " / " . $lecturaTicket2->getContadorColorA();
    } else {
        $cadena.= " /  ";
    }
    $lecturaTicket3 = new LecturaTicket();
    $lecturaTicket3->setNoSerie($noSerie);
    $lecturaTicket3->getUltimaLecturaCambioToner();
    if ($lecturaTicket3->getContadorBNA() != null && $lecturaTicket3->getContadorBNA() != "") {
        $cadena.= " / " . $lecturaTicket3->getContadorBNA();
    } else {
        $cadena.= " /  ";
    }
    if ($lecturaTicket3->getContadorColorA() != null && $lecturaTicket3->getContadorColorA() != "") {
        $cadena.= " / " . $lecturaTicket3->getContadorColorA();
    } else {
        $cadena.= " /  ";
    }
    echo $cadena;
} else if (isset($_POST['buscar']) && $_POST['buscar'] == "BuscarXdia") {
    $noSerie = $_POST['NoSerie'];
    $tipo = $_POST['tipo'];
    $diasTicket = $ticket->BuscarTicketXdia($noSerie, $tipo);
    echo $diasTicket;
} else if (isset($_POST['buscar']) && $_POST['buscar'] == "BuscarCliente") {
    $claveCliente = $_POST['cliente'];
    $estatus = "";
    $suspendido = "";
    $queryCliente = $catalogo->obtenerLista("SELECT c.IdEstatusCobranza,c.Suspendido,c.NombreRazonSocial,IdTipoMorosidad 
        FROM c_cliente c WHERE c.ClaveCliente='$claveCliente'");
    $parametros = new Parametros();
    $permitir_moroso = "0";

    if ($parametros->getRegistroById("14")) {
        $permitir_moroso = $parametros->getValor();
    }

    while ($rs = mysql_fetch_array($queryCliente)) {
        if ($rs['IdTipoMorosidad'] == 1 || $rs['IdTipoMorosidad'] == "") {
            $estatus = $rs['IdEstatusCobranza'];
        } else {
            $estatus = 1;
        }

        $suspendido = $rs['Suspendido'];
        $nombre = $rs['NombreRazonSocial'];
    }

    echo $suspendido . " / " . $estatus . " / " . $nombre . " / " . $permitir_moroso;
} else if (isset($_POST['buscar']) && $_POST['buscar'] == "BuxcarLocalidad") {
    $consulta = "SELECT cc.ClaveCentroCosto,cc.Nombre,ml.IdAlmacen
    FROM c_centrocosto cc 
    INNER JOIN k_minialmacenlocalidad ml ON ml.ClaveCentroCosto=cc.ClaveCentroCosto
    INNER JOIN c_almacen a ON a.id_almacen = ml.IdAlmacen
    WHERE cc.ClaveCentroCosto='" . $_POST['localidad'] . "' AND a.Activo = 1;";
    $queryCliente = $catalogo->obtenerLista($consulta);
    if (mysql_num_rows($queryCliente) > 0) {
        while ($rs = mysql_fetch_array($queryCliente)) {
            $localidad = $rs['ClaveCentroCosto'];
            $nombre = $rs['Nombre'];
            $almacen = $rs['IdAlmacen'];
        }
        echo $localidad . " / " . $nombre . " / " . $almacen;
    } else {
        echo "";
    }
} else if (isset($_POST['buscar']) && $_POST['buscar'] == "BuscarDatosPorSerieFalla") {
    $parametros = new Parametros();
    $permitir_moroso = "0";

    if ($parametros->getRegistroById("14")) {
        $permitir_moroso = $parametros->getValor();
    }

    $queryCliente = $catalogo->obtenerLista("SELECT ie.NoSerie,(SELECT CASE WHEN ISNULL(ie.IdKserviciogimgfa) 
                                            THEN (SELECT c.Suspendido FROM k_anexoclientecc an,c_centrocosto cc,c_cliente c WHERE cc.ClaveCentroCosto=an.CveEspClienteCC AND an.IdAnexoClienteCC=ie.IdAnexoClienteCC AND cc.ClaveCliente=c.ClaveCliente)
                                            ELSE (SELECT c.Suspendido FROM c_centrocosto cc,k_serviciogimgfa sg,c_cliente c WHERE sg.IdKserviciogimgfa=ie.IdKserviciogimgfa AND sg.ClaveCentroCosto=cc.ClaveCentroCosto  AND cc.ClaveCliente=c.ClaveCliente)END )AS suspendido,
                                            (SELECT CASE WHEN ISNULL(ie.IdKserviciogimgfa) 
                                            THEN (SELECT c.IdEstatusCobranza FROM k_anexoclientecc an,c_centrocosto cc,c_cliente c WHERE cc.ClaveCentroCosto=an.CveEspClienteCC AND an.IdAnexoClienteCC=ie.IdAnexoClienteCC AND cc.ClaveCliente=c.ClaveCliente)
                                            ELSE (SELECT c.IdEstatusCobranza FROM c_centrocosto cc,k_serviciogimgfa sg,c_cliente c WHERE sg.IdKserviciogimgfa=ie.IdKserviciogimgfa AND sg.ClaveCentroCosto=cc.ClaveCentroCosto  AND cc.ClaveCliente=c.ClaveCliente)END )AS estatus,
                                            (SELECT CASE WHEN ISNULL(ie.IdKserviciogimgfa) 
                                            THEN (SELECT c.NombreRazonSocial FROM k_anexoclientecc an,c_centrocosto cc,c_cliente c WHERE cc.ClaveCentroCosto=an.CveEspClienteCC AND an.IdAnexoClienteCC=ie.IdAnexoClienteCC AND cc.ClaveCliente=c.ClaveCliente)
                                            ELSE (SELECT c.NombreRazonSocial FROM c_centrocosto cc,k_serviciogimgfa sg,c_cliente c WHERE sg.IdKserviciogimgfa=ie.IdKserviciogimgfa AND sg.ClaveCentroCosto=cc.ClaveCentroCosto  AND cc.ClaveCliente=c.ClaveCliente)END )AS nombre,
                                            (SELECT CASE WHEN ISNULL(ie.IdKserviciogimgfa) 
                                            THEN (SELECT c.ClaveCliente FROM k_anexoclientecc an,c_centrocosto cc,c_cliente c WHERE cc.ClaveCentroCosto=an.CveEspClienteCC AND an.IdAnexoClienteCC=ie.IdAnexoClienteCC AND cc.ClaveCliente=c.ClaveCliente)
                                            ELSE (SELECT c.ClaveCliente FROM c_centrocosto cc,k_serviciogimgfa sg,c_cliente c WHERE sg.IdKserviciogimgfa=ie.IdKserviciogimgfa AND sg.ClaveCentroCosto=cc.ClaveCentroCosto  AND cc.ClaveCliente=c.ClaveCliente)END )AS clavecliente
                                            FROM c_inventarioequipo ie WHERE  ie.NoSerie='" . $_POST['NoSerie'] . "' ");
    while ($rs = mysql_fetch_array($queryCliente)) {
        $suspendido = $rs['suspendido'];
        $tipoCliente = $rs['estatus'];
        $nombreCliente = $rs['nombre'];
        $claveCliente = $rs['clavecliente'];
    }
    $noSerie = $_POST['NoSerie'];
    $idTicket = $ticket->BuscarTicketAbierto($noSerie, "1");
    $diasTicket = $ticket->BuscarTicketXdia($noSerie, "1");
    echo $suspendido . " / " . $tipoCliente . " / " . $idTicket . " / " . $diasTicket . " / " . $nombreCliente . " / " . $claveCliente . " / " . $permitir_moroso;
} else if (isset($_POST['buscar']) && $_POST['buscar'] == "BuscarDatosPorSerieVentaDirecta") {
    $queryCliente = $catalogo->obtenerLista("SELECT b.VentaDirecta FROM c_bitacora b  WHERE  b.NoSerie='" . $_POST['NoSerie'] . "'");
    $queryClienteCorreo = $catalogo->obtenerLista("SELECT ie.Demo,
        (SELECT CASE WHEN ISNULL(ie.IdKserviciogimgfa) THEN (SELECT c.NombreRazonSocial FROM k_anexoclientecc an,c_centrocosto cc,c_cliente c WHERE cc.ClaveCentroCosto=an.CveEspClienteCC AND an.IdAnexoClienteCC=ie.IdAnexoClienteCC AND cc.ClaveCliente=c.ClaveCliente)ELSE (SELECT c.NombreRazonSocial FROM c_centrocosto cc,k_serviciogimgfa sg,c_cliente c WHERE sg.IdKserviciogimgfa=ie.IdKserviciogimgfa AND sg.ClaveCentroCosto=cc.ClaveCentroCosto  AND cc.ClaveCliente=c.ClaveCliente)END )AS nombre,
        (SELECT CASE WHEN ISNULL(ie.IdKserviciogimgfa) THEN (SELECT c.EjecutivoCuenta FROM k_anexoclientecc an,c_centrocosto cc,c_cliente c WHERE cc.ClaveCentroCosto=an.CveEspClienteCC AND an.IdAnexoClienteCC=ie.IdAnexoClienteCC AND cc.ClaveCliente=c.ClaveCliente)ELSE (SELECT c.EjecutivoCuenta FROM c_centrocosto cc,k_serviciogimgfa sg,c_cliente c WHERE sg.IdKserviciogimgfa=ie.IdKserviciogimgfa AND sg.ClaveCentroCosto=cc.ClaveCentroCosto  AND cc.ClaveCliente=c.ClaveCliente)END )AS ejecutivo                                           
        FROM c_inventarioequipo ie WHERE ie.NoSerie='" . $_POST['NoSerie'] . "'");
    while ($rs = mysql_fetch_array($queryCliente)) {
        $ventaDirecta = $rs['VentaDirecta'];
    }
    while ($rs = mysql_fetch_array($queryClienteCorreo)) {
        $cliente = $rs['nombre'];
        $ejecutivo = $rs['ejecutivo'];
        if ($rs['Demo'] == "1") {
            $ventaDirecta = "-1";
        }
    }
    echo $ventaDirecta . " // " . $cliente . " // " . $ejecutivo;
} else if (isset($_POST['buscar']) && $_POST['buscar'] == "EnviarMailCotizacionVD") {
    $noSerie = $_POST['noSerie'];
    $queryClienteCorreo = $catalogo->obtenerLista("SELECT u.correo,CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS nombre  FROM c_usuario u WHERE u.IdUsuario='" . $_POST['ejecutivo'] . "'");
    while ($rs = mysql_fetch_array($queryClienteCorreo)) {
        $correo = $rs['correo'];
        $nombreEjecutivo = $rs['nombre'];
    }
    if (isset($correo) && $correo != "" && filter_var($correo, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
        if ($parametroGlobal->getRegistroById("8")) {
            $mail->setFrom($parametroGlobal->getValor());
        } else {
            $mail->setFrom("scg-salida@scgenesis.mx");
        }
        $mail->setSubject("Solicitud de cotizacion para cliente: " . $_POST['cliente']);
        $message = "El cliente " . $_POST['cliente'] . " solicitó una cotización para el equipo: <b>" . $noSerie . "</b> ";
        $mail->setBody($message);
        $mail->setTo($correo);
        if ($mail->enviarMail() == "1") {
            //echo "El correo del ejecutivo <b>" . $nombreEjecutivo . "</b> del cliente <b>" . $_POST['cliente'] . "</b> es incorrecto favor de revisar";
            echo "Se envio un correo de aviso al ejecutivo <b>" . $nombreEjecutivo . "</b> .";
        } else {
            echo "Error: El correo no se pudo enviar.";
        }
    } else {
        echo "El correo del ejecutivo <b>" . $nombreEjecutivo . "</b> del cliente <b>" . $_POST['cliente'] . "</b> es incorrecto favor de revisar";
    }
} else if (isset($_POST['buscar']) && $_POST['buscar'] == "cambiarNotaMostrar") {
    $idNota = $_POST['idNota'];
    $mostrar = $_POST['mostrar'];
    if ($notaTicket->updateNotaMostrarCliente($mostrar, $idNota)) {
        echo "La nota se modificó correctamente";
    } else {
        echo "Error: La nota no se modificó correctamente";
    }
} else if (isset($_POST['buscar']) && $_POST['buscar'] == "notaTicketAbierto") {
    if (isset($_POST['idEstatus']) && $_POST['idEstatus'] == "59") {//Para cancelar, no se valida
        echo 0;
        return;
    }
    $idTicket = $_POST['idTicket'];
    $query = $catalogo->obtenerLista("SELECT MAX(IdNotaTicket) AS IdNotaTicket FROM `c_notaticket` WHERE IdEstatusAtencion = 67 AND IdTicket = $idTicket;");
    $idNota = 0;
    while ($rs = mysql_fetch_array($query)) {
        $idNota = $rs['IdNotaTicket'];
    }
    if ($idNota == 0) {
        $query = $catalogo->obtenerLista("SELECT MAX(IdNotaTicket) AS IdNotaTicket FROM `c_notaticket` WHERE IdEstatusAtencion = 9 AND IdTicket = $idTicket;");
        while ($rs = mysql_fetch_array($query)) {
            $idNota = $rs['IdNotaTicket'];
        }
        if ($idNota == 0) {
            echo 0;
        } else {
            include_once("../Classes/EntregaRefaccion.class.php");
            $obj = new EntregaRefaccion();
            $obj->getCantidadSolicitadaBYticket($idNota);
            $obj->getCantidadSurtidaByticket($idNota);
            $totalSolicitada = $obj->getCantidadTotalSolicitada();
            $totalSurtidas = $obj->getCantidadTotalSurtida();
            if (intval($totalSurtidas) >= intval($totalSolicitada)) {
                echo 0;
            } else {
                //echo 1;
                echo "refacciones, faltan por atender:";
                $partesSolicitadas = $obj->getPartesSolicitadas();
                $partesAtendidas = $obj->getPartesEntregadas();
                foreach ($partesSolicitadas as $key => $value) {
                    if (!isset($partesAtendidas[$key])) {
                        echo "<br/>$value - $key";
                    } else {
                        if ($partesSolicitadas[$key] > $partesAtendidas[$key]) {
                            echo "<br/>" . ($partesSolicitadas[$key] - $partesAtendidas[$key]) . " - $key";
                        }
                    }
                }
            }
            //}
        }
    } else {

        include_once("../Classes/SolicitudToner.class.php");

        $obj = new SolicitudToner();
        $obj->getCantidadSolicitadaBYticket($idNota);
        $obj->getCantidadSurtidaByticket($idNota);
        $totalSolicitada = $obj->getCantidadTotalSolicitada();
        $totalSurtidas = $obj->getCantidadTotalSurtida();
        if (intval($totalSurtidas) >= intval($totalSolicitada)) {
            echo 0;
        } else {
            //echo 2;
            echo "tóner, faltan por atender:";
            $partesSolicitadas = $obj->getPartesSolicitadas();
            $partesAtendidas = $obj->getPartesEntregadas();
            foreach ($partesSolicitadas as $key => $value) {
                if (!isset($partesAtendidas[$key])) {
                    echo "<br/>$value - $key";
                } else {
                    if ($partesSolicitadas[$key] > $partesAtendidas[$key]) {
                        echo "<br/>" . ($partesSolicitadas[$key] - $partesAtendidas[$key]) . " - $key";
                    }
                }
            }
        }
        //}
    }
}
?>
