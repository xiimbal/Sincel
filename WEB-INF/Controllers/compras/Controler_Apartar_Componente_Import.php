<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../../Classes/Apartar_Imports.class.php");
$obj = new Apartar_Imports();
if (isset($_POST['eliminarApartados'])) {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $tamanoComponente = $_POST['tbComponente'];
    $x = 0;
    $y = 0;
    while ($y < (int) $tamanoComponente - 1) {
        if (isset($parametros['txtidApartado' . $x])) {
            if ($parametros['txtidApartado' . $x] != "") {
                $idNota = $parametros['txtidApartado' . $x];
                if ($obj->deleteRegistro($idNota)) {
                    
                } else {
                    
                }
            }
            $y++;
        }
        $x++;
    }
} else {
    if (isset($_POST['idApartados']) && $_POST['idApartados'] != "") {//eliminar
        $idNota = $_POST['idApartados'];
        if ($obj->deleteRegistro($idNota)) {
            
        } else {
            
        }
    } else if (isset($_POST['ApartarMoroso'])) {
        $obj->setUsuarioCreacion($_SESSION['user']);
        $obj->setUsuarioModificacion($_SESSION['user']);
        $obj->setPantalla("Importar componentes alta_orden_compra.php");
        if ($obj->newRegistro()) {
            echo $obj->getIdApartado();
        } else {
            
        }
    }
}
