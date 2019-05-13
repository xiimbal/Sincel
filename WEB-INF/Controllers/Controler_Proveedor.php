<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Proveedor.class.php");
$obj = new Proveedor();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setClave($_GET['id']);
    if ($obj->deleteDomicilio()) {
        if ($obj->deleteRegistro()) {
            echo "El proveedor se eliminó correctamente";
        } else {
            echo "El proveedor no se pudo eliminar, ya que contiene datos asociados.";
        }
    } else {
        echo "Error: El domicilio del proveedor no se eliminó correctamnete";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setClave($parametros['txt_clave']);
    $obj->setNombre($parametros['txt_nombre']);
    $obj->setRfc($parametros['txt_rfc']);
    $obj->setTipo($parametros['sl_tipo']);
    $obj->setTelefono($parametros['txt_tel']);
    $obj->setContacto($parametros['txt_contacto']);
    $obj->setCorreo($parametros['txt_correo']);
    $obj->setFormPago($parametros['sl_forma_pago']);
    $obj->setCuentaBancaria($parametros['cuentaBancaria']);
    $obj->setDiasCredito($parametros['txt_Dias']);
    $obj->setCalle($parametros['txt_calle']);
    $obj->setNumExterior($parametros['txt_numExt']);
    $obj->setNumInterior($parametros['txt_numInt']);
    $obj->setColonia($parametros['txt_colonia']);
    $obj->setDelegacion($parametros['txt_delegacion']);
    $obj->setCiudad($parametros['txt_ciudad']);
    $obj->setEstado($parametros['txt_estado']);
    $obj->setPais($parametros['txt_pais']);
    $obj->setCp($parametros['txt_cp']);
    $obj->setNoiCliente($parametros['txt_no_cliente']);
    $obj->setReferencia($parametros['referenciaNum']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('catálogo proveedor');
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    if (isset($parametros['ck_notificacion']) && $parametros['ck_notificacion'] == "on") {
        $obj->setNotificar(1);
    } else {
        $obj->setNotificar(0);
    }
    $obj->setPorcentajeServicio($parametros["porcentaje_servicio"]);

    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            if ($obj->newDomicilio()) {
                echo "El proveedor <b>" . $obj->getNombre() . "</b> con clave <b>" . $obj->getClave() . "</b> se registró correctamente";
            } else {
                echo "Error: El domicilio del proveedor no se regitró";
            }
        } else {
            echo "Error: El proveedor con clave " . $obj->getClave() . " ya se encuentra registrado";
        }
    } else {/* Modificar */
        $obj->setClave($parametros['id']);
        if ($obj->editRegistro()) {
            if ($obj->editDomicilio()) {
                echo "El proveedor <b>" . $obj->getNombre() . "</b> con clave <b>" . $obj->getClave() . "</b> se modificó correctamente";
            } else {
                echo "Error: El domicilio del proveedor <b>" . $obj->getNombre() . "</b>  no se modificó";
            }
        } else {
            echo "Error: El proveedor con clave " . $obj->getClave() . " ya se encuentra registrado";
        }
    }
}