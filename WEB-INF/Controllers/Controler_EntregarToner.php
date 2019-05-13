<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/SolicitudToner.class.php");
include_once("../Classes/Ticket.class.php");
include_once("../Classes/LecturaTicket.class.php");
include_once("../Classes/Componente.class.php");
include_once("../Classes/Catalogo.class.php");
include_once("../Classes/NotaTicket.class.php");
include_once("../Classes/NotaRefaccion.class.php");
include_once("../Classes/AlmacenConmponente.class.php");
include_once("../Classes/AlmacenComponenteTicket.class.php");
include_once("../Classes/MovimientoComponente.class.php");
include_once("../Classes/ResurtidoToner.class.php");
include_once("../Classes/AgregarNota.class.php");
include_once("../Classes/Vehiculo.class.php");
include_once("../Classes/Mensajeria.class.php");
include_once("../Classes/Conductor.class.php");
include_once("../Classes/Incidencia.class.php");
include_once("../Classes/Pedido.class.php");
include_once("../Classes/Almacen.class.php");
include_once("../Classes/Mail.class.php");
include_once("../Classes/Usuario.class.php");
include_once("../Classes/ParametroGlobal.class.php");
include_once("../Classes/Parametros.class.php");

$obj = new SolicitudToner();
//$ticket1 = new Ticket();
$movimientoCompoenente = new MovimientoComponente();
$almacenComponente = new AlmacenComponente();
$resurtidoToner = new ResurtidoToner();
$notaTicket = new NotaTicket();
$pedido1 = new Pedido();
$notaRefaccion = new NotaRefaccion();
$componente = new Componente();
$catalogo = new Catalogo();
$surtidoCompleto = false;
$ticket = new Ticket();
$ticket_obj = new Ticket();
$idLecturaTicket = "";
$mail = new Mail();
$usuario = new Usuario();
$correo_emisor = "";
$parametroGlobal = new ParametroGlobal();
$parametroSistema = new Parametros();

if ($parametroGlobal->getRegistroById("8")) {
    $correo_emisor = ($parametroGlobal->getValor());
} else {
    $correo_emisor = ("scg-salida@scgenesis.mx");
}

$url = "http://genesis.techra.com.mx/";
if ($parametroSistema->getRegistroById(8)) {
    $url = $parametroSistema->getDescripcion();
}

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}
$num_envios = intval($parametros['contador_envios']);
$numeros_surtidos_ticket = array();

for ($i = 0; $i < $num_envios; $i++) {
    $surtidoCompleto = false;
    $ticket_obj->getTicketByID($parametros['idTicket' . $i]);
    //Vamos a verificar que ya estén surtidos todos los tóner para no volverlos a mandar, esto pasa por los dobles clics
    //Obtenemos primero los surtidos del ticket
    $consultaSolicitados = ("SELECT SUM(nr.Cantidad) AS total,nt.IdTicket 
        FROM c_notaticket nt,k_nota_refaccion nr 
        WHERE nt.IdTicket= " . $parametros['idTicket' . $i] . "
        AND (nt.IdEstatusAtencion=67) AND nr.IdNotaTicket=nt.IdNotaTicket AND nr.NoParteComponente = '" . $parametros['noparte' . $i] . "';");
    $querySolicitados = $catalogo->obtenerLista($consultaSolicitados);
    while ($rsSolicitados = mysql_fetch_array($querySolicitados)) {
        $cantidadTotalSolicitada = $rsSolicitados['total'];
    }

    $consultaAtendido = ("SELECT SUM(nr.Cantidad) AS total 
        FROM c_notaticket nt,k_nota_refaccion nr 
        WHERE nt.IdTicket= " . $parametros['idTicket' . $i] . "
        AND (nt.IdEstatusAtencion=66 OR nt.IdEstatusAtencion = 68) AND nr.IdNotaTicket=nt.IdNotaTicket AND nr.NoParteComponente = '" . $parametros['noparte' . $i] . "';");
    $queryAtendido = $catalogo->obtenerLista($consultaAtendido);
    while ($rsAtendido = mysql_fetch_array($queryAtendido)) {
        $cantidadTotalSurtida = $rsAtendido['total'];
    }

    if (intval($cantidadTotalSurtida) >= intval($cantidadTotalSolicitada)) {
        $surtidoCompleto = true;
    }

    if (!$surtidoCompleto) {
        /* validamos si ya todo fue enviado o no */
        $consulta = "SELECT SUM(nr.Cantidad) AS total,nt.IdTicket, nr.NoParteComponente, c.Modelo,
                (
                SELECT SUM(CantidadMovimiento) FROM movimiento_componente AS mc WHERE mc.IdTicket = nt.IdTicket AND mc.NoParteComponente = nr.NoParteComponente AND mc.Entradada_Salida = 0
                ) AS Surtidas
                FROM c_notaticket nt
                LEFT JOIN k_nota_refaccion nr ON nr.IdNotaTicket=nt.IdNotaTicket
                LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
                WHERE 
                nt.IdTicket = " . $parametros['idTicket' . $i] . " AND nr.NoParteComponente = '" . $parametros['noparte' . $i] . "'
                AND (nt.IdEstatusAtencion=67)
                GROUP BY nr.NoParteComponente, nt.IdTicket
                HAVING total > 0
                ORDER BY nt.IdTicket;";
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            if (((int) $rs['Surtidas'] + (int) $parametros['cantidad' . $i]) > (int) $rs['total']) {
                echo "<br/>Atención: no se pueden enviar " . $parametros['cantidad' . $i] . " de la pieza " . $rs['Modelo'] . "/" . $rs['NoParteComponente'] . ", ya se han enviado " . $rs['Surtidas'] . " de " . $rs['total'];
                continue 2;
            }
        }
    }
    
    if (!$surtidoCompleto) {
        /* Agregamos la nota de surtido en el ticket */
        $nota = new AgregarNota();
        $nota->setIdTicket($parametros['idTicket' . $i]);
        $nota->setDiagnosticoSolucion("");
        $nota->setIdestatusAtencion("70");
        $nota->setShow("1");
        $nota->setActivo("1");
        $nota->setUsuarioCreacion($_SESSION['user']);
        $nota->setUsuarioSolicitud($_SESSION['user']);
        $nota->setUsuarioModificacion($_SESSION['user']);
        $nota->setPantalla("PHP Controler_EntregarToner");
        $nota->newRegistro();
        //agregar nota
        $obj->setNotaAnterior($parametros['nota' . $i]);
        $obj->setIdEstadoNota(66);
        $obj->setUsuarioCreacion($_SESSION['user']);
        $obj->setUsuarioModificacion($_SESSION['user']);
        $obj->setPantalla("Entregar de toner");
        $obj->setMostrarCliente(0);
        /*Buscamos el id del ultimo surtido parcial*/
        if(isset($numeros_surtidos_ticket[$parametros['idTicket' . $i]])){
            $numeroSurtido = $numeros_surtidos_ticket[$parametros['idTicket' . $i]];
        }else{
            $numeroSurtido = 1;
            $consulta = "SELECT (CASE WHEN !ISNULL(MAX(NumeroSurtido)) THEN MAX(NumeroSurtido) ELSE 0 END) AS NumeroSurtido
                FROM `c_notaticket` AS nt 
                WHERE IdTicket = (SELECT IdTicket FROM c_notaticket WHERE IdNotaTicket = ".$obj->getNotaAnterior().");";
            $result = $catalogo->obtenerLista($consulta);
            while($rs = mysql_fetch_array($result)){
                if(isset($rs['NumeroSurtido']) && !empty($rs['NumeroSurtido'])){
                    $numeroSurtido = (int)$rs['NumeroSurtido'] + 1;
                }
            }
            $numeros_surtidos_ticket[$parametros['idTicket' . $i]] = $numeroSurtido;
        }
        $obj->setNumeroSurtido($numeroSurtido);
        
        $descripcion = "";

        if (isset($parametros['tipoMensajeria']) && $parametros['tipoMensajeria'] == "1") {//Transporte propio
            $vehiculo = new Vehiculo();
            $conductor = new Conductor();
            if ($parametros['tranportepropio'] != "") {
                $vehiculo->getRegistroById($parametros['tranportepropio']);
            } else {
                $vehiculo->setPlacas("");
                $vehiculo->setModelo("");
            }
            if ($parametros['conductor'] != "") {
                $conductor->getRegistroById($parametros['conductor']);
            } else {
                $conductor->setNombre("");
                $conductor->setApellidoPaterno("");
                $conductor->setApellidoMaterno("");
            }
            $descripcion = "Envío con vehículo " . $vehiculo->getPlacas() . "/" . $vehiculo->getModelo() . " y conductor " . $conductor->getNombre() . " " . $conductor->getApellidoPaterno() . " " . $conductor->getApellidoMaterno();
        } else if (isset($parametros['tipoMensajeria']) && $parametros['tipoMensajeria'] == "2") {
            $mensajeria = new Mensajeria();
            $mensajeria->getRegistroById($parametros['tranporteMensajeria']);
            $descripcion = "Envío por " . $mensajeria->getNombre() . " con numero de guia: " . $parametros['noGuia'];
        } else {
            $descripcion = "Envío otros: " . $parametros['otro'];
        }


        $obj->setDescripcion($descripcion);
        if ($obj->newNotaSolicitudToner()) {
            //agregar nota refaccion
            $obj->setIdAlmacen($parametros['almacen' . $i]);
            $obj->setNoParteComponente($parametros['noparte' . $i]);
            $obj->setCantidadSolicitada($parametros['cantidad' . $i]);
            if ($obj->InsertNotaToner()) {
                if ($obj->EditCantidadPedidoEntrega($parametros['cantidad' . $i])) {
                    if ($obj->editAlmacenComponenteApartado($parametros['cantidad' . $i])) {//restar las cantidades apartadas
                        //rusurtir mini almacen
                        $diagnosticoNota = $parametros['diagnostico' . $i];
                        $findme = 'Solicitud';
                        $findme2 = 'resurtido';
                        $pos01 = strpos($diagnosticoNota, $findme);
                        $pos11 = strpos($diagnosticoNota, $findme2);
                        $miniAlmacen = "";
                        if ($pos01 !== FALSE && $pos11 !== FALSE) {
                            $miniAlmacen = $obj->getAlmacenByCC($parametros['localidad' . $i]);
                            if ($miniAlmacen == 0) {
                                echo "Error: no se encontró el mini-almacén a surtir";
                                exit;
                            }
                            $noParteToner = $parametros['noparte' . $i];
                            $cantidadResurtido = $parametros['cantidad' . $i];

                            $almacenComponente->setIdAlmacen($miniAlmacen);
                            $almacenComponente->setNoParte($noParteToner);
                            $almacenComponente->setCantidadSalida($cantidadResurtido);
                            $almacenComponente->setUsuarioModificacion($_SESSION['user']);
                            $almacenComponente->setPantalla("Resurtido de mini almacén");

                            $almacen_obj = new Almacen();
                            //echo $miniAlmacen." - ".$parametros['almacen'.$i];
                            /*if ($almacen_obj->getRegistroById($miniAlmacen) && $almacen_obj->getSurtir() == "0") {
                                $resultCompatibles = $obj->getTonerCompatible($noParteToner);
                                while ($rsCompatible = mysql_fetch_array($resultCompatibles)) {
                                    if ($almacenComponente->getRegistroById($rsCompatible['ParteCompatible'], $miniAlmacen)) {
                                        $almacenComponente->setNoParte($rsCompatible['ParteCompatible']);
                                        break;
                                    }
                                }
                            } else {
                                
                            }*/

                            //Por si no esta registrado el toner en el mini almacen
                            $almacenComponente->setExistencia($cantidadResurtido);
                            $almacenComponente->setApartados(0);
                            $almacenComponente->setMinimo(0);
                            $almacenComponente->setMaximo(0);
                            $almacenComponente->setUbicacion("");
                            $almacenComponente->setUsuarioCreacion($_SESSION['user']);
                            if (($almacenComponente->getRegistroById($almacenComponente->getNoParte(), $miniAlmacen) &&
                                    $almacenComponente->editarCantidadAlmacenReusrtir()) || $almacenComponente->newRegistro()) {
                                if ($noParteToner != $almacenComponente->getNoParte()) {//Si se surtio en el mini-almacen un # parte diferente al que se había pedido porque se encontró compatibilidad
                                    $incidencia = new Incidencia();
                                    $pedido = new Pedido();
                                    $almacen = new Almacen();

                                    $pedido->getPedidoByIdTicket($ticket_obj->getIdTicket());
                                    $almacen->getRegistroById($almacenComponente->getIdAlmacen());

                                    $incidencia->setNoSerie($ticket_obj->getNoSerieEquipo());
                                    $incidencia->setFecha(date('Y') . "-" . date('m') . "-" . date('d'));
                                    $incidencia->setFechaFin(date('Y') . "-" . date('m') . "-" . date('d'));
                                    $incidencia->setDescripcion("Se surtió el toner " . $almacenComponente->getNoParte() . " en vez de $noParteToner al almacen " . $almacen->getNombre() . " ($miniAlmacen) por compatibilidad");
                                    $incidencia->setStatus(1);
                                    $incidencia->setClaveCentroCosto($ticket_obj->getClaveCentroCosto());
                                    $incidencia->setId_Ticket($ticket_obj->getIdTicket());
                                    $incidencia->setActivo(1);
                                    $incidencia->setUsuarioCreacion($almacenComponente->getUsuarioCreacion());
                                    $incidencia->setUsuarioUltimaModificacion($almacenComponente->getUsuarioModificacion());
                                    $incidencia->setPantalla($almacenComponente->getPantalla());
                                    $incidencia->setIdTipoIncidencia(6);

                                    if (!$incidencia->newRegistro()) {
                                        echo "No se registró la incidencia de cambio de surtido de toner";
                                    }
                                }

                                //modificar resurtido
                                if (!$almacenComponente->editarResurtidoReporte($miniAlmacen, $parametros['noparte' . $i])) {
                                    //echo "Error: No se modifico lo del resurtido";
                                }

                                $movimientoCompoenente->setIdTicket($parametros['idTicket' . $i]);
                                $movimientoCompoenente->setNoParteComponente($noParteToner);
                                $movimientoCompoenente->setCantidadMovimiento($cantidadResurtido);
                                $movimientoCompoenente->setIdAlmacenAnterior($parametros['almacen' . $i]);
                                $movimientoCompoenente->setIdAlmacenNuevo($miniAlmacen);
                                $movimientoCompoenente->setEntradaSalida(0);
                                $movimientoCompoenente->setUsuarioCreacion($_SESSION['user']);
                                $movimientoCompoenente->setUsuarioModificacion($_SESSION['user']);
                                $movimientoCompoenente->setPantalla("Entregar de toner");
                                $movimientoCompoenente->newRegistro();

                                echo "<br/>El mini almacen se ha resurtido exitosamente <br/>";
                            } else {
                                echo "Error: El mini almacén no se resurtió <br/>";
                                return;
                            }
                        }
                        $componente->getRegistroById($parametros['noparte' . $i]);
                        echo "<br/> La nota de envío del componente " . $componente->getModelo() . " se registró correctamente";

                        //Verificamos que sea un almacén de donde se pueda resurtir.
                        $almacen1000 = new Almacen();
                        if ($almacen1000->verificarAlmacenParaSurtirByAlmacen($parametros['almacen' . $i])) {
                            /* Hay que chechar todos los tickets de resurtido de este almacén que sigan abiertos, y verificar
                             * que en estos tickets se encuentre este NoParte si está tomar en cuenta los que se han pedido 
                             * más los que se tienen ahora */
                            $almacenComponenteAux = new AlmacenComponente();
                            $pedidosAnteriores = 0;
                            $queryChecarResurtidosAnteriores = "SELECT rt.CantidadResurtido, nr.CantidadSurtida FROM k_resurtidotoner rt 
                               LEFT JOIN c_ticket t ON rt.IdTicket = t.IdTicket 
                               INNER JOIN c_notaticket nt ON (nt.IdTicket = t.IdTicket AND nt.IdEstatusAtencion = 67)
                               INNER JOIN k_nota_refaccion nr ON (nr.NoParteComponente = rt.NoComponenteToner AND nr.IdNotaTicket = nt.IdNotaTicket)
                               WHERE rt.NoComponenteToner = '" . $parametros['noparte' . $i] . "' AND t.EstadoDeTicket <> 2 AND t.EstadoDeTicket <> 4
                               AND rt.IdAlmacen = " . $parametros['almacen' . $i];
                            $resultChecarResurtidosAnteriores = $catalogo->obtenerLista($queryChecarResurtidosAnteriores);
                            while ($rsChecarResurtidosAnteriores = mysql_fetch_array($resultChecarResurtidosAnteriores)) {
                                $pedidosAnteriores += ((int) $rsChecarResurtidosAnteriores['CantidadResurtido'] - (int) $rsChecarResurtidosAnteriores['CantidadSurtida']);
                            }
                            //verificar stock minimo
                            $nombreALmacen = "";
                            $almacenComponenteAux->getRegistroById($parametros['noparte' . $i], $parametros['almacen' . $i]);
                            $cantidadExistente = $almacenComponenteAux->getExistencia() + $pedidosAnteriores;
                            $cantidadMinima = $almacenComponenteAux->getMinimo();
                            $cantidadMaxima = $almacenComponenteAux->getMaximo();
                            if ((int) $cantidadExistente < (int) $cantidadMinima) {//agregar resurtido de toner
                                $almacen_obj = new Almacen();
                                $almacen_obj->getRegistroMiniAlmacenByIdLocalidad($parametros['almacen' . $i]);
                                $array_localidades = $almacen_obj->getLocalidades();
                                if (!isset($array_localidades[0]) || empty($array_localidades[0])) {
                                    echo "Error: el mini-almacen destino no tiene una localidad asociada para generar un resurtido";
                                    continue;
                                }


                                //obtener todos los toner del almacen
                                $almacenComponenteAux->getComponentesAlmacen($parametros['almacen' . $i]);
                                $arrayNoParte = $almacenComponenteAux->getArrayNoParte();
                                $arrayExistente = $almacenComponenteAux->getArrayExistente();
                                $arrayMaxima = $almacenComponenteAux->getArrayMaxima();
                                $arrayModelo = $almacenComponenteAux->getArrayModelo();
                                $arrayDescripcion = $almacenComponenteAux->getArrayDescripcion();
                                $arrayApartados = $almacenComponenteAux->getArrayApartados();
                                $arrayMinima = $almacenComponenteAux->getArrayMinima();
                                $nombreALmacen = $almacenComponenteAux->getNombreAlamcen();
                                $contador = 0;
                                $resurtidoToner->setIdAlmacen($parametros['almacen' . $i]);
                                $resurtidoToner->setUsuarioCreacion($_SESSION['user']);
                                $resurtidoToner->setUsuarioModificacion($_SESSION['user']);
                                $resurtidoToner->setPantalla("Solicitud de toner del mini almacén2");
                                $idTicketPedidoAnterior = "";
                                $idMailFusionado = "";

                                if ($resurtidoToner->verificarAlmacenTicketExistente()) {//verificar si  existe un ticket de almacen de resurtido pendiente
                                    $idTicketPedidoAnterior = $resurtidoToner->getIdTicketF();
                                    $idMailFusionado = $resurtidoToner->getIdMail();
                                    $resurtidoToner->setIdTicket($idTicketPedidoAnterior);
                                } else {
                                    $resurtidoToner->setIdTicket($idTicket);
                                }

                                $resurtidoToner->setIdTicket($parametros['idTicket' . $i]);

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
                                        AND rt.IdAlmacen = " . $parametros['almacen' . $i];
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
                                                    echo "No se generó resurtido de toner";
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
                                            $notaRefaccion->setPantalla("Solicitud de toner del mini almacén2");
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
                                        $almacen_obj = new Almacen();
                                        $almacen_obj->getRegistroMiniAlmacenByIdLocalidad($parametros['almacen' . $i]);
                                        $array_localidades = $almacen_obj->getLocalidades();
                                        if (!isset($array_localidades[0]) || empty($array_localidades[0])) {
                                            echo "Error: el mini-almacen destino no tiene una localidad asociada para generar un resurtido";
                                            continue;
                                        }
                                        $ticket->setEstadoDeTicket(3);
                                        $descripcion = "Solicitud de resurtido de los toners: " . implode(",", $arraySolicitudTicket) . " del almacén:" . $nombreALmacen . " proveniente del ticket: $idTicket";
                                        $ticket->setDescripcionReporte($descripcion);
                                        $ticket->setResurtido(1);
                                        $ticket->setTipoReporte(15);
                                        $ticket->setNombreCliente($ticket_obj->getNombreCliente());
                                        $ticket->setClaveCentroCosto($array_localidades[0]);
                                        $ticket->setClaveCliente($ticket_obj->getClaveCliente());
                                        $ticket->setNombreCentroCosto($parametros['nombreCentroCosto']);
                                        $ticket->setNoSerieEquipo($null);
                                        $ticket->setModeloEquipo($null);
                                        $ticket->setAreaAtencion(2);
                                        $ticket->setActivo(1);
                                        $ticket->setUsuario($_SESSION['user']);
                                        $ticket->setUsuarioCreacion($_SESSION['user']);
                                        $ticket->setUsuarioUltimaModificacion($_SESSION['user']);
                                        $ticket->setPantalla("Envío de tóner");
                                        $ticket->setUbicacion(1);
                                        if ($ticket->newRegistroResurtido()) {
                                            $idTicketNuevo = $ticket->getIdTicket();
                                            $resurtidoToner->editRegistroTicket($parametros['idTicket' . $i], $idTicketNuevo);
                                            $idTicketNota = $ticket->getIdTicket();
                                            $notaTicket->setIdTicket($idTicketNota);
                                            $notaTicket->setDiagnostico("Solicitud de resurtido de toners:");
                                            $notaTicket->setIdEstatus(67);
                                            $notaTicket->setUsuarioSolicitud($_SESSION['user']);
                                            $notaTicket->setMostrarCliente(1);
                                            $notaTicket->setActivo(1);
                                            $notaTicket->setUsuarioCreacion($_SESSION['user']);
                                            $notaTicket->setUsuarioModificacion($_SESSION['user']);
                                            $notaTicket->setPantalla("Solicitud de toner del mini almacén2");
                                            if ($notaTicket->newRegistro()) {//agregar nota refaccion
                                                $idNotaTicket = $notaTicket->getIdNota();
                                                $notaRefaccion->setIdNota($idNotaTicket);
                                                $notaRefaccion->setUsuarioCreacion($_SESSION['user']);
                                                $notaRefaccion->setUsuarioModificacion($_SESSION['user']);
                                                $notaRefaccion->setPantalla("Solicitud de toner del mini almacén2");
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
                                            //Buscar modelo
                                            $NoSerieEquipo6 = "";
                                            $buscarModelo = "SELECT dnr.NoSerieEquipo, e.Modelo FROM k_detalle_notarefaccion dnr
                                                 INNER JOIN c_notaticket nt ON nt.IdNotaTicket = dnr.IdNota
                                                 INNER JOIN c_ticket t ON t.IdTicket = nt.IdTicket
                                                 INNER JOIN c_bitacora b ON b.NoSerie = dnr.NoSerieEquipo
                                                 INNER JOIN c_equipo e ON e.NoParte = b.NoParte
                                                 WHERE t.IdTicket = " . $parametros['idTicket' . $i] . " AND nt.IdEstatusAtencion = 67 AND dnr.Componente = '" . $parametros['noparte' . $i] . "';";
                                            $resultModelo = $catalogo->obtenerLista($buscarModelo);
                                            if ($rsModelo = mysql_fetch_array($resultModelo)) {
                                                $NoSerieEquipo6 = $rsModelo['NoSerieEquipo'];
                                                $Modelo999 = $rsModelo['Modelo'];
                                            }
                                            $pedido1->setClaveEspEquipo($NoSerieEquipo6);
                                            $pedido1->setModelo($Modelo999);
                                            $pedido1->setUsuarioCreacion($_SESSION['user']);
                                            $pedido1->setUsuarioUltimaModificacion($_SESSION['user']);
                                            $pedido1->setPantalla("Envío de tóner");
                                            $pedido1->setActivo(1);
                                            //Obtener la lectura del ticket
                                            $lecturaTicket = new LecturaTicket();
                                            $lecturaTicket->getLecturaByTicket($parametros['idTicket' . $i]);
                                            $idLecturaTicket = $lecturaTicket->getIdLectura();

                                            $x = 0;
                                            while ($x < count($arrayDescripcion)) {
                                                if ($arrayCantidadSurtido[$x] != "0" && $arrayCantidadSurtido[$x] != "") {
                                                    $pedido1->setIdTicket($ticket->getIdTicket());
                                                    //Obtener el Id de lectura de este Modelo

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

                                $queryPendiente = $resurtidoToner->verificarResurtidoByAlamcen($parametros['almacen' . $i], $idTicketNuevo);
                                if (mysql_num_rows($queryPendiente) > 0) {//verificar si ya existe resurtido   
                                    $mail->setFrom($correo_emisor);
                                    $mail->setSubject("Existe un resurtido de toner pendiente del almacen:" . $nombreALmacen);
                                    $message = "<html><body>";
                                    $usuario->getRegistroById($_SESSION['idUsuario']);
                                    $message .= "<h3>Hay una solicitud de toner pendiente del usuario:</h3><h4>" . $usuario->getPaterno() . " " . $usuario->getMaterno() . " " . $usuario->getNombre() . "</h4>";
                                    $message .= "<h3>El almacén $nombreALmacen tiene un resurtido de toner pendiente </h3>";
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
                                if ($idTicketPedidoAnterior != "") {
                                    $almacenComponenteTicket->setIdTicket($idTicketPedidoAnterior);
                                } else {
                                    $almacenComponenteTicket->setIdTicket($idTicketNuevo);
                                }
                                $almacenComponenteTicket->setIdAlmacen($parametros['almacen' . $i]);
                                $almacenComponenteTicket->setArrayNoParte($arrayNoParte);
                                $almacenComponenteTicket->setArrayApartados($arrayApartados);
                                $almacenComponenteTicket->setArrayExistente($arrayExistente);
                                $almacenComponenteTicket->setArrayMaxima($arrayMaxima);
                                $almacenComponenteTicket->setArrayMinima($arrayMinima);
                                $almacenComponenteTicket->setUsuarioCreacion($_SESSION['user']);
                                $almacenComponenteTicket->setUsuarioModificacion($_SESSION['user']);
                                $almacenComponenteTicket->setPantalla("Cambio de Tóner");
                                if ($almacenComponenteTicket->newRegistros()) {
                                    
                                }
                                $mail->setFrom($correo_emisor);
                                $idTicketFinal = 0;
                                if ($idTicketPedidoAnterior != "") {
                                    $idTicketFinal = $idTicketPedidoAnterior;
                                    $idNotaUltima = $idNotaFucion;
                                    echo "Se ha generado un resurtido que se fusionó al ticket: $idTicketFinal";
                                } else {
                                    $idTicketFinal = $idTicketNuevo;
                                    $idNotaUltima = $idNotaTicket;
                                }

                                if (empty($idTicketFinal) || !isset($idTicketFinal)) {
                                    exit;
                                }

                                $mail->setSubject("Solicitud de toner del ticket: " . $idTicketFinal);
                                $message = "<html><body>";
                                $usuario->getRegistroById($_SESSION['idUsuario']);
                                $message .= "<h3>Hay una solicitud de toner del usuario:</h3><h4>" . $usuario->getPaterno() . " " . $usuario->getMaterno() . " " . $usuario->getNombre() . "</h4>";
                                /*                                 * ************************ Cuerpo del correo **************************** */
                                $resurtido = new ResurtidoToner();
                                $catalogo = new Catalogo();
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
                                        $primeraFila1.= "<td class='borde centrado'>" . $resultSet['ModeloT'] . "</td>";
                                        $primeraFila1.= "<td class='borde centrado'>" . $resultSet['precio'] . "</td>";
                                        $primeraFila1.= "<td class='borde centrado'>" . $resultSet['CantidadSolicitada'] . "</td>";
                                        if ((int) $resultSet['mail'] == 1) {
                                            if (isset($resultSet['Cantidad']) && $resultSet['Cantidad'] != "") {
                                                $primeraFila1.= "<td class='borde centrado'>" . $resultSet['Cantidad'] . "</td>";
                                            } else {
                                                $primeraFila1.= "<td class='borde centrado'>" . 0 . "</td>";
                                            }
                                            if (isset($resultSet['existencia'])) {
                                                $primeraFila2.= "<td class='borde centrado'>" . $resultSet['existencia'] . "</td>";
                                            } else {
                                                $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                                                $nota = true;
                                            }
                                            if (isset($resultSet['minimo'])) {
                                                $primeraFila2.= "<td class='borde centrado'>" . $resultSet['minimo'] . "</td>";
                                            } else {
                                                $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                                                $nota = true;
                                            }
                                            if (isset($resultSet['maximo'])) {
                                                $primeraFila2.= "<td class='borde centrado'>" . $resultSet['maximo'] . "</td>";
                                            } else {
                                                $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                                                $nota = true;
                                            }
                                        } else {
                                            if (isset($resultSet['existenciaA'])) {
                                                $primeraFila2.= "<td class='borde centrado'>" . $resultSet['existenciaA'] . "</td>";
                                            } else {
                                                $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                                                $nota = true;
                                            }
                                            if (isset($resultSet['minimoA'])) {
                                                $primeraFila2.= "<td class='borde centrado'>" . $resultSet['minimoA'] . "</td>";
                                            } else {
                                                $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                                                $nota = true;
                                            }
                                            if (isset($resultSet['maximoA'])) {
                                                $primeraFila2.= "<td class='borde centrado'>" . $resultSet['maximoA'] . "</td>";
                                            } else {
                                                $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                                                $nota = true;
                                            }
                                        }
                                        $rowspan = 0;
                                        $idTicketAnteriorComponente = $resurtido->ticketAnteriorResurtidoPorComponenteYAlmacen($resultSet['NoComponenteToner'], $idTicket, $resultSet['IdAlmacen']);
                                        $arrayNoTicketComponente['' . $resultSet['NoComponenteToner']] = $idTicketAnteriorComponente;
                                        $arrayComponenteModelo['' . $resultSet['NoComponenteToner']] = $resultSet['ModeloT'];
                                        $arrayCantidadSolicitadaComponente['' . $resultSet['NoComponenteToner']] = (int) $resultSet['CantidadSolicitada'];
                                    } else {
                                        $filas.= "<tr>";
                                        $filas.= "<td class='borde centrado'>" . $resultSet['ModeloT'] . "</td>";
                                        $filas.= "<td class='borde centrado'>" . $resultSet['precio'] . "</td>";
                                        $filas.= "<td class='borde centrado'>" . $resultSet['CantidadSolicitada'] . "</td>";
                                        if ((int) $resultSet['mail'] == 1) {
                                            if (isset($resultSet['Cantidad']) && $resultSet['Cantidad'] != "") {
                                                $filas.= "<td class='borde centrado'>" . $resultSet['Cantidad'] . "</td>";
                                            } else {
                                                $filas.= "<td class='borde centrado'>" . 0 . "</td>";
                                            }
                                            if (isset($resultSet['existencia'])) {
                                                $filas.= "<td class='borde centrado'>" . $resultSet['existencia'] . "</td>";
                                            } else {
                                                $filas.= "<td class='borde centrado'>N/A</td>";
                                                $nota = true;
                                            }
                                            if (isset($resultSet['minimo'])) {
                                                $filas.= "<td class='borde centrado'>" . $resultSet['minimo'] . "</td>";
                                            } else {
                                                $filas.= "<td class='borde centrado'>N/A</td>";
                                                $nota = true;
                                            }
                                            if (isset($resultSet['maximo'])) {
                                                $filas.= "<td class='borde centrado'>" . $resultSet['maximo'] . "</td>";
                                            } else {
                                                $filas.= "<td class='borde centrado'>N/A</td>";
                                                $nota = true;
                                            }
                                        } else {
                                            if (isset($resultSet['existenciaA'])) {
                                                $filas.= "<td class='borde centrado'>" . $resultSet['existenciaA'] . "</td>";
                                            } else {
                                                $filas.= "<td class='borde centrado'>N/A</td>";
                                                $nota = true;
                                            }
                                            if (isset($resultSet['minimoA'])) {
                                                $filas.= "<td class='borde centrado'>" . $resultSet['minimoA'] . "</td>";
                                            } else {
                                                $filas.= "<td class='borde centrado'>N/A</td>";
                                                $nota = true;
                                            }
                                            if (isset($resultSet['maximoA'])) {
                                                $filas.= "<td class='borde centrado'>" . $resultSet['maximoA'] . "</td>";
                                            } else {
                                                $filas.= "<td class='borde centrado'>N/A</td>";
                                                $nota = true;
                                            }
                                        }
                                        $idTicketAnteriorComponente = $resurtido->ticketAnteriorResurtidoPorComponenteYAlmacen($resultSet['NoComponenteToner'], $idTicket, $resultSet['IdAlmacen']);
                                        $arrayNoTicketComponente['' . $resultSet['NoComponenteToner']] = $idTicketAnteriorComponente;
                                        $arrayComponenteModelo['' . $resultSet['NoComponenteToner']] = $resultSet['ModeloT'];
                                        $arrayCantidadSolicitadaComponente['' . $resultSet['NoComponenteToner']] = (int) $resultSet['CantidadSolicitada'];
                                        $filas.= "</tr>";
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
                                    $tabla.= "<tr>";
                                    $tabla.= "<td class='borde centrado' rowspan='$rowspan'>" . $idTicket . "</td>";
                                    $tabla.= "<td class='borde centrado' rowspan='$rowspan'>" . $fecha . "</td>";

                                    $tabla.= $primeraFila1;
                                    if ($contestada != 1) {
                                        $tabla.= "<td class='borde centrado' rowspan='$rowspan'>Sin autorizar</td>";
                                    }
                                    $tabla.=$primeraFila2;

                                    $tabla.= "</tr>";
                                    $tabla.= $filas;
                                }
                                if ($val == false) {
                                    $tabla.= "<tr>";
                                    $tabla.= "<td class='borde centrado' colspan='13'>No se encontraron datos que coincidieran con su búsqueda</td>";
                                    $tabla.= "</tr>";
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

                                $message.= "<h3>Ticket de resurtido: $idTicket</h3>";
                                $message.= "<h3>Cliente: $cliente</h3>";
                                $message.= "<h3>Localidad: $localidad</h3>";
                                $message.= "<h3>Almacen: $almacen </h3>";
                                $message.= "<h3>Pedido: </h3>";
                                $message.= "<br/>";

                                $message.="<table class='completeSize'>";
                                $message.="<tr>";
                                $message.="<th class='borde centrado'>Ticket</th>";
                                $message.="<th class='borde centrado'>Fecha</th>";
                                $message.="<th class='borde centrado'>Modelo</th>";
                                $message.="<th class='borde centrado'>Precio USD</th>";
                                $message.="<th class='borde centrado'>Cantidad Solicitada</th>";
                                $message.="<th class='borde centrado'>Cantidad Surtida</th>";
                                $message.="<th class='borde centrado'>Existencia</th>";
                                $message.="<th class='borde centrado'>Mínimo</th>";
                                $message.="<th class='borde centrado'>Máximo</th>";
                                $message.="</tr>";
                                $message.= $tabla;
                                $message.="</table>";
                                $message.="<br/>";

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
                                    $message.= "<h5>Ha habido cambios en los mínimos y máximos de un modelo";
                                    $message.= "<table>";
                                    $message.= "<tr>";
                                    $message.= "<th>Modelo</th>";
                                    $message.= "<th>Fecha</th>";
                                    $message.= "<th>Min Anterior</th>";
                                    $message.= "<th>Max Anterior</th>";
                                    $message.= "<th>Min</th>";
                                    $message.= "<th>Max</th>";
                                    $message.= "</tr>";
                                    while ($rsCambios = mysql_fetch_array($resultCambios)) {
                                        $message.= "<tr>";
                                        $message.= "<td>" . $rsCambios['Modelo'] . "</td>";
                                        $message.= "<td>" . $rsCambios['Fecha'] . "</td>";
                                        $message.= "<td>" . $rsCambios['MinimoAnterior'] . "</td>";
                                        $message.= "<td>" . $rsCambios['MaximoAnterior'] . "</td>";
                                        $message.= "<td>" . $rsCambios['MinimoNuevo'] . "</td>";
                                        $message.= "<td>" . $rsCambios['MaximoNuevo'] . "</td>";
                                        $message.= "</tr>";
                                    }
                                    $message.= "</table>";
                                }
                                $message.= "<br/>";
                                $message.= "<h4>Los toner que se cambiaron fueron:</h4>";
                                $message.= "<table class='tablaCompleta'>";
                                $message.= "<tr>";
                                $message.= "<th class='borde centrado'>Ticket</th>";
                                $message.= "<th class='borde centrado'>Fecha</th>";
                                $message.= "<th class='borde centrado'>Equipo</th>";
                                $message.= "<th class='borde centrado'>Serie</th>";
                                $message.= "<th class='borde centrado'>NoParte</th>";
                                $message.= "<th class='borde centrado'>Modelo</th>";
                                $message.= "<th class='borde centrado'>Contador Actual</th>";
                                $message.= "<th class='borde centrado'>Contador Anterior</th>";
                                $message.= "<th class='borde centrado'>Impresiones</th>";
                                $message.= "<th class='borde centrado'>Rendimiento</th>";
                                $message.= "<th class='borde centrado'>Localidad</th>";
                                $message.= "</tr>";

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

                                    $message.= "<tr>";
                                    $message.= "<td class='borde centrado'>" . $rsTickets['NoTicket'] . "</td>";
                                    $message.= "<td class='borde centrado'>" . $rsTickets['Fecha'] . "</td>";
                                    $message.= "<td class='borde centrado'>" . $rsTickets['Equipo'] . "</td>";
                                    $message.= "<td class='borde centrado'>" . $rsTickets['NoSerie'] . "</td>";
                                    $message.= "<td class='borde centrado'>" . $rsTickets['NoParte'] . "</td>";
                                    $message.= "<td class='borde centrado'>" . $rsTickets['Modelo'] . "</td>";
                                    $message.= "<td class='borde centrado'>" . $rsTickets['ContadorBN'] . "</td>";
                                    $message.= "<td class='borde centrado'>" . $rsTickets['FechaAnterior'] . "<br/>" . $rsTickets['ContadorBNAnterior'] . "</td>";
                                    $message.= "<td class='borde centrado'>" . $rsTickets['Impresiones'] . "</td>";
                                    if ($porcentajeRendimiento == 0) {
                                        if (!isset($rsTickets['ContadorBNAnterior']) || $rsTickets['ContadorBNAnterior'] == "") {
                                            $message.= "<td class='borde centrado'>Sin rendimiento por lectura anterior</td>";
                                        } else {
                                            $message.= "<td class='borde centrado'>Sin rendimiento</td>";
                                        }
                                    } else {
                                        if ($porcentajeRendimiento < 0) {
                                            $message.= "<td class='borde centrado'> 0 % de <br/>" . $rsTickets['Rendimiento'] . "</td>";
                                        } else {
                                            $message.= "<td class='borde centrado'> " . number_format($porcentajeRendimiento) . "% de <br/>" . $rsTickets['Rendimiento'] . "</td>";
                                        }
                                    }
                                    $message.= "<td class='borde centrado'>" . $rsTickets['Localidad'] . "</td>";
                                    $message.= "</tr>";
                                    $arrayCantidadSolicitadaComponente['' . $rsTickets['NoParte']] --;
                                }

                                $message.= "</table>";
                                $message.= "<br/>";

                                $primeraVez = true;
                                foreach ($arrayCantidadSolicitadaComponente as $key => $value) {
                                    if ($value != 0) {
                                        if ($primeraVez) {
                                            $message.= "<h5>Los siguientes modelos tienen inconsistencias en la cantidad solicitada y "
                                                    . "los cambios de tóner desde el último resurtido</h5>";
                                            $primeraVez = false;
                                        }
                                        $message.= "Para el modelo: " . $arrayComponenteModelo[$key] . " el ticket anterior de resurtido es: ";
                                        if ($arrayNoTicketComponente[$key] == "") {
                                            $message.="No hay ticket anterior de resurtido<br/>";
                                        } else {
                                            $message.= " <a href='" . $url . "reportes/reporte_toner_ticket.php?idTicket=" . $arrayNoTicketComponente[$key] . "'  target='_blank'>" . $arrayNoTicketComponente[$key] . "</a><br/>";
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
                                    $message.= "<h5>Hubo cambios manuales en este almacen</h5>";
                                    $message.= "<table>";
                                    $message.= "<tr>";
                                    $message.= "<th>Modelo</th>";
                                    $message.= "<th>Fecha</th>";
                                    $message.= "<th>Tipo</th>";
                                    $message.= "<th>CantidadMovimiento</th>";
                                    $message.= "<th>Usuario de Modificación</th>";
                                    $message.= "</tr>";
                                    while ($rsMovimientosComponente = mysql_fetch_array($resultMovimientosComponente)) {
                                        $message.= "<tr>";
                                        $message.= "<td>" . $rsMovimientosComponente['Modelo'] . "</td>";
                                        $message.= "<td>" . $rsMovimientosComponente['Fecha'] . "</td>";
                                        $message.= "<td>" . $rsMovimientosComponente['Tipo'] . "</td>";
                                        $message.= "<td class='centrado'>" . $rsMovimientosComponente['CantidadMovimiento'] . "</td>";
                                        $message.= "<td>" . $rsMovimientosComponente['UsuarioCreacion'] . "</td>";
                                        $message.= "</tr>";
                                    }
                                    $message.= "</table>";
                                }
                                /*                                 * ************************ Cuerpo del correo **************************** */
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
                                                        VALUES($idTicketFinal,0,MD5('$clave'),2,1,'" . $_SESSION['user'] . "',now(),'" . $_SESSION['user'] . "',now(),'nueva_solicitud.php');");
                                $liga = "$url/aceptarPedidoToner.php?clv=$clave&idTicket=$idTicketFinal&idMail=$idMail&idNota=$idNotaUltima&tipo";
                                $message = $message . "<br/>Autorizar solicitud: " . $liga . "=1&uguid=" . $_SESSION['idEmpresa'] . " <br/><br/>";
                                $message = $message . "<br/>Rechazar solicitud: " . $liga . "=3&uguid=" . $_SESSION['idEmpresa'] . " <br/><br/>";
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
                            }
                        }


                        //verificar si el stock
                        $obj->setIdMensajeria($parametros['tranporteMensajeria']);
                        $obj->setNoGuia($parametros['noGuia']);
                        $obj->setIdVehiculo($parametros['tranportepropio']);
                        $obj->setIdConductor($parametros['conductor']);
                        $obj->setClaveCentroCosto($parametros['localidad' . $i]);
                        $obj->setIdSolicitud($parametros['pedido' . $i]);
                        $obj->setOtros($parametros['otro']);
                        if ($obj->newEnvioToner($parametros['tipoMensajeria'])) {
                            
                        }

                        if ($obj->getDatosAlmacen()) {
                            $cantidadExistente = $obj->getCantidadExistente();
                            $cantidadMaxima = $obj->getCantidadMaxima();
                            $totalResurtido = (int) $cantidadMaxima - (int) $cantidadExistente;
                            $obj->setCantidadResurtido($totalResurtido);

                            $obj->getCantidadSolicitadaBYticket($parametros['nota' . $i]);
                            $obj->getCantidadSurtidaByticket($parametros['nota' . $i]);
                        }

                        if ($pos01 !== FALSE && $pos11 !== FALSE) {
                            $obj->setIdAlmacen($parametros['almacen' . $i]);
                            $obj->setIdAlmaceNuevo($miniAlmacen);
                            if ($obj->newMovimientoToner()) {
                                
                            }
                        } else {
                            $obj->setClaveCliente($parametros['cliente' . $i]);
                            if ($obj->newMovimientoToner()) {
                                
                            }
                        }
                    }
                }
            }
        } else {
            echo "Error: no se pudo crear la nota de enviado";
        }
    } else {
        $componente->getRegistroById($parametros['noparte' . $i]);
        echo "El componente " . $componente->getModelo() . " no se envío porque ya ha sido atendido completamente.";
    }
}
?>