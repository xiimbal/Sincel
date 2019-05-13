<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/EntregaRefaccion.class.php");
include_once("../Classes/CambiarEstatusNota.class.php");
include_once("../Classes/AgregarNota.class.php");
include_once("../Classes/Catalogo.class.php");
$obj = new EntregaRefaccion();
$obj1 = new CambiarEstatusNota();
$obj2 = new AgregarNota();
$catalogo = new Catalogo();
if (isset($_POST['refaccion'])) {
    $ticket = $_POST['ticket'];
    $nota = $_POST['nota'];
    $descripcion = "Refaccion entregada"; //$_POST['descripcion'];
    $refaccion = $_POST['refaccion'];
    $activo = 1;
    $mostrar = 1;
    $usuarioSolicitud = $_POST['usuarioSolicitud'];
    $cantidadPeticion = $_POST['cantidadPeticion'];
    $almacen = $_POST['almacen'];
    $cliente = $_POST['claveCliente'];
    $modelo = $_POST['modelo'];
    $noSerie = $_POST['noSerie'];
    $localidad = $_POST['localidad'];
    $contador = 0;
    while ($contador < count($refaccion)) {        
        $surtidoCompleto = false;
        //Vamos a verificar que ya estén surtidos todos los tóner para no volverlos a mandar, esto pasa por los dobles clics
        //Obtenemos primero los surtidos del ticket
        if(!empty($ticket[$contador]) && !empty($refaccion[$contador]))
        {
            $consultaSolicitados = ("SELECT SUM(nr.Cantidad) AS total,nt.IdTicket 
                FROM c_notaticket nt,k_nota_refaccion nr 
                WHERE nt.IdTicket= ".$ticket[$contador]."
                AND (nt.IdEstatusAtencion=21) AND nr.IdNotaTicket=nt.IdNotaTicket AND nr.NoParteComponente = '".$refaccion[$contador]."';");
            $querySolicitados = $catalogo->obtenerLista($consultaSolicitados);
            while ($rsSolicitados = mysql_fetch_array($querySolicitados)) {
                $cantidadTotalSolicitada = $rsSolicitados['total'];
            }

            $consultaAtendido = ("SELECT SUM(nr.Cantidad) AS total 
                FROM c_notaticket nt,k_nota_refaccion nr 
                WHERE nt.IdTicket= ".$ticket[$contador]."
                AND (nt.IdEstatusAtencion=17) AND nr.IdNotaTicket=nt.IdNotaTicket AND nr.NoParteComponente = '".$refaccion[$contador]."';");
            $queryAtendido = $catalogo->obtenerLista($consultaAtendido);
            while ($rsAtendido = mysql_fetch_array($queryAtendido)) {
                $cantidadTotalSurtida = $rsAtendido['total'];
            }

            if (intval($cantidadTotalSurtida) >= intval($cantidadTotalSolicitada)) {
                $surtidoCompleto = true;
            }
        }
        
        if(!$surtidoCompleto){
        // echo $refaccion[$contador] . ">>" . $ticket[$contador] . ">>" . $cantidadPeticion[$contador] . "<br/>";
            if ($obj->ObtenerCAntidadSurtida($nota[$contador], $refaccion[$contador])) {
                $cantidad = $obj->getCantidadSurtida();
                $obj->setUsuarioCreacion($_SESSION['user']);
                $obj->setUsuarioModificacion($_SESSION['user']);
                $obj->setPantalla("Entrega de refacciones");
                $total = (int) $cantidad + (int) $cantidadPeticion[$contador];
                if ($obj->EditCantidadSurtidas($nota[$contador], $refaccion[$contador], $total)) {
                    //agregar nota y la refaccion entregada 
                    $obj1->setIdTicket($ticket[$contador]);
                    $obj1->setDiagnosticoSolucion($descripcion);
                    $obj1->setIdestatusAtencion(17);
                    $obj1->setActivo(1);
                    $obj1->setUsuarioSolicitud($usuarioSolicitud[$contador]);
                    $obj1->setMostrarCliente(0);
                    $obj1->setUsuarioCreacion($_SESSION['user']);
                    $obj1->setUsuarioModificacion($_SESSION['user']);
                    $obj1->setPantalla("Entrega de refacciones");
                    if ($obj1->cambiarNota()) {
                        $idNota = $obj1->getIdNotaTicket();
                        //$obj2->setIdNotaTicket($idNota);
                        $obj->setRefaccion($refaccion[$contador]);
                        $obj->setCantidad($cantidadPeticion[$contador]);
                        $obj->setIdAlmacen($almacen[$contador]);
                        $obj->setUsuarioCreacion($_SESSION['user']);
                        $obj->setUsuarioModificacion($_SESSION['user']);
                        $obj->setPantalla("Entrega de refacciones");
                        if ($obj->EntregarRefaccion($idNota)) {//registra refaccion entregada
                            //editar componentes en almacén
                            $obj->setRefaccion($refaccion[$contador]);
                            $obj->setIdAlmacen($almacen[$contador]);
                            if ($obj->getCantidadALmacen()) {//obtiene la cantidad existentes en almacen
                                $cantidadAlmacen = $obj->getCantidad();
                                $cantidadApartadas = $obj->getCantidadApartadas();
                                if ((int) $cantidadAlmacen > 0) {
                                    $totalExistentes = (int) $cantidadAlmacen - (int) $cantidadPeticion[$contador];
                                    $obj->setCantidad($totalExistentes);
                                } else
                                    $obj->setCantidad(0);

                                $totalApartadas = (int) $cantidadApartadas - (int) $cantidadPeticion[$contador];

                                $obj->setCantidadApartadas($totalApartadas);
                                $obj->setNoParte($refaccion[$contador]);
                                $obj->setIdAlmacen($almacen[$contador]);
                                if ($obj->editAlmacenComponentes()) {
                                    $obj->setNota($idNota);
                                    $obj->setIdTicket($ticket[$contador]);
                                    $obj->setCantidadEntregar($cantidadPeticion[$contador]);
                                    $obj->setUsuarioCreacion($_SESSION['user']);
                                    $obj->setUsuarioModificacion($_SESSION['user']);
                                    $obj->setPantalla("Entrega de refacciones");
                                    $obj->setIdAlmacenAnterior($almacen[$contador]);
                                    $obj->setClaveClienteNuevo($cliente[$contador]);
                                    $obj->setEntradaSalida(1);
                                    $obj->setNoSerie($noSerie[$contador]);
                                    $obj->setClaveCentroCosto($localidad[$contador]);
                                    if ($obj->newMovimiento()) {
                                        echo "La refacción <b>" . $modelo[$contador] . "</b> se entregó correctamente <br/>";
                                    } else
                                        echo "El movimiento de componente no se pudo generar <br/>";
                                } else {
                                    echo "El almacen no cuenta con la refaccion solicitada <br/>";
                                }
                            } else {
                                echo "Sin refacion";
                            }
                        } else
                            echo "La refacción <b>" . $modelo[$contador] . "</b> no se entregó correctamente <br/>";
                    }else {
                        echo "La nota no se registro";
                    }
                } else {
                    echo "Sin editar";
                }
            }
        }else{
            echo "El componente ".$modelo[$contador]." no se envío porque ya ha sido atendido completamente.";
        }
        $contador++;
    }
    // echo $refaccion[0] . ">" . $refaccion[2];
}
?>
