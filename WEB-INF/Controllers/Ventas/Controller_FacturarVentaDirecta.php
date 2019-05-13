<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
date_default_timezone_get();
$anio = date('Y');
$mes = date('m');
$fecha = $anio . "-" . $mes . "-" . "01 00:00:00";
include_once("../../Classes/FacturarVentaDirecta.class.php");
$obj = new FacturarVentaDirecta();
$rfcCliente = "";
$nombreCliente = "";
$rfcEmpresa = "";
$nombreEmpresa = "";
$totalFactura = "";
$idfacturaAux = 0;
$idfactura = 0;
$prefijo = "";
$tipo = "";
if (isset($_POST['id']) && $_POST['id'] != "") {
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla("Facturar venta directa");
    $obj->setPathFactura("Facturas/sin facturar");
    $noVenta = $_POST['id'];
    if ($_POST['tipo'] == "1") {//prefactura
        $prefijo = "PREF";
        $tipo = "prefactura";
    } else if ($_POST['tipo'] == "0") {// nota de remision
        $prefijo = "REMIS";
        $tipo = "nota de remisión";
    }
    $obj->setIdVentaDirecta($noVenta);
    if ($obj->getDatosFacturacion()) {
        $rfcCliente = $obj->getRfcCliente();
        $nombreCliente = $obj->getNombreCliente();
        $rfcEmpresa = $obj->getRfcEmpresa();
        $nombreEmpresa = $obj->getNombreEmpresa();
        $totalFactura = $obj->getTotalFactura();
        // echo $rfcCliente . " >> " . $nombreCliente . " >> " . $rfcEmpresa . " >> " . $nombreEmpresa . " >> " . $totalFactura;
        if ($obj->getUltimoIdRegistrado()) {
            $idfacturaAux = $obj->getIdFactura();
            $idfactura = (int) $idfacturaAux + 1;
            // echo $idfactura;
            $obj->setIdFactura($idfactura);
            $obj->setFolio($idfactura . $prefijo);
            $obj->setSerie($prefijo);
            $obj->setPeriodoFacturacion($fecha);
            if ($obj->newFactura()) {
                if ($obj->editIdFactura())
                    echo "La " . $tipo . " se creo correctamente";
                else
                    echo "si modificar";
            } else {
                echo "La " . $tipo . " no se creó correctamente";
            }
        } else {
            echo "No se pudo obener el ultimo id de facturas";
        }
    } else {
        echo "La venta no contiene datos para facturar";
    }
}
?>
