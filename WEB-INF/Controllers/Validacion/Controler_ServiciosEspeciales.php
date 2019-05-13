<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['form'])) {
    header("Location: ../../index.php");
}

include_once("../../Classes/ServiciosVE.class.php");

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$obj = new ServiciosVE();

$IdServiciosNoBorrar = array();
$obj->setIdAnexoClienteCC($parametros['claveAnexo']);
$obj->setUsuarioCreacion($_SESSION['user']);
$obj->setUsuarioUltimaModificacion($_SESSION['user']);
$obj->setPantalla("PHP servicios especiales");

$numero_conceptos = $parametros['numero_conceptos'];
for($i=1; $i<=$numero_conceptos; $i++){    
    if(isset($parametros['nombre_'.$i]) && $parametros['nombre_'.$i] != ""){
        $obj->setNombreServicio($parametros['nombre_'.$i]);
        $obj->setPrecioUnitario((double)$parametros['precio_'.$i]); 
        $obj->setIdTarifa($parametros['tarifa_'.$i]);
        $obj->setIdEstado($parametros['estado_'.$i]);
        if(isset($parametros['variable_'.$i]) && $parametros['variable_'.$i] == "Activo"){
            $obj->setTipo(1);
        }else{
            $obj->setTipo(0);
        }
        if(isset($parametros['idServicioVe'.$i]) && $parametros['idServicioVe'.$i] != ""){  //Solo hay que actualizar el registro
            $obj->setIdServicioVE($parametros['idServicioVe'.$i]);
            $obj->editRegistro();
            array_push($IdServiciosNoBorrar, $parametros['idServicioVe'.$i]);
        }else{
            if($obj->newRegistro()){
                echo "Se registró un servicio";
                array_push($IdServiciosNoBorrar, $obj->getIdServicioVE());
            }else{
                echo "Error: no se pudo registrar ";
            }
        }
    }       
}

//Borraremos todos los servicios que no se encuentran en el array, significa que los eliminaron.
$servicio = implode(",",$IdServiciosNoBorrar);
if($servicio != ""){
    if(!$obj->borrarRegistrosPorServicios($servicio)){
        echo "Error: No se pudieron borrar los registros";
    }
}