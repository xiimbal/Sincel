<?php
header('Content-Type: text/html; charset=utf-8');
require_once "../lib/nusoap.php";
$client = new nusoap_client("https://ara.techra.com.mx/WebService/OrdenCompra.php");
 
$error = $client->getError();
if ($error) {
    echo "<h2>Constructor error</h2><pre>" . $error . "</pre>";
}
 
$productos = array();
array_push($productos, array("cantidad" => 86, "tipoComponente" => 3, "producto" => "GRANDE // VG // CHILE HABANERO VERDE GRANDE", "precioCompra" => 300.5));
array_push($productos, array("cantidad" => 21, "tipoComponente" => 1, "producto" => "CHICO // VCH // CHILE HABANERO VERDE CHICO", "precioCompra" => 0));
array_push($productos, array("cantidad" => 50, "tipoComponente" => 1, "producto" => "GRANDE // 023J10UN // Rojo Despatado", "precioCompra" => 0));
array_push($productos, array("cantidad" => 53, "tipoComponente" => 1, "producto" => "CHICO // 1T02MNAUS0 // JASPEADO", "precioCompra" => 0));

$parametros = array("IdSession" => "psSQGQ+3QV1Are2","NoPedido" => "21","Total" => 0, "Emisor" => "provedor0", "Productos" => $productos);
$result = $client->call("crearOrdenCompra", $parametros);
 
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
        echo "<h2>Orden de compra</h2><pre>";
        echo $result;
        echo "</pre>";
    }
}

?>
