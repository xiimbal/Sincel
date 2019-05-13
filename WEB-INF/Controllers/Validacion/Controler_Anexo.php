<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['form'])) {
    header("Location: ../../index.php");
}

include_once("../../Classes/Anexo.class.php");
$obj = new Anexo();

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$obj->setClaveCC($parametros['clave_cc_contrato']);
//$obj->setClaveAnexoTecnico($parametros['clave_anexo2']);
$obj->setDiaCorte($parametros['dia_corte']);
$obj->setFechaElaboracion($parametros['fecha_anexo2']);
$obj->setNoContrato($parametros['anexo_contrato']);
$obj->setActivo(1);

$obj->setUsuarioUltimaModificacion($_SESSION['user']);
$obj->setUsuarioCreacion($_SESSION['user']);
$obj->setPantalla('PHP valida anexo');

if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
    if ($obj->newRegistro()) {
        echo $obj->getClaveAnexoTecnico();
    } else {
        echo "Error:El anexo no se pudo registrar, intenta más tarde por favor";
    }
} else {/* Modificar */
    $obj->setClaveAnexoTecnico($parametros['id']);
    if ($obj->editRegistro()) {
        echo $obj->getClaveAnexoTecnico();
    } else {
        echo "Error:El anexo no se pudo modificar, intenta más tarde por favor";
    }
}
?>