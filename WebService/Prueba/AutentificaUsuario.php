<?php
header('Content-Type: text/html; charset=utf-8');
require_once "lib/nusoap.php";
//$client = new nusoap_client("http://genesis2.techra.com.mx/genesis2/WebService/AutenticaUsuario.php");
$client = new nusoap_client("http://ara.techra.com.mx/WebService/AutenticaUsuario.php");
 
$error = $client->getError();
if ($error) {
    echo "<h2>Constructor error</h2><pre>" . $error . "</pre>";
}
 
$result = $client->call("autenticaUsuario", array("usuario" => "magg_demo", "password" => "a725a6e3d52ac16a1856dca8317e8c7a", "IdSessionAnterior" => ""));
 
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
        echo "<h2>Consultando AutenticaUsuario</h2><pre>";
        echo $result;
        echo "</pre>";
    }
}

?>