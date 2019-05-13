<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/FlujoFalla.class.php");
$obj = new FlujoFalla();

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    
    $obj->setIdEstado($_GET['id']);
    if ($obj->deleteFlujoByEstado() && $obj->deleteRegistroEstado()) {
        echo "El estado se eliminó correctamente";
    } else {
        echo "El estado no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    
    $obj->setEstado($parametros['estado']);
    $obj->setOrden($parametros['orden']);
    if(isset($parametros['clientes'])){
        $obj->setMostrarClientes(1);
    }else{
        $obj->setMostrarClientes(0);
    }
    
    if(isset($parametros['contactos'])){
        $obj->setMostrarContactos(1);
    }else{
        $obj->setMostrarContactos(0);
    }
    if ($parametros['area'] == '0'){
        $obj->setArea('NULL');
    }else{
        $obj->setArea($parametros['area']);
    }
    
    if(isset($parametros['estadoTicket']) && $parametros['estadoTicket'] != 0){
        $obj->setIdEstadoTicket((int)$parametros['estadoTicket']);
    }else{
        $obj->setIdEstadoTicket('NULL');
    }
    
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    if(isset($parametros['flagValidacion']) && $parametros['flagValidacion'] == "on"){
        $obj->setFlagValidacion(1);
    }else{
        $obj->setFlagValidacion(0);
    }
    if(isset($parametros['flagCobrar']) && $parametros['flagCobrar'] == "on"){
        $obj->setFlagCobrar(1);
    }else{
        $obj->setFlagCobrar(0);
    }
    
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('PHP admin flujo falla');

    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistroEstado()) {            
            if(isset($parametros['flujos_estado']) && !empty($parametros['flujos_estado']) && is_array($parametros['flujos_estado'])){
                $flujos = $parametros['flujos_estado'];
                foreach ($flujos as $value) {
                    $obj->setIdFlujo($value);
                    if(!$obj->newRegistro()){
                        echo "<br/>Error: no se pudo agregar el flujo $value";
                    }
                }
            }
            if(isset($_POST['numerador'])){
                $i = 0;
                $aux = 0;
                $cadena = "";
                while($i <= $_POST['numerador']){
                    while(!isset($parametros['tiempoEnvio_'.$aux])){
                        $aux++;
                    }
                    $obj->setTiempoEnvio($parametros['tiempoEnvio_'.$aux]);
                    $obj->setColor($parametros['color_'.$aux]);
                    $obj->setPrioridad($parametros['prioridad_'.$aux]);
                    $obj->setMensaje($parametros['mensaje_'.$aux]);
                    if($obj->newRegistroEscalamiento()){
                        $correos = $parametros['correos_'.$aux];
                        foreach($correos as $value){
                            $obj->setCorreo($value);
                            if(!$obj->newRegistroCorreo()){
                                echo "Error al agregar el correo al escalamiento";
                            }
                        }
                    }else{
                        if(trim($parametros['tiempoEnvio_'.$aux])!= ""){
                            echo "Fallo el escalamiento<br/>";
                        }
                    }
                    $aux++;
                    $i++;
                }
            }
            echo "El estado <b>" . $obj->getEstado() . "</b> se registró correctamente.";
        } else {
            echo "Error: El estado <b>" . $obj->getEstado() . "</b> no se pudo registrar.";
        }
    } else {/* Modificar */
        $obj->setIdEstado($parametros['id']);       
        //Primero borramos los escalamientos asociados a este estado.
        $obj->deleteRegistroEscalamientoByEstado();
        if ($obj->editRegistro()) {
            $obj->deleteFlujoByEstado();
            if(isset($parametros['flujos_estado']) && !empty($parametros['flujos_estado']) && is_array($parametros['flujos_estado'])){
                $flujos = $parametros['flujos_estado'];
                foreach ($flujos as $value) {
                    $obj->setIdFlujo($value);
                    if(!$obj->newRegistro()){
                        echo "<br/>Error: no se pudo agregar la pantalla $value";
                    }
                }
            }
            if(isset($_POST['numerador'])){
                $i = 0;
                $aux = 0;
                $cadena = "";
                while($i <= $_POST['numerador']){
                    while(!isset($parametros['tiempoEnvio_'.$aux])){
                        $aux++;
                    }
                    $obj->setTiempoEnvio($parametros['tiempoEnvio_'.$aux]);
                    $obj->setColor($parametros['color_'.$aux]);
                    $obj->setPrioridad($parametros['prioridad_'.$aux]);
                    $obj->setMensaje($parametros['mensaje_'.$aux]);
                    if($obj->newRegistroEscalamiento()){
                        $correos = $parametros['correos_'.$aux];
                        foreach($correos as $value){
                            $obj->setCorreo($value);
                            if(!$obj->newRegistroCorreo()){
                                echo "Error al agregar el correo al escalamiento";
                            }
                        }
                    }
                    $aux++;
                    $i++;
                }
            }
            echo "El estado <b>" . $obj->getEstado() . "</b> se modificó correctamente.";
        } else {
            echo "Error: El estado <b>" . $obj->getEstado() . "</b> no se pudo modificar.";
        }
    }
}
?>