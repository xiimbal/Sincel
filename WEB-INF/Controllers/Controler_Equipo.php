<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Equipo.class.php");
include_once("../Classes/EquipoCaracteristicasFormatoServicio.class.php");

$obj = new Equipo();
$servicioFormato = new EquipoCaracteristicasFormatoServicio();

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setNoParte($_GET['id']);
    $servicioFormato->setNoParte($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El equipo se eliminó correctamente";
        if(!isset($_GET['computo']) || (int)$_GET['computo'] != 1){
             if(!$servicioFormato->deleteRegistro()){
                 echo "<br/>Hubo un error al eliminar el servicio";
             }
        }
    } else {
        echo "El equipo no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if(isset($_GET['computo'])){
        $obj->setNoParte($_POST['partes']);
        $obj->setModelo($_POST['modelo']);
        if (isset($_POST['activo']) && $_POST['activo'] == "on") {
            $obj->setActivo(1);
        } else {
            $obj->setActivo(0);
        }
        if (isset($_POST['incluyeToner']) && $_POST['incluyeToner'] == "on") {
            $obj->setIncluyeToner(1);
        } else {
            $obj->setIncluyeToner(0);
        }
        
        //$obj->setImagen($parametros['estado']); 
        $descripcionAux = $_POST['descripcion'];
        $descripcion = str_replace("'", "’", $descripcionAux);
        //print_r($_POST);
        if(isset($_POST['IdTipoEquipo'])){
            $obj->setIdTipoEquipo($_POST['IdTipoEquipo']);
        }
        $obj->setDescripcion($descripcion);
        $obj->setPrefijo($_POST['prefijo']);

        $obj->setPrecio($_POST['precio']);
        $obj->setMeses($_POST['periodoMeses']);
        $obj->setImpresiones($_POST['periodoImpresion']);
        $obj->setUsuarioCreacion($_SESSION['user']);
        $obj->setUsuarioModificacion($_SESSION['user']);
        $obj->setPantalla('PHP admin equipo');

        $obj->setProcesador($_POST['procesador']);
        $obj->setHD($_POST['hd']);
        $obj->setIdiomaSO($_POST['idiomaSO']);
        $obj->setRAM($_POST['ram']);
        $obj->setSistemaOperativo($_POST['sistemaOperativo']);
        $obj->setResolucion($_POST['resolucion']);
        $obj->setTamanoPulgadas($_POST['pulgadas']);

        if(isset($_POST['HDMI']) && $_POST['HDMI'] == "on") {
            $obj->setHDMI(1);
        } else {
            $obj->setHDMI(0);
        }

        if(isset($_POST['DVD']) && $_POST['DVD'] == "on") {
            $obj->setDVD(1);
        } else {
            $obj->setDVD(0);
        }

        if(isset($_POST['USB']) && $_POST['USB'] == "on") {
            $obj->setUSB(1);
        } else {
            $obj->setUSB(0);
        }

        if(isset($_POST['WIFI']) && $_POST['WIFI'] == "on") {
            $obj->setWIFI(1);
        } else {
            $obj->setWIFI(0);
        }

        if (isset($_POST['id']) && $_POST['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
            if ($obj->ComprobarExistencia()) {
                if ($obj->newRegistro())
                {    
                    echo $obj->getNoParte();   
                }
                else
                {    echo "Error: El equipo no se pudo registrar, intenta más tarde por favor"; }
            } else
            {   
                echo "Error: El equipo <b>" . $obj->getNoParte() . "</b> ya se encuentra registrado";
            }
        } else {/* Modificar */
            if ($obj->editRegistro()) {
                echo $obj->getNoParte();         
            } else {
                echo "Error: El equipo no se pudo modificar, intenta más tarde por favor";
            }
        }
    }else{
        if (isset($_POST['form'])) {
            $parametros = "";
            parse_str($_POST['form'], $parametros);
        }

        $obj->setNoParte($parametros['partes']);
        $obj->setModelo($parametros['modelo']);
        if (isset($parametros['activo']) && $parametros['activo'] == "on") {
            $obj->setActivo(1);
        } else {
            $obj->setActivo(0);
        }
        if (isset($parametros['incluyeToner']) && $parametros['incluyeToner'] == "on") {
            $obj->setIncluyeToner(1);
        } else {
            $obj->setIncluyeToner(0);
        }
        //$obj->setImagen($parametros['estado']); 
        $servicioFormato->setNoParte($parametros['partes']);
        $servicioFormato->setIdCaracteristica($servicioFormato->getCaract($parametros['caracteristica']));

        $servicioFormato->setUsuarioCreacion($_SESSION['user']);
        $servicioFormato->setUsuarioModificacion($_SESSION['user']);
        $servicioFormato->setPantalla('PHP admin equipo');
        // if (isset($parametros['color']) && $parametros['color'] == "on") {
        $servicioFormato->setServicioColor($parametros['tiposerv']);
        // }
    //echo "tipo ".$parametros['caracteristica'];
        if ($parametros['caracteristica'] != "Formato amplio") {
            if (isset($parametros['fax']) && $parametros['fax'] == "on")
                $servicioFormato->setServicioFax($servicioFormato->getServicio("Fax"));
            $servicioFormato->setFormatoEquipo($servicioFormato->getTipoFormato($parametros['formato1']));
        }
        $descripcionAux = $parametros['descripcion'];
        $descripcion = str_replace("'", "’", $descripcionAux);

        $obj->setDescripcion($descripcion);
        $obj->setIdTipoEquipo($parametros['IdTipoEquipo']);
        $obj->setPrefijo($parametros['prefijo']);


        $obj->setPrecio($parametros['precio']);
        $obj->setMeses($parametros['periodoMeses']);
        $obj->setImpresiones($parametros['periodoImpresion']);
        $obj->setUsuarioCreacion($_SESSION['user']);
        $obj->setUsuarioModificacion($_SESSION['user']);
        $obj->setPantalla('PHP admin equipo');

        $obj->setCapacidadDuplex($parametros['duplex']);
        $obj->setPld($parametros['lenguajeImpr']);
        $obj->setVeocidad($parametros['velocidad']);
        $obj->setCiclo($parametros['ciclo']);
        $obj->setResolucion($parametros['resolucion']);
        $obj->setCapacidadMemoria($parametros['capacidad']);
        $obj->setPesoPapel($parametros['peso']);
        $obj->setLongitudSerie($parametros['longitud_serie']);

        $obj->setProcesador($parametros['procesador']);
        $obj->setHD($parametros['hd']);
        $obj->setIdiomaSO($parametros['idiomaSO']);
        $obj->setRAM($parametros['ram']);
        $obj->setSistemaOperativo($parametros['sistemaOperativo']);
        $obj->setResolucion($parametros['resolucion']);
        $obj->setTamanoPulgadas($parametros['pulgadas']);

        if(isset($parametros['HDMI']) && $parametros['HDMI'] == "on") {
            $obj->setHDMI(1);
        } else {
            $obj->setHDMI(0);
        }

        if(isset($parametros['DVD']) && $parametros['DVD'] == "on") {
            $obj->setDVD(1);
        } else {
            $obj->setDVD(0);
        }

        if(isset($parametros['USB']) && $parametros['USB'] == "on") {
            $obj->setUSB(1);
        } else {
            $obj->setUSB(0);
        }

        if(isset($parametros['WIFI']) && $parametros['WIFI'] == "on") {
            $obj->setWIFI(1);
        } else {
            $obj->setWIFI(0);
        }

        if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
            if ($obj->ComprobarExistencia()) {
                if ($obj->newRegistro())
                {    
                    echo $obj->getNoParte();   
                    if(!isset($_GET['computo']) || (int)$_GET['computo'] != 1){
                        if(!$servicioFormato->newRegistro()){
                            echo "<br/>Hubo un error al registrar el servicio";
                        }
                   }
                }
                else
                {    echo "Error: El equipo no se pudo registrar, intenta más tarde por favor"; }
            } else
            {   
                echo "Error: El equipo <b>" . $obj->getNoParte() . "</b> ya se encuentra registrado";
            }
        } else {/* Modificar */
            if ($obj->editRegistro()) {
                echo $obj->getNoParte();
                if(!isset($_GET['computo']) || (int)$_GET['computo'] != 1){
                    if(!$servicioFormato->editRegistro()){
                        echo "<br/>Hubo un error al modificar el servicio";
                    }
               }           
            } else {
                echo "Error: El equipo no se pudo modificar, intenta más tarde por favor";
            }
        }
    }
}
?>