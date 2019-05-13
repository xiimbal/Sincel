<?php

ini_set("memory_limit","256M");
set_time_limit (0);

include_once("WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
include_once("WEB-INF/Classes/Catalogo.class.php");
include_once("WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("WEB-INF/Classes/ReporteFacturacion_net.class.php");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$con = new ConexionMultiBD();         
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
$con->Desconectar();
while($rs_multi = mysql_fetch_array($result_bases)){
    $empresa = $rs_multi['id_empresa'];
        
    echo "<br/><br/>Procesando empresa ".$rs_multi['nombre_empresa']."<br/>";    
    
    $parametro_global = new ParametroGlobal();
    $parametro_global->setEmpresa($empresa);
    
    if($parametro_global->getRegistroById("11")){
        $path = $parametro_global->getValor();
    }else{
        $path = "/html/www/";
    }
    /*header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');*/
    $writer = new XLSXWriter();
    $writer->setAuthor('Techra');
    $cabeceras = array('Factura' => 'string', 'Fecha' => "date", 'Numero_cliente' => "string", 'Nombre_cliente' => "string", 'Subtotal' => "money",
        'IVA' => "money", 'Total' => "money", 'Nota_de_credito' => "number", 'Pagos' => "money" ,'Importe_por_pagar' => "money",
        'Fecha_pago' => "date",'Fecha_de_captura' => "date" ,'Estado' => "string", 'Bancos' => "string" , 'Referencia' => "string",'Razon_social_emisor' => "string", 'Tipo_factura' => "string", 
        'Ejecutivo_atencion' => "string",'Periodo' => "string", 'Fecha_de_comentario' => "string", 'Observaciones' => "string",'Usuario_CXC' => "string",'Método_de_pago' => "string");
    
    $hoja = "Reporte";
    $writer->writeSheetHeader($hoja, $cabeceras);

    $catalogo = new Catalogo();
    $catalogo->setEmpresa($empresa);
    $reporte = new ReporteFacturacion();
    $reporte->setEmpresa($empresa);
    if (isset($_GET['Ejecutivo']) && $_GET['Ejecutivo'] != "") {
        $reporte->setEjecutivo($_GET['Ejecutivo']);
    }

    if (isset($_GET['RFC']) && $_GET['RFC'] != "") {
        $reporte->setRFC($_GET['RFC']);
    }
    if (isset($_GET['fecha1']) && $_GET['fecha1'] != "") {
        $reporte->setFechaInicial($_GET['fecha1']);
    }
    if (isset($_GET['fecha2']) && $_GET['fecha2'] != "") {
        $reporte->setFechaFinal($_GET['fecha2']);
    }
    if (isset($_GET['rfccliente']) && $_GET['rfccliente'] != "") {
        $reporte->setRfccliente($_GET['rfccliente']);
    }
    if (isset($_GET['cliente']) && $_GET['cliente'] != "") {
        $reporte->setCliente($_GET['cliente']);
    }
    if (isset($_GET['status']) && $_GET['status'] != "") {
        $estatus = explode(",", $_GET['status']);
        $reporte->setStatus($estatus);
    }
    if (isset($_GET['docto']) && $_GET['docto'] != "") {    
        $reporte->setDocto($_GET['docto']);
    }
    if (isset($_GET['folio']) && $_GET['folio'] != "") {
        $reporte->setFolio($_GET['folio']);
    }
    if (isset($_GET['TF']) && $_GET['TF'] != "") {
        $tipos = explode(",", $_GET['TF']);
        $reporte->setTipoFactura($tipos);
    }
    if (isset($_GET['periodo']) && $_GET['periodo'] != "") {
        $reporte->setPeriodoFacturacion($_GET['periodo']);
    }

    $result = $reporte->getTablaCXC(false);
    $folioAnterior = 0;
    $pagado = 0;
    while ($rs = mysql_fetch_array($result)) {
        $array_valores = array();

        $subtotal = (float) str_replace(',','',$rs['subtotal']);
        $importe = (float) str_replace(',','',$rs['importe']);
        $total = (float) str_replace(',','',$rs['Total']);
        $estadoFactura = "";
        switch ($rs['EstadoFactura']){
            case 'C':
                $estadoFactura = "Cancelado";
                break;
            case 'INC':
                $estadoFactura = "Incobrable";
                break;
            case 'NP':
                $estadoFactura = "No pagado";
                break;
            case 'P':
                $estadoFactura = "Pagado";
                break;
            case 'NDC':
                $estadoFactura = "Nota de crédito";
                break;
            default:
                $estadoFactura = $rs['EstadoFactura'];
                break;
        }
        $tipoComprobante = "";

        if($folioAnterior == $rs['Folio']){
            $pagado += $rs['ImportePagado'];
        }else{
            $pagado = $rs['ImportePagado'];
        }
        array_push($array_valores, $rs['Folio']);
        array_push($array_valores, $rs['FechaFacturacion']);
        array_push($array_valores, $rs['ClaveCliente']);
        array_push($array_valores, $rs['NombreReceptor']);
        array_push($array_valores, $subtotal);
        array_push($array_valores, $importe);
        array_push($array_valores, $total);
       $folioAnterior = $rs['Folio'];

        switch ($rs['TipoComprobante']){
            case 'F':
                $tipoComprobante = "Factura";                
                array_push($array_valores, $rs['PagadoNDC']);//Debe de ser el monto de la NDC.
                array_push($array_valores, $rs['ImportePagado']);
                break;
            case 'NDC':
                $tipoComprobante = "Nota de crédito";
                array_push($array_valores,$rs['ImportePagado']);
                array_push($array_valores,0);
                break;
            default:
                array_push($array_valores,0);
                array_push($array_valores,0);
                $tipoComprobante = "";
                break;
        }

        array_push($array_valores, ($total - $pagado));
     
        if(isset($rs['FechaPago']) && $rs['FechaPago']!="0000-00-00 00:00:00"){
            $fechaPago = substr($rs['FechaPago'],0,10);
        }else{
            $fechaPago = "";
        }
        if(isset($rs['Fecha_de_captura']) && $rs['Fecha_de_captura']!="0000-00-00 00:00:00"){
            $fechaCaptura = substr($rs['Fecha_de_captura'],0,10);
        }else{
            $fechaCaptura = "";
        }
        if(isset($rs['PeriodoFacturacion'])){
            $periodo = substr($catalogo->formatoFechaReportes($rs['PeriodoFacturacion']), 6);
        }else{
            $periodo = "";
        }
        $pagos = $rs['PagadoNDC'];
        
        $banco = "";
        if(isset($rs['idCuentaBancaria']) && $rs['idCuentaBancaria'] != ""){
            $queryBanco = "SELECT cb.*,b.Nombre from c_cuentaBancaria cb 
                        LEFT JOIN c_banco AS b ON b.IdBanco = cb.idBanco WHERE cb.idCuentaBancaria = ".$rs['idCuentaBancaria'];
        }else{
            $queryBanco = "SELECT cb.*,b.Nombre from c_cliente c
                        LEFT JOIN c_cuentaBancaria AS cb ON c.idCuentaBancaria = cb.idCuentaBancaria
                        LEFT JOIN c_banco AS b ON b.IdBanco = cb.idBanco
                        WHERE c.RFC = '".$rs['RFCReceptor']."'";
        }
        $resultBanco = $catalogo->obtenerLista($queryBanco);
        if($rsBanco = mysql_fetch_array($resultBanco)){
            $banco = $rsBanco['Nombre']."- XXXX".substr($rsBanco['noCuenta'],strlen($rsBanco['noCuenta']) - 4);
        }
        if(strcmp($banco,"- XXXX") == 0){
            $banco = "";
        }
        
        array_push($array_valores, $fechaPago);
        array_push($array_valores, $fechaCaptura);
        array_push($array_valores, $estadoFactura);
        array_push($array_valores, $banco);
        array_push($array_valores, $rs['Referencia']);
        array_push($array_valores, $rs['NombreEmisor']);
        array_push($array_valores, $tipoComprobante);
        array_push($array_valores, $rs['Ejecutivo_atencion']);
        array_push($array_valores, $periodo);
        array_push($array_valores, $fechaCaptura);
        array_push($array_valores, $rs['Observaciones']);        
        array_push($array_valores, $rs['UsuarioCreacion']);  
        
        if($rs['MetodoPago'] != "" && $rs['MetodoPago'] != null){
                    $consultaMetodo = "SELECT ClaveMetodoPago FROM c_metodopago WHERE IdMetodoPago = ".$rs['MetodoPago'].";";
                    $resultMetodo = $catalogo->obtenerLista($consultaMetodo);
                    while ($row = mysql_fetch_array($resultMetodo)) {
                        array_push($array_valores, $row['ClaveMetodoPago']);
                    }
                }else{
                    array_push($array_valores, "");
                }

        $writer->writeSheetRow($hoja, $array_valores);
    }

    //$writer->writeToStdOut();
    /*$writer->writeToFile('example.xlsx');
    echo $writer->writeToString();*/
    $nombre = $path."Reporte_facturacionCXC_".$empresa.".xlsx";
    echo $nombre;
    $writer->writeToFile($nombre);
    echo '#'.floor((memory_get_peak_usage())/1024/1024)."MB"."\n";
}



?>
