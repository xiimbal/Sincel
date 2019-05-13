<?php
if(!isset($_GET['clv']) || !isset($_GET['RFC']) || !isset($_GET['esp'])){
    header("Location: index.php");
}

if(!isset($_GET['uguid'])){
    /*echo "La liga no está completa, favor de comunicarlo a soporte.";
    return;*/
    $empresa = 1;//Temporalmente, se toma por default la empresa 1, que es genesis.
}else{
    $empresa = $_GET['uguid'];
}

include_once("WEB-INF/Classes/Mail.class.php");
include_once("WEB-INF/Classes/Cliente.class.php");
$mail = new Mail();
$mail->setEmpresa($_GET['uguid']);
if($mail->getClaveGeneralByIDClave($_GET['esp'], $_GET['clv'])){
    $cliente = new Cliente();
    $cliente->setRFC($_GET['RFC']);
    $cliente->setEmpresa($_GET['uguid']);
    if($cliente->marcarComoSuspendidoRFC(true)){
        $mail->marcarContestado($_GET['esp']);
        echo "<br/>El cliente con RFC ".$_GET['RFC']." fue marcado como suspendido correctamente";
    }else{
        echo "<br/>Error: No se pudo cambiar el estado del cliente, intenta de nuev por favor";
    }
}else{
    header("Location: index.php?msj=ClaveNotFound");
}

?>
