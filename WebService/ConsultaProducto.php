<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/HistoricoPosiciones.class.php");
include_once("../WEB-INF/Classes/Session.class.php");

/**
 *  
 */
function getProductos($ClaveCliente, $Latitud, $Longitud, $IdSession) {
    $empresa = 3;    
    $session = new Session();
    $session->setEmpresa($empresa);    

    $resultadoLoggin = (int)$session->logginWithSession($IdSession);
    
    if (empty($IdSession) || $resultadoLoggin > 0) {
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        
        $historico_posiciones = new HistoricoPosiciones();
        $historico_posiciones->setEmpresa($empresa);
        
        $resultados = array();
        $id_almacen = "";
        $consulta = "SELECT a.id_almacen, a.nombre_almacen, cl.ClaveCliente, p.NombreComercial , cl.NombreRazonSocial, cc.Nombre AS Localidad, c.NoParte, c.Modelo, c.Descripcion, kac.cantidad_existencia,
            pabc.Precio_A, pabc.Precio_B, pabc.Precio_C, tco.Nombre AS TipoComponente,
            (CASE WHEN cl.IdEstatusCobranza = 1 THEN tc.Radio ELSE (SELECT Radio FROM c_tipocliente WHERE IdTipoCliente = 1) END) AS Radio_calculado,
            111.1111 *
            DEGREES(ACOS(COS(RADIANS(da.Latitud))
                            * COS(RADIANS($Latitud))
                            * COS(RADIANS(da.Longitud - $Longitud))
                            + SIN(RADIANS(da.Latitud))
                            * SIN(RADIANS($Latitud)))) AS distance_in_km
            FROM c_almacen AS a
            LEFT JOIN c_domicilio_almacen AS da ON da.IdAlmacen = a.id_almacen
            LEFT JOIN k_minialmacenlocalidad AS mini ON mini.IdAlmacen = a.id_almacen
            LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = mini.ClaveCentroCosto
            LEFT JOIN c_cliente AS cl ON cl.ClaveCliente = cc.ClaveCliente
            LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
            LEFT JOIN k_almacencomponente AS kac ON kac.id_almacen = a.id_almacen
            LEFT JOIN c_orden_compra AS co ON co.Id_orden_compra = kac.IdOrdenCompra
            LEFT JOIN c_proveedor AS p ON p.ClaveProveedor = co.FacturaEmisor
            LEFT JOIN c_componente AS c ON c.NoParte = kac.NoParte
            LEFT JOIN c_tipocomponente AS tco ON tco.IdTipoComponente = c.IdTipoComponente
            LEFT JOIN c_precios_abc AS pabc ON pabc.NoParteComponente = c.NoParte AND pabc.IdAlmacen = a.id_almacen
            WHERE cc.ClaveCliente = '$ClaveCliente'
            GROUP BY a.id_almacen, c.NoParte
            ORDER BY distance_in_km ASC, a.Prioridad ASC;";
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)) {
            if(!empty($id_almacen) && $id_almacen != $rs['id_almacen']){
                break;
            }
            $aux = array();
            $aux['TipoComponente'] = $rs['TipoComponente'];
            $aux['NoParte'] = $rs['NoParte'];
            $aux['Modelo'] = $rs['Modelo'];
            $aux['Descripcion'] = $rs['Descripcion'];
            $aux['PrecioA'] = $rs['Precio_A'];
            //$aux['PrecioB'] = $rs['Precio_B'];
            //$aux['PrecioC'] = $rs['Precio_C'];            
            $aux['CantidadExistencia'] = $rs['cantidad_existencia'];
            $aux['Almacen'] = $rs['nombre_almacen'];
            $aux['Negocio'] = $rs['NombreRazonSocial'];
            $aux['Proveedor'] = $rs['NombreComercial'];
            array_push($resultados, $aux);
            $id_almacen = $rs['id_almacen'];
        }        
        
        if(empty($IdSession)){
            $historico_posiciones->setIdUsuario("NULL");
            $historico_posiciones->setUsuarioCreacion(""); $historico_posiciones->setUsuarioUltimaModificacion("");
        }else{
            $historico_posiciones->setIdUsuario($resultadoLoggin);
            $historico_posiciones->setUsuarioCreacion($resultadoLoggin); $historico_posiciones->setUsuarioUltimaModificacion($resultadoLoggin);
        }
        $historico_posiciones->setLatitud($Latitud); $historico_posiciones->setLongitud($Longitud);
        $historico_posiciones->setRadio("NULL");
        $historico_posiciones->setClaveCliente($ClaveCliente);
        $historico_posiciones->setIdGiro("NULL"); $historico_posiciones->setIdTipoContacto("NULL");
        
        $historico_posiciones->setIdWebService(8);
        $historico_posiciones->setPantalla("Cliente Consulta WS24");
                
        $historico_posiciones->setRespuesta(json_encode(array_values($resultados)));
        $historico_posiciones->newRegistro();
        
        return json_encode(array_values($resultados));
    }else{
        return json_encode($resultadoLoggin);
    }    
}

$server = new soap_server();
$server->configureWSDL("consultaProducto", "urn:consultaProducto");
$server->register("getProductos", array("ClaveCliente" => "xsd:string", "Latitud" => "xsd:float", "Longitud" => "xsd:float", "IdSession" => "xsd:string"), 
        array("return" => "xsd:string"), 
        "urn:consultaProducto", "urn:consultaProducto#getProductos", 
        "rpc", "encoded", 
        "Obtiene los productos del cliente");
$server->service($HTTP_RAW_POST_DATA);
?>