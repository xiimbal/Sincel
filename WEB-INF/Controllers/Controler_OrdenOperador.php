<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/NotaTicket.class.php");
include_once("../Classes/Catalogo.class.php");
include_once("../Classes/Ticket.class.php");
include_once("../Classes/OrdenOperador.class.php");
include_once("../Classes/Usuario.class.php");
include_once("../Classes/Puesto.class.php");

$catalogo = new Catalogo();
$ticket = new Ticket();
$ordenOp = new OrdenOperador();
$usuario = new Usuario();
$puesto = new Puesto();

if (isset($_POST['Asignar'])) {
    $prioridad = "";
    $duracion = "";
    $unidad = "";
    $tipo = "";
    $ordenOp->getRegistroById($_POST['IdOrdenO']);
    $IdUsuario = $ordenOp->getIdUsuario();
    $usuario->getRegistroById($IdUsuario);
    $puesto->getRegistroById($usuario->getPuesto());
    $tipo = $puesto->getNombre();
    $ticket->getTicketByID($_POST['IdTicket']);
    $FechaHora = $ticket->getFechaHora();
    $ticket->setUsuarioUltimaModificacion($_SESSION['user']);
    $ticket->setUsuarioCreacion($_SESSION['user']);
    $ticket->setPantalla('PHP OrdenOperador');
    $ticket->eliminarAsignaciones(); //Eliminamos asignaciones anteriores    
    if ($ticket->asociarTicketTecnicoGeneral($IdUsuario, $prioridad, $duracion, $unidad, $FechaHora)) {
        if ($ticket->crearNota($usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " para atender en fecha $FechaHora", $tipo)) {
            $ordenOp->editRegistroA($_POST['IdOrdenO']);
            
            
                    $ordenO = $ordenOp->getOrden();
                    $usuario->getRegistroById($ordenOp->getIdUsuario());
                    if ($ordenOp->getMayorOrden() > 0) {
                        $mayor = $ordenOp->getMayorOrden();
                        if ($ordenO == $mayor) {
                            if ($ordenOp->deleteRegistro()) {
                                echo "El operador <b>" . $usuario->getUsuario() . "</b>-" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " se quito de la lista";
                            }
                        } else {
                            $inc = 1;
                            while ($ordenO < $mayor) {
                                $num = $inc;
                                $idOrdenOp = $ordenOp->getPorOrden($num,2);
                                if ($idOrdenOp == 0) {
                                    echo "Error al quitar";
                                } else {
                                    if ($ordenOp->editRegistro($ordenO, $idOrdenOp)) {
                                        $inc++;
                                        $ordenO++;
                                    } else {
                                        $inc++;
                                    }
                                }
                            }
                            if ($ordenO == $mayor) {
                                if ($ordenOp->deleteRegistro()) {
                                    echo "El operador <b>" . $usuario->getUsuario() . "</b>-" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " se quito de la lista";
                                }else{
                                    echo "Error: No se quito operador de la lista";
                                }
                            }else{
                                echo "Porblema al recorrer operadores";
                            }
                        }
                    } else {
                        echo "Aún no hay registros de fecha actual";
                    }
                
            
            echo "<br/>El Servicio <b>".$_POST['IdTicket']."</b> fue asignado al operador correctamente";
        }
    } else {
        echo "<br/>No se pudo asignar el Servicio <b>".$_POST['IdTicket']."</b>";
    }
} else {
    if (isset($_POST['Agregar'])) {
        $ordenOp->setIdUsuario($_POST['IdUsuario']);
        $ordenOp->setIdBase($_POST['IdBase']);
        $ordenMayor = $ordenOp->getMayorOrden();
        $ordenOp->setOrden($ordenMayor + 1);
        $ordenOp->setActivo(1);
        $ordenOp->setUsuarioModificacion($_SESSION['user']);
        $ordenOp->setUsuarioCreacion($_SESSION['user']);
        $ordenOp->setPantalla('PHP OrdenOperador');
        $usuario->getRegistroById($_POST['IdUsuario']);
        if ($ordenOp->newRegistro()) {
            echo "<br/>Se agrego correctamente al operador <b>" . $usuario->getUsuario() . "</b>-" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . "";
        } else {
            echo "<br/>No se pudo agregar el operador <b>" . $usuario->getUsuario() . "</b>-" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . "";
        }
    } else {
        if (isset($_POST['Subir'])) {
            $ordenOp->getRegistroById($_POST['IdOrdenO']);
            $ordenO = $ordenOp->getOrden();
            $usuario->getRegistroById($ordenOp->getIdUsuario());
            if ($ordenO == 1) {
                echo "El operador <b>" . $usuario->getUsuario() . "</b>-" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " es el primero en la lista";
            } else {
                if ($ordenOp->getPorOrden('1','1') != 0) {
                    $idOrdenOSuperior = $ordenOp->getPorOrden('1','1');
                    if ($ordenOp->editRegistro(($ordenO - 1), $_POST['IdOrdenO'])) {
                        if ($ordenOp->editRegistro($ordenO, $idOrdenOSuperior)) {
                            echo "El operador <b>" . $usuario->getUsuario() . "</b>-" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " ha subido";
                        } else
                            echo "Error";
                    } else
                        echo "Error";
                } else {
                    echo "Ocurrio un error determinando el operador superior";
                }
            }
        } else {
            if (isset($_POST['Bajar'])) {
                $ordenOp->getRegistroById($_POST['IdOrdenO']);
                $ordenO = $ordenOp->getOrden();
                $usuario->getRegistroById($ordenOp->getIdUsuario());
                if ($ordenOp->getMayorOrden() > 0) {
                    $mayor = $ordenOp->getMayorOrden();
                    if ($ordenO == $mayor) {
                        echo "El operador <b>" . $usuario->getUsuario() . "</b>-" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " es el último en la lista";
                    } else {
                        if ($ordenOp->getPorOrden(1,2) != 0) {
                            $idOrdenOInferior = $ordenOp->getPorOrden(1,2);
                            if ($ordenOp->editRegistro(($ordenO + 1), $_POST['IdOrdenO'])) {
                                if ($ordenOp->editRegistro($ordenO, $idOrdenOInferior)) {
                                    echo "El operador <b>" . $usuario->getUsuario() . "</b>-" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " ha bajado";
                                } else
                                    echo "Error";
                            } else
                                echo "Error";
                        } else {
                            echo "Ocurrio un error determinando el operador inferior";
                        }
                    }
                } else {
                    echo "Aún no hay registros de fecha actual";
                }
            } else {
                if (isset($_POST['Eliminar'])) {
                    $ordenOp->getRegistroById($_POST['IdOrdenO']);
                    $ordenO = $ordenOp->getOrden();
                    $usuario->getRegistroById($ordenOp->getIdUsuario());
                    if ($ordenOp->getMayorOrden() > 0) {
                        $mayor = $ordenOp->getMayorOrden();
                        if ($ordenO == $mayor) {
                            if ($ordenOp->deleteRegistro()) {
                                echo "El operador <b>" . $usuario->getUsuario() . "</b>-" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " se quito de la lista";
                            }
                        } else {
                            $inc = 1;
                            while ($ordenO < $mayor) {
                                $num = $inc;
                                $idOrdenOp = $ordenOp->getPorOrden($num,2);
                                if ($idOrdenOp == 0) {
                                    echo "Error al quitar";
                                } else {
                                    if ($ordenOp->editRegistro($ordenO, $idOrdenOp)) {
                                        $inc++;
                                        $ordenO++;
                                    } else {
                                        $inc++;
                                    }
                                }
                            }
                            if ($ordenO == $mayor) {
                                if ($ordenOp->deleteRegistro()) {
                                    echo "El operador <b>" . $usuario->getUsuario() . "</b>-" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . " se quito de la lista";
                                }else{
                                    echo "Error: No se quito operador de la lista";
                                }
                            }else{
                                echo "Porblema al recorrer operadores";
                            }
                        }
                    } else {
                        echo "Aún no hay registros de fecha actual";
                    }
                }
            }
        }
    }
}
?>