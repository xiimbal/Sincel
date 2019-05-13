<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['NoSerie'])) {
    header("Location: ../../../index.php");
}

include_once("../../Classes/ValidarRefacciones.class.php");
$validarRefacciones = new ValidarRefaccion();
$noSerie = $_POST['NoSerie'];

$result = $validarRefacciones->getRefaccionesPendientesServicio($noSerie);

if(mysql_num_rows($result) > 0){
    
}else{
    return "0";
}    
?>
