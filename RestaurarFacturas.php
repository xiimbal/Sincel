<?php
ini_set("memory_limit","1024M");
set_time_limit (0);

include_once("WEB-INF/Classes/ReporteLectura.class.php");
include_once("WEB-INF/Classes/LeerXML.class.php");
include_once("WEB-INF/Classes/Factura.class.php");

$empresa = 8;
$reporte = new ReporteLectura();
$xml = new LeerXML();
$factura = new Factura_NET();
$reporte->setEmpresa($empresa);
$factura->setEmpresa($empresa);
$directorio = "Santi/scg";//diectorio donde estan los XML debe estar en raiz y sin anidar de contraro no lo procesa
$files = scandir($directorio);

foreach ($files as $value) {//Recorremos todos los archivos del directorio especificado
    if($reporte->endsWith($value, ".xml") || $reporte->endsWith($value, ".XML")){//Si es un archivo XML
        $xml->setXml($directorio."/".$value);
        $xml->getDatosXMLCFDI();        
        if(!$factura->existeFactura($xml->getFolio(), $xml->getFecha(), $xml->getRfcEmisor(), number_format(doubleval($xml->getTotal()), 2, '.', ''))){
            $líneas = file($xml->getXml());
            // Recorre nuestro array, muestra el código fuente HTML como tal y muestra tambíen los números de línea.
            $xml_detalle = "";            
            foreach ($líneas as $num_línea => $línea) {
                $xml_detalle .= htmlspecialchars($línea);
            }
            $factura->setRFCEmisor($xml->getRfcEmisor()); $factura->setNombreEmisor($xml->getNombreEmisor());
            $factura->setRFCReceptor($xml->getRfcReceptor()); $factura->setNombreReceptor($xml->getNombreReceptor());
            $factura->setFolio($xml->getFolio());$factura->setSerie("");
            $factura->setTipoComprobante($xml->getTipoComprobante());            
            $factura->setFacturaXML("");  $factura->setCfdiXML($xml_detalle);
            $factura->setPeriodoFacturacion($xml->getFecha()); $factura->setFechaFacturacion($xml->getFecha());
            $factura->setTotal($xml->getTotal()); 
            $factura->setPathPDF("PDF/Santi/scg/".  substr($value, 0, strlen($value)-4).".PDF");//directorio donde van los PDF tener cuidado si la extencion esta en mayusculas o minusculas
            $factura->setPathXML("$directorio/$value");
            $factura->setTipoFactura("1"); $factura->setFolioFiscal($xml->getUUID());
            $factura->setFacturaEnviada("1");
            
            if($factura->newFactura()){            
                echo "<br/>BIEN: La factura con el folio ".$factura->getFolio()." de ".$factura->getRFCEmisor()." [".$factura->getIdFactura()."] se inserto correctamente";
            }else{
                echo "<br/>ERROR: La factura con el folio ".$factura->getFolio()." de ".$factura->getRFCEmisor()." [".$factura->getIdFactura()."] no se inserto correctamente";
            }
        }else{
            echo "<br/>Ya existe - ".$xml->getFolio()." ".$xml->getFecha()." ".$xml->getRfcEmisor()." ".$xml->getRfcReceptor()." ".$xml->getTotal()."<br/>";
        }                
    }
}
?>
