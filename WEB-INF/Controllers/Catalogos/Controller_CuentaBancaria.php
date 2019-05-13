<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/CuentaBancaria.class.php");
if (isset($_POST['clave']) && $_POST['clave'] != "") {
    $ctaba = new CuentaBancaria();
    if ($ctaba->deleteRegistro($_POST['clave'])) {
        echo "Se ha eliminado la cuenta bancaria exitosamente";
    } else {
        echo "Error: la cuenta bancaria tiene valores dependientes";
    }
} else {
    $parametros = "";
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $ctaba = new CuentaBancaria();
    $ctaba->setBanco($parametros['componenteCopiar']);
    $ctaba->setDescripcion($parametros['descripcion']);
    $ctaba->setClave($parametros['clave']);
    $ctaba->setCorreoEjecutivo($parametros['correo']);
    $ctaba->setEjecutivoCuenta($parametros['ejecutivo']);
    $ctaba->setNoCuenta($parametros['noCuenta']);
    $ctaba->setRFC($parametros['RFC']);
    $ctaba->setSucursal($parametros['sucursal']);
    $ctaba->setTelEjecutivo($parametros['telefono']);
    $ctaba->setTipoCuenta($parametros['tipoCuenta']);
    $ctaba->setFechaCorte($parametros['fecha_corte']);
    if (isset($parametros['activo']) && $parametros['activo'] == "1") {
        $ctaba->setActivo($parametros['activo']);
    } else {
        $ctaba->setActivo(0);
    }
    $ctaba->setPantalla("PHP Controller_CuentaBancaria");
    $ctaba->setUsuarioCreacion($_SESSION['user']);
    $ctaba->setUsuarioUltimaModificacion($_SESSION['user']);
    if (isset($parametros['id']) && $parametros['id'] != "") {
        $ctaba->setIdCuentaBancaria($parametros['id']);
        if ($ctaba->editRegistro()) {
            echo "Se ha editado la cuenta exitosamente";
        } else {
            echo "Error: La cuenta bancaria no se logro editar";
        }
    } else {
        if ($ctaba->newRegistro()) {
            echo "Se ha registrado la nueva cuenta bancaria";
        } else {
            echo "Error: La cuenta bancaria no se registro";
        }
    }
}
?>



