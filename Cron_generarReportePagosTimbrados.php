<?php
ini_set("memory_limit","1024M");
set_time_limit (0);

include_once("WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

include_once('WEB-INF/Classes/ConexionMultiBD.class.php');
include_once("WEB-INF/Classes/ParametroGlobal.class.php");

include_once ("WEB-INF/Classes/Factura.class.php");
include_once ("WEB-INF/Classes/Factura2.class.php");
include_once ("WEB-INF/Classes/PagoParcial.class.php");
include_once ("WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once ("WEB-INF/Classes/Catalogo.class.php");


 $cabeceras = array("Folio_fac" => "string","RFC_Emisor" => "string","RFC_Receptor" => "string","Nombre_receptor" => "string","Tipo_comprobante" => "string",
     "Versión" => "string","Fecha_de_pago" => "date","Fecha_de_aplicación" => "date","Forma_de_pago" => "string","Banco" => "string","Moneda_fac" => "string",
     "Monto" => "money","Timbre_fiscal" => "string","Folio_pago" => "string","Moneda_pago" => "string","Método_de_pago" => "string","Parcialidad" => "number",
     "Saldo_anterior" => "money","Importe_pagado" => "money","Saldo_actual" => "money","Usuario" => "string","Estatus" => "string");

$con = new ConexionMultiBD();         
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
$con->Desconectar();
while($rs_multi = mysql_fetch_array($result_bases)){ 
    $empresa = $rs_multi['id_empresa'];
    echo "<br/><br/>Procesando empresa ".$rs_multi['nombre_empresa']."<br/>";
    $writer = new XLSXWriter();//Nuevo libro
    $writer->setAuthor('Techra');
    $hoja = "Reporte";
    $writer->writeSheetHeader($hoja, $cabeceras );

$consultaPagos = "SELECT p.IdPagoParcial,
((case when (`f`.`EstadoFactura` = 0) then 'Cancelado' when (`f`.`PendienteCancelar` = 1) then 'Pendiente Cancelar' 
when (`f`.`TipoComprobante` <> 'ingreso') then 'Nota de crÃ©dito' else 
(select (case when (`f`.`EstatusFactura` = 3) then 'Incobrable' else 
(select (case when (`f`.`FacturaPagada` = 0) then 'No pagado' else 'Pagado' end)) end)) end)) AS `Estado`
FROM c_pagosparciales AS p INNER JOIN c_factura AS f ON f.IdFactura = p.IdFactura WHERE p.FolioFiscal IS NOT NULL;";

$parametro_global = new ParametroGlobal();
$parametro_global->setEmpresa($empresa);

$catalogoFacturacion = new CatalogoFacturacion();
$catalogoFacturacion->setEmpresa($empresa);

$catalogo = new Catalogo();
$catalogo->setEmpresa($empresa);

$result = $catalogoFacturacion->obtenerLista($consultaPagos);

while ($row = mysql_fetch_array($result)) {
$idPago = $row['IdPagoParcial'];    
$array_valores = array();

$factura = new Factura_NET();
$factura->setEmpresa($empresa);
$aux = new Factura();
$aux->setEmpresa($empresa);
$pagoParcial = new PagoParcial();
$pagoParcial->setEmpresa($empresa);

$pagoParcial->setId_pago($idPago);
$pagoParcial->getRegistrobyID(true);

$factura->getRegistroById($pagoParcial->getId_factura());




array_push($array_valores,$factura->getSerie().$factura->getFolio());
array_push($array_valores,$factura->getRFCEmisor());
array_push($array_valores,$factura->getRFCReceptor());
array_push($array_valores,$factura->getNombreReceptor());
array_push($array_valores,$factura->getTipoComprobante());
if($factura->getCFDI33() == 1){
    array_push($array_valores,"3.3");
}else{
    array_push($array_valores,"");
}

array_push($array_valores,$pagoParcial->getFechapago());
array_push($array_valores, str_replace("T","-",$pagoParcial->getFechaTimbrado()));

$formaPago = $aux->getNombreFormaPago($pagoParcial->getIdFormaPago());

array_push($array_valores,$formaPago);

$consulta = "SELECT CONCAT(b.Nombre,'-',c.noCuenta) AS banco FROM c_cuentaBancaria AS c INNER JOIN c_banco AS b ON b.IdBanco = c.idBanco
WHERE c.idCuentaBancaria = ".$pagoParcial->getCuentaBancaria().";";
$resultBanco = $catalogo->obtenerLista($consulta);
while ($row1 = mysql_fetch_array($resultBanco)) {
    array_push($array_valores,$row1['banco']);
}

array_push($array_valores,"MXN PESO MEXICANO");
array_push($array_valores,$pagoParcial->getImporte());

array_push($array_valores,$pagoParcial->getFolioFiscal());
array_push($array_valores,$factura->getSerie().$pagoParcial->getFolio());
array_push($array_valores,"MXN PESO MEXICANO");
array_push($array_valores,$aux->getClaveMetodoPago($factura->getMetodoPago()). " " . $aux->getNombreMetodoPago($factura->getMetodoPago()));
$pagoParcial->getNumeroParcialidad();
array_push($array_valores,$pagoParcial->getNumParcialidad());
array_push($array_valores,number_format($factura->getTotal() - $pagoParcial->getImpSaldoAnt(),2,".",""));
array_push($array_valores,number_format($pagoParcial->getImporte(),2,".",""));
array_push($array_valores,number_format($factura->getTotal() - $pagoParcial->getImpSaldoAnt() - $pagoParcial->getImporte(),2,".",""));
array_push($array_valores,$pagoParcial->getUsuarioCreacion());
array_push($array_valores,$row['Estado']);

$writer->writeSheetRow($hoja, $array_valores);
//print_r($array_valores);
//echo "<br>";
}
if($parametro_global->getRegistroById("11")){
        $path = $parametro_global->getValor();
    }else{
        $path = "/html/www/";
    }
    $nombre = $path."Reporte_pagos_timbrados_".$empresa.".xlsx";
    $writer->writeToFile($nombre);
    
    echo '#'.floor((memory_get_peak_usage())/1024/1024)."MB"."\n";

}


