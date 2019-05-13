<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Orden_Compra.class.php");
include_once("../WEB-INF/Classes/PagoParcialProveedor.class.php");

function pagoProveedor($IdSession, $OC, $Cantidad) {
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);
    
    if ($empresa == "0") {
        return -100;
    }
    
    if(empty($OC)){
        return -4;  //No se recibió el parámetro de la orden de compra
    }
    
    if(empty($Cantidad)){
        return -5;  //No se recibió el parámetro de la cantidad
    }
    
    if($Cantidad  < 0){
        return -6;
    }
    
    //Verificamos que exista la OC
    $ordenCompra = new Orden_Compra();
    $ordenCompra->setEmpresa($empresa);
    if(!$ordenCompra->getRegistroById($OC)){
        return 31;
    }
    //Primero buscaremos que exista una factura para esa orden de compra.
    $catalogo = new Catalogo();
    $catalogo->setEmpresa($empresa);
    $consulta = "SELECT fp.* FROM c_factura_proveedor AS fp
        INNER JOIN c_orden_compra AS oc ON fp.IdOrdenCompra = oc.Id_orden_compra 
        WHERE Id_orden_compra = $OC GROUP BY(oc.Id_orden_compra)";
    $result = $catalogo->obtenerLista($consulta);
    if(mysql_num_rows($result) > 0){
        if($rs = mysql_fetch_array($result)){   //Se encontró la factura y se va a hacer el pago
            //Si existe procederemos a realizar el pago.
            $pago = new PagoParcialProveedor();
            $pago->setEmpresa($empresa);
            
            if($pago->verificaPagoMayor($Cantidad, $rs['IdFacturaProveedor'], "0")){
                return -8;  //El pago es mayor a lo que falta por pagar de la factura
            }
            
            $pago->setReferencia('');
            $pago->setId_factura($rs['IdFacturaProveedor']);
            $pago->setObservaciones('');
            $pago->setFechapago(date("Y-m-d"));
            $pago->setImporte($Cantidad);
            $pago->setPantalla("PHP WS pagoproveedores");
            $pago->setUsuarioCreacion($_SESSION['user']);
            $pago->setUsuarioUltimaModificacion($_SESSION['user']);
            
            if ($pago->nuevoRegistro()){
                return 1;
            } else {
                return -9; //No se pudo registrar el pago.           
            }
        }
    }else{
        return -7; //La orden de compra no tiene relacionada una factura de proveedor
    }
}

$server = new soap_server();
$server->configureWSDL("pagoproveedores", "urn:pagoproveedores"); 
$server->register("pagoProveedor",
    array("IdSession" => "xsd:string", "OC" => "xsd:int", "Cantidad" => "xsd:float"),
    array("return" => "xsd:string"),
    "urn:pagoproveedores",
    "urn:pagoproveedores#pagoProveedor",
    "rpc",
    "encoded",
    "Registra todos los pagos a proveedores activos en el sistema.");
 
$server->service($HTTP_RAW_POST_DATA);

?>
