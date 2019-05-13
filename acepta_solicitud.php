<?php
header('Content-Type: text/html; charset=utf-8');
ini_set("memory_limit","512M");
if(!isset($_GET['clv']) || !isset($_GET['soli']) || !isset($_GET['tipo'])){
    header("Location: index.php");
}

if(!isset($_GET['uguid'])){
    /*echo "La liga no está completa, favor de comunicarlo a soporte.";
    return;*/
    $empresa = 1;//Temporalmente, se toma por default la empresa 1, que es genesis.
}else{
    $empresa = $_GET['uguid'];
}

include_once("WEB-INF/Classes/Solicitud.class.php");
$obj = new Solicitud();
$obj->setEmpresa($empresa);

if($obj->getRegistroById($_GET['soli'])){
    if($obj->getIdByClaveLink($_GET['clv'], $_GET['soli'])){
        $obj->setIdUsuario(2);
        if($obj->getContestada() != "0"){
            echo "La solicitud ".$_GET['soli']." ya ha sido respondida anteriormente";
            return;
        }
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
}else{
    echo "La solicitud <b>".$_GET['soli']."</b> ya no se encuentra registrada en el sistema";
}
?>