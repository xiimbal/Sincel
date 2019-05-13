<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/ReporteFacturacion2.class.php");
include_once("../../Classes/XML_Facturacion.class.php");
include_once("../../Classes/Factura.class.php");
include_once("../../Classes/Factura2.class.php");
include_once("../../Classes/FacturaExtra.class.php");
include_once("../../Classes/Localidad.class.php");
include_once("../../Classes/Concepto.class.php");
include_once("../../Classes/Empresa.class.php");
include_once("../../Classes/Cliente.class.php");
include_once("../../Classes/ccliente.class.php");
include_once("../../Classes/AddendaDetalle.class.php");
require_once("../../Classes/nu_soap/nusoap.php");
include_once("../../Classes/PDFFactura.class.php");
include_once("../../Classes/EnLetras.class.php");
include_once("../../Classes/CFDI.class.php");
include_once("../../Classes/Base64Convert.class.php");
include_once("../../Classes/XMLReadSAT.class.php");
include_once("../../Classes/XMLAbraham.class.php");
include_once("../../Classes/PAC.class.php");
include_once("../../Classes/phpqrcode/qrlib.php");
include_once("../../Classes/FacturaAbraham.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/PagoParcial.class.php");
include_once("../../Classes/Contrato.class.php");
include_once("../../Classes/DatosFacturacionEmpresa.class.php");

if (isset($_GET['id']) && $_GET['id'] != "") {
    date_default_timezone_set("America/Mexico_City");

    $ndc = false;
    $tipo = "factura";
    if (isset($_POST['ndc']) && $_POST['ndc'] == "1") {
        $ndc = true;
        $tipo = "nota de crédito";
    }
    
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    
    $contrato = new Contrato();
    $reporte = new ReporteFacturacion2();
    $reporte->setFolio($_GET['id']);
    $factura = new Factura();
    $factura->setIdFactura($_GET['id']);    
    
    //Se verifica que no exista una factura del mismo periodo a detalle
    if($factura->verificarFacturasDobles()){
        echo "Error: factura doble del mismo periodo: ".$factura->getMensaje()."<br/>";
        return;
    }
    
    $xml = new XML_Facturacion();
    $xmlAbraham = new XMLAbraham();
    $pdf = new PDFFactura();             //Crea objeto PDF
    $ccliente = new Cliente();
    
    $factura->getRegistrobyID();
    $factura->setUsuarioCreacion($_SESSION['user']);
    $factura->setUsuarioUltimaModificacion($_SESSION['user']);
    $factura->setPantalla("PHP Generar Factura");
    $ccliente->getRegistroById($factura->getRFCReceptor());
    $IdSeriePrefijo = $factura->getIdSerie();
    if(isset($IdSeriePrefijo) && $IdSeriePrefijo != ""){
        $cata = new Catalogo();
        $cons = "SELECT Prefijo FROM c_serie WHERE IdSerie = $IdSeriePrefijo";
        $result = $cata->obtenerLista($cons);
        while($rs = mysql_fetch_array($result)){
            $xml->setPrefijoSerie($rs['Prefijo']);
        }
    }
    $cero = "";
    if ((date("i") - 1) < 10) {
        $cero = "0";
    }
    
    $timestamp = strtotime(date('H:i')) - 3*60; //El número de minutos que se quiere retrasar se multiplica por los 60 segundos.
    $time = date('H:i', $timestamp);
    
    $xml->setFecha(date("Y-m-d") . "T" . $time . date(":s"));
    $xml->setFormaDePago($factura->getFormaPago());
    $xmlAbraham->setFecha(date("Y-m-d") . "T" . date("H:") . $cero . (date("i") - 1) . date(":s"));
    $xmlAbraham->setFormaDePago($factura->getFormaPago());
    $factExtra = new FacturaExtra();
    $factExtra->setId_factura($_GET['id']);
    $obs_in = "";
    $comentario = "";
    $valores = array();
    
    //Si en el parametro del cliente está encendio mostrar el mes de contrato y además es una factura de arrendamiento
    $result = $contrato->getRegistroValidacion($ccliente->getClaveCliente());    
    if($rs = mysql_fetch_array($result)){
        $resultValores = $contrato->getValorPorContrato($rs['NoContrato']);
        while($rsValores = mysql_fetch_array($resultValores)){
            if((int)$rsValores['mostrarPDF'] == 1){
                $valores[$rsValores['campo']] =  $rsValores['valor'];
            }
        }
        $pdf->setValores($valores);
    }
    
    if ($factExtra->getRegistroByFactura()) {
        $obs_in = $factExtra->getObservaciones_dentro_xml();
        $comentario.=$factExtra->getObservaciones_fuera_xml();
        if ($factExtra->getNumero_orden() != "") {
            $comentario.=" Número de orden:" . $factExtra->getNumero_orden();
        }
        if ($factExtra->getNumero_proveedor() != "") {
            $comentario.=" Número de proveedor:" . $factExtra->getNumero_proveedor();
        }
    }
    $concepto = new Concepto();
    $concepto->setObs_in($obs_in);
    $subtotal = $concepto->subtotalbyFacturaObs($_GET['id']);        
    $xml->setSubTotal($subtotal);
    $xml->setTotal($subtotal * 1.16);
    $xml->setTipoDeComprobante($factura->getTipoComprobante());
    $xml->setMetodoDePago($factura->getMetodoPago());    
    $xmlAbraham->setSubTotal($subtotal);
    $xmlAbraham->setTotal($subtotal * 1.16);
    $xmlAbraham->setTipoDeComprobante($factura->getTipoComprobante());
    $xmlAbraham->setMetodoDePago($factura->getMetodoPago());
    $empresa = new Empresa();
    $empresa->setId($factura->getRFCEmisor());
    $empresa->getRegistrobyID();
    $xml->setRegimen($empresa->getRegimenFiscal());
    $xml->setLugarExpedicion($empresa->getEstado());
    $xml->setEmisor_rfc($empresa->getRFC());
    $xml->setEmisor_Dom_CP($empresa->getCP());
    $xml->setEmisor_Dom_Calle($empresa->getCalle());
    $xml->setEmisor_Dom_Col($empresa->getColonia());
    $xml->setEmisor_Dom_Est($empresa->getEstado());
    $xml->setEmisor_Dom_Mun($empresa->getDelegacion());
    $xml->setEmisor_Dom_NoExt($empresa->getNoExterior());
    $xml->setEmisor_Dom_NoInt($empresa->getNoInterior());
    $xml->setEmisor_Dom_Pais($empresa->getPais());
    $xml->setEmisor_nombre($empresa->getRazonSocial());
    $xml->setEmisor_rfc($empresa->getRFC());
    //Abraham
    $xmlAbraham->setRegimen($empresa->getRegimenFiscal());
    $xmlAbraham->setLugarExpedicion($empresa->getEstado());
    $xmlAbraham->setEmisor_rfc($empresa->getRFC());
    $xmlAbraham->setEmisor_Dom_CP($empresa->getCP());
    $xmlAbraham->setEmisor_Dom_Calle($empresa->getCalle());
    $xmlAbraham->setEmisor_Dom_Col($empresa->getColonia());
    $xmlAbraham->setEmisor_Dom_Est($empresa->getEstado());
    $xmlAbraham->setEmisor_Dom_Mun($empresa->getDelegacion());
    $xmlAbraham->setEmisor_Dom_NoExt($empresa->getNoExterior());
    $xmlAbraham->setEmisor_Dom_NoInt($empresa->getNoInterior());
    $xmlAbraham->setEmisor_Dom_Pais($empresa->getPais());
    $xmlAbraham->setEmisor_nombre($empresa->getRazonSocial());
    $xmlAbraham->setEmisor_rfc($empresa->getRFC());
    //
    $xml->setExpedido_CP($empresa->getCP());
    $xml->setExpedido_Calle($empresa->getCalle());
    $xml->setExpedido_Col($empresa->getColonia());
    $xml->setExpedido_Estado($empresa->getEstado());
    $xml->setExpedido_Mun($empresa->getDelegacion());
    $xml->setExpedido_NoExt($empresa->getNoExterior());
    $xml->setExpedido_NoInt($empresa->getNoInterior());
    $xml->setExpedido_Pais($empresa->getPais());
    //XML Abraham
    $xmlAbraham->setExpedido_CP($empresa->getCP());
    $xmlAbraham->setExpedido_Calle($empresa->getCalle());
    $xmlAbraham->setExpedido_Col($empresa->getColonia());
    $xmlAbraham->setExpedido_Estado($empresa->getEstado());
    $xmlAbraham->setExpedido_Mun($empresa->getDelegacion());
    $xmlAbraham->setExpedido_NoExt($empresa->getNoExterior());
    $xmlAbraham->setExpedido_NoInt($empresa->getNoInterior());
    $xmlAbraham->setExpedido_Pais($empresa->getPais());
    //
    //$ccliente->setClaveCliente($factura->getRFCReceptor());        
    $localidad = new Localidad();
    if ($factura->getIdDomicilioFiscal() != "") {
        $localidad->getLocalidadById($factura->getIdDomicilioFiscal());
    } else {
        $localidad->getLocalidadByClaveTipo($factura->getRFCReceptor(), "3");
    }

    $xml->setReceptor_Dom_CP($localidad->getCodigoPostal());
    $xml->setReceptor_rfc($ccliente->getRFC());
    $xml->setReceptor_nombre($ccliente->getNombreRazonSocial());
    $xml->setReceptor_Dom_Calle($localidad->getCalle());
    $xml->setReceptor_Dom_Col($localidad->getColonia());
    $xml->setReceptor_Dom_Est($localidad->getEstado());
    $xml->setReceptor_Dom_Mun($localidad->getDelegacion());
    $xml->setReceptor_Dom_NoExt($localidad->getNoExterior());
    $xml->setReceptor_Dom_NoInt($localidad->getNoInterior());
    $xml->setReceptor_Dom_Pais("México");
    $xml->setConceptos($concepto->getConceptos_array());
    $xml->setImpuestos_totalImpuestosTrasladados($subtotal * .16);
    $xml->setImpuestos_Trasladado(Array(Array("IVA", "16.00", $subtotal * 0.16)));
    if (!$ndc) {
        $xml->setTipoDeComprobante("ingreso");
    } else {
        $xml->setTipoDeComprobante("egreso");
    }
    
    /*Agregar addendas*/
    $addendas = array();
    $cliente_detalle = new ccliente();    
    if($cliente_detalle->getregistrobyID($ccliente->getClaveCliente()) && $cliente_detalle->getIdAddenda()!=NULL && $cliente_detalle->getIdAddenda()!=""){        
        $detalle_addenda = new AddendaDetalle();
        $result = $detalle_addenda->getRegistrosByAdenda($cliente_detalle->getIdAddenda());
        while($rs = mysql_fetch_array($result)){            
            $aux = array();
            array_push($aux, $rs['campo']);
            if(isset($parametros['addenda_'.$rs['id_kaddenda']])){                
                array_push($aux, $parametros['addenda_'.$rs['id_kaddenda']]);
            }else{
                array_push($aux, $rs['valor']);
            }
            array_push($addendas, $aux);
        }
        if(!empty($addendas)){
            $xml->setAddendas($addendas);
            $xmlAbraham->setAddendas($addendas);
            if($cliente_detalle->getMostrarAddenda() == "1"){
                $pdf->setAddendas($addendas);
            }
        }
    }
    //Condiciones de pago
    if($cliente_detalle->getMostarCondicionesPago() == "1"){
        $xml->setCondicionesPago("CONTADO");
        $xmlAbraham->setCondicionesPago("CONTADO");
    }
    
    //XML Abraham
    $xmlAbraham->setReceptor_Dom_CP($localidad->getCodigoPostal());
    $xmlAbraham->setReceptor_rfc($ccliente->getRFC());
    $xmlAbraham->setReceptor_nombre($ccliente->getNombreRazonSocial());
    $xmlAbraham->setReceptor_Dom_Calle($localidad->getCalle());
    $xmlAbraham->setReceptor_Dom_Col($localidad->getColonia());
    $xmlAbraham->setReceptor_Dom_Est($localidad->getEstado());
    $xmlAbraham->setReceptor_Dom_Mun($localidad->getDelegacion());
    $xmlAbraham->setReceptor_Dom_NoExt($localidad->getNoExterior());
    $xmlAbraham->setReceptor_Dom_NoInt($localidad->getNoInterior());
    $xmlAbraham->setReceptor_Dom_Pais("México");
    $xmlAbraham->setConceptos($concepto->getConceptos_array());
    $xmlAbraham->setImpuestos_totalImpuestosTrasladados($subtotal * .16);
    $xmlAbraham->setImpuestos_Trasladado(Array(Array("IVA", "16.00", $subtotal * 0.16)));
    if (!$ndc) {
        $xmlAbraham->setTipoDeComprobante("ingreso");
    } else {
        $xmlAbraham->setTipoDeComprobante("egreso");
    }

    //datos del comprobante
    $cadena_original = "||" . $xml->getVersion() . "|" . $xml->getFecha() . "|" . $xml->getTipoDeComprobante() . "|" . $xml->getFormaDePago();    
    if($cliente_detalle->getMostarCondicionesPago() == "1"){
        $cadena_original .=  "|" . $xml->getCondicionesPago();
    }
    $cadena_original.="|" . $xml->getSubTotal() . "|" . $xml->getMoneda() . "|" . $xml->getTotal() . "|" . $xml->getMetodoDePago() . "|" . $xml->getLugarExpedicion();
    $cadena_original.="|" . $xml->getEmisor_rfc() . "|" . $xml->getEmisor_nombre() . "|" . $xml->getEmisor_Dom_Calle() . "|" . $xml->getEmisor_Dom_NoExt();
    if ($xml->getEmisor_Dom_NoInt() != "") {
        $cadena_original.="|" . $xml->getEmisor_Dom_NoInt();
    }
    $cadena_original.="|" . $xml->getEmisor_Dom_Col() . "|" . $xml->getEmisor_Dom_Mun() . "|" . $xml->getEmisor_Dom_Est() . "|" . $xml->getEmisor_Dom_Pais() . "|" . $xml->getEmisor_Dom_CP();
    $cadena_original.="|" . $xml->getRegimen();
    $cadena_original.="|" . $xml->getReceptor_rfc() . "|" . $xml->getReceptor_nombre() . "|" . $xml->getReceptor_Dom_Calle() . "|" . $xml->getReceptor_Dom_NoExt();
    if ($xml->getReceptor_Dom_NoInt() != "") {
        $cadena_original.="|" . $xml->getReceptor_Dom_NoInt();
    }
    $cadena_original.="|" . $xml->getReceptor_Dom_Col() . "|" . $xml->getReceptor_Dom_Mun() . "|" . $xml->getReceptor_Dom_Est() . "|" . $xml->getReceptor_Dom_Pais() . "|" . $xml->getReceptor_Dom_CP();
    
    $array = $xml->getConceptos();
    for ($i = 0; $i < count($array); $i++) {
        $arr = $array[$i];
        $arr[0] = str_replace(",", "", $arr[0]);               
        $cadena_original.=trim(preg_replace('/\s\s+/', ' ', "|" . $arr[0] . "|" . $arr[1] . "|" . $arr[2] . "|" . $arr[3] . "|" . $arr[4]));
    }
    $array = $xml->getImpuestos_Trasladado();
    for ($i = 0; $i < count($array); $i++) {
        $arr = $array[$i];
        $cadena_original.=trim(preg_replace('/\s\s+/', ' ', "|" . $arr[0] . "|" . $arr[1] . "|" . $arr[2]));
    }
    $cadena_original.="|" . $xml->getImpuestos_totalImpuestosTrasladados();
    /*$array = $xml->getAddendas();
    for ($i = 0; $i < count($array); $i++) {
        $arr = $array[$i];
        $arr[0] = str_replace(",", "", $arr[0]);               
        $cadena_original.=trim(preg_replace('/\s\s+/', ' ', "|" . $arr[0] . "|" . $arr[1]));
    }*/
    $cadena_original.="||";
    $cadena_original = trim(str_replace("| ", "|", str_replace(" |", "|", preg_replace('/\s\s+/', ' ', $cadena_original))));
    //echo $cadena_original;
    $factura->setCadenaOriginal($cadena_original);
    $cfdi = new CFDI();
    $cfdi->setId_Cfdi($empresa->getId_Cfdi());
    $cfdi->getRegistrobyID();
    $key = '../../../CSD/' . $cfdi->getPem();
    $fp = fopen($key, "r");
    if (!$fp) {
        error_log("Unable to open keyfile $key");
    }
    $raw_key_data = fread($fp, 8192);
    //$priv_key = fread($fp, 8192);
    fclose($fp);
    $pkeyid = openssl_get_privatekey($raw_key_data, $cfdi->getCsd_password());
    if ($pkeyid === FALSE) {
        echo "Unable to extract private key from raw key data: " . openssl_error_string();
    }
    //$pkeyid = openssl_get_privatekey($priv_key, "12345678a");
    if (!openssl_sign($cadena_original, $raw_sig, $pkeyid, OPENSSL_ALGO_SHA1)) {
        echo "Unable to sign request $cadena_original: " . openssl_error_string();
        $sello = "";
    }
    //echo $raw_sig;
    $sello = base64_encode($raw_sig);
    $factura->setSello($sello);
    $xml->setSello($sello);
    $base = new Base64Convert();
    $base->setFile("../../../CSD/" . $cfdi->getCsd());
    $xml->setCertificado($base->Convertbase64File());
    $xml->setNoCertificado($cfdi->getNoCertificado());

    $pac = new PAC();
    $pac->setId_pac($empresa->getId_pac());
    $pac->getRegistrobyID();
    $soapclient = new nusoap_client($pac->getDireccion_timbrado(), $esWSDL = true);
    $usuario = $pac->getUsuario();
    $clave = $pac->getPassword();
    /* Guardamos el folio */
    $file = fopen("../../../XML/XML-" . $_GET['id'] . ".xml", "w");
    if(!$factura->folioNotimbrado()){
        $factura->folioReciente();       
    }
    /*if($ndc){
        $factura->setFolio("NC-".$factura->getFolio());
    }*/
    $xml->setFolio(str_replace($xml->getPrefijoSerie(), "", $factura->getFolio()));
    $xmlAbraham->setFolio($xml->getFolio());
    $xmlcreado = $xml->CrearXML();
    /* guarda XML */
    fwrite($file, $xmlcreado);
    fclose($file);
    $factura->setPathXML("XML-" . $_GET['id'] . ".xml");
    $timbrado = FALSE;
    
    //Decodificamos el CFDI a UTF8
    $cfdix = (utf8_decode($xmlcreado));
    if ($reporte->GenerarFactura($xmlcreado)) {
        //Generamos el arreglo con los parametros para timbrado
        $tim = array('cfdi' => $cfdix, 'usuario' => $usuario, 'clave' => $clave);
        //Generamos el llamado al servicio de timbrado
        $soap_timbrado = $soapclient->call('timbrado', $tim);
        //Verificamos que el llamado se pudo hacer correctamente
        if ($soap_timbrado == false) {
            echo "No se logro contactar al PAC";
        } else {            
            if (isset($soap_timbrado['return']['timbre']) && $soap_timbrado['return']['timbre'] != "") {
                $timbrado = TRUE;
                if (isset($_GET['id2']) && $_GET['id2'] != "0") {
                    $factura->setFolio_respaldo($_GET['id2']);
                }                
                $factura->UpdateXMLFactura();
                
                $nombreArchivo = "XML/" . substr($xml->getEmisor_rfc(), 0, 3) . "_" . $factura->getFolio() . ".xml";
                $nombreArchivoPDF = "PDF/" . substr($xml->getEmisor_rfc(), 0, 3) . "_" . $factura->getFolio() . ".pdf";
                $file = fopen("../../../" . $nombreArchivo, "w");                
                //Agregamos addendas si es que hay
                $xml_timbrado = $soap_timbrado['return']['timbre'];
                if(!empty($addendas)){
                    $xmlstr = utf8_encode($xml_timbrado);
                    $sxe = new SimpleXMLElement($xmlstr);
                    //$sxe->addAttribute('tipo', 'documental');
                    $nodo_addenda = $sxe->addChild('Addenda');                    
                    $generales = $nodo_addenda->addChild('Generales');
                    foreach ($addendas as $value) {
                        $aux  = $generales->addChild($value[0], $value[1]);
                    }                    
                    $xml_timbrado = $sxe->asXML();
                    fwrite($file, ($xml_timbrado));   
                }else{
                    fwrite($file, utf8_encode($xml_timbrado));   
                }
                fclose($file);
                $xmls = new XMLReadSAT();
                $xmls->setFile("../../../" . $nombreArchivo);
                $xmls->LeerXML();
                $factura->setSello($sello);
                $factura->setFolioFiscal($xmls->getUuid());
                $factura->setCadenaOriginal($cadena_original);
                
                $catalogo = new Catalogo();

                $fechaInicio = "";
                $fechaFin = "";
                $mes_contrato = "";
                $total_meses = "";
                //Si en el parametro del cliente está encendio mostrar el mes de contrato y además es una factura de arrendamiento
                if($ccliente->getMostarMesContrato() == "1" && $factura->getTipoArrendamiento() == "1"){
                    $result = $contrato->getRegistroValidacion($ccliente->getClaveCliente());    
                    while($rs = mysql_fetch_array($result)){
                        $fechaInicio = $rs['FechaInicio'];
                        $fechaFin = $rs['FechaTermino'];
                    }

                    if(!empty($fechaInicio) && !empty($fechaFin)){
                        $consulta = "SELECT (TIMESTAMPDIFF(MONTH,'$fechaInicio','".$factura->getFechaFacturacion()."')+1) AS MesContrato, "
                                . "TIMESTAMPDIFF(MONTH,'$fechaInicio','$fechaFin') AS DuracionMeses;";
                        $result = $catalogo->obtenerLista($consulta);
                        while($rs = mysql_fetch_array($result)){
                            $mes_contrato = $rs['MesContrato'];
                            $total_meses = $rs['DuracionMeses'];
                        }
                    }
                }

                //*AQUI SE CREA EL PDF                
                $pdf->AddPage('P', 'Legal'); //Agrega hoja, Vertical, Carta
                if (!$ndc) {
                    $pdf->setTitulo("FACTURA");
                    $pdf->setNdc(false);
                } else {
                    $pdf->setTitulo("NOTA DE CRÉDITO");
                    $pdf->setNdc(true);
                }
                $pdf->setFolioFiscal($xmls->getUuid());
                $pdf->setFolio($factura->getFolio());
                $pdf->setLogo($empresa->getArchivoLogo());
                $pdf->setCSD_Emisor($cfdi->getNoCertificado());
                $pdf->setCSD_Sat($cfdi->getNoSAT());
                $pdf->setFecha_Cert(str_replace("T", " ", $xmls->getFecha()));
                $pdf->setLugarExpedicion(strtoupper($empresa->getPais()).", ".strtoupper($empresa->getEstado()));
                $pdf->setHoraEmision(str_replace("T", " ", $xmls->getFecha()));
                $pdf->setFormaPago($factura->getFormaPago());
                if($factura->getDescripcionMetodoPago() != ""){
                    $pdf->setMetodoPago($factura->getMetodoPago()."-".$factura->getDescripcionMetodoPago());
                }else{
                    $pdf->setMetodoPago($factura->getMetodoPago());
                }
                
                $pdf->setCondicionesPago($xml->getCondicionesPago());
                $pdf->setNumeroCtaPago($factura->getNumCtaPago());
                $pdf->setNombre_Emisor($empresa->getRazonSocial());
                $pdf->setRFC_Emisor($empresa->getRFC());
                $pdf->setRegimenFiscal_Emisor($empresa->getRegimenFiscal());
                $pdf->setCalle_Emisor($empresa->getCalle());
                $pdf->setNo_Ext_Emisor($empresa->getNoExterior());
                $pdf->setNo_int_Emisor($empresa->getNoInterior());
                $pdf->setColonia_Emisor($empresa->getColonia());
                $pdf->setEstado_Emisor(strtoupper($empresa->getEstado()));
                $pdf->setDelegacion_Emisor($empresa->getDelegacion());
                $pdf->setPais_Emisor($empresa->getPais());
                $pdf->setCP_Emisor($empresa->getCP());
                $pdf->setTel_Emisor($empresa->getTelefono());
                $resultPeriodo = $factura->getMultiPeriodos();
                if (mysql_num_rows($resultPeriodo) == 0) {
                    $pdf->setPeriodo_Facturacion_Emisor($factura->getFechaFacturacionNombre());
                } else {
                    $periodos = "";                    
                    while ($rs = mysql_fetch_array($resultPeriodo)) {
                        $periodos .= (str_replace(" de", "", substr($catalogo->formatoFechaReportes($rs['Periodo']), 5)) . ", ");
                    }
                    if ($periodos != "") {
                        $periodos = substr($periodos, 0, strlen($periodos) - 2);
                    }
                    $pdf->setPeriodo_Facturacion_Emisor($periodos);
                }
                $pdf->setClaveCliente($ccliente->getClaveCliente());
                $pdf->setNombre_Receptor($ccliente->getNombreRazonSocial());
                $pdf->setRFC_Receptor($ccliente->getRFC());
                $pdf->setRegimenFiscal_Receptor($empresa->getRegimenFiscal());
                $pdf->setCalle_Receptor($localidad->getCalle());
                $pdf->setNo_Ext_Receptor($localidad->getNoExterior());
                $pdf->setNo_int_Receptor($localidad->getNoInterior());
                $pdf->setColonia_Receptor($localidad->getColonia());
                $pdf->setEstado_Receptor($localidad->getEstado());
                $pdf->setDelegacion_Receptor($localidad->getDelegacion());
                $pdf->setPais_Receptor("México$pdf->setClaveCliente($ccliente->getClaveCliente());");
                $pdf->setCP_Receptor($localidad->getCodigoPostal());
                $pdf->setTel_Receptor("");
                $pdf->setLocalidad($localidad->getLocalidad());
                $pdf->setPeriodo_Facturacion_Receptor($factura->getFechaFacturacionNombre());
                $pdf->setSubtotal(number_format($subtotal, 2));
                $concepto->subtotalbyFacturaObsPDF($_GET['id']);
                $pdf->setTabla($concepto->getConceptos_array());
                $pdf->setIva(number_format($subtotal * .16, 2));
                $pdf->setTotal(number_format($subtotal * 1.16, 2));
                $letras = new EnLetras();
                $total_letra = str_replace(",", "", number_format($subtotal * 1.16, 2));
                $total_letra_arr = explode(".", $total_letra);
                $pdf->setNum_Letra(strtoupper($letras->ValorEnLetras($total_letra_arr[0], "")) . " PESOS " . $total_letra_arr[1] . "/100 MN");
                $pdf->setSello_Digital($xmls->getSelloCSD());
                $pdf->setSello_Emisor($xmls->getSelloSAT());
                $pdf->setCadena_SAT($cadena_original);
                $pdf->setComentarios($comentario);
                if(!empty($mes_contrato) && is_numeric($mes_contrato) && !empty($total_meses) && is_numeric($total_meses)){
                    $pdf->setMes_contrato("$mes_contrato de $total_meses");
                }
                $tempDir = "../../../PDF/";
                $cadena = "?re=" . $pdf->getRFC_Emisor() . "&rr=" . $pdf->getRFC_Receptor();
                $tot = $pdf->getTotal();
                $toa = explode(".", $tot);
                $toa[0] = str_replace(",", "", $toa[0]);
                $tot = $toa[0];
                $tod = $toa[1];
                for ($i = 0; $i < 10 - strlen($toa[0]); $i++) {
                    $tot = "0" . $tot;
                }
                for ($i = 0; $i < 6 - strlen($toa[1]); $i++) {
                    $tod = $tod . "0";
                }
                $cadena.="&tt=" . $tot . "." . $tod . "&id=" . $pdf->getFolioFiscal();
                QRcode::png($cadena, $tempDir . $_GET['id'] . ".png");
                $pdf->setCbb($tempDir . $_GET['id'] . ".png");
                $pdf->setLeyenda($_POST['leyenda']);
                if($factura->getMostrarSerie() == "1"){
                    $pdf->setIdPrefactura($_GET['id']);
                    $pdf->CrearPDF_DetalleSerie(false);
                }else{
                    $pdf->setIdPrefactura($_GET['id']);                    
                    $pdf->CrearPDF();
                }
                $pdf->Output("../../../" . $nombreArchivoPDF, 'F');
                //Abraham
                $factAbra = new FacturaAbraham();
                $factAbra->setRFCReceptor($xml->getReceptor_rfc());
                $factAbra->setRFCEmisor($xml->getEmisor_rfc());
                $factAbra->setNombreEmisor($xml->getEmisor_nombre());
                $factAbra->setNombreReceptor($xml->getReceptor_nombre());
                $factAbra->setFechaFacturacion("NOW()");
                $factAbra->setFolio($factura->getFolio());
                $factAbra->setSerie("");
                $factAbra->setFacturaXML(($xmlAbraham->CrearXML()));
                $factAbra->setPathXML($nombreArchivo);
                $factAbra->setPeriodoFacturacion($factura->getPeriodoFacturacion());
                $factAbra->setEstadoFactura("1");
                $factAbra->setFechaModificacion("NOW()");
                $factAbra->setPathPDF($nombreArchivoPDF);
                $factAbra->setFacturaEnviada("0");
                $factAbra->setObservaciones("");
                $factAbra->setFacturaPagada("0");
                $factAbra->setFechaPago("null");
                $factAbra->setNumTransaccion("null");
                $factAbra->setCentrosCosto("");
                $factAbra->setTotal($subtotal * 1.16);
                $factAbra->setCfdiXML(($cfdix));
                $factAbra->setCfdiTimbrado((utf8_encode($soap_timbrado['return']['timbre'])));
                $factAbra->setCfdiRespPac("");
                $factAbra->setFolioFiscal($xmls->getUuid());
                $factAbra->setEstatusFactura("null");
                $factAbra->setCanceladaSAT("null");
                $factAbra->setPendienteCancelar("0");
                $factAbra->setUsuarioCreacion($_SESSION['user']);
                $factAbra->setUsuarioModificacion($_SESSION['user']);
                $factAbra->setUsuarioEnvio("null");
                $factAbra->setFechaEnvio("null");
                $factAbra->setTipoFactura("0");
                $factAbra->setTipoArrendamiento($factura->getTipoArrendamiento());
                $algo = $factura->getIdSerie();
                $diasCredito = $factura->getDiasCredito();
                if(isset($algo)){
                    $factAbra->setIdSerie($factura->getIdSerie());
                }
                if(isset($diasCredito)){
                    $factAbra->setDiasCredito($diasCredito);
                }
                if (!$ndc) {
                    $factAbra->setTipoComprobante("ingreso");
                    $factAbra->setIdFacturaRelacion("0");
                } else {
                    $factAbra->setTipoComprobante("egreso");
                    if (isset($_POST['IdFacturaNET']) && $_POST['IdFacturaNET'] != "0") {
                        $factAbra->setIdFacturaRelacion($_POST['IdFacturaNET']);
                    } else {
                        $factAbra->setIdFacturaRelacion("0");
                    }
                    //Aplicamos la NDC a la factura relacionada.
                    $factura_relacion = new Factura_NET();
                    if ($factura_relacion->getRegistroById($factAbra->getIdFacturaRelacion())) {                        
                        //Agregamos el Pago parcial
                        $pagosParciales = new PagoParcial();
                        $pagosParciales->setId_factura($factura_relacion->getIdFactura());
                        //$pagosParciales->setF($factura_relacion->getFolio());
                        $pagosParciales->setImporte($factAbra->getTotal());                        
                        $pagosParciales->setReferencia("");
                        $pagosParciales->setObservaciones("Pago realizado a partir de la nota de crédito con folio: ".$factAbra->getFolio());
                        $pagosParciales->setFechapago($factAbra->getFechaFacturacion());
                        $pagosParciales->setUsuarioCreacion($_SESSION['user']);
                        if (!$pagosParciales->nuevoRegistro(true)) {
                            echo "<br/>Error: No se pudo registrar el importe pagado.<br/>";                                 
                        }
                        
                        //Marcar pre-factura como cancelada
                        $empresa_relacion = new DatosFacturacionEmpresa();
                        if($empresa_relacion->getRegistroByRFC($factura_relacion->getRFCEmisor())){
                            $consulta = "UPDATE `c_factura` SET EstadoFactura = 1 WHERE Folio IN (SELECT Folio FROM c_folio_prefactura WHERE FolioTimbrado = '".$factura_relacion->getFolio()."' AND IdEmisor = ".$empresa->getId()." ) AND RFCEmisor = ".$empresa->getId().";";
                            $catalogo->obtenerLista($consulta);
                        }
                    } else {
                        //echo "Error: no se pudo aplicar la NDC a la factura relacionada<br/>";
                    }
                }

                if ($factAbra->newRegistro()) {
                    $factura->insertarMultiPeriodosFacturados($factAbra->getIdFactura());
                    $factura->marcarFacturadoEquiposPorFacturar();
                    //$factura->verificarFacturasDobles($factAbra->getRFCReceptor(), substr($factAbra->getPeriodoFacturacion(), 0, 10));
                } else {
                    echo "Error: no se pudo insertar la $tipo CFDI en la base de datos, pero si se timbro ante el SAT<br/>";
                }
                echo "La $tipo <a href='principal.php?mnu=facturacion&action=ReporteFacturacion_net&id=" . $factura->getFolio() . "' target='_blank'>" . $factura->getFolio() . "</a> se timbró exitosamente";
            } else {
                echo "<br/>La $tipo " . $factura->getFolio() . " no se pudo timbrar<br/> Error: " . $soap_timbrado['return']['status'];
            }
        }
    } else {
        echo "Verifique que el folio exista";
    }
    
    if($timbrado){
        $factura->marcarFolioTimbrado();
    }else{
        if(!$factura->nuevoFolioNoTimbrado()){
            echo "<br/>Atención: el folio no se pudo poner como pendiente, es importante reportarlo al adminsitrador del sistema";
        }
    }
} else {
    echo "No se recibió el folio";
}
?>
