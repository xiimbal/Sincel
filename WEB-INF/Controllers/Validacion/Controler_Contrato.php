<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['form'])) {
    header("Location: ../../index.php");
}

include_once("../../Classes/Contrato.class.php");
include_once("../../Classes/Cliente.class.php");
include_once("../../Classes/Localidad.class.php");
$obj = new Contrato();

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}
//print_r($parametros);

if(isset($parametros['limbo']) && $parametros['limbo'] == "limbo"){
    $obj->setContratoLimbo(true);
    $obj->setNoContrato("Limbo_".$parametros['clave_contrato_cliente']);
}else{
    $obj->setContratoLimbo(false);
}

$campos = array();
$valores = array();
$mostrar = array();
$numero_conceptos = $parametros['numero_conceptos'];
for($i=1; $i<=$numero_conceptos; $i++){    
    if(isset($parametros['campo_'.$i]) && $parametros['campo_'.$i] != ""){
        array_push($campos, $parametros['campo_'.$i]);
        array_push($valores, $parametros['valor_'.$i]); 
        if(isset($parametros['mostrar_'.$i]) && $parametros['mostrar_'.$i] == "Activo"){
            array_push($mostrar, 1);
        }else{
            array_push($mostrar, 0);
        }
    }       
}

$obj->setCampos($campos);
$obj->setValores($valores);
$obj->setMostrar($mostrar);
$obj->setClaveCliente($parametros['clave_contrato_cliente']);
$obj->setNumeroCuenta($parametros['numero_cuenta']);
$obj->setIdBanco($parametros['banco']);
$obj->setIdCuentaBancaria($parametros['cuenta_bancaria']);
$obj->setDiasCredito($parametros['dias_credito']);
$obj->setFechaInicio($parametros['fecha_ini2']);
$obj->setFechaTermino($parametros['fecha_fin2']);
if(isset($parametros['activo']) && $parametros['activo'] == "1"){
    $obj->setActivo(1);
}else{
    $obj->setActivo(0);
}
$obj->setRazonSocial($parametros['razon_social']);
$obj->setFacturarA($parametros['facturarA']);
$obj->setFormaPago($parametros['forma_pago']);
$obj->setIdMetodoPago($parametros['metodo_pago']);
$obj->setIdFormaComprobantePago($parametros['forma_pago_complemento']);
$obj->setIdUsoCFDI($parametros['usoCFDI']);
$obj->setUsuarioUltimaModificacion($_SESSION['user']);
$obj->setUsuarioCreacion($_SESSION['user']);
$obj->setPantalla('PHP valida contrato');

$localidad = new Localidad();
$localidad->setCalle($parametros['Calle']);
$localidad->setNoExterior($parametros['NoExterior']);
$localidad->setNoInterior($parametros['NoInterior']);
$localidad->setEstado($parametros['Estado']);
$localidad->setColonia($parametros['Colonia']);
$localidad->setCiudad($parametros['Estado']);
$localidad->setDelegacion($parametros['Delegacion']);
$localidad->setPais("México");
$localidad->setCodigoPostal($parametros['CP']);
$localidad->setUsuarioCreacion($_SESSION['user']);
$localidad->setUsuarioUltimaModificacion($_SESSION['user']);
$localidad->setPantalla("PHP valida localidad");
$localidad->setActivo(1);

if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
    if ($obj->newRegistro()) {
        /*Si el cliente del contrato solo tiene un contrato, atcualizamos la razon social del cliente*/
        $contrato = new Contrato();
        $result = $contrato->getRegistroValidacion($obj->getClaveCliente());
        if(mysql_num_rows($result) == 1){//Si el cliente solo tiene un contrato, obtenemos ese contrato y actualizamos también la razon social del contrato
            $cliente = new Cliente();
            if($cliente->getRegistroById($obj->getClaveCliente())){
                if($cliente->getIdDatosFacturacionEmpresa() != $obj->getRazonSocial()){
                    $cliente->setIdDatosFacturacionEmpresa($obj->getRazonSocial());
                    $cliente->editRegistro();
                }
            }
        }
        
        $localidad->setClaveEspecialDomicilio($obj->getClaveCliente());
        $localidad->newRegistro(3);
        echo $obj->getNoContrato();
    } else {
        echo "Error:El contrato no se pudo registrar, notificalo al administrador por favor.";
    }
} else {/* Modificar */
    $localidad->setClaveEspecialDomicilio($obj->getClaveCliente());
    $obj->setNoContrato($parametros['id']);
    $obj->eliminarCampos();
    if ($obj->editRegistro()) {
        /*Si el cliente del contrato solo tiene un contrato, atcualizamos la razon social del cliente*/
        $contrato = new Contrato();
        $result = $contrato->getRegistroValidacion($obj->getClaveCliente());
        if(mysql_num_rows($result) == 1){//Si el cliente solo tiene un contrato, obtenemos ese contrato y actualizamos también la razon social del contrato
            $cliente = new Cliente();
            if($cliente->getRegistroById($obj->getClaveCliente())){
                if($cliente->getIdDatosFacturacionEmpresa() != $obj->getRazonSocial()){
                    $cliente->setIdDatosFacturacionEmpresa($obj->getRazonSocial());
                    $cliente->editRegistro();
                }
            }
        }
        
        $localidad2 = new Localidad();
        if ($localidad2->getLocalidadByClaveTipo($obj->getClaveCliente(), 3)) {
            $localidad->setIdDomicilio($localidad2->getIdDomicilio());
            $localidad->editRegistro();
        } else {
            $localidad->newRegistro(3);
        }
        echo $obj->getNoContrato();
    } else {
        echo "Error:El contrato no se pudo modificar, notificalo al administrador por favor.";
    }
}
?>