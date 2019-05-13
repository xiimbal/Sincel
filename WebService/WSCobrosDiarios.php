<?php
//Fernando
header('Content-Type: text/html; charset=utf-8');
require_once "../lib/nusoap.php";

//include_once("../conexion.php");
include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
//include_once("../Classes/SaldosAFavor.class.php");


function wsDiariosIngresos($IdSession, $ClaveCliente, $Fecha, $TipoFactura){//variables que debe recibir para funcionar
	//revalidar sesion
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);
    $user = $session->getObtenerUsuarioCreacion($IdSession);

    if($empresa == "0"){
        return -100;
    }
    
    $session->setEmpresa($empresa);
    $usuario_obj = new Usuario();
    $usuario_obj->setEmpresa($empresa);
    
    $respuesta = $session->revalidarSesion($IdSession);
    if($respuesta == -1){
        return json_encode(-1);//El id de sesion no fue encontrado activo
    }else{
        $valores = array();
        if($usuario_obj->getRegistroById($session->getId_usu())){
            $respuesta['IdPuesto'] = $usuario_obj->getPuesto(); //Id de puesto
        }else{
            $respuesta['IdPuesto'] = 0;
        }
        array_push($valores, $respuesta);
        //revalidar sesion

        $catalogo_fac = new CatalogoFacturacion();
        $catalogo_fac->setEmpresa($empresa);//se selecciona la base de datos de la empresa

		if (isset($Fecha)){
            $query_fact = "SELECT f.IdFactura from c_factura AS f, c_cliente AS c where c.ClaveCliente = '".$ClaveCliente."' AND c.RFC = f.RFCReceptor AND f.FechaFacturacion = '".$Fecha."';";
            $q_fact = $catalogo_fac->obtenerLista($query_fact);
            while($rs = mysql_fetch_array($q_fact)){
                $IdFactura = $rs["IdFactura"];
                $consulta_pp = "SELECT DISTINCT(f.Total) AS total FROM c_factura AS f, c_cliente AS c, c_pagosparciales AS p WHERE c.RFC = f.RFCReceptor AND f.IDFactura = p.IDFactura AND c.ClaveCliente = '".$ClaveCliente."' AND p.IdFactura = ".$IdFactura.";";
                $pp = $catalogo_fac->obtenerLista($consulta_pp);
                while($ru = mysql_fetch_array($pp)){
                    $total = $ru["total"];
                }

                $consulta_pagado = "SELECT DISTINCT(SUM(p.ImportePagado)) AS Pagado, p.UsuarioCreacion from c_factura AS f,c_cliente AS c,c_pagosparciales AS p WHERE c.RFC = f.RFCReceptor AND f.IDFactura = p.IDFactura AND c.ClaveCliente = '".$ClaveCliente."' AND p.IdFactura = ".$IdFactura.";";
                $cpagado = $catalogo_fac->obtenerLista($consulta_pagado);
                while($ro = mysql_fetch_array($cpagado)){
                    $pagado = $ro["Pagado"];
                    $us_crea = $ro["UsuarioCreacion"];
                }
                $resta = ($total - $pagado);
                array_push($valores, $IdFactura, $Fecha, $ClaveCliente, $pagado, $resta, $us_crea);//lineas_modificadas
                $Ingresos = $Ingresos + $pagado;//lineas_modificadas
            }
            array_push($valores, $Ingresos);//lineas_modificadas
        }
        else{
            $query_fact = "SELECT f.IdFactura from c_factura AS f, c_cliente AS c where c.ClaveCliente = '".$ClaveCliente."' AND c.RFC = f.RFCReceptor AND f.FechaFacturacion = NOW();";
            $q_fact = $catalogo_fac->obtenerLista($query_fact);
            while($rs = mysql_fetch_array($q_fact)){
                $IdFactura = $rs["IdFactura"];
                $consulta_pp = "SELECT DISTINCT(f.Total) AS total FROM c_factura AS f, c_cliente AS c, c_pagosparciales AS p WHERE c.RFC = f.RFCReceptor AND f.IDFactura = p.IDFactura AND c.ClaveCliente = '".$ClaveCliente."' AND p.IdFactura = ".$IdFactura.";";
                $pp = $catalogo_fac->obtenerLista($consulta_pp);
                while($ru = mysql_fetch_array($pp)){
                    $total = $ru["total"];
                }

                $consulta_pagado = "SELECT DISTINCT(SUM(p.ImportePagado)) AS Pagado, p.UsuarioCreacion from c_factura AS f,c_cliente AS c,c_pagosparciales AS p WHERE c.RFC = f.RFCReceptor AND f.IDFactura = p.IDFactura AND c.ClaveCliente = '".$ClaveCliente."' AND p.IdFactura = ".$IdFactura.";";
                $cpagado = $catalogo_fac->obtenerLista($consulta_pagado);
                while($ro = mysql_fetch_array($cpagado)){
                    $pagado = $ro["Pagado"];
                    $us_crea = $ro["UsuarioCreacion"];
                }
                $resta = ($total - $pagado);
                array_push($valores, $IdFactura, $Fecha, $ClaveCliente, $pagado, $resta, $us_crea);//lineas_modificadas
                $Ingresos = $Ingresos + $pagado;//lineas_modificadas
            }
            array_push($valores, $Ingresos);//lineas_modificadas
        }
        return json_encode($valores);
    }
}

$server = new soap_server();
$server->configureWSDL("WSCobrosDiarios", "urn:WSCobrosDiarios"); 
$server->register("wsDiariosIngresos",
    array("IdSesion" => "xsd:string", "ClaveCliente" => "xsd:string", "Fecha" => "xsd:string", "TipoFactura" => "xsd:string"),
    array("return" => "xsd:string"),
    "urn:WSCobrosDiarios",
    "urn:WSCobrosDiarios#wsDiariosIngresos",
    "rpc",
    "encoded",
    "Revalida la sesion en caso que exista y sea la ultima activa");

    $server->service($HTTP_RAW_POST_DATA);
?>