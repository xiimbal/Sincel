<?php
header('Content-Type: text/html; charset=utf-8');
require_once "lib/nusoap.php";
$client = new nusoap_client("http://50.31.138.92/genesis2/WebService/VersionCategoria.php");
 
$error = $client->getError();
if ($error) {
    echo "<h2>Constructor error</h2><pre>" . $error . "</pre>";
}
 
$result = $client->call("getVersion", array("ParamVersionMovil" => $_POST["vParamVersionMovil"], 
"usuario" => $_POST["vusuario"] , 
"password" => $_POST["vpassword"]));
 
if ($client->fault) {
    echo "<h2>Fault</h2><pre>";
    print_r($result);
    echo "</pre>";
}
else {
    $error = $client->getError();    
    if ($error) {
        echo "<h2>Error</h2><pre>" . $error . "</pre>";
    }
    else {
        echo "<h2>Consultando VersionCategoria</h2><pre>";
        
		$nuevo = str_replace("%%,", "<br>", $result);
		//echo $result;
		echo $nuevo;
		
        echo "</pre>";
    }
}
echo '<a HREF="WEBVersionCategoriaClienteForma.php">Regresar</a>';

?>