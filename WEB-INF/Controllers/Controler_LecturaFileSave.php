<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
set_time_limit (0);
include_once("../Classes/Lectura.class.php");
include_once("../Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("../Classes/Inventario.class.php");

$lectura = new Lectura();
$equipo_carac = new EquipoCaracteristicasFormatoServicio();
$inventario = new Inventario();

$usuario = $_SESSION['user'];
$series_error = array();
$clientes = array();

if(isset($_POST['banderaTR']) && isset($_POST['banderaTR']) != "" ){
    $banderaRT= $_POST['banderaTR'];
}

$lista = $_POST['lista'];
$cliente = $_POST['cliente'];
$periodo = $_POST['periodo'];

if(empty($lista)){
    echo "Error: no se recibio ninguna lectura";
    return;
}

if($banderaRT==0){     
    
    $listaSeries = explode("|", $_POST['listaSeries']);
    $listaClientes = explode("|", $_POST['listaClientes']);
    $listaStatus = explode("|", $_POST['listaStatus']);
}

if(empty($listaSeries)){
    echo "Error: no se recibio ninguna lectura de lista series";
    return;
}

$errores = false;
$lectura->setLecturaCorte(1);
$lectura->setNivelTonAmarillo("null");
$lectura->setNivelTonCian("null");
$lectura->setNivelTonMagenta("null");
$lectura->setNivelTonNegro("null");
$lectura->setActivo(1);
$lectura->setUsuarioCreacion($usuario);
$lectura->setUsuarioUltimaModificacion($usuario);
$lectura->setPantalla("LecturaFileSave PHP");
$i=1;

//*********************************************** Archivo Tipo Normal
if($banderaRT==1){                  
foreach ($lista as $lec) {
    $lectura->setNoSerie($lec[0]);    
    $i++;
    if(!$inventario->getRegistroById($lectura->getNoSerie())){
        echo "<br/>Hubo un error al obtener el número de parte del equipo ".$lectura->getNoSerie();
        continue;
    }        
    $lectura->setFecha($periodo);
    if($equipo_carac->isFormatoAmplio($inventario->getNoParteEquipo())){
        $lectura->setContadorBNML($lec[1]);
        if($equipo_carac->isColor($inventario->getNoParteEquipo()) && isset($lec[2])){
            $lectura->setContadorColorML($lec[2]);
        }else{
            $lectura->setContadorColorML("NULL");
        }
        $lectura->setContadorBNPaginas("NULL");
        $lectura->setContadorColorPaginas("NULL");
    }else{
        $lectura->setContadorBNPaginas($lec[1]);
        if($equipo_carac->isColor($inventario->getNoParteEquipo()) && isset($lec[2])){
            $lectura->setContadorColorPaginas($lec[2]);
        }else{
            $lectura->setContadorColorPaginas("NULL");
        }
        $lectura->setContadorBNML("NULL");
        $lectura->setContadorColorML("NULL");
    }
    
    
    if(!$lectura->newRegistro()){
        array_push($series_error, $lectura->getNoSerie());   
        
    }
}
}

//******************************************** Archivo Tipo PrintFleet
if($banderaRT==0){                  
    foreach ($lista as $lec) {
        $lectura->setNoSerie($lec[1]);    
        $i++;
        if(!$inventario->getRegistroById($lectura->getNoSerie())){
            echo "<br/>Hubo un error al obtener el número de parte del equipo ".$lectura->getNoSerie();
            continue;
        }        
        $lectura->setFecha($periodo);
        if($equipo_carac->isFormatoAmplio($inventario->getNoParteEquipo())){
            $lectura->setContadorBNML($lec[2]);
            if($equipo_carac->isColor($inventario->getNoParteEquipo()) && isset($lec[2])){
                $lectura->setContadorColorML($lec[3]);
            }else{
                $lectura->setContadorColorML("NULL");
            }
            $lectura->setContadorBNPaginas("NULL");
            $lectura->setContadorColorPaginas("NULL");
        }else{
            $lectura->setContadorBNPaginas($lec[2]);
            if($equipo_carac->isColor($inventario->getNoParteEquipo()) && isset($lec[3])){
                $lectura->setContadorColorPaginas($lec[3]);
            }else{
                $lectura->setContadorColorPaginas("NULL");
            }
            $lectura->setContadorBNML("NULL");
            $lectura->setContadorColorML("NULL");
        }


        if(!$lectura->newRegistro()){
            array_push($series_error, $lectura->getNoSerie());  
        }
        
    }

    /*$listaSeries = explode("|", $_POST['listaSeries']);
    $listaClientes = explode("|", $_POST['listaClientes']);
    $listaUbicacion = explode("|", $_POST['listaUbicacion']);
    $listaStatus = explode("|", $_POST['listaStatus']);

    if(!$lectura->newRegistroCargasMasivas()){
            echo "<b>Atenci�n:<b> No se guardo correctamente el registros";
    }

    /*for($x=0; $x<count($listaSeries) && $x<count($listaClientes) && $x<count($listaUbicacion) && $x<count($listaStatus);$x++){

        $listaUbicacion[$x]=$lectura->getRegistroBySerie($listaSeries[$x]);
        $lectura->newRegistrosCargasMasivas($listaSeries[$x], $listaClientes[$x], $listaUbicacion[$x], $listaStatus[$x]);
    }*/
}

if (empty($series_error)) {
    echo "Se han registrado con éxito las lecturas";
} else {
    echo "Ocurrió un error al insertar las lecturas de los siguientes equipos:";
    foreach ($series_error as $value) {
        echo "<br/>$value";
    }
}
     