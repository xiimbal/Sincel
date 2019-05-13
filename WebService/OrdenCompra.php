<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Orden_Compra.class.php");
include_once("../WEB-INF/Classes/Detalle_Orden_Compra.class.php");

function crearOrdenCompra($IdSession, $NoPedido, $Total, $Emisor, $Productos){
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }
    
    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);
    if ($resultadoLoggin > 0) {
        $user_obj = new Usuario();
        $user_obj->setEmpresa($empresa);
        $usuario = "";
        
        if ($user_obj->getRegistroById($resultadoLoggin)) {
            $usuario = $user_obj->getUsuario();
        }
        
        $ordenCompra = new Orden_Compra();
        $detOC = new Detalle_Orden_Compra();
        
        $ordenCompra->setEmpresa($empresa);
        $detOC->setEmpresa($empresa);
        
        $ordenCompra->setNo_pedido($NoPedido);
        $ordenCompra->setFechaOC(date("Y")."-".date("m")."-".date("d"));
        $ordenCompra->setFacturaEmisor($Emisor);
        $ordenCompra->setEstatus(71);
        $ordenCompra->setFactura_Ticket(0);
        $ordenCompra->setSubtotal_Ticket($Total / 1.16);
        $ordenCompra->setTotal_Ticket($Total);
        $ordenCompra->setActivo(1);
        $ordenCompra->setUsuarioCreacion($usuario);
        $ordenCompra->setUsuarioModificacion($usuario);
        $ordenCompra->setPantalla("WS Orden de Compra");
        
        if(!$ordenCompra->newRegistro()){
            return -5;
        }
        
        $detOC->setUsuarioCreacion($usuario);
        $detOC->setUsuarioModificacion($usuario);
        $detOC->setPantalla("WS Orden de compra");
        $detOC->setIdOrdenCompra($ordenCompra->getIdOrdenCompra());
        foreach($Productos as $producto){
            $varcomp = explode(" // ", $producto['producto']);
            $detOC->setNoParteComponente($varcomp[1]);
            $detOC->setCantidad($producto['cantidad']);
            $total = (int) $producto['cantidad'] * (float) $producto['precioCompra'];
            $detOC->setPrecioUnitario($producto['precioCompra']);
            $detOC->setCostoTotal($total); //precio pesos
            
            if (!$detOC->newRegistroCompnente()) {
                return -6;
            }
        }
        return $ordenCompra->getIdOrdenCompra();
    }else{
        return $resultadoLoggin;
    }
}

$server = new soap_server();
$server->configureWSDL("OrdenCompra", "urn:OrdenCompra");

// Parametros de entrada
$server->wsdl->addComplexType(  'datos_oc', 
                                'complexType', 
                                'struct', 
                                'all', 
                                '',
                                array('cantidad'              => array('name' => 'cantidad','type' => 'xsd:int'),
                                      'tipoComponente'        => array('name' => 'tipoComponente','type' => 'xsd:int'),
                                      'producto'              => array('name' => 'producto','type' => 'xsd:string'),
                                      'precioCompra'          => array('name' => 'precioCompra','type' => 'xsd:float'))
);

$server->register("crearOrdenCompra", 
        array("IdSession" => "xsd:string","NoPedido" => "xsd:string","Total" => "xsd:float", "Emisor" => "xsd:string", "Productos" => "tns:datos_oc"), 
        array("return" => "xsd:string"), "urn:OrdenCompra", "urn:OrdenCompra#crearOrdenCompra", "rpc", "encoded", "Inserta una orden de compra");
$server->service($HTTP_RAW_POST_DATA);


?>
