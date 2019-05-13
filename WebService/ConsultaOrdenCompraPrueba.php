<?php
header('Content-Type: text/html; charset=utf-8');
require_once "../lib/nusoap.php";
$client = new nusoap_client("http://pruebas1.techra.com.mx/WebService/ConsultaOrdenCompra.php");
 
$error = $client->getError();
if ($error) {
    echo "<h2>Constructor error</h2><pre>" . $error . "</pre>";
}
 
$parametros = array(130408,"pruebas25124");
$result = $client->call("getProductos", $parametros);
 
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
        echo "<h2>Consultando ordenes de compra del ticket 130408</h2><pre>";
        echo $result;
        echo "</pre>";
    }
}

?>

