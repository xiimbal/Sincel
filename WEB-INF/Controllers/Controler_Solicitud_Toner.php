<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/Catalogo.class.php");
include_once("../Classes/Usuario.class.php");
include_once("../Classes/SolicitudToner.class.php");
include_once("../Classes/Ticket.class.php");
include_once("../Classes/NotaTicket.class.php");
$catalogo = new Catalogo();
$obj = new SolicitudToner();
$ticket1 = new Ticket();
if (isset($_POST['accion']) && $_POST['accion'] == "solicitarAlmacen") {//solicitar toner al almacen
    $idNota = $_POST['idNota'];
    $componente = $_POST['componente'];
    $cantidad = $_POST['cantidad'];
    $series = $_POST['series'];
    $cantidadAlmacen = $_POST['cantidadAlmacen'];
    $cantidadSolicitadas = $_POST['cantidadSolicitadas'];
    $estatus = $_POST['estatus'];
    $almacen = $_POST['almacen'];
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla("Solicitud de toner al almacén");
    $obj->setMostrarCliente(0);
    $contadorModi = 0;
    $array_id_nota = array();
    for ($x = 1; $x < count($idNota); $x++) {
        //agregar nota
        $obj->setNotaAnterior($idNota[$x]);
        $obj->setIdEstadoNota($estatus[$x]);
        $obj->setNoParteComponente($componente[$x]);
        $obj->setCantidadSolicitada($cantidadSolicitadas[$x]);
        $obj->setIdAlmacen($almacen[$x]);
        if($obj->estaTodoListo($series[$x])){
            echo "<br/>Atención: ya se han procesado todos los componentes con NoParte: ".$obj->getNoParteComponente()." de la serie: ".$series[$x].", no se puede volver a procesar<br/>";
            continue;
        }
        //tipo de nota backorder o listo
        if ($obj->newNotaSolicitudToner()) {//nueva nota
            if ($estatus[$x] == "21") {//agregar id notas para enviar los toner listos para entregar
                array_push($array_id_nota, $obj->getIdNota());
            }
            if ($obj->InsertNotaToner()) {
                if ($obj->EditCantidadPedido($cantidadSolicitadas[$x], $series[$x])) {
                    if ($estatus[$x] == "20") {//agregar nota
                        $contadorModi++;
                    } else if ($estatus[$x] == "21") {
                        if ($obj->EditCantidadAlmacen()) {//Modificar componentes en almacen                            
                            $contadorModi++;
                        }
                    } else if ($estatus[$x] == "68") {
                        $contadorModi++;
                        $obj->getCantidadSolicitadaBYticket($idNota[$x]);
                        $obj->getCantidadSurtidaByticket($idNota[$x]);
                        $totalSolicitadaPedido = $obj->getCantidadTotalSolicitada();
                        $totalSurtidasPedido = $obj->getCantidadTotalSurtida();
                        $nota = new NotaTicket();
                        $nota->getRegistroById($idNota[$x]);
                        $obj->setIdTicket($nota->getIdTicket());
                        $obj->marcarSurtidoResurtidoToner(); //Se marca en la tabla k_resurtidotoner como surtido.
                    }
                }else{
                    echo "Error: No s epudo actualizar la cantidad de lo validado";
                }
            }else{
                echo "Error: notaToner";
            }
        } else {
            echo "La nota no se registró correctamente";
        }
    }
    
    $str_id = implode(",", $array_id_nota);
    if ($contadorModi > 0) {
        echo "La solicitudes de tóner se atendieron correctamente. /*/*/ " . $str_id;
    } else {
        echo "La solicitudes de tóner no se atendieron. /*/*/ " . $str_id;
    }
    
} else if (isset($_POST['accion']) && $_POST['accion'] == "cambiarToner") {
    $partes = $_POST['toners'];
    $cantidades = $_POST['cantidades'];
    $series = $_POST['series'];
    $texto_nota = "";
    $hay_cambios = false;
    $usuario = $_SESSION['user'];
    $pantalla = "Controler_Sustitucion_Toner";
    foreach ($partes as $key => $value) {
        $partes = explode("/**/", $value);
        $parte_original = $partes[1];
        $parte_sustituidora = $partes[0];
        $cantidad = $cantidades[$key];
        $serie_original = $series[$key];
        if ($parte_original == "" || $parte_sustituidora == "") {
            continue;
        }

        if ($serie_original != NULL && $serie_original != "") {
            $consulta = "SELECT nr.IdNotaTicket, nr.Cantidad,nr.NoSerieEquipo, nt.IdEstatusAtencion
                FROM c_notaticket AS nt
                LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
                WHERE nt.IdTicket = " . $_POST['ticket'] . " AND nt.IdEstatusAtencion IN(67,65) AND nr.NoParteComponente = '$parte_original' 
                AND nr.NoSerieEquipo = '$serie_original'
                ORDER BY NoSerieEquipo DESC;";
        } else {
            $consulta = "SELECT nr.IdNotaTicket, nr.Cantidad,nr.NoSerieEquipo, nt.IdEstatusAtencion
                FROM c_notaticket AS nt
                LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
                WHERE nt.IdTicket = " . $_POST['ticket'] . " AND nt.IdEstatusAtencion IN(67,65) AND nr.NoParteComponente = '$parte_original'                 
                ORDER BY NoSerieEquipo DESC;";
        }

        $result = $catalogo->obtenerLista($consulta);

        while ($rs = mysql_fetch_array($result)) {
            if (isset($rs['NoSerieEquipo']) && $rs['NoSerieEquipo'] != "") {
                $serie = $rs['NoSerieEquipo'];
            }

            if ($rs['IdEstatusAtencion'] == "65") {
                /* Actualizamos NoParte */
                if ($serie_original != NULL && $serie_original != "") {
                    $consulta = "UPDATE k_nota_refaccion SET NoParteComponente = '$parte_sustituidora', NoSerieEquipo = '$serie', Cantidad = Cantidad - $cantidad,
                        CantidadSurtida = CantidadSurtida + $cantidad,UsuarioUltimaModificacion = '$usuario', FechaUltimaModificacion = NOW(), Pantalla = '$pantalla' 
                         WHERE NoParteComponente = '$parte_original' AND IdNotaTicket = " . $rs['IdNotaTicket'] . " AND NoSerieEquipo = '$serie';";
                } else {
                    $consulta = "UPDATE k_nota_refaccion SET NoParteComponente = '$parte_sustituidora', Cantidad = Cantidad - $cantidad,
                        CantidadSurtida = CantidadSurtida + $cantidad,UsuarioUltimaModificacion = '$usuario', FechaUltimaModificacion = NOW(), Pantalla = '$pantalla' 
                         WHERE NoParteComponente = '$parte_original' AND IdNotaTicket = " . $rs['IdNotaTicket'] . ";";
                }
            } else {
                /* Actualizamos NoParte */
                if ($serie_original != NULL && $serie_original != "") {
                    $consulta = "UPDATE k_nota_refaccion SET NoParteComponente = '$parte_sustituidora', NoSerieEquipo = '$serie',
                        UsuarioUltimaModificacion = '$usuario', FechaUltimaModificacion = NOW(), Pantalla = '$pantalla (2)' 
                        WHERE NoParteComponente = '$parte_original' AND IdNotaTicket = " . $rs['IdNotaTicket'] . " AND NoSerieEquipo = '$serie';";
                } else {
                    $consulta = "UPDATE k_nota_refaccion SET NoParteComponente = '$parte_sustituidora',
                        UsuarioUltimaModificacion = '$usuario', FechaUltimaModificacion = NOW(), Pantalla = '$pantalla (2)' 
                        WHERE NoParteComponente = '$parte_original' AND IdNotaTicket = " . $rs['IdNotaTicket'] . ";";
                }
            }
            
            //echo "<br/>" . $consulta;
            $query = $catalogo->obtenerLista($consulta);

            if ($serie_original != NULL && $serie_original != "") {
                $consulta = "UPDATE `k_detalle_notarefaccion` SET Componente = '$parte_sustituidora', UsuarioUltimaModificacion = '$usuario', 
                    FechaUltimaModificacion = NOW(), Pantalla = '$pantalla' WHERE IdNota = " . $rs['IdNotaTicket'] . " AND Componente = '$parte_original' 
                    AND NoSerieEquipo = '$serie';";
            } else {
                $consulta = "UPDATE `k_detalle_notarefaccion` SET Componente = '$parte_sustituidora', UsuarioUltimaModificacion = '$usuario', 
                    FechaUltimaModificacion = NOW(), Pantalla = '$pantalla' WHERE IdNota = " . $rs['IdNotaTicket'] . " AND Componente = '$parte_original';";
            }

            $catalogo->obtenerLista($consulta);
            $consulta = "UPDATE k_resurtidotoner SET NoComponenteToner = '$parte_sustituidora',
                        UsuarioUltimaModificacion = '$usuario', FechaUltimaModificacion = NOW(), Pantalla = '$pantalla (2)'  "
                    . "WHERE IdTicket = " . $_POST['ticket'] . " AND NoComponenteToner = '$parte_original';";
            $catalogo->obtenerLista($consulta);
            
            if ($query >= 1) {
                $hay_cambios = true;
            } else {
                echo "<br/>Error: no se pudo modificar el toner $parte_original por el sustituto: $parte_sustituidora";
            }
        }
        $texto_nota .= "<br/>Cambio de la pieza $parte_original por $parte_sustituidora para la serie $serie";
    }
    if ($hay_cambios) {
        $nota = new NotaTicket();
        $nota->setIdTicket($_POST['ticket']);
        $nota->setDiagnostico($texto_nota);
        $nota->setIdEstatus(8);
        $nota->setUsuarioSolicitud($usuario);
        $nota->setMostrarCliente(0);
        $nota->setActivo(1);
        $nota->setUsuarioCreacion($usuario);
        $nota->setUsuarioModificacion($usuario);
        $nota->setPantalla($pantalla);
        if (!$nota->newRegistro()) {
            
        }
    }
} else {
    if (isset($_POST['accion']) && $_POST['accion'] == "enviarToner") {//enviar toner
        $idNota = $_POST['nota'];
        $componente = $_POST['refaccion'];
        $cantidad = $_POST['cantidad'];
        $cantidadSolicitadas = $_POST['cantidadPeticion'];
        $almacen = $_POST['almacen'];
        $contador = 0;
        while ($contador < count($idNota)) {
            $obj->setNotaAnterior($idNota[$contador]);
            $obj->setIdEstadoNota(66);
            $obj->setNoParteComponente($componente[$contador]);
            $obj->setCantidadSolicitada($cantidadSolicitadas[$contador]);
            $obj->setIdAlmacen($almacen[$contador]);
            $obj->setUsuarioCreacion($_SESSION['user']);
            $obj->setUsuarioModificacion($_SESSION['user']);
            $obj->setPantalla("Envio de toner");
            if ($obj->newNotaSolicitudToner()) {
                $solicitud = $cantidadSolicitadas;
                $obj->setCantidadSolicitada($solicitud);
                if ($obj->InsertNotaToner()) {
                    if ($obj->EditCantidadPedido($solicitud, NULL)) {
                        if ($obj->editAlmacenComponenteApartado($solicitud)) {//restar las cantidades apartadas
                            echo "La nota se registró correctamente";
//verificar si el stock
                            if ($obj->getDatosAlmacen()) {
                                $cantidadExistente = $obj->getCantidadExistente();
                                $cantidadMinima = $obj->getCantidadMinima();
                                $cantidadMaxima = $obj->getCantidadMaxima();
                                $totalResurtido = (int) $cantidadMaxima - (int) $cantidadExistente;
                                $obj->setCantidadResurtido($totalResurtido);
                                if ((int) $cantidadExistente < (int) $cantidadMinima) {
                                    if ($obj->newResurtidoToner()) {
                                        echo "Se generó un reporte de resurtido de toner";
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                echo "La nota no se registró correctamente";
            }
            $contador++;
        }
    } else {
        if (isset($_POST['accion']) && $_POST['accion'] == "TonerEntregado") {//enviar toner
            $obj->setNotaAnterior($_POST['idNota']);
            $obj->setIdEstadoNota(17);
            $obj->setUsuarioCreacion($_SESSION['user']);
            $obj->setUsuarioModificacion($_SESSION['user']);
            $obj->setPantalla("Entregar de toner ST");
            if ($obj->newNotaSolicitudToner()) {//Nota de toner entregado
                if ($obj->copyTonerNota()) {
//cerrar ticket
                    echo "Toner entregado";
                }
            }
        } else {
            echo "Otra opcion";
        }
    }
}
