<?php

session_start();

if ((!isset($_SESSION['user']) || $_SESSION['user'] == "") && (!isset($_POST['sistema_autorizado']) || $_POST['sistema_autorizado'] != "true")) {
    header("Location: ../../index.php");
}

include_once("../../Classes/Entrada_Orden_Trabajo.class.php");
include_once("../../Classes/Detalle_Orden_Compra.class.php");
include_once("../../Classes/AlmacenConmponente.class.php");
include_once("../../Classes/MovimientoComponente.class.php");
include_once("../../Classes/Bitacora.class.php");
include_once("../../Classes/AlmacenEquipo.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Orden_Compra.class.php");
include_once("../../Classes/Parametros.class.php");
include_once("../../Classes/Configuracion.class.php");
include_once("../../Classes/Detalle_entrada_Almacen_OC.class.php");


include_once("../../Classes/Mail.class.php");
include_once("../../Classes/Componente.class.php");
include_once("../../Classes/ParametroGlobal.class.php");


$almacenComponente = new AlmacenComponente();
$obj = new Entrada_Orden_trabajo();
$detalle = new Detalle_Orden_Compra();
$mc = new MovimientoComponente();
$bitacora = new Bitacora();
$almacenEquipo = new AlmacenEquipo();
$catalogo = new Catalogo();
$ordenCompra = new Orden_Compra();
$parametros = new Parametros();
$configuracion = new Configuracion();
$det_entrada = new Detalle_entrada_Almacen_OC();

$mail = new Mail();
$parametroGlobal = new ParametroGlobal();
$componente = new Componente();

if (isset($_POST['empresa'])) {
    $empresa = $_POST['empresa'];
    $almacenComponente->setEmpresa($empresa);
    $obj->setEmpresa($empresa);
    $detalle->setEmpresa($empresa);
    $mc->setEmpresa($empresa);
    $bitacora->setEmpresa($empresa);
    $almacenEquipo->setEmpresa($empresa);
    $catalogo->setEmpresa($empresa);
    $ordenCompra->setEmpresa($empresa);
    $parametros->setEmpresa($empresa);
    $configuracion->setEmpresa($empresa);
}

$dividir_recepcion = false;
if ($parametros->getRegistroById("24") && $parametros->getValor() != "0") {
    $dividir_recepcion = true;
}

if (isset($_POST['arrayIdDetalle']) && isset($_POST['arrayCantidad']) && isset($_POST['arrayUbicacion']) && isset($_POST['almacen']) && isset($_POST['estatus'])) {
    $no_pedido = $_POST['no_pedido'];
    $idORdenC = $_POST['estadoOC'];
    $almacen = $_POST['almacen'];
    $estatus = $_POST['estatus'];
    $folio = $_POST['folio'];
    $arrayIdDetalle = $_POST['arrayIdDetalle'];
    $arrayCantidad = $_POST['arrayCantidad'];
    $arrayUbicacion = $_POST['arrayUbicacion'];
    $arrayNoSerie = $_POST['arrayNoSerie'];
    $obj->setCancelado($estatus);
    $obj->setFolioFactura($folio);
    $mc->setComentario($no_pedido);

    if (isset($_SESSION['user'])) {
        $usuario = $_SESSION['user'];
    } else if (isset($_POST['user'])) {
        $usuario = $_POST['user'];
    } else {
        $usuario = "";
    }
    $pantalla = "Entrada de orden de compra";

    $obj->setUsuarioCreacion($usuario);
    $obj->setUsuarioModificacion($usuario);
    $obj->setPantalla($pantalla);
    $ordenCompra->setUsuarioModificacion($usuario);
    $ordenCompra->setPantalla($pantalla);
    $query = $catalogo->obtenerLista("SELECT oc.Estatus FROM  c_orden_compra oc WHERE oc.Id_orden_compra=$idORdenC");
    while ($rs = mysql_fetch_array($query)) {
        $estatusOC = $rs['Estatus'];
    }

    if ($estatusOC == "71") {//cambiar a estatus de surtido parcial      
        $ordenCompra->setIdOrdenCompra($idORdenC);
        $ordenCompra->setEstatus(72);
        if ($ordenCompra->editRegistro()) {
            
        }
    }
    $contadorEquipo = 0;
    $contadorComponente = 0;
    //datos de equipo
    $bitacora->setUsuarioCreacion($usuario);
    $bitacora->setUsuarioModificacion($usuario);
    $bitacora->setPantalla("Entrada orden de compra");
    $almacenEquipo->setComentario($no_pedido);
    $almacenEquipo->setUsuarioCreacion($usuario);
    $almacenEquipo->setUsuarioModificacion($usuario);
    $almacenEquipo->setPantalla($pantalla);
    $almacenEquipo->setIdOrden($idORdenC);
    $almacenComponente->setApartados(0);
    $almacenComponente->setMinimo(0);
    $almacenComponente->setMaximo(0);
    $almacenComponente->setUsuarioCreacion($usuario);
    $almacenComponente->setUsuarioModificacion($usuario);
    $almacenComponente->setPantalla($pantalla);
    $mc->setIdAlmacenNuevo($almacen);
    $mc->setEntradaSalida(0);
    $mc->setUsuarioCreacion($usuario);
    $mc->setUsuarioModificacion($usuario);
    $mc->setPantalla($pantalla);
    $mc->setIdOrden($idORdenC);

    $detalle->setIdOrdenCompra($idORdenC);

    $det_entrada->setUsuarioCreacion($usuario);
    $det_entrada->setUsuarioModificacion($usuario);
    $det_entrada->setPantalla($pantalla);

    if (!empty($arrayUbicacion)) {//verifica si hay entradas
        $x = 0;
        while ($x < count($arrayIdDetalle)) {
            $detalle->setIdDetalle($arrayIdDetalle[$x]);
            $obj->setIdOrden($arrayIdDetalle[$x]);
            $obj->setCantidad($arrayCantidad[$x]);
            $obj->setUbicacion($arrayUbicacion[$x]);

            $detalle->getNoParteBYID();
            $noParteComp = $detalle->getNoParteComponente();
            $noParteEquipo = $detalle->getNoParteEquipo();
            if ($estatus == "1") {
                $obj->setAlmacen("NULL");
            } else {
                $obj->setAlmacen($almacen);
            }
            $obj->setNoSerie($arrayNoSerie[$x]);

            if ($arrayNoSerie[$x] == "++" || $arrayNoSerie[$x] == "%2B%2B") {//entrada componentes
                $obj->setNoSerie("");
                $detalle->getCantidadesPorParte("0");
                if ($detalle->getTotalEntrada() < $detalle->getTotalCantidad()) {//Si ya ha sido recibida, ya no debe de volverse a registrar
                    if ($obj->newRegistro()) {//registrar entrada
                        $detalle->setIdDetalle($arrayIdDetalle[$x]);
                        $detalle->setCantidad($arrayCantidad[$x]);
                        if ($detalle->editRegistroCantidad()) {//modificar cantidades entrada                       
                            if ($estatus == "1") {
                                // $obj->marcarComoRecibidoAlmacen($usuario, $pantalla);
                                $det_entrada->setId_det_entrada($obj->getIdOrden());
                                $det_entrada->setCantidad($arrayCantidad[$x]);
                                $det_entrada->setUbicacion($arrayUbicacion[$x]);
                                if ($det_entrada->newRegistro()) {
                                    
                                }
                                $contadorComponente++;
                            } else {
                                if (!$dividir_recepcion) {//Si la recepcion se hace en un solo paso, se aumentan directamente las existencias
                                    $almacenComponente->setNoParte($noParteComp);
                                    $almacenComponente->setIdAlmacen($almacen);
                                    $almacenComponente->setExistencia($arrayCantidad[$x]);
                                    $id_almacen = $almacenComponente->getIdAlmacen();
                                    if ($almacenComponente->verificarComponenteAlmacen()) {//modificar Existencia
                                        $almacenComponente->setCantidadSalida($arrayCantidad[$x]);
                                        if ($almacenComponente->editarCantidadAlmacenReusrtir()) {
                                            $mc->setNoParteComponente($noParteComp);
                                            $mc->setCantidadMovimiento($arrayCantidad[$x]);
                                            if ($mc->newRegistroCompraComponente()) {//movimiento componente
                                                $contadorComponente++;
                                            }
                                        }
                                    } else {//nuevo registro almacen
                                        if ($almacenComponente->newRegistro()) {
                                            $mc->setNoParteComponente($noParteComp);
                                            $mc->setCantidadMovimiento($arrayCantidad[$x]);
                                            if ($mc->newRegistroCompraComponente()) {//movimiento componente
                                                $contadorComponente++;
                                            }
                                        }
                                    }
                                    $det_entrada->setId_det_entrada($obj->getIdOrden());
                                    $det_entrada->setCantidad($arrayCantidad[$x]);
                                    $det_entrada->setUbicacion($arrayUbicacion[$x]);
                                    if ($det_entrada->newRegistro()) {//agregar en 
                                        $id_orden_compra = $ordenCompra->get_registro_by_id_detalle($obj->getIdOrden());
                                        if ($det_entrada->verificar_backorder($id_orden_compra, $noParteComp)) {//si existe manda correo
                                            $id_ticket = $det_entrada->getId_ticket();
                                            $query_correo = $catalogo->obtenerLista("SELECT cs.correo FROM c_correossolicitud cs WHERE cs.TipoSolicitud=15 AND cs.Activo=1");
                                            $correo = array();
                                            while ($rs_correo = mysql_fetch_array($query_correo)) {//enviar correo de que se rcibio un componente de backorder
                                                array_push($correo, $rs_correo['correo']);
                                            }
                                            $query_lista_correo = $catalogo->obtenerLista("SELECT usu.correo,al.nombre_almacen FROM k_responsablealmacen ra INNER JOIN c_almacen al ON ra.IdAlmacen=al.id_almacen INNER JOIN c_usuario usu ON ra.IdUsuario=usu.IdUsuario aND al.id_almacen='$id_almacen'");
                                            $nombre_almacen = "";
                                            while ($rslista = mysql_fetch_array($query_lista_correo)) {//responsable de almacen
                                                array_push($correo, $rslista['correo']);
                                                $nombre_almacen = $rslista['nombre_almacen'];
                                            }
                                            if ($parametroGlobal->getRegistroById("8")) {//obtiene el from del correo
                                                $mail->setFrom($parametroGlobal->getValor());
                                            } else {
                                                $mail->setFrom("scg-salida@scgenesis.mx");
                                            }
                                            $componente->getRegistroById($noParteComp);
                                            $mail->setSubject("Entrada de componente en almacén $nombre_almacen");
                                            $tabla .= "<table style='border: 1px solid black;border-collapse: collapse;width:100%'>"
                                                    . "<tr>"
                                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>Ticket</th>"
                                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>ORDEN DE COMPRA</th>"
                                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>NO PARTE</th>"
                                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>MODELO</th>"
                                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>DESCRIPCIÓN</th>"
                                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>CANTIDAD</th>"
                                                    . "</tr>";
                                            $tabla .= "<tr>"
                                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>$id_ticket</th>"
                                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>$id_orden_compra</th>"
                                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>$noParteComp</th>"
                                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $componente->getModelo() . "</th>"
                                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $componente->getDescripcion() . "</th>"
                                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>$arrayCantidad[$x]</th>"
                                                    . "</tr>";
                                            $mail->setBody($tabla);
                                            foreach ($correo as $valor) {
                                                if (isset($valor) && $valor != "" && filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                                                    $mail->setTo($valor);
                                                    $mail->enviarMail();
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $contadorComponente++;
                                }
                            }
                        }
                    } else {
                        echo "<br/>El componente $noParteComp no se registro correctamente<br/>";
                    }
                } else {
                    echo "<br/>Error: El componente $noParteComp ya había sido recibido en su totalidad(" . $detalle->getTotalCantidad() . ")";
                }
            } else {//entrada equipo               
                $detalle->setIdDetalle($arrayIdDetalle[$x]); //                  
                $bitacora->setNoParte($noParteEquipo);
                $bitacora->setNoSerie($arrayNoSerie[$x]);
                $bitacora->setIdAlmacen($almacen);
                $almacenEquipo->setNoSerie($arrayNoSerie[$x]);
                $almacenEquipo->setNoParteEquipo($noParteEquipo);
                $almacenEquipo->setIdAlmacen($almacen);
                $almacenEquipo->setUbicacion($arrayUbicacion[$x]);
                if ($estatus == "1") {//Si se cancela el pedido del equipo
                    $detalle->getCantidadesPorParte("1");
                    if ($detalle->getTotalEntrada() < $detalle->getTotalCantidad()) {//Si ya ha sido recibida, ya no debe de volverse a registrar
                        if ($obj->newRegistro()) {
                            //  $obj->marcarComoRecibidoAlmacen($usuario, $pantalla);
                            $det_entrada->setId_det_entrada($obj->getIdOrden());
                            $det_entrada->setCantidad($arrayCantidad[$x]);
                            $det_entrada->setUbicacion($arrayUbicacion[$x]);
                            if ($det_entrada->newRegistro()) {
                                
                            }
                            $detalle->setCantidad($arrayCantidad[$x]);
                            $detalle->setIdDetalle($arrayIdDetalle[$x]);
                            if ($detalle->editRegistroCantidad()) {
                                $contadorEquipo++;
                            }
                        }
                    } else {
                        echo "<br/>Error: Los equipos " . $bitacora->getNoParte() . " ya han sido recibido en su totalidad(" . $detalle->getTotalCantidad() . ")";
                    }
                } else {
                    if (!$det_entrada->verificar_no_serie($arrayNoSerie[$x])) {
                        if (!$dividir_recepcion) {//Si la recepcion se hace en un solo paso, se registra la bitacora y el equipo
                            $existe_bitacora = false;
                            if (!$bitacora->verficarExistencia()) {//verificar existencia en bitacora
                                if ($bitacora->newRegistro()) {//registrar en bitacora
                                    $existe_bitacora = true;
                                } else {
                                    echo "<br/>El número de serie $arrayNoSerie[$x] no se registró en la bitacora correctamente<br/>";
                                }
                            } else {
                                $existe_bitacora = true;
                            }
                            if ($existe_bitacora && $almacenEquipo->newRegistroCompra()) {//ne registro almacen equipo
                            } else {
                                echo "El número de serie $arrayNoSerie[$x] no se registró en almacén<br/>";
                            }
                        }
                        $detalle->getCantidadesPorParte("1");
                        if ($detalle->getTotalEntrada() < $detalle->getTotalCantidad()) {//Si ya ha sido recibida, ya no debe de volverse a registrar
                            /* Verificamos que la serie coincida con el prefijo del modelo */
                            if (!$dividir_recepcion || !$bitacora->verficarExistencia()) {
                                if ($configuracion->validarSerie($bitacora->getNoSerie(), $bitacora->getNoParte())) {
                                    if ($obj->newRegistro()) {//nuevo registro entrada 
                                        $detalle->setCantidad($arrayCantidad[$x]);
                                        $detalle->setIdDetalle($arrayIdDetalle[$x]);
                                        if (!$dividir_recepcion) {
                                            $det_entrada->setId_det_entrada($obj->getIdOrden());
                                            $det_entrada->setCantidad($arrayCantidad[$x]);
                                            $det_entrada->setUbicacion($arrayUbicacion[$x]);
                                            if ($det_entrada->newRegistro()) {
                                                
                                            }
                                        }
                                        if ($detalle->editRegistroCantidad()) {
                                            $contadorEquipo++;
                                        }
                                    }
                                }
                            } else {
                                echo "<br/>Error: El No. de serie <b>" . $bitacora->getNoSerie() . "</b> ya se encuentra registrado en el sistema";
                            }
                        } else {
                            echo "<br/>Error: Los equipos " . $bitacora->getNoParte() . " ya han sido recibido en su totalidad(" . $detalle->getTotalCantidad() . ")";
                        }
                    } else {
                        echo "El número de serie <b>$arrayNoSerie[$x]</b> ya se encuentra registrado<br/>";
                    }
                }
            }
            $x++;
        }

        echo "<br/>Se registraron $contadorComponente componente(s) y $contadorEquipo equipo(s) correctamente<br/>";
        $detalle->setIdOrdenCompra($idORdenC);

        if (($detalle->getCantidades() && !$dividir_recepcion) || 
                ($dividir_recepcion && $detalle->getTotalRecibidaAlmacen() >= $detalle->getTotalCantidad())) {//cambiar estatus a surtido orden de compra cuando las cantidades entradas son iguales a las solicitadas
            $ordenCompra->setIdOrdenCompra($idORdenC);
            $ordenCompra->setEstatus(70);
            if ($ordenCompra->editRegistro()) {
                echo "<br/> * La orden de compra se surtio completa correctamente";
            } else {
                echo "<br/>Error: La orden de compra no se surtio completa correctamente";
            }
        }
    }
}