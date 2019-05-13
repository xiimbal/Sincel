<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");

//*******************************************************       *JT     16/10/18
//                                                                 Codigo Modificado
if (isset($_POST['dato']) && $_POST['dato'] == 1) {
 $consulta = ("SELECT BanderaTimbrado FROM c_Bandera");
   $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $var = $rs['BanderaTimbrado'];
        }   
      
        if($var==1){
            echo 1;
            $consulta = ("UPDATE c_Bandera SET BanderaTimbrado=0");
            if(!$catalogo->obtenerLista($consulta))
                echo"<br>No se actualizo correctamente la bandera a cero";
        }else
            echo 0;
}

//                                                                  Nuevo CMLJNKodigo
if (isset($_POST['dato2']) && $_POST['dato2'] == 2) {
    
    $consulta = ("UPDATE c_Bandera SET BanderaTimbrado=1");
    $catalogo2 = new Catalogo();
    if(!$catalogo2->obtenerLista($consulta))
        echo "<br>Importante: No se pudo actualizar la bandera a uno";
//****************************************************************    
}
?>