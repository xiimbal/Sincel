<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Factura2.class.php");
include_once("../../Classes/CatalogoFacturacion.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Concepto.class.php");
include_once("../../Classes/XMLReadAbraham.class.php");
include_once("../../Classes/Empresa.class.php");
include_once("../../Classes/Cliente.class.php");
include_once("../../Classes/Contrato.class.php");

$cat = new CatalogoFacturacion();
$catalogo = new Catalogo();
$cancelada = true; //es para saber si la factura esta cancelada
if (empty($_POST['Tiporelacion'])) { //Si se manda un valor por Tiporelacion $cancelada es verdadero
    $cancelada = false;
}
$query = $cat->obtenerLista("SELECT Folio,FacturaXML,PeriodoFacturacion,RFCEmisor,RFCReceptor,TipoArrendamiento,CFDI33,Serie FROM c_factura WHERE IdFactura=" . $_POST['id']);

if ($rs = mysql_fetch_array($query)) {
    //Buscamos la prefactura que generó esta factura
    $consulta = "SELECT f.IdFactura
        FROM c_factura AS f,
        (SELECT Folio, IdEmisor 
        FROM c_folio_prefactura AS fp
        WHERE fp.IdEmisor = (SELECT IdDatosFacturacionEmpresa FROM c_datosfacturacionempresa WHERE RFC = '" . $rs['RFCEmisor'] . "' LIMIT 1)  AND FolioTimbrado = '" .$rs['Serie'] . $rs['Folio'] . "') AS fp
        WHERE f.Folio = fp.Folio AND f.RFCEmisor = fp.IdEmisor;";
    $result = $catalogo->obtenerLista($consulta);    
    if (mysql_num_rows($result) > 0) {//Si se encontro la prefactura, de ahí se copiaran los datos                        
        $factura_original = new Factura();
        $factura_copia = new Factura();
        while($rs_prefactura = mysql_fetch_array($result)){
            $factura_original->setIdFactura($rs_prefactura['IdFactura']);
        }
        
        if($factura_original->getRegistrobyID()){
            $pantalla = "PHP Copiar Factura Lectura";
            $factura_copia->setRFCReceptor($factura_original->getRFCReceptor());
            $factura_copia->setRFCEmisor($factura_original->getRFCEmisor());
            $factura_copia->setTipoComprobante($factura_original->getTipoComprobante());
            $factura_copia->setMetodoPago($factura_original->getMetodoPago());
            $factura_copia->setFormaPago($factura_original->getFormaPago());
            $factura_copia->setNumCtaPago($factura_original->getNumCtaPago());
            $factura_copia->setIdDomicilioFiscal($factura_original->getIdDomicilioFiscal());
            $factura_copia->setId_TipoFactura($factura_original->getId_TipoFactura());
            $factura_copia->setTipoArrendamiento($factura_original->getTipoArrendamiento());
            $factura_copia->setMostrarSerie($factura_original->getMostrarSerie());
            $factura_copia->setMostrarUbicacion($factura_original->getMostrarUbicacion());
            $factura_copia->setDescuentos($factura_original->getDescuentos());
            $factura_copia->setIdSerie($factura_original->getIdSerie());
            $factura_copia->setNoContrato($factura_original->getNoContrato());
            $factura_copia->setDiasCredito($factura_original->getDiasCredito());
            $factura_copia->setIdUsoCFDI($factura_original->getIdUsoCFDI());
            $factura_copia->setCFDI33($factura_original->getCFDI33());
            $factura_copia->setUsuarioCreacion($_SESSION['user']);
            $factura_copia->setUsuarioUltimaModificacion($_SESSION['user']);
            $factura_copia->setPantalla($pantalla);
            if($factura_copia->NuevaPreFactura()){
                //Copiamos los conceptos de la nueva 
                $consulta = "INSERT INTO c_conceptos(idConcepto,Cantidad,Unidad,Descripcion, PrecioUnitario, idFactura, Encabezado, Descuento,
                    UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,
                    Tipo, id_articulo,IdEmpresaProductoSAT)
                    SELECT 0,Cantidad,Unidad,Descripcion, PrecioUnitario, ".$factura_copia->getIdFactura().", Encabezado, Descuento,
                    '".$_SESSION['user']."',NOW(),'".$_SESSION['user']."',NOW(),'$pantalla',
                    Tipo, id_articulo,IdEmpresaProductoSAT
                    FROM `c_conceptos` 
                    WHERE idFactura = ".$factura_original->getIdFactura().";";
                //echo $consulta;
                $result = $catalogo->obtenerLista($consulta);
                if($result != "0"){
                    $consulta = "INSERT INTO c_facturadetalle(IdFacturaDetalle,IdFactura,IdBitacora,ClaveCentroCosto,Color,IdKServicio,IdServicio,RentaMensual,IncluidosBN,IncluidosColor,
                        CostoExcedentesBN,CostoExcedentesColor,CostoProcesadosBN,CostoProcesadosColor,ContadorBN,ContadorBNAnterior,ContadorColor,ContadorColorAnterior,
                        ContadorProcesadasBN,ContadorExcedentesBN,ContadorProcesadasColor,ContadorExcedentesColor,Ubicacion,NumeroPartida,
                        UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                        SELECT 0,".$factura_copia->getIdFactura().",IdBitacora,ClaveCentroCosto,Color,IdKServicio,IdServicio,RentaMensual,IncluidosBN,IncluidosColor,
                        CostoExcedentesBN,CostoExcedentesColor,CostoProcesadosBN,CostoProcesadosColor,ContadorBN,ContadorBNAnterior,ContadorColor,ContadorColorAnterior,
                        ContadorProcesadasBN,ContadorExcedentesBN,ContadorProcesadasColor,ContadorExcedentesColor,Ubicacion,NumeroPartida,
                        '".$_SESSION['user']."',NOW(),'".$_SESSION['user']."',NOW(),'$pantalla'
                        FROM `c_facturadetalle` 
                        WHERE IdFactura = ".$factura_original->getIdFactura().";";
                    //echo $consulta;
                    $result = $catalogo->obtenerLista($consulta);
                    if($result != "0"){
                        if ($cancelada && $factura_copia->getCFDI33()== '1') {
                            $NumTipo= $_POST['Tiporelacion'];
                            echo "Se generó la pre-factura con el folio 
                                <a href='principal.php?mnu=facturacion&action=alta_factura_33&id=" . $factura_copia->getIdFactura() . "&param1=egreso&tipo=" . $NumTipo ."&idfacAsust=".$_POST['id']." ' target='_blank'>" . $factura_copia->getFolio() . "</a>, se puede consultar en facturas lecturas";
                                //<a href='principal.php?mnu=facturacion&action=alta_factura_33&id=" . $factura_copia->getIdFactura() . "&param1=egreso&tipo=" . $NumTipo ."&idfacAsust=".$_POST['id']." ' target='_blank'>" . $factura_copia->getFolio() . "</a>, se puede consultar en facturas lecturas idCirou ".$_POST['id']. "idFActura " . $factura_copia->getIdFactura();
                        }else if($factura_copia->getCFDI33() == "1"){
                            echo "Se generó la pre-factura con el folio 
                                <a href='principal.php?mnu=facturacion&action=alta_factura_33&id=" . $factura_copia->getIdFactura() . "' target='_blank'>" . $factura_copia->getFolio() . "</a>, se puede consultar en facturas lecturas";
                        }else{
                            echo "Se generó la pre-factura con el folio 
                                <a href='principal.php?mnu=facturacion&action=alta_factura&id=" . $factura_copia->getIdFactura() . "' target='_blank'>" . $factura_copia->getFolio() . "</a>, se puede consultar en facturas lecturas";
                        }
                    }else{
                        echo "Error: no se pudieron copiar los detalles a la factura copiada, favor de reportarlo con el administrador";
                    }
                }else{
                    echo "Error: no se pudieron copiar los conceptos a la factura copiada, favor de reportarlo con el administrador";
                }
            }else{
                echo "Error: La factura copiada no se pudo registrar, favor de reportarlo con el administrador ";
            }
        }else{
            echo "Error: no se pudo obtener la información a copiar, favor de reportarlo con el administrador";
        }
    } else {//No se encontro la pre-factura, los datos se tomaran del XML directamente, aunque se perderan algunos detalles.
        $xml = new XMLReadAbraham();
        if (!$xml->ReadXMLSinValidacion($rs['FacturaXML'])) {
            echo "Error: no se pudo leer el XML correctamente";
            return;
        }
        $empresa = new Empresa();
        $empresa->setRFC($rs['RFCEmisor']);
        $empresa->getRegistrobyRFC();
        $factura = new Factura();
        $factura->setIdEmpresa($empresa->getId());
        $factura->setMetodoPago($xml->getMetodoDePago());
        $factura->setFormaPago($xml->getFormaDePago());
        //$factura->setPeriodoFacturacion($rs['PeriodoFacturacion']);
        $cliente = new Cliente();
        $cliente->getRegistroByRFC($rs['RFCReceptor']);
        $factura->setRFCReceptor($cliente->getClaveCliente());
        $factura->setRFCEmisor($empresa->getId());
        $factura->setUsuarioCreacion($_SESSION['user']);
        $factura->setUsuarioUltimaModificacion($_SESSION['user']);
        $factura->setPantalla("PHP Copiar Factura Lectura");
        $factura->setId_TipoFactura(1);
        $factura->setCFDI33($rs['CFDI33']);
        if (isset($rs['TipoArrendamiento']) && $rs['TipoArrendamiento'] != "") {
            $factura->setTipoArrendamiento($rs['TipoArrendamiento']);
        }

        $contrato = new Contrato();
        $result = $contrato->getRegistroValidacion($cliente->getClaveCliente());
        while ($rs = mysql_fetch_array($result)) {
            $factura->setNumCtaPago($rs['NumeroCuenta']);
        }

        if ($factura->NuevaPreFactura()) {
            $concepto = new Concepto();
            $concepto->setIdFactura($factura->getIdFactura());
            $concepto->setPantalla("PHP Controller_nuevo_concepto");
            $concepto->setFechaCreacion("NOW()");
            $concepto->setFechaUltimaModificacion("NOW()");
            $concepto->setUsuarioCreacion($_SESSION['user']);
            $concepto->setTipo("null");
            $concepto->setId_articulo("null");
            $concepto->setUsuarioUltimaModificacion($_SESSION['user']);
            foreach ($xml->getConceptos() as $val) {
                $concepto->setCantidad($val[0]);
                if ($val[1] == 0) {
                    $concepto->setUnidad("Servicio");
                } else {
                    $concepto->setUnidad($val[1]);
                }
                $concepto->setDescripcion($val[2]);
                $concepto->setPrecioUnitario($val[3]);
                $concepto->nuevoRegistro();
            }
            echo "Se generó la pre-factura con el folio 
            <a href='principal.php?mnu=facturacion&action=alta_factura&id=" . $factura->getIdFactura() . "' target='_blank'>" . $factura->getFolio() . "</a>, se puede consultar en facturas lecturas";
        } else {
            echo "Error: La factura copiada no se pudo registrar, favor de reportarlo con el administrador ";
        }
    }
} else {
    echo "Error: no se encontro el xml, favor de reportarlo con el administrador";
}

