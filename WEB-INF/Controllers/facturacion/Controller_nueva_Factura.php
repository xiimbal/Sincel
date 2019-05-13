<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Factura2.class.php");
include_once("../../Classes/Cliente.class.php");
include_once("../../Classes/Contrato.class.php");
$parametros = "";
if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}
$factura = new Factura();
$factura->setIdSerie($parametros['Serie']);
$factura->setFormaPago($parametros['FormaPago']);
$factura->setMetodoPago($parametros['MetodoPago']);
$cliente = new Cliente();
if ($cliente->getRegistroById($parametros['RFCReceptor'])) {
    $factura->setIdEmpresa($cliente->getIdDatosFacturacionEmpresa());
    $factura->setRFCEmisor($cliente->getIdDatosFacturacionEmpresa());
    $contrato = new Contrato();
    $resultContrato = $contrato->getRegistroValidacion($parametros['RFCReceptor']);
    if (mysql_num_rows($resultContrato) > 0) {
        if ($rsContrato = mysql_fetch_array($resultContrato)) {
            $factura->setDiasCredito($rsContrato['DiasCredito']);
        }
    } else {
        $factura->setDiasCredito(0);
    }
} else {
    $factura->setIdEmpresa($parametros['RFCEmisor']);
    $factura->setRFCEmisor($parametros['RFCEmisor']);
    $factura->setDiasCredito(0);
}

if (isset($parametros['cfdi33']) && (int) $parametros['cfdi33'] == 1) {
    $factura->setCFDI33(1);
} else {
    $factura->setCFDI33(0);
}

if (isset($parametros['usoCFDI']) && !empty($parametros['usoCFDI'])) {
    $factura->setIdUsoCFDI($parametros['usoCFDI']);
}

$factura->setFacturaPagada("0");
$factura->setTotal("0");
$factura->setRFCReceptor($parametros['RFCReceptor']);
$factura->setNumCtaPago($parametros['NumCtaPago']);
$factura->setTipoArrendamiento($parametros['TipoArrendamiento']);
$factura->setUsuarioCreacion($_SESSION['user']);
$factura->setUsuarioUltimaModificacion($_SESSION['user']);
$factura->setPantalla("PHP Controller_nueva_Factura");

$ndc = false;
if (isset($_POST['ndc']) && $_POST['ndc'] == "1") {
    $factura->setIdSerie(1);
    $factura->setTipoComprobante("egreso");
    $ndc = true;
}

/* Se guardan los n periodos */
$nperiodos = 1;
if (isset($parametros['numero_periodos']) && $parametros['numero_periodos']) {
    $nperiodos = $parametros['numero_periodos'];
}
$array_periodos = array();
for ($i = 1; $i <= $nperiodos; $i++) {
    if (isset($parametros['periodo_facturacion_' . $i])) {
        array_push($array_periodos, $parametros['periodo_facturacion_' . $i]);
    }
}

$factura->setPeriodoFacturacion($array_periodos);
if(isset($parametros['Descuento_general']) && !empty($parametros['Descuento_general'])){
    $factura->setDescuentos($parametros['Descuento_general']);
}

if(isset($parametros['TipoRelacion'])){
    $factura->setTipoRelacion($parametros['TipoRelacion']);
}

if ((isset($parametros['idFactura']) && $parametros['idFactura'] != "")) {
    $factura->setIdFactura($parametros['idFactura']);
    if ($factura->UpdateFactura()) {
        echo $factura->getIdFactura() . "," . $factura->getFolio();
        //echo "La factura se actualizó correctamente.";
    } else {
        echo "Error: no se pudo actualizar la factura, intente mas tarde o contacte con el administrador";
    }
} else {
    if ($factura->NuevaPreFactura()) {
        $factura->getRegistrobyID();
        echo $factura->getIdFactura() . "," . $factura->getFolio();
    } else {
        echo "Error: no se pudo agregar la factura intente mas tarde o contacte con el administrador";
    }
}
?>