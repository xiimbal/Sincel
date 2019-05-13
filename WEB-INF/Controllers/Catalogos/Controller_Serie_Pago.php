<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Serie.class.php");
if (isset($_POST['clave']) && $_POST['clave'] != "") {
    $serie = new Serie();
    if ($serie->deleteRegistroPago($_POST['clave'])) {
        echo "Se ha eliminado la serie exitosamente";
    } else {
        echo "Error: la serie tiene valores dependientes";
    }
}else {
    $parametros = "";
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $serie = new Serie();
    $serie->setPrefijo($parametros['prefijo']);
    $serie->setFolioInicio($parametros['folioInicio']);
    if($serie->existeFolio()){
        echo "Error: Ya existe una factura con este prefijo y folio, intente con otro folio o prefijo";
    }else{
        $serie->setFolioPreFactura($parametros['folioInicio']);
        if (isset($parametros['activo']) && $parametros['activo'] == "1") {
            $serie->setActivo($parametros['activo']);
        } else {
            $serie->setActivo(0);
        }
        $serie->setPantalla("PHP Controller_Serie_Pago");
        $serie->setUsuarioCreacion($_SESSION['user']);
        $serie->setUsuarioUltimaModificacion($_SESSION['user']);
        if (isset($parametros['id']) && $parametros['id'] != "") {
            $serie->setIdSerie($parametros['id']);
            if ($serie->editRegistroPago()) {
                echo "Se ha editado la serie exitosamente";
            } else {
                echo "Error: La serie no se logrÛ editar";
            }
        } else {
            if ($serie->newRegistroPago()) {
                echo "Se ha registrado la nueva serie";
            } else {
                echo "Error: La serie no se registr√≥";
            }
        }
    }
}
