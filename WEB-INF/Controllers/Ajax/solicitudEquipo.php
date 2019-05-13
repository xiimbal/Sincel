<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
include_once("../../Classes/Catalogo.class.php");
$catalogo = new Catalogo();

$sinAlmacen = true;
$equiposConComponente = true;

if(isset($_POST['equipos'])){
    $equipos = split(",", $_POST['equipos']);
    $localidad = split(",", $_POST['localidad']);
    for($i = 0; $i < count($equipos); $i++){
        $queryAlmacen = "SELECT mal.IdAlmacen FROM k_minialmacenlocalidad mal
                LEFT JOIN c_almacen AS a ON mal.IdAlmacen = a.id_almacen
                WHERE mal.ClaveCentroCosto = '".$localidad[$i]."' AND a.Activo = 1";
        $resultAlmacen = $catalogo->obtenerLista($queryAlmacen);
        if(mysql_num_rows($resultAlmacen) > 0)
        {
            $sinAlmacen = false;
            if($rsAlmacen = mysql_fetch_array($resultAlmacen)){ 
                $query = "SELECT ecc.NoParteComponente FROM k_equipocomponentecompatible ecc
                    INNER JOIN k_almacencomponente ac ON ac.NoParte = ecc.NoParteComponente 
                    WHERE ac.id_almacen IN (SELECT IdAlmacen FROM k_minialmacenlocalidad WHERE ClaveCentroCosto = '".$localidad[$i]."') 
                    AND ecc.NoParteEquipo = '".$equipos[$i]."'";
                $result = $catalogo->obtenerLista($query);
                if(mysql_num_rows($result) < 1){
                    $equiposConComponente = false;
                    echo "No hay tóners compatibles con el equipo ".$equipos[$i]." en el mini almacén de esta localidad<br/>
                            Por favor seleccione un tóner en la solicitud<br/>";
                }
            }
        }
    }
    if($sinAlmacen){
        echo "SinAlmacen";
    }else if($equiposConComponente){
        echo "Exito";
    }
    
}

