<?php

//header('Content-Type: application/json');

//Este archivo solo devolverá respuestas en JSON para usarlos en el JQuery UI Autocomplete
session_start();

$array = array();   //Contendra todas las respuestas

if (isset($_POST['tipo_controlador']) && $_POST['tipo_controlador'] == "UnidadMedida") {
    include_once("../../Classes/Catalogo.class.php");
    $palabra = $_POST['Palabra'];
    $catalogo = new Catalogo();
    $consulta = "SELECT CONCAT_WS(' ',um.ClaveUnidad,um.UnidadMedida) AS Unidad
        FROM c_unidadmedidaSAT um
        HAVING Unidad LIKE '%$palabra%';";
    $result = $catalogo->obtenerLista($consulta);
    while($rs = mysql_fetch_array($result)){
        array_push($array, $rs['Unidad']);
    }
}else if(isset($_POST['tipo_controlador']) && $_POST['tipo_controlador'] == "ProductosSat"){
    include_once("../../Classes/Catalogo.class.php");
    $palabra = $_POST['Palabra'];
    $catalogo = new Catalogo();
    $consulta = "SELECT CONCAT_WS(' ',cps.ClaveProdServ,cps.Descripcion) AS Producto
        FROM c_claveprodserv cps
        HAVING Producto LIKE '%$palabra%';";
    $result = $catalogo->obtenerLista($consulta);
    while($rs = mysql_fetch_array($result)){
        array_push($array, $rs['Producto']);
    }
}

echo json_encode($array);