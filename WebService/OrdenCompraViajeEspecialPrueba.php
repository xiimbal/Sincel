<?php
header('Content-Type: text/html; charset=utf-8');
require_once "../lib/nusoap.php";
$client = new nusoap_client("https://ara.techra.com.mx/WebService/OrdenCompraViajeEspecial.php");//http://estadia.factury.mx
 
$error = $client->getError();
if ($error) {
    echo "<h2>Constructor error</h2><pre>" . $error . "</pre>";
}
 
$parametros = array(9,32,1,"Lago Zurich 245","Televisa Sanata Fe","Lago Zurich","245",
            "SIN","Ampl. Granada","SinCiudad","Miguel Hidalgo","55550","","9",19.438971,-99.222023,
            "Del proveedor: Pakal: 86 Cartones GRANDE // VG // CHILE HABANERO VERDE GRANDE 1650kg $300.5,21 Arpillas CHICO // VCH // CHILE HABANERO VERDE CHICO 500 Kg $300 Del proveedor: Javier: 50 Arpillas GRANDE // 023J10UN // Rojo Despatado 1400 Kg $350, 53 Arpillas CHICO // 1T02MNAUS0 // JASPEADO 500Kg $450", "Donalt Trump", "7712134567", "", 
            "", "", "", "2018-04-17 09:00:00", "2018-04-17 15:00:00",1,5,1, 
            "Av. Vasco de Quiroga","2000","SIN","Santa Fe","SinCiudad","Álvaro Obregón","01210",
            "","9",19.376425,-99.253914,"","BOPBr1njD4R5H[B","76,75"); 
$result = $client->call("insertaOrdenCompraViajeEspecial", $parametros);
 
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
        echo "<h2>Orden de compra creando Camion</h2><pre>";
        echo $result;
        echo "</pre>";
    }
}

?>

