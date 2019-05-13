<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Factura2.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Concepto.class.php");
if (isset($_GET['id']) && $_GET['id'] != "") {
    $factura = new Factura();
    $factura->setIdFactura($_GET['id']);
    $factura->getRegistrobyID();
    $factura->setUsuarioCreacion($_SESSION['user']);
    $factura->setUsuarioUltimaModificacion($_SESSION['user']);
    $factura->setPantalla("PHP Copiar Factura");
    $factura->setId_TipoFactura(1);
    $factura->setIdEmpresa($factura->getRFCEmisor());
    if ($factura->NuevaPreFactura()) {
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista("SELECT * FROM c_conceptos WHERE idFactura=" . $_GET['id']);
        $concepto = new Concepto();
        $concepto->setIdFactura($factura->getIdFactura());
        $concepto->setUsuarioCreacion($_SESSION['user']);
        $concepto->setUsuarioUltimaModificacion($_SESSION['user']);
        $concepto->setPantalla("PHP Copiar Factura");
        while ($rs = mysql_fetch_array($result)) {
            $concepto->setCantidad($rs['Cantidad']);
            if ($rs['Unidad'] == 0) {
                $concepto->setUnidad("Servicio");
            } else {
                $concepto->setUnidad($rs['Unidad']);
            }
            $concepto->setDescripcion($rs['Descripcion']);
            $concepto->setPrecioUnitario($rs['PrecioUnitario']);
            if ($rs['Tipo'] == "") {
                $concepto->setTipo("null");
            } else {
                $concepto->setTipo($rs['Tipo']);
            }$concepto->setId_articulo($rs['id_articulo']);
            $concepto->setEncabezado(0);
            $concepto->nuevoRegistro();
        }
        echo $factura->getIdFactura() . "," . $factura->getFolio();
    } else {
        echo "Error: no se pudo generar la factura intente mas tarde o contacte con el administrador";
    }
} else {
    echo "Error: no se recibio el folio de la factura";
}
?>