<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Concepto.class.php");
include_once("../../Classes/Factura2.class.php");

$parametros = "";
$notaRemision = new Factura();

if (isset($_GET['id']) && $_GET['id'] != "") {
    $borrar = true;
    $concepto = new Concepto();
    $concepto->setIdConcepto($_GET['id']);
    $concepto->getRegistrobyID();
    
    $notaRemision->setIdFactura($concepto->getIdFactura());
    $notaRemision->getRegistroById();
    $nuevoTotal = $notaRemision->getTotal() - ($concepto->getCantidad() * $concepto->getPrecioUnitario() * 1.16);
    
    if($notaRemision->tienePagos()){
        $pagado = $notaRemision->totalPagado();
        if($nuevoTotal < $pagado){
            echo "Error: No se puede borrar el concepto debido a que lo pagado sería mayor al nuevo total de la nota de remisión";
            $borrar = false;
        }
    }
    if($borrar){
        $notaRemision->setUsuarioUltimaModificacion($_SESSION['user']);
        $notaRemision->setPantalla("Eliminar Concepto");
        $notaRemision->setTotal($nuevoTotal);
        $notaRemision->actualizarTotal();
        $concepto->deleteRegistro();
    }
} else {
    if (isset($_POST['idFactura']) && $_POST['idFactura'] != "") {
        parse_str($_POST['form'], $parametros);
        $concepto = new Concepto();
        $concepto->setIdFactura($_POST['idFactura']);
        if (isset($_POST['concepto']) && $_POST['concepto'] != "") {
            $concepto->setCantidad($parametros['cantidad_'.$_POST['concepto']]);
            //$concepto->setUnidad($parametros['Unidad']);
            if(isset($parametros['Unidad_'.$_POST['concepto']]) && !empty($parametros['Unidad_'.$_POST['concepto']])){
                $concepto->setUnidad($parametros['Unidad_'.$_POST['concepto']]);
            }
            if(isset($parametros['producto_'.$_POST['concepto']]) && !empty($parametros['producto_'.$_POST['concepto']])){
                $concepto->setIdEmpresaProductoSAT($parametros['producto_'.$_POST['concepto']]);
            }
            $concepto->setDescripcion($parametros['descripcion_'.$_POST['concepto']]);
            $concepto->setPrecioUnitario($parametros['preciounitario_'.$_POST['concepto']]);
            if(isset($parametros['descuento_partida_'.$_POST['concepto']])){
                $concepto->setDescuento($parametros['descuento_partida_'.$_POST['concepto']]);
                if($parametros['porcentaje_partida_'.$_POST['concepto']]){
                    $concepto->setPorcentaje(1);
                }else{
                    $concepto->setPorcentaje(0);
                }
            }
        } else {
            $concepto->setCantidad($parametros['Cantidad']);
            if(isset($parametros['Unidad']) && !empty($parametros['Unidad'])){
                $concepto->setUnidad($parametros['Unidad']);
            }
            if(isset($parametros['producto']) && !empty($parametros['producto'])){
                $concepto->setIdEmpresaProductoSAT($parametros['producto']);
            }
            $concepto->setDescripcion($parametros['Descripcion']);
            $concepto->setPrecioUnitario($parametros['PrecioUnitario']);
            if(isset($parametros['descuento_partida'])){
                $concepto->setDescuento($parametros['descuento_partida']);
                if($parametros['porcentaje_partida']){
                    $concepto->setPorcentaje(1);
                }else{
                    $concepto->setPorcentaje(0);
                }
            }
        }
        $concepto->setPantalla("PHP Controller_nuevo_concepto");
        $concepto->setFechaCreacion("NOW()");
        $concepto->setFechaUltimaModificacion("NOW()");
        $concepto->setUsuarioCreacion($_SESSION['user']);
        $concepto->setTipo("null");
        $concepto->setId_articulo("null");
        $concepto->setUsuarioUltimaModificacion($_SESSION['user']);
        
        $notaRemision->setIdFactura($concepto->getIdFactura());
        $notaRemision->getRegistroById();
        if (isset($_POST['concepto']) && $_POST['concepto'] != "") {//actualizar
            $concepto->setIdConcepto($_POST['concepto']);
            $actualizar = true;
            $conceptoAnterior = new Concepto();
            $conceptoAnterior->setIdConcepto($concepto->getIdConcepto());
            $conceptoAnterior->getRegistrobyID();
            
            $diferencia = ($concepto->getCantidad() * $concepto->getPrecioUnitario() * 1.16) - ($conceptoAnterior->getCantidad() * $conceptoAnterior->getPrecioUnitario() * 1.16);
            
            $nuevoTotal = $notaRemision->getTotal() + $diferencia;
            
            if($notaRemision->tienePagos()){
                $pagado = $notaRemision->totalPagado();
                if($diferencia < 0){
                    echo "Error: No se puede editar el concepto debido a que lo pagado sería mayor al nuevo total de la nota de remisión";
                    $actualizar = false;
                }
            }
            
            if ($actualizar && $concepto->updateRegistro()) {
                $notaRemision->setUsuarioUltimaModificacion($_SESSION['user']);
                $notaRemision->setPantalla("Eliminar Concepto");
                $notaRemision->setTotal($nuevoTotal);
                $notaRemision->actualizarTotal();
                echo "Se actualizo exitosamente";
            } else {
                echo "Error: El concepto no se actualizo";
            }
        } else {
            if ($concepto->nuevoRegistro()) {
                $notaRemision->setTotal($notaRemision->getTotal() + ($concepto->getCantidad() * $concepto->getPrecioUnitario() * 1.16));
                $notaRemision->setPantalla("Nuevo Concepto");
                $notaRemision->setUsuarioUltimaModificacion($_SESSION['user']);
                $notaRemision->actualizarTotal();
                echo "Se agregó exitosamente";
            } else {
                echo "Error: El concepto no se agregó";
            }
        }
    } else {
        echo "Error: No se recibió el Concepto";
    }
}
?>

