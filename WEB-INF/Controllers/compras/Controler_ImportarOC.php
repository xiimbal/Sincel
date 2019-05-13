<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

$usuario = $_SESSION['user'];
$pantalla = "Controler_ImportarOC";
$archivo = $_POST['nombre_archivo'];
$idCompra = $_POST['idCompra'];

if (substr($archivo, -4) != '.csv') {
    echo "Error: el archivo no tiene formato csv";
    return false;
}

include_once("../../Classes/LeerCSV_OC.class.php");
$csv = new LeerCSV_OC();

if(isset($_POST['tipo']) && $_POST['tipo']=="1"){
    if($csv->cargarCSVOC("../../../compras/php/files/".$archivo, $idCompra, $usuario, $pantalla)){
        echo "<br/>El archivo <b>$archivo</b> se importó correctamente";
    }
}else if(isset($_POST['tipo']) && $_POST['tipo']=="2"){
    if($csv->cargarCSVEntrada("../../../compras/php/files/".$archivo, $idCompra, $_POST['folio_factura'], $usuario, $pantalla, 
            $_POST['almacen'], $_POST['estatus'], $_POST['estadoOC'], $_SESSION['idEmpresa'])){
        echo "<br/>El archivo <b>$archivo</b> se importó correctamente";
    }
}
?>
