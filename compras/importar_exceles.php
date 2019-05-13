<?php

/*
 * PHP Excel - Read a simple 2007 XLSX Excel file
 */

/** Set default timezone (will throw a notice otherwise) */
date_default_timezone_set('America/Los_Angeles');

include_once '../WEB-INF/Classes/PHPExcel/IOFactory.php';
include_once '../WEB-INF/Classes/Orden_Compra.class.php';
include_once '../WEB-INF/Classes/Detalle_Orden_Compra.class.php';
include_once '../WEB-INF/Classes/Equipo.class.php';
include_once '../WEB-INF/Classes/Entrada_Orden_Trabajo.class.php';
include_once '../WEB-INF/Classes/Configuracion.class.php';

$inputFileName = 'complemento_2012.xlsx';//Cambiar nombre excel

$claveProovedor = "Prov3004";
$empresaFactura = "1";
$formaPago = "1";
$estatus = "70";
$idAlmacen = "6";
$usuario = "sistemas";
$pantalla = "Importar compras complemento 2012";//Cambiar pantalla
$empresa = 1; //Cambiar empresa

//  Read your Excel workbook
try {
    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);
} catch (Exception $e) {
    die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) 
    . '": ' . $e->getMessage());
}

//  Get worksheet dimensions
$sheet = $objPHPExcel->getSheet(0);
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();

$nueva_orden = true;
$proforma = "";
$contador_inserciones = 0;
//  Loop through each row of the worksheet in turn
for ($row = 2; $row <= $highestRow; $row++) {
    if($row == 6){
        continue;
    }
    //  Read a row of data into an array
    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, 
    NULL, TRUE, FALSE);
    if($nueva_orden){
        $orden_compra = new Orden_Compra(); 
        $detalle_orden = new Detalle_Orden_Compra();
        $orden_compra->setEmpresa($empresa);
        $detalle_orden->setEmpresa($empresa);
        
        $orden_compra->setFacturaEmisor($claveProovedor);
        $orden_compra->setFacturaRecptor($empresaFactura);
        $orden_compra->setEstatus($estatus);
        $orden_compra->setCondicionPago($formaPago);
        $orden_compra->setEmbarca("");
        $orden_compra->setNoCliente("");
        $orden_compra->setNoPedidoProv("");
        $orden_compra->setNotas("");
        $orden_compra->setPeso(0);
        $orden_compra->setMetros(0);
        $orden_compra->setTransportista(0);
        $orden_compra->setOrigen("");
        $orden_compra->setMetodoEntrega("");
        $orden_compra->setObservacion("");            
        $orden_compra->setAlmacen($idAlmacen);
        $orden_compra->setActivo(1);
        $orden_compra->setUsuarioCreacion($usuario);
        $orden_compra->setUsuarioModificacion($usuario);
        $orden_compra->setPantalla($pantalla);

        $orden_compra->setTipoCambio($rowData[0][9]);//
        $orden_compra->setFechaOC(PHPExcel_Style_NumberFormat::toFormattedString($rowData[0][6], "YYYY-MM-DD"));//
        
        if($orden_compra->newRegistro()){
            $detalle_orden->actualizarProforma($orden_compra->getIdOrdenCompra(), $rowData[0][0], $usuario, $pantalla);//Actualizamos proforma
            $nueva_orden = false;
        }else{
            echo "<br/>Error: no se pudo crear la orden de compra";
        }                       
    }
    
    foreach($rowData[0] as $k=>$v){  
        $detalle_orden->setCantidad(1);
        $detalle_orden->setDolar(1);
        $detalle_orden->setUsuarioCreacion($usuario);
        $detalle_orden->setUsuarioModificacion($usuario);
        $detalle_orden->setPantalla($pantalla);
        
        if($k == 5){            
            $v = PHPExcel_Style_NumberFormat::toFormattedString($v, "YYYY-MM-DD");
        }
        if($k == 4){
            $detalle_orden->setPrecioUnitario($v);
            $detalle_orden->setCostoTotal($v);
        }
        if($k == 3){
            $serie = $v;            
        }
        
        if($k == 2){
            if($rowData[0][3] == "" || $rowData[0][3] == "N/A"){
                echo "<br/>No es un equipo, no tiene No. Serie: ";
                continue 2;
            }
            $equipo = new Equipo();
            $equipo->setEmpresa($empresa);
            if($equipo->getRegistroByModelo($v)){
               $parte = $equipo->getNoParte(); 
               $detalle_orden->setNoParteEquipo($parte);
            }else{
                $configuracion = new Configuracion();
                $configuracion->setEmpresa($empresa);
                if($configuracion->getRegistroByNoSerie($serie)){
                    $detalle_orden->setNoParteEquipo($configuracion->getNoParte());
                }else{
                    echo "<br/>Error: el modelo $v no se encuentra registrado de la serie ".$rowData[0][3];
                    continue 2;
                }
            }
        }
        
        if($k == 1){
            $folio = $v;
        }
                
        if($proforma!="" && $proforma!=$rowData[0][0]){            
            $nueva_orden = true;
        }
          
        if($k == 0){
            $detalle_orden->setIdOrdenCompra($orden_compra->getIdOrdenCompra());
            $proforma = $v;
        }
    }    
    
    if($detalle_orden->newRegistroEquipo()){
        $entrada_orden = new Entrada_Orden_trabajo();
        $entrada_orden->setEmpresa($empresa);
        $entrada_orden->setIdOrden($detalle_orden->getIdDetalle());
        $entrada_orden->setCantidad(1);
        $entrada_orden->setAlmacen($idAlmacen);
        $entrada_orden->setNoSerie($serie);
        $entrada_orden->setUbicacion("");
        $entrada_orden->setCancelado(0);
        $entrada_orden->setFolioFactura($folio);
        $entrada_orden->setUsuarioCreacion($usuario);
        $entrada_orden->setUsuarioModificacion($usuario);
        $entrada_orden->setPantalla($pantalla);
        if($entrada_orden->newRegistro()){
            $contador_inserciones++;
            echo "<br/>$contador_inserciones]Orden ".$orden_compra->getIdOrdenCompra()." se registro correctamente la entrada ".$entrada_orden->getIdOrden()." del equipo $serie";
        }else{
            echo "<br/>Error: no se pudo registrar la entrada de ".$detalle_orden->getIdDetalle();
        }
    }else{
        echo "<br/>Error: no se pudo insertar el detalle de la compra ".$orden_compra->getIdOrdenCompra();
    }
    
}
?>