<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../../Classes/Orden_Compra.class.php");
include_once("../../Classes/Detalle_Orden_Compra.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Apartar_Imports.class.php");
$import = new Apartar_Imports();
$orden = new Orden_Compra();
$detOC = new Detalle_Orden_Compra();
$catalogo = new Catalogo();
if (isset($_GET['id'])) {
    $orden->setIdOrdenCompra($_GET['id']);
    $orden->setEstatus(59);
    if ($orden->editRegistro()) {
        echo "El registro se canceló correctamente";
    } else {
        echo "Error: El registro no se canceló correctamente";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $tamanoComponente = $_POST['tbComponente'];
    $tamanoEquipo = $_POST['tbEquipo'];
    $orden->setFechaOC($parametros['txtfechaOrden']);
    $orden->setFacturaEmisor($parametros['slProveedor']);
    $orden->setFacturaRecptor($parametros['slRazonSocial']);
    $orden->setCondicionPago($parametros['slFormaPago']);
    $orden->setEmbarca($parametros['txtdireccionEmbarca']);
    $orden->setNoCliente($parametros['txtNoCliente']);
    $orden->setNoPedidoProv($parametros['txtPedidoProv']);
    $orden->setNotas($parametros['txtNotas']);
    $orden->setTransportista($parametros['slMensajeria']);
    $orden->setPeso($parametros['txtPeso']);
    $orden->setMetros($parametros['txtMetros']);
    $orden->setOrigen($parametros['txtOrigen']);
    $orden->setMetodoEntrega($parametros['txtMetodo']);
    $orden->setObservacion($parametros['txtObservaciones']);
    $orden->setTipoCambio($parametros['txtTipoCambio']);
    $orden->setActivo(1);
    $orden->setUsuarioCreacion($_SESSION['user']);
    $orden->setUsuarioModificacion($_SESSION['user']);
    $orden->setPantalla("Alta Orden de compra");
    $orden->setAlmacen($parametros['slAlmacen']);
    if(isset($parametros['descripcionTicket'])){
        $orden->setDescripcion_Ticket($parametros['descripcionTicket']);
        $orden->setFactura_Ticket(1);
    }
    if(isset($parametros['subtotalTicket'])){
        $orden->setSubtotal_Ticket($parametros['subtotalTicket']);
        $orden->setFactura_Ticket(1);
    }
    if(isset($parametros['totalTicket'])){
        $orden->setFactura_Ticket(1);
        $orden->setTotal_Ticket($parametros['totalTicket']);
    }
    $detOC->setUsuarioCreacion($_SESSION['user']);
    $detOC->setUsuarioModificacion($_SESSION['user']);
    $detOC->setPantalla("Alta Orden de compra");
    $orden->setNo_pedido($parametros['txt_pedido']);
    if (isset($parametros['ck_dolar']) && $parametros['ck_dolar'] == "on") {
        $detOC->setDolar(1);
    } else {
        $detOC->setDolar(0);
    }
    if (isset($parametros['slEstatus'])) {
        $orden->setEstatus($parametros['slEstatus']);
    } else {
        $orden->setEstatus(71);
    }

    if (isset($parametros['idOrden_compra']) && $parametros['idOrden_compra'] == "" || isset($parametros['copiado']) && $parametros['copiado'] == "1") {
        if ($orden->newRegistro()) {
            $idOrden = $orden->getIdOrdenCompra();
            $detOC->setIdOrdenCompra($idOrden);
            if ((int) $tamanoComponente > 1) {
                $contadorC = 0;
                $filaC = 0;
                while ($filaC < $tamanoComponente - 1) {
                    if (isset($parametros['slTipoComponente' . $contadorC])) {
                        $varcomp = explode(" // ", $parametros['txtComponentesOC' . $contadorC]);
                        $detOC->setNoParteComponente($varcomp[1]);
                        $detOC->setCantidad($parametros['txtCantidadComponente' . $contadorC]);
                        $total = (int) $parametros['txtCantidadComponente' . $contadorC] * (float) $parametros['txtPrecioUnitarioC' . $contadorC];
                        $detOC->setPrecioUnitario($parametros['txtPrecioUnitarioC' . $contadorC]);
                        $detOC->setCostoTotal($total); //precio pesos
                        if ($parametros['txtidApartado' . $contadorC] != "") {
                            $idNotas = $parametros['txtidApartado' . $contadorC];
                            $verificar = $import->verificarInsert($idNotas);
                        } else {
                            $verificar = true;
                        }
                        if ($verificar) {
                            if (!$detOC->verificarExistencia()) {//editar cantidad 
                                if ($detOC->newRegistroCompnente()) {
                                    
                                }
                            }
                            if (isset($parametros['txtidApartado' . $contadorC]) && $parametros['txtidApartado' . $contadorC] != "") {
                                $import->setIdOrdenCompra($idOrden);
                                if ($import->editImportados($parametros['txtidApartado' . $contadorC])) {
                                    
                                }
                            }
                        }
                        //poner id Orden compra a los toner apartados 

                        $filaC++;
                    }
                    $contadorC++;
                }
            }
            if ((int) $tamanoEquipo > 1) {
                $contadorE = 0;
                $filaE = 0;
                while ($filaE < $tamanoEquipo - 1) {
                    if (isset($parametros['txtEquipoOC' . $contadorE])) {
                        $varequipo = explode(" / ", $parametros['txtEquipoOC' . $contadorE]);
                        $detOC->setNoParteEquipo($varequipo[1]);
                        $detOC->setCantidad($parametros['txtCantidadEquipo' . $contadorE]);
                        $total = (int) $parametros['txtCantidadEquipo' . $contadorE] * (float) $parametros['txtPrecioUnitarioE' . $contadorE];
                        $detOC->setPrecioUnitario($parametros['txtPrecioUnitarioE' . $contadorE]);
                        $detOC->setCostoTotal($total);
                        if (!$detOC->verificarExistenciaEquipo()) {
                            if ($detOC->newRegistroEquipo()) {
                                
                            } else {
                                
                            }
                        }
                        $filaE++;
                    }
                    $contadorE++;
                }
            }
            echo $orden->getIdOrdenCompra();
        } else {
            echo "Error: La orden no se registro";
        }
    } else {
        $orden->setIdOrdenCompra($parametros['idOrden_compra']);
        $detOC->setIdOrdenCompra($parametros['idOrden_compra']);
//        if ($parametros['idEstatusAnterior'] != "71") {// editar solo estatus
//            if ($orden->editRegistro()) {
//                echo $orden->getIdOrdenCompra();
//            } else {
//                echo "Error: La orden no se modificó correctamente";
//            }
//        } else {//editar todo
        if (!empty($_POST['arrayCompiDetalle'])) {
            $auxArrayDet = "'" . implode("','", $_POST['arrayCompiDetalle']) . "'";
            if ($detOC->deleteRegistro($auxArrayDet)) {
                
            } else {
                
            }
        }
        if ($orden->editTodoRegistro()) {
            $filaC = 0;
            $contC = 0;
            while ($filaC < (int) $tamanoComponente - 1) {
                if (isset($parametros['txtidDetalleC' . $contC])) {
                    $varcomp = explode(" // ", $parametros['txtComponentesOC' . $contC]);
                    $detOC->setNoParteComponente($varcomp[1]);
                    $detOC->setCantidad($parametros['txtCantidadComponente' . $contC]);
                    $total = (int) $parametros['txtCantidadComponente' . $contC] * (float) $parametros['txtPrecioUnitarioC' . $contC];
                    $detOC->setPrecioUnitario($parametros['txtPrecioUnitarioC' . $contC]);
                    $detOC->setCostoTotal($total); //precio pesos
                    if (isset($parametros['txtidDetalleC' . $contC]) && $parametros['txtidDetalleC' . $contC] != "") {//editar
                        $detOC->setIdDetalle($parametros['txtidDetalleC' . $contC]);
                        if ($detOC->editRegistroComp()) {
                            
                        } else {
                            
                        }
                    } else {//nuevo                          
                        if ($detOC->verificarExistencia()) {//editar cantidad 
                            if ($detOC->editarCantidadComponente()) {
                            } else {
                            }
                        } else {
                            if ($detOC->newRegistroCompnente()) {
                            } else {
                            }
                        }
                    }
                    $filaC++;
                }
                $contC++;
            }
            $filaE = 0;
            $contE = 0;
            while ($filaE < (int) $tamanoEquipo - 1) {
                if (isset($parametros['txtidDetalleE' . $contE])) {
                    $varequipo = explode(" / ", $parametros['txtEquipoOC' . $contE]);
                    $detOC->setNoParteEquipo($varequipo[1]);
                    $detOC->setCantidad($parametros['txtCantidadEquipo' . $contE]);
                    $total = (int) $parametros['txtCantidadEquipo' . $contE] * (float) $parametros['txtPrecioUnitarioE' . $contE];
                    $detOC->setPrecioUnitario($parametros['txtPrecioUnitarioE' . $contE]);
                    $detOC->setCostoTotal($total);
                    if (isset($parametros['txtidDetalleE' . $contE]) && $parametros['txtidDetalleE' . $contE] != "") {//editar
                        $detOC->setIdDetalle($parametros['txtidDetalleE' . $contE]);
                        if ($detOC->editRegistroEquipo()) {
                            
                        } else {
                            
                        }
                    } else {//nuevo
                        if ($detOC->verificarExistenciaEquipo()) {
                            if ($detOC->editarCantidadEquipo()) {
                            } else {
                            }
                        } else {
                            if ($detOC->newRegistroEquipo()) {
                            } else {
                            }
                        }
                    }
                    $filaE++;
                }
                $contE++;
            }
            echo $orden->getIdOrdenCompra();
        } else {
            echo "Error: La orden no se modificó correctamente";
        }
    }
}
