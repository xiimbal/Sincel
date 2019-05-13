<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/MovimientoComponente.class.php");
include_once("../Classes/LecturaTicket.class.php");
include_once("../Classes/AlmacenConmponente.class.php");
include_once("../Classes/AlmacenComponenteTicket.class.php");
include_once("../Classes/ResurtidoToner.class.php");
include_once("../Classes/Ticket.class.php");
include_once("../Classes/Pedido.class.php");
include_once("../Classes/NotaTicket.class.php");
include_once("../Classes/NotaRefaccion.class.php");
include_once("../Classes/Mail.class.php");
include_once("../Classes/Usuario.class.php");
include_once("../Classes/Catalogo.class.php");
include_once("../Classes/ParametroGlobal.class.php");

$parametroGlobal = new ParametroGlobal();
if($parametroGlobal->getRegistroById("8")){
    $correo_emisor = ($parametroGlobal->getValor());
}else{
    $correo_emisor = ("scg-salida@scgenesis.mx");
}
$obj = new MovimientoComponente();
$lecturaTicket = new LecturaTicket();
$almacenComponente = new AlmacenComponente();
$resurtidoToner = new ResurtidoToner();
$ticket = new Ticket();
$pedido1 = new Pedido();
$notaTicket = new NotaTicket();
$notaRefaccion = new NotaRefaccion();
$mail = new Mail();
$usuario = new Usuario();
$catalogo = new Catalogo();
$idNotaTicket = "";
$idTicketNuevo = "";
if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}
$obj->setIdTicket("");
$obj->setIdNotaTicket("");
$toner = $parametros['toner'];
list($noParte, $descripcionToner) = explode(" // ", $toner);
$obj->setNoParteComponente($noParte);
$obj->setCantidadMovimiento($parametros['cantidadToner']);
$obj->setIdAlmacenAnterior($parametros['almacen']);
$obj->setIdAlmacenNuevo("");
$obj->setClaveClienteAnterior("");
$obj->setClaveClienteNuevo($parametros['cliente']);
$obj->setUsuarioCreacion($_SESSION['user']);
$obj->setUsuarioModificacion($_SESSION['user']);
$obj->setPantalla("Solicitud de toner del mini almacén");
$obj->setEntradaSalida(1);
$obj->setClaveCentroCostoNuevo($parametros['Localidad']);
list($noParteLista) = explode(" / ", $parametros['noSerie']);
/*print_r($parametros);
echo "Error ".$parametros['noSerie'];
print_r("Uno ".$parametros['noSerie']);
print_r("<br/>Dos ".$noParteLista);*/
$obj->setNoSerieEquipoNuevo($parametros['noSerie2']);
$idLecturaTicket = 0;
$idMovimiento = "";
$nombreLocalidad = "";
$modeloComponenteConsulta = "";
$almacen = $parametros['almacen'];

if ($almacenComponente->verificarExistenciaAlmacen($noParte, $parametros['almacen'])) {//verificar cantidad existente
    if ($obj->newRegistro()) {
        $idMovimiento = $obj->getIdMovimiento();
        // echo "La solicitud se atendio exitosamente";
        $almacenComponente->setCantidadSalida($parametros['cantidadToner']);
        $almacenComponente->setNoParte($noParte);
        $almacenComponente->setIdAlmacen($parametros['almacen']);
        if ($almacenComponente->editarCantidadAlmacen()) {
            //enviar correo si el toner llego a 0     
            $existenteAlamcen = $almacenComponente->TonerExistentesAlamcen();
            if ($existenteAlamcen == "0") {
//                if ($resurtidoToner->verificarResurtidoByAlamcen($parametros['almacen'])) {//verificar si ya existe resurtido   
                $mail->setFrom($correo_emisor);
                $mail->setSubject("Existencia de toner en almacén");
                $nombreAlmacen = "";
                $queryAlamcen = $catalogo->obtenerLista("SELECT al.nombre_almacen,c.Modelo FROM c_almacen al,c_componente c,k_almacencomponente ac  
                                                            WHERE al.id_almacen='$almacen' AND c.NoParte='$noParte' AND al.id_almacen=ac.id_almacen AND c.NoParte=ac.NoParte ");
                while ($rs = mysql_fetch_array($queryAlamcen)) {
                    $nombreAlmacen = $rs['nombre_almacen'];
                    $modeloComponenteConsulta = $rs['Modelo'];
                }
                $message = "El toner <b>$modeloComponenteConsulta</b> del almacén <b>$nombreAlmacen</b> tiene como existencia <b>0</b>";
                $correos = array();
                $z = 0;
                $queryCorreo = $catalogo->obtenerLista("SELECT CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno)AS ejecutivo,u.correo FROM c_cliente c,c_usuario u WHERE c.EjecutivoCuenta=u.IdUsuario AND c.ClaveCliente='" . $parametros['cliente'] . "'");
                while ($rs = mysql_fetch_array($queryCorreo)) {
                    $correos[$z] = $rs['correo'];
                    $z++;
                }
                //$correos[0] = "hugosh189@gmail.com";               
                $mail->setBody($message);
                foreach ($correos as $value) {
                    if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $mail->setTo($value);
                        if ($mail->enviarMail() == "1") {
                            // echo "Un correo fue enviado para la autorización.";
                        } else {
                            echo "No se envio correo de resurtido existente, el correo es incorrecto.";
                        }
                    }
                }
//                }
            }
            //generar ticket       
            $idTicket = "";
            $ticket->setUsuario($_SESSION['user']);
            $ticket->setEstadoDeTicket(2);
            $ticket->setTipoReporte(15);
            $null = NULL;
            $descripcion = "Solicitud de toner al mini almacén";
            $ticket->setNombreCliente($parametros['nombreCliente']);
            $ticket->setClaveCentroCosto($parametros['Localidad']);
            $ticket->setClaveCliente($parametros['cliente']);
            $ticket->setNombreCentroCosto($parametros['nombreCentroCosto']);
            $nombreLocalidad = $parametros['nombreCentroCosto'];
            $ticket->setNoSerieEquipo($null);
            $ticket->setModeloEquipo($null);
            $ticket->setDescripcionReporte($descripcion);
            $ticket->setAreaAtencion(2);
            $ticket->setActivo(1);
            $ticket->setUsuarioCreacion($_SESSION['user']);
            $ticket->setUsuarioUltimaModificacion($_SESSION['user']);
            $ticket->setPantalla("Solicitud de toner del mini almacén");
            $ticket->setUbicacion(1);
            if ($ticket->newRegistro()) {
                $idTicket = $ticket->getIdTicket();
                echo "La solicitud se atendio exitosamente, se generó el ticket: " . $idTicket . " ";
                //modificar movimiento
                $obj->setIdMovimiento($idMovimiento);
                if ($obj->EditarIdTicket($idTicket)) {
                    // echo "SE modifico";
                } else{
                    echo "<br/>No se pudo asignar el Folio del ticket al movimiento de componente<br/>";
                }
                
                if (isset($parametros['txtContadorBNNuevo']) && $parametros['txtContadorBNNuevo'] != "" || isset($parametros['txtContadorColorNuevo']) && $parametros['txtContadorColorNuevo'] != "" || isset($parametros['txtNivelNegroNuevo']) && $parametros['txtNivelNegroNuevo'] != "" || isset($parametros['txtNivelCainNuevo']) && $parametros['txtNivelCainNuevo'] != "" || isset($parametros['txtNivelMagentaNuevo']) && $parametros['txtNivelMagentaNuevo'] != "" || isset($parametros['txtNivelAmarilloNuevo']) && $parametros['txtNivelAmarilloNuevo'] != "") { //agregar lectura
                    list($noParteLista) = explode(" / ", $parametros['noSerie']);
                    $lecturaTicket->setClaveEspEquipo($parametros['noSerie2']);
                    $lecturaTicket->setModeloEquipo($parametros['ModeloEquipo']);
                    $lecturaTicket->setContadorBN($parametros['txtContadorBNNuevo']);
                    $lecturaTicket->setNivelNegro($parametros['txtNivelNegroNuevo']);

                    if (!isset($parametros['txtContadorColorNuevo']))
                        $lecturaTicket->setContadorColor("");
                    else
                        $lecturaTicket->setContadorColor($parametros['txtContadorColorNuevo']);
                    if (!isset($parametros['txtNivelCainNuevo']))
                        $lecturaTicket->setNivelCia("");
                    else
                        $lecturaTicket->setNivelCia($parametros['txtNivelCainNuevo']);
                    if (!isset($parametros['txtNivelMagentaNuevo']))
                        $lecturaTicket->setNivelMagenta("");
                    else
                        $lecturaTicket->setNivelMagenta($parametros['txtNivelMagentaNuevo']);
                    if (!isset($parametros['txtNivelAmarilloNuevo']))
                        $lecturaTicket->setNivelAmarillo("");
                    else
                        $lecturaTicket->setNivelAmarillo($parametros['txtNivelAmarilloNuevo']);

                    $lecturaTicket->setIdTicket($idTicket);
                    if ($parametros['txtFechaContadorAnterior'] != "") {
                        list($fecha, $hora) = explode(" ", $parametros['txtFechaContadorAnterior']);
                        list($dia, $mes, $anio) = explode("-", $fecha);
                        $lecturaTicket->setFechaA($anio . "-" . $mes . "-" . $dia . " " . $hora);
                    } else {
                        $lecturaTicket->setFechaA("");
                    }

                    $lecturaTicket->setContadorBNA($parametros['txtContadorBNAnterior']);
                    $lecturaTicket->setNivelNegroA($parametros['txtNivelNegroAnterior']);
                    if (!isset($parametros['txtContadorColorAnterior']))
                        $lecturaTicket->setContadorColorA("");
                    else
                        $lecturaTicket->setContadorColorA($parametros['txtContadorColorAnterior']);
                    if (!isset($parametros['txtNivelCainAnterior']))
                        $lecturaTicket->setNivelCiaA("");
                    else
                        $lecturaTicket->setNivelCiaA($parametros['txtNivelCainAnterior']);
                    if (!isset($parametros['txtNivelMagentaAnterior']))
                        $lecturaTicket->setNivelMagentaA("");
                    else
                        $lecturaTicket->setNivelMagentaA($parametros['txtNivelMagentaAnterior']);
                    if (!isset($parametros['txtNivelAmarilloAnterior']))
                        $lecturaTicket->setNivelAmarilloA("");
                    else
                        $lecturaTicket->setNivelAmarilloA($parametros['txtNivelAmarilloAnterior']);
                    $lecturaTicket->setActivo(1);
                    $lecturaTicket->setUsuarioCreacion($_SESSION['user']);
                    $lecturaTicket->setUsuarioUltimaModificacion($_SESSION['user']);
                    $lecturaTicket->setPantalla("Solicitud de toner del mini almacén");
                    if ($lecturaTicket->NewRegistro()) {
                        $idLecturaTicket = $lecturaTicket->getIdLectura();
                        // echo "La lectura  se agrego correctamente";
                    } else {
                        echo "La lectura no se agregó correctamente";
                    }
                }
                
                //agregar pedido
                $pedido1->setIdTicket($idTicket);
                list($noParteLista) = explode(" / ", $parametros['noSerie']);
                $notaRefaccion->setNoSerie($parametros['noSerie2']);
                $pedido1->setClaveEspEquipo($parametros['noSerie2']);
                $tonerNegro = 0;
                $tonerCia = 0;
                $tonerMagenta = 0;
                $tonerAmarillo = 0;
                if ($descripcionToner == "1")
                    $tonerNegro = $parametros['cantidadToner'];
                else if ($descripcionToner == "2")
                    $tonerCia = $parametros['cantidadToner'];
                else if ($descripcionToner == "3")
                    $tonerMagenta = $parametros['cantidadToner'];
                else if ($descripcionToner == "4")
                    $tonerAmarillo = $parametros['cantidadToner'];
                else
                    $tonerNegro = $parametros['cantidadToner'];

                $pedido1->setTonerNegro($tonerNegro);
                $pedido1->setTonerCian($tonerCia);
                $pedido1->setTonerMagenta($tonerMagenta);
                $pedido1->setTonerAmarillo($tonerAmarillo);
                $pedido1->setIdLecturaTicket($idLecturaTicket);
                $pedido1->setActivo(1);
                $pedido1->setUsuarioCreacion($_SESSION['user']);
                $pedido1->setUsuarioUltimaModificacion($_SESSION['user']);
                $pedido1->setPantalla("Solicitud de toner del mini almacén");
                $pedido1->setEstado("Entregado");
                $pedido1->setModelo($parametros['ModeloEquipo']);

                if ($pedido1->newRegistro()) {
                    // echo "Se registro pedido";
                } else {
                    echo "No se registro pedido";
                }
                //crear nota de solicitud de toner 
                $idTicketNota = $ticket->getIdTicket();
                $notaTicket->setIdTicket($idTicketNota);
                $notaTicket->setDiagnostico("Solicitud de resurtido de toners:");
                $notaTicket->setIdEstatus(67);
                $notaTicket->setUsuarioSolicitud($_SESSION['user']);
                $notaTicket->setMostrarCliente(1);
                $notaTicket->setActivo(1);
                $notaTicket->setUsuarioCreacion($_SESSION['user']);
                $notaTicket->setUsuarioModificacion($_SESSION['user']);
                $notaTicket->setPantalla("Solicitud de toner del mini almacén");
                
                if ($notaTicket->newRegistro()) {//agregar nota refaccion
                    $idNotaTicket = $notaTicket->getIdNota();
                    $notaRefaccion->setNoSerie($parametros['noSerie2']);
                    $notaRefaccion->setIdNota($idNotaTicket);
                    $notaRefaccion->setUsuarioCreacion($_SESSION['user']);
                    $notaRefaccion->setUsuarioModificacion($_SESSION['user']);
                    $notaRefaccion->setPantalla("Solicitud de toner del mini almacén");
                    $notaRefaccion->setCantidadSurtidas(1);
                    $notaRefaccion->setIdAlmacen($almacen);
                    $notaRefaccion->setNoParte($noParte);
                    $notaRefaccion->setCantidad(1);
                    if ($notaRefaccion->newRegistro()) {
                        if ($notaRefaccion->newRegistroDetalle()) {
                            $resurtidoToner->setNoParte($noParte);
                            $resurtidoToner->setIdAlmacen($almacen);
                            if ($resurtidoToner->verificarResurtidoExistente()) { //verificar si existe un resurtido
                                $idTicketModificar = $resurtidoToner->getIdTicket();
                                $notaRefaccion->setUsuarioModificacion($_SESSION['user']);
                                if ($notaRefaccion->editarCantidadResurtido($idTicketModificar, $noParte)) {
                                    //echo "modificado";
                                }
//                                $notaRefaccion->setIdNota($idNota)
                            }
                        } else{
                            echo "El detalle no se agregó correctamente";
                        }
                        echo "";
                    } else {
                        echo "La refaccion no se registró exitosamente";
                    }
                }
            }
            
            /* Hay que chechar todos los tickets de resurtido de este almacén que sigan abiertos, y verificar
             * que en estos tickets se encuentre este NoParte si está tomar en cuenta los que se han pedido 
             * más los que se tienen ahora */
            
            $pedidosAnteriores = 0;
            $queryChecarResurtidosAnteriores = "SELECT rt.CantidadResurtido, nr.CantidadSurtida FROM k_resurtidotoner rt 
                LEFT JOIN c_ticket t ON rt.IdTicket = t.IdTicket 
                INNER JOIN c_notaticket nt ON (nt.IdTicket = t.IdTicket AND nt.IdEstatusAtencion = 67)
                INNER JOIN k_nota_refaccion nr ON (nr.NoParteComponente = rt.NoComponenteToner AND nr.IdNotaTicket = nt.IdNotaTicket)
                WHERE rt.NoComponenteToner = '".$noParte."' AND t.EstadoDeTicket <> 2 AND t.EstadoDeTicket <> 4
                AND rt.IdAlmacen = ".$parametros['almacen'];
            $resultChecarResurtidosAnteriores = $catalogo->obtenerLista($queryChecarResurtidosAnteriores);
            while($rsChecarResurtidosAnteriores = mysql_fetch_array($resultChecarResurtidosAnteriores)){
                $pedidosAnteriores += ((int)$rsChecarResurtidosAnteriores['CantidadResurtido'] - (int)$rsChecarResurtidosAnteriores['CantidadSurtida']);
            }
            //verificar stock minimo
            $nombreALmacen = "";
            $almacenComponente->getRegistroById($noParte, $parametros['almacen']);
            $cantidadExistente = $almacenComponente->getExistencia() + $pedidosAnteriores;
            $cantidadMinima = $almacenComponente->getMinimo();
            $cantidadMaxima = $almacenComponente->getMaximo();
            if ((int) $cantidadExistente < (int) $cantidadMinima) {//agregar resurtido de toner
                //obtener todos los toner del almacen
                $almacenComponente->getComponentesAlmacen($parametros['almacen']);
                $arrayNoParte = $almacenComponente->getArrayNoParte();
                $arrayExistente = $almacenComponente->getArrayExistente();
                $arrayMaxima = $almacenComponente->getArrayMaxima();
                $arrayModelo = $almacenComponente->getArrayModelo();
                $arrayDescripcion = $almacenComponente->getArrayDescripcion();
                $arrayApartados = $almacenComponente->getArrayApartados();
                $arrayMinima = $almacenComponente->getArrayMinima();
                $nombreALmacen = $almacenComponente->getNombreAlamcen();
                $contador = 0;
                $resurtidoToner->setIdAlmacen($parametros['almacen']);
                $resurtidoToner->setUsuarioCreacion($_SESSION['user']);
                $resurtidoToner->setUsuarioModificacion($_SESSION['user']);
                $resurtidoToner->setPantalla("Solicitud de toner del mini almacén");
                $idTicketPedidoAnterior = "";
                $idMailFusionado = "";
                
                if ($resurtidoToner->verificarAlmacenTicketExistente()) {//verificar si  existe un ticket de almacen de resurtido pendiente
                    $idTicketPedidoAnterior = $resurtidoToner->getIdTicketF();
                    $idMailFusionado = $resurtidoToner->getIdMail();
                    $resurtidoToner->setIdTicket($idTicketPedidoAnterior);
                } else {
                    $resurtidoToner->setIdTicket($idTicket);
                }
                
                $arraySolicitudTicket = array();
                $arrayCantidadSurtido = array();
                while ($contador < count($arrayNoParte)) {
                    $resurtidoToner->setNoParte($arrayNoParte[$contador]);
                    /* Para cada número de parte tenemos que checar lo mismo si existe un ticket de resurtido abierto */
                    $pedidosAnteriores = 0;
                    $queryChecarResurtidosAnteriores = "SELECT rt.CantidadResurtido, nr.CantidadSurtida FROM k_resurtidotoner rt 
                        LEFT JOIN c_ticket t ON rt.IdTicket = t.IdTicket 
                        INNER JOIN c_notaticket nt ON (nt.IdTicket = t.IdTicket AND nt.IdEstatusAtencion = 67)
                        INNER JOIN k_nota_refaccion nr ON (nr.NoParteComponente = rt.NoComponenteToner AND nr.IdNotaTicket = nt.IdNotaTicket)
                        WHERE rt.NoComponenteToner = '".$noParte."' AND t.EstadoDeTicket <> 2 AND t.EstadoDeTicket <> 4
                        AND rt.IdAlmacen = ".$parametros['almacen'];
                    echo $queryChecarResurtidosAnteriores;
                    $resultChecarResurtidosAnteriores = $catalogo->obtenerLista($queryChecarResurtidosAnteriores);
                    while($rsChecarResurtidosAnteriores = mysql_fetch_array($resultChecarResurtidosAnteriores)){
                        $pedidosAnteriores += ((int)$rsChecarResurtidosAnteriores['CantidadResurtido'] - (int)$rsChecarResurtidosAnteriores['CantidadSurtida']);
                    }
                    if ((int) $arrayMaxima [$contador] > ((int) $arrayExistente[$contador] + $pedidosAnteriores)) {
                        $totalResurtido = (int) $arrayMaxima [$contador] - ((int) $arrayExistente[$contador] + $pedidosAnteriores);
                        $arrayCantidadSurtido[$contador] = $totalResurtido;
                        if ($totalResurtido != "" && (int) $totalResurtido > 0) {
                            $arraySolicitudTicket[$contador] = "(" . $totalResurtido . " - " . $arrayModelo[$contador] . ")";
                            $resurtidoToner->setCantidadSurtido($totalResurtido);
                            if ((int) $totalResurtido > 0) {
                                if ($resurtidoToner->newRegistro()) {
                                    //echo "Se genero un resurtido de toner";
                                } else {
                                    echo "No se genero resurtido de toner";
                                }
                            }
                        }
                    }

                    $contador++;
                }

                if (!empty($arraySolicitudTicket)) {//agregar ticket
                    if ($idTicketPedidoAnterior != "") {//fusionar el resurtido
                        if ($notaTicket->getNotaTicketByTicket($idTicketPedidoAnterior)) {//obtener la nota del ticket
                            $idNotaFucion = $notaTicket->getIdNota();
                            $notaRefaccion->setIdNota($idNotaFucion);
                            $notaRefaccion->setUsuarioCreacion($_SESSION['user']);
                            $notaRefaccion->setUsuarioModificacion($_SESSION['user']);
                            $notaRefaccion->setPantalla("Solicitud de toner del mini almacén");
                            $notaRefaccion->setCantidadSurtidas(0);
                            $notaRefaccion->setIdAlmacen("NULL");
                            $x = 0;
                            $arrayMailToner = array();
                            $arrayMailCantidad = array();
                            while ($x < count($arrayNoParte)) {
                                $arrayMailToner[$x] = $arrayModelo[$x];
                                $arrayMailCantidad[$x] = $arrayCantidadSurtido[$x];
                                $notaRefaccion->setNoParte($arrayNoParte[$x]);
                                $notaRefaccion->setCantidad($arrayCantidadSurtido[$x]);
                                if ((int) $arrayCantidadSurtido[$x] > 0 && $arrayCantidadSurtido[$x] != "") {
                                    if ((int) $arrayCantidadSurtido[$x] > 0) {
                                        if ($notaRefaccion->newRegistro()) {
                                            if ($notaRefaccion->newRegistroDetallefusion()) {
                                                
                                            } else {
                                                //echo "El detalle no se agregó correctamente";
                                            }
                                            echo "";
                                        } else {
//                                            echo "La refaccion no se registró exitosamente";
                                        }
                                    }
                                }

                                $x++;
                            }
                            if (!empty($arrayMailToner)) {//mandar el correo con los toner solicitados que se fusionaron
                                $mail->setFrom($correo_emisor);
                                $mail->setSubject("Solicitud de toner del ticket: " . $idTicketPedidoAnterior);
                                $message = "<html><body>";
                                $usuario->getRegistroById($_SESSION['idUsuario']);
                                $message .= "<h3>Hay una solicitud de toner del usuario:</h3><h4>" . $usuario->getPaterno() . " " . $usuario->getMaterno() . " " . $usuario->getNombre() . "</h4>";
                                $message = $message . "<br/><b>Comentario de quien generó la solicitud</b>:<br/>Solicitud de toner<br/><br/>";
                                $message .= "<h3>Para el cliente:</h3><h4> " . $ticket->getNombreCliente() . " </h4>";
                                $message .= "<h3>Localidad</h3><h4> " . $nombreLocalidad . " </h4>";
                                $texto1 = "<table border='1'>";
                                $texto1.="<tr><th>Modelo</th><th>Cantidad</th><th>Almacén</th></tr>";
                                $cont = 0;
                                //nombreAlamcen
                                $nombreAlmacen = "";
                                $queryAlamcen = $catalogo->obtenerLista("SELECT al.nombre_almacen FROM c_almacen al WHERE al.id_almacen='$almacen'");
                                while ($rs = mysql_fetch_array($queryAlamcen)) {
                                    $nombreAlmacen = $rs['nombre_almacen'];
                                }
                                while ($cont < count($arrayMailToner)) {
                                    if ((int) $arrayMailCantidad[$cont] > 0 && $arrayMailCantidad[$cont] != "") {
                                        $texto1 .= "<tr><td>" . $arrayMailToner[$cont] . "</td><td>" . $arrayMailCantidad[$cont] . "</td><td>" . $nombreAlmacen . "</td></tr>";
                                    }
                                    $cont++;
                                }
                                $texto1.="</table>";
                                $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud=1;");
                                $correos = array();
                                $z = 0;
                                while ($rs = mysql_fetch_array($query4)) {
                                    $correos[$z] = $rs['correo'];
                                    $z++;
                                }
                                $message .= $texto1;
                                /* Guardamos y creamos la liga para aceptar/rechazar la solicitud directamente */
                                $clave = $mail->generaPass();

//                                $idMail = $catalogo->insertarRegistro("INSERT INTO c_mailpedidotoner(idTicket, contestada, clave, IdUsuario, Activo, UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
//                                                            VALUES($idTicketNota,0,MD5('$clave'),2,1,'" . $_SESSION['user'] . "',now(),'" . $_SESSION['user'] . "',now(),'nueva_solicitud.php');");

                                $liga = $_SESSION['ip_server'] . "/aceptarPedidoToner.php?clv=$clave&idTicket=$idTicketPedidoAnterior&idMail=$idMailFusionado&idNota=$idNotaFucion&uguid=".$_SESSION['idEmpresa']."&tipo";
                                $message = $message . "<br/>Autorizar solicitud: " . $liga . "=1 <br/><br/>";
                                $message = $message . "<br/>Rechazar solicitud: " . $liga . "=3 <br/><br/>";
                                $message .= "</body></html>";
                                $mail->setBody($message);
                                foreach ($correos as $value) {
                                    if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                                        $mail->setTo($value);
                                        if ($mail->enviarMail() == "1") {
                                            // echo "Un correo fue enviado para la autorización.";
                                        } else {
                                            echo "Error: No se pudo enviar el correo para autorizar.";
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $ticket->setEstadoDeTicket(3);
                        $descripcion = "Solicitud de resurtido de los toners: " . implode(",", $arraySolicitudTicket) . " del almacén:" . $nombreALmacen . " proveniente del ticket: $idTicket";
                        $ticket->setDescripcionReporte($descripcion);
                        $ticket->setResurtido(1);                        
                        if ($ticket->newRegistroResurtido()) {
                            $idTicketNuevo = $ticket->getIdTicket();
                            $resurtidoToner->editRegistroTicket($idTicket, $idTicketNuevo);
                            $idTicketNota = $ticket->getIdTicket();
                            $notaTicket->setIdTicket($idTicketNota);
                            $notaTicket->setDiagnostico("Solicitud de resurtido de toners:");
                            $notaTicket->setIdEstatus(67);
                            $notaTicket->setUsuarioSolicitud($_SESSION['user']);
                            $notaTicket->setMostrarCliente(1);
                            $notaTicket->setActivo(1);
                            $notaTicket->setUsuarioCreacion($_SESSION['user']);
                            $notaTicket->setUsuarioModificacion($_SESSION['user']);
                            $notaTicket->setPantalla("Solicitud de toner del mini almacén");
                            if ($notaTicket->newRegistro()) {//agregar nota refaccion
                                $idNotaTicket = $notaTicket->getIdNota();
                                $notaRefaccion->setIdNota($idNotaTicket);
                                $notaRefaccion->setUsuarioCreacion($_SESSION['user']);
                                $notaRefaccion->setUsuarioModificacion($_SESSION['user']);
                                $notaRefaccion->setPantalla("Solicitud de toner del mini almacén");
                                $notaRefaccion->setCantidadSurtidas(0);
                                $notaRefaccion->setIdAlmacen("NULL");
                                $x = 0;
                                $arrayMailToner = array();
                                $arrayMailCantidad = array();
                                while ($x < count($arrayNoParte)) {
                                    $arrayMailToner[$x] = $arrayModelo[$x];
                                    $arrayMailCantidad[$x] = $arrayCantidadSurtido[$x];
                                    $notaRefaccion->setNoParte($arrayNoParte[$x]);
                                    $notaRefaccion->setCantidad($arrayCantidadSurtido[$x]);
                                    if ((int) $arrayCantidadSurtido[$x] > 0 && $arrayCantidadSurtido[$x] != "") {
                                        if ((int) $arrayCantidadSurtido[$x] > 0) {
                                            if ($notaRefaccion->newRegistro()) {
                                                if ($notaRefaccion->newRegistroDetalle())
                                                    echo "";
                                                else
                                                    echo "El detalle no se agregó correctamente";
                                                echo "";
                                            } else {
                                                echo "La refaccion no se registró exitosamente";
                                            }
                                        }
                                    }

                                    $x++;
                                }
                                if (!empty($arrayMailToner)) {
                                    $mail->setFrom($correo_emisor);
                                    $mail->setSubject("Solicitud de toner del ticket: " . $idTicketNota);
                                    $message = "<html><body>";
                                    $usuario->getRegistroById($_SESSION['idUsuario']);
                                    $message .= "<h3>Hay una solicitud de toner del usuario:</h3><h4>" . $usuario->getPaterno() . " " . $usuario->getMaterno() . " " . $usuario->getNombre() . "</h4>";
                                    $message = $message . "<br/><b>Comentario de quien generó la solicitud</b>:<br/>Solicitud de toner<br/><br/>";
                                    $message .= "<h3>Para el cliente:</h3><h4> " . $ticket->getNombreCliente() . " </h4>";
                                    $message .= "<h3>Localidad</h3><h4> " . $nombreLocalidad . " </h4>";
                                    $texto1 = "<table border='1'>";
                                    $texto1.="<tr><th>Modelo</th><th>Cantidad</th><th>Almacén</th></tr>";
                                    $cont = 0;
                                    //nombreAlamcen
                                    $nombreAlmacen = "";
                                    $queryAlamcen = $catalogo->obtenerLista("SELECT al.nombre_almacen FROM c_almacen al WHERE al.id_almacen='$almacen'");
                                    while ($rs = mysql_fetch_array($queryAlamcen)) {
                                        $nombreAlmacen = $rs['nombre_almacen'];
                                    }
                                    while ($cont < count($arrayMailToner)) {
                                        if ((int) $arrayMailCantidad[$cont] > 0 && $arrayMailCantidad[$cont] != "") {
                                            $texto1 .= "<tr><td>" . $arrayMailToner[$cont] . "</td><td>" . $arrayMailCantidad[$cont] . "</td><td>" . $nombreAlmacen . "</td></tr>";
                                        }
                                        $cont++;
                                    }
                                    $texto1.="</table>";
                                    $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud=1;");
                                    $correos = array();
                                    $z = 0;
                                    while ($rs = mysql_fetch_array($query4)) {
                                        $correos[$z] = $rs['correo'];
                                        $z++;
                                    }
                                    $message .= $texto1;
                                    /* Guardamos y creamos la liga para aceptar/rechazar la solicitud directamente */
                                    $clave = $mail->generaPass();
                                    $idMail = $catalogo->insertarRegistro("INSERT INTO c_mailpedidotoner(idTicket, contestada, clave, IdUsuario, Activo, UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                                                            VALUES($idTicketNota,0,MD5('$clave'),2,1,'" . $_SESSION['user'] . "',now(),'" . $_SESSION['user'] . "',now(),'nueva_solicitud.php');");
                                    $liga = $_SESSION['ip_server'] . "/aceptarPedidoToner.php?clv=$clave&idTicket=$idTicketNota&idMail=$idMail&idNota=$idNotaTicket&tipo";
                                    $message = $message . "<br/>Autorizar solicitud: " . $liga . "=1&uguid=".$_SESSION['idEmpresa']." <br/><br/>";
                                    $message = $message . "<br/>Rechazar solicitud: " . $liga . "=3&uguid=".$_SESSION['idEmpresa']." <br/><br/>";
                                    $message .= "</body></html>";
                                    $mail->setBody($message);
                                    foreach ($correos as $value) {
                                        if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                                            $mail->setTo($value);
                                            if ($mail->enviarMail() == "1") {
                                                // echo "Un correo fue enviado para la autorización.";
                                            } else {
                                                echo "Error: No se pudo enviar el correo para autorizar.";
                                            }
                                        }
                                    }
                                }
                                //echo "La nota se registró correctamente";
                            } else {
                                echo "La nota no se registró exitosamente";
                            }
                            echo "<br/> Se generó un resurtido de toner con el ticket: " . $ticket->getIdTicket() . "";
                            $pedido1->setEstado("Validar Existencia");
                            $x = 0;
                            while ($x < count($arrayDescripcion)) {
                                if ($arrayCantidadSurtido[$x] != "0" && $arrayCantidadSurtido[$x] != "") {
                                    $pedido1->setIdTicket($ticket->getIdTicket());
                                    //$pedido1->setModelo($arrayModelo[$x]);
                                    $tonerNegro = 0;
                                    $tonerCia = 0;
                                    $tonerMagenta = 0;
                                    $tonerAmarillo = 0;
                                    if ($arrayDescripcion[$x] == "1")
                                        $tonerNegro = $arrayCantidadSurtido[$x];
                                    else if ($arrayDescripcion[$x] == "2")
                                        $tonerCia = $parametros['cantidadToner'];
                                    else if ($arrayDescripcion[$x] == "3")
                                        $tonerMagenta = $arrayCantidadSurtido[$x];
                                    else if ($arrayDescripcion[$x] == "4")
                                        $tonerAmarillo = $arrayCantidadSurtido[$x];
                                    else
                                        $tonerNegro = $arrayCantidadSurtido[$x];

                                    $pedido1->setTonerNegro($tonerNegro);
                                    $pedido1->setTonerCian($tonerCia);
                                    $pedido1->setTonerMagenta($tonerMagenta);
                                    $pedido1->setTonerAmarillo($tonerAmarillo);
                                    $pedido1->setIdLecturaTicket($idLecturaTicket);
                                    if ((int) $tonerNegro >= 0 && (int) $tonerCia >= 0 && (int) $tonerMagenta >= 0 && (int) $tonerAmarillo >= 0) {
                                        if ($pedido1->newRegistro()) {
                                            // echo "Se registro pedido";
                                        } else {
                                            echo "No se registro pedido";
                                        }
                                    }
                                }
                                $x++;
                            }
                        }else{
                            echo "<br/>Error: no se pudo registrar el ticket de resurtido, favor de reportarlo al administrador del sistema<br/>";
                        }
                    }
                }
                
                $queryPendiente = $resurtidoToner->verificarResurtidoByAlamcen($parametros['almacen'], $idTicketNuevo);
                if (mysql_num_rows($queryPendiente) > 0) {//verificar si ya existe resurtido   
                    $mail->setFrom($correo_emisor);
                    $mail->setSubject("Existe un resurtido de toner pendiente del almacen:" . $nombreALmacen);
                    $message = "<html><body>";
                    $usuario->getRegistroById($_SESSION['idUsuario']);
                    $message .= "<h3>Hay una solicitud de toner pendiente del usuario:</h3><h4>" . $usuario->getPaterno() . " " . $usuario->getMaterno() . " " . $usuario->getNombre() . "</h4>";
                    $message .= "<h3>EL amacén $nombreALmacen tiene un resurtido de toner pendiente </h3>";
                    $texto1 = "<table border='1'>";
                    $texto1.="<tr><th>Ticket</th><th>Modelo</th><th>Cantidad</th><th>Fecha</th></tr>";                                                           
                                        
                    $cont = 0;
                    // $tamanoRegistro=  mysql_num_rows($queryPendiente);
                    while ($rs = mysql_fetch_array($queryPendiente)) {
                        $texto1 .= "<tr><td>" . $rs['IdTicket'] . "</td><td>" . $rs['Modelo'] . "</td><td>" . $rs['Cantidadresurtido'] . "</td><td>" . $rs['fecha'] . "</td></tr>";
                        $cont++;
                    }
                    $texto1.="</table>";
                    $correos = array();
                    $queryCorreo = $catalogo->obtenerLista("SELECT cs.correo FROM c_correossolicitud cs WHERE cs.TipoSolicitud=6 AND cs.Activo=1");
                    while ($rs = mysql_fetch_array($queryCorreo)) {
                        $correos[$z] = $rs['correo'];
                        $z++;
                    }
                    // $correos[0] = "hugosh189@gmail.com";
                    $message .= $texto1;
                    $mail->setBody($message);
                    if ($cont > 0) {
                        foreach ($correos as $value) {
                            if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $mail->setTo($value);
                                if ($mail->enviarMail() == "1") {
                                    // echo "Un correo fue enviado para la autorización.";
                                } else {
                                    echo "No se envio correo de resurtido existente, el correo es incorrecto.";
                                }
                            }
                        }
                    }
                }
                //Guardamos los valores actuales de máximos, mínimos para uso en el reporte.
                //Primero hay que verificar que no exista ya una imagen para este ticket para evitar duplicado de informacion.
                $almacenComponenteTicket = new AlmacenComponenteTicket();
                if ($idTicketPedidoAnterior != ""){
                    $almacenComponenteTicket->setIdTicket($idTicketPedidoAnterior);
                }else{
                    $almacenComponenteTicket->setIdTicket($idTicketNuevo);
                }
                $almacenComponenteTicket->setIdAlmacen($parametros['almacen']);
                $almacenComponenteTicket->setArrayNoParte($arrayNoParte);
                $almacenComponenteTicket->setArrayApartados($arrayApartados);
                $almacenComponenteTicket->setArrayExistente($arrayExistente);
                $almacenComponenteTicket->setArrayMaxima($arrayMaxima);
                $almacenComponenteTicket->setArrayMinima($arrayMinima);
                $almacenComponenteTicket->setUsuarioCreacion($_SESSION['user']);
                $almacenComponenteTicket->setUsuarioModificacion($_SESSION['user']);
                $almacenComponenteTicket->setPantalla("Cambio de Tóner");
                if($almacenComponenteTicket->newRegistros()){
                    
                }
            }//FIN RESURTIDO MINI ALMACEN
        } else {
            echo "Error: La salida del almacen no se generó exitosamente";
        }
    } else {
        echo "Error: La solicitud no pudo ser atendida.";
    }
} else {
    echo "Error: El almacen no cuenta con el toner solicitado.";
}
?>