<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Session.class.php");

/**
 *  
 */
function getProductos($IdTicket, $IdSession) {
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);
    
    if ($empresa == "0") {
        return -100;
    }
    
    $session->setEmpresa($empresa);
    $resultadoLoggin = (int)$session->logginWithSession($IdSession);
    
    if ($resultadoLoggin > 0) {
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        
        $resultados = array();
        $consulta = "SELECT p.NombreComercial , c.NoParte, c.Modelo, c.Descripcion,
            tco.Nombre AS TipoComponente, koc.IdOrdenCompra, koc.IdDetalleOC, koc.Cantidad
            FROM c_ticket AS t
            LEFT JOIN k_tickets_oc AS toc ON toc.IdTicket = t.IdTicket
            LEFT JOIN c_orden_compra AS oc ON oc.Id_orden_compra = toc.IdOrdenCompra
            LEFT JOIN c_proveedor AS p ON p.ClaveProveedor = oc.FacturaEmisor
            LEFT JOIN k_orden_compra AS koc ON koc.IdOrdenCompra = oc.Id_orden_compra
            LEFT JOIN c_componente AS c ON c.NoParte = koc.NoParteComponente
            LEFT JOIN c_tipocomponente AS tco ON tco.IdTipoComponente = c.IdTipoComponente
            WHERE t.IdTicket = $IdTicket ORDER BY NombreComercial";
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)) {
            $aux = array();
            $aux['TipoComponente'] = $rs['TipoComponente'];
            $aux['NoParte'] = $rs['NoParte'];
            $aux['Modelo'] = $rs['Modelo'];
            $aux['Descripcion'] = $rs['Descripcion'];
            $aux['Cantidad'] = $rs['Cantidad'];
            $aux['Proveedor'] = $rs['NombreComercial']; 
            array_push($resultados, $aux);
        }
        return json_encode(array_values($resultados));
    }else{
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("consultaOrdenCompra", "urn:consultaOrdenCompra");
$server->register("getProductos", array("IdTicket" => "xsd:int", "IdSession" => "xsd:string"), 
        array("return" => "xsd:string"), 
        "urn:consultaOrdenCompra", "urn:consultaOrdenCompra#getProductos", 
        "rpc", "encoded", 
        "Obtiene los productos del tiquet");
$server->service($HTTP_RAW_POST_DATA);
?>