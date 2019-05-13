<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/CambiarEstatusNota.class.php");
include_once("../Classes/AgregarNota.class.php");
include_once("../Classes/NotaTicket.class.php");
include_once("../Classes/Catalogo.class.php");
$obj = new CambiarEstatusNota();
$obj1 = new AgregarNota();
if (!isset($_POST['accion'])) {
    if (isset($_POST['almacen']) && isset($_POST['refaccion'])) {
        if ($obj->obtenerExistenciaAlmacen($_POST['almacen'], $_POST['refaccion']))
            echo $obj->getCantidadAlmacen();
        else
            echo "0";
    }
    else {
        
    }
} else {
    if (isset($_POST['accion']) && $_POST['accion'] == "nuevoStatus") {
        //usuarioSolicitud
        $obj->setIdTicket($_POST['ticket']);
        $obj->setDiagnosticoSolucion("Listo para entregar");
        $obj->setIdestatusAtencion($_POST['estatus']);
        $obj->setActivo(1);
        $obj->setMostrarCliente(0);
        $obj->setUsuarioSolicitud($_POST['usuarioSolicitud']);
        $obj->setUsuarioCreacion($_SESSION['user']);
        $obj->setUsuarioModificacion($_SESSION['user']);
        $obj->setPantalla("Agregar nota de cambio de estatus");
        $cantidadRestante = (int) $_POST['cantidad'] - (int) $_POST['solicitadas'];
        $obj1->setCantidad($cantidadRestante);
        $obj1->setRefaccion($_POST['refaccion']);
        //echo $cantidadRestante;
        // "nota":nota,"refaccion":refaccion,"cantidad":cantidad,solicitadas
        if ($obj1->cambiarCantidadRefaccion($_POST['nota'])) {
            if ($obj->cambiarNota()) {
                $obj1->setCantidad($_POST['solicitadas']);
                $obj1->setEstatusRefaccion($_POST['estatus']); //p
                $obj1->setUsuarioSolicitud($_SESSION['user']); //p
                $obj1->setUsuarioCreacion($_SESSION['user']);
                $obj1->setUsuarioModificacion($_SESSION['user']);
                $obj1->setPantalla("cambio de estatus de refacción");
                $nuevaNota = $obj->getIdNotaTicket();
                if ($obj1->newRefaccion($nuevaNota)) {
                    if (isset($_POST['estatus']) && $_POST['estatus'] == "21") {
                        $obj->setDiagnosticoSolucion("Listo para entregar");
                        $obj->EditAlmacenRefaccion($nuevaNota, $_POST['refaccion'], $_POST['almacen']);
                        $obj->obtenerExistenciaAlmacen($_POST['almacen'], $_POST['refaccion']);
                        $totalApartadas = $_POST['solicitadas'] + (int) $obj->getCantidadApartadas();
                        $totalExistencia = (int) $obj->getCantidadAlmacen() - (int) $_POST['solicitadas'];
                        $obj->setCantidadAlmacen($totalExistencia);
                        $obj->setCantidadExistentesAlmacen($totalApartadas);
                        if ($obj->EditApartados($_POST['almacen'], $_POST['refaccion']))
                            echo "La nota se atendio correctamente";
                        else
                            echo "La nota no se registro correctamente";
                    }else {
                        echo "La nota se atendio correctamente";
                    }
                } else {
                    echo "La nota no se atendio correctamente";
                }
            } else {
                echo "No se agrego la nota";
            }
        } else {
            echo "no se realizaron cambios";
        }
    } else if (isset($_POST['accion']) && $_POST['accion'] == "validar") {
        $obj->setDiagnosticoSolucion("Refaccion validada");
        $obj->setUsuarioCreacion($_SESSION['user']);
        $obj->setUsuarioModificacion($_SESSION['user']);
        $obj->setPantalla("Validar refacciones solicitadas");
        $obj->setMostrarCliente(0);
        $nota_obj = new NotaTicket();
        $idNota = $_POST['nota'];
        $nota_obj->getRegistroById($idNota);
        $result = $nota_obj->getNotasByTicket($nota_obj->getIdTicket());
        while ($rs = mysql_fetch_array($result)) {
            $idNota = $rs['IdNotaTicket'];
            if ($obj->obtenerNoParteEquipo($idNota)) {
                $NoParteEquipo = $obj->getNoParte();
                $obj->obtenerComponentesNotaRefaccion($idNota);
                $arrayRefacciones = $obj->getArrayNoComponente();
                $contadorRef = 0;
                while ($contadorRef < count($arrayRefacciones)) {
                    //comprobar existencia en componentes compatibles
                    if ($obj1->ComprobarExistenciaCompatiblesEquipo($NoParteEquipo, $arrayRefacciones[$contadorRef])) {
                        $obj1->setNoParte($NoParteEquipo);
                        $obj1->setRefaccion($arrayRefacciones[$contadorRef]);
                        $obj1->setUsuarioCreacion($_SESSION['user']);
                        $obj1->setUsuarioModificacion($_SESSION['user']);
                        $obj1->setPantalla("Validar refacción");
                        if ($obj1->newEquipoComponenteCompatible()) {
                            
                        }
                    }
                    $contadorRef++;
                }
            } else {
                if ($obj->ObtenerNoParteVarios($idNota)) {
                    $NoParteEquipo = $obj->getNoParte();
                    $obj->obtenerComponentesNotaRefaccion($idNota);
                    $arrayRefacciones = $obj->getArrayNoComponente();
                    $contadorRef = 0;
                    while ($contadorRef < count($arrayRefacciones)) {
                        //comprobar existencia en componentes compatibles
                        if ($obj1->ComprobarExistenciaCompatiblesEquipo($NoParteEquipo, $arrayRefacciones[$contadorRef])) {
                            $obj1->setNoParte($NoParteEquipo);
                            $obj1->setRefaccion($arrayRefacciones[$contadorRef]);
                            $obj1->setUsuarioCreacion($_SESSION['user']);
                            $obj1->setUsuarioModificacion($_SESSION['user']);
                            $obj1->setPantalla("Validar refacción");
                            if ($obj1->newEquipoComponenteCompatible()) {
                                //echo "compatibke agregado varios<br/>";
                            }
                        }
                        $contadorRef++;
                    }
                }
            }
        }


        //obtener las refacciones de la nota que tenga estatus de solicitud
        $obj->setMostrarCliente(0);
        if ($obj->copiarNotaSolicitud($nota_obj->getIdNota())) {
            $notaNueva = $obj->getIdNotaTicket();
            if ($obj->copiarRefaccionesSolciitadasPorTicket($nota_obj->getIdTicket(), $notaNueva)) {
                echo "Las refacciones se validaron correctamente";
            }
            else
                echo "Las refacciones no se validaron correctamente";
        } else {
            echo "La nota no se valido correctamente";
        }
    } else if (isset($_POST['accion']) && $_POST['accion'] == "cambiarRefaccion") {
        $refaccion_original = $_POST['original'];
        $refaccion_nueva = $_POST['nueva'];
        $ticket = $_POST['idTicket'];
        $usuario = $_SESSION['user'];
        $pantalla = "Controler_Sustitucion_Refaccion";

        if ($refaccion_nueva != "" && $refaccion_nueva != $refaccion_original) {
            $catalogo = new Catalogo();
            $hay_cambios = false;
            $texto_nota = "";
            /* Cambiar la solicitud de refaccion, validacion de refaccion y backorders */
            $consulta = "SELECT nr.IdNotaTicket, nr.NoParteComponente, nr.Cantidad, nr.CantidadNota, nr.CantidadSurtida 
                FROM c_ticket AS t
                LEFT JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket
                LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
                WHERE t.IdTicket = $ticket AND nt.IdEstatusAtencion IN (9,24,20) AND NoParteComponente = '$refaccion_original'
                GROUP BY nt.IdEstatusAtencion, nt.IdNotaTicket
                ORDER BY nt.IdNotaTicket;";
            $result = $catalogo->obtenerLista($consulta);
            while ($rs = mysql_fetch_array($result)) {
                //Verificamos si la refaccion nueva ya está dada de alta en la nota
                $consulta = "SELECT nr.IdNotaTicket, nr.NoParteComponente
                    FROM k_nota_refaccion AS nr
                    WHERE nr.IdNotaTicket = " . $rs['IdNotaTicket'] . " AND nr.NoParteComponente = '$refaccion_nueva';";
                $resultExistencia = $catalogo->obtenerLista($consulta);

                if (mysql_num_rows($resultExistencia) <= 0) {//Si este número de parte no existe en la nota
                    $consulta = "UPDATE k_nota_refaccion SET NoParteComponente = '$refaccion_nueva',UsuarioUltimaModificacion = '$usuario', FechaUltimaModificacion = NOW(), 
                        Pantalla = '$pantalla' WHERE IdNotaTicket = " . $rs['IdNotaTicket'] . " AND NoParteComponente = '$refaccion_original';";
                    $query = $catalogo->obtenerLista($consulta);
                } else {//Si este componente ya está dado de alta en la nota       
                    $cantidad = 0;
                    if (isset($rs['Cantidad']) && is_numeric($rs['Cantidad'])) {
                        $cantidad = $rs['Cantidad'];
                    }
                    $cantidadNota = 0;
                    if (isset($rs['CantidadNota']) && is_numeric($rs['CantidadNota'])) {
                        $cantidadNota = $rs['CantidadNota'];
                    }
                    $cantidadSurtida = 0;
                    if (isset($rs['CantidadSurtida']) && is_numeric($rs['CantidadSurtida'])) {
                        $cantidadSurtida = $rs['CantidadSurtida'];
                    }
                    $consulta = "UPDATE k_nota_refaccion SET Cantidad = Cantidad + $cantidad,
                        CantidadNota = CantidadNota + $cantidadNota, CantidadSurtida = CantidadSurtida + $cantidadSurtida,
                        UsuarioUltimaModificacion = '$usuario', FechaUltimaModificacion = NOW(), 
                        Pantalla = '$pantalla' WHERE IdNotaTicket = " . $rs['IdNotaTicket'] . " AND NoParteComponente = '$refaccion_nueva';"; //Actualizamos las cantidades de la refaccion nueva ya existente                    
                    $query = $catalogo->obtenerLista($consulta);
                    //Eliminamos la nota de la refaccion original
                    $consulta = "DELETE FROM k_nota_refaccion WHERE IdNotaTicket = " . $rs['IdNotaTicket'] . " AND NoParteComponente = '$refaccion_original';";
                    $catalogo->obtenerLista($consulta);
                }

                if ($query >= 1) {
                    $hay_cambios = true;
                }
            }

            if ($hay_cambios) {
                $texto_nota .= "<br/>Cambio de la pieza $refaccion_original por $refaccion_nueva";
                $nota = new NotaTicket();
                $nota->setIdTicket($ticket);
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
        }
    } else if (isset($_POST['accion']) && $_POST['accion'] == "cambiar_cero") {
        $refaccion = $_POST['refaccion'];        
        $ticket = $_POST['ticket'];
        $usuario = $_SESSION['user'];
        $pantalla = "Controler_PonerCero_Refaccion";

        $catalogo = new Catalogo();
        $hay_cambios = false;
        echo "Error: ";
        /* Cambiar la solicitud de refaccion, validacion de refaccion y verificando uso */
        $consulta = "SELECT nr.IdNotaTicket, nr.NoParteComponente, nr.Cantidad, nr.CantidadNota, nr.CantidadSurtida 
                FROM c_ticket AS t
                LEFT JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket
                LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
                WHERE t.IdTicket = $ticket AND nt.IdEstatusAtencion IN (9,24,19) AND NoParteComponente = '$refaccion'
                GROUP BY nt.IdEstatusAtencion, nt.IdNotaTicket
                ORDER BY nt.IdNotaTicket;";
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            $cantidad = "NULL";
            if (isset($rs['Cantidad']) && is_numeric($rs['Cantidad'])) {
                $cantidad = 0;
            }
            $cantidadNota = "NULL";
            if (isset($rs['CantidadNota']) && is_numeric($rs['CantidadNota'])) {
                $cantidadNota = 0;
            }
            $cantidadSurtida = "NULL";
            if (isset($rs['CantidadSurtida']) && is_numeric($rs['CantidadSurtida'])) {
                $cantidadSurtida = 0;
            }
            $consulta = "UPDATE k_nota_refaccion SET Cantidad = $cantidad, CantidadNota = $cantidadNota,CantidadSurtida = $cantidadSurtida, 
                UsuarioUltimaModificacion = '$usuario', FechaUltimaModificacion = NOW(), 
                Pantalla = '$pantalla' WHERE IdNotaTicket = " . $rs['IdNotaTicket'] . " AND NoParteComponente = '$refaccion';";            
            $query = $catalogo->obtenerLista($consulta);
            
            if ($query >= 1) {
                $hay_cambios = true;
            }
        }
        
        if($hay_cambios){
            echo "<br/>La nota se marco con cantidad cero correctamente.";
        }
    }
}
?>
