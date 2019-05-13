<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['form'])) {
    header("Location: ../../index.php");
}

include_once("../../Classes/CentroCosto.class.php");
include_once("../../Classes/Localidad.class.php");
include_once("../../Classes/Catalogo.class.php");
$obj = new CentroCosto();

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}


$obj->setClaveCentroCosto($parametros['id']);
$obj->setClaveCliente($parametros['cliente_cc2']);
$obj->setNombre($parametros['nombre_cc2']);
if (isset($parametros['Moroso']) && $parametros['Moroso'] == "1") {
    $obj->setMoroso("1");
} else {
    $obj->setMoroso("0"); //Por default es 0
}
$obj->setTipoDomicilioFiscal($parametros['domicilio_fiscal']);
if (isset($parametros['activo']) && $parametros['activo'] == 1) {
    $obj->setActivo(1);
} else {
    $obj->setActivo(0);
}

$obj->setUsuarioCreacion($_SESSION['user']);
$obj->setUsuarioUltimaModificacion($_SESSION['user']);
$obj->setPantalla('PHP valida localidad');

$localidad = new Localidad();
$localidad->setCalle($parametros['Calle']);
$localidad->setNoExterior($parametros['NoExterior']);
$localidad->setNoInterior($parametros['NoInterior']);
$localidad->setEstado($parametros['Estado']);
$localidad->setClaveZona($parametros['zona']);
$localidad->setColonia($parametros['Colonia']);
$localidad->setCiudad($parametros['Estado']);
$localidad->setDelegacion($parametros['Delegacion']);
$localidad->setPais("México");
$localidad->setCodigoPostal($parametros['CP']);
$localidad->setLatitud($parametros['Latitud']);
$localidad->setLongitud($parametros['Longitud']);
$localidad->setLocalidad($parametros['localidad_string']);
$localidad->setIdTipoDomicilio($parametros['domicilio_fiscal']);
$localidad->setUsuarioCreacion($_SESSION['user']);
$localidad->setUsuarioUltimaModificacion($_SESSION['user']);
$localidad->setPantalla("PHP valida localidad");
$localidad->setActivo(1);

if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
    $result = $obj->getCentroCostoByClienteYNombre($parametros['cliente_cc2'], $parametros['nombre_cc2']);
    if (mysql_num_rows($result) > 0) {//Si ya existe una localidad con este nombre
        echo "Error: ya existe una localidad con el nombre de <b>" . $parametros['nombre_cc2'] . "</b> para este cliente";
        return;
    } else {
        if ($obj->newRegistro()) {                        
            
            if ($obj->getTipoDomicilioFiscal() == "2") {//Si se asocia al cliente
                $localidad->setClaveEspecialDomicilio($obj->getClaveCliente());
                $localidad2 = new Localidad();
                if ($localidad2->getLocalidadByClaveTipo($localidad->getClaveEspecialDomicilio(), "3")) {
                    $localidad->setIdDomicilio($localidad2->getIdDomicilio());
                    $localidad->editRegistro();
                } else {                    
                    $localidad->newRegistro(3);
                }
            } else {
                $localidad->setClaveEspecialDomicilio($obj->getClaveCentroCosto());
                $localidad2 = new Localidad();
                if ($localidad2->getLocalidadByClaveTipo($localidad->getClaveEspecialDomicilio(), "5")) {
                    $localidad->setIdDomicilio($localidad2->getIdDomicilio());
                    $localidad->editRegistro();
                } else {                    
                    $localidad->newRegistro(5);
                }
            }
            echo $obj->getClaveCentroCosto();
        } else {
            echo "Error: El centro de costo no se pudo registrar, intenta más tarde por favor";
        }
    }
} else {/* Modificar */
    if ($obj->getTipoDomicilioFiscal() == "2") {
        $localidad->setClaveEspecialDomicilio($obj->getClaveCliente());
    } else {
        $localidad->setClaveEspecialDomicilio($obj->getClaveCentroCosto());
    }
    
    if ($obj->editRegistro()) {
        if ($obj->getTipoDomicilioFiscal() == "2") {
            $localidad2 = new Localidad();
            if ($localidad2->getLocalidadByClaveTipo($localidad->getClaveEspecialDomicilio(), "3")) {
                $localidad->setIdDomicilio($localidad2->getIdDomicilio());
                $localidad->editRegistro();
            } else {
                $localidad->newRegistro(3);
            }
        } else {
            $localidad2 = new Localidad();
            if ($localidad2->getLocalidadByClaveTipo($localidad->getClaveEspecialDomicilio(), "5")) {
                $localidad->setIdDomicilio($localidad2->getIdDomicilio());
                $localidad->editRegistro();
            } else {
                $localidad->newRegistro(5);
            }
        }
        
        echo $obj->getClaveCentroCosto();
    } else {
        echo "Error: El centro de costo no se pudo modificar, intenta más tarde por favor";
    }
}
?>