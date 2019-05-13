<?php
header('Content-Type: text/html; charset=UTF-8');
header('Content-type: application/x-msdownload; charset=UTF-8');
header('Content-Disposition: attachment; filename=ReporteLecturas.xls');
header('Pragma: no-cache');
header('Expires: 0');

date_default_timezone_set('America/Mexico_City');
session_start();

if(!isset($_POST['post'])){
    header("Location: ../index.php");
}

$POST = unserialize($_POST['post']);

?>
<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

if (!isset($POST['cliente']) || !isset($POST['localidad'])) {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/ReporteLectura.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Contacto.class.php");
include_once("../WEB-INF/Classes/CentroCostoReal.class.php");
include_once("../WEB-INF/Classes/Zona.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("../WEB-INF/Classes/ServicioGeneral.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");

$iva = 0.16;
$catalogo = new Catalogo();
$obj_cliente = new Cliente();
$obj_direccion = new Localidad();
$obj_contacto = new Contacto();
$obj_zona = new Zona();
$reporte = new ReporteLectura();
$caracteristicas = new EquipoCaracteristicasFormatoServicio();
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$prefijos = array("gim","gfa","im","fa");//Prefijos a revisar (siempre se toman las prioridades: im, fa, gim, gfa)                
$direcciones = array();

$parametros = new Parametros(); 
$mostrarContadores = true;
if($parametros->getRegistroById("13") && $parametros->getValor() == "0"){
    $mostrarContadores = false;
}

/*************************      Variables de parametros     *************************/
if(isset($POST['resal_perio']) && $POST['resal_perio']=="1"){
    $resaltar_encabezado = true;
}else{
    $resaltar_encabezado = false;
}

/*Mostrar equipos*/
if(isset($POST['Mostrar_Serie']) && $POST['Mostrar_Serie']=="1"){
    $MostrarSeries = true;    
}else{
    $MostrarSeries = false;    
}

/*Mostrar equipos*/
if(isset($POST['Mostrar_Modelo']) && $POST['Mostrar_Modelo']=="1"){
    $MostrarModelo = true;
}else{
    $MostrarModelo = false;
}

/*Mostrar Localidades*/
if(isset($_POST['MostrarLocalidad']) && $_POST['MostrarLocalidad']=="1"){    
    $MostrarLocalidad = true;    
}else{    
    $MostrarLocalidad = false;    
}

/*Mostrar direccion*/
if(isset($POST['dir_rep']) && $POST['dir_rep']=="1"){
    $MostrarDireccion = true;    
}else{
    $MostrarDireccion = false;    
}

/*Mostrar ubicacion*/
if(isset($POST['mostrar_area']) && $POST['mostrar_area']=="1"){
    $MostrarUbicacion = true;    
}else{
    $MostrarUbicacion = false;    
}

/*Renta adelantada*/
if(isset($POST['fact_adel']) && $POST['fact_adel']=="1"){
    $FacturaAdelantada = true;    
}else{
    $FacturaAdelantada = false;    
}

/*Imprimer cantidad en 0 */
if(isset($POST['MostrarImporteCero']) && $POST['MostrarImporteCero']=="1"){
    $imprimir_cero = 1;
}else{
    $imprimir_cero = 0;
}

/*Dividir las facturas de renta y de lecturas*/
if(isset($POST['rentas_lecturas']) && $POST['rentas_lecturas']=="1"){
    $dividir_lecturas = true;
}else{
    $dividir_lecturas = false;
}

/*Dividir las facturas de renta y de lecturas*/
if(isset($POST['MostrarEncabezadoServicio']) && $POST['MostrarEncabezadoServicio']=="1"){
    $mostrarEncabezadosServicio = true;
}else{
    $mostrarEncabezadosServicio = false;
}

/*Dividir las facturas de renta y de lecturas*/
if(isset($POST['Agrupar_Renta']) && $POST['Agrupar_Renta']=="1"){
    $mostrarRenta = true;
}else{
    $mostrarRenta = false;
}

/*Dividir las facturas de color y BN*/
if(isset($POST['Dividir_Color']) && $POST['Dividir_Color']=="1"){
    $agrupar_color = true;
}else{
    $agrupar_color = false;
}

if($resaltar_encabezado){
    $resaltado_i = "<b>";
    $resaltado_f = "</b>";
}else{
    $resaltado_i = "";
    $resaltado_f = "";
}

$formaPago = "";
$NoContrato = "";
$encabezado_variable = $reporte->getEncabezadoServicio($POST, $prefijos);//Obtenemos los encabezados personalizados.
$unidad_servicio = $reporte->getUMServicio($POST);//Obtenemos las unidades de medida personalizadas.
$conceptos_adicionales = $reporte->getConceptosAdicionales($POST);//Conceptos adicionales.

$orden = $POST['num_orden'];
$proveedor = $POST['num_prov'];
$obs_dentro_xml = $POST['obs_in_xml'];
$obs_fuera_xml = $POST['obs_out_xml'];

/*Division de facturas*/
$agrupar_equipo = false;
$agrupar_servicio = false;
$agrupar_tipo_servicio = false;
$agrupar_localidad = false;
$agrupar_zona = false;
$agrupar_cc = false;
$agrupar_todo = false;

if(isset($POST['dividir_factura'])){
    switch ($POST['dividir_factura']){
        case "0":
            $agrupar_todo = true;
            break;
        case "1":
            $agrupar_servicio = true;
            break;
        case "2":
            $agrupar_tipo_servicio = true;
            break;
        case "3":
            $agrupar_localidad = true;
            break;
        case "4":
            $agrupar_cc = true;
            break;
        case "5":
            $agrupar_zona = true;
            break;
        default:
            $agrupar_equipo = true;
            break;
    }
}

/*Detalles por lectura*/
$MostrarEquipos = true;
$mostrarDetalleServicio = false;
$mostrarDetalleLocalidad = false;
$mostrarDetalleCC = false;
$mostrarDetalleZona = false;
/*if(isset($POST['agrupar_factura'])){
    switch ($POST['agrupar_factura']){        
        case "0":
            $MostrarEquipos = true; 
            break;
        case "1":
            $mostrarDetalleServicio = true;
            break;
        case "2":
            $mostrarDetalleLocalidad = true;
            break;        
        case "3":
            $mostrarDetalleCC = true;
            break;
        case "4":
            $mostrarDetalleZona = true;
            break;
    }
}*/

$cliente = $POST['cliente'];
$cc = $POST['localidad'];
$cen_costo = $POST['centro_costo'];
$anexo = $POST['anexo'];
$contrato = $POST['contrato'];
$zona = $POST['zona'];

/*Obtenemos la fecha a procesar*/
if(isset($POST['fecha']) && $POST['fecha']!=""){                            
    $month = substr($POST['fecha'], 0, 2);
    $year = substr($POST['fecha'], 3, 4);                               
}else{
    $month = date('m');
    $year = date('Y');                            
}   

$localidades = array();
if(empty($cc)){//Sino se filtro una localidad, se obtienen las localidades que fueron marcadas como direccion fiscal
    $query = $catalogo->obtenerLista("SELECT ClaveCentroCosto FROM c_centrocosto WHERE ClaveCliente = '$cliente' AND Activo = 1 AND TipoDomicilioFiscal = 1");
    while($rs = mysql_fetch_array($query)){
        array_push($localidades, $rs['ClaveCentroCosto']);
    }
}else{
    array_push($localidades, $cc);
}
//print_r($localidades);
$liga_excel = "ReporteLecturaXLS.php?cl=$cliente";
if($cc != ""){
    $liga_excel.="&cc=$cc";
}
if($cen_costo!=""){
    $liga_excel.="&cco=$cen_costo";
}
if($anexo!=""){
    $liga_excel.="&an=$anexo";
}
if(isset($POST['fecha']) && $POST['fecha']!=""){
    $liga_excel.="&fe=".$POST['fecha'];
}

$tiposDomicilios = array(0,1);

$obj_cliente->getRegistroById($cliente);
$obj_direccion->getLocalidadByClave($cliente);
$obj_contacto->getContactoByClaveEspecial($cliente);
$obj_zona->getRegistroById($obj_cliente->getClaveZona());

$form = "form_facturalectura";
if(isset($_GET['postfijo']) && $_GET['postfijo']!=""){
    $form.= ("_".$_GET['postfijo']);
}
?>
<!DOCTYPE>
<html lang="es" style="width: 100%;">
    <head>
        <title>Reporte de facturación</title>
        <link rel="icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <!-- JS -->
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />        
        <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
		<script src="../resources/js/jquery/jquery-ui.min.js"></script>   
        <!--<script type="text/javascript" language="javascript" src="../resources/js/paginas/exportar_excel.js"></script>-->
        <style>
            table{
                border-collapse:collapse;
            }            
            .borde{border: 1px solid #000;}
        </style>
        <script>
            $(".button").button();
        </script>
    </head>
    <body style="min-width: 96%;">                              
        <div style="width: 100%;" class="reporte">              
            <form id="<?php echo $form; ?>" name="<?php echo $form; ?>" action="../facturacion/facturarReporteLectura.php" method="POST" target="_blank">                                
            <?php
                $prefijo_pagml = array("PAGINAS","ML","PAGINAS","ML"); //Este array debe de ir asociado a los prefijos del array prefijos
                $NumeroFacturas = 0; $hay_resultados = false;
                foreach ($tiposDomicilios as $value) {
                    if($value == 1){
                        $limit = count($localidades);                        
                    }else{
                        $limit = 1;
                    }                    
                    for($j=0; $j<$limit; $j++){                        
                        if($value == 1){
                            //echo "Procesando ".$localidades[$i];
                            $cc_aux = $localidades[$j];
                        }else{
                            $cc_aux = $cc;
                        }
                        $consulta = $reporte->generarConsultaConRetiros($anexo, $cc_aux, $cen_costo, $cliente, $contrato, $zona,
                                $agrupar_equipo, $agrupar_localidad, $agrupar_cc, $agrupar_servicio, $agrupar_zona, $agrupar_tipo_servicio, $value, $year, $month, $MostrarLocalidad);
                        //echo $consulta;
                        $result = $catalogo->obtenerLista($consulta);                    
                        $prefijo = 0; $no_equipo = 1; $nueva_hoja = true;
                        if(mysql_num_rows ($result) > 0){
                            $hay_resultados = true;
                            //Inicializacion de variables
                            $costoTotalPorGrupo = 0;
                            $contadorBNServicio = array(); $contadorColorServicio = array(); 
                            $costoRentaServicio = array(); $costoExcedentesBN = array(); $costoExcedentesColor = array(); $costoProBN = array(); $costoProColor = array(); 
                            $particularServicio = array(); $excedenteBNServicio = array(); $excedenteColorServicio = array(); $idServicioByKServicio = array();
                            $incluidosBNServicio = array(); $incluidosColorServicio = array(); $seriesServicio = array();
                            /*Arreglos para las agrupaciones*/
                            $nombreAgrupaciones = array(); $rentaAgrupacion = array(); $esParticularAgrupacion = array(); $impresionesBNAgrupacion = array(); $impresionesColorAgrupacion = array();
                            $costoExcedentesBNAgrupacion = array(); $costoExcedentesColorAgrupacion = array(); $incluidosBNAgrupacion = array(); 
                            $incluidosColorAgrupacion = array(); $excedenteBNAgrupacion = array(); $excedenteColorAgrupacion = array();
                            $costoProcesadasBNAgrupacion = array(); $costoProcesadasColorAgrupacion = array(); $equiposPorAgrupacion = array(); $seriesPorAgrupacion = array();
                            $excedentes_bn_por_equipo = array(); $excedentes_color_por_equipo = array();
                            $cliente_por_factura = array(); $anexos_por_factura = array(); $zonas_por_factura = array(); $cc_por_factura = array(); $localidad_por_factura = array();

                            $IdKServicioAnterior = "0"; $idServicioAnterior = "0"; $prefijoAnterior = ""; $localidadAnterior = ""; $NombreLocalidadAnterior = "";
                            $impresionesBNPorServicio = 0; $impresionesColorPorServicio = 0; $equiposPorServicio = 0; $NumeroRentaPorServicio = 0; $NumeroConceptosColor = 0;
                            $NumeroConcepto = 0; $descripcion_renta = ""; 

                            $cobrarRenta = false; $cobrarExcedenteBN = false; $cobrarExcedenteColor = false; $cobrarProcesadasBN = false; $cobrarProcesadasColor = false;  

                            $incluidosBN = 0; $incluidosColor = 0; $excedentesBN = 0; $excedentesColor = 0;
                            $particular = false; $idServicio = 0; $procesadosSeparado = false;
                            $todo_cerrado = false;//Esta variable se ocupa para arregla un bug, en ocaciones en el ultimo servicio, se cierra dos veces

                            /** Estas variables son para mandar al php de generar la factura **/
                            $rfc = ""; $rfcFacturacion = "";                                                            
                            $variable_rs_cambio = ""; //Cada que cambie esta variable, se creara una nueva hoja (factura)
                            $variable_cambio_anterior = 0; $color_anterior = "";
                ?>            
                <?php
                    while($rs = mysql_fetch_array($result)){
                        $rfc = $rs['RFC']; $rfcFacturacion = $rs['RFCFacturacion']; $formaPago = $rs['FormaPago']; $NoContrato = $rs['NoContrato'];                        
                        $reporte = new ReporteLectura();                        
                        if(($variable_cambio_anterior!="0" && $variable_cambio_anterior!=$rs[$variable_rs_cambio]) 
                                /*|| ($agrupar_color && $color_anterior!="" && $color_anterior!=$rs['isColor'])*/){//Cada nuevo servicio es una hoja nueva o si se pide separara por color
                            if(!$nueva_hoja){/*Cerramos la ultima hoja abierta*/
                                /*Si se quiere imprimir la fila de informacion detallada por servicio*/                                                        
                                if($mostrarDetalleServicio){                                    
                                    $NumeroConcepto = $reporte->imprimirDetalleServicio($IdKServicioAnterior, $idServicioAnterior, $prefijoAnterior, $impresionesBNPorServicio, $impresionesColorPorServicio,
                                        $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProBN, $costoProColor, 
                                        $particularServicio, $iva, $excedenteBNServicio, $excedenteColorServicio, $equiposPorServicio, 
                                        $NumeroFacturas, $NumeroConcepto, $unidad_servicio, $prefijos, $incluidosBNServicio, $incluidosColorServicio, 
                                        $mostrarRenta, $encabezado_variable, $incluidosColor, $incluidosBN, $imprimir_cero, $dividir_lecturas, 
                                        $MostrarSeries, $seriesServicio, $MostrarModelo, $agrupar_color, $NumeroConceptosColor);
                                    if($IdKServicioAnterior != $rs['IdKServicio'.$prefijos[$prefijo]] && $IdKServicioAnterior != 0){                                                                                                                        
                                        $impresionesBNPorServicio = 0; $impresionesColorPorServicio = 0; $equiposPorServicio = 0;                                        
                                    }                                    
                                }else if($mostrarDetalleLocalidad || $mostrarDetalleCC || $mostrarDetalleZona){                                    
                                    $NumeroConcepto = $reporte->imprimirDetalle($nombreAgrupaciones, $esParticularAgrupacion, $idServicioByKServicio, $incluidosBNAgrupacion, 
                                        $incluidosColorAgrupacion, $equiposPorAgrupacion, $impresionesBNAgrupacion, $impresionesColorAgrupacion, $excedenteBNAgrupacion, 
                                        $excedenteColorAgrupacion, $rentaAgrupacion, $costoExcedentesBNAgrupacion, $costoExcedentesColorAgrupacion, $costoProcesadasBNAgrupacion, 
                                        $costoProcesadasColorAgrupacion, $iva, $mostrarRenta, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $imprimir_cero, 
                                        $dividir_lecturas, $MostrarSeries, $seriesPorAgrupacion, $MostrarModelo, $agrupar_color, $NumeroConceptosColor);
                                }
                                
                                /*Guardamos el concepto de la renta*/                                
                                $particularAnterior = $reporte->isParticularByPrefijo($reporte->obtenerIdPrefijo($prefijoAnterior, $prefijos));                                                               
                                if(!$mostrarDetalleServicio && !$mostrarDetalleLocalidad && !$mostrarDetalleCC && !$mostrarDetalleZona){
                                    if(!$particularAnterior){                                        
                                        $um = $reporte->getUnidadMedida($idServicioAnterior, "Renta", $unidad_servicio);
                                        if($dividir_lecturas){
                                            $auxFactura = $NumeroFacturas+1;
                                            $auxConcepto = $NumeroRentaPorServicio++;                                    
                                        }else{
                                            $auxFactura = $NumeroFacturas;
                                            $auxConcepto = $NumeroConcepto++;
                                        }                                        
                                    }
                                }
                                $descripcion_renta = "";                                
                                if(!empty($conceptos_adicionales)){
                                    $NumeroConcepto = $reporte->crearConceptosAdicionales($conceptos_adicionales, $cliente_por_factura, $anexos_por_factura, 
                                            $zonas_por_factura, $cc_por_factura, $localidad_por_factura, $iva, $NumeroFacturas, $NumeroConcepto, $procesadosSeparado, $dividir_lecturas);
                                    $procesadosSeparado = true;                                                                        
                                    $cliente_por_factura = $reporte->ponerValorUno($cliente_por_factura); $anexos_por_factura = $reporte->ponerValorUno($anexos_por_factura); 
                                    $zonas_por_factura = $reporte->ponerValorUno($zonas_por_factura); $cc_por_factura = $reporte->ponerValorUno($cc_por_factura); 
                                    $localidad_por_factura = $reporte->ponerValorUno($localidad_por_factura);
                                }
                                $NumeroConcepto = $reporte->imprimirTablaTotalAgrupacion($costoTotalPorGrupo + $reporte->getTotalConceptosAdicionales(), 
                                    $contadorBNServicio, $contadorColorServicio, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, 
                                    $costoProBN, $costoProColor, $particularServicio, $excedenteBNServicio, $excedenteColorServicio, $iva, 
                                    $idServicioByKServicio, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $mostrarDetalleServicio, 
                                    $incluidosBNServicio, $incluidosColorServicio, $MostrarEquipos, $imprimir_cero, $agrupar_color, $NumeroConceptosColor);                                                                                                
                                if($reporte->getHayConceptosSeparados()){
                                    $NumeroFacturas++;
                                }
                                if($dividir_lecturas && $MostrarEquipos){//Si se dividio la factura, tenemos que guardar el numero de componentes de la factura de renta
                                    
                                }else if($agrupar_color && $MostrarEquipos){
                                    $NumeroConceptosColor = $reporte->getNumeroConceptosColor();                                    
                                }
                                echo "</table><br/>";
                                $todo_cerrado = true;                                
                                echo "<br/><br/>";
                                /*Reiniciamos las variables necesarias para una nueva factura*/
                                $costoTotalPorGrupo = 0;
                                $contadorBNServicio = array(); $contadorColorServicio = array();
                                $costoRentaServicio = array(); $costoExcedentesBN = array(); $costoExcedentesColor = array(); $costoProBN = array(); $costoProColor = array();
                                $particularServicio = array(); $excedenteBNServicio = array(); $excedenteColorServicio = array();      
                                $incluidosBNServicio = array(); $incluidosColorServicio = array(); $seriesServicio = array();
                                $impresionesBNPorServicio = 0; $impresionesColorPorServicio = 0; $equiposPorServicio=0;
                                ?>
                                <div style="page-break-after: always;"></div>
                                <?php
                            }
                            $no_equipo = 1;                            
                            $nueva_hoja = true;         

                            $incluidosBN = 0;
                            $incluidosColor = 0;
                            $excedentesBN = 0;
                            $excedentesColor = 0;                                                      
                        }                                                
                        
                        while(!isset($rs[$variable_rs_cambio])){//Cuando se termina un tipo de servicio e inicia otro
                            if(!$nueva_hoja){/*Cerramos la ultima hoja abierta*/
                                /*Si se quiere imprimir la fila de informacion detallada por servicio*/                                                                                                       
                                if($IdKServicioAnterior != $rs['IdKServicio'.$prefijos[$prefijo]]){                                        
                                    if($mostrarDetalleServicio){                                        
                                        $NumeroConcepto = $reporte->imprimirDetalleServicio($IdKServicioAnterior, $idServicioAnterior, $prefijoAnterior, $impresionesBNPorServicio, $impresionesColorPorServicio,
                                        $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProBN, $costoProColor, 
                                        $particularServicio, $iva, $excedenteBNServicio, $excedenteColorServicio, $equiposPorServicio,
                                        $NumeroFacturas, $NumeroConcepto, $unidad_servicio, $prefijos, $incluidosBNServicio, $incluidosColorServicio, 
                                        $mostrarRenta, $encabezado_variable, $incluidosColor, $incluidosBN, $imprimir_cero, $dividir_lecturas, 
                                        $MostrarSeries, $seriesServicio, $MostrarModelo, $agrupar_color, $NumeroConceptosColor);
                                        if($IdKServicioAnterior != 0){                                            
                                            $impresionesBNPorServicio = 0; $impresionesColorPorServicio = 0; $equiposPorServicio = 0;
                                        }
                                    }else if($mostrarDetalleLocalidad || $mostrarDetalleCC || $mostrarDetalleZona){                                        
                                        $NumeroConcepto = $reporte->imprimirDetalle($nombreAgrupaciones, $esParticularAgrupacion, $idServicioByKServicio, $incluidosBNAgrupacion, 
                                            $incluidosColorAgrupacion, $equiposPorAgrupacion, $impresionesBNAgrupacion, $impresionesColorAgrupacion, $excedenteBNAgrupacion, 
                                            $excedenteColorAgrupacion, $rentaAgrupacion, $costoExcedentesBNAgrupacion, $costoExcedentesColorAgrupacion, $costoProcesadasBNAgrupacion, 
                                            $costoProcesadasColorAgrupacion, $iva, $mostrarRenta, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $imprimir_cero, 
                                            $dividir_lecturas, $MostrarSeries, $seriesPorAgrupacion, $MostrarModelo, $agrupar_color, $NumeroConceptosColor);
                                    }
                                }                                    
                                
                                /*Guardamos el concepto de la renta*/                                
                                $particularAnterior = $reporte->isParticularByPrefijo($reporte->obtenerIdPrefijo($prefijoAnterior, $prefijos));                                
                                if(!$mostrarDetalleServicio && !$mostrarDetalleLocalidad && !$mostrarDetalleCC && !$mostrarDetalleZona){
                                    if(!$particularAnterior){                                        
                                        $um = $reporte->getUnidadMedida($idServicioAnterior, "Renta", $unidad_servicio);
                                        if($dividir_lecturas){
                                            $auxFactura = $NumeroFacturas+1;
                                            $auxConcepto = $NumeroRentaPorServicio++;                                    
                                        }else{
                                            $auxFactura = $NumeroFacturas;
                                            $auxConcepto = $NumeroConcepto++;
                                        }                                                                                
                                    }
                                }
                                $descripcion_renta = "";
                                if(!empty($conceptos_adicionales)){
                                    $NumeroConcepto = $reporte->crearConceptosAdicionales($conceptos_adicionales, $cliente_por_factura, $anexos_por_factura, 
                                            $zonas_por_factura, $cc_por_factura, $localidad_por_factura, $iva, $NumeroFacturas, $NumeroConcepto, $procesadosSeparado, $dividir_lecturas);
                                    $procesadosSeparado = true;                                                                        
                                    $cliente_por_factura = $reporte->ponerValorUno($cliente_por_factura); $anexos_por_factura = $reporte->ponerValorUno($anexos_por_factura); 
                                    $zonas_por_factura = $reporte->ponerValorUno($zonas_por_factura); $cc_por_factura = $reporte->ponerValorUno($cc_por_factura); 
                                    $localidad_por_factura = $reporte->ponerValorUno($localidad_por_factura);
                                }
                                $NumeroConcepto = $reporte->imprimirTablaTotalAgrupacion($costoTotalPorGrupo + $reporte->getTotalConceptosAdicionales(), 
                                    $contadorBNServicio, $contadorColorServicio, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, 
                                    $costoProBN, $costoProColor, $particularServicio, $excedenteBNServicio, $excedenteColorServicio, $iva, 
                                    $idServicioByKServicio, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $mostrarDetalleServicio, 
                                        $incluidosBNServicio, $incluidosColorServicio, $MostrarEquipos, $imprimir_cero, $agrupar_color, $NumeroConceptosColor);                                                                
                                if($reporte->getHayConceptosSeparados()){
                                    $NumeroFacturas++;
                                }
                                if($dividir_lecturas && $MostrarEquipos){//Si se dividio la factura, tenemos que guardar el numero de componentes de la factura de renta
                                    
                                }else if($agrupar_color && $MostrarEquipos){
                                    $NumeroConceptosColor = $reporte->getNumeroConceptosColor();                                    
                                }
                                
                                echo "</table><br/>";  
                                
                                $costoTotal = 0;
                                echo "<br/><br/>";                                
                                $costoTotalPorGrupo = 0;
                                $contadorBNServicio = array(); $contadorColorServicio = array();
                                $costoRentaServicio = array(); $costoExcedentesBN = array(); $costoExcedentesColor = array(); $costoProBN = array(); $costoProColor = array();
                                $particularServicio = array(); $excedenteBNServicio = array(); $excedenteColorServicio = array();   
                                $incluidosBNServicio = array(); $incluidosColorServicio = array(); $seriesServicio = array();
                                ?>
                                <div style="page-break-after: always;"></div>
                                <?php                                
                            }                            
                            $prefijo++;
                            $no_equipo = 1;
                            $nueva_hoja = true;//Si se quieren varias hojas, aqui tendria que ser true
                            if($prefijo >= count($prefijos)){     
                                $prefijo = 0;
                                break 1;
                            }    
                            
                            //Vemos como se va a agrupar los datos segun los parametros
                            if($agrupar_servicio){
                                $variable_rs_cambio = 'IdKServicio'.$prefijos[$prefijo];//Cada que cambie esta variable, se creara una hoja                                
                            }else if($agrupar_tipo_servicio){
                                $variable_rs_cambio = 'IdServicio'.$prefijos[$prefijo];//Cada que cambie esta variable, se creara una hoja                                
                            }/*else if($agrupar_color){
                                $variable_rs_cambio = "isColor";
                            }*/else if($agrupar_localidad){
                                $variable_rs_cambio = "ClaveCentroCosto";//Cada que cambie esta variable, se creara una hoja                                
                            }else if($agrupar_cc){
                                $variable_rs_cambio = 'idCen_Costo';//Cada que cambie esta variable, se creara una hoja                                
                            }else if($agrupar_zona){
                                $variable_rs_cambio = "ClaveZona";
                            }else if($agrupar_todo){
                                $variable_rs_cambio = "Junto";
                            }else{
                                $variable_rs_cambio = "NoSerie";
                            }
                            
                            $IdKServicioAnterior = "0";
                            $idServicioAnterior = "0";                                                        
                        }
                        
                        /*Obtenemos el prefijo actual*/
                        for($i=0;$i<count($prefijos);$i++){
                            if(isset($rs['IdKServicio'.$prefijos[$i]])){
                                $prefijo = $i;
                                break;
                            }else{
                                $prefijo = 0;
                            }
                        }                                                                        
                        $particular = $reporte->isParticularByPrefijo($prefijo);                        
                        $idServicio = $rs['IdServicio'.$prefijos[$prefijo]];                        
                        //Dependiendo del servicio, es lo que se va a cobrar
                        $servicio_general = new ServicioGeneral();
                        if($servicio_general->getCobranzasByTipoServicio($idServicio)){
                            $cobrarRenta = $servicio_general->getCobrarRenta();
                            $cobrarExcedenteBN = $servicio_general->getCobrarExcedenteBN();
                            $cobrarExcedenteColor = $servicio_general->getCobrarExcedenteColor();
                            $cobrarProcesadasBN = $servicio_general->getCobrarProcesadasBN();
                            $cobrarProcesadasColor = $servicio_general->getCobrarProcesadasColor();
                        }else{
                            $cobrarRenta = false;
                            $cobrarExcedenteBN = false;
                            $cobrarExcedenteColor = false;
                            $cobrarProcesadasBN = false;
                            $cobrarProcesadasColor = false;
                        }
                        
                        /*Obtenemos los costos por servicio si aun no estan registrados*/
                        if(!isset($particularServicio[$rs['IdKServicio'.$prefijos[$prefijo]]])){
                            $idServicioByKServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = $idServicio;                            
                            if($particular){
                                $particularServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = 1;
                            }else{
                                $particularServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = 0;
                            }
                            $seriesServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = "";
                            if($cobrarRenta){                                
                                $costoRentaServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = $rs[$prefijos[$prefijo].'Renta'];
                            }                            
                            if($cobrarExcedenteBN){
                                $costoExcedentesBN[$rs['IdKServicio'.$prefijos[$prefijo]]] = $rs[$prefijos[$prefijo].'ExcedentesBN'];
                            }
                            if($cobrarExcedenteColor){
                                $costoExcedentesColor[$rs['IdKServicio'.$prefijos[$prefijo]]] = $rs[$prefijos[$prefijo].'ExcedentesColor'];
                            }
                            if($cobrarProcesadasBN){
                                $costoProBN[$rs['IdKServicio'.$prefijos[$prefijo]]] = $rs[$prefijos[$prefijo].'ProcesadasBN'];                            
                            }
                            if($cobrarProcesadasColor){
                                $costoProColor[$rs['IdKServicio'.$prefijos[$prefijo]]] = $rs[$prefijos[$prefijo].'ProcesadosColor'];
                            }  
                            $incluidosBNServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = $rs[$prefijos[$prefijo].'incluidosBN'];
                            $incluidosColorServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = $rs[$prefijos[$prefijo].'incluidosColor'];
                        }
                                                
                        $mes_actual = $meses[$month-1];
                        $hay_lectura = false;
                        $hay_lectura_anterior = false;
                        /*Datos del equipo*/
                        //Lecturas del mes actual
                        if($reporte->getLecturaMesActualCorte($rs['NoSerie'], $month, $year)){
                            $hay_lectura = true;
                            /*if($reporte->getLecturaMesActual($rs['NoSerie'],$month,$year,$rs['ClaveCliente'])==null){
                                $hay_lectura = false;
                            }*/
                        }
                        
                        //Lecturas del mes anterior, para poder sacara la diferencia de impresiones
                        $month_aux = $month;
                        $year_aux = $year;
                        if($month != "1"){
                            $month_aux--;
                        }else{
                            $month_aux = "12";
                            $year_aux--;
                        }
                        
                        $mes_anterior = $meses[$month_aux-1];                        
                        if($reporte->getLecturaMesAnteriorCorte($rs['NoSerie'], $month_aux, $year_aux)){
                            $hay_lectura_anterior = true;
                            /*$tipo = $reporte->getLecturasSinMarcaMesAnterior($rs['NoSerie'],$month,$year,$rs['ClaveCliente']);
                            if(isset($tipo) && $tipo == "0" || $tipo == "1"){
                                $hay_lectura_anterior = true;
                            }      */                  
                        }
                        
                        //Saber si el equipo es de color o b/n
                        $color = $caracteristicas->isColor($rs['NoParteEquipo']);
                        //Saber si el equipo es formato amplio o no                        
                        $fa = $caracteristicas->isFormatoAmplio($rs['NoParteEquipo']);

                        if($nueva_hoja){ /*Iniciamos nueva hoja (factura)*/                            
                            $todo_cerrado = false;
                            //Reiniciamos las variables.                                                                               
                            $incluidosBN = 0; $incluidosColor = 0; $excedentesBN = 0; $excedentesColor = 0; $procesadosBN = 0; $procesadosColor = 0;                                                                                          
                            $excedentes_bn_por_equipo = array(); $excedentes_color_por_equipo = array();                                                                                                                
                            /***************************     CREAMOS LOS ENCABEZADOS    ***************************/                                                        
                            $NumeroFacturas++; 
                            if($dividir_lecturas && $variable_cambio_anterior!=0){//Si se dividen las facturas, se aumenta otra factura por que se crearon dos.
                                $NumeroFacturas++;
                            }else if($agrupar_color && $variable_cambio_anterior!=0){
                                $NumeroFacturas++;
                            }
                            $NumeroConcepto = 0; $NumeroRentaPorServicio = 0; $NumeroConceptosColor = 0; //Aumentamos el identificador de la factura a crear, y reiniciamos el contador de conceptos por factura
                            $localidadAnterior = ""; $NombreLocalidadAnterior = ""; $equiposPorServicio = 0;
                            $nombreAgrupaciones = array(); $rentaAgrupacion = array(); $esParticularAgrupacion = array(); $impresionesBNAgrupacion = array(); $impresionesColorAgrupacion = array();
                            $costoExcedentesBNAgrupacion = array(); $costoExcedentesColorAgrupacion = array(); $incluidosBNAgrupacion = array(); $incluidosColorAgrupacion = array();
                            $costoProcesadasBNAgrupacion = array(); $costoProcesadasColorAgrupacion = array(); $equiposPorAgrupacion = array();$excedenteBNAgrupacion = array(); $excedenteColorAgrupacion = array();
                            $seriesPorAgrupacion = array();
                            echo "<table style='min-width: 95%;'><tr><td>";
                            echo "<table>";
                            echo "<tr>"; 
                            if(isset($rs['ImagenPHP']) && $rs['ImagenPHP']!=""){
                                //echo "<td colspan='2'><img src='../".$rs['ImagenPHP']."'/><td>"; 
                                echo "<td colspan='2'>"; echo "</td>";
                                //imagedestroy("../".$rs['ImagenPHP']);
                            }
                            echo "</tr>";
                            echo "<tr><td>CLIENTE: </td><td><b>".$obj_cliente->getNombreRazonSocial()."</b></td></tr>";
                            if($value == 1){//Si se está procesando
                                $obj_direccion->getLocalidadByClaveTipo($rs['ClaveCentroCosto'], "5");                                
                            }
                            echo "<tr><td colspan='2'>DIRECCION: ".$obj_direccion->getCalle()." No. Ext: ".$obj_direccion->getNoExterior()." 
                                No. Int: ".$obj_direccion->getNoInterior()."<br/>".$obj_direccion->getCodigoPostal()." ".
                                    $obj_direccion->getCiudad()." Delegación ".$obj_direccion->getDelegacion()."</td></tr>";
                            echo "<tr><td style='vertical-align:top;'>CONTACTO: </td><td>".$obj_contacto->getNombre()."<br/>Tel. ".$obj_contacto->getTelefono()."<br/>".$obj_contacto->getCorreoElectronico()."</td></tr>";
                            echo "</table></td>";
                            echo "<td><table>";
                            //echo "<tr><td>ZONA CLIENTE: <b>".$obj_zona->getNombre()."</b></td></tr>";
                            
                            if(isset($rs[$prefijos[$prefijo].'incluidosBN']) && $rs[$prefijos[$prefijo].'incluidosBN']!=""){
                                $incluidosBN = $rs[$prefijos[$prefijo].'incluidosBN'];
                            }else{
                                $incluidosBN = 0;
                            }
                            
                            //echo "<tr><td>Incluye ".number_format($incluidosBN, 0, '.', ',')." ".$prefijo_pagml[$prefijo]." BN</td></tr>";
                            if(isset($rs[$prefijos[$prefijo].'incluidosColor']) && $rs[$prefijos[$prefijo].'incluidosColor']!=""){
                                $incluidosColor = $rs[$prefijos[$prefijo].'incluidosColor'];
                            }else{
                                $incluidosColor = 0;
                            }
                            
                            //echo "<tr><td>Incluye ".number_format($incluidosColor, 0, '.', ',')." ".$prefijo_pagml[$prefijo]." de color</td></tr>";
                            if(isset($rs[$prefijos[$prefijo].'ExcedentesBN']) && $rs[$prefijos[$prefijo].'ExcedentesBN']!=""){
                                $excedentesBN = $rs[$prefijos[$prefijo].'ExcedentesBN'];
                            }else{
                                $excedentesBN = 0;
                            }                            
                            if($cobrarExcedenteBN){
                                //echo "<tr><td>Costo por ".$prefijo_pagml[$prefijo]." BN excedente ".$excedentesBN."</td></tr>";
                            }
                            
                            if(isset($rs[$prefijos[$prefijo].'ExcedentesColor']) && $rs[$prefijos[$prefijo].'ExcedentesColor']!=""){
                                $excedentesColor = $rs[$prefijos[$prefijo].'ExcedentesColor'];
                            }else{
                                $excedentesColor = 0;
                            }
                            
                            if($cobrarExcedenteColor){
                                //echo "<tr><td>Costo por ".$prefijo_pagml[$prefijo]." Color excedente ".$excedentesColor."</td></tr>";
                            }
                            if($cobrarProcesadasBN){
                                if(isset($rs[$prefijos[$prefijo].'ProcesadasBN']) && $rs[$prefijos[$prefijo].'ProcesadasBN']!=""){
                                    $procesadosBN = $rs[$prefijos[$prefijo].'ProcesadasBN'];
                                }else{
                                    $procesadosBN = 0;
                                }
                                //echo "<tr><td>Costo por ".$prefijo_pagml[$prefijo]." BN procesados ".$procesadosBN."</td></tr>";
                            }
                            
                            if($cobrarProcesadasColor){
                                if(isset($rs[$prefijos[$prefijo].'ProcesadosColor']) && $rs[$prefijos[$prefijo].'ProcesadosColor']!=""){
                                    $procesadosColor = $rs[$prefijos[$prefijo].'ProcesadosColor'];
                                }else{
                                    $procesadosColor = 0;
                                }
                                //echo "<tr><td>Costo por ".$prefijo_pagml[$prefijo]." Color procesados ".$procesadosColor."</td></tr>";
                            }                            
                                                      
                            if($cobrarRenta){
                                //echo "<tr><td><b>Renta mensual: ".number_format($rs[$prefijos[$prefijo]."Renta"], 2, '.', ',')."</b></td></tr>";                            
                            }
                            
                            if(!isset($encabezado_variable[$idServicio])){
                                $encabezado_servicio = $rs["Nombre".$prefijos[$prefijo]];
                            }else{
                                $encabezado_servicio = str_replace("__0", "", $encabezado_variable[$idServicio]);
                            }
                         
                            echo "</table></td>";
                            echo "</tr></table>";
                            echo "<br/><br/>";
                            
                            echo "<table class='borde' style='width:100%;'>";
                            echo "<tr><td class='borde'>No.</td><td class='borde'>Localidad</td><td class='borde'>Modelo</td><td class='borde'>No. Serie</td>";
                            if($mostrarContadores){
                                echo "<td class='borde'>$resaltado_i B&N [$mes_anterior]$resaltado_f</td><td class='borde'>$resaltado_i Color [$mes_anterior]$resaltado_f</td>".
                                    "<td class='borde'>$resaltado_i B&N [$mes_actual]$resaltado_f</td><td class='borde'>$resaltado_i Color [$mes_actual]$resaltado_f</td>".
                                    "<td class='borde'>Impresiones B&N</td><td class='borde'>Impresiones Color</td>";                            
                                echo "<td class='borde' class='excedente'>Excedentes B&N</td><td class='borde' class='excedente'>Excedentes Color</td>";
                            }
                            echo "<td class='borde'>Subtotal</td><td class='borde'>IVA</td><td class='borde'>Total</td>";
                            
                            if($MostrarUbicacion){
                                echo "<td class='borde'>Ubicación</td>";
                            }
                            if($MostrarDireccion){
                                echo "<td class='borde'>Dirección</td>";
                            }                            
                            echo "</tr>";                                                        
                                  
                            $IdKServicioAnterior = 0; $idServicioAnterior = 0;
                        }else{  //Sino es nuva factura el equipo procesado actual                                                      
                            if(isset($rs[$prefijos[$prefijo].'incluidosBN']) && $rs[$prefijos[$prefijo].'incluidosBN']!=""){
                                $incluidosBN = $rs[$prefijos[$prefijo].'incluidosBN'];
                            }else{
                                $incluidosBN = 0;
                            }                                                        
                            
                            if(isset($rs[$prefijos[$prefijo].'incluidosColor']) && $rs[$prefijos[$prefijo].'incluidosColor']!=""){
                                $incluidosColor = $rs[$prefijos[$prefijo].'incluidosColor'];
                            }else{
                                $incluidosColor = 0;
                            }                            
                            
                            if(isset($rs[$prefijos[$prefijo].'ExcedentesBN']) && $rs[$prefijos[$prefijo].'ExcedentesBN']!=""){
                                $excedentesBN = $rs[$prefijos[$prefijo].'ExcedentesBN'];
                            }else{
                                $excedentesBN = 0;
                            }                            
                                                        
                            if(isset($rs[$prefijos[$prefijo].'ExcedentesColor']) && $rs[$prefijos[$prefijo].'ExcedentesColor']!=""){
                                $excedentesColor = $rs[$prefijos[$prefijo].'ExcedentesColor'];
                            }else{
                                $excedentesColor = 0;
                            }                            
                            
                            if($cobrarProcesadasBN){
                                if(isset($rs[$prefijos[$prefijo].'ProcesadasBN']) && $rs[$prefijos[$prefijo].'ProcesadasBN']!=""){
                                    $procesadosBN = $rs[$prefijos[$prefijo].'ProcesadasBN'];
                                }else{
                                    $procesadosBN = 0;
                                }                                
                            }
                            
                            if($cobrarProcesadasColor){
                                if(isset($rs[$prefijos[$prefijo].'ProcesadosColor']) && $rs[$prefijos[$prefijo].'ProcesadosColor']!=""){
                                    $procesadosColor = $rs[$prefijos[$prefijo].'ProcesadosColor'];
                                }else{
                                    $procesadosColor = 0;
                                }                                
                            }                                              
                            
                            if(!isset($encabezado_variable[$idServicio])){
                                $encabezado_servicio = $rs["Nombre".$prefijos[$prefijo]];
                            }else{
                                $encabezado_servicio = str_replace("__0", "", $encabezado_variable[$idServicio]);
                            }
                           
                        }

                        $contadorBN = 0;
                        $contadorColor = 0;
                        $contadorBNAnterior = 0;
                        $contadorColorAnterior = 0;

                        if(!$color){/*Si el equipo es blanco y negro*/
                            if(!$fa){/*Si el equipo no es de formato amplio (es decir que es impresora)*/
                                $contadorBN = $reporte->getContadorBNPagina(); 
                                $contadorBNAnterior = $reporte->getContadorBNPaginaAnterior(); 
                            }else{
                                $contadorBN = $reporte->getContadorBNML();
                                $contadorBNAnterior = $reporte->getContadorBNMLAnterior();
                            }
                        }else{/*Si el equipo es color*/
                            if(!$fa){/*Si el equipo no es de formato amplio (es decir que es impresora)*/
                                $contadorBN = $reporte->getContadorBNPagina();
                                $contadorColor = $reporte->getContadorColorPagina();
                                $contadorBNAnterior = $reporte->getContadorBNPaginaAnterior();
                                $contadorColorAnterior = $reporte->getContadorColorPaginaAnterior();
                            }else{
                                $contadorBN = $reporte->getContadorBNML();
                                $contadorColor = $reporte->getContadorColorML();
                                $contadorBNAnterior = $reporte->getContadorBNMLAnterior();
                                $contadorColorAnterior = $reporte->getContadorColorMLAnterior();
                            }
                        }
                        /*************************************        Aqui se procesa la fila de cada tabla       *************************************/                        
                        $resultMovimiento = $reporte->obtenerMovimientoPorFecha($rs['ClaveCliente'], $rs['NoSerie'], $month, $year);
                        if(mysql_num_rows($resultMovimiento) > 0){//Si el equipo tuvo movimiento durante el mes de facturacion
                            //Agregar algoritmo para calcular las impresiones
                            $cantidadInicial = $cantidadInicialColor = 0;
                            $cantidadFinal = $cantidadFinalColor = 0;
                            $quitar = $quitarColor = 0;
                            $ult_reg = $ult_regColor = 0;
                            $sin_regresar = true;
                            
                            $ult_reg = $cantidadInicial = $contadorBNAnterior;
                            $ult_regColor = $cantidadInicialColor = $contadorColorAnterior;
                            
                            while($rsMovimiento = mysql_fetch_array($resultMovimiento))
                            {
                                if(!$fa)
                                {
                                    $nvo_reg = $rsMovimiento['ContadorBNPaginas'];
                                    $nvo_regColor = $rsMovimiento['ContadorColorPaginas'];
                                }else{
                                    $nvo_reg = $rsMovimiento['ContadorBNML'];
                                    $nvo_regColor = $rsMovimiento['ContadorColorML'];
                                }
                                if($rsMovimiento['clave_cliente_nuevo']){       //En el caso en que llego el equipo
                                    if($nvo_reg - $ult_reg > 0 && $nvo_regColor - $ult_regColor >= 0  && $sin_regresar){//Para evitar lecturas incoherentes
                                        $quitar += $nvo_reg - $ult_reg;             //Se descuentan las impresiones desde la última lectura
                                        $quitarColor += $nvo_regColor - $ult_regColor;
                                        $ult_reg = $nvo_reg;
                                        $ult_regColor = $nvo_regColor;
                                    }
                                    $sin_regresar = false;                     //Indicamos que ya regreso el equipo
                                }else{                                          //En el caso de que el equipo salga
                                    if($nvo_reg >= $ult_reg){
                                        $ult_reg = $nvo_reg;                        
                                        $ult_regColor = $nvo_regColor;
                                    }
                                    $sin_regresar = true;                       //El equipo ha salido
                                }
                            }
                            
                            $cantidadFinal = $contadorBN;
                            $cantidadFinalColor = $contadorColor;
                            $diferencia_bn = $diferencia_color = 0;
                            //Obtenemos la diferencia total de los dos cortes para después descontar cuando estuvo inactiva
                            if($sin_regresar)
                            {
                                $diferencia_bn = $ult_reg - $cantidadInicial;//No se toma en cuenta la ultima lectura puesto que no ha regresado el equipo
                                $diferencia_color = $ult_regColor - $cantidadInicialColor;
                            }
                            else
                            {
                                $diferencia_bn = $cantidadFinal - $cantidadInicial;
                                $diferencia_color = $cantidadFinalColor - $cantidadInicialColor;
                            }
                                
                            $diferencia_bn -= $quitar;
                            $diferencia_color -= $quitarColor;
                        }else{
                            $diferencia_bn = (intval($contadorBN) - intval($contadorBNAnterior));
                            $diferencia_color = (intval($contadorColor) - intval($contadorColorAnterior));                        
                        }
                        if($diferencia_bn < 0){ $diferencia_bn = 0; }                        
                        if($diferencia_color < 0){ $diferencia_color = 0;}
                        $des_aux = "";
                        if($MostrarSeries){
                            $des_aux .= "SERIE: ".$rs['NoSerie'];
                        }
                        if($MostrarModelo){
                            $des_aux .= " MODELO:".$rs['Modelo'];
                        }
                        if($des_aux != ""){
                            $des_aux .= ", ";
                        }
                        $seriesServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] .= ($des_aux);                        
                        
                        /*Si se quiere imprimir la fila de informacion detallada por servicio*/                                                
                        if($IdKServicioAnterior != 0 && $IdKServicioAnterior != $rs['IdKServicio'.$prefijos[$prefijo]]){
                            if($mostrarEncabezadosServicio){
                                $NumeroConcepto = $reporte->imprimirEncabezadoServicio($encabezado_variable, $incluidosBN, $incluidosColor, $costoRentaServicio, 
                                        $costoExcedentesBN, $costoExcedentesColor,$costoProBN, $costoProColor, $rs['IdKServicio'.$prefijos[$prefijo]], 
                                        $rs['IdServicio'.$prefijos[$prefijo]], $agrupar_color, $NumeroFacturas, $NumeroConcepto);
                            }                            
                            if($mostrarDetalleServicio){                                  
                                $NumeroConcepto = $reporte->imprimirDetalleServicio($IdKServicioAnterior, $idServicioAnterior, $prefijoAnterior, $impresionesBNPorServicio, $impresionesColorPorServicio,
                                        $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProBN, $costoProColor, 
                                        $particularServicio, $iva, $excedenteBNServicio, $excedenteColorServicio, $equiposPorServicio, 
                                        $NumeroFacturas, $NumeroConcepto, $unidad_servicio, $prefijos, $incluidosBNServicio, $incluidosColorServicio, 
                                        $mostrarRenta, $encabezado_variable, $incluidosColor, $incluidosBN, $imprimir_cero, $dividir_lecturas, 
                                        $MostrarSeries, $seriesServicio, $MostrarModelo, $agrupar_color, $NumeroConceptosColor);
                            }/*else if($mostrarDetalleLocalidad || $mostrarDetalleCC || $mostrarDetalleZona){                                
                                $NumeroConcepto = $reporte->imprimirDetalle($nombreAgrupaciones, $esParticularAgrupacion, $idServicioByKServicio, $incluidosBNAgrupacion, 
                                        $incluidosColorAgrupacion, $equiposPorAgrupacion, $impresionesBNAgrupacion, $impresionesColorAgrupacion, $excedenteBNAgrupacion, 
                                        $excedenteColorAgrupacion, $rentaAgrupacion, $costoExcedentesBNAgrupacion, $costoExcedentesColorAgrupacion, $costoProcesadasBNAgrupacion, 
                                        $costoProcesadasColorAgrupacion, $iva, $mostrarRenta, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $imprimir_cero);
                            }*/
                            
                            /*Guardamos el concepto de la renta*/                            
                            $particularAnterior = $reporte->isParticularByPrefijo($reporte->obtenerIdPrefijo($prefijoAnterior, $prefijos));                            
                            if(!$mostrarDetalleServicio && !$mostrarDetalleLocalidad && !$mostrarDetalleCC && !$mostrarDetalleZona){
                                if(!$particularAnterior){                                    
                                    $um = $reporte->getUnidadMedida($idServicioAnterior, "Renta", $unidad_servicio);
                                    if($dividir_lecturas){
                                        $auxFactura = $NumeroFacturas+1;
                                        $auxConcepto = $NumeroRentaPorServicio++;                                    
                                    }else{
                                        $auxFactura = $NumeroFacturas;
                                        $auxConcepto = $NumeroConcepto++;
                                    }                                                      
                                }
                            }
                            $descripcion_renta = "   RENTA EQUIPO EN LOCALIDAD $localidadAnterior: ";
                            /*Reiniciamos las variables por servicio*/
                            $impresionesBNPorServicio = 0; $impresionesColorPorServicio = 0; $equiposPorServicio = 0;                              
                        }else if($IdKServicioAnterior != $rs['IdKServicio'.$prefijos[$prefijo]]){//Hay un cambio 
                            if($mostrarEncabezadosServicio){
                                $NumeroConcepto = $reporte->imprimirEncabezadoServicio($encabezado_variable, $incluidosBN, $incluidosColor, $costoRentaServicio, $costoExcedentesBN, 
                                        $costoExcedentesColor,$costoProBN, $costoProColor, $rs['IdKServicio'.$prefijos[$prefijo]], 
                                        $rs['IdServicio'.$prefijos[$prefijo]], $agrupar_color, $NumeroFacturas, $NumeroConcepto);
                            }                 
                        }
                        if($diferencia_bn > 0){
                            $impresionesBNPorServicio += $diferencia_bn;
                        }
                        if($diferencia_color > 0){
                            $impresionesColorPorServicio +=  $diferencia_color;
                        }
                        $equiposPorServicio++;                        
                        
                        /*guardamos todos los clientes, anexos, zonas, centro de costos y localidad de la factura, para los conceptos adicionales*/
                        if(!isset($cliente_por_factura[$rs['ClaveCliente']])){
                            $cliente_por_factura[$rs['ClaveCliente']] = 0;
                        }
                        
                        if(!isset($anexos_por_factura[$rs['ClaveAnexoTecnico']])){
                            $anexos_por_factura[$rs['ClaveAnexoTecnico']] = 0;
                        }
                        
                        if(!isset($zonas_por_factura[$rs['ClaveZona']]) && $rs['ClaveZona']!=""){                            
                            $zonas_por_factura[$rs['ClaveZona']] = 0;                            
                        }
                        
                        if(!isset($cc_por_factura[$rs['idCen_Costo']]) && $rs['idCen_Costo']!=""){                            
                            $cc_por_factura[$rs['idCen_Costo']] = 0;                                                        
                        }
                        
                        if(!isset($localidad_por_factura[$rs['ClaveCentroCosto']])){
                            $localidad_por_factura[$rs['ClaveCentroCosto']] = 0;
                        }                        

                        /*Descripcion por concepto de renta*/
                        if($rs['ClaveCentroCosto'] != $localidadAnterior){
                            $descripcion_renta .= "  RENTA EQUIPO EN LOCALIDAD ".$rs['CentroCostoNombre'].": ";
                        }                      
                        if($MostrarModelo){
                            $descripcion_renta.= ("  MODELO: ".$rs['Modelo']);
                        }if($MostrarSeries){
                            $descripcion_renta .= (" SERIE ".$rs['NoSerie']);
                        }
                                                
                        if($MostrarEquipos){
                            $sin_servicio = "";
                            if(!isset($rs['IdKServicio'.$prefijos[$prefijo]]) || $rs['IdKServicio'.$prefijos[$prefijo]] == ""){
                                $sin_servicio = "<span style='color:red'> Sin servicio asignado</span>";
                            }
                            echo "<tr>";
                            echo "<td class='borde'>$no_equipo</td>";
                            if(isset($rs['CentroCostoLocalidad'])){
                                echo "<td class='borde'>".$rs['CentroCostoLocalidad']." - ".$rs['CentroCostoNombre']."</td>";
                            }else{
                                echo "<td class='borde'>".$rs['CentroCostoNombre']."</td>";
                            }
                            echo "<td class='borde'>".$rs['Modelo']."</td>";
                            echo "<td class='borde'>".$rs['NoSerie']."$sin_servicio</td>";                                                
                            
                            $reporte->imprimirContadores($contadorBNAnterior, $contadorColorAnterior, $contadorBN, $contadorColor);                            
                            $reporte->imprimirExcedentesPorFila($diferencia_bn, $diferencia_color);
                        }
                        
                        $aux = 0;//Son los excedentes por equipo bn
                        $aux1 = 0;//Excedentes por equipo color
                        if($particular){//Para los equipos en servicio particular, se calcula por fila los costos
                            $totalParticular = $reporte->calcularCostoParticularPorEquipo($cobrarRenta, $rs[$prefijos[$prefijo]."Renta"], $incluidosBN, $cobrarExcedenteBN, 
                                    $excedentesBN, $procesadosBN, $diferencia_bn, $diferencia_color, $incluidosColor, $cobrarExcedenteColor, 
                                    $excedentesColor, $procesadosColor);
                            
                            $aux = $diferencia_bn - $incluidosBN;
                            if($aux < 0){
                                $aux = 0;
                            }
                            array_push($excedentes_bn_por_equipo, $aux);                                                        
                            
                            $aux1 = $diferencia_color - $incluidosColor;
                            if($aux1 < 0){
                                $aux1 = 0;
                            }
                            array_push($excedentes_color_por_equipo, $aux1);
                            $costoTotalPorGrupo += $totalParticular;                            
                            $iva_particular = $totalParticular * $iva;
                            $totalParticular += $iva_particular;                                                        
                            
                            if($MostrarEquipos){
                                if($mostrarContadores){
                                    echo "<td class='borde'>".number_format($aux, 0, '.', ',')."</td>";
                                    echo "<td class='borde'>".number_format($aux1, 0, '.', ',')."</td>";                                                                       
                                }
                                echo "<td class='borde' style='text-align:right;'>$".number_format($totalParticular-$iva_particular, 2, '.', ',')."</td>";//Subtotal
                                echo "<td class='borde' style='text-align:right;'>$".number_format($iva_particular, 2, '.', ',')."</td>";//IVA                            
                                echo "<td class='borde' style='text-align:right;'>$".number_format($totalParticular, 2, '.', ',')."</td>";//Total
                                /*Creamos los campos para generar la factura*/
                                if(!$mostrarRenta){//Si solo se muestra una fila por equipo ..
                                    //$um = $reporte->getUnidadMedida($idServicio, "Excedentes", $unidad_servicio);                                    
                                    $NumeroConcepto++;
                                }else{//Si se detalla por equipo
                                    if($particular && $cobrarRenta){
                                        $um = $reporte->getUnidadMedida($idServicio, "Renta", $unidad_servicio);
                                        $des_aux = "RENTA EQUIPO ".$rs['CentroCostoNombre'];
                                        if($MostrarSeries){
                                            $des_aux.= " SERIE ".$rs['NoSerie'];
                                        }
                                        if($MostrarModelo){
                                            $des_aux .= " MODELO ".$rs['Modelo'];
                                        }                                        
                                        $NumeroConcepto++;
                                    }
                                    if($cobrarExcedenteBN){                                    
                                        $um = $reporte->getUnidadMedida($idServicio, "Excedentes", $unidad_servicio);
                                        $des_aux = "PÁGINAS IMPRESAS NEGRO: $diferencia_bn INCLUYE ($incluidosBN)";
                                        if($MostrarSeries){
                                            $des_aux .= " SERIE ".$rs['NoSerie'];
                                        }
                                        if($MostrarModelo){
                                            $des_aux .= " MODELO ".$rs['Modelo'];
                                        }                                        
                                        $NumeroConcepto++;
                                    }
                                    if($cobrarExcedenteColor){
                                        $um = $reporte->getUnidadMedida($idServicio, "Excedentes", $unidad_servicio);
                                        $des_aux = "PÁGINAS IMPRESAS COLOR: $diferencia_color INCLUYE ($incluidosColor)";
                                        if($MostrarSeries){
                                            $des_aux .= " SERIE ".$rs['NoSerie'];
                                        }
                                        if($MostrarModelo){
                                            $des_aux .= " MODELO ".$rs['Modelo'];
                                        }
                                        $auxFactura = $NumeroFacturas;
                                        $auxConcepto = $NumeroConcepto;
                                        if($agrupar_color){
                                            $auxFactura = $NumeroFacturas + 1;
                                            $auxConcepto = $NumeroConceptosColor++;
                                        }                                        
                                        $NumeroConcepto++;
                                    }
                                    if($cobrarProcesadasBN){
                                        $um = $reporte->getUnidadMedida($idServicio, "Impresiones", $unidad_servicio);
                                        $des_aux = "PÁGINAS IMPRESAS NEGRO";
                                        if($MostrarSeries){
                                            $des_aux .= " SERIE ".$rs['NoSerie'];
                                        }
                                        if($MostrarModelo){
                                            $des_aux .= " MODELO ".$rs['Modelo'];
                                        }                                        
                                        $NumeroConcepto++;
                                    }
                                    if($cobrarProcesadasColor){
                                        $um = $reporte->getUnidadMedida($idServicio, "Impresiones", $unidad_servicio);
                                        $des_aux = "PÁGINAS IMPRESAS COLOR";
                                        if($MostrarSeries){
                                            $des_aux .= " SERIE ".$rs['NoSerie'];
                                        }
                                        if($MostrarModelo){
                                            $des_aux .= " MODELO ".$rs['Modelo'];
                                        }
                                        $auxFactura = $NumeroFacturas;
                                        $auxConcepto = $NumeroConcepto;
                                        if($agrupar_color){
                                            $auxFactura = $NumeroFacturas + 1;
                                            $auxConcepto = $NumeroConceptosColor++;
                                        }                                        
                                        $NumeroConcepto++;
                                    }
                                }
                            }
                        }else{                            
                            if($MostrarEquipos){//Llena en blanco las columnas de los servicios globales que no se muestran.
                                for($i=0;$i<5;$i++){
                                    if($mostrarContadores || $i>= 2){
                                        echo "<td class='borde'></td>";
                                    }
                                }
                            }
                        }
                        
                        if($MostrarEquipos){   //Imprime direccion y/o ubicacion                     
                            if($MostrarUbicacion){//Imprime ubicacion
                                echo "<td class='borde'>".$rs['Ubicacion']."</td>";
                            }

                            if($MostrarDireccion){//Imprime direccion
                                if(isset($direcciones[$rs['ClaveCentroCosto']])){
                                    $direccion = $direcciones[$rs['ClaveCentroCosto']];
                                }else{
                                    $localidad = new Localidad();
                                    if($localidad->getLocalidadByClave($rs['ClaveCentroCosto'])){
                                        $direccion = $localidad->getCalle()." No. Ext: ".$localidad->getNoExterior()." No. Int: ".$localidad->getNoInterior()." 
                                            Col: ".$localidad->getColonia()." C.P.: ".$localidad->getCodigoPostal().", Del.: ".$localidad->getDelegacion().", ".$localidad->getEstado();
                                        $direcciones[$rs['ClaveCentroCosto']] = $direccion;
                                    }else{
                                        $direccion = "";
                                    }
                                }                            
                                echo "<td class='borde'>$direccion</td>";                            
                            }
                            echo "</tr>";
                        }          
                        
                        //Seccion para guardar los datos de las agrupaciones si se necesita
                        if($mostrarDetalleLocalidad || $mostrarDetalleCC || $mostrarDetalleZona){                            
                            if($mostrarDetalleLocalidad){
                                $campo_clave = $rs['ClaveCentroCosto'];                                
                                $nombre = $rs['CentroCostoNombre'];
                            }else if($mostrarDetalleCC){
                                $campo_clave = $rs["idCen_Costo"];
                                $nombre = $rs['CentroCostoLocalidad'];
                            }else{
                                $obj = new Zona();
                                $campo_clave = $rs["ClaveZona"];
                                if($obj->getRegistroById($campo_clave)){
                                    $nombre = $obj->getNombre();
                                }else{
                                    $nombre = $rs['ClaveZona'];
                                }
                            }
                            
                            if(!isset($nombreAgrupaciones[$campo_clave])){
                                $nombreAgrupaciones[$campo_clave] = $nombre;
                                $seriesPorAgrupacion[$campo_clave] = "";
                            }
                            $des_aux = "";
                            if($MostrarSeries){
                                $des_aux .= "SERIE: ".$rs['NoSerie'];
                            }
                            if($MostrarModelo){
                                $des_aux .= " MODELO:".$rs['Modelo'];
                            }
                            if($des_aux!=""){
                                $des_aux .= ", ";
                            }
                            $seriesPorAgrupacion[$campo_clave][$rs['IdKServicio'.$prefijos[$prefijo]]] .= ($des_aux);
                            $esParticularAgrupacion[$campo_clave][$rs['IdKServicio'.$prefijos[$prefijo]]] = $particular;
                            $idServicioByKServicio[$campo_clave][$rs['IdKServicio'.$prefijos[$prefijo]]] = $idServicio;
                            $incluidosBNAgrupacion[$campo_clave][$rs['IdKServicio'.$prefijos[$prefijo]]] = $rs[$prefijos[$prefijo].'incluidosBN'];
                            $incluidosColorAgrupacion[$campo_clave][$rs['IdKServicio'.$prefijos[$prefijo]]] = $rs[$prefijos[$prefijo].'incluidosColor'];
                            $equiposPorAgrupacion = $reporte->agregarValorNumericoArrayBidimensional($equiposPorAgrupacion, $campo_clave, $rs['IdKServicio'.$prefijos[$prefijo]], 1);
                            $impresionesBNAgrupacion = $reporte->agregarValorNumericoArrayBidimensional($impresionesBNAgrupacion, $campo_clave, $rs['IdKServicio'.$prefijos[$prefijo]], $diferencia_bn);
                            $impresionesColorAgrupacion = $reporte->agregarValorNumericoArrayBidimensional($impresionesColorAgrupacion, $campo_clave, $rs['IdKServicio'.$prefijos[$prefijo]], $diferencia_color);                            
                            if($particular){//Si es particular, por equipo se descuentan los paginas incluidas
                                $excedenteBNAgrupacion = $reporte->agregarValorNumericoArrayBidimensional($excedenteBNAgrupacion, $campo_clave, $rs['IdKServicio'.$prefijos[$prefijo]], $diferencia_bn-$incluidosBN); 
                                $excedenteColorAgrupacion = $reporte->agregarValorNumericoArrayBidimensional($excedenteColorAgrupacion, $campo_clave, $rs['IdKServicio'.$prefijos[$prefijo]], $diferencia_color-$incluidosColor);                                 
                            }else{//Si es global, no se descuentan las paginas incluidas, se hará hasta que se impriman los datos
                                $excedenteBNAgrupacion = $reporte->agregarValorNumericoArrayBidimensional($excedenteBNAgrupacion, $campo_clave, $rs['IdKServicio'.$prefijos[$prefijo]], $diferencia_bn); 
                                $excedenteColorAgrupacion = $reporte->agregarValorNumericoArrayBidimensional($excedenteColorAgrupacion, $campo_clave, $rs['IdKServicio'.$prefijos[$prefijo]], $diferencia_color); 
                            }                            
                            if($cobrarRenta){
                                $rentaAgrupacion[$campo_clave][$rs['IdKServicio'.$prefijos[$prefijo]]] = $rs[$prefijos[$prefijo]."Renta"];
                            }                                                                                                          
                            if($cobrarExcedenteBN){
                                $costoExcedentesBNAgrupacion[$campo_clave][$rs['IdKServicio'.$prefijos[$prefijo]]] = $rs[$prefijos[$prefijo].'ExcedentesBN'];
                            }
                            if($cobrarExcedenteColor){
                                $costoExcedentesColorAgrupacion[$campo_clave][$rs['IdKServicio'.$prefijos[$prefijo]]] = $rs[$prefijos[$prefijo].'ExcedentesColor'];
                            }
                            if($cobrarProcesadasBN){
                                $costoProcesadasBNAgrupacion[$campo_clave][$rs['IdKServicio'.$prefijos[$prefijo]]] = $rs[$prefijos[$prefijo].'ProcesadasBN'];                            
                            }
                            if($cobrarProcesadasColor){
                                $costoProcesadasColorAgrupacion[$campo_clave][$rs['IdKServicio'.$prefijos[$prefijo]]] = $rs[$prefijos[$prefijo].'ProcesadosColor'];
                            }                              
                        }
                        /*************************************        Fin de la fila de cada tabla       *************************************/
                        
                        
                        if($diferencia_bn > 0){
                            if(isset($contadorBNServicio[$rs['IdKServicio'.$prefijos[$prefijo]]])){
                                $contadorBNServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] += $diferencia_bn;
                                if($particular && $diferencia_bn > $incluidosBN){                                    
                                    $excedenteBNServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] += ($diferencia_bn - $incluidosBN);                                                                         
                                }else if(!$particular){
                                    $excedenteBNServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] += ($diferencia_bn);
                                }                                
                            }else{
                                $contadorBNServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = $diferencia_bn;
                                if($particular){
                                    if($diferencia_bn > $incluidosBN){                                        
                                        $excedenteBNServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = ($diferencia_bn - $incluidosBN);                                                                             
                                    }else{
                                        $excedenteBNServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = 0;                                     
                                    }
                                }else{
                                    $excedenteBNServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = ($diferencia_bn);
                                }
                            }
                        }
                        
                        if($diferencia_color > 0){                            
                            if(isset($contadorColorServicio[$rs['IdKServicio'.$prefijos[$prefijo]]])){
                                $contadorColorServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] += $diferencia_color;
                                if($particular && $diferencia_color > $incluidosColor){                                    
                                    $excedenteColorServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] += ($diferencia_color - $incluidosColor);                                     
                                }else if(!$particular){
                                    $excedenteColorServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] += ($diferencia_color); 
                                }
                            }else{
                                $contadorColorServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = $diferencia_color;
                                if($particular){
                                    if($diferencia_color > $incluidosColor){                                        
                                        $excedenteColorServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = ($diferencia_color - $incluidosColor);                                            
                                    }else{
                                        $excedenteColorServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = 0; 
                                    }
                                }else{
                                    $excedenteColorServicio[$rs['IdKServicio'.$prefijos[$prefijo]]] = ($diferencia_color);
                                }
                            }
                        }
                        
                        $no_equipo++;
                        $nueva_hoja = false;
                        $IdKServicioAnterior = $rs['IdKServicio'.$prefijos[$prefijo]];
                        $idServicioAnterior = $rs['IdServicio'.$prefijos[$prefijo]];
                        $prefijoAnterior = $prefijos[$prefijo];
                        $localidadAnterior = $rs['ClaveCentroCosto'];
                        $NombreLocalidadAnterior = $rs['CentroCostoNombre'];                     
                        $color_anterior = $rs['isColor'];
                        
                        //Vemos como se va a agrupar los datos segun los parametros
                        if($agrupar_servicio){
                            $variable_rs_cambio = 'IdKServicio'.$prefijos[$prefijo];//Cada que cambie esta variable, se creara una hoja                                
                            $variable_cambio_anterior = $rs[$variable_rs_cambio];
                        }else if($agrupar_tipo_servicio){
                            $variable_rs_cambio = 'IdServicio'.$prefijos[$prefijo];//Cada que cambie esta variable, se creara una hoja                                
                            $variable_cambio_anterior = $rs[$variable_rs_cambio];
                        }else if($agrupar_localidad){
                            $variable_rs_cambio = "ClaveCentroCosto";//Cada que cambie esta variable, se creara una hoja                                
                            $variable_cambio_anterior = $rs[$variable_rs_cambio];
                        }else if($agrupar_cc){
                            $variable_rs_cambio = 'idCen_Costo';//Cada que cambie esta variable, se creara una hoja                                
                            $variable_cambio_anterior = $rs[$variable_rs_cambio];
                        }else if($agrupar_zona){
                            $variable_rs_cambio = "ClaveZona";
                            $variable_cambio_anterior = $rs[$variable_rs_cambio];
                        }else if($agrupar_todo){
                            $variable_rs_cambio = "Junto";
                            $variable_cambio_anterior = $rs[$variable_rs_cambio];
                        }else{
                            $variable_rs_cambio = "NoSerie";
                            $variable_cambio_anterior = $rs[$variable_rs_cambio];
                        }
                        
                    }//Cierre while
                          
                    $costoTotal = 0;                    
                    if(!$todo_cerrado){                        
                        /*Si se quiere imprimir la fila de informacion detallada por servicio*/                        
                        if($IdKServicioAnterior != $rs['IdKServicio'.$prefijos[$prefijo]]){                                                                
                            if($mostrarDetalleServicio){                                       
                                $NumeroConcepto = $reporte->imprimirDetalleServicio($IdKServicioAnterior, $idServicioAnterior, $prefijoAnterior, $impresionesBNPorServicio, $impresionesColorPorServicio,
                                        $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProBN, $costoProColor, 
                                        $particularServicio, $iva, $excedenteBNServicio, $excedenteColorServicio, $equiposPorServicio, 
                                        $NumeroFacturas, $NumeroConcepto, $unidad_servicio, $prefijos, $incluidosBNServicio, $incluidosColorServicio, 
                                        $mostrarRenta, $encabezado_variable, $incluidosColor, $incluidosBN, $imprimir_cero, $dividir_lecturas, 
                                        $MostrarSeries, $seriesServicio, $MostrarModelo, $agrupar_color, $NumeroConceptosColor);
                                if($IdKServicioAnterior != 0){                                    
                                    $impresionesBNPorServicio = 0; $impresionesColorPorServicio = 0; $equiposPorServicio=0;
                                }
                            }
                        }                                                    
                       
                        
                        if($mostrarDetalleLocalidad || $mostrarDetalleCC || $mostrarDetalleZona){                                
                            $NumeroConcepto = $reporte->imprimirDetalle($nombreAgrupaciones, $esParticularAgrupacion, $idServicioByKServicio, $incluidosBNAgrupacion, 
                                    $incluidosColorAgrupacion, $equiposPorAgrupacion, $impresionesBNAgrupacion, $impresionesColorAgrupacion, $excedenteBNAgrupacion, 
                                    $excedenteColorAgrupacion, $rentaAgrupacion, $costoExcedentesBNAgrupacion, $costoExcedentesColorAgrupacion, $costoProcesadasBNAgrupacion, 
                                    $costoProcesadasColorAgrupacion, $iva, $mostrarRenta, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $imprimir_cero, 
                                    $dividir_lecturas,$MostrarSeries, $seriesPorAgrupacion, $MostrarModelo, $agrupar_color, $NumeroConceptosColor);
                        }
                        
                        /*Guardamos el concepto de la renta*/                        
                        $particularAnterior = $reporte->isParticularByPrefijo($reporte->obtenerIdPrefijo($prefijoAnterior, $prefijos));                        
                        if(!$mostrarDetalleServicio && !$mostrarDetalleLocalidad && !$mostrarDetalleCC && !$mostrarDetalleZona){
                            if(!$particularAnterior){         
                                $um = $reporte->getUnidadMedida($idServicioAnterior, "Renta", $unidad_servicio);
                                if($dividir_lecturas){
                                    $auxFactura = $NumeroFacturas+1;
                                    $auxConcepto = $NumeroRentaPorServicio++;                                    
                                }else{
                                    $auxFactura = $NumeroFacturas;
                                    $auxConcepto = $NumeroConcepto++;
                                }                                                                
                            }
                        }
                        $descripcion_renta = "";       
                        if(!empty($conceptos_adicionales)){
                            $NumeroConcepto = $reporte->crearConceptosAdicionales($conceptos_adicionales, $cliente_por_factura, $anexos_por_factura, 
                                    $zonas_por_factura, $cc_por_factura, $localidad_por_factura, $iva, $NumeroFacturas, $NumeroConcepto, $procesadosSeparado, $dividir_lecturas);
                            $procesadosSeparado = true;                                                                
                            $cliente_por_factura = $reporte->ponerValorUno($cliente_por_factura); $anexos_por_factura = $reporte->ponerValorUno($anexos_por_factura); 
                            $zonas_por_factura = $reporte->ponerValorUno($zonas_por_factura); $cc_por_factura = $reporte->ponerValorUno($cc_por_factura); 
                            $localidad_por_factura = $reporte->ponerValorUno($localidad_por_factura);
                        }
                        $NumeroConcepto = $reporte->imprimirTablaTotalAgrupacion($costoTotalPorGrupo + $reporte->getTotalConceptosAdicionales(), 
                                    $contadorBNServicio, $contadorColorServicio, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, 
                                    $costoProBN, $costoProColor, $particularServicio, $excedenteBNServicio, $excedenteColorServicio, $iva, 
                                    $idServicioByKServicio, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $mostrarDetalleServicio,
                                $incluidosBNServicio, $incluidosColorServicio, $MostrarEquipos, $imprimir_cero, $agrupar_color, $NumeroConceptosColor);                                                
                        
                        if($reporte->getHayConceptosSeparados()){
                            $NumeroFacturas++;
                        }
                        if($dividir_lecturas && $MostrarEquipos){//Si se dividio la factura, tenemos que guardar el numero de componentes de la factura de renta
                            
                        }else if($agrupar_color && $MostrarEquipos){
                            $NumeroConceptosColor = $reporte->getNumeroConceptosColor();                            
                        }
                        
                        echo "</table/><br/><br/>";                                                                       
                    }
                    
                        if($dividir_lecturas){
                            $NumeroFacturas++;
                        }else if($agrupar_color && $mostrarRenta){
                            $NumeroFacturas++;
                        }
                ?>                
                <?php
                        }/*else{
                            if($value == 0){
                                echo "No se pudieron encontrar equipos para esta búsqueda";
                            }
                        }*/
                    }//Fin iteracion de localidades con domicilio Fiscal
                }//Fin iteracion de los tipos de domicilio fiscal
                if(!$hay_resultados){
                    echo "No se pudieron encontrar equipos para esta búsqueda";
                }
                ?>                
            </form>
        </div>
    </body>
</html>