<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/Catalogo.class.php");
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $catalogo = new Catalogo();
    if ($id == 0) {
        $query3 = $catalogo->obtenerLista("SELECT e.Descripcion
FROM
	c_equipo AS e
WHERE e.NoParte='".$_POST['modelo']."'");
        if($rs=  mysql_fetch_array($query3)){
            if($rs['Descripcion']==""){
                echo "Sin descripción";
            }else{
                echo $rs['Descripcion'];
            }
        }else{
            echo "Sin descripción";
        }
    } else {
        $query3 = $catalogo->obtenerLista("SELECT e.Descripcion
FROM
	c_componente AS e
WHERE e.NoParte='".$_POST['modelo']."'");
        if($rs=  mysql_fetch_array($query3)){
            if($rs['Descripcion']==""){
                echo "Sin descripción";
            }else{
                echo $rs['Descripcion'];
            }
        }else{
            echo "Sin descripción";
        }
    }
}
?>