<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../Classes/Productos_Genesis.class.php");
if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
    $Productos_Genesis = new Productos_Genesis();
    $Productos_Genesis->setNombre($parametros['nombre']);
    $Productos_Genesis->setId_precio($parametros['precio']);
    $Productos_Genesis->setUsuarioCreacion($_SESSION['user']);
    $Productos_Genesis->setUsuarioModificacion($_SESSION['user']);
    if(isset($parametros['id'])){
        $Productos_Genesis->setId($parametros['id']);
        if($Productos_Genesis->actualizar()){
            echo "Actualizado";
        }else{
            echo "Error: Ocurrió un error";
        }
    }else{
        if($Productos_Genesis->insertar()){
            echo "Se registró exitosamente";
        }else{
            echo "Error: Ocurrió un error";
        }
    }
}else{
    echo "Error: Ocurrió un error";
}
?>
