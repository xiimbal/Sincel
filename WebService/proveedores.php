<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

function obtenerProveedores($IdSession, $Estado, $Pagina) {
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);
    
    $session->setEmpresa($empresa);
    $user_obj = new Usuario();
    $user_obj->setEmpresa($empresa);
   
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);

    if ($resultadoLoggin > 0) {
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $arrayProveedor = array();
        $registros_por_pagina = 10;
        $where = "";
        $i = 0;
        
        if((int)$Estado == 1){
            $where = "AND oc.Estatus NOT IN(70,59,16)";
        }
        $indice = $registros_por_pagina * ($Pagina-1);
        
        $consulta = "SELECT p.*,tp.Nombre AS tipoProveedor,fp.Descripcion AS FormaPago,
            cb.noCuenta
            FROM c_proveedor AS p 
            LEFT JOIN c_tipoproveedor AS tp ON tp.IdTipoProveedor = p.IdTipoProveedor
            LEFT JOIN c_orden_compra AS oc ON oc.FacturaEmisor = p.ClaveProveedor
            LEFT JOIN c_formapago AS fp ON fp.IdFormaPago = p.FormaPago
            LEFT JOIN c_cuentaBancaria AS cb ON cb.idCuentaBancaria = p.CuentaBancaria
            WHERE p.Activo = 1 $where 
            GROUP BY oc.FacturaEmisor
            LIMIT $indice,$registros_por_pagina";
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            array_push($arrayProveedor, array("ClaveProveedor" => $rs['ClaveProveedor'],
                "Nombre" => $rs['NombreComercial'],"IdTipo" => $rs['IdTipoProveedor'],"TipoProveedor" => $rs['tipoProveedor'],
                "RFC" => $rs['RFC'], "Telefono" => $rs['Telefono'], "Correo" => $rs['Correo'], "FormaPago" => $rs['FormaPago'],
                "CuentaBancaria" => $rs['noCuenta'],"DiasCredito" => $rs['DiasCredito']));
        }
        return json_encode(array_values($arrayProveedor));
    }else{
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("proveedores", "urn:proveedores"); 
$server->register("obtenerProveedores",
    array("IdSession" => "xsd:string", "Estado" => "xsd:int", "Pagina" => "xsd:int"),
    array("return" => "xsd:string"),
    "urn:proveedores",
    "urn:proveedores#obtenerProveedores",
    "rpc",
    "encoded",
    "Obtiene todos los proveedores y sus tipos, activos en el sistema.");
 
$server->service($HTTP_RAW_POST_DATA);


?>

