<?php

session_start();
if ((!isset($_SESSION['user']) || $_SESSION['user'] == "")) {
    header("Location: ../../index.php");
}

include_once("../../Classes/AlmacenConmponente.class.php");
include_once("../../Classes/MovimientoComponente.class.php");
include_once("../../Classes/Bitacora.class.php");
include_once("../../Classes/AlmacenEquipo.class.php");
include_once("../../Classes/Entrada_Orden_Trabajo.class.php");
include_once("../../Classes/Detalle_Orden_Compra.class.php");
include_once("../../Classes/Orden_Compra.class.php");
include_once("../../Classes/Detalle_entrada_Almacen_OC.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Mail.class.php");
include_once("../../Classes/Componente.class.php");
include_once("../../Classes/ParametroGlobal.class.php");

$bitacora = new Bitacora();
$almacenEquipo = new AlmacenEquipo();
$almacenComponente = new AlmacenComponente();
$mc = new MovimientoComponente();
$obj = new Entrada_Orden_trabajo();
$detalle = new Detalle_Orden_Compra();
$ordenCompra = new Orden_Compra();
$det_entrada = new Detalle_entrada_Almacen_OC();
$catalogo = new Catalogo();
$mail = new Mail();
$parametroGlobal = new ParametroGlobal();
$componente = new Componente();
if (isset($_SESSION['user'])) {
    $usuario = $_SESSION['user'];
} else if (isset($_POST['user'])) {
    $usuario = $_POST['user'];
} else {
    $usuario = "";
}
$pantalla = "Entrada_Orden_Individual";
$separador = $_POST['separador'];
$tipos = explode($separador, $_POST['tipo']);
$partes = explode($separador, $_POST['NoParte']);
$almacenes = explode($separador, $_POST['Almacen']);
$cantidades = explode($separador, $_POST['Cantidad']);
$series = explode($separador, $_POST['Serie']);
$ubicaciones = explode($separador, $_POST['Ubicacion']);

$detalles = explode($separador, $_POST['id_detalle']);

$ubicaciones_nueva = explode($separador, $_POST['ubicaciones_nueva']);
$cantidades_nueva = explode($separador, $_POST['cantidad_nueva']);

foreach ($tipos as $key => $value) {
    $obj->setIdOrden($detalles[$key]);
    $no_pedido = $_POST['no_pedido'];

    $id_det_entrada_oc = $detalles[$key];
    $ubicacion_nueva = $ubicaciones_nueva[$key];
    $cantidad_entrada = $cantidades_nueva[$key];
    $id_orden_compra = $ordenCompra->get_registro_by_id_detalle($id_det_entrada_oc);
    $det_entrada->setUsuarioCreacion($usuario);
    $det_entrada->setUsuarioModificacion($usuario);
    $det_entrada->setPantalla($pantalla);
    if ($value == 'C') { //Si es tipo componente
        $almacenComponente->setApartados(0);
        $almacenComponente->setMinimo(0);
        $almacenComponente->setMaximo(0);
        $almacenComponente->setUsuarioCreacion($usuario);
        $almacenComponente->setUsuarioModificacion($usuario);
        $almacenComponente->setPantalla($pantalla);
        $almacenComponente->setNoParte($partes[$key]);
        $almacenComponente->setIdAlmacen($almacenes[$key]);
        $almacenComponente->setExistencia($cantidades[$key]);

        $mc->setIdAlmacenNuevo($almacenComponente->getIdAlmacen());
        $mc->setEntradaSalida(0);
        $mc->setUsuarioCreacion($usuario);
        $mc->setUsuarioModificacion($usuario);
        $mc->setPantalla($pantalla);
        $mc->setComentario($no_pedido);
        $mc->setIdOrden($id_orden_compra);

        $ordenCompra->setUsuarioModificacion($usuario);
        $ordenCompra->setPantalla($pantalla);
        $id_almacen = $almacenComponente->getIdAlmacen();
        if ($almacenComponente->verificarComponenteAlmacen()) {//modificar Existencia
            $almacenComponente->setCantidadSalida($almacenComponente->getExistencia());
            if ($almacenComponente->editarCantidadAlmacenReusrtir()) {
                $mc->setNoParteComponente($almacenComponente->getNoParte());
                $mc->setCantidadMovimiento($almacenComponente->getExistencia());
                if ($mc->newRegistroCompraComponente()) {//movimiento componente
                    $no_parte = $almacenComponente->getNoParte();
                    echo "<br/>El componente " . $no_parte . " fue registrado en el almacén correctamente";
                    $det_entrada->setId_det_entrada($id_det_entrada_oc);
                    $det_entrada->setCantidad($cantidad_entrada);
                    $det_entrada->setUbicacion($ubicacion_nueva);
                    if ($det_entrada->newRegistro()) {
                        if ($det_entrada->verificar_backorder($id_orden_compra, $no_parte)) {//si existe manda correo
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
                            $componente->getRegistroById($no_parte);
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
                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>$no_parte</th>"
                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $componente->getModelo() . "</th>"
                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $componente->getDescripcion() . "</th>"
                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>$cantidad_entrada</th>"
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
                }
            }
        } else {//nuevo registro almacen
            if ($almacenComponente->newRegistro()) {
                $mc->setNoParteComponente($almacenComponente->getNoParte());
                $mc->setCantidadMovimiento($almacenComponente->getExistencia());
                if ($mc->newRegistroCompraComponente()) {//movimiento componente
                    echo "<br/>El componente " . $almacenComponente->getNoParte() . " fue registrado en el almacén correctamente";
                    $det_entrada->setId_det_entrada($id_det_entrada_oc);
                    $det_entrada->setCantidad($cantidad_entrada);
                    $det_entrada->setUbicacion($ubicacion_nueva);
                    if ($det_entrada->newRegistro()) {
                        if ($det_entrada->verificar_backorder($id_orden_compra, $no_parte)) {//si existe manda correo
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
                            $componente->getRegistroById($no_parte);
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
                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>$no_parte</th>"
                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $componente->getModelo() . "</th>"
                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>" . $componente->getDescripcion() . "</th>"
                                    . "<th style='border: 1px black solid;text-align:center;font-size: 10px;'>$cantidad_entrada</th>"
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
                }
            }
        }
    } else { //Si es tipo equipo
        $bitacora->setUsuarioCreacion($usuario);
        $bitacora->setUsuarioModificacion($usuario);
        $bitacora->setPantalla($pantalla);
        $bitacora->setNoParte($partes[$key]);
        $bitacora->setNoSerie($series[$key]);
        $bitacora->setIdAlmacen($almacenes[$key]);

        $almacenEquipo->setUsuarioCreacion($usuario);
        $almacenEquipo->setUsuarioModificacion($usuario);
        $almacenEquipo->setPantalla($pantalla);
        $almacenEquipo->setNoSerie($bitacora->getNoSerie());
        $almacenEquipo->setNoParteEquipo($bitacora->getNoParte());
        $almacenEquipo->setIdAlmacen($bitacora->getIdAlmacen());
        $almacenEquipo->setUbicacion($ubicaciones[$key]);
        $almacenEquipo->setIdOrden($id_orden_compra);

        $existe_bitacora = false;
        if (!$bitacora->verficarExistencia()) {//verificar existencia en bitacora
            if ($bitacora->newRegistro()) {//registrar en bitacora
                $existe_bitacora = true;
            } else {
                echo "<br/>El número de serie " . $bitacora->getNoSerie() . " no se registró en la bitácora<br/>";
            }
        } else {
            $existe_bitacora = true;
        }
        $almacenEquipo->setComentario($no_pedido);
        if ($existe_bitacora && $almacenEquipo->newRegistroCompra()) {//ne registro almacen equipo
            $det_entrada->setId_det_entrada($id_det_entrada_oc);
            $det_entrada->setCantidad($cantidad_entrada);
            $det_entrada->setUbicacion($ubicacion_nueva);
            if ($det_entrada->newRegistro()) {
                
            }
        } else {
            echo "<br/>El número de serie " . $bitacora->getNoSerie() . " no se registró en el almacén<br/>";
        }
    }
}

$detalle->setIdOrdenCompra($_POST['id_compra']);
$detalle->getCantidades();
if ($detalle->getTotalRecibidaAlmacen() >= $detalle->getTotalCantidad()) {//cambiar estatus a surtido orden de compra cuando las cantidades entradas son iguales a las solicitadas
    $ordenCompra->setIdOrdenCompra($detalle->getIdOrdenCompra());
    $ordenCompra->setEstatus(70);
    if ($ordenCompra->editRegistro()) {
        echo "<br/> * La orden de compra se surtio completa correctamente";
    } else {
        echo "<br/>Error: La orden de compra no se surtio completa correctamente";
    }
}
