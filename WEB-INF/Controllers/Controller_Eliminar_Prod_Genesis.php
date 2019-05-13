<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../Classes/Productos_Genesis.class.php");
if(isset($_GET['id'])){
    $Productos_Genesis = new Productos_Genesis();
    $Productos_Genesis->setId($_GET['id']);
    if($Productos_Genesis->deleteRegistro()){
        echo "Se eliminó correctamente";
    }else{
        echo "Ocurrió un error";
    }
}
?>
