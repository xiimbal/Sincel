<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/ccliente.class.php");
include_once("../WEB-INF/Classes/Componente.class.php");
include_once("../WEB-INF/Classes/Almacen.class.php");
include_once("../WEB-INF/Classes/AlmacenConmponente.class.php");
include_once("../WEB-INF/Classes/DatosFacturacionEmpresa.class.php");
include_once("../WEB-INF/Classes/Factura2.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Contrato.class.php");
include_once("../WEB-INF/Classes/Concepto.class.php");
include_once("../WEB-INF/Classes/ClaveProdServ.class.php");
include_once("../WEB-INF/Classes/PrecioABC.class.php");

function ventas($IdSession, $ClaveCliente, $NoParte, $Cantidad, $Precio, $NombreAlmacen, $ClaveSAT){
    
    $usuario = "Venta WS";
    $pantalla = "Venta WS";
    
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);
    if ($empresa == "0") {
        return -100;
    }    
    
    $catalogo = new Catalogo();
    $catalogo->setEmpresa($empresa);
    
    $cliente = new ccliente();
    $cliente->setEmpresa($empresa);
    if(!$cliente->getregistrobyID($ClaveCliente)){
        return 12;
    }
    
    $componente = new Componente();
    $componente->setEmpresa($empresa);
    if(!$componente->getRegistroById($NoParte)){
        return 13;
    }
 
    $precioABC = new PrecioABC();
    $precioABC->setEmpresa($empresa);
    if(!$precioABC->getRegistroByComponenteAndTipo($NoParte, "A")){
        return 14;  //No se encontro el precio
    }
    
    if($Precio < $precioABC->getPrecio_A()){
        return 20;  //En caso de que el precio sea menor
    }
    
    $almacen = new Almacen();
    $almacen->setEmpresa($empresa);
    if(!$almacen->getRegistroByNombre($NombreAlmacen)){
        return 15;
    }
    $IdAlmacen = $almacen->getIdAlmacen();

    $almacenComponente = new AlmacenComponente();
    $almacenComponente->setEmpresa($empresa);
    if(!$almacenComponente->verificarExistenciaAlmacen($NoParte, $IdAlmacen)){
        return 17;  //El NoParte no fue encontrado en el almacén.
    }
        
    if($almacenComponente->getExistencia() < $Cantidad){
        return 16;
    }
    
    //Ahora vamos a descontar la cantidad del almacén
    $almacenComponente->setCantidadSalida($Cantidad);
    $almacenComponente->setUsuarioModificacion($usuario);
    $almacenComponente->setPantalla($pantalla);
    $almacenComponente->setNoParte($NoParte);
    $almacenComponente->setIdAlmacen($IdAlmacen);
    if(!$almacenComponente->editarCantidadAlmacen()){
        return 18; //No se pudo actualizar la cantidad en el almacén.
    }
    
    $datosFacturacion = new DatosFacturacionEmpresa();
    $datosFacturacion->setEmpresa($empresa);
    $factura2 = new Factura(); //Este objeto guarda los datos en la bd
    $factura2->setEmpresa($empresa);
    $domicilio = new Localidad();
    $domicilio->setEmpresa($empresa);
    $empresa2 = new DatosFacturacionEmpresa();
    $empresa2->setEmpresa($empresa);
    $concepto_obj = new Concepto();
    $concepto_obj->setEmpresa($empresa);
    $contrato = new Contrato();
    $contrato->setEmpresa($empresa);
    
    $empresa2->getRegistroById($cliente->getIdDatosFacturacionEmpresa());
    
    $MetodoPago = "5";
    $FormaPago = "1";
    $IdUsoCFDI = "3";
    $datosFacturacion->getRegistroById($empresa);
    
    if($contrato->getRegistroValidacion2($ClaveCliente)){        
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
    
    if(!$domicilio->getLocalidadByClaveTipo($cliente->getClaveCliente(),"3")){
        $domicilio->getLocalidadByClave($cliente->getClaveCliente());
    }
    
    $idDomicilio = $domicilio->getIdDomicilio();
    
    /*Datos para guardar en la bd*/
    $factura2->setIdEmpresa($datosFacturacion->getIdDatosFacturacionEmpresa());
    //Aunque los campos dicen setRFC, hay que mandarles la clave del cliente y el id de la empresa dee facturacion
    $factura2->setRFCEmisor($datosFacturacion->getIdDatosFacturacionEmpresa()); 
    $factura2->setRFCReceptor($cliente->getClaveCliente());
    $factura2->setPeriodoFacturacion(date("Y")."-".date("m")."-01"); 
    $factura2->setIdDomicilioFiscal($idDomicilio); 
    $factura2->setUsuarioCreacion($usuario); 
    $factura2->setUsuarioUltimaModificacion($usuario);
    $factura2->setPantalla($pantalla);
    $factura2->setFormaPago($FormaPago); 
    $factura2->setMetodoPago($MetodoPago); 
    $factura2->setId_TipoFactura("2");
    $factura2->setNumCtaPago($contrato->getNumeroCuenta());
    $factura2->setTipoArrendamiento("1"); //Se guarda el tipo de arrendamiento 1, todas estas facturas son de arrendamiento
    $factura2->setMostrarSerie("0");
    $factura2->setMostrarUbicacion("0");
    $factura2->setNoContrato($contrato->getNoContrato());
    $factura2->setDiasCredito($contrato->getDiasCredito);
    $factura2->setTotal($Precio * $Cantidad);
    $factura2->setFacturaPagada("0");
    
    if($datosFacturacion->getIdSerie() != ""){
        $factura2->setIdSerie($datosFacturacion->getIdSerie());
    }
    
    if((int)$empresa2->getCfdi33() == 1){
        $factura2->setCFDI33(1);
        $factura2->setIdUsoCFDI($IdUsoCFDI);
    }else{
        $factura2->setCFDI33(0);
    }

    if(!$factura2->NuevaPreFactura()){
        return 19;  //No se pudo registrar la prefactura
    }
    
    if((int)$empresa2->getCfdi33() == 1){
        if(empty($ClaveSAT)){
            $idClave = 51334;
        }else{
            $ClaveProdServ = new ClaveProdServ();
            $ClaveProdServ->setEmpresa($empresa);
            $ClaveProdServ->setClaveProdServ($ClaveSAT);
            if(!$ClaveProdServ->getIdByClaveProdServ()){
                return 20;  //No se encontro la clave en el catálogo del SAT
            }
            $idClave = $ClaveProdServ->getIdProdServ();
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
        $concepto_obj->setUnidad("PIEZA");
        $concepto_obj->setIdEmpresaProductoSAT("");
    }
    $concepto_obj->setIdFactura($factura2->getIdFactura()); 
    $concepto_obj->setPrecioUnitario($Precio);
    $concepto_obj->setCantidad($Cantidad); 
    $concepto_obj->setDescripcion("Concepto desde WS");
    $concepto_obj->setUsuarioCreacion($usuario); 
    $concepto_obj->setUsuarioUltimaModificacion($usuario);
    $concepto_obj->setPantalla($pantalla); 
    $concepto_obj->setTipo("null"); 
    $concepto_obj->setId_articulo("");
    $concepto_obj->setEncabezado("0");
    if(!$concepto_obj->nuevoRegistro()){
        return 21;  //no se pudo registrar el concepto
    }
    return 1;
}

$server = new soap_server();
$server->configureWSDL("ventas", "urn:Ventas"); 
$server->register("ventas",
    array("IdSession" => "xsd:string", "ClaveCliente" => "xsd:string", "NoParte" => "xsd:string",
        "Cantidad" => "xsd:float", "TipoPrecio" => "xsd:string", "NombreAlmacen" => "xsd:string", "ClaveSAT" => "xsd:string"),
    array("return" => "xsd:string"),
    "urn:Ventas",
    "urn:Ventas#ventas",
    "rpc",
    "encoded",
    "Registra todos los pagos a proveedores activos en el sistema.");
 
$server->service($HTTP_RAW_POST_DATA);

?>