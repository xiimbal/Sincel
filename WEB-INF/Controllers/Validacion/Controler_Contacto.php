<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../../Classes/Contacto.class.php");
include_once("../../Classes/CentroCosto.class.php");
$obj = new Contacto();
$cc = new CentroCosto();

if(isset($_POST['clave'])){ //Eliminar el registro
    if($obj->deleteRegistro($_POST['clave'])){
        echo "El registro se ha eliminado con éxito";
    }else{
        echo "<b>Hubo un error al eliminar el registro</b>";
    }
}else{
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    if(isset($parametros['nivel']) && $parametros['nivel'] == "2"){
        if($cc->getRegistroById($parametros['clave_domicilio'])){
            $obj->setClaveEspecialContacto($cc->getClaveCliente());
        }
    }else if(isset($parametros['nivel']) && $parametros['nivel'] == "1"){
        $obj->setClaveEspecialContacto($parametros['clave_domicilio']);
    }else{
        $obj->setClaveEspecialContacto($parametros['cliente']);
    }

    $obj->setNombre($parametros['nombre_contacto2']);
    $obj->setCorreoElectronico($parametros['correo_contacto2']);
    $obj->setTelefono($parametros['telefono_contacto2']);
    $obj->setCelular($parametros['celular_contacto2']); 
    $obj->setIdTipoContacto($parametros['tipo_contacto2']);
    
    if (isset($parametros['envio_factura']) && $parametros['envio_factura'] == "on"){ //verifica si esta activado el boton de todo el grupo
        $obj->setEnvioFactura(1);    
    }else{
        $obj->setEnvioFactura(0);      
    }
    
    if (isset($parametros['contacto_cobranza']) && $parametros['contacto_cobranza'] == "on"){ //verifica si esta activado el boton de todo el grupo
        $obj->setContactoCobranza(1);    
    }else{
        $obj->setContactoCobranza(0);      
    }
    
    if (isset($parametros['activo']) && $parametros['activo'] == "on"){ //verifica si esta activado el boton de todo el grupo
        $obj->setActivo(1);    
    }else{
        $obj->setActivo(0);      
    }

    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setPantalla('PHP valida contacto');

    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo $obj->getIdContacto();
        } else {
            echo "Error:El contacto no se pudo registrar, intenta más tarde por favor";
        }
    } else {/* Modificar */
        $obj->setIdContacto($parametros['id']);
        if ($obj->editRegistro()) {
            echo $obj->getIdContacto();
        } else {
            echo "Error:El contacto no se pudo modificar, intenta más tarde por favor";
        }
    }
}
?>