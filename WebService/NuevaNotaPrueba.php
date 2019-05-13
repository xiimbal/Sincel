<?php
header('Content-Type: text/html; charset=utf-8');
require_once "../lib/nusoap.php";
$client = new nusoap_client("http://pruebas1.techra.com.mx/WebService/NuevaNota.php");
 
$error = $client->getError();
if ($error) {
    echo "<h2>Constructor error</h2><pre>" . $error . "</pre>";
}
 
$parametros = array("Título","Mensaje","","","2018-02-10 10:00:00",3,17,130400,19.376425,-99.253914,"pruebas25124");
$result = $client->call("insertaNota", $parametros);
 
if ($client->fault) {
    echo "<h2>Fault</h2><pre>";
    print_r($result);
    echo "</pre>";
}else{
    $error = $client->getError();    
    if ($error) {
        echo "<h2>Error</h2><pre>" . $error . "</pre>";
    }
    else {
        echo "<h2>Orden de compra con viaje especial</h2><pre>";
        echo $result;
        echo "</pre>";
    }
}

?>

