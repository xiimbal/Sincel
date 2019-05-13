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


function wspagos($IdSession, $ClaveCliente, $IdFactura, $monto, $Referencia, $Observaciones, $FechaPago, $idCuentaBancaria){//variables que debe recibir para funcionar
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

        $consulta_rfc = "SELECT Folio AS folio FROM c_factura WHERE IdFactura = ".$IdFactura.";";
            $prueba = $catalogo_fac->obtenerLista($consulta_rfc);            
            while($rs = mysql_fetch_array($prueba)){
                $fol = $rs["folio"];
        }  

        $consulta_pp = "SELECT DISTINCT(f.Total) AS total FROM c_factura AS f, c_cliente AS c, c_pagosparciales AS p WHERE c.RFC = f.RFCReceptor AND f.IDFactura = p.IDFactura AND c.ClaveCliente = '".$ClaveCliente."' AND p.IdFactura = ".$IdFactura.";";
        $pp = $catalogo_fac->obtenerLista($consulta_pp);
        while($ru = mysql_fetch_array($pp)){
            $total = $ru["total"];
        }

        $consulta_pagado = "SELECT DISTINCT(SUM(p.ImportePagado)) AS Pagado from c_factura AS f,c_cliente AS c,c_pagosparciales AS p WHERE c.RFC = f.RFCReceptor AND f.IDFactura = p.IDFactura AND c.ClaveCliente = '".$ClaveCliente."' AND p.IdFactura = ".$IdFactura.";";
        $cpagado = $catalogo_fac->obtenerLista($consulta_pagado);
        while($ro = mysql_fetch_array($cpagado)){
            $pagado = $ro["Pagado"];
        }

        $resta = ($total - $pagado);
        if ($resta < $monto){
            $restante = ($resta - $monto);
            $sobrante = ($restante * -1);
            $restante = 0;
            array_push($valores, $restante);//se devuelve el valor solicitado

            $consulta_pago = "INSERT INTO c_pagosparciales(IdFactura, Folio, ImportePagado, ImportePorPagar, FechaPago, Referencia, Observaciones, UsuarioCreacion, FechaCreacion, idCuentaBancaria) values (".$IdFactura.", ".$fol.", ".$monto.", ".$restante.", NOW(), '".$Referencia."', '".$Observaciones."', '".$user."', NOW(), ".$idCuentaBancaria.")";
            $catalogo_fac->insertarRegistro($consulta_pago);
            $consulta_busqueda = "SELECT DISTINCT RFCCliente FROM c_saldosAFavor AS s, c_factura AS f, c_cliente AS c WHERE s.RFCCliente = f.RFCReceptor AND f.RFCReceptor = c.RFC AND c.ClaveCliente = '".$ClaveCliente."';";
            $query_busqueda = $catalogo_fac->obtenerLista($consulta_busqueda);
            while($ra = mysql_fetch_array($query_IdPP)){
                $rfc_con = $ra["RFCCliente"];
            }
            $con_IdPP = "SELECT DISTINCT IdPagoParcial FROM c_saldosAFavor AS s, c_factura AS f, c_cliente AS c WHERE s.RFCCliente = f.RFCReceptor AND f.RFCReceptor = c.RFC AND c.ClaveCliente = '".$ClaveCliente."';";
            $query_IdPP = $catalogo_fac->obtenerLista($con_IdPP);
            while($Id_PP = mysql_fetch_array($query_IdPP)){
                $pagoParcial = $Id_PP["IdPagoParcial"];
            }
            $insertar_SaF = "INSERT INTO c_saldosAFavor (RFCCliente, IdPagoParcial, Cantidad, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) values ('".$rfc_con."', ".$pagoParcial.", ".$sobrante.", '". $user."', NOW(), '".$user."', NOW(), 'WebService');";
            $catalogo_fac->insertarRegistro($insertar_SaF);
            //insercion del registro del pago
            return json_encode($valores);
        }
        else{
            $restante = ($resta - $monto);
            array_push($valores, $restante);//se devuelve el valor solicitado
            $consulta_pago = "INSERT INTO c_pagosparciales(IdFactura, Folio, ImportePagado, ImportePorPagar, FechaPago, Referencia, Observaciones, UsuarioCreacion, FechaCreacion, idCuentaBancaria) values (".$IdFactura.", ".$fol.", ".$monto.", ".$restante.", NOW(), '".$Referencia."', '".$Observaciones."', '".$user."', NOW(), ".$idCuentaBancaria.")";
            $catalogo_fac->insertarRegistro($consulta_pago);
            //insercion del registro del pago
            return json_encode($valores);
        }
    }
}
/*       

        if ($monto >= 0){
        
            

            
            if ($monto <= $resta){
                
            }
            else if($monto > $resta){
                $restante = ($resta - $monto);
                $sobrante = (($restante)(-1));
                $restante = 0;
                array_push($valores, $restante);//se devuelve el valor solicitado

                $consulta_pago = "INSERT INTO c_pagosparciales(IdFactura, Folio, ImportePagado, ImportePorPagar, FechaPago, Referencia, Observaciones, UsuarioCreacion, FechaCreacion, idCuentaBancaria) values (".$IdFactura.", ".$fol.", ".$monto.", ".$restante.", NOW(), '".$Referencia."', '".$Observaciones."', '".$user."', NOW(), ".$idCuentaBancaria.")";
                $catalogo_fac->insertarRegistro($consulta_pago);
                //insercion del registro del pago

                $consulta_busqueda = "SELECT DISTINCT RFCCliente FROM c_saldosAFavor AS s, c_factura AS f, c_cliente AS c WHERE s.RFCCliente = f.RFCReceptor AND f.RFCReceptor = c.RFC AND c.ClaveCliente = '".$ClaveCliente."';";
                $cbusqueda = $catalogo_fac->obtenerLista($consulta_busqueda);
                if($ro = mysql_fetch_array($cbusqueda)){
                    $rfc = $ro["RFCCliente"];
                    $consulta_sobrante = "UPDATE c_saldosAFavor SET Cantidad = Cantidad + $sobrante WHERE RFCCliente = '".$rfc."'";
                    $catalogo_fac->obtenerLista($consulta_sobrante);
                }
                else{
                    $con_IdPP = "SELECT DISTINCT IdPagoParcial FROM c_saldosAFavor AS s, c_factura AS f, c_cliente AS c WHERE s.RFCCliente = f.RFCReceptor AND f.RFCReceptor = c.RFC AND c.ClaveCliente = '".$ClaveCliente."';";
                    while($Id_PP = mysql_fetch_array($con_IdPP)){
                        $pagoParcial = $Id_PP["IdPagoParcial"];
                    }
                    $insertar_SaF = "INSERT INTO c_saldosAFavor (RFCCliente, IdPagoParcial, Cantidad, UsuarioCreación, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) values ('".$rfc."', ".$pagoParcial.", ".$sobrante.", '". $user."', NOW(), '".$user."', NOW(), 'WebService');";
                }
            }
        
            return json_encode($valores);
        }
        else{

            $consulta_rfc = "SELECT Folio AS folio FROM c_factura WHERE IdFactura = ".$IdFactura.";";
            $prueba = $catalogo_fac->obtenerLista($consulta_rfc);            
            while($rs = mysql_fetch_array($prueba)){
                $fol = $rs["folio"];
            }  

            $consulta_pp = "SELECT DISTINCT(f.Total) AS total FROM c_factura AS f, c_cliente AS c, c_pagosparciales AS p WHERE c.RFC = f.RFCReceptor AND f.IDFactura = p.IDFactura AND c.ClaveCliente = '".$ClaveCliente."' AND p.IdFactura = ".$IdFactura.";";
            $pp = $catalogo_fac->obtenerLista($consulta_pp);
            while($ru = mysql_fetch_array($pp)){
                $total = $ru["total"];
            }

            $consulta_pagado = "SELECT DISTINCT(SUM(p.ImportePagado)) AS Pagado from c_factura AS f,c_cliente AS c,c_pagosparciales AS p WHERE c.RFC = f.RFCReceptor AND f.IDFactura = p.IDFactura AND c.ClaveCliente = '".$ClaveCliente."' AND p.IdFactura = ".$IdFactura.";";
            $cpagado = $catalogo_fac->obtenerLista($consulta_pagado);
            while($ro = mysql_fetch_array($cpagado)){
                $pagado = $ro["Pagado"];
            

            $restante = ($total - $pagado);
            array_push($valores, $restante);//se devuelve el valor solicitado
            return json_encode($valores);
            }
        }
    }       
}*/

$server = new soap_server();
$server->configureWSDL("WSPagos", "urn:WSPagos"); 
$server->register("wspagos",
    array("IdSesion" => "xsd:string", "ClaveCliente" => "xsd:string", "IdFactura" => "xsd:string", "monto" => "xsd:string", "Referencia" => "xsd:string", "Observaciones" => "xsd:string", "FechaPago" => "xsd:string", "idCuentaBancaria" => "xsd:string"),
    array("return" => "xsd:string"),
    "urn:WSPagos",
    "urn:WSPagos#wspagos",
    "rpc",
    "encoded",
    "Revalida la sesion en caso que exista y sea la ultima activa");

    $server->service($HTTP_RAW_POST_DATA);
?>