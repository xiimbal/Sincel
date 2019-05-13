<?php

include_once("WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("WEB-INF/Classes/ConexionMultiBD.class.php");

$con = new ConexionMultiBD();         
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
$con->Desconectar();
while($rs_multi = mysql_fetch_array($result_bases)){
    echo "<br/><br/>Procesando empresa ".$rs_multi['nombre_empresa'];
    $empresa = $rs_multi['id_empresa'];
    
    $catalogo = new CatalogoFacturacion();
    $catalogo->setEmpresa($empresa);
    $consulta = "SELECT IdFactura, Folio, RFCEmisor, NombreEmisor, RFCReceptor, NombreReceptor, FechaFacturacion, PathPDF, PathXML FROM c_factura WHERE FechaFacturacion >= '2015-01-01 00:00:00';";
    $result = $catalogo->obtenerLista($consulta);
    while($rs = mysql_fetch_array($result)){
        if(isset($rs['PathPDF']) && !file_exists($rs['PathPDF'])){
            echo "<br/>a. El archivo PDF de la factura ".$rs['Folio']." del emisor ".$rs['RFCEmisor']." con fecha ".$rs['FechaFacturacion']." no se encuentra en el servidor";
        }
        if(isset($rs['PathXML']) && !file_exists($rs['PathXML'])){
            echo "<br/>b. El archivo XML de la factura ".$rs['Folio']." del emisor ".$rs['RFCEmisor']." con fecha ".$rs['FechaFacturacion']." no se encuentra en el servidor";
        }
    }
    
}