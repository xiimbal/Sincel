<?php

header('Content-Type: text/html; charset=UTF-8');
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
//importamos las classes
include_once("WEB-INF/Classes/CatalogoFacturacion.class.php");

//inicializo el objeto
$catalogo = new CatalogoFacturacion();

//obtenemos los resultados del query
$query = $catalogo->obtenerLista("SELECT * FROM c_factura WHERE ISNULL(FacturaXML) OR FacturaXML = '' AND RFCReceptor!='' ORDER BY FechaFacturacion;");

//recorremos los resultados
while ($rs = mysql_fetch_array($query)) {
    //verificamos que el archivo exista
    if (file_exists($rs['PathXML'])) {
        //obtenemos el archivo
        $archivo = file_get_contents($rs['PathXML']);
        $cfdiTimbrado = $archivo;
        //reemplazamos la cadena por una vacía
        $facturaXML = str_replace("tfd:", "", str_replace("cfdi:", "", $archivo));
        $catalogo->obtenerLista("UPDATE c_factura SET FacturaXML='$facturaXML',cfdiXML='$cfdiTimbrado',cfdiTimbrado='$cfdiTimbrado' WHERE IdFactura=" . $rs['IdFactura']);
        echo "Se encontro el archivo " . $rs['PathXML'] . " y la factura con id " . $rs['IdFactura'] . " y el folio " . $rs['Folio'] . " se actualizo </br>";
    } else {
        echo "Error: No se encontro el archivo " . $rs['PathXML'] . " de la factura con id " . $rs['IdFactura'] . " y el folio " . $rs['Folio'] . "<br/>";
    }
}
?>
