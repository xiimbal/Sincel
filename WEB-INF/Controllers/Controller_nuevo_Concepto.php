<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Concepto.class.php");
$parametros = "";
if (isset($_GET['id']) && $_GET['id'] != "") {
    $concepto = new Concepto();
    $concepto->setIdConcepto($_GET['id']);
    $concepto->deleteRegistro();
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
        
        if (isset($_POST['concepto']) && $_POST['concepto'] != "") {//actualizar
            $concepto->setIdConcepto($_POST['concepto']);
            if ($concepto->updateRegistro()) {
                echo "Se actualizo exitosamente";
            } else {
                echo "Error: El concepto no se actualizo";
            }
        } else {
            if ($concepto->nuevoRegistro()) {
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

