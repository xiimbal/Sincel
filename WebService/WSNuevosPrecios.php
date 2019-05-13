<?php
//Fernando
header('Content-Type: text/html; charset=utf-8');
require_once "../lib/nusoap.php";

//include_once("../conexion.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
//include_once("../Classes/SaldosAFavor.class.php");


function wspagos($IdSession, $precio_a, $precio_b, $precio_c, $NoParteEquipo, $NoParteComponente, $IdAlmacen){//variables que debe recibir para funcionar
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
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);//se selecciona la base de datos de la empresa

        $consulta = " INSERT INTO c_precios_abc (Precio_A, Precio_B, Precio_C, NoParteEquipo, NoParteComponente, IdAlmacen, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) VALUES(".$precio_a.", ".$precio_b.", ".$precio_c.", ".$NoParteEquipo.", ".$NoParteComponente.", ".$IdAlmacen.",'".$user."',NOW(),'".$user."',NOW(), 'WService')";
        $catalogo->insertarRegistro($consulta);

        return json_encode($valores);
    }       
}

$server = new soap_server();
$server->configureWSDL("WSPagos", "urn:WSPagos"); 
$server->register("wspagos",
    array("IdSesion" => "xsd:string", "precio_a" => "xsd:string", "precio_b" => "xsd:string", "precio_c" => "xsd:string", "NoParteEquipo" => "xsd:string", "NoParteComponente" => "xsd:string", "IdAlmacen" => "xsd:string"),
    array("return" => "xsd:string"),
    "urn:WSPagos",
    "urn:WSPagos#wspagos",
    "rpc",
    "encoded",
    "Revalida la sesion en caso que exista y sea la ultima activa");

    $server->service($HTTP_RAW_POST_DATA);
?>