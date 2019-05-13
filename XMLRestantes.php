<?php
include_once("WEB-INF/Classes/CatalogoFacturacion.class.php");

$catalogo = new CatalogoFacturacion();
$catalogo->setEmpresa(1);
$consulta = "SELECT IdFactura, Folio, PathXML, cfdiTimbrado FROM c_factura WHERE PathXML LIKE '%XML/%' AND Serie = '' ORDER BY Folio;";
$result = $catalogo->obtenerLista($consulta);

while($rs = mysql_fetch_array($result)){
    if(isset($rs['PathXML']) && $rs['PathXML']!="" && !file_exists($rs['PathXML'])){
        echo "<br/>".$rs['Folio'].": falta el archivo ".$rs['PathXML'];
        if(isset($rs['cfdiTimbrado']) && $rs['cfdiTimbrado']!=""){
            if(!file_put_contents($rs['PathXML'], $rs['cfdiTimbrado'])){
                echo " y NO se creó el archivo";
            }else{
                echo " y SI se creó el archivo";
            }
        }
    }
}
?>
