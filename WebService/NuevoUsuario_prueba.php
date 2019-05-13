<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/ParametroGlobal.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Mail.class.php");
include_once("../WEB-INF/Classes/ConexionMultiBD.class.php");

function insertarNuevoUsuario($usuario_nuevo, $password_nuevo, $nombre, $apellido_paterno, $apellido_materno, $puesto, $activo, $email, $idempresa, $usuario, $password) {
    $empresa = $idempresa;
    $parametros = new Parametros();
    $parametros->setEmpresa($empresa);
    if($parametros->getRegistroById(36) && $parametros->getValor() != "1"){
        return -6; //No se tiene permiso para insertar usuarios en el sistema
    }
    
    $url = "http://guau.eldirectoriomasperron.mx";
    if($parametros->getRegistroById(8)){
        $url = $parametros->getValor();
    }
    
    $con = new ConexionMultiBD();
    $result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE id_empresa = $empresa;");
    $con->Desconectar();
    $nombre_empresa = "GUAU el directorio más perron";
    while($rs = mysql_fetch_array($result_bases)){
        $nombre_empresa = $rs['nombre_empresa'];
    }
        
    $session = new Session();
    $session->setEmpresa($empresa);
    if (($session->getLogin($usuario, $password)) != "") {
        $user = "NuevoUsuario WS";
        $pantalla = "NuevoUsuario WS";
        $obj = new Usuario();
        $obj_aux = new Usuario();
        $obj->setEmpresa($empresa);
        $obj_aux->setEmpresa($empresa);
        $obj->setPuesto($puesto);
        $obj->setNombre($nombre);
        $obj->setPaterno($apellido_paterno);
        $obj->setMaterno($apellido_materno);
        $obj->setUsuario($usuario_nuevo);
        $obj->setPassword($password_nuevo);
        $obj->setActivo($activo);
        $obj->setUsuarioCreacion($user);
        $obj->setUsuarioModificacion($user);
        $obj->setPantalla($pantalla);
        $obj->setEmail($email);
        if (!$obj->getUsuarioByUser($usuario_nuevo)) {//si no existe el nombre de usuario
            if ($obj_aux->getRegistroByEmail($email)) { //Si el correo electronico ya está registrado.
                if ($obj_aux->getPuesto() == "41") {//Si es usuario fb
                    return -4;
                } else {//Si no es usuario fb
                    return -3;
                }
            }

            
            $mail = new Mail();
            $mail->setEmpresa($empresa);
            $parametroGlobal = new ParametroGlobal();
            $parametroGlobal->setEmpresa($empresa);
            
            $mail->setSubject("Confirmación de alta de usuario");
            if($parametroGlobal->getRegistroById("8")){
                $mail->setFrom($parametroGlobal->getValor());
            }else{
                $mail->setFrom("scg-salida@scgenesis.mx");
            }
            $mail->setTo($email);
            
            $mensaje = "Hemos enviado este correo electrónico de manera automática para confirmar tu identidad, "
                    . "ya que se ha registrado una solicitud de nuevo usuario para <b>$nombre</b>."
                    . "<br/><br/>Para continuar con el proceso da clic en el siguiente enlace <a href='$url/aceptaUsuario.php'>clic aquí</a>"
                    . "<br/>Si no recuerdas haber mandado este correo electrónico probablemnete algún usuario ingreso correo electrónico por error. "
                    . "En tal caso te agradeceríamos si pudieras eliminar este correo."
                    . "<br/><br/>La liga de activación sólo será válida durante 30 minutos.";
            $mail->setBody($mensaje);
            $mail->enviarMail();
            
            $obj->setActivo(1);
            if ($obj->newRegistroSinEcriptar()) {
                $session->setId_usu($obj->getId());
                $respuesta = $session->generarClaveSession(15, "");
                $respuesta['IdUsuario'] = $session->getId_usu();
                $valores = array();
                array_push($valores, $respuesta);
                return json_encode($valores);
            } else {
                return -2;//No se pudo insertar el usuario
            }
        } else {
            $session->setId_usu($obj->getId());
            if ($obj_aux->getRegistroByEmail($email) && $obj_aux->getId() != $obj->getId()) {
                if ($obj_aux->getPuesto() == "41") {//Si es usuario fb
                    return -4;
                } else {//Si no es usuario fb
                    return -3;
                }
            }

            $obj->setEmpresa($empresa);
            $obj->setPuesto($puesto);
            $obj->setNombre($nombre);
            $obj->setPaterno($apellido_paterno);
            $obj->setMaterno($apellido_materno);
            $obj->setUsuario($usuario_nuevo);
            $obj->setPassword($password_nuevo);
            $obj->setActivo($activo);
            $obj->setUsuarioCreacion($user);
            $obj->setUsuarioModificacion($user);
            $obj->setPantalla($pantalla);
            $obj->setEmail($email);
            if ($obj->editarRegistroConPasswordSinEcriptar()) {
                $session->setId_usu($obj->getId());
                $respuesta = $session->generarClaveSession(15, "");
                $respuesta['IdUsuario'] = $session->getId_usu();
                $respuesta['PermisoEP'] = 3; //Permiso para eventos y promociones
                $valores = array();
                array_push($valores, $respuesta);
                return json_encode($valores);
            } else {
                return -5; //Error al editar el password
            }
        }
    } else {
        return -1;
    }
}

$server = new soap_server();
$server->configureWSDL("nuevousuario", "urn:nuevousuario");
$server->register("insertarNuevoUsuario", array("usuario_nuevo" => "xsd:string", "password_nuevo" => "xsd:string", "nombre" => "xsd:string", "apellido_paterno" => "xsd:string",
    "apellido_materno" => "xsd:string", "puesto" => "xsd:int", "activo" => "xsd:int", "email" => "xsd:string", "idempresa" => "xsd:int",
    "usuario" => "xsd:string", "password" => "xsd:string"), array("return" => "xsd:string"), "urn:nuevousuario", "urn:nuevousuario#insertarNuevoUsuario", "rpc", "encoded", "Inserta un nuevo usuario");

$server->service($HTTP_RAW_POST_DATA);
?>