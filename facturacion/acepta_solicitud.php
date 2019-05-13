<?php
header('Content-Type: text/html; charset=utf-8');

if(!isset($_GET['clv']) || !isset($_GET['soli']) || !isset($_GET['tipo'])){
    header("Location: index.php");
}

if(!isset($_GET['uguid'])){
    echo "La liga no está completa, favor de comunicarlo a soporte.";
    return;
}

include_once("WEB-INF/Classes/Solicitud.class.php");
$obj = new Solicitud();
$obj->setEmpresa($_GET['uguid']);

if($obj->getIdByClaveLink($_GET['clv'], $_GET['soli'])){
    if(($_GET['tipo']=="1" || $_GET['tipo']=="3") && $obj->aceptarRechazarSolicitud($_GET['tipo'])){
        if($_GET['tipo']=="1"){
            echo "La solicitud ".$_GET['soli']." fue autorizada";
        }else{
            echo "La solicitud ".$_GET['soli']." fue rechazada";
        }
    }else{
        echo " Hubo un error al actualizar, vuelva a intentarlo por favor";
    }
}else{
    header("Location: index.php?msj=ClaveNotFound");
}
?>