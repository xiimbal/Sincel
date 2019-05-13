<?php
require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
 
function getVersion($ParamVersionMovil, $usuario, $password) {
    $empresa = 3;
    $catalogo = new Catalogo();
    $catalogo->setEmpresa($empresa);
    $session = new Session();
    $session->setEmpresa($empresa);
    if($session->getLogin($usuario, $password)){
        $consulta = "SELECT MAX(IdVersion) AS IdVersion, DATE(Fecha) AS Fecha FROM `c_versioncategorias`;";
        $result = $catalogo->obtenerLista($consulta);
        $ParamVersionServer = 0;
        while($rs = mysql_fetch_array($result)){
            $ParamVersionServer = (int)$rs['IdVersion'];
        }    

        if($ParamVersionMovil < $ParamVersionServer){        
            $separator = "||";
            $cadena_enviar = "\"".$ParamVersionServer."\",";
            $consulta = "SELECT IdGiro, Nombre FROM `c_giro` WHERE Activo = 1;";
            $result = $catalogo->obtenerLista($consulta);
            while($rs = mysql_fetch_array($result)){
                $cadena_enviar .= ("\"".$rs['IdGiro']."\",\"".$rs['Nombre']."\",");
            }
            if($cadena_enviar != ""){//Quitamos el ultimo separador que se concateno, ya que despues no hay datos
                $cadena_enviar = substr($cadena_enviar, 0, strlen($cadena_enviar)-2);
            }
            return $cadena_enviar;
        }else{
            return "0";
        }
    }else{
        return "Error: Usuario y/o password incorrecto";
    }    
}
 
$server = new soap_server();
$server->configureWSDL("versioncategoria", "urn:versioncategoria"); 
$server->register("getVersion",
    array("ParamVersionMovil" => "xsd:integer", "usuario" => "xsd:string", "password" => "xsd:string"),
    array("return" => "xsd:string"),
    "urn:versioncategoria",
    "urn:versioncategoria#getVersion",
    "rpc",
    "encoded",
    "Obtiene la ultima version de categorias");
 
$server->service($HTTP_RAW_POST_DATA);
/*$server->register("getProd");
$server->service($HTTP_RAW_POST_DATA);*/
?>