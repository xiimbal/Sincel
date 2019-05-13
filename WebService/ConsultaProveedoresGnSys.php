<?php
header('Content-Type: text/html; charset=utf-8');
require_once "../lib/nusoap.php";
$client = new nusoap_client("http://pruebas1.techra.com.mx/WebService/proveedores.php");
 
$error = $client->getError();
if ($error) {
    echo "<h2>Constructor error</h2><pre>" . $error . "</pre>";
}
 
$parametros = array("IdSession" => "pruebas25124",1,1);
$result = $client->call("obtenerProveedores", $parametros);
 
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
        echo "<h2>Consultando proveedores</h2><pre>";
        echo $result;
        echo "</pre>";
    }
}

?>

