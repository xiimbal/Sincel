<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/Catalogo.class.php");
$obj = new Catalogo();
$claveCliente = $_POST['cliente'];
$arregloLocalidad = array();

if (isset($_POST['buscar']) && $_POST['buscar'] == "LocalidadCliente") {
    $consulta = "SELECT cc.ClaveCentroCosto AS claveCC,cc.Nombre AS localidad FROM c_centrocosto cc WHERE cc.ClaveCliente='$claveCliente'";
} else {
    $consulta = "SELECT cc.ClaveCentroCosto AS claveCC,cc.Nombre AS localidad FROM c_centrocosto cc WHERE cc.ClaveCliente='$claveCliente' AND cc.ClaveCentroCosto NOT IN(SELECT ml.ClaveCentroCosto FROM k_minialmacenlocalidad ml)";
}
$queryLoclaidades = $obj->obtenerLista($consulta);
$conador = 0;
while ($rs = mysql_fetch_array($queryLoclaidades)) {
    $arregloLocalidad[$conador] = $rs['claveCC'] . " /* " . $rs['localidad'];
    $conador++;
}
echo json_encode($arregloLocalidad);
?>
