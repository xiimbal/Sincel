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


function wspagos($IdSession, $NoParteComponente){//variables que debe recibir para funcionar
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

        $consulta = "SELECT Precio_A, Precio_B, Precio_C from c_precios_abc where NoParteComponente = ".$NoParteComponente."";
        $resultado = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($resultado)){
            $PreA = $rs["Precio_A"];
            $PreB = $rs["Precio_B"];
            $PreC = $rs["Precio_C"];
        }

        array_push($valores, $PreA, $PreB, $PreB);

        return json_encode($valores);
    }       
}

$server = new soap_server();
$server->configureWSDL("WSPagos", "urn:WSPagos"); 
$server->register("wspagos",
    array("IdSesion" => "xsd:string", "NoParteComponente" => "xsd:string"),
    array("return" => "xsd:string"),
    "urn:WSPagos",
    "urn:WSPagos#wspagos",
    "rpc",
    "encoded",
    "Revalida la sesion en caso que exista y sea la ultima activa");

    $server->service($HTTP_RAW_POST_DATA);
?>