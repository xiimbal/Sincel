<?php

    session_start();

    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {

        header("Location: ../../index.php");
        
    }

    if (!isset($_POST['empresa'])) { //Viene de aceptarPedidoToner (autorización)
        
        $path = "../";

    } else {

        $path = "WEB-INF/";
    }

    include_once($path . "Classes/MovimientoComponente.class.php");
    include_once($path . "Classes/LecturaTicket.class.php");
    include_once($path . "Classes/AlmacenConmponente.class.php");
    include_once($path . "Classes/AlmacenComponenteTicket.class.php");
    include_once($path . "Classes/ResurtidoToner.class.php");
    include_once($path . "Classes/Ticket.class.php");
    include_once($path . "Classes/Pedido.class.php");
    include_once($path . "Classes/NotaTicket.class.php");
    include_once($path . "Classes/NotaRefaccion.class.php");
    include_once($path . "Classes/Mail.class.php");
    include_once($path . "Classes/Usuario.class.php");
    include_once($path . "Classes/Catalogo.class.php");
    include_once($path . "Classes/ParametroGlobal.class.php");
    include_once($path . "Classes/Parametros.class.php");
    include_once($path . "Classes/TicketAuxiliar.class.php");
    include_once($path . "Classes/Almacen.class.php");

    $parametroGlobal = new ParametroGlobal();
    $parametroSistema = new Parametros();
    $idEmpresa = "";
    $idUsuario = "";
    $idTicket = "";

    if (isset($_POST['empresa'])) {

        $parametroGlobal->setEmpresa($_POST['empresa']);
        $parametroSistema->setEmpresa($_POST['empresa']);
        $idEmpresa = $_POST['empresa'];
        $idUsuario = $_POST['idUsuario'];

    } else {

        $idEmpresa = $_SESSION['idEmpresa'];
        $idUsuario = $_SESSION['idUsuario'];

    }

    if ($parametroGlobal->getRegistroById("8")) {

        $correo_emisor = ($parametroGlobal->getValor());    

    } else {

        $correo_emisor = ("scg-salida@scgenesis.mx");            

    }

    $url = "http://genesis2.techra.com.mx/genesis2/";
    if ($parametroSistema->getRegistroById(8)) {
        $url = $parametroSistema->getDescripcion();    
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
    $ticketA = new TicketAuxiliar();
    $idNotaTicket = "";
    $idTicketNuevo = "";

    if (isset($_POST['empresa'])) {

        $obj->setEmpresa($_POST['empresa']);
        $lecturaTicket->setEmpresa($_POST['empresa']);
        $almacenComponente->setEmpresa($_POST['empresa']);
        $resurtidoToner->setEmpresa($_POST['empresa']);
        $ticket->setEmpresa($_POST['empresa']);
        $pedido1->setEmpresa($_POST['empresa']);
        $notaTicket->setEmpresa($_POST['empresa']);
        $notaRefaccion->setEmpresa($_POST['empresa']);
        $mail->setEmpresa($_POST['empresa']);
        $usuario->setEmpresa($_POST['empresa']);
        $catalogo->setEmpresa($_POST['empresa']);
        $ticketA->setEmpresa($_POST['empresa']);
        echo ("<h1>".$_POST['empresa']."</h1>");        

    }

    $user = "sistema";
    if (isset($_SESSION['user'])) {

        $user = $_SESSION['user'];

    }
    if (isset($_POST['form'])) {
        
        $parametros = "";    
        parse_str($_POST['form'], $parametros);    

    }

    // COMPRUEBA QUE EL TICKET ESTE PENDIENTE POR AUTORIZAR
    if (isset($_GET['pendiente_autorizar']) && $_GET['pendiente_autorizar'] == "1") { //Requiere autorización
                
        $ticket->setUsuario($user);
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
        $ticket->setUsuarioCreacion($user);
        $ticket->setUsuarioUltimaModificacion($user);
        $ticket->setPantalla("Solicitud de toner del mini almacén");
        $ticket->setUbicacion(1);
        $ticket->setCambioToner(1);
        
        if ($ticket->newRegistro()) {

            echo "Solicitud generada, se generó el ticket sin autorizar: " . $ticket->getIdTicket();

            $ticketA->setForm($_POST['form']);
            $ticketA->setIdTicket($ticket->getIdTicket());
            $ticketA->setActivo(1);
            $ticketA->setUsuarioCreacion($user);
            $ticketA->setUsuarioUltimaModificacion($user);
            $ticketA->setPantalla("Solicitud de toner del mini almacén pendiente autorizar");

            if ($ticketA->newRegistro()) {

                $mail = new Mail();

                if (isset($_POST['empresa'])) {

                    $mail->setEmpresa($_POST['empresa']);

                }

                $mail->setFrom($correo_emisor);
                $usuario->getRegistroById($idUsuario);
                
                $almacen = new Almacen();
                $almacen->getRegistroById($parametros['almacen']);
                $mail->setSubject("Solicitud de cambio de tóner del TFS " . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " del mini-almacén ".$almacen->getNombre()." para el  ticket " . $ticket->getIdTicket() . " del cliente " . $ticket->getNombreCliente());

                // Guardamos y creamos la liga para aceptar/rechazar la solicitud directamente 
                $clave = $mail->generaPass();
                $idMail = $catalogo->insertarRegistro("INSERT INTO c_mailpedidotoner(idTicket, contestada, clave, IdUsuario, Activo, UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                                                VALUES(" . $ticket->getIdTicket() . ",0,MD5('$clave'),2,1,'" . $user . "',now(),'" . $user . "',now(),'PHP Controler Reportar Cambio Toner');");
                
                $liga = "$url/aceptarPedidoToner.php?clv=$clave&idTicket=" . $ticket->getIdTicket() . "&idMail=$idMail&idUs=" . $idUsuario . "&tipo";

            $contadorAnterior = "";
            $contadorNuevo = "";
            $totalContadores = "";
            $porcentaje = "";
            $toner = $parametros['toner'];
            $noParte = explode(" // ", $toner);                        

            if ($noParte[1] == "1") {//si el toner es negro
                $contadorAnterior = $parametros['txtContadorBNAnterior'];
                $contadorNuevo = $parametros['txtContadorBNNuevo'];
            } else {
                $contadorAnterior = $parametros['txtContadorColorAnterior'];
                $contadorNuevo = $parametros['txtContadorColorNuevo'];
            }

            $rendimiento = "0";
            
            $query = $catalogo->obtenerLista("SELECT (c.Rendimiento*1) AS rendimiento FROM c_componente c WHERE c.NoParte='" . $noParte[0] . "'");

            while ($rs = mysql_fetch_array($query)) {
                $rendimiento = $rs['rendimiento'];
            }

            if ($contadorAnterior != null && $contadorAnterior != "" && $contadorNuevo != null && $contadorNuevo != "") {
                $totalContadores = $contadorNuevo - $contadorAnterior;
            } else {
                $totalContadores = 0;
            }

            $porcentaje = ($totalContadores * 100) / $rendimiento;

            $message = "Hay una solicitud de cambio de tóner que no cumplen con el rendimiento registrado en el sistema.<br/>"
                    . "<h2>Cliente: " . $ticket->getNombreCliente() . "</h2>"
                    . "<h2>Localidad: " . $ticket->getNombreCentroCosto() . "</h2>";
              
            $message .= "<table class='table' style='border-collapse: collapse;'><thead class='thead-dark'><tr>";
            $message .= "<th style='background-color: #144E80;color:white;padding:12px;border:1px solid #ddd;text-align:center;'>Serie</th>";

            if (isset($parametros['txtContadorBNNuevo']) && $parametros['txtContadorBNNuevo'] != "") {
                $message .= "<th style='background-color: #144E80;color:white;padding:12px;border:1px solid #ddd;text-align:center;'>Contador BN</th>";
            }

            if (isset($parametros['txtContadorColorNuevo']) && $parametros['txtContadorColorNuevo'] != "") {
                $message .= "<th style='background-color: #144E80;color:white;padding:12px;border:1px solid #ddd;text-align:center;'>Contador Color</th>";
            }

            if (isset($parametros['txtContadorBNAnterior']) && $parametros['txtContadorBNAnterior'] != "") {
                $message .= "<th style='background-color: #144E80;color:white;padding:12px;border:1px solid #ddd;text-align:center;'>Contador BN Anterior</th>";
            }

            if (isset($parametros['txtContadorColorAnterior']) && $parametros['txtContadorColorAnterior'] != "") {
                $message .= "<th style='background-color: #144E80;color:white;padding:12px;border:1px solid #ddd;text-align:center;'>Contador Color Anterior</th>";
            }

            $message .= "<th style='background-color: #144E80;color:white;padding:12px;border:1px solid #ddd;text-align:center;'>Toner solicitado (rendimiento)</th>";
            if (isset($parametros['txtContadorBNNuevo']) && $parametros['txtContadorBNNuevo'] != "" && isset($parametros['txtContadorBNAnterior']) && $parametros['txtContadorBNAnterior'] != "") {
                $message .= "<th style='background-color: #144E80;color:white;padding:12px;border:1px solid #ddd;text-align:center;'>Impresas negro</th>";
            }

            if (isset($parametros['txtContadorColorNuevo']) && $parametros['txtContadorColorNuevo'] != "" && isset($parametros['txtContadorColorAnterior']) && $parametros['txtContadorColorAnterior'] != "") {
                $message .= "<th style='background-color: #144E80;color:white;padding:12px;border:1px solid #ddd;text-align:center;'>Impresas color</th>";
            }

            $message .= "</tr></thead><tbody>";
            $message .= "<tr><td style='background:#F1F1E6;border: 1px solid #ddd;padding: 10px;text-align:center;'>" . $parametros['noSerie2'] . " / " . $parametros['ModeloEquipo'] . "</td>";            
            
            if (isset($parametros['txtContadorBNNuevo']) && $parametros['txtContadorBNNuevo'] != "") {
                $message .= "<td style='background:#F1F1E6;border: 1px solid #ddd;padding: 10px;text-align:center;'>" . number_format($parametros['txtContadorBNNuevo']) . "</td>";
            }

            if (isset($parametros['txtContadorColorNuevo']) && $parametros['txtContadorColorNuevo'] != "") {
                $message .= "<td style='background:#F1F1E6;border: 1px solid #ddd;padding: 10px;text-align:center;'>" . number_format($parametros['txtContadorColorNuevo']) . "</td>";
            }

            if (isset($parametros['txtContadorBNAnterior']) && $parametros['txtContadorBNAnterior'] != "") {
                $message .= "<td style='background:#F1F1E6;border: 1px solid #ddd;padding: 10px;text-align:center;'>" . number_format($parametros['txtContadorBNAnterior']) . "</td>";
            }

            if (isset($parametros['txtContadorColorAnterior']) && $parametros['txtContadorColorAnterior'] != "") {
                $message .= "<td style='background:#F1F1E6;border: 1px solid #ddd;padding: 10px;text-align:center;'>" . number_format($parametros['txtContadorColorAnterior']) . "</td>";
            }
            
            $toner = explode(" // ", $parametros['toner']);

            $consulta = $catalogo->obtenerLista("SELECT * from c_componente WHERE NoParte ='$toner[0]';");

            while ($respuesta_bd = mysql_fetch_array($consulta)) {
                $Modelo_Equipo = $respuesta_bd['Modelo'];                
            }            

            $message .= "<td style='background:#F1F1E6;border: 1px solid #ddd;padding: 10px;text-align:center;'><b>(" . $parametros['cantidadToner'] .") " . "$Modelo_Equipo" . " </b>/ " . $toner[0] . ":<br>" . $rendimiento . "</td>";
            if (isset($parametros['txtContadorBNNuevo']) && $parametros['txtContadorBNNuevo'] != "" && isset($parametros['txtContadorBNAnterior']) && $parametros['txtContadorBNAnterior'] != "") {
                $Impresas_Negro = ((($parametros['txtContadorBNNuevo'] - $parametros['txtContadorBNAnterior']) * 100) / $rendimiento);
                $message .= "<td style='background:#F1F1E6;border: 1px solid #ddd;padding: 10px;text-align:center;'>" . ($parametros['txtContadorBNNuevo'] - $parametros['txtContadorBNAnterior']) . " <br><b>(" . round($Impresas_Negro,1) . "%)<b></td>";
            }
            if (isset($parametros['txtContadorColorNuevo']) && $parametros['txtContadorColorNuevo'] != "" && isset($parametros['txtContadorColorAnterior']) && $parametros['txtContadorColorAnterior'] != "") {
                $Impresas_Color = ((($parametros['txtContadorColorNuevo'] - $parametros['txtContadorColorAnterior']) * 100) / $rendimiento); 
                $message .= "<td style='background:#F1F1E6;border: 1px solid #ddd;padding: 10px;text-align:center;'>" . ($parametros['txtContadorColorNuevo'] - $parametros['txtContadorColorAnterior']) . " <br><b>(" . round($Impresas_Color,1) . "%)<b></td>";
            }
            $message .= "</tr></tbody></table>";
            if ($almacen->getRegistrByLocalidad($ticket->getClaveCentroCosto())) {
                $message .= "<h2>Mini-almacén de la localidad: " . $almacen->getNombre() . "</h2>";
            }
            
            $message = $message . "<br/>Autorizar solicitud: " . $liga . "=11&uguid=" . $idEmpresa . " <br/><br/>";
            $message = $message . "<br/>Rechazar solicitud: " . $liga . "=13&uguid=" . $idEmpresa . " <br/><br/>";
            $mail->setBody($message);
            $consultaCorreos = "SELECT correo FROM c_correossolicitud WHERE TipoSolicitud = 23 AND Activo = 1;";
            $resultCorreos = $catalogo->obtenerLista($consultaCorreos);            
            while ($rsCorreo = mysql_fetch_array($resultCorreos)) {
                $value = $rsCorreo['correo'];                
                if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $mail->setTo($value);
                    if ($mail->enviarMail() == "1") {
                        // echo "Un correo fue enviado para la autorización.";
                    } else {
                        echo "<br/>No se envió correo de autorización de cambio de toner a $value.<br/>";
                    }
                }
            }
        }
    }
} else {
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
    $obj->setUsuarioCreacion($user);
    $obj->setUsuarioModificacion($user);
    $obj->setPantalla("Solicitud de toner del mini almacén");
    $obj->setEntradaSalida(1);
    $obj->setClaveCentroCostoNuevo($parametros['Localidad']);
    list($noParteLista) = explode(" / ", $parametros['noSerie']);
    /* print_r($parametros);
      echo "Error ".$parametros['noSerie'];
      print_r("Uno ".$parametros['noSerie']);
      print_r("<br/>Dos ".$noParteLista); */
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
                if (!isset($_POST['autorizar'])) {
                    $idTicket = "";
                } else {
                    $idTicket = $_POST['IdTicket'];
                    $ticket->setIdTicket($idTicket);
                }
                $ticket->setUsuario($user);
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
                $ticket->setUsuarioCreacion($user);
                $ticket->setUsuarioUltimaModificacion($user);
                $ticket->setPantalla("Solicitud de toner del mini almacén");
                $ticket->setUbicacion(1);
                $ticket->setCambioToner(1);
                if ($idTicket != "" || (!isset($_POST['autorizar']) && $ticket->newRegistro())) {
                    if (!isset($_POST['autorizar'])) {
                        $idTicket = $ticket->getIdTicket();
                    }
                    echo "La solicitud se atendio exitosamente, se generó el ticket: " . $idTicket . " ";
                    //modificar movimiento
                    $obj->setIdMovimiento($idMovimiento);
                    if ($obj->EditarIdTicket($idTicket)) {
                        // echo "SE modifico";
                    } else {
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
                        $lecturaTicket->setUsuarioCreacion($user);
                        $lecturaTicket->setUsuarioUltimaModificacion($user);
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
                    $pedido1->setUsuarioCreacion($user);
                    $pedido1->setUsuarioUltimaModificacion($user);
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
                    $notaTicket->setDiagnostico("Solicitud de cambio de toners:");
                    $notaTicket->setIdEstatus(67);
                    $notaTicket->setUsuarioSolicitud($user);
                    $notaTicket->setMostrarCliente(1);
                    $notaTicket->setActivo(1);
                    $notaTicket->setUsuarioCreacion($user);
                    $notaTicket->setUsuarioModificacion($user);
                    $notaTicket->setPantalla("Solicitud de toner del mini almacén");

                    if ($notaTicket->newRegistro()) {//agregar nota refaccion
                        $idNotaTicket = $notaTicket->getIdNota();
                        $notaRefaccion->setNoSerie($parametros['noSerie2']);
                        $notaRefaccion->setIdNota($idNotaTicket);
                        $notaRefaccion->setUsuarioCreacion($user);
                        $notaRefaccion->setUsuarioModificacion($user);
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
                                    $notaRefaccion->setUsuarioModificacion($user);
                                    if ($notaRefaccion->editarCantidadResurtido($idTicketModificar, $noParte)) {
                                        //echo "modificado";
                                    }
//                                $notaRefaccion->setIdNota($idNota)
                                }
                            } else {
                                echo "El detalle no se agregó correctamente";
                            }
                            echo "";
                        } else {
                            echo "La refaccion no se registró exitosamente";
                        }
                    }

                    /* $notaTicket->setIdEstatus(16);//Se cierra el ticket en automático
                      if(!$notaTicket->newRegistro()){

                      } */
                }

                /* Hay que chechar todos los tickets de resurtido de este almacén que sigan abiertos, y verificar
                 * que en estos tickets se encuentre este NoParte si está tomar en cuenta los que se han pedido 
                 * más los que se tienen ahora */

                $pedidosAnteriores = 0;
                $queryChecarResurtidosAnteriores = "SELECT rt.CantidadResurtido, nr.CantidadSurtida FROM k_resurtidotoner rt 
                LEFT JOIN c_ticket t ON rt.IdTicket = t.IdTicket 
                INNER JOIN c_notaticket nt ON (nt.IdTicket = t.IdTicket AND nt.IdEstatusAtencion = 67)
                INNER JOIN k_nota_refaccion nr ON (nr.NoParteComponente = rt.NoComponenteToner AND nr.IdNotaTicket = nt.IdNotaTicket)
                WHERE rt.NoComponenteToner = '" . $noParte . "' AND t.EstadoDeTicket <> 2 AND t.EstadoDeTicket <> 4
                AND rt.IdAlmacen = " . $parametros['almacen'];
                $resultChecarResurtidosAnteriores = $catalogo->obtenerLista($queryChecarResurtidosAnteriores);
                while ($rsChecarResurtidosAnteriores = mysql_fetch_array($resultChecarResurtidosAnteriores)) {
                    $pedidosAnteriores += ((int) $rsChecarResurtidosAnteriores['CantidadResurtido'] - (int) $rsChecarResurtidosAnteriores['CantidadSurtida']);
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
                    $resurtidoToner->setUsuarioCreacion($user);
                    $resurtidoToner->setUsuarioModificacion($user);
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
                        WHERE rt.NoComponenteToner = '" . $arrayNoParte[$contador] . "' AND t.EstadoDeTicket <> 2 AND t.EstadoDeTicket <> 4
                        AND rt.IdAlmacen = " . $parametros['almacen'];
                        $resultChecarResurtidosAnteriores = $catalogo->obtenerLista($queryChecarResurtidosAnteriores);
                        while ($rsChecarResurtidosAnteriores = mysql_fetch_array($resultChecarResurtidosAnteriores)) {
                            $pedidosAnteriores += ((int) $rsChecarResurtidosAnteriores['CantidadResurtido'] - (int) $rsChecarResurtidosAnteriores['CantidadSurtida']);
                        }
                        if ((int) $arrayMaxima [$contador] > ((int) $arrayExistente[$contador] + $pedidosAnteriores)) {
                            $totalResurtido = (int) $arrayMaxima [$contador] - ((int) $arrayExistente[$contador] + $pedidosAnteriores);
                            $arrayCantidadSurtido[$contador] = $totalResurtido;
                            if ($totalResurtido != "" && (int) $totalResurtido > 0) {
                                $resurtidoToner->setCantidadSurtido($totalResurtido);
                                if ((int) $totalResurtido > 0) {
                                    if ($resurtidoToner->newRegistro()) {
                                        $arraySolicitudTicket[$contador] = "(" . $totalResurtido . " - " . $arrayModelo[$contador] . ")";
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
                                $notaRefaccion->setUsuarioCreacion($user);
                                $notaRefaccion->setUsuarioModificacion($user);
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
                                $notaTicket->setUsuarioSolicitud($user);
                                $notaTicket->setMostrarCliente(1);
                                $notaTicket->setActivo(1);
                                $notaTicket->setUsuarioCreacion($user);
                                $notaTicket->setUsuarioModificacion($user);
                                $notaTicket->setPantalla("Solicitud de toner del mini almacén");
                                if ($notaTicket->newRegistro()) {//agregar nota refaccion
                                    $idNotaTicket = $notaTicket->getIdNota();
                                    $notaRefaccion->setIdNota($idNotaTicket);
                                    $notaRefaccion->setUsuarioCreacion($user);
                                    $notaRefaccion->setUsuarioModificacion($user);
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
                            } else {
                                echo "<br/>Error: no se pudo registrar el ticket de resurtido, favor de reportarlo al administrador del sistema<br/>";
                            }
                        }
                    }

                    $queryPendiente = $resurtidoToner->verificarResurtidoByAlamcen($parametros['almacen'], $idTicketNuevo);
                    if (mysql_num_rows($queryPendiente) > 0) {//verificar si ya existe resurtido   
                        $mail->setFrom($correo_emisor);
                        $mail->setSubject("Existe un resurtido de toner pendiente del almacen:" . $nombreALmacen);
                        $message = "<html><body>";
                        $usuario->getRegistroById($idUsuario);
                        $message .= "<h3>Hay una solicitud de toner pendiente del usuario:</h3><h4>" . $usuario->getPaterno() . " " . $usuario->getMaterno() . " " . $usuario->getNombre() . "</h4>";
                        $message .= "<h3>EL amacén $nombreALmacen tiene un resurtido de toner pendiente </h3>";
                        $texto1 = "<table border='1'>";
                        $texto1 .= "<tr><th>Ticket</th><th>Modelo</th><th>Cantidad</th><th>Fecha</th></tr>";

                        $cont = 0;
                        // $tamanoRegistro=  mysql_num_rows($queryPendiente);
                        while ($rs = mysql_fetch_array($queryPendiente)) {
                            $texto1 .= "<tr><td>" . $rs['IdTicket'] . "</td><td>" . $rs['Modelo'] . "</td><td>" . $rs['Cantidadresurtido'] . "</td><td>" . $rs['fecha'] . "</td></tr>";
                            $cont++;
                        }
                        $texto1 .= "</table>";
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
                    if (isset($_POST['empresa'])) {
                        $almacenComponenteTicket->setEmpresa($_POST['empresa']);
                    }
                    if ($idTicketPedidoAnterior != "") {
                        $almacenComponenteTicket->setIdTicket($idTicketPedidoAnterior);
                    } else {
                        $almacenComponenteTicket->setIdTicket($idTicketNuevo);
                    }
                    $almacenComponenteTicket->setIdAlmacen($parametros['almacen']);
                    $almacenComponenteTicket->setArrayNoParte($arrayNoParte);
                    $almacenComponenteTicket->setArrayApartados($arrayApartados);
                    $almacenComponenteTicket->setArrayExistente($arrayExistente);
                    $almacenComponenteTicket->setArrayMaxima($arrayMaxima);
                    $almacenComponenteTicket->setArrayMinima($arrayMinima);
                    $almacenComponenteTicket->setUsuarioCreacion($user);
                    $almacenComponenteTicket->setUsuarioModificacion($user);
                    $almacenComponenteTicket->setPantalla("Cambio de Tóner");
                    if ($almacenComponenteTicket->newRegistros()) {
                        
                    }
                    $mail->setFrom($correo_emisor);
                    $idTicketFinal = 0;
                    if ($idTicketPedidoAnterior != "") {
                        $idTicketFinal = $idTicketPedidoAnterior;
                        $idNotaUltima = $idNotaFucion;
                        echo "<br/>Se ha generado un resurtido que se fusionó al ticket: $idTicketFinal";
                    } else {
                        $idTicketFinal = $idTicketNuevo;
                        $idNotaUltima = $idNotaTicket;
                    }

                    if (empty($idTicketFinal) || !isset($idTicketFinal)) {
                        exit;
                    }

                    $mail->setSubject("Solicitud de toner del ticket: " . $idTicketFinal);
                    $message = "<html><body>";
                    $usuario->getRegistroById($idUsuario);
                    $message .= "<h3>Hay una solicitud de toner del usuario:</h3><h4>" . $usuario->getPaterno() . " " . $usuario->getMaterno() . " " . $usuario->getNombre() . "</h4>";
                    /*                     * ************************ Cuerpo del correo **************************** */
                    $resurtido = new ResurtidoToner();
                    $catalogo = new Catalogo();
                    if (isset($_POST['empresa'])) {
                        $resurtido->setEmpresa($_POST['empresa']);
                        $catalogo->setEmpresa($_POST['empresa']);
                    }
                    $idTicket = $idTicketFinal;
                    $resurtido->setIdTicket($idTicket);
                    $query = $resurtido->getTabla();
                    $primeraFila1 = "";
                    $primeraFila2 = "";
                    $tabla = "";
                    $almacen = "";
                    $idAlmacen = "";
                    $fecha = "";
                    $cliente = "";
                    $localidad = "";
                    $claveLocalidad = "";
                    $val = false;
                    $claveCliente = "";
                    $rowspan = 1;
                    $contestada = 0;
                    $filas = "";
                    $arrayNoTicketComponente = array();
                    $arrayComponenteModelo = array();
                    $arrayCantidadSolicitadaComponente = array();

                    while ($resultSet = mysql_fetch_array($query)) {

                        if ($primeraFila1 == "") {
                            $primeraFila1 .= "<td class='borde centrado'>" . $resultSet['ModeloT'] . "</td>";
                            $primeraFila1 .= "<td class='borde centrado'>" . $resultSet['precio'] . "</td>";
                            $primeraFila1 .= "<td class='borde centrado'>" . $resultSet['CantidadSolicitada'] . "</td>";
                            if ((int) $resultSet['mail'] == 1) {
                                if (isset($resultSet['Cantidad']) && $resultSet['Cantidad'] != "") {
                                    $primeraFila1 .= "<td class='borde centrado'>" . $resultSet['Cantidad'] . "</td>";
                                } else {
                                    $primeraFila1 .= "<td class='borde centrado'>" . 0 . "</td>";
                                }
                                if (isset($resultSet['existencia'])) {
                                    $primeraFila2 .= "<td class='borde centrado'>" . $resultSet['existencia'] . "</td>";
                                } else {
                                    $primeraFila2 .= "<td class='borde centrado'>N/A</td>";
                                    $nota = true;
                                }
                                if (isset($resultSet['minimo'])) {
                                    $primeraFila2 .= "<td class='borde centrado'>" . $resultSet['minimo'] . "</td>";
                                } else {
                                    $primeraFila2 .= "<td class='borde centrado'>N/A</td>";
                                    $nota = true;
                                }
                                if (isset($resultSet['maximo'])) {
                                    $primeraFila2 .= "<td class='borde centrado'>" . $resultSet['maximo'] . "</td>";
                                } else {
                                    $primeraFila2 .= "<td class='borde centrado'>N/A</td>";
                                    $nota = true;
                                }
                            } else {
                                if (isset($resultSet['existenciaA'])) {
                                    $primeraFila2 .= "<td class='borde centrado'>" . $resultSet['existenciaA'] . "</td>";
                                } else {
                                    $primeraFila2 .= "<td class='borde centrado'>N/A</td>";
                                    $nota = true;
                                }
                                if (isset($resultSet['minimoA'])) {
                                    $primeraFila2 .= "<td class='borde centrado'>" . $resultSet['minimoA'] . "</td>";
                                } else {
                                    $primeraFila2 .= "<td class='borde centrado'>N/A</td>";
                                    $nota = true;
                                }
                                if (isset($resultSet['maximoA'])) {
                                    $primeraFila2 .= "<td class='borde centrado'>" . $resultSet['maximoA'] . "</td>";
                                } else {
                                    $primeraFila2 .= "<td class='borde centrado'>N/A</td>";
                                    $nota = true;
                                }
                            }
                            $rowspan = 0;
                            $idTicketAnteriorComponente = $resurtido->ticketAnteriorResurtidoPorComponenteYAlmacen($resultSet['NoComponenteToner'], $idTicket, $resultSet['IdAlmacen']);
                            $arrayNoTicketComponente['' . $resultSet['NoComponenteToner']] = $idTicketAnteriorComponente;
                            $arrayComponenteModelo['' . $resultSet['NoComponenteToner']] = $resultSet['ModeloT'];
                            $arrayCantidadSolicitadaComponente['' . $resultSet['NoComponenteToner']] = (int) $resultSet['CantidadSolicitada'];
                        } else {
                            $filas .= "<tr>";
                            $filas .= "<td class='borde centrado'>" . $resultSet['ModeloT'] . "</td>";
                            $filas .= "<td class='borde centrado'>" . $resultSet['precio'] . "</td>";
                            $filas .= "<td class='borde centrado'>" . $resultSet['CantidadSolicitada'] . "</td>";
                            if ((int) $resultSet['mail'] == 1) {
                                if (isset($resultSet['Cantidad']) && $resultSet['Cantidad'] != "") {
                                    $filas .= "<td class='borde centrado'>" . $resultSet['Cantidad'] . "</td>";
                                } else {
                                    $filas .= "<td class='borde centrado'>" . 0 . "</td>";
                                }
                                if (isset($resultSet['existencia'])) {
                                    $filas .= "<td class='borde centrado'>" . $resultSet['existencia'] . "</td>";
                                } else {
                                    $filas .= "<td class='borde centrado'>N/A</td>";
                                    $nota = true;
                                }
                                if (isset($resultSet['minimo'])) {
                                    $filas .= "<td class='borde centrado'>" . $resultSet['minimo'] . "</td>";
                                } else {
                                    $filas .= "<td class='borde centrado'>N/A</td>";
                                    $nota = true;
                                }
                                if (isset($resultSet['maximo'])) {
                                    $filas .= "<td class='borde centrado'>" . $resultSet['maximo'] . "</td>";
                                } else {
                                    $filas .= "<td class='borde centrado'>N/A</td>";
                                    $nota = true;
                                }
                            } else {
                                if (isset($resultSet['existenciaA'])) {
                                    $filas .= "<td class='borde centrado'>" . $resultSet['existenciaA'] . "</td>";
                                } else {
                                    $filas .= "<td class='borde centrado'>N/A</td>";
                                    $nota = true;
                                }
                                if (isset($resultSet['minimoA'])) {
                                    $filas .= "<td class='borde centrado'>" . $resultSet['minimoA'] . "</td>";
                                } else {
                                    $filas .= "<td class='borde centrado'>N/A</td>";
                                    $nota = true;
                                }
                                if (isset($resultSet['maximoA'])) {
                                    $filas .= "<td class='borde centrado'>" . $resultSet['maximoA'] . "</td>";
                                } else {
                                    $filas .= "<td class='borde centrado'>N/A</td>";
                                    $nota = true;
                                }
                            }
                            $idTicketAnteriorComponente = $resurtido->ticketAnteriorResurtidoPorComponenteYAlmacen($resultSet['NoComponenteToner'], $idTicket, $resultSet['IdAlmacen']);
                            $arrayNoTicketComponente['' . $resultSet['NoComponenteToner']] = $idTicketAnteriorComponente;
                            $arrayComponenteModelo['' . $resultSet['NoComponenteToner']] = $resultSet['ModeloT'];
                            $arrayCantidadSolicitadaComponente['' . $resultSet['NoComponenteToner']] = (int) $resultSet['CantidadSolicitada'];
                            $filas .= "</tr>";
                        }
                        $rowspan++;
                        $fecha = $resultSet['Fecha'];
                        $almacen = $resultSet['almacen'];
                        $idAlmacen = $resultSet['IdAlmacen'];
                        $cliente = $resultSet['cliente'];
                        $localidad = $resultSet['localidad'];
                        $claveLocalidad = $resultSet['ClaveCentroCosto'];
                        $val = true;
                        $claveCliente = $resultSet['ClaveCliente'];
                        $contestada = (int) $resultSet['mail'];
                    }
                    if ($val == true) {
                        $tabla .= "<tr>";
                        $tabla .= "<td class='borde centrado' rowspan='$rowspan'>" . $idTicket . "</td>";
                        $tabla .= "<td class='borde centrado' rowspan='$rowspan'>" . $fecha . "</td>";

                        $tabla .= $primeraFila1;
                        if ($contestada != 1) {
                            $tabla .= "<td class='borde centrado' rowspan='$rowspan'>Sin autorizar</td>";
                        }
                        $tabla .= $primeraFila2;

                        $tabla .= "</tr>";
                        $tabla .= $filas;
                    }
                    if ($val == false) {
                        $tabla .= "<tr>";
                        $tabla .= "<td class='borde centrado' colspan='13'>No se encontraron datos que coincidieran con su búsqueda</td>";
                        $tabla .= "</tr>";
                    }

                    $consultaTickets = "SELECT lt.ClvEsp_Equipo AS NoSerie, nr.NoParteComponente AS NoParte, t.FechaHora AS Fecha,
                        c.Modelo AS Modelo, c.Descripcion AS Descripcion, nr.Cantidad AS Cantidad, t.IdTicket AS NoTicket,
                        a.nombre_almacen AS Almacen, t.NombreCliente AS Cliente, t.NombreCentroCosto AS Localidad,
                        lt.ContadorBN AS ContadorBN, lt.ContadorCL AS ContadorCL, lt.ModeloEquipo AS Equipo,
                        (lt.ContadorBN - lt2.ContadorBN) AS Impresiones, c.Rendimiento AS Rendimiento,
                        lt2.ContadorBN AS ContadorBNAnterior, lt2.ContadorCL AS ContadorCLAnterior, lt2.Fecha AS FechaAnterior
                        FROM c_ticket t 
                        INNER JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket
                        LEFT JOIN c_lecturasticket AS lt ON fk_idticket = t.IdTicket
                        LEFT JOIN k_nota_refaccion AS nr ON nt.IdNotaTicket = nr.IdNotaTicket
                        LEFT JOIN c_almacen AS a ON a.id_almacen = nr.IdAlmacen
                        LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
                        LEFT JOIN c_ticket AS ta ON ta.IdTicket = (SELECT MAX(t2.IdTicket) FROM c_ticket t2 
                            WHERE t2.IdTicket < $idTicket AND t2.Resurtido = 1 AND t2.ClaveCliente = '$claveCliente' AND t2.EstadoDeTicket = 2)
                        LEFT JOIN c_mailpedidotoner AS mpt ON mpt.IdTicket = ta.IdTicket
                        LEFT JOIN c_lecturasticket AS lt2 ON lt2.id_lecturaticket = 
                            (SELECT MAX(lta.id_lecturaticket) FROM c_lecturasticket lta 
                            LEFT JOIN c_ticket AS ta ON lta.fk_idticket = ta.IdTicket
                            INNER JOIN c_notaticket nt3 ON nt3.IdTicket=ta.IdTicket 
                            INNER JOIN k_nota_refaccion nr3 ON nt3.IdNotaTicket=nr3.IdNotaTicket 
                            INNER JOIN c_componente c2 ON c2.NoParte=nr3.NoParteComponente
                            WHERE lta.ClvEsp_Equipo = lt.ClvEsp_Equipo AND ta.Resurtido = 0 AND lta.id_lecturaticket <  lt.id_lecturaticket AND c2.IdColor=c.IdColor)
                        WHERE t.TipoReporte = 15 AND t.Resurtido = 0 AND t.FechaHora < '$fecha' AND a.id_almacen = " . $idAlmacen
                            . " AND c.NoParte IN (SELECT c2.NoParte FROM c_componente c2 INNER JOIN k_resurtidotoner AS rt2 ON rt2.NoComponenteToner = c2.NoParte WHERE rt2.IdTicket = $idTicket)
                        AND t.FechaHora > mpt.FechaUltimaModificacion 
                    GROUP BY t.IdTicket ORDER BY nr.NoParteComponente,t.IdTicket";
                    $resultTickets = $catalogo->obtenerLista($consultaTickets);

                    $message .= "<h3>Ticket de resurtido: $idTicket</h3>";
                    $message .= "<h3>Cliente: $cliente</h3>";
                    $message .= "<h3>Localidad: $localidad</h3>";
                    $message .= "<h3>Almacen: $almacen </h3>";
                    $message .= "<h3>Pedido: </h3>";
                    $message .= "<br/>";

                    $message .= "<table class='completeSize'>";
                    $message .= "<tr>";
                    $message .= "<th class='borde centrado'>Ticket</th>";
                    $message .= "<th class='borde centrado'>Fecha</th>";
                    $message .= "<th class='borde centrado'>Modelo</th>";
                    $message .= "<th class='borde centrado'>Precio USD</th>";
                    $message .= "<th class='borde centrado'>Cantidad Solicitada</th>";
                    $message .= "<th class='borde centrado'>Cantidad Surtida</th>";
                    $message .= "<th class='borde centrado'>Existencia</th>";
                    $message .= "<th class='borde centrado'>Mínimo</th>";
                    $message .= "<th class='borde centrado'>Máximo</th>";
                    $message .= "</tr>";
                    $message .= $tabla;
                    $message .= "</table>";
                    $message .= "<br/>";

                    $ticketAnterior = 0;
                    $fechaAnterior = "";
                    $ticketAnteriorConsulta = "SELECT t.IdTicket AS ticketAnterior, t.FechaHora FROM c_ticket t
                    WHERE t.IdTicket = (SELECT MAX(t2.IdTicket) FROM c_ticket t2 
                    WHERE t2.IdTicket < $idTicket AND t2.Resurtido = 1 AND t2.ClaveCliente = '$claveCliente')";
                    $resultTicketAnterior = $catalogo->obtenerLista($ticketAnteriorConsulta);
                    if ($rsTicketAnterior = mysql_fetch_array($resultTicketAnterior)) {
                        $ticketAnterior = $rsTicketAnterior['ticketAnterior'];
                        $fechaAnterior = $rsTicketAnterior['FechaHora'];
                    }
                    /* if($ticketAnterior != 0){
                      echo "Para consultar el ticket de resurtido anterior de este almacén haga clic <a href='reporte_toner_ticket.php?idTicket=$ticketAnterior'  target='_blank'>"
                      . " <img src='../resources/images/icono_impresora.png' width='20' height='20'></a>";
                      } */
                    //Vamos a mostrar los cambios de máximos y mínimos si es que hubo.
                    $queryCambiosMinimosMaximos = "SELECT cma.*,c.Modelo FROM k_cambiosminialmacen cma 
                        LEFT JOIN c_componente AS c ON c.NoParte = cma.NoParte 
                        WHERE cma.IdAlmacen = $idAlmacen AND cma.Fecha < '$fecha' AND cma.Fecha > '$fechaAnterior' AND 
                        cma.NoParte IN (SELECT c2.NoParte FROM c_componente c2 INNER JOIN k_resurtidotoner AS rt2 ON rt2.NoComponenteToner = c2.NoParte WHERE rt2.IdTicket = $idTicket)";
                    $resultCambios = $catalogo->obtenerLista($queryCambiosMinimosMaximos);
                    if (mysql_num_rows($resultCambios) > 0) {
                        $message .= "<h5>Ha habido cambios en los mínimos y máximos de un modelo";
                        $message .= "<table>";
                        $message .= "<tr>";
                        $message .= "<th>Modelo</th>";
                        $message .= "<th>Fecha</th>";
                        $message .= "<th>Min Anterior</th>";
                        $message .= "<th>Max Anterior</th>";
                        $message .= "<th>Min</th>";
                        $message .= "<th>Max</th>";
                        $message .= "</tr>";
                        while ($rsCambios = mysql_fetch_array($resultCambios)) {
                            $message .= "<tr>";
                            $message .= "<td>" . $rsCambios['Modelo'] . "</td>";
                            $message .= "<td>" . $rsCambios['Fecha'] . "</td>";
                            $message .= "<td>" . $rsCambios['MinimoAnterior'] . "</td>";
                            $message .= "<td>" . $rsCambios['MaximoAnterior'] . "</td>";
                            $message .= "<td>" . $rsCambios['MinimoNuevo'] . "</td>";
                            $message .= "<td>" . $rsCambios['MaximoNuevo'] . "</td>";
                            $message .= "</tr>";
                        }
                        $message .= "</table>";
                    }
                    $message .= "<br/>";
                    $message .= "<h4>Los toner que se cambiaron fueron:</h4>";
                    $message .= "<table class='tablaCompleta'>";
                    $message .= "<tr>";
                    $message .= "<th class='borde centrado'>Ticket</th>";
                    $message .= "<th class='borde centrado'>Fecha</th>";
                    $message .= "<th class='borde centrado'>Equipo</th>";
                    $message .= "<th class='borde centrado'>Serie</th>";
                    $message .= "<th class='borde centrado'>NoParte</th>";
                    $message .= "<th class='borde centrado'>Modelo</th>";
                    $message .= "<th class='borde centrado'>Contador Actual</th>";
                    $message .= "<th class='borde centrado'>Contador Anterior</th>";
                    $message .= "<th class='borde centrado'>Impresiones</th>";
                    $message .= "<th class='borde centrado'>Rendimiento</th>";
                    $message .= "<th class='borde centrado'>Localidad</th>";
                    $message .= "</tr>";

                    while ($rsTickets = mysql_fetch_array($resultTickets)) {
                        //Calculamos el porcentaje del rendimiento
                        $rendimientoTotal = 0;
                        if (isset($rsTickets['Rendimiento']) && $rsTickets['Rendimiento'] != "") {
                            $rendimientoTotal = (int) $rsTickets['Rendimiento'];
                        }
                        $impresiones = $rsTickets['Impresiones'];
                        $porcentajeRendimiento = 0;
                        if ($rendimientoTotal != 0) {
                            $porcentajeRendimiento = ($impresiones * 100) / $rendimientoTotal;
                        }

                        $message .= "<tr>";
                        $message .= "<td class='borde centrado'>" . $rsTickets['NoTicket'] . "</td>";
                        $message .= "<td class='borde centrado'>" . $rsTickets['Fecha'] . "</td>";
                        $message .= "<td class='borde centrado'>" . $rsTickets['Equipo'] . "</td>";
                        $message .= "<td class='borde centrado'>" . $rsTickets['NoSerie'] . "</td>";
                        $message .= "<td class='borde centrado'>" . $rsTickets['NoParte'] . "</td>";
                        $message .= "<td class='borde centrado'>" . $rsTickets['Modelo'] . "</td>";
                        $message .= "<td class='borde centrado'>" . $rsTickets['ContadorBN'] . "</td>";
                        $message .= "<td class='borde centrado'>" . $rsTickets['FechaAnterior'] . "<br/>" . $rsTickets['ContadorBNAnterior'] . "</td>";
                        $message .= "<td class='borde centrado'>" . $rsTickets['Impresiones'] . "</td>";
                        if ($porcentajeRendimiento == 0) {
                            if (!isset($rsTickets['ContadorBNAnterior']) || $rsTickets['ContadorBNAnterior'] == "") {
                                $message .= "<td class='borde centrado'>Sin rendimiento por lectura anterior</td>";
                            } else {
                                $message .= "<td class='borde centrado'>Sin rendimiento</td>";
                            }
                        } else {
                            if ($porcentajeRendimiento < 0) {
                                $message .= "<td class='borde centrado'> 0 % de <br/>" . $rsTickets['Rendimiento'] . "</td>";
                            } else {
                                $message .= "<td class='borde centrado'> " . number_format($porcentajeRendimiento) . "% de <br/>" . $rsTickets['Rendimiento'] . "</td>";
                            }
                        }
                        $message .= "<td class='borde centrado'>" . $rsTickets['Localidad'] . "</td>";
                        $message .= "</tr>";
                        $arrayCantidadSolicitadaComponente['' . $rsTickets['NoParte']] --;
                    }

                    $message .= "</table>";
                    $message .= "<br/>";

                    $primeraVez = true;
                    foreach ($arrayCantidadSolicitadaComponente as $key => $value) {
                        if ($value != 0) {
                            if ($primeraVez) {
                                $message .= "<h5>Los siguientes modelos tienen inconsistencias en la cantidad solicitada y "
                                        . "los cambios de tóner desde el último resurtido</h5>";
                                $primeraVez = false;
                            }
                            $message .= "Para el modelo: " . $arrayComponenteModelo[$key] . " el ticket anterior de resurtido es: ";
                            if ($arrayNoTicketComponente[$key] == "") {
                                $message .= "No hay ticket anterior de resurtido<br/>";
                            } else {
                                $message .= " <a href='" . $url . "reportes/reporte_toner_ticket.php?idTicket=" . $arrayNoTicketComponente[$key] . "'  target='_blank'>" . $arrayNoTicketComponente[$key] . "</a><br/>";
                            }
                        }
                    }
                    $consultaMovimientosComponentes = "SELECT mc.CantidadMovimiento, c.Modelo, mc.Fecha,
                        (CASE WHEN !ISNULL(mc.IdAlmacenAnterior) THEN 'Salida' ELSE 'Entrada' END) AS Tipo, mc.UsuarioCreacion
                        FROM movimiento_componente mc 
                        LEFT JOIN c_componente AS c ON c.NoParte = mc.NoParteComponente
                        WHERE (mc.IdAlmacenAnterior = $idAlmacen OR mc.IdAlmacenNuevo = $idAlmacen) 
                        AND mc.Fecha < '$fecha' AND mc.Fecha > '$fechaAnterior' AND mc.IdTicket IS NULL 
                        AND mc.NoParteComponente IN (SELECT c2.NoParte FROM c_componente c2 INNER JOIN k_resurtidotoner AS rt2 ON rt2.NoComponenteToner = c2.NoParte WHERE rt2.IdTicket = $idTicket) ";
                    $resultMovimientosComponente = $catalogo->obtenerLista($consultaMovimientosComponentes);
                    if (mysql_num_rows($resultMovimientosComponente)) {
                        $message .= "<h5>Hubo cambios manuales en este almacen</h5>";
                        $message .= "<table>";
                        $message .= "<tr>";
                        $message .= "<th>Modelo</th>";
                        $message .= "<th>Fecha</th>";
                        $message .= "<th>Tipo</th>";
                        $message .= "<th>CantidadMovimiento</th>";
                        $message .= "<th>Usuario de Modificación</th>";
                        $message .= "</tr>";
                        while ($rsMovimientosComponente = mysql_fetch_array($resultMovimientosComponente)) {
                            $message .= "<tr>";
                            $message .= "<td>" . $rsMovimientosComponente['Modelo'] . "</td>";
                            $message .= "<td>" . $rsMovimientosComponente['Fecha'] . "</td>";
                            $message .= "<td>" . $rsMovimientosComponente['Tipo'] . "</td>";
                            $message .= "<td class='centrado'>" . $rsMovimientosComponente['CantidadMovimiento'] . "</td>";
                            $message .= "<td>" . $rsMovimientosComponente['UsuarioCreacion'] . "</td>";
                            $message .= "</tr>";
                        }
                        $message .= "</table>";
                    }
                    /*                     * ************************ Cuerpo del correo **************************** */
                    $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud=1;");
                    $correos = array();
                    $z = 0;
                    while ($rs = mysql_fetch_array($query4)) {
                        $correos[$z] = $rs['correo'];
                        $z++;
                    }
                    $message .= $texto1;
                    // Guardamos y creamos la liga para aceptar/rechazar la solicitud directamente 
                    $clave = $mail->generaPass();
                    $idMail = $catalogo->insertarRegistro("INSERT INTO c_mailpedidotoner(idTicket, contestada, clave, IdUsuario, Activo, UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                                        VALUES($idTicketFinal,0,MD5('$clave'),2,1,'" . $user . "',now(),'" . $user . "',now(),'nueva_solicitud.php');");
                    $liga = "$url/aceptarPedidoToner.php?clv=$clave&idTicket=$idTicketFinal&idMail=$idMail&idNota=$idNotaUltima&tipo";

                    // $message = $message . "<br/>Autorizar solicitud: " . $liga . "=1&uguid=" . $idEmpresa . " <br/><br/>";
                    // $message = $message . "<br/>Rechazar solicitud: " . $liga . "=3&uguid=" . $idEmpresa . " <br/><br/>";
                    $message = $message . "<br/><a href='". $liga . "=1&uguid=" . $idEmpresa ."' style='padding: 10px; background:#12CE4D; color:white; border-radius:3px;text-align:center;border:0px;text-decoration:none'>Autorizar solicitud</a> ";
                    $message = $message . "<br/><a href='". $liga . "=3&uguid=" . $idEmpresa ."' style='padding: 10px; background:#C41818; color:white; border-radius:3px;text-align:center;border:0px;text-decoration:none'>Rechazar solicitud</a> ";                    
                    
                    $message .= "</body></html>";
                    $mail->setBody($message);
                    foreach ($correos as $value) {
                        if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {// Si el correo es valido
                            $mail->setTo($value);
                            if ($mail->enviarMail() == "1") {
                                // echo "Un correo fue enviado para la autorización.";
                            } else {
                                echo "Error: No se pudo enviar el correo para autorizar.";
                            }
                        }
                    }
                }//FIN RESURTIDO MINI ALMACEN
                //echo $message;
            } else {
                echo "Error: La salida del almacen no se generó exitosamente";
            }
        } else {
            echo "Error: La solicitud no pudo ser atendida.";
        }
    } else {
        echo "Error: El almacen no cuenta con el toner solicitado.";
    }
}
?>