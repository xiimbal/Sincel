<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Configuracion.class.php");
include_once("../Classes/Catalogo.class.php");
include_once("../Classes/Log.class.php");
include_once("../Classes/AlmacenConmponente.class.php");


if(isset($_GET['desasociar'])){
    $configuracion = new Configuracion();
    if($configuracion->desasociarBitacora($_GET['desasociar'])){
        echo "El equipo fue desasociado de la solicitud correctamente";
    }else{
        echo "Error: El equipo no pudo ser desasociado de la solicitud, intente de nuevo o reportelo por favor";
    }
}else if(isset ($_GET['solicitud']) && isset ($_GET['partida']) && isset ($_GET['NoParte']) && isset ($_GET['cantidad']) && isset ($_GET['almacen'])){
   $NoParte = str_replace("||__||", " ", $_GET['NoParte']);
    
    $configuracion = new Configuracion();
    $catalogo = new Catalogo();
    if($configuracion->desasociarComponente($_GET['solicitud'], $_GET['partida'])){
        $cantidad = $_GET['cantidad'];
        $pantalla = "Controler_Bitacora";
        /*Verificamos que no entren existencias negativas*/
        $almacenComponente = new AlmacenComponente();
        if($almacenComponente->getRegistroById($NoParte, $_GET['almacen'])){
            if($cantidad > $almacenComponente->getApartados()){
                $log = new Log();
                $log->setConsulta("Intento de registrar existencias negativas ($cantidad)");
                $log->setSeccion($this->pantalla);
                $log->setIdUsuario($_SESSION['idUsuario']);
                $log->setTipo("Incidencia sistema");
                $log->newRegistro();
                $cantidad = $almacenComponente->getApartados();
            }
        }
        /*Desapartamos lo que había apartado en el almacen anterior*/
        $consulta = "UPDATE `k_almacencomponente` SET cantidad_existencia = cantidad_existencia + ".$_GET['cantidad'].", 
        cantidad_apartados = cantidad_apartados - ".$cantidad.", Pantalla = '$pantalla'  
        WHERE id_almacen = ".$_GET['almacen']." AND NoParte = '".$NoParte."';";
        
        $catalogo->obtenerLista($consulta);
        echo "El componente fue desasociado del almacén correctamente";
    }else{
        echo "Error: El componente no pudo ser desasociado de la solicitud, intente de nuevo o reportelo por favor";
    }
}
?>
