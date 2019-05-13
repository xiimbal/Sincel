<?php

header('Content-Type: text/html; charset=UTF-8');

session_start();
ini_set("memory_limit","600M");
/*echo ini_get("post_max_size");
echo ini_get("max_input_vars");*/

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" /*|| !isset($_POST['rfc']) || !isset($_POST['rfcFacturacion'])*/) {    
    header("Location: ../index.php");
}

if(!isset($_POST['rfc']) || !isset($_POST['rfcFacturacion'])){
    echo "<br/>Error: no se recibieron los datos completos para generar la pre-factura";
    return;
}

include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Factura.class.php");
include_once("../WEB-INF/Classes/Factura2.class.php");
include_once("../WEB-INF/Classes/FacturaConceptoExtra.class.php");
include_once("../WEB-INF/Classes/Concepto.class.php");
include_once("../WEB-INF/Classes/Contrato.class.php");
include_once("../WEB-INF/Classes/DatosFacturacionEmpresa.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/Menu.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$usuario = $_SESSION['user'];
$pantalla = "PHP facturarReporteLectura";

/*Para insertar en las tablas para la factura en PHP*/
$factura2 = new Factura(); //Este objeto guarda los datos en la bd
$concepto_obj = new Concepto();
$parametros = new Parametros();
$cliente = new Cliente();
$domicilio = new Localidad();
$datosFacturacion = new DatosFacturacionEmpresa();
$domicilioFiscal = new Localidad();
$contrato = new Contrato();
$menu = new Menu();
$catalogo = new Catalogo();
$empresa2 = new DatosFacturacionEmpresa();

//print_r($_POST);
if(isset($_POST['empresa'])){
    $empresa = $_POST['empresa'];
    $factura2->setEmpresa($empresa);
    $concepto_obj->setEmpresa($empresa);
    $parametros->setEmpresa($empresa);
    $cliente->setEmpresa($empresa);
    $domicilio->setEmpresa($empresa);
    $datosFacturacion->setEmpresa($empresa);
    $domicilioFiscal->setEmpresa($empresa);
    $contrato->setEmpresa($empresa);
    $menu->setEmpresa($empresa);
    $catalogo->setEmpresa($empresa);
    $pantalla = "PHP facturarReporteLectura cron";
}

$parametros->getRegistroById("8");
$liga = $parametros->getDescripcion();
$cliente->getRegistroByRFC($_POST['rfc']);
$empresa2->getRegistroById($cliente->getIdDatosFacturacionEmpresa());
if(!$domicilio->getLocalidadByClaveTipo($cliente->getClaveCliente(),"3")){
    $domicilio->getLocalidadByClave($cliente->getClaveCliente());
}

$MetodoPago = "5";
$FormaPago = "1";
$IdUsoCFDI = "3";
$datosFacturacion->getRegistroByRFC($_POST['rfcFacturacion']);
if($contrato->getRegistroById($_POST['no_contrato'])){
    if($contrato->getIdMetodoPago() != ""){
        $MetodoPago = $contrato->getIdMetodoPago();
    }
    if($contrato->getFormaPago() != ""){
        $FormaPago = $contrato->getFormaPago();
    }
    if($contrato->getIdUsoCFDI() != ""){
        $IdUsoCFDI = $contrato->getIdUsoCFDI();
    }
}

$idDomicilio = $domicilio->getIdDomicilio();

$iva = $_POST['iva'];
$imprimir_cero = $_POST['imprimir_cero'];
$muestra_serie = $_POST['muestra_serie'];
$muestra_ubicacion = $_POST['muestra_ubicacion'];

/*Datos para guardar en la bd*/
$factura2->setIdEmpresa($datosFacturacion->getIdDatosFacturacionEmpresa());
//Aunque los campos dicen setRFC, hay que mandarles la clave del cliente y el id de la empresa dee facturacion
$factura2->setRFCEmisor($datosFacturacion->getIdDatosFacturacionEmpresa()); $factura2->setRFCReceptor($cliente->getClaveCliente());
$factura2->setPeriodoFacturacion($_POST['periodo_facturacion']); $factura2->setIdDomicilioFiscal($idDomicilio); $factura2->setUsuarioCreacion($usuario); 
$factura2->setUsuarioUltimaModificacion($usuario);
$factura2->setPantalla($pantalla);
$factura2->setFormaPago($FormaPago); $factura2->setMetodoPago($MetodoPago); $factura2->setId_TipoFactura("2");
$factura2->setNumCtaPago($contrato->getNumeroCuenta());
$factura2->setTipoArrendamiento("1"); //Se guarda el tipo de arrendamiento 1, todas estas facturas son de arrendamiento
$factura2->setMostrarSerie($muestra_serie);
$factura2->setMostrarUbicacion($muestra_ubicacion);
$factura2->setNoContrato($contrato->getNoContrato());
$factura2->setDiasCredito($contrato->getDiasCredito());
if($datosFacturacion->getIdSerie() != ""){
    $factura2->setIdSerie($datosFacturacion->getIdSerie());
}

if((int)$empresa2->getCfdi33() == 1){
    $factura2->setCFDI33(1);
    $factura2->setIdUsoCFDI($IdUsoCFDI);
}else{
    $factura2->setCFDI33(0);
}

$num_facturas = intval($_POST['num_facturas']);
$serie_factura_partida = array();
?>
<html>
    <head>
        <title>Generaci&oacute;n de pre-facturas</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    </head>
    <body>
        <?php
            for($i=1; $i<=$num_facturas; $i++){    
                $conceptos = array();
                $Impuestos_Trasladado = array();
                $total = 0;
                $subtotal = 0;
                //Obtenemos el numero de conceptos por cada factura    
                if(isset($_POST['conceptos_factura_'.$i])){
                    $num_conceptos = intval($_POST['conceptos_factura_'.$i]);
                    if($num_conceptos == 0){
                        continue;
                    }
                }else{
                    $num_conceptos = 0;
                    continue;
                }    
                if(isset($_POST['id_domicilio_'.$i]) && $_POST['id_domicilio_'.$i] != ""){
                    if($domicilioFiscal->getLocalidadById($_POST['id_domicilio_'.$i])){                        
                        $idDomicilio = $domicilioFiscal->getIdDomicilio();$factura2->setIdDomicilioFiscal($idDomicilio);
                    }
                }
                //Insertamos la factura
                if(!$factura2->NuevaPreFactura()){
                    echo "<br/>Error: no se pudo generar la factura $i";
                    continue;
                }
                
                if($_POST['orden']!="" || $_POST['proveedor']!="" || $_POST['obs_dentro_xml']!="" || $_POST['obs_fuera_xml']!=""){
                    $extras = new FacturaConceptoExtra();
                    if(isset($_POST['empresa'])){
                        $empresa = $_POST['empresa'];
                        $extras->setEmpresa($empresa);
                    }
                    $extras->setId_factura($factura2->getIdFactura());
                    $extras->setNumero_order($_POST['orden']);
                    $extras->setNumero_proveedor($_POST['proveedor']);
                    $extras->setObservaciones_dentro_xml($_POST['obs_dentro_xml']);
                    $extras->setObservaciones_fuera_xml($_POST['obs_fuera_xml']);
                    $extras->setUsuarioCreacion($usuario);
                    $extras->setUsuarioUltimaModificacion($usuario);
                    $extras->setPantalla($pantalla);    
                    if(!$extras->newRegistro()){
                        echo "<br/>Error: no se pudieron guardar las observaciones o números de proveedor y orden";
                    }
                }

                $numero_partida = 1;
                for($j = 0;$j<$num_conceptos;$j++){//Recorremos todos los conceptos del servicio actual        
                    if(isset($_POST['cantidad_'.$i.'_'.$j])){
                        $cantidad = intval($_POST['cantidad_'.$i."_".$j]);
                    }else{
                        $cantidad = 0;
                    }

                    if(isset($_POST['um_'.$i."_".$j])){
                        $um = $_POST['um_'.$i."_".$j];
                    }else{
                        $um = "Servicio";
                    }

                    if(isset($_POST['descripcion_'.$i."_".$j])){
                        $concepto = $_POST['descripcion_'.$i."_".$j];
                        //Vamos a buscar las series que hay en esta partida                
                        $needle = "SERIE: ";
                        $lastPos = 0;
                        $positions = array();
                        $series = array();

                        while (($lastPos = strpos($concepto, $needle, $lastPos))!== false) {
                            $positions[] = $lastPos;
                            $lastPos = $lastPos + strlen($needle);
                        }

                        // Displays 3 and 10
                        foreach ($positions as $value) {
                            $index_fin = strpos($concepto, " ", $value+  strlen($needle));
                            if(!$index_fin){
                                $index_fin = strlen($concepto);
                            }
                            array_push($series, substr($concepto, $value + strlen($needle), $index_fin - ($value + strlen($needle)) ));
                        }
                        
                        if($_POST['imprime_serie'] == "0"){
                            while (($lastPos = strpos($concepto, $needle))!== false) {
                                $index_fin = strpos($concepto, " ", $value+  strlen($needle));
                                if(!$index_fin){
                                    $index_fin = strlen($concepto);
                                }
                                $concepto = substr($concepto, 0, $lastPos)."".substr($concepto, $index_fin);
                            }
                        }
                    }else{
                        $concepto = "";
                    }

                    if(isset($_POST['pu_'.$i."_".$j])){            
                        $pu = (float)($_POST['pu_'.$i."_".$j]);
                    }else{            
                        $pu = 0;
                    }

                    if(isset($_POST['importe_'.$i."_".$j])){            
                        $importe = (float)($_POST['importe_'.$i."_".$j]);
                    }else{            
                        $importe = 0;
                    }

                    if(isset($_POST['encabezado_'.$i."_".$j]) && $_POST['encabezado_'.$i."_".$j] == "1"){                        
                        $encabezado = true;
                    }else{            
                        $encabezado = false;
                    }

                    //echo "<br/> Agregando $cantidad $um $concepto $pu $importe para la factura $i";
                    if($importe == 0 && $cantidad == 0 && $concepto == "" && $importe == 0 && !$encabezado){
                        continue;
                    }

                    if($importe != 0 || $imprimir_cero == 1 || $encabezado){
                        if($concepto == ""){
                            continue;
                        }
                        if((int)$empresa2->getCfdi33() == 1){
                            if(isset($_POST['sat_'.$i."_".$j]) && !empty($_POST['sat_'.$i."_".$j])){                        
                                $idClave = $_POST['sat_'.$i."_".$j];
                            }else{            
                                $idClave = 51334;
                            }
                            
                            $idProductoEmpresa = 0;
                            $consulta = "SELECT * FROM k_empresaproductosat eps WHERE IdDatosFacturacionEmpresa = ".$empresa2->getIdDatosFacturacionEmpresa().
                                    " AND IdClaveProdServ = $idClave;";
                            $result = $catalogo->obtenerLista($consulta);
                            if(mysql_num_rows($result) > 0){
                                if($rs = mysql_fetch_array($result)){
                                    $idProductoEmpresa = $rs['IdEmpresaProductoSAT'];
                                }
                            }else{
                                $insert = "INSERT INTO k_empresaproductosat VALUES(0,".$empresa2->getIdDatosFacturacionEmpresa().",$idClave,700,'sistemas',NOW(),'sistemas',NOW(),'Facturar Reporte Lectura');";
                                //echo $insert;
                                $idProductoEmpresa = $catalogo->insertarRegistro($insert);
                            }
                            $concepto_obj->setIdEmpresaProductoSAT($idProductoEmpresa);
                            $concepto_obj->setUnidad("");
                        }else{
                            $concepto_obj->setUnidad($um);
                            $concepto_obj->setIdEmpresaProductoSAT("");
                        }
                        $concepto_obj->setIdFactura($factura2->getIdFactura()); $concepto_obj->setPrecioUnitario($pu);
                        $concepto_obj->setCantidad($cantidad); $concepto_obj->setDescripcion($concepto);
                        $concepto_obj->setUsuarioCreacion($usuario); $concepto_obj->setUsuarioUltimaModificacion($usuario);
                        $concepto_obj->setPantalla($pantalla); $concepto_obj->setTipo("null"); $concepto_obj->setId_articulo("");
                        if($encabezado){
                            $concepto_obj->setEncabezado("1");
                        }else{
                            $concepto_obj->setEncabezado("0");
                        }
                        if(!$concepto_obj->nuevoRegistro()){
                            echo "<br/>Error: no se pudo insertar el concepto $concepto de la factura $i";
                            continue;
                        }
                        
                        //Guardamos la factura-concepto correspondiente a las series de la partida actual
                        foreach ($series as $value) {
                            if(!isset($serie_factura_partida[$value])){
                                $serie_factura_partida[$value] = $numero_partida;
                            }
                        }
                        
                        $numero_partida++;
                        //echo "<br/> Agregando $cantidad $um $concepto $pu $importe para la factura $i";
                        $iva_concepto = $importe * $iva;            
                        $concepto1 = array($cantidad,$um,$concepto,$pu,$importe);
                        array_push($conceptos, $concepto1);
                        

                        $impuestos1 = array("IVA","".($iva * 100), $iva_concepto);
                        array_push($Impuestos_Trasladado, $impuestos1);

                        $subtotal += $importe;
                        $total += ($importe + $iva_concepto);
                    }
                }
                                
                if(isset($_POST['bitacoras_factura_'.$i]) || isset($_POST['bitacorascolor_factura_'.$i]) || isset($_POST['bitacorasrenta_factura_'.$i])){
                    $array_bitacoras = explode(",", $_POST['bitacoras_factura_'.$i]);
                    $array_bitacoras_color = explode(",", $_POST['bitacorascolor_factura_'.$i]);    
                    $array_bitacoras_renta = explode(",", $_POST['bitacorasrenta_factura_'.$i]);    
                    //Unimos todas las bitacoras en un solo array
                    $todos_array = array();
                    $contadoresBN = array();
                    $contadoresBNAnterior = array();
                    $contadoresProcesadasBN = array();
                    $contadoresExcedentesBN = array();
                    $contadoresColor = array();
                    $contadoresColorAnterior = array();  
                    $contadoresProcesadasColor = array();
                    $contadoresExcedentesColor = array();
                    
                    $array_bitacoras_final = array();
                    $array_bitacoras_color_final = array();
                    $array_bitacoras_renta_final = array();
                    $cc_bitacoras_movs = array();
                    //print_r($_POST);
                    foreach ($array_bitacoras as $value) {
                        $valores = explode("&&", $value);
                        $value = $valores[0];  
                        array_push($array_bitacoras_final, $value);
                        if(!empty($value) && !in_array($value, $todos_array)){
                            if(isset($_POST['cc_'.$value]) && !empty($_POST['cc_'.$value])){
                                $cc_bitacoras_movs[$value] = $_POST['cc_'.$value];
                            }
                            array_push($todos_array, $value);                            
                            array_push($contadoresBN, $valores[1]);
                            array_push($contadoresBNAnterior, $valores[2]);
                            array_push($contadoresProcesadasBN, $valores[3]);
                            array_push($contadoresExcedentesBN, $valores[4]);
                            if(isset($valores[5]) || isset($valores[6])){
                                if(!empty($valores[5])){
                                    array_push($contadoresColor, $valores[5]);
                                }else{
                                    array_push($contadoresColor, 0);
                                }
                                if(!empty($valores[6])){
                                    array_push($contadoresColorAnterior, $valores[6]);
                                }else{
                                    array_push($contadoresColorAnterior, 0);
                                }
                                if(!empty($valores[7])){
                                    array_push($contadoresProcesadasColor, $valores[7]);
                                }else{
                                    array_push($contadoresProcesadasColor, 0);
                                }
                                if(!empty($valores[8])){
                                    array_push($contadoresExcedentesColor, $valores[8]);
                                }else{
                                    array_push($contadoresExcedentesColor, 0);
                                }
                            }else{
                                array_push($contadoresColor, "null");
                                array_push($contadoresColorAnterior, "null");
                                array_push($contadoresProcesadasColor, "null");
                                array_push($contadoresExcedentesColor, "null");
                            }
                        }
                    }
                    
                    foreach ($array_bitacoras_color as $value) {
                        $valores = explode("&&", $value);
                        $value = $valores[0]; 
                        array_push($array_bitacoras_color_final, $value);
                        if(!empty($value) && !in_array($value, $todos_array)){
                            if(isset($_POST['cc_'.$value]) && !empty($_POST['cc_'.$value])){
                                $cc_bitacoras_movs[$value] = $_POST['cc_'.$value];
                            }
                            
                            array_push($todos_array, $value);                            
                            if(isset($valores[2]) && isset($valores[1]) && !empty($valores[2]) && !empty($valores[1])){
                                array_push($contadoresBN, $valores[1]);
                                array_push($contadoresBNAnterior, $valores[2]);
                                array_push($contadoresProcesadasBN, $valores[3]);
                                array_push($contadoresExcedentesBN, $valores[4]);
                                if(isset($valores[5]) || isset($valores[6])){
                                    if(!empty($valores[5])){
                                        array_push($contadoresColor, $valores[5]);
                                    }else{
                                        array_push($contadoresColor, 0);
                                    }
                                    if(!empty($valores[6])){
                                        array_push($contadoresColorAnterior, $valores[6]);
                                    }else{
                                        array_push($contadoresColorAnterior, 0);
                                    }
                                    if(!empty($valores[7])){
                                        array_push($contadoresProcesadasColor, $valores[7]);
                                    }else{
                                        array_push($contadoresProcesadasColor, 0);
                                    }
                                    if(!empty($valores[8])){
                                        array_push($contadoresExcedentesColor, $valores[8]);
                                    }else{
                                        array_push($contadoresExcedentesColor, 0);
                                    }
                                }else{
                                    array_push($contadoresColor, "null");
                                    array_push($contadoresColorAnterior, "null");
                                    array_push($contadoresProcesadasColor, "null");
                                    array_push($contadoresExcedentesColor, "null");
                                }
                            }else{
                                array_push($contadoresBN, "null");
                                array_push($contadoresBNAnterior, "null");
                                array_push($contadoresProcesadasBN, "null");
                                array_push($contadoresExcedentesBN, "null");
                                array_push($contadoresColor, "null");
                                array_push($contadoresColorAnterior, "null");
                                array_push($contadoresProcesadasColor, "null");
                                array_push($contadoresExcedentesColor, "null");
                            }
                        }
                    }
                                                          
                    foreach ($array_bitacoras_renta as $value) {
                        $valores = explode("&&", $value);
                        $value = $valores[0]; 
                        array_push($array_bitacoras_renta_final, $value);
                        if(!empty($value) && !in_array($value, $todos_array)){
                            array_push($todos_array, $value);
                            if(isset($_POST['cc_'.$value]) && !empty($_POST['cc_'.$value])){
                                $cc_bitacoras_movs[$value] = $_POST['cc_'.$value];
                            }
                            
                            if(isset($valores[2]) && isset($valores[1]) && !empty($valores[2]) && !empty($valores[1])){
                                array_push($contadoresBN, $valores[2]);
                                array_push($contadoresBNAnterior, $valores[1]);
                                array_push($contadoresProcesadasBN, $valores[3]);
                                array_push($contadoresExcedentesBN, $valores[4]);
                                if(isset($valores[5]) || isset($valores[6])){
                                    if(!empty($valores[5])){
                                        array_push($contadoresColor, $valores[5]);
                                    }else{
                                        array_push($contadoresColor, 0);
                                    }
                                    if(!empty($valores[6])){
                                        array_push($contadoresColorAnterior, $valores[6]);
                                    }else{
                                        array_push($contadoresColorAnterior, 0);
                                    }
                                }else{
                                    array_push($contadoresColor, "null");
                                    array_push($contadoresColorAnterior, "null");
                                    array_push($contadoresProcesadasColor, "null");
                                    array_push($contadoresExcedentesColor, "null");
                                }
                            }else{
                                array_push($contadoresBN, "null");
                                array_push($contadoresBNAnterior, "null");
                                array_push($contadoresProcesadasBN, "null");
                                array_push($contadoresExcedentesBN, "null");
                                array_push($contadoresColor, "null");
                                array_push($contadoresColorAnterior, "null");
                                array_push($contadoresProcesadasColor, "null");
                                array_push($contadoresExcedentesColor, "null");
                            }
                        }
                    }
                    
                    //print_r($todos_array);
                    $factura2->detalleFactura($factura2->getIdFactura(), $todos_array, $array_bitacoras_final, $array_bitacoras_color_final, $array_bitacoras_renta_final, false, $usuario, $pantalla,
                            $contadoresBN, $contadoresBNAnterior, $contadoresProcesadasBN, $contadoresExcedentesBN ,$contadoresColor, $contadoresColorAnterior, $contadoresProcesadasColor , $contadoresExcedentesColor, $cc_bitacoras_movs); 
                                        
                    foreach ($serie_factura_partida as $key => $value) {
                        $consulta = "UPDATE `c_facturadetalle` SET NumeroPartida = $value WHERE IdFactura = ".$factura2->getIdFactura()." AND IdBitacora = (SELECT id_bitacora FROM c_bitacora WHERE NoSerie = '$key');";
                        $result = $catalogo->obtenerLista($consulta);
                        if($result != "1"){
                            echo "<br/>Error: no se pudo guardar la serie $key de la factura ".$factura2->getFolio();
                        }
                    }                    
                }
                
                if(isset($_SESSION['idUsuario']) && $menu->tieneSubmenu($_SESSION['idUsuario'], 92)){
                    $action12 = "alta_factura";
                    if((int)$empresa2->getCfdi33() == 1){
                        $action12 = "alta_factura_33";
                    }
                    echo "Se registró la nueva pre-factura con el folio <a href='".$liga."principal.php?mnu=facturacion&action=$action12&id=".$factura2->getIdFactura()."' 
                        target='_blank'>".$factura2->getFolio()."</a><br/>";
                }else{
                    echo "Se registró la nueva pre-factura con el folio". $factura2->getFolio();
                }
            }
        ?>
    </body>
</html>