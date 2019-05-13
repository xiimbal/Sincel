<?php
//ini_set("post_max_size","100M");
session_start();
if (!isset($_POST['cliente']) || !isset($_POST['localidad'])) {
    header("Location: ../index.php");
}

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

header('Content-Type: text/html; charset=UTF-8');

ini_set("memory_limit", "600M");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/ReporteLectura.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Contacto.class.php");
include_once("../WEB-INF/Classes/Contrato.class.php");
include_once("../WEB-INF/Classes/CentroCostoReal.class.php");
include_once("../WEB-INF/Classes/Zona.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("../WEB-INF/Classes/ServicioGeneral.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");

if (isset($_POST['empresa'])) {
    $empresa = $_POST['empresa'];
} else {
    $empresa = NULL;
}
$contrato = $_POST['contrato'];
$anexo = $_POST['anexo'];

$permisos_grid = new PermisosSubMenu();
$permisos_grid->setEmpresa($empresa);
$catalogo = new Catalogo();
$catalogo->setEmpresa($empresa);
$obj_cliente = new Cliente();
$obj_cliente->setEmpresa($empresa);
$obj_direccion = new Localidad();
$obj_direccion->setEmpresa($empresa);
$obj_contacto = new Contacto();
$obj_contacto->setEmpresa($empresa);
$obj_zona = new Zona();
$obj_zona->setEmpresa($empresa);
$reporte = new ReporteLectura();
$reporte->setEmpresa($empresa);
$caracteristicas = new EquipoCaracteristicasFormatoServicio();
$caracteristicas->setEmpresa($empresa);
$parametros = new Parametros();
$parametros->setEmpresa($empresa);

if ($parametros->getRegistroById("8")) {
    $liga = $parametros->getDescripcion();
} else {
    $liga = "http://genesis2.techra.com.mx/genesis2/";
}

$mostrarContadores = true;
if ($parametros->getRegistroById("13") && $parametros->getValor() == "0") {
    $mostrarContadores = false;
}

$same_page = "facturacion/ReporteLecturas.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$faltanLecturasActual = false;
$faltanLecturasMesAnterior = false;
$textoSeriesActuales = "";
$textoSeriesMesAnterior = "";
$iva = 0.16;

$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
$prefijos = array("gim", "gfa", "im", "fa"); //Prefijos a revisar (siempre se toman las prioridades: gim, gfa, im, fa)
$direcciones = array();

/* * ***********************      Variables de parametros     ************************ */
if (isset($_POST['resal_perio']) && $_POST['resal_perio'] == "1") {
    $resaltar_encabezado = true;
} else {
    $resaltar_encabezado = false;
}

/* Mostrar equipos */
if (isset($_POST['Mostrar_Serie']) && $_POST['Mostrar_Serie'] == "1") {
    $imprime_serie = 1;
    $MostrarSeries = true;
} else {
    $MostrarSeries = false;
    $imprime_serie = 0;
}

/* Mostrar equipos */
if (isset($_POST['Mostrar_Modelo']) && $_POST['Mostrar_Modelo'] == "1") {
    $MostrarModelo = true;
} else {
    $MostrarModelo = false;
}

/* Mostrar Lecturas */
if (isset($_POST['MostrarLecturas']) && $_POST['MostrarLecturas'] == "1") {
    $muestra_serie = 1;
    $MostrarLecturas = true;
} else {
    $muestra_serie = 0;
    $MostrarLecturas = true;
}

/* Mostrar Localidades */
if (isset($_POST['MostrarLocalidad']) && $_POST['MostrarLocalidad'] == "1") {
    $MostrarLocalidad = true;
} else {
    $MostrarLocalidad = false;
}

/* Manejar Historico de facturacion, se utiliza para que se tome en cuenta si los equipos estaban con el cliente en el periodo que se esta facturando o no */
if (isset($_POST['HistoricoFacturacion']) && $_POST['HistoricoFacturacion'] == "1") {
    $HistoricoFacturacion = true;
} else {
    $HistoricoFacturacion = false;
}

/* Tomar en cuenta la fecha de instalacion de los equipos, para ser considerados o no dentro de la facturación */
if (isset($_POST['FechaInstalacion']) && $_POST['FechaInstalacion'] == "1") {
    $FechaInstalacion = true;
} else {
    $FechaInstalacion = false;
}

/* Mostrar direccion */
if (isset($_POST['dir_rep']) && $_POST['dir_rep'] == "1") {
    $MostrarDireccion = true;
} else {
    $MostrarDireccion = false;
}

/* Mostrar ubicacion */
if (isset($_POST['mostrar_area']) && $_POST['mostrar_area'] == "1") {
    $MostrarUbicacion = true;
    $muestra_ubicacion = 1;
} else {
    $MostrarUbicacion = false;
    $muestra_ubicacion = 0;
}

/* Renta adelantada */
if (isset($_POST['fact_adel']) && $_POST['fact_adel'] == "1") {
    $FacturaAdelantada = true;
} else {
    $FacturaAdelantada = false;
}

/* Imprimer cantidad en 0 */
if (isset($_POST['MostrarImporteCero']) && $_POST['MostrarImporteCero'] == "1") {
    $imprimir_cero = 1;
} else {
    $imprimir_cero = 0;
}

/* Dividir las facturas de renta y de lecturas */
if (isset($_POST['rentas_lecturas']) && $_POST['rentas_lecturas'] == "1") {
    $dividir_lecturas = true;
} else {
    $dividir_lecturas = false;
}

/* Dividir las facturas de renta y de lecturas */
if (isset($_POST['MostrarEncabezadoServicio']) && $_POST['MostrarEncabezadoServicio'] == "1") {
    $mostrarEncabezadosServicio = true;
} else {
    $mostrarEncabezadosServicio = false;
}

/* Dividir las facturas de renta y de lecturas */
if (isset($_POST['Agrupar_Renta']) && $_POST['Agrupar_Renta'] == "1") {
    $mostrarRenta = true;
} else {
    $mostrarRenta = false;
}

/* Mostrar periodo */
if (isset($_POST['periodo_factura']) && $_POST['periodo_factura'] == "1") {
    $mostrar_periodo = true;
} else {
    $mostrar_periodo = false;
}

/* Dividir las facturas de color y BN */
if (isset($_POST['Dividir_Color']) && $_POST['Dividir_Color'] == "1") {
    $agrupar_color = true;
} else {
    $agrupar_color = false;
}

/* Codigo de productos para renta e impresiones */
$IdProductoSATRenta = 50951;
$IdProductoSATImpresion = 51334;
if (isset($_POST['rentaSAT']) && !empty($_POST['rentaSAT'])) {
    $IdProductoSATRenta = $_POST['rentaSAT'];
}
if (isset($_POST['impresionesSAT']) && !empty($_POST['impresionesSAT'])) {
    $IdProductoSATImpresion = $_POST['impresionesSAT'];
}

if ($resaltar_encabezado) {
    $resaltado_i = "<b>";
    $resaltado_f = "</b>";
} else {
    $resaltado_i = "";
    $resaltado_f = "";
}

$formaPago = "";
$NoContrato = "";
$encabezado_variable = $reporte->getEncabezadoServicio($_POST, $prefijos); //Obtenemos los encabezados personalizados.
$unidad_servicio = $reporte->getUMServicio($_POST); //Obtenemos las unidades de medida personalizadas.
$conceptos_adicionales = $reporte->getConceptosAdicionales($_POST); //Conceptos adicionales.

$orden = $_POST['num_orden'];
$proveedor = $_POST['num_prov'];
$obs_dentro_xml = $_POST['obs_in_xml'];
$obs_fuera_xml = $_POST['obs_out_xml'];

/* Division de facturas */
$agrupar_equipo = false;
$agrupar_servicio = false;
$agrupar_tipo_servicio = false;
$agrupar_localidad = false;
$agrupar_zona = false;
$agrupar_cc = false;
$agrupar_todo = false;

if (isset($_POST['dividir_factura'])) {
    switch ($_POST['dividir_factura']) {
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

/* Detalles por lectura */
$MostrarEquipos = false;
$mostrarDetalleServicio = false;
$mostrarDetalleLocalidad = false;
$mostrarDetalleCC = false;
$mostrarDetalleZona = false;
if (isset($_POST['agrupar_factura'])) {
    switch ($_POST['agrupar_factura']) {
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
}

$cliente = $_POST['cliente'];
$cc = $_POST['localidad'];
$cen_costo = $_POST['centro_costo'];
$anexo = $_POST['anexo'];
$contrato = $_POST['contrato'];
$zona = $_POST['zona'];
$facturarA = "";
$rfcFacturarA = "";

//Vemos si el cliente le factura a otra empresa en este contrato.
$objContrato = new Contrato();
$objContrato->setNoContrato($contrato);
if ($objContrato->getNombreClienteGrupoFacturacion()) {
    $facturarA = $objContrato->getFacturarA();
    $rfcFacturarA = $objContrato->getRazonSocial();
}

/* Obtenemos la fecha a procesar */
if (isset($_POST['fecha']) && $_POST['fecha'] != "") {
    $month = substr($_POST['fecha'], 0, 2);
    $year = substr($_POST['fecha'], 3, 4);
} else {
    $month = date('m');
    $year = date('Y');
}

$localidades = array();
if (empty($cc)) {//Sino se filtro una localidad, se obtienen las localidades que fueron marcadas como direccion fiscal
    $query = $catalogo->obtenerLista("SELECT ClaveCentroCosto FROM c_centrocosto WHERE ClaveCliente = '$cliente' AND Activo = 1 AND TipoDomicilioFiscal = 1");
    while ($rs = mysql_fetch_array($query)) {
        array_push($localidades, $rs['ClaveCentroCosto']);
    }
} else {
    array_push($localidades, $cc);
}
//print_r($localidades);
$liga_excel = "ReporteLecturaXLS.php?cl=$cliente";
if ($cc != "") {
    $liga_excel.="&cc=$cc";
}
if ($cen_costo != "") {
    $liga_excel.="&cco=$cen_costo";
}
if ($anexo != "") {
    $liga_excel.="&an=$anexo";
}
if (isset($_POST['fecha']) && $_POST['fecha'] != "") {
    $liga_excel.="&fe=" . $_POST['fecha'];
}

$tiposDomicilios = array(0, 1);

$obj_cliente->getRegistroById($cliente);
$obj_direccion->getLocalidadByClave($cliente);
$obj_contacto->getContactoByClaveEspecial($cliente);
$obj_zona->getRegistroById($obj_cliente->getClaveZona());

$form = "form_facturalectura";
if (isset($_POST['postfijo']) && $_POST['postfijo'] != "") {
    $form.= ("_" . $_POST['postfijo']);
}
?>
<!DOCTYPE>
<html lang="es" style="width: 100%;">
    <head>
        <title>Reporte de facturación</title>
        <script type="text/javascript" src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
        <link rel="icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <!-- JS -->
        <?php
        if (!isset($_POST['independiente'])) {
            echo '<link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />';
            echo '<script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
                    <script src="../resources/js/jquery/jquery-ui.min.js"></script>';
            $action = "../facturacion/facturarReporteLectura.php";
            $liga .= $action;
        } else {
            echo '<link rel="stylesheet" href="resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />';
            echo '<script src="resources/js/jquery/jquery-1.11.3.min.js"></script>
                    <script src="resources/js/jquery/jquery-ui.min.js"></script>';
            $action = "facturacion/facturarReporteLectura.php";
            $liga .= $action;
        }
        ?>


<!--<script type="text/javascript" language="javascript" src="../resources/js/paginas/exportar_excel.js"></script>-->
        <style>
            table{
                border-collapse:collapse;
            }            
            .borde{border: 1px solid #000;}
        </style>
        <script>
            //$(".button").button();
        </script>
    </head>
    <body style="min-width: 96%;">                
                <?php
                if (!isset($_POST['independiente'])) {
                    ?>
            <div style="margin-left: 83%;">            
                <form action="ReporteLecturaXLS.php" method="POST" target="_blank">
                    <a href=javascript:window.print();>Imprimir PDF</a>
            <?php
            $data = serialize($_POST);
            $encoded = htmlentities($data);
            echo '<input type="hidden" name="post" id="post" value="' . $encoded . '">';
            echo '<input type="submit" class="botonExcel button" title="Exportar a excel" value="Exportar a excel"/>';
            ?>                            
                </form>                        
            </div>        
<?php } ?>
        <div style="width: 100%;" class="reporte">              
            <form id="<?php echo $form; ?>" name="<?php echo $form; ?>" action="<?php echo $action; ?>" method="POST" target="_blank">                                
                <input type="hidden" id="orden" name="orden" value="<?php echo $orden; ?>"/>
                <input type="hidden" id="proveedor" name="proveedor" value="<?php echo $proveedor; ?>"/>
                <input type="hidden" id="obs_dentro_xml" name="obs_dentro_xml" value="<?php echo $obs_dentro_xml; ?>"/>
                <input type="hidden" id="obs_fuera_xml" name="obs_fuera_xml" value="<?php echo $obs_fuera_xml; ?>"/>
                <input type="hidden" id="imprimir_cero" name="imprimir_cero" value="<?php echo $imprimir_cero; ?>"/>   
                <input type="hidden" id="muestra_serie" name="muestra_serie" value="<?php echo $muestra_serie; ?>"/>
                <input type="hidden" id="muestra_ubicacion" name="muestra_ubicacion" value="<?php echo $muestra_ubicacion; ?>"/>
                <?php
                if ($permisos_grid->getAlta()) {
                    echo '<input type="submit" name="submit_facturalectura" id="submit_facturalectura" value="Generar prefactura" style="margin-left: 91%;"/>';
                }
                if (isset($_POST['atm_post'])) {
                    //extract data from the post
                    extract($_POST);
                    //set POST variables
                    $url = $liga;
                    $fields = array(
                        'orden' => urlencode($orden),
                        'proveedor' => urlencode($proveedor),
                        'obs_dentro_xml' => urlencode($obs_dentro_xml),
                        'obs_fuera_xml' => urlencode($obs_fuera_xml),
                        'imprimir_cero' => urlencode($imprimir_cero),
                        'muestra_serie' => urlencode($muestra_serie),
                        'muestra_ubicacion' => urlencode($muestra_ubicacion),
                        'empresa' => urlencode($empresa)
                    );
                }

                $prefijo_pagml = array("PAGINAS", "ML", "PAGINAS", "ML"); //Este array debe de ir asociado a los prefijos del array prefijos
                $NumeroFacturas = 0;
                $hay_resultados = false;
                foreach ($tiposDomicilios as $value) {
                    if ($value == 1) {
                        $limit = count($localidades);
                    } else {
                        $limit = 1;
                    }
                    for ($j = 0; $j < $limit; $j++) {
                        if ($value == 1) {
                            //echo "Procesando ".$localidades[$i];
                            $cc_aux = $localidades[$j];
                        } else {
                            $cc_aux = $cc;
                        }
                        $consulta = $reporte->generarConsultaConRetiros($anexo, $cc_aux, $cen_costo, $cliente, $contrato, $zona, $agrupar_equipo, $agrupar_localidad, $agrupar_cc, $agrupar_servicio, $agrupar_zona, $agrupar_tipo_servicio, $value, $year, $month, $MostrarLocalidad, $HistoricoFacturacion, $FechaInstalacion);

                        //echo $consulta;
                        $result = $catalogo->obtenerLista($consulta);
                        $prefijo = 0;
                        $no_equipo = 1;
                        $nueva_hoja = true;
                        if (mysql_num_rows($result) > 0) {
                            $hay_resultados = true;
                            //Inicializacion de variables
                            $costoTotalPorGrupo = 0;
                            $contadorBNServicio = array();
                            $contadorColorServicio = array();
                            $costoRentaServicio = array();
                            $costoExcedentesBN = array();
                            $costoExcedentesColor = array();
                            $costoProBN = array();
                            $costoProColor = array();
                            $particularServicio = array();
                            $excedenteBNServicio = array();
                            $excedenteColorServicio = array();
                            $idServicioByKServicio = array();
                            $incluidosBNServicio = array();
                            $incluidosColorServicio = array();
                            $seriesServicio = array();
                            /* Arreglos para las agrupaciones */
                            $nombreAgrupaciones = array();
                            $rentaAgrupacion = array();
                            $esParticularAgrupacion = array();
                            $impresionesBNAgrupacion = array();
                            $impresionesColorAgrupacion = array();
                            $costoExcedentesBNAgrupacion = array();
                            $costoExcedentesColorAgrupacion = array();
                            $incluidosBNAgrupacion = array();
                            $incluidosColorAgrupacion = array();
                            $excedenteBNAgrupacion = array();
                            $excedenteColorAgrupacion = array();
                            $costoProcesadasBNAgrupacion = array();
                            $costoProcesadasColorAgrupacion = array();
                            $equiposPorAgrupacion = array();
                            $seriesPorAgrupacion = array();
                            $excedentes_bn_por_equipo = array();
                            $excedentes_color_por_equipo = array();
                            $cliente_por_factura = array();
                            $anexos_por_factura = array();
                            $zonas_por_factura = array();
                            $cc_por_factura = array();
                            $localidad_por_factura = array();

                            $IdKServicioAnterior = "0";
                            $idServicioAnterior = "0";
                            $prefijoAnterior = "";
                            $localidadAnterior = "";
                            $NombreLocalidadAnterior = "";
                            $impresionesBNPorServicio = 0;
                            $impresionesColorPorServicio = 0;
                            $equiposPorServicio = 0;
                            $NumeroRentaPorServicio = 0;
                            $NumeroConceptosColor = 0;
                            $NumeroConcepto = 0;
                            $descripcion_renta = "";
                            $idBitacoras = "";
                            $idBitacorasColor = "";
                            $idBitacorasRenta = "";

                            $cobrarRenta = false;
                            $cobrarExcedenteBN = false;
                            $cobrarExcedenteColor = false;
                            $cobrarProcesadasBN = false;
                            $cobrarProcesadasColor = false;

                            $incluidosBN = 0;
                            $incluidosColor = 0;
                            $excedentesBN = 0;
                            $excedentesColor = 0;
                            $particular = false;
                            $idServicio = 0;
                            $procesadosSeparado = false;
                            $todo_cerrado = false; //Esta variable se ocupa para arregla un bug, en ocaciones en el ultimo servicio, se cierra dos veces

                            /** Estas variables son para mandar al php de generar la factura * */
                            $rfc = "";
                            $rfcFacturacion = "";
                            $variable_rs_cambio = ""; //Cada que cambie esta variable, se creara una nueva hoja (factura)
                            $variable_cambio_anterior = 0;
                            $color_anterior = "";
                            ?>            
                            <?php
                            while ($rs = mysql_fetch_array($result)) {
                                //print_r($rs);
                                $rfc = $rs['RFC'];
                                $rfcFacturacion = $rs['RFCFacturacion'];
                                $formaPago = $rs['FormaPago'];
                                $NoContrato = $rs['NoContrato'];
                                $reporte = new ReporteLectura();
                                $reporte->setIdProductoSATRenta($IdProductoSATRenta);
                                $reporte->setIdProductoSATImpresion($IdProductoSATImpresion);

                                $reporte->setEmpresa($empresa);
                                if ($mostrar_periodo) {
                                    $reporte->setPeriodo("$year-$month-01");
                                }

                                if (isset($_POST['atm_post'])) {
                                    $reporte->setAtm_post(true);
                                } else {
                                    $reporte->setAtm_post(false);
                                }

                                if ($rs['esMovimiento'] == "1") {
                                    echo "<input type='hidden' id='cc_" . $rs['id_bitacora'] . "' name='cc_" . $rs['id_bitacora'] . "' value='" . $rs['ClaveCentroCosto'] . "'/>";
                                    if (isset($_POST['atm_post'])) {
                                        $fields['cc_' . $rs['id_bitacora']] = urlencode($rs['ClaveCentroCosto']);
                                    }
                                }

                                if (($variable_cambio_anterior != "0" && $variable_cambio_anterior != $rs[$variable_rs_cambio])
                                /* || ($agrupar_color && $color_anterior!="" && $color_anterior!=$rs['isColor']) */) {//Cada nuevo servicio es una hoja nueva o si se pide separara por color
                                    if (!$nueva_hoja) {/* Cerramos la ultima hoja abierta */
                                        /* Si se quiere imprimir la fila de informacion detallada por servicio */
                                        if ($mostrarDetalleServicio) {
                                            $reporte->setFields($fields);
                                            $NumeroConcepto = $reporte->imprimirDetalleServicio($IdKServicioAnterior, $idServicioAnterior, $prefijoAnterior, $impresionesBNPorServicio, $impresionesColorPorServicio, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProBN, $costoProColor, $particularServicio, $iva, $excedenteBNServicio, $excedenteColorServicio, $equiposPorServicio, $NumeroFacturas, $NumeroConcepto, $unidad_servicio, $prefijos, $incluidosBNServicio, $incluidosColorServicio, $mostrarRenta, $encabezado_variable, $incluidosColor, $incluidosBN, $imprimir_cero, $dividir_lecturas, $MostrarSeries, $seriesServicio, $MostrarModelo, $agrupar_color, $NumeroConceptosColor);
                                            $fields = $reporte->getFields();
                                            if ($IdKServicioAnterior != $rs['IdKServicio' . $prefijos[$prefijo]] && $IdKServicioAnterior != 0) {
                                                $impresionesBNPorServicio = 0;
                                                $impresionesColorPorServicio = 0;
                                                $equiposPorServicio = 0;
                                            }
                                        } else if ($mostrarDetalleLocalidad || $mostrarDetalleCC || $mostrarDetalleZona) {
                                            $reporte->setFields($fields);
                                            $NumeroConcepto = $reporte->imprimirDetalle($nombreAgrupaciones, $esParticularAgrupacion, $idServicioByKServicio, $incluidosBNAgrupacion, $incluidosColorAgrupacion, $equiposPorAgrupacion, $impresionesBNAgrupacion, $impresionesColorAgrupacion, $excedenteBNAgrupacion, $excedenteColorAgrupacion, $rentaAgrupacion, $costoExcedentesBNAgrupacion, $costoExcedentesColorAgrupacion, $costoProcesadasBNAgrupacion, $costoProcesadasColorAgrupacion, $iva, $mostrarRenta, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $imprimir_cero, $dividir_lecturas, $MostrarSeries, $seriesPorAgrupacion, $MostrarModelo, $agrupar_color, $NumeroConceptosColor);
                                            $fields = $reporte->getFields();
                                        }

                                        /* Guardamos el concepto de la renta */
                                        $particularAnterior = $reporte->isParticularByPrefijo($reporte->obtenerIdPrefijo($prefijoAnterior, $prefijos));
                                        if (!$mostrarDetalleServicio && !$mostrarDetalleLocalidad && !$mostrarDetalleCC && !$mostrarDetalleZona) {
                                            if (!$particularAnterior) {
                                                $um = $reporte->getUnidadMedida($idServicioAnterior, "Renta", $unidad_servicio);
                                                if ($dividir_lecturas) {
                                                    $auxFactura = $NumeroFacturas + 1;
                                                    $auxConcepto = $NumeroRentaPorServicio++;
                                                } else {
                                                    $auxFactura = $NumeroFacturas;
                                                    $auxConcepto = $NumeroConcepto++;
                                                }
                                                $reporte->setFields($fields);
                                                $reporte->crearConceptoRentaFactura($particularAnterior, $equiposPorServicio, $um, $descripcion_renta, $IdKServicioAnterior, $costoRentaServicio, $idServicioByKServicio, $auxFactura, $auxConcepto);
                                                $fields = $reporte->getFields();
                                            }
                                        }
                                        $descripcion_renta = "";
                                        if (!empty($conceptos_adicionales)) {
                                            $reporte->setFields($fields);

                                            $NumeroConcepto = $reporte->crearConceptosAdicionales($conceptos_adicionales, $cliente_por_factura, $anexos_por_factura, $zonas_por_factura, $cc_por_factura, $localidad_por_factura, $iva, $NumeroFacturas, $NumeroConcepto, $procesadosSeparado, $dividir_lecturas);
                                            $fields = $reporte->getFields();
                                            $procesadosSeparado = true;
                                            $cliente_por_factura = $reporte->ponerValorUno($cliente_por_factura);
                                            $anexos_por_factura = $reporte->ponerValorUno($anexos_por_factura);
                                            $zonas_por_factura = $reporte->ponerValorUno($zonas_por_factura);
                                            $cc_por_factura = $reporte->ponerValorUno($cc_por_factura);
                                            $localidad_por_factura = $reporte->ponerValorUno($localidad_por_factura);
                                        }
                                        $reporte->setFields($fields);
                                        $NumeroConcepto = $reporte->imprimirTablaTotalAgrupacion($costoTotalPorGrupo + $reporte->getTotalConceptosAdicionales(), $contadorBNServicio, $contadorColorServicio, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProBN, $costoProColor, $particularServicio, $excedenteBNServicio, $excedenteColorServicio, $iva, $idServicioByKServicio, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $mostrarDetalleServicio, $incluidosBNServicio, $incluidosColorServicio, $MostrarEquipos, $imprimir_cero, $agrupar_color, $NumeroConceptosColor);
                                        $fields = $reporte->getFields();

                                        echo "<input type='hidden' id='conceptos_factura_$NumeroFacturas' name='conceptos_factura_$NumeroFacturas' value='$NumeroConcepto'/>";
                                        if (isset($_POST['atm_post'])) {
                                            $fields['conceptos_factura_' . $NumeroFacturas] = urlencode($NumeroConcepto);
                                        }
                                        if ($reporte->getHayConceptosSeparados()) {
                                            $NumeroFacturas++;
                                        }
                                        if ($dividir_lecturas && $MostrarEquipos) {//Si se dividio la factura, tenemos que guardar el numero de componentes de la factura de renta
                                            echo "<input type='hidden' id='conceptos_factura_" . ($NumeroFacturas + 1) . "' "
                                            . "name='conceptos_factura_" . ($NumeroFacturas + 1) . "' value='$NumeroRentaPorServicio'/>";
                                            if (isset($_POST['atm_post'])) {
                                                $fields['conceptos_factura_' . ($NumeroFacturas + 1)] = urlencode($NumeroRentaPorServicio);
                                            }
                                        } else if ($agrupar_color && $MostrarEquipos) {

                                            $NumeroConceptosColor = $reporte->getNumeroConceptosColor();
                                            echo "<input type='hidden' id='conceptos_factura_" . ($NumeroFacturas + 1) . "' "
                                            . "name='conceptos_factura_" . ($NumeroFacturas + 1) . "' value='$NumeroConceptosColor'/>";
                                            if (isset($_POST['atm_post'])) {
                                                $fields['conceptos_factura_' . ($NumeroFacturas + 1)] = urlencode($NumeroConceptosColor);
                                            }
                                        }
                                        echo "</table><br/>";
                                        $todo_cerrado = true;
                                        echo "<br/><br/>";

                                        if (!empty($idBitacoras)) {
                                            $idBitacoras = substr($idBitacoras, 0, strlen($idBitacoras) - 1);
                                            echo "<input type='hidden' id='bitacoras_factura_" . $NumeroFacturas . "' name='bitacoras_factura_" . $NumeroFacturas . "' 
                                        value='$idBitacoras'/>";
                                            if (isset($_POST['atm_post'])) {
                                                $fields['bitacoras_factura_' . ($NumeroFacturas)] = urlencode($idBitacoras);
                                            }
                                        }

                                        if (!empty($idBitacorasColor)) {
                                            $idBitacorasColor = substr($idBitacorasColor, 0, strlen($idBitacorasColor) - 1);
                                            echo "<input type='hidden' id='bitacorascolor_factura_" . $NumeroFacturas . "' name='bitacorascolor_factura_" . $NumeroFacturas . "' 
                                        value='$idBitacorasColor'/>";
                                            if (isset($_POST['atm_post'])) {
                                                $fields['bitacorascolor_factura_' . ($NumeroFacturas)] = urlencode($idBitacorasColor);
                                            }
                                        }

                                        if (!empty($idBitacorasRenta)) {
                                            $idBitacorasRenta = substr($idBitacorasRenta, 0, strlen($idBitacorasRenta) - 1);
                                            echo "<input type='hidden' id='bitacorasrenta_factura_" . $NumeroFacturas . "' name='bitacorasrenta_factura_" . $NumeroFacturas . "' 
                                        value='$idBitacorasRenta'/>";
                                            if (isset($_POST['atm_post'])) {
                                                $fields['bitacorasrenta_factura_' . ($NumeroFacturas)] = urlencode($idBitacorasRenta);
                                            }
                                        }

                                        /* Reiniciamos las variables necesarias para una nueva factura */
                                        $costoTotalPorGrupo = 0;
                                        $contadorBNServicio = array();
                                        $contadorColorServicio = array();
                                        $costoRentaServicio = array();
                                        $costoExcedentesBN = array();
                                        $costoExcedentesColor = array();
                                        $costoProBN = array();
                                        $costoProColor = array();
                                        $particularServicio = array();
                                        $excedenteBNServicio = array();
                                        $excedenteColorServicio = array();
                                        $incluidosBNServicio = array();
                                        $incluidosColorServicio = array();
                                        $seriesServicio = array();
                                        $impresionesBNPorServicio = 0;
                                        $impresionesColorPorServicio = 0;
                                        $equiposPorServicio = 0;
                                        $idBitacoras = "";
                                        $idBitacorasColor = "";
                                        $idBitacorasRenta = "";
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

                                while (!isset($rs[$variable_rs_cambio])) {//Cuando se termina un tipo de servicio e inicia otro
                                    if (!$nueva_hoja) {/* Cerramos la ultima hoja abierta */
                                        /* Si se quiere imprimir la fila de informacion detallada por servicio */
                                        if ($IdKServicioAnterior != $rs['IdKServicio' . $prefijos[$prefijo]]) {
                                            if ($mostrarDetalleServicio) {
                                                $reporte->setFields($fields);

                                                $NumeroConcepto = $reporte->imprimirDetalleServicio($IdKServicioAnterior, $idServicioAnterior, $prefijoAnterior, $impresionesBNPorServicio, $impresionesColorPorServicio, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProBN, $costoProColor, $particularServicio, $iva, $excedenteBNServicio, $excedenteColorServicio, $equiposPorServicio, $NumeroFacturas, $NumeroConcepto, $unidad_servicio, $prefijos, $incluidosBNServicio, $incluidosColorServicio, $mostrarRenta, $encabezado_variable, $incluidosColor, $incluidosBN, $imprimir_cero, $dividir_lecturas, $MostrarSeries, $seriesServicio, $MostrarModelo, $agrupar_color, $NumeroConceptosColor);
                                                $fields = $reporte->getFields();
                                                if ($IdKServicioAnterior != 0) {
                                                    $impresionesBNPorServicio = 0;
                                                    $impresionesColorPorServicio = 0;
                                                    $equiposPorServicio = 0;
                                                }
                                            } else if ($mostrarDetalleLocalidad || $mostrarDetalleCC || $mostrarDetalleZona) {
                                                $reporte->setFields($fields);

                                                $NumeroConcepto = $reporte->imprimirDetalle($nombreAgrupaciones, $esParticularAgrupacion, $idServicioByKServicio, $incluidosBNAgrupacion, $incluidosColorAgrupacion, $equiposPorAgrupacion, $impresionesBNAgrupacion, $impresionesColorAgrupacion, $excedenteBNAgrupacion, $excedenteColorAgrupacion, $rentaAgrupacion, $costoExcedentesBNAgrupacion, $costoExcedentesColorAgrupacion, $costoProcesadasBNAgrupacion, $costoProcesadasColorAgrupacion, $iva, $mostrarRenta, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $imprimir_cero, $dividir_lecturas, $MostrarSeries, $seriesPorAgrupacion, $MostrarModelo, $agrupar_color, $NumeroConceptosColor);
                                                $fields = $reporte->getFields();
                                            }
                                        }

                                        /* Guardamos el concepto de la renta */
                                        $particularAnterior = $reporte->isParticularByPrefijo($reporte->obtenerIdPrefijo($prefijoAnterior, $prefijos));
                                        if (!$mostrarDetalleServicio && !$mostrarDetalleLocalidad && !$mostrarDetalleCC && !$mostrarDetalleZona) {
                                            if (!$particularAnterior) {
                                                $um = $reporte->getUnidadMedida($idServicioAnterior, "Renta", $unidad_servicio);
                                                if ($dividir_lecturas) {
                                                    $auxFactura = $NumeroFacturas + 1;
                                                    $auxConcepto = $NumeroRentaPorServicio++;
                                                } else {
                                                    $auxFactura = $NumeroFacturas;
                                                    $auxConcepto = $NumeroConcepto++;
                                                }
                                                $reporte->setFields($fields);
                                                $reporte->crearConceptoRentaFactura($particularAnterior, $equiposPorServicio, $um, $descripcion_renta, $IdKServicioAnterior, $costoRentaServicio, $idServicioByKServicio, $auxFactura, $auxConcepto);
                                                $fields = $reporte->getFields();
                                            }
                                        }
                                        $descripcion_renta = "";
                                        if (!empty($conceptos_adicionales)) {
                                            $reporte->setFields($fields);

                                            $NumeroConcepto = $reporte->crearConceptosAdicionales($conceptos_adicionales, $cliente_por_factura, $anexos_por_factura, $zonas_por_factura, $cc_por_factura, $localidad_por_factura, $iva, $NumeroFacturas, $NumeroConcepto, $procesadosSeparado, $dividir_lecturas);
                                            $fields = $reporte->getFields();
                                            $procesadosSeparado = true;
                                            $cliente_por_factura = $reporte->ponerValorUno($cliente_por_factura);
                                            $anexos_por_factura = $reporte->ponerValorUno($anexos_por_factura);
                                            $zonas_por_factura = $reporte->ponerValorUno($zonas_por_factura);
                                            $cc_por_factura = $reporte->ponerValorUno($cc_por_factura);
                                            $localidad_por_factura = $reporte->ponerValorUno($localidad_por_factura);
                                        }
                                        $reporte->setFields($fields);
                                        $NumeroConcepto = $reporte->imprimirTablaTotalAgrupacion($costoTotalPorGrupo + $reporte->getTotalConceptosAdicionales(), $contadorBNServicio, $contadorColorServicio, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProBN, $costoProColor, $particularServicio, $excedenteBNServicio, $excedenteColorServicio, $iva, $idServicioByKServicio, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $mostrarDetalleServicio, $incluidosBNServicio, $incluidosColorServicio, $MostrarEquipos, $imprimir_cero, $agrupar_color, $NumeroConceptosColor);
                                        $fields = $reporte->getFields();

                                        echo "<input type='hidden' id='conceptos_factura_$NumeroFacturas' name='conceptos_factura_$NumeroFacturas' value='$NumeroConcepto'/>";
                                        if (isset($_POST['atm_post'])) {
                                            $fields['conceptos_factura_' . ($NumeroFacturas)] = urlencode($NumeroConcepto);
                                        }
                                        if ($reporte->getHayConceptosSeparados()) {
                                            $NumeroFacturas++;
                                        }
                                        if ($dividir_lecturas && $MostrarEquipos) {//Si se dividio la factura, tenemos que guardar el numero de componentes de la factura de renta
                                            echo "<input type='hidden' id='conceptos_factura_" . ($NumeroFacturas + 1) . "' "
                                            . "name='conceptos_factura_" . ($NumeroFacturas + 1) . "' value='$NumeroRentaPorServicio'/>";
                                            if (isset($_POST['atm_post'])) {
                                                $fields['conceptos_factura_' . ($NumeroFacturas + 1)] = urlencode($NumeroRentaPorServicio);
                                            }
                                        } else if ($agrupar_color && $MostrarEquipos) {
                                            $NumeroConceptosColor = $reporte->getNumeroConceptosColor();

                                            echo "<input type='hidden' id='conceptos_factura_" . ($NumeroFacturas + 1) . "' "
                                            . "name='conceptos_factura_" . ($NumeroFacturas + 1) . "' value='$NumeroConceptosColor'/>";
                                            if (isset($_POST['atm_post'])) {
                                                $fields['conceptos_factura_' . ($NumeroFacturas + 1)] = urlencode($NumeroConceptosColor);
                                            }
                                        }

                                        echo "</table><br/>";


                                        if (!empty($idBitacoras)) {
                                            $idBitacoras = substr($idBitacoras, 0, strlen($idBitacoras) - 1);
                                            echo "<input type='hidden' id='bitacoras_factura_" . $NumeroFacturas . "' name='bitacoras_factura_" . $NumeroFacturas . "' 
                                        value='$idBitacoras'/>";
                                            if (isset($_POST['atm_post'])) {
                                                $fields['bitacoras_factura_' . $NumeroFacturas] = urlencode($idBitacoras);
                                            }
                                        }

                                        if (!empty($idBitacorasColor)) {
                                            $idBitacorasColor = substr($idBitacorasColor, 0, strlen($idBitacorasColor) - 1);
                                            echo "<input type='hidden' id='bitacorascolor_factura_" . $NumeroFacturas . "' name='bitacorascolor_factura_" . $NumeroFacturas . "' 
                                        value='$idBitacorasColor'/>";
                                            if (isset($_POST['atm_post'])) {
                                                $fields['bitacorascolor_factura_' . ($NumeroFacturas)] = urlencode($idBitacorasColor);
                                            }
                                        }

                                        if (!empty($idBitacorasRenta)) {
                                            $idBitacorasRenta = substr($idBitacorasRenta, 0, strlen($idBitacorasRenta) - 1);
                                            echo "<input type='hidden' id='bitacorasrenta_factura_" . $NumeroFacturas . "' name='bitacorasrenta_factura_" . $NumeroFacturas . "' 
                                        value='$idBitacorasRenta'/>";
                                            if (isset($_POST['atm_post'])) {
                                                $fields['bitacorasrenta_factura_' . ($NumeroFacturas)] = urlencode($idBitacorasRenta);
                                            }
                                        }

                                        $costoTotal = 0;
                                        echo "<br/><br/>";
                                        $costoTotalPorGrupo = 0;
                                        $contadorBNServicio = array();
                                        $contadorColorServicio = array();
                                        $costoRentaServicio = array();
                                        $costoExcedentesBN = array();
                                        $costoExcedentesColor = array();
                                        $costoProBN = array();
                                        $costoProColor = array();
                                        $particularServicio = array();
                                        $excedenteBNServicio = array();
                                        $excedenteColorServicio = array();
                                        $incluidosBNServicio = array();
                                        $incluidosColorServicio = array();
                                        $seriesServicio = array();
                                        $idBitacoras = "";
                                        $idBitacorasColor = "";
                                        $idBitacorasRenta = "";
                                        ?>
                                        <div style="page-break-after: always;"></div>
                                        <?php
                                    }
                                    $prefijo++;
                                    $no_equipo = 1;
                                    $nueva_hoja = true; //Si se quieren varias hojas, aqui tendria que ser true
                                    if ($prefijo >= count($prefijos)) {
                                        $prefijo = 0;
                                        break 1;
                                    }

                                    //Vemos como se va a agrupar los datos segun los parametros
                                    if ($agrupar_servicio) {
                                        $variable_rs_cambio = 'IdKServicio' . $prefijos[$prefijo]; //Cada que cambie esta variable, se creara una hoja                                
                                    } else if ($agrupar_tipo_servicio) {
                                        $variable_rs_cambio = 'IdServicio' . $prefijos[$prefijo]; //Cada que cambie esta variable, se creara una hoja                                
                                    }/* else if($agrupar_color){
                                      $variable_rs_cambio = "isColor";
                                      } */ else if ($agrupar_localidad) {
                                        $variable_rs_cambio = "ClaveCentroCosto"; //Cada que cambie esta variable, se creara una hoja                                
                                    } else if ($agrupar_cc) {
                                        $variable_rs_cambio = 'idCen_Costo'; //Cada que cambie esta variable, se creara una hoja                                
                                    } else if ($agrupar_zona) {
                                        $variable_rs_cambio = "ClaveZona";
                                    } else if ($agrupar_todo) {
                                        $variable_rs_cambio = "Junto";
                                    } else {
                                        $variable_rs_cambio = "NoSerie";
                                    }

                                    $IdKServicioAnterior = "0";
                                    $idServicioAnterior = "0";
                                }

                                /* Obtenemos el prefijo actual */
                                for ($i = 0; $i < count($prefijos); $i++) {
                                    if (isset($rs['IdKServicio' . $prefijos[$i]])) {
                                        $prefijo = $i;
                                        break;
                                    } else {
                                        $prefijo = 0;
                                    }
                                }
                                $particular = $reporte->isParticularByPrefijo($prefijo);
                                $idServicio = $rs['IdServicio' . $prefijos[$prefijo]];
                                //Dependiendo del servicio, es lo que se va a cobrar
                                $servicio_general = new ServicioGeneral();

                                if ($servicio_general->getCobranzasByTipoServicio($idServicio)) {
                                    $cobrarRenta = $servicio_general->getCobrarRenta();
                                    $cobrarExcedenteBN = $servicio_general->getCobrarExcedenteBN();
                                    $cobrarExcedenteColor = $servicio_general->getCobrarExcedenteColor();
                                    $cobrarProcesadasBN = $servicio_general->getCobrarProcesadasBN();
                                    $cobrarProcesadasColor = $servicio_general->getCobrarProcesadasColor();
                                } else {
                                    $cobrarRenta = false;
                                    $cobrarExcedenteBN = false;
                                    $cobrarExcedenteColor = false;
                                    $cobrarProcesadasBN = false;
                                    $cobrarProcesadasColor = false;
                                }

                                /* Obtenemos los costos por servicio si aun no estan registrados */
                                if (!isset($particularServicio[$rs['IdKServicio' . $prefijos[$prefijo]]])) {
                                    $idServicioByKServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = $idServicio;
                                    if ($particular) {
                                        $particularServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = 1;
                                    } else {
                                        $particularServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = 0;
                                    }
                                    $seriesServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = "";
                                    if ($cobrarRenta) {
                                        $costoRentaServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = $rs[$prefijos[$prefijo] . 'Renta'];
                                    }
                                    if ($cobrarExcedenteBN) {
                                        $costoExcedentesBN[$rs['IdKServicio' . $prefijos[$prefijo]]] = $rs[$prefijos[$prefijo] . 'ExcedentesBN'];
                                    }
                                    if ($cobrarExcedenteColor) {
                                        $costoExcedentesColor[$rs['IdKServicio' . $prefijos[$prefijo]]] = $rs[$prefijos[$prefijo] . 'ExcedentesColor'];
                                    }
                                    if ($cobrarProcesadasBN) {
                                        $costoProBN[$rs['IdKServicio' . $prefijos[$prefijo]]] = $rs[$prefijos[$prefijo] . 'ProcesadasBN'];
                                    }
                                    if ($cobrarProcesadasColor) {
                                        $costoProColor[$rs['IdKServicio' . $prefijos[$prefijo]]] = $rs[$prefijos[$prefijo] . 'ProcesadosColor'];
                                    }
                                    $incluidosBNServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = $rs[$prefijos[$prefijo] . 'incluidosBN'];
                                    $incluidosColorServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = $rs[$prefijos[$prefijo] . 'incluidosColor'];
                                }

                                if ($FacturaAdelantada) {//Si se cobra la factura adelantada, se pone como fecha de facturacion el mes siguientes
                                    if ($month == "12") {
                                        echo "<input type='hidden' id='periodo_facturacion' name='periodo_facturacion' value='" . ($year + 1) . "-01-01'/>";
                                        if (isset($_POST['atm_post'])) {
                                            $fields['periodo_facturacion'] = urlencode(($year + 1) . "-01-01");
                                        }
                                    } else {
                                        echo "<input type='hidden' id='periodo_facturacion' name='periodo_facturacion' value='" . ($year) . "-" . ($month + 1) . "-01'/>";
                                        if (isset($_POST['atm_post'])) {
                                            $fields['periodo_facturacion'] = urlencode(($year) . "-" . ($month + 1) . "-01");
                                        }
                                    }
                                } else {
                                    echo "<input type='hidden' id='periodo_facturacion' name='periodo_facturacion' value='$year-$month-01'/>";
                                    if (isset($_POST['atm_post'])) {
                                        $fields['periodo_facturacion'] = urlencode($year . "-" . $month . "-01");
                                    }
                                }

                                $mes_actual = $meses[$month - 1];
                                $hay_lectura = false;
                                $hay_lectura_anterior = false;
                                /* Datos del equipo */
                                //Lecturas del mes actual
                                if ($reporte->getLecturaMesActualCorte($rs['NoSerie'], $month, $year)) {
                                    $hay_lectura = true;
                                    /* if($reporte->getLecturaMesActual($rs['NoSerie'],$month,$year,$rs['ClaveCliente'])==null){
                                      $hay_lectura = false;
                                      } */
                                } else {
                                    $faltanLecturasActual = true;
                                    $textoSeriesActuales.= $rs['NoSerie'] . ", ";
                                }

                                //Lecturas del mes anterior, para poder sacara la diferencia de impresiones
                                $month_aux = $month;
                                $year_aux = $year;
                                if ($month != "1") {
                                    $month_aux--;
                                } else {
                                    $month_aux = "12";
                                    $year_aux--;
                                }
                                $mes_anterior = $meses[$month_aux - 1];

                                if ($reporte->getLecturaMesAnteriorCorte($rs['NoSerie'], $month_aux, $year_aux)) {
                                    $hay_lectura_anterior = true;
                                    /* $tipo = $reporte->getLecturasSinMarcaMesAnterior($rs['NoSerie'],$month,$year,$rs['ClaveCliente']);
                                      if(isset($tipo) && $tipo == "0" || $tipo == "1"){
                                      $hay_lectura_anterior = true;
                                      } */
                                } else {
                                    $faltanLecturasMesAnterior = true;
                                    $textoSeriesMesAnterior .= $rs['NoSerie'] . ", ";
                                }

                                //Saber si el equipo es de color o b/n
                                $color = $caracteristicas->isColor($rs['NoParteEquipo']);
                                //Saber si el equipo es formato amplio o no                        
                                $fa = $caracteristicas->isFormatoAmplio($rs['NoParteEquipo']);

                                if ($nueva_hoja) { /* Iniciamos nueva hoja (factura) */
                                    $todo_cerrado = false;
                                    //Reiniciamos las variables.                                                                               
                                    $incluidosBN = 0;
                                    $incluidosColor = 0;
                                    $excedentesBN = 0;
                                    $excedentesColor = 0;
                                    $procesadosBN = 0;
                                    $procesadosColor = 0;
                                    $excedentes_bn_por_equipo = array();
                                    $excedentes_color_por_equipo = array();
                                    /*                                     * *************************     CREAMOS LOS ENCABEZADOS    ************************** */
                                    $NumeroFacturas++;
                                    if ($dividir_lecturas && $variable_cambio_anterior != 0) {//Si se dividen las facturas, se aumenta otra factura por que se crearon dos.
                                        $NumeroFacturas++;
                                    } else if ($agrupar_color && $variable_cambio_anterior != 0) {
                                        $NumeroFacturas++;
                                    }

                                    $NumeroConcepto = 0;
                                    $NumeroRentaPorServicio = 0;
                                    $NumeroConceptosColor = 0; //Aumentamos el identificador de la factura a crear, y reiniciamos el contador de conceptos por factura
                                    $localidadAnterior = "";
                                    $NombreLocalidadAnterior = "";
                                    $equiposPorServicio = 0;
                                    $nombreAgrupaciones = array();
                                    $rentaAgrupacion = array();
                                    $esParticularAgrupacion = array();
                                    $impresionesBNAgrupacion = array();
                                    $impresionesColorAgrupacion = array();
                                    $costoExcedentesBNAgrupacion = array();
                                    $costoExcedentesColorAgrupacion = array();
                                    $incluidosBNAgrupacion = array();
                                    $incluidosColorAgrupacion = array();
                                    $costoProcesadasBNAgrupacion = array();
                                    $costoProcesadasColorAgrupacion = array();
                                    $equiposPorAgrupacion = array();
                                    $excedenteBNAgrupacion = array();
                                    $excedenteColorAgrupacion = array();
                                    $seriesPorAgrupacion = array();
                                    if (!isset($_POST['independiente'])) {
                                        echo "<table style='min-width: 95%;'><tr><td>";
                                        echo "<table>";
                                        echo "<tr>";
                                        if (isset($rs['ImagenPHP']) && $rs['ImagenPHP'] != "") {
                                            if (!isset($_POST['independiente'])) {
                                                echo "<td colspan='2'><img src='../" . $rs['ImagenPHP'] . "'/><td>";
                                            } else {
                                                echo "<td colspan='2'><img src='" . $rs['ImagenPHP'] . "'/><td>";
                                            }
                                        }
                                        echo "</tr>";
                                        if (isset($facturarA) && $facturarA != "") {
                                            echo "<tr><td>FACTURA A: </td><td><b>" . $facturarA . "</b></td></tr>";
                                        }
                                        echo "<tr><td>CLIENTE: </td><td><b>" . $obj_cliente->getNombreRazonSocial() . "</b></td></tr>";
                                        if ($value == 1) {//Si se está procesando
                                            $obj_direccion->getLocalidadByClaveTipo($rs['ClaveCentroCosto'], "5");
                                            echo "<input type='hidden' id='id_domicilio_$NumeroFacturas' name='id_domicilio_$NumeroFacturas' value='" . $obj_direccion->getIdDomicilio() . "' />";
                                            if (isset($_POST['atm_post'])) {
                                                $fields['id_domicilio_' . $NumeroFacturas] = urlencode($obj_direccion->getIdDomicilio());
                                            }
                                        }
                                        echo "<tr><td colspan='2'>DIRECCION: " . $obj_direccion->getCalle() . " No. Ext: " . $obj_direccion->getNoExterior() . " 
                                    No. Int: " . $obj_direccion->getNoInterior() . "<br/>" . $obj_direccion->getCodigoPostal() . " " .
                                        $obj_direccion->getCiudad() . " Delegación " . $obj_direccion->getDelegacion() . "</td></tr>";
                                        echo "<tr><td style='vertical-align:top;'>CONTACTO: </td><td>" . $obj_contacto->getNombre() . "<br/>Tel. " . $obj_contacto->getTelefono() . "<br/>" . $obj_contacto->getCorreoElectronico() . "</td></tr>";
                                         echo "<tr><td style='vertical-align:top;'>CONTRATO: </td><td>" . $contrato . "</td></tr>";
                                         echo "<tr><td style='vertical-align:top;'>ANEXO: </td><td>" . $anexo . "</td></tr>";
                                        echo "</table></td>";
                                        echo "<td><table>";
                                    }
                                    //echo "<tr><td>ZONA CLIENTE: <b>".$obj_zona->getNombre()."</b></td></tr>";

                                    if (isset($rs[$prefijos[$prefijo] . 'incluidosBN']) && $rs[$prefijos[$prefijo] . 'incluidosBN'] != "") {
                                        $incluidosBN = $rs[$prefijos[$prefijo] . 'incluidosBN'];
                                    } else {
                                        $incluidosBN = 0;
                                    }

                                    //echo "<tr><td>Incluye ".number_format($incluidosBN, 0, '.', ',')." ".$prefijo_pagml[$prefijo]." BN</td></tr>";
                                    if (isset($rs[$prefijos[$prefijo] . 'incluidosColor']) && $rs[$prefijos[$prefijo] . 'incluidosColor'] != "") {
                                        $incluidosColor = $rs[$prefijos[$prefijo] . 'incluidosColor'];
                                    } else {
                                        $incluidosColor = 0;
                                    }

                                    //echo "<tr><td>Incluye ".number_format($incluidosColor, 0, '.', ',')." ".$prefijo_pagml[$prefijo]." de color</td></tr>";
                                    if (isset($rs[$prefijos[$prefijo] . 'ExcedentesBN']) && $rs[$prefijos[$prefijo] . 'ExcedentesBN'] != "") {
                                        $excedentesBN = $rs[$prefijos[$prefijo] . 'ExcedentesBN'];
                                    } else {
                                        $excedentesBN = 0;
                                    }
                                    if ($cobrarExcedenteBN) {
                                        //echo "<tr><td>Costo por ".$prefijo_pagml[$prefijo]." BN excedente ".$excedentesBN."</td></tr>";
                                    }

                                    if (isset($rs[$prefijos[$prefijo] . 'ExcedentesColor']) && $rs[$prefijos[$prefijo] . 'ExcedentesColor'] != "") {
                                        $excedentesColor = $rs[$prefijos[$prefijo] . 'ExcedentesColor'];
                                    } else {
                                        $excedentesColor = 0;
                                    }

                                    if ($cobrarExcedenteColor) {
                                        //echo "<tr><td>Costo por ".$prefijo_pagml[$prefijo]." Color excedente ".$excedentesColor."</td></tr>";
                                    }

                                    if ($cobrarProcesadasBN) {
                                        if (isset($rs[$prefijos[$prefijo] . 'ProcesadasBN']) && $rs[$prefijos[$prefijo] . 'ProcesadasBN'] != "") {
                                            $procesadosBN = $rs[$prefijos[$prefijo] . 'ProcesadasBN'];
                                        } else {
                                            $procesadosBN = 0;
                                        }
                                        //echo "<tr><td>Costo por ".$prefijo_pagml[$prefijo]." BN procesados ".$procesadosBN."</td></tr>";
                                    }

                                    if ($cobrarProcesadasColor) {
                                        if (isset($rs[$prefijos[$prefijo] . 'ProcesadosColor']) && $rs[$prefijos[$prefijo] . 'ProcesadosColor'] != "") {
                                            $procesadosColor = $rs[$prefijos[$prefijo] . 'ProcesadosColor'];
                                        } else {
                                            $procesadosColor = 0;
                                        }
                                        //echo "<tr><td>Costo por ".$prefijo_pagml[$prefijo]." Color procesados ".$procesadosColor."</td></tr>";
                                    }

                                    if ($cobrarRenta) {
                                        //echo "<tr><td><b>Renta mensual: ".number_format($rs[$prefijos[$prefijo]."Renta"], 2, '.', ',')."</b></td></tr>";                            
                                    }

                                    if (!isset($encabezado_variable[$idServicio])) {
                                        $encabezado_servicio = $rs["Nombre" . $prefijos[$prefijo]];
                                    } else {
                                        $encabezado_servicio = str_replace("__0", "", $encabezado_variable[$idServicio]);
                                    }

                                    echo "</table></td>";
                                    echo "</tr></table>";
                                    echo "<br/><br/>";
                                    echo "<table class='borde' style='width:100%;'>";
                                    echo "<tr><td class='borde'>No.</td><td class='borde'>Localidad</td><td class='borde'>Modelo</td><td class='borde'>No. Serie</td>";
                                    if ($mostrarContadores) {
                                        echo "<td class='borde'>$resaltado_i B&N [$mes_anterior]$resaltado_f</td><td class='borde'>$resaltado_i Color [$mes_anterior]$resaltado_f</td>" .
                                        "<td class='borde'>$resaltado_i B&N [$mes_actual]$resaltado_f</td><td class='borde'>$resaltado_i Color [$mes_actual]$resaltado_f</td>" .
                                        "<td class='borde'>Impresiones B&N</td><td class='borde'>Impresiones Color</td>";
                                        echo "<td class='borde' class='excedente'>Excedentes B&N</td><td class='borde' class='excedente'>Excedentes Color</td>";
                                    }
                                    echo "<td class='borde'>Subtotal</td><td class='borde'>IVA</td><td class='borde'>Total</td>";

                                    if ($MostrarUbicacion) {
                                        echo "<td class='borde'>Ubicación</td>";
                                    }
                                    if ($MostrarDireccion) {
                                        echo "<td class='borde'>Dirección</td>";
                                    }
                                    echo "</tr>";

                                    $IdKServicioAnterior = 0;
                                    $idServicioAnterior = 0;
                                } else {  //Sino es nueva factura el equipo procesado actual                                                      
                                    if (isset($rs[$prefijos[$prefijo] . 'incluidosBN']) && $rs[$prefijos[$prefijo] . 'incluidosBN'] != "") {
                                        $incluidosBN = $rs[$prefijos[$prefijo] . 'incluidosBN'];
                                    } else {
                                        $incluidosBN = 0;
                                    }

                                    if (isset($rs[$prefijos[$prefijo] . 'incluidosColor']) && $rs[$prefijos[$prefijo] . 'incluidosColor'] != "") {
                                        $incluidosColor = $rs[$prefijos[$prefijo] . 'incluidosColor'];
                                    } else {
                                        $incluidosColor = 0;
                                    }

                                    if (isset($rs[$prefijos[$prefijo] . 'ExcedentesBN']) && $rs[$prefijos[$prefijo] . 'ExcedentesBN'] != "") {
                                        $excedentesBN = $rs[$prefijos[$prefijo] . 'ExcedentesBN'];
                                    } else {
                                        $excedentesBN = 0;
                                    }

                                    if (isset($rs[$prefijos[$prefijo] . 'ExcedentesColor']) && $rs[$prefijos[$prefijo] . 'ExcedentesColor'] != "") {
                                        $excedentesColor = $rs[$prefijos[$prefijo] . 'ExcedentesColor'];
                                    } else {
                                        $excedentesColor = 0;
                                    }

                                    if ($cobrarProcesadasBN) {
                                        if (isset($rs[$prefijos[$prefijo] . 'ProcesadasBN']) && $rs[$prefijos[$prefijo] . 'ProcesadasBN'] != "") {
                                            $procesadosBN = $rs[$prefijos[$prefijo] . 'ProcesadasBN'];
                                        } else {
                                            $procesadosBN = 0;
                                        }
                                    }

                                    if ($cobrarProcesadasColor) {
                                        if (isset($rs[$prefijos[$prefijo] . 'ProcesadosColor']) && $rs[$prefijos[$prefijo] . 'ProcesadosColor'] != "") {
                                            $procesadosColor = $rs[$prefijos[$prefijo] . 'ProcesadosColor'];
                                        } else {
                                            $procesadosColor = 0;
                                        }
                                    }

                                    if (!isset($encabezado_variable[$idServicio])) {
                                        $encabezado_servicio = $rs["Nombre" . $prefijos[$prefijo]];
                                    } else {
                                        $encabezado_servicio = str_replace("__0", "", $encabezado_variable[$idServicio]);
                                    }
                                }

                                $contadorBN = 0;
                                $contadorColor = 0;
                                $contadorBNAnterior = 0;
                                $contadorColorAnterior = 0;

                                if (!$color) {/* Si el equipo es blanco y negro */
                                    if (!$fa) {/* Si el equipo no es de formato amplio (es decir que es impresora) */
                                        $contadorBN = $reporte->getContadorBNPagina();
                                        $contadorBNAnterior = $reporte->getContadorBNPaginaAnterior();
                                    } else {
                                        $contadorBN = $reporte->getContadorBNML();
                                        $contadorBNAnterior = $reporte->getContadorBNMLAnterior();
                                    }
                                } else {/* Si el equipo es color */
                                    if (!$fa) {/* Si el equipo no es de formato amplio (es decir que es impresora) */
                                        $contadorBN = $reporte->getContadorBNPagina();
                                        $contadorColor = $reporte->getContadorColorPagina();
                                        $contadorBNAnterior = $reporte->getContadorBNPaginaAnterior();
                                        $contadorColorAnterior = $reporte->getContadorColorPaginaAnterior();
                                    } else {
                                        $contadorBN = $reporte->getContadorBNML();
                                        $contadorColor = $reporte->getContadorColorML();
                                        $contadorBNAnterior = $reporte->getContadorBNMLAnterior();
                                        $contadorColorAnterior = $reporte->getContadorColorMLAnterior();
                                    }
                                }

                                /*                                 * ***********************************        Aqui se procesa la fila de cada tabla       ************************************ */
                                $resultMovimiento = $reporte->obtenerMovimientoPorFecha($rs['ClaveCliente'], $rs['NoSerie'], $month, $year);
                                if (mysql_num_rows($resultMovimiento) > 0) {//Si el equipo tuvo movimientos durante el mes de facturacion
                                    //echo "<br/>".$rs['NoSerie'].": ".$rs['Fecha'];
                                    //Agregar algoritmo para calcular las impresiones
                                    $cantidadInicial = $cantidadInicialColor = 0;
                                    $cantidadFinal = $cantidadFinalColor = 0;
                                    $quitar = $quitarColor = 0;
                                    $ult_reg = $ult_regColor = 0;
                                    $sin_regresar = true;

                                    $ult_reg = $cantidadInicial = $contadorBNAnterior;
                                    $ult_regColor = $cantidadInicialColor = $contadorColorAnterior;

                                    while ($rsMovimiento = mysql_fetch_array($resultMovimiento)) {
                                        if (!$fa) {
                                            $nvo_reg = $rsMovimiento['ContadorBNPaginas'];
                                            $nvo_regColor = $rsMovimiento['ContadorColorPaginas'];
                                        } else {
                                            $nvo_reg = $rsMovimiento['ContadorBNML'];
                                            $nvo_regColor = $rsMovimiento['ContadorColorML'];
                                        }
                                        if ($rsMovimiento['clave_cliente_nuevo']) {       //En el caso en que llego el equipo
                                            if ($nvo_reg - $ult_reg > 0 && $nvo_regColor - $ult_regColor >= 0 && $sin_regresar) {//Para evitar lecturas incoherentes                                        
                                                $quitar += $nvo_reg - $ult_reg;             //Se descuentan las impresiones desde la última lectura
                                                $quitarColor += $nvo_regColor - $ult_regColor;
                                                $ult_reg = $nvo_reg;
                                                $ult_regColor = $nvo_regColor;
                                            }
                                            $sin_regresar = false;                     //Indicamos que ya regreso el equipo
                                        } else {                                          //En el caso de que el equipo salga
                                            if ($nvo_reg >= $ult_reg) {
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
                                    if ($sin_regresar) {
                                        $diferencia_bn = $ult_reg - $cantidadInicial; //No se toma en cuenta la ultima lectura puesto que no ha regresado el equipo
                                        $diferencia_color = $ult_regColor - $cantidadInicialColor;
                                    } else {
                                        $diferencia_bn = $cantidadFinal - $cantidadInicial;
                                        $diferencia_color = $cantidadFinalColor - $cantidadInicialColor;
                                    }
                                    //echo "<br/>".$quitar." vs $quitarColor =  $diferencia_bn vs $diferencia_color";    
                                    $diferencia_bn -= $quitar;
                                    $diferencia_color -= $quitarColor;
                                    //echo "<br/>".$quitar." vs $quitarColor =  $diferencia_bn vs $diferencia_color";
                                } else {
                                    $diferencia_bn = (intval($contadorBN) - intval($contadorBNAnterior));
                                    $diferencia_color = (intval($contadorColor) - intval($contadorColorAnterior));
                                }

                                if ($diferencia_bn < 0) {
                                    $diferencia_bn = 0;
                                }
                                if ($diferencia_color < 0) {
                                    $diferencia_color = 0;
                                }
                                $des_aux = "";
                                if (true) {
                                    $des_aux .= "SERIE: " . $rs['NoSerie'];
                                    if($rs['IdTipoInventario'] == "8"){
                                        $des_aux .= " * En backup";
                                    }
                                }

                                /* if($MostrarLecturas){
                                  if(!empty($contadorBN)){
                                  $des_aux .= " LEC. ACT. ($year-$month): ".  number_format($contadorBN);
                                  }

                                  if(!empty($contadorBNAnterior)){
                                  $des_aux .= " LEC. ANT. ($year_aux-$month_aux): ".  number_format($contadorBNAnterior);
                                  }

                                  if(!empty($contadorColor)){
                                  $des_aux .= " LEC. ACT. COLOR($year-$month): ".  number_format($contadorColor);
                                  }

                                  if(!empty($contadorColorAnterior)){
                                  $des_aux .= " LEC. ANT. COLOR($year_aux-$month_aux): ".  number_format($contadorColorAnterior);
                                  }
                                  } */
                                if ($MostrarModelo) {
                                    $des_aux .= " MODELO:" . $rs['Modelo'];
                                }
                                if (!$muestra_serie && $MostrarUbicacion && $rs['Ubicacion'] != "") {
                                    $des_aux .= " UBICACIÓN: " . $rs['Ubicacion'];
                                }
                                if ($des_aux != "") {
                                    $des_aux .= ", ";
                                }
                                $seriesServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] .= ($des_aux);

                                /* Si se quiere imprimir la fila de informacion detallada por servicio */
                                if ($IdKServicioAnterior != 0 && $IdKServicioAnterior != $rs['IdKServicio' . $prefijos[$prefijo]]) {
                                    if ($mostrarEncabezadosServicio) {
                                        $reporte->setFields($fields);

                                        $NumeroConcepto = $reporte->imprimirEncabezadoServicio($encabezado_variable, $incluidosBN, $incluidosColor, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProBN, $costoProColor, $rs['IdKServicio' . $prefijos[$prefijo]], $rs['IdServicio' . $prefijos[$prefijo]], $agrupar_color, $NumeroFacturas, $NumeroConcepto);
                                        $fields = $reporte->getFields();
                                    }
                                    if ($mostrarDetalleServicio) {
                                        $reporte->setFields($fields);

                                        $NumeroConcepto = $reporte->imprimirDetalleServicio($IdKServicioAnterior, $idServicioAnterior, $prefijoAnterior, $impresionesBNPorServicio, $impresionesColorPorServicio, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProBN, $costoProColor, $particularServicio, $iva, $excedenteBNServicio, $excedenteColorServicio, $equiposPorServicio, $NumeroFacturas, $NumeroConcepto, $unidad_servicio, $prefijos, $incluidosBNServicio, $incluidosColorServicio, $mostrarRenta, $encabezado_variable, $incluidosColor, $incluidosBN, $imprimir_cero, $dividir_lecturas, $MostrarSeries, $seriesServicio, $MostrarModelo, $agrupar_color, $NumeroConceptosColor);
                                        $fields = $reporte->getFields();
                                    }

                                    /* Guardamos el concepto de la renta */
                                    $particularAnterior = $reporte->isParticularByPrefijo($reporte->obtenerIdPrefijo($prefijoAnterior, $prefijos));
                                    if (!$mostrarDetalleServicio && !$mostrarDetalleLocalidad && !$mostrarDetalleCC && !$mostrarDetalleZona) {
                                        if (!$particularAnterior) {
                                            $um = $reporte->getUnidadMedida($idServicioAnterior, "Renta", $unidad_servicio);
                                            if ($dividir_lecturas) {
                                                $auxFactura = $NumeroFacturas + 1;
                                                $auxConcepto = $NumeroRentaPorServicio++;
                                            } else {
                                                $auxFactura = $NumeroFacturas;
                                                $auxConcepto = $NumeroConcepto++;
                                            }
                                            $reporte->setFields($fields);
                                            $reporte->crearConceptoRentaFactura($particularAnterior, $equiposPorServicio, $um, $descripcion_renta, $IdKServicioAnterior, $costoRentaServicio, $idServicioByKServicio, $auxFactura, $auxConcepto);
                                            $fields = $reporte->getFields();
                                        }
                                    }
                                    $descripcion_renta = "   RENTA EQUIPO ";
                                    /* Reiniciamos las variables por servicio */
                                    $impresionesBNPorServicio = 0;
                                    $impresionesColorPorServicio = 0;
                                    $equiposPorServicio = 0;
                                } else if ($IdKServicioAnterior != $rs['IdKServicio' . $prefijos[$prefijo]]) {//Hay un cambio 
                                    if ($mostrarEncabezadosServicio) {
                                        $reporte->setFields($fields);

                                        $NumeroConcepto = $reporte->imprimirEncabezadoServicio($encabezado_variable, $incluidosBN, $incluidosColor, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProBN, $costoProColor, $rs['IdKServicio' . $prefijos[$prefijo]], $rs['IdServicio' . $prefijos[$prefijo]], $agrupar_color, $NumeroFacturas, $NumeroConcepto);
                                        $fields = $reporte->getFields();
                                    }
                                }

                                if ($diferencia_bn > 0) {
                                    $impresionesBNPorServicio += $diferencia_bn;
                                }
                                if ($diferencia_color > 0) {
                                    $impresionesColorPorServicio += $diferencia_color;
                                }
                                if($rs['IdTipoInventario'] != "8"){
                                    $equiposPorServicio++;
                                }                                

                                /* guardamos todos los clientes, anexos, zonas, centro de costos y localidad de la factura, para los conceptos adicionales */
                                if (!isset($cliente_por_factura[$rs['ClaveCliente']])) {
                                    $cliente_por_factura[$rs['ClaveCliente']] = 0;
                                }

                                if (!isset($anexos_por_factura[$rs['ClaveAnexoTecnico']])) {
                                    $anexos_por_factura[$rs['ClaveAnexoTecnico']] = 0;
                                }

                                if (!isset($zonas_por_factura[$rs['ClaveZona']]) && $rs['ClaveZona'] != "") {
                                    $zonas_por_factura[$rs['ClaveZona']] = 0;
                                }

                                if (!isset($cc_por_factura[$rs['idCen_Costo']]) && $rs['idCen_Costo'] != "") {
                                    $cc_por_factura[$rs['idCen_Costo']] = 0;
                                }

                                if (!isset($localidad_por_factura[$rs['ClaveCentroCosto']])) {
                                    $localidad_por_factura[$rs['ClaveCentroCosto']] = 0;
                                }

                                /* Descripcion por concepto de renta */
                                if ($rs['ClaveCentroCosto'] != $localidadAnterior) {
                                    $descripcion_renta .= "  RENTA EQUIPO ";
                                }
                                if ($MostrarModelo) {
                                    $descripcion_renta.= ("  MODELO: " . $rs['Modelo']);
                                }if (true) {
                                    $descripcion_renta .= (" SERIE: " . $rs['NoSerie']);
                                    if($rs['IdTipoInventario'] == "8"){
                                        $descripcion_renta .= " * En backup";
                                    }
                                }
                                /* if($MostrarLecturas){
                                  if(!empty($contadorBN)){
                                  $descripcion_renta .= " LEC. ACT. ($year-$month): ".  number_format($contadorBN);
                                  }

                                  if(!empty($contadorBNAnterior)){
                                  $descripcion_renta .= " LEC. ANT. ($year_aux-$month_aux): ".  number_format($contadorBNAnterior);
                                  }

                                  if(!empty($contadorColor)){
                                  $descripcion_renta .= " LEC. ACT. COLOR($year-$month): ".  number_format($contadorColor);
                                  }

                                  if(!empty($contadorColorAnterior)){
                                  $descripcion_renta .= " LEC. ANT. COLOR($year_aux-$month_aux): ".  number_format($contadorColorAnterior);
                                  }
                                  } */
                                if (!$muestra_serie && $MostrarUbicacion && $rs['Ubicacion'] != "") {
                                    $descripcion_renta .= " UBICACIÓN: " . $rs['Ubicacion'];
                                }
                                if ($MostrarEquipos) {
                                    $sin_servicio = "";
                                    if (!isset($rs['IdKServicio' . $prefijos[$prefijo]]) || $rs['IdKServicio' . $prefijos[$prefijo]] == "") {
                                        $sin_servicio = "<span style='color:red'> Sin servicio asignado</span>";
                                    }
                                    $backup = "";
                                    if($rs['IdTipoInventario'] == "8"){
                                        $backup = "<span style='color:red'> * En backup</span>";
                                    }
                                    echo "<tr>";
                                    echo "<td class='borde'>$no_equipo</td>";
                                    if (isset($rs['CentroCostoLocalidad'])) {
                                        echo "<td class='borde'>" . $rs['CentroCostoLocalidad'] . " - " . $rs['CentroCostoNombre'] . "</td>";
                                    } else {
                                        echo "<td class='borde'>" . $rs['CentroCostoNombre'] . "</td>";
                                    }
                                    echo "<td class='borde'>" . $rs['Modelo'] . "</td>";
                                    echo "<td class='borde'>" . $rs['NoSerie'] . "$sin_servicio $backup</td>";

                                    $reporte->imprimirContadores($contadorBNAnterior, $contadorColorAnterior, $contadorBN, $contadorColor);
                                    $reporte->imprimirExcedentesPorFila($diferencia_bn, $diferencia_color);
                                }

                                $aux = 0; //Son los excedentes por equipo bn
                                $aux1 = 0; //Excedentes por equipo color
                                if ($particular) {//Para los equipos en servicio particular, se calcula por fila los costos  
                                    if($rs['IdTipoInventario'] != "8"){
                                        $totalParticular = $reporte->calcularCostoParticularPorEquipo($cobrarRenta, $rs[$prefijos[$prefijo] . "Renta"], $incluidosBN, $cobrarExcedenteBN, $excedentesBN, $procesadosBN, $diferencia_bn, $diferencia_color, $incluidosColor, $cobrarExcedenteColor, $excedentesColor, $procesadosColor);
                                    }else{
                                        $totalParticular = 0;
                                    }

                                    $aux = $diferencia_bn - $incluidosBN;
                                    if ($aux < 0) {
                                        $aux = 0;
                                    }
                                    array_push($excedentes_bn_por_equipo, $aux);

                                    $aux1 = $diferencia_color - $incluidosColor;
                                    if ($aux1 < 0) {
                                        $aux1 = 0;
                                    }
                                    array_push($excedentes_color_por_equipo, $aux1);
                                    $costoTotalPorGrupo += $totalParticular;
                                    $iva_particular = $totalParticular * $iva;
                                    $totalParticular += $iva_particular;

                                    if ($MostrarEquipos) {
                                        if ($mostrarContadores) {
                                            echo "<td class='borde'>" . number_format($aux, 0, '.', ',') . "</td>";
                                            echo "<td class='borde'>" . number_format($aux1, 0, '.', ',') . "</td>";
                                        }
                                        echo "<td class='borde' style='text-align:right;'>$" . number_format($totalParticular - $iva_particular, 2, '.', ',') . "</td>"; //Subtotal
                                        echo "<td class='borde' style='text-align:right;'>$" . number_format($iva_particular, 2, '.', ',') . "</td>"; //IVA                            
                                        echo "<td class='borde' style='text-align:right;'>$" . number_format($totalParticular, 2, '.', ',') . "</td>"; //Total
                                        /* Creamos los campos para generar la factura */
                                        if (!$mostrarRenta) {//Si solo se muestra una fila por equipo ..
                                            $reporte->setFields($fields);
                                            if (!$MostrarLocalidad) {
                                                $concepto = "COSTO POR EQUIPO SERIE " . $rs['NoSerie'];
                                                if($rs['IdTipoInventario'] == "8"){
                                                    $concepto .= " * En backup";
                                                }
                                            } else {
                                                $concepto = $rs['CentroCostoNombre'] . " COSTO POR EQUIPO SERIE " . $rs['NoSerie'];
                                                if($rs['IdTipoInventario'] == "8"){
                                                    $concepto .= " * En backup";
                                                }
                                            }
                                            $reporte->crearConceptoFactura(1, "Servicio", $concepto, $totalParticular - $iva_particular, $totalParticular - $iva_particular, $NumeroFacturas, $NumeroConcepto, $IdProductoSATImpresion);
                                            $fields = $reporte->getFields();
                                            $NumeroConcepto++;
                                        } else {//Si se detalla por equipo
                                            if ($cobrarExcedenteBN) {
                                                $um = $reporte->getUnidadMedida($idServicio, "Excedentes", $unidad_servicio);
                                                $des_aux = "PAGINAS IMPRESAS NEGRO: $diferencia_bn INCLUYE ($incluidosBN)";
                                                if (true) {
                                                    $des_aux .= " SERIE: " . $rs['NoSerie'];
                                                    if($rs['IdTipoInventario'] == "8"){
                                                        $des_aux .= " * En backup";
                                                    }
                                                }
                                                if ($MostrarModelo) {
                                                    $des_aux .= " MODELO: " . $rs['Modelo'];
                                                }
                                                if (!$muestra_serie && $MostrarUbicacion && $rs['Ubicacion'] != "") {
                                                    $des_aux .= " UBICACIÓN: " . $rs['Ubicacion'];
                                                }
                                                $reporte->setFields($fields);

                                                $reporte->crearConceptoFactura($aux, $um, $des_aux, $excedentesBN, $excedentesBN * $aux, $NumeroFacturas, $NumeroConcepto, $IdProductoSATImpresion);
                                                $fields = $reporte->getFields();
                                                $NumeroConcepto++;
                                            }
                                            if ($cobrarExcedenteColor && $color) {
                                                $um = $reporte->getUnidadMedida($idServicio, "Excedentes", $unidad_servicio);
                                                $des_aux = "PAGINAS IMPRESAS COLOR: $diferencia_color INCLUYE ($incluidosColor)";
                                                if (true) {
                                                    $des_aux .= " SERIE: " . $rs['NoSerie'];
                                                    if($rs['IdTipoInventario'] == "8"){
                                                        $des_aux .= " * En backup";
                                                    }
                                                }
                                                if ($MostrarModelo) {
                                                    $des_aux .= " MODELO: " . $rs['Modelo'];
                                                }
                                                if (!$muestra_serie && $MostrarUbicacion && $rs['Ubicacion'] != "") {
                                                    $des_aux .= " UBICACIÓN: " . $rs['Ubicacion'];
                                                }
                                                $auxFactura = $NumeroFacturas;
                                                $auxConcepto = $NumeroConcepto;
                                                if ($agrupar_color) {
                                                    $auxFactura = $NumeroFacturas + 1;
                                                    $auxConcepto = $NumeroConceptosColor++;
                                                }
                                                $reporte->setFields($fields);

                                                $reporte->crearConceptoFactura($aux1, $um, $des_aux, $excedentesColor, $excedentesColor * $aux1, $auxFactura, $auxConcepto, $IdProductoSATImpresion);
                                                $fields = $reporte->getFields();
                                                $NumeroConcepto++;
                                            }
                                            if ($cobrarProcesadasBN) {
                                                $um = $reporte->getUnidadMedida($idServicio, "Impresiones", $unidad_servicio);
                                                $des_aux = "PAGINAS IMPRESAS NEGRO";
                                                if (true) {
                                                    $des_aux .= " SERIE: " . $rs['NoSerie'];
                                                    if($rs['IdTipoInventario'] == "8"){
                                                        $des_aux .= " * En backup";
                                                    }
                                                }
                                                if ($MostrarModelo) {
                                                    $des_aux .= " MODELO: " . $rs['Modelo'];
                                                }
                                                if (!$muestra_serie && $MostrarUbicacion && $rs['Ubicacion'] != "") {
                                                    $des_aux .= " UBICACIÓN: " . $rs['Ubicacion'];
                                                }
                                                $reporte->setFields($fields);

                                                $reporte->crearConceptoFactura($diferencia_bn, $um, $des_aux, $procesadosBN, $procesadosBN * $diferencia_bn, $NumeroFacturas, $NumeroConcepto, $IdProductoSATImpresion);
                                                $fields = $reporte->getFields();
                                                $NumeroConcepto++;
                                            }
                                            if ($cobrarProcesadasColor && $color) {
                                                $um = $reporte->getUnidadMedida($idServicio, "Impresiones", $unidad_servicio);
                                                $des_aux = "PAGINAS IMPRESAS COLOR";
                                                if (true) {
                                                    $des_aux .= " SERIE: " . $rs['NoSerie'];
                                                    if($rs['IdTipoInventario'] == "8"){
                                                        $des_aux .= " * En backup";
                                                    }
                                                }
                                                if ($MostrarModelo) {
                                                    $des_aux .= " MODELO: " . $rs['Modelo'];
                                                }
                                                if (!$muestra_serie && $MostrarUbicacion && $rs['Ubicacion'] != "") {
                                                    $des_aux .= " UBICACIÓN: " . $rs['Ubicacion'];
                                                }
                                                $auxFactura = $NumeroFacturas;
                                                $auxConcepto = $NumeroConcepto;
                                                if ($agrupar_color) {
                                                    $auxFactura = $NumeroFacturas + 1;
                                                    $auxConcepto = $NumeroConceptosColor++;
                                                }
                                                $reporte->setFields($fields);

                                                $reporte->crearConceptoFactura($diferencia_color, $um, $des_aux, $procesadosColor, $procesadosColor * $diferencia_color, $auxFactura, $auxConcepto, $IdProductoSATImpresion);
                                                $fields = $reporte->getFields();
                                                $NumeroConcepto++;
                                            }
                                            if ($particular && $cobrarRenta) {
                                                if ($dividir_lecturas) {
                                                    $auxFactura = $NumeroFacturas + 1;
                                                    $auxConcepto = 0;
                                                    $fields = $reporte->getFields();
                                                    $NumeroRentaPorServicio++;
                                                    /* echo "<br/>7. ".($NumeroFacturas)." - $NumeroConcepto";
                                                      echo "<input type='hidden' id='conceptos_factura_$NumeroFacturas' name='conceptos_factura_$NumeroFacturas' value='$NumeroConcepto'/>";
                                                      if(isset($_POST['atm_post'])){
                                                      $fields['conceptos_factura_'.$NumeroFacturas] = urlencode($NumeroConcepto);
                                                      }
                                                      $NumeroFacturas++;
                                                      $NumeroConcepto = 0; */
                                                } else {
                                                    $auxFactura = $NumeroFacturas;
                                                    $auxConcepto = $NumeroConcepto++;
                                                }
                                                $um = $reporte->getUnidadMedida($idServicio, "Renta", $unidad_servicio);
                                                $des_aux = "RENTA EQUIPO " . $rs['CentroCostoNombre'];
                                                if (true) {
                                                    $des_aux.= " SERIE: " . $rs['NoSerie'];
                                                    if($rs['IdTipoInventario'] == "8"){
                                                        $des_aux .= " * En backup";
                                                    }
                                                }
                                                if ($MostrarModelo) {
                                                    $des_aux .= " MODELO " . $rs['Modelo'];
                                                }
                                                if (!$muestra_serie && $MostrarUbicacion && $rs['Ubicacion'] != "") {
                                                    $des_aux .= " UBICACIÓN: " . $rs['Ubicacion'];
                                                }
                                                $reporte->setFields($fields);

                                                $cantidad_rentas = 1;
                                                if($rs['IdTipoInventario'] == "8"){
                                                    $cantidad_rentas = 0;
                                                }
                                                $reporte->crearConceptoFactura($cantidad_rentas, $um, $des_aux, $rs[$prefijos[$prefijo] . "Renta"], $rs[$prefijos[$prefijo] . "Renta"], $auxFactura, $auxConcepto, $IdProductoSATRenta);
                                                $fields = $reporte->getFields();
                                                $NumeroConcepto++;
                                            }
                                        }
                                    }
                                } else {
                                    if ($MostrarEquipos) {//Llena en blanco las columnas de los servicios globales que no se muestran.
                                        for ($i = 0; $i < 5; $i++) {
                                            if ($mostrarContadores || $i >= 2) {
                                                echo "<td class='borde'></td>";
                                            }
                                        }
                                    }
                                }

                                if ($MostrarEquipos) {   //Imprime direccion y/o ubicacion                     
                                    if ($MostrarUbicacion) {//Imprime ubicacion
                                        echo "<td class='borde'>" . $rs['Ubicacion'] . "</td>";
                                    }

                                    if ($MostrarDireccion) {//Imprime direccion
                                        if (isset($direcciones[$rs['ClaveCentroCosto']])) {
                                            $direccion = $direcciones[$rs['ClaveCentroCosto']];
                                        } else {
                                            $localidad = new Localidad();
                                            $localidad->setEmpresa($empresa);
                                            if ($localidad->getLocalidadByClave($rs['ClaveCentroCosto'])) {
                                                $direccion = $localidad->getCalle() . " No. Ext: " . $localidad->getNoExterior() . " No. Int: " . $localidad->getNoInterior() . " 
                                            Col: " . $localidad->getColonia() . " C.P.: " . $localidad->getCodigoPostal() . ", Del.: " . $localidad->getDelegacion() . ", " . $localidad->getEstado();
                                                $direcciones[$rs['ClaveCentroCosto']] = $direccion;
                                            } else {
                                                $direccion = "";
                                            }
                                        }
                                        echo "<td class='borde'>$direccion</td>";
                                    }
                                    echo "</tr>";
                                }

                                //Seccion para guardar los datos de las agrupaciones si se necesita
                                if ($mostrarDetalleLocalidad || $mostrarDetalleCC || $mostrarDetalleZona) {
                                    if ($mostrarDetalleLocalidad) {
                                        $campo_clave = $rs['ClaveCentroCosto'];
                                        $nombre = $rs['CentroCostoNombre'];
                                    } else if ($mostrarDetalleCC) {
                                        $campo_clave = $rs["idCen_Costo"];
                                        $nombre = $rs['CentroCostoLocalidad'];
                                    } else {
                                        $obj = new Zona();
                                        $obj->setEmpresa($empresa);
                                        $campo_clave = $rs["ClaveZona"];
                                        if ($obj->getRegistroById($campo_clave)) {
                                            $nombre = $obj->getNombre();
                                        } else {
                                            $nombre = $rs['ClaveZona'];
                                        }
                                    }

                                    if (!isset($nombreAgrupaciones[$campo_clave])) {
                                        $nombreAgrupaciones[$campo_clave] = $nombre;
                                        $seriesPorAgrupacion[$campo_clave] = "";
                                    }
                                    $des_aux = "";
                                    if (true) {
                                        $des_aux .= "SERIE: " . $rs['NoSerie'];
                                        if($rs['IdTipoInventario'] == "8"){
                                            $des_aux .= " * En backup";
                                        }
                                    }
                                    if ($MostrarModelo) {
                                        $des_aux .= " MODELO:" . $rs['Modelo'];
                                    }
                                    /* if($MostrarLecturas){
                                      if(!empty($contadorBN)){
                                      $des_aux .= " LEC. ACT. ($year-$month): ".  number_format($contadorBN);
                                      }

                                      if(!empty($contadorBNAnterior)){
                                      $des_aux .= " LEC. ANT. ($year_aux-$month_aux): ".  number_format($contadorBNAnterior);
                                      }

                                      if(!empty($contadorColor)){
                                      $des_aux .= " LEC. ACT. COLOR($year-$month): ".  number_format($contadorColor);
                                      }

                                      if(!empty($contadorColorAnterior)){
                                      $des_aux .= " LEC. ANT. COLOR($year_aux-$month_aux): ".  number_format($contadorColorAnterior);
                                      }
                                      } */
                                    if (!$muestra_serie && $MostrarUbicacion && $rs['Ubicacion'] != "") {
                                        $des_aux .= " UBICACIÓN: " . $rs['Ubicacion'];
                                    }
                                    if ($des_aux != "") {
                                        $des_aux .= ", ";
                                    }
                                    $seriesPorAgrupacion[$campo_clave][$rs['IdKServicio' . $prefijos[$prefijo]]] .= ($des_aux);
                                    $esParticularAgrupacion[$campo_clave][$rs['IdKServicio' . $prefijos[$prefijo]]] = $particular;
                                    $idServicioByKServicio[$campo_clave][$rs['IdKServicio' . $prefijos[$prefijo]]] = $idServicio;
                                    $incluidosBNAgrupacion[$campo_clave][$rs['IdKServicio' . $prefijos[$prefijo]]] = $rs[$prefijos[$prefijo] . 'incluidosBN'];
                                    $incluidosColorAgrupacion[$campo_clave][$rs['IdKServicio' . $prefijos[$prefijo]]] = $rs[$prefijos[$prefijo] . 'incluidosColor'];
                                    $equiposPorAgrupacion = $reporte->agregarValorNumericoArrayBidimensional($equiposPorAgrupacion, $campo_clave, $rs['IdKServicio' . $prefijos[$prefijo]], 1);
                                    $impresionesBNAgrupacion = $reporte->agregarValorNumericoArrayBidimensional($impresionesBNAgrupacion, $campo_clave, $rs['IdKServicio' . $prefijos[$prefijo]], $diferencia_bn);
                                    $impresionesColorAgrupacion = $reporte->agregarValorNumericoArrayBidimensional($impresionesColorAgrupacion, $campo_clave, $rs['IdKServicio' . $prefijos[$prefijo]], $diferencia_color);
                                    if ($particular) {//Si es particular, por equipo se descuentan los paginas incluidas
                                        $excedenteBNAgrupacion = $reporte->agregarValorNumericoArrayBidimensional($excedenteBNAgrupacion, $campo_clave, $rs['IdKServicio' . $prefijos[$prefijo]], $diferencia_bn - $incluidosBN);
                                        $excedenteColorAgrupacion = $reporte->agregarValorNumericoArrayBidimensional($excedenteColorAgrupacion, $campo_clave, $rs['IdKServicio' . $prefijos[$prefijo]], $diferencia_color - $incluidosColor);
                                    } else {//Si es global, no se descuentan las paginas incluidas, se hará hasta que se impriman los datos
                                        $excedenteBNAgrupacion = $reporte->agregarValorNumericoArrayBidimensional($excedenteBNAgrupacion, $campo_clave, $rs['IdKServicio' . $prefijos[$prefijo]], $diferencia_bn);
                                        $excedenteColorAgrupacion = $reporte->agregarValorNumericoArrayBidimensional($excedenteColorAgrupacion, $campo_clave, $rs['IdKServicio' . $prefijos[$prefijo]], $diferencia_color);
                                    }
                                    if ($cobrarRenta) {
                                        $rentaAgrupacion[$campo_clave][$rs['IdKServicio' . $prefijos[$prefijo]]] = $rs[$prefijos[$prefijo] . "Renta"];
                                    }
                                    if ($cobrarExcedenteBN) {
                                        $costoExcedentesBNAgrupacion[$campo_clave][$rs['IdKServicio' . $prefijos[$prefijo]]] = $rs[$prefijos[$prefijo] . 'ExcedentesBN'];
                                    }
                                    if ($cobrarExcedenteColor) {
                                        $costoExcedentesColorAgrupacion[$campo_clave][$rs['IdKServicio' . $prefijos[$prefijo]]] = $rs[$prefijos[$prefijo] . 'ExcedentesColor'];
                                    }
                                    if ($cobrarProcesadasBN) {
                                        $costoProcesadasBNAgrupacion[$campo_clave][$rs['IdKServicio' . $prefijos[$prefijo]]] = $rs[$prefijos[$prefijo] . 'ProcesadasBN'];
                                    }
                                    if ($cobrarProcesadasColor) {
                                        $costoProcesadasColorAgrupacion[$campo_clave][$rs['IdKServicio' . $prefijos[$prefijo]]] = $rs[$prefijos[$prefijo] . 'ProcesadosColor'];
                                    }
                                }
                                /*                                 * ***********************************        Fin de la fila de cada tabla       ************************************ */


                                if ($diferencia_bn > 0) {
                                    if (isset($contadorBNServicio[$rs['IdKServicio' . $prefijos[$prefijo]]])) {
                                        $contadorBNServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] += $diferencia_bn;
                                        if ($particular && $diferencia_bn > $incluidosBN) {
                                            $excedenteBNServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] += ($diferencia_bn - $incluidosBN);
                                        } else if (!$particular) {
                                            $excedenteBNServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] += ($diferencia_bn);
                                        }
                                    } else {
                                        $contadorBNServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = $diferencia_bn;
                                        if ($particular) {
                                            if ($diferencia_bn > $incluidosBN) {
                                                $excedenteBNServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = ($diferencia_bn - $incluidosBN);
                                            } else {
                                                $excedenteBNServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = 0;
                                            }
                                        } else {
                                            $excedenteBNServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = ($diferencia_bn);
                                        }
                                    }
                                }

                                if ($diferencia_color > 0) {
                                    if (isset($contadorColorServicio[$rs['IdKServicio' . $prefijos[$prefijo]]])) {
                                        $contadorColorServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] += $diferencia_color;
                                        if ($particular && $diferencia_color > $incluidosColor) {
                                            $excedenteColorServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] += ($diferencia_color - $incluidosColor);
                                        } else if (!$particular) {
                                            $excedenteColorServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] += ($diferencia_color);
                                        }
                                    } else {
                                        $contadorColorServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = $diferencia_color;
                                        if ($particular) {
                                            if ($diferencia_color > $incluidosColor) {
                                                $excedenteColorServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = ($diferencia_color - $incluidosColor);
                                            } else {
                                                $excedenteColorServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = 0;
                                            }
                                        } else {
                                            $excedenteColorServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] = ($diferencia_color);
                                        }
                                    }
                                }

                                $no_equipo++;
                                $nueva_hoja = false;
                                $IdKServicioAnterior = $rs['IdKServicio' . $prefijos[$prefijo]];
                                $idServicioAnterior = $rs['IdServicio' . $prefijos[$prefijo]];
                                $prefijoAnterior = $prefijos[$prefijo];
                                $localidadAnterior = $rs['ClaveCentroCosto'];
                                $NombreLocalidadAnterior = $rs['CentroCostoNombre'];
                                $color_anterior = $rs['isColor'];

                                //Vemos como se va a agrupar los datos segun los parametros
                                if ($agrupar_servicio) {
                                    $variable_rs_cambio = 'IdKServicio' . $prefijos[$prefijo]; //Cada que cambie esta variable, se creara una hoja                                
                                    $variable_cambio_anterior = $rs[$variable_rs_cambio];
                                } else if ($agrupar_tipo_servicio) {
                                    $variable_rs_cambio = 'IdServicio' . $prefijos[$prefijo]; //Cada que cambie esta variable, se creara una hoja                                
                                    $variable_cambio_anterior = $rs[$variable_rs_cambio];
                                } else if ($agrupar_localidad) {
                                    $variable_rs_cambio = "ClaveCentroCosto"; //Cada que cambie esta variable, se creara una hoja                                
                                    $variable_cambio_anterior = $rs[$variable_rs_cambio];
                                } else if ($agrupar_cc) {
                                    $variable_rs_cambio = 'idCen_Costo'; //Cada que cambie esta variable, se creara una hoja                                
                                    $variable_cambio_anterior = $rs[$variable_rs_cambio];
                                } else if ($agrupar_zona) {
                                    $variable_rs_cambio = "ClaveZona";
                                    $variable_cambio_anterior = $rs[$variable_rs_cambio];
                                } else if ($agrupar_todo) {
                                    $variable_rs_cambio = "Junto";
                                    $variable_cambio_anterior = $rs[$variable_rs_cambio];
                                } else {
                                    $variable_rs_cambio = "NoSerie";
                                    $variable_cambio_anterior = $rs[$variable_rs_cambio];
                                }

                                $existeContadorBN = false;
                                if (( (isset($contadorBN) && $contadorBN > 0) || (isset($contadorBNAnterior) && $contadorBNAnterior > 0) ) || $imprimir_cero == 1) {
                                    if (((isset($contadorColor) && $contadorColor > 0) || (isset($contadorColorAnterior) && $contadorColorAnterior > 0)) || $imprimir_cero == 1) {
                                        $idBitacoras .= ($rs['id_bitacora'] . "&&$contadorBN&&$contadorBNAnterior&&$diferencia_bn&&" . $excedenteBNServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] . "&&$contadorColor&&$contadorColorAnterior&&$diferencia_color&&" . $excedenteColorServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] . ",");
                                    } else {
                                        $idBitacoras .= ($rs['id_bitacora'] . "&&$contadorBN&&$contadorBNAnterior&&$diferencia_bn&&" . $excedenteBNServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] . "&&,");
                                    }
                                    $existeContadorBN = true;
                                }

                                if (isset($contadorColor) && $contadorColor > 0) {
                                    if ($existeContadorBN) {
                                        $idBitacorasColor .= ($rs['id_bitacora'] . ",");
                                    } else {
                                        $idBitacorasColor .= ($rs['id_bitacora'] . "&&$contadorBN&&$contadorBNAnterior&&$diferencia_bn&&" . $excedenteBNServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] . "&&$contadorColor&&$contadorColorAnterior&&$diferencia_color&&" . $excedenteColorServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] . ",");
                                    }
                                    $existeContadorBN = true;
                                }

                                if ($cobrarRenta && $rs[$prefijos[$prefijo] . "Renta"] > 0) {
                                    if ($existeContadorBN) {
                                        $idBitacorasRenta .= ($rs['id_bitacora'] . ",");
                                    } else {
                                        $idBitacorasRenta .= ($rs['id_bitacora'] . "&&$contadorBN&&$contadorBNAnterior&&$diferencia_bn&&" . $excedenteBNServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] . "&&$contadorColor&&$contadorColorAnterior&&$diferencia_color&&" . $excedenteColorServicio[$rs['IdKServicio' . $prefijos[$prefijo]]] . ",");
                                    }
                                    $existeContadorBN = true;
                                }
                            }//Cierre while                    

                            $costoTotal = 0;
                            if (!$todo_cerrado) {
                                /* Si se quiere imprimir la fila de informacion detallada por servicio */
                                if ($IdKServicioAnterior != $rs['IdKServicio' . $prefijos[$prefijo]]) {
                                    if ($mostrarDetalleServicio) {
                                        $reporte->setFields($fields);

                                        $NumeroConcepto = $reporte->imprimirDetalleServicio($IdKServicioAnterior, $idServicioAnterior, $prefijoAnterior, $impresionesBNPorServicio, $impresionesColorPorServicio, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProBN, $costoProColor, $particularServicio, $iva, $excedenteBNServicio, $excedenteColorServicio, $equiposPorServicio, $NumeroFacturas, $NumeroConcepto, $unidad_servicio, $prefijos, $incluidosBNServicio, $incluidosColorServicio, $mostrarRenta, $encabezado_variable, $incluidosColor, $incluidosBN, $imprimir_cero, $dividir_lecturas, $MostrarSeries, $seriesServicio, $MostrarModelo, $agrupar_color, $NumeroConceptosColor);
                                        $fields = $reporte->getFields();
                                        if ($IdKServicioAnterior != 0) {
                                            $impresionesBNPorServicio = 0;
                                            $impresionesColorPorServicio = 0;
                                            $equiposPorServicio = 0;
                                        }
                                    }
                                }


                                if ($mostrarDetalleLocalidad || $mostrarDetalleCC || $mostrarDetalleZona) {
                                    $reporte->setFields($fields);

                                    $NumeroConcepto = $reporte->imprimirDetalle($nombreAgrupaciones, $esParticularAgrupacion, $idServicioByKServicio, $incluidosBNAgrupacion, $incluidosColorAgrupacion, $equiposPorAgrupacion, $impresionesBNAgrupacion, $impresionesColorAgrupacion, $excedenteBNAgrupacion, $excedenteColorAgrupacion, $rentaAgrupacion, $costoExcedentesBNAgrupacion, $costoExcedentesColorAgrupacion, $costoProcesadasBNAgrupacion, $costoProcesadasColorAgrupacion, $iva, $mostrarRenta, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $imprimir_cero, $dividir_lecturas, $MostrarSeries, $seriesPorAgrupacion, $MostrarModelo, $agrupar_color, $NumeroConceptosColor);
                                    $fields = $reporte->getFields();
                                }

                                /* Guardamos el concepto de la renta */
                                $particularAnterior = $reporte->isParticularByPrefijo($reporte->obtenerIdPrefijo($prefijoAnterior, $prefijos));
                                if (!$mostrarDetalleServicio && !$mostrarDetalleLocalidad && !$mostrarDetalleCC && !$mostrarDetalleZona) {
                                    if (!$particularAnterior) {
                                        $um = $reporte->getUnidadMedida($idServicioAnterior, "Renta", $unidad_servicio);
                                        if ($dividir_lecturas) {
                                            $auxFactura = $NumeroFacturas + 1;
                                            $auxConcepto = $NumeroRentaPorServicio++;
                                        } else {
                                            $auxFactura = $NumeroFacturas;
                                            $auxConcepto = $NumeroConcepto++;
                                        }
                                        $reporte->setFields($fields);
                                        $reporte->crearConceptoRentaFactura($particularAnterior, $equiposPorServicio, $um, $descripcion_renta, $IdKServicioAnterior, $costoRentaServicio, $idServicioByKServicio, $auxFactura, $auxConcepto);
                                        $fields = $reporte->getFields();
                                    }
                                }
                                $descripcion_renta = "";
                                if (!empty($conceptos_adicionales)) {
                                    $reporte->setFields($fields);

                                    $NumeroConcepto = $reporte->crearConceptosAdicionales($conceptos_adicionales, $cliente_por_factura, $anexos_por_factura, $zonas_por_factura, $cc_por_factura, $localidad_por_factura, $iva, $NumeroFacturas, $NumeroConcepto, $procesadosSeparado, $dividir_lecturas);
                                    $fields = $reporte->getFields();
                                    $procesadosSeparado = true;
                                    $cliente_por_factura = $reporte->ponerValorUno($cliente_por_factura);
                                    $anexos_por_factura = $reporte->ponerValorUno($anexos_por_factura);
                                    $zonas_por_factura = $reporte->ponerValorUno($zonas_por_factura);
                                    $cc_por_factura = $reporte->ponerValorUno($cc_por_factura);
                                    $localidad_por_factura = $reporte->ponerValorUno($localidad_por_factura);
                                }
                                $reporte->setFields($fields);
                                $NumeroConcepto = $reporte->imprimirTablaTotalAgrupacion($costoTotalPorGrupo + $reporte->getTotalConceptosAdicionales(), $contadorBNServicio, $contadorColorServicio, $costoRentaServicio, $costoExcedentesBN, $costoExcedentesColor, $costoProBN, $costoProColor, $particularServicio, $excedenteBNServicio, $excedenteColorServicio, $iva, $idServicioByKServicio, $unidad_servicio, $NumeroFacturas, $NumeroConcepto, $mostrarDetalleServicio, $incluidosBNServicio, $incluidosColorServicio, $MostrarEquipos, $imprimir_cero, $agrupar_color, $NumeroConceptosColor);
                                $fields = $reporte->getFields();

                                echo "<input type='hidden' id='conceptos_factura_$NumeroFacturas' name='conceptos_factura_$NumeroFacturas' value='$NumeroConcepto'/>";
                                if (isset($_POST['atm_post'])) {
                                    $fields['conceptos_factura_' . $NumeroFacturas] = urlencode($NumeroConcepto);
                                }

                                if (!empty($idBitacoras)) {
                                    $idBitacoras = substr($idBitacoras, 0, strlen($idBitacoras) - 1);
                                    echo "<input type='hidden' id='bitacoras_factura_" . $NumeroFacturas . "' name='bitacoras_factura_" . $NumeroFacturas . "' 
                                value='$idBitacoras'/>";
                                    if (isset($_POST['atm_post'])) {
                                        $fields['bitacoras_factura_' . $NumeroFacturas] = urlencode($idBitacoras);
                                    }
                                }

                                if (!empty($idBitacorasColor)) {
                                    $idBitacorasColor = substr($idBitacorasColor, 0, strlen($idBitacorasColor) - 1);
                                    echo "<input type='hidden' id='bitacorascolor_factura_" . $NumeroFacturas . "' name='bitacorascolor_factura_" . $NumeroFacturas . "' 
                                value='$idBitacorasColor'/>";
                                    if (isset($_POST['atm_post'])) {
                                        $fields['bitacorascolor_factura_' . ($NumeroFacturas)] = urlencode($idBitacorasColor);
                                    }
                                }

                                if (!empty($idBitacorasRenta)) {
                                    $idBitacorasRenta = substr($idBitacorasRenta, 0, strlen($idBitacorasRenta) - 1);
                                    echo "<input type='hidden' id='bitacorasrenta_factura_" . $NumeroFacturas . "' name='bitacorasrenta_factura_" . $NumeroFacturas . "' 
                                value='$idBitacorasRenta'/>";
                                    if (isset($_POST['atm_post'])) {
                                        $fields['bitacorasrenta_factura_' . ($NumeroFacturas)] = urlencode($idBitacorasRenta);
                                    }
                                }

                                if ($reporte->getHayConceptosSeparados()) {
                                    $NumeroFacturas++;
                                }
                                if ($dividir_lecturas && $MostrarEquipos) {//Si se dividio la factura, tenemos que guardar el numero de componentes de la factura de renta
                                    echo "<input type='hidden' id='conceptos_factura_" . ($NumeroFacturas + 1) . "' "
                                    . "name='conceptos_factura_" . ($NumeroFacturas + 1) . "' value='$NumeroRentaPorServicio'/>";
                                    if (isset($_POST['atm_post'])) {
                                        $fields['conceptos_factura_' . ($NumeroFacturas + 1)] = urlencode($NumeroRentaPorServicio);
                                    }
                                } else if ($agrupar_color && $MostrarEquipos) {
                                    $NumeroConceptosColor = $reporte->getNumeroConceptosColor();

                                    echo "<input type='hidden' id='conceptos_factura_" . ($NumeroFacturas + 1) . "' "
                                    . "name='conceptos_factura_" . ($NumeroFacturas + 1) . "' value='$NumeroConceptosColor'/>";
                                    if (isset($_POST['atm_post'])) {
                                        $fields['conceptos_factura_' . ($NumeroFacturas + 1)] = urlencode($NumeroConceptosColor);
                                    }
                                }

                                echo "</table/><br/><br/>";
                            }

                            if ($dividir_lecturas) {
                                $NumeroFacturas++;
                            } else if ($agrupar_color && $mostrarRenta) {
                                $NumeroFacturas++;
                            }
                            ?>                
                    <?php
                }/* else{
                  if($value == 0){
                  echo "No se pudieron encontrar equipos para esta búsqueda";
                  }
                  } */
            }//Fin iteracion de localidades con domicilio Fiscal
        }//Fin iteracion de los tipos de domicilio fiscal
        if (!$hay_resultados) {
            echo "No se pudieron encontrar equipos para esta búsqueda";
        }
        ?>
                <input type="hidden" name="iva" id="iva" value="<?php echo $iva; ?>"/>
        <?php if (isset($rfcFacturarA) && $rfcFacturarA != "") { ?>
                    <input type="hidden" name="rfc" id="rfc" value="<?php echo $rfcFacturarA; ?>"/>
<?php } else { ?>
                    <input type="hidden" name="rfc" id="rfc" value="<?php echo $rfc; ?>"/>
<?php } ?>
                <input type="hidden" name="rfcFacturacion" id="rfcFacturacion" value="<?php echo $rfcFacturacion; ?>"/>                
                <input type="hidden" name="forma_pago" id="forma_pago" value="<?php echo $formaPago; ?>"/>
                <input type="hidden" name="no_contrato" id="no_contrato" value="<?php echo $NoContrato; ?>"/>                            
                <input type="hidden" name="num_facturas" id="num_facturas" value="<?php echo $NumeroFacturas; ?>"/>
                <input type="hidden" name="imprime_serie" id="imprime_serie" value="<?php echo $imprime_serie; ?>"/>
<?php
if (isset($_POST['atm_post'])) {
    $fields['iva'] = urlencode($iva);
    $fields['rfc'] = urlencode($rfc);
    $fields['rfcFacturacion'] = urlencode($rfcFacturacion);
    $fields['forma_pago'] = urlencode($formaPago);
    $fields['no_contrato'] = urlencode($NoContrato);
    $fields['num_facturas'] = urlencode($NumeroFacturas);
    $fields['imprime_serie'] = urlencode($imprime_serie);
}
?>
            </form>
        </div>
<?php
if (isset($_POST['atm_post'])) {

    //url-ify the data for the POST
    foreach ($fields as $key => $value) {
        $fields_string .= $key . '=' . $value . '&';
    }
    rtrim($fields_string, '&');
    //open connection
    $ch = curl_init();
    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

    //execute post
    $result_post = curl_exec($ch);
    //echo $result_post;
    //close connection
    curl_close($ch);
}
?>
    </body>
    <script>
        $(document).ready(function () {
<?php if ($permisos_grid->getAlta() && isset($_POST['activarBoton']) && (int) $_POST['activarBoton'] == 1 && !$faltanLecturasActual && !$faltanLecturasMesAnterior) { ?>
                $('#<?php echo $form ?>').submit();
<?php } else if ($permisos_grid->getAlta() && isset($_POST['activarBoton'])) {
    if ($faltanLecturasActual) {
        ?>
                    alert("Las series <?php echo substr($textoSeriesActuales, 0, strlen($textoSeriesActuales) - 2) ?> no tienen lecturas del mes actual");
    <?php }
    if ($faltanLecturasMesAnterior) {
        ?>
                    alert("Las series <?php echo substr($textoSeriesMesAnterior, 0, strlen($textoSeriesMesAnterior) - 2) ?> no tienen lecturas del mes anterior");
    <?php }
} ?>
        });
    </script>
</html>