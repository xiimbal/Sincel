<?php
header('Content-Type: text/html; charset=utf-8');
require_once "lib/nusoap.php";
$client = new nusoap_client("http://50.31.138.92/genesis2/WebService/NuevoCliente.php");
 
 
$error = $client->getError();
if ($error) {
    echo "<h2>Constructor error</h2><pre>" . $error . "</pre>";
}

$parametros = array("NombreRazonSocial" => $_POST["vNombreRazonSocial"], 
		"idTipoCliente" => $_POST["vidTipoCliente"], 
		"latitud" => $_POST["vlatitud"], 
		"longitud" => $_POST["vlongitud"], 
		"calle" => $_POST["vcalle"],
        "noInterior" => $_POST["vnoInterior"], 
		"noExterior" => $_POST["vnoExterior"], 
		"colonia" => $_POST["vcolonia"], 
		"ciudad" => $_POST["vciudad"], 
		"estado" => $_POST["vestado"],
        "delegacion" => $_POST["vdelegacion"], 
		"pais" => $_POST["vpais"], 
		"codigo_postal" => $_POST["vcodigo_postal"], 
		"usuario" => $_POST["vusuario"] , 
		"password" => $_POST["vpassword"]);
$result = $client->call("insertarNuevoRegistro", $parametros);
 
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
        echo "<h2>Consultando NuevoCliente</h2><pre>";
        echo $result;
        echo "</pre>";
    }
}
echo '<a HREF="WEBInsertaClienteForma.php">Regresar</a>';
?>