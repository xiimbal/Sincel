<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}

include_once("../../Classes/Cliente.class.php");
include_once("../../Classes/Contrato.class.php");
include_once("../../Classes/CentroCosto.class.php");
include_once("../../Classes/ParametroGlobal.class.php");
include_once("../../Classes/ccliente.class.php");
include_once("../../Classes/Catalogo.class.php");

$localidad = new CentroCosto();
$obj = new Cliente();
if (isset($_GET['id'])) {
    if ($obj->deleteRegistro($_GET['id'])) {
        echo "El cliente fue eliminado exitosamente";
    } else {
        echo "No se pudo eliminar el cliente, ya tiene datos asociados";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }        

    $obj->setIdDatosFacturacionEmpresa($parametros['razon_cliente2']);
    $obj->setRFC($parametros['rfc_cliente2']);
    $obj->setNombreRazonSocial($parametros['nombre_cliente2']);
    if (isset($parametros['mostrar_contrato']) && $parametros['mostrar_contrato'] == "1") {
        $obj->setMostarMesContrato(1);    
    } else {
        $obj->setMostarMesContrato(0);    
    }
    
    if (isset($parametros['activo']) && $parametros['activo'] == "1") {
        $obj->setActivo(1);    
    } else {
        $obj->setActivo(0);    
    }
    
    $obj->setIdTipoCliente($parametros['tipo']);
    $obj->setIdTipoMorosidad($parametros['tipo_morosidad']);
    
    if (isset($parametros['modalidad2'])) {
        $obj->setModalidad($parametros['modalidad2']);
    } else {
        $obj->setModalidad("1"); //Por default es 1 (Arrendamiento)
    }
    if (isset($parametros['Moroso']) && $parametros['Moroso'] == "1") {
        $obj->setIdEstatusCobranza("2");
    } else {
        $obj->setIdEstatusCobranza("1"); //Por default es 0
    }

    if (isset($parametros['tipo_facturacion'])) {
        $obj->setIdTipoFacturacion($parametros['tipo_facturacion']);
    } else {
        $obj->setIdTipoFacturacion("1"); //por default es 1 (Por cliente)
    }
    
    if (isset($parametros['genera']) && $parametros['genera'] == "1") {
        $obj->setGeneraFactura("1");
    } else {
        $obj->setGeneraFactura("0");
    }
    
    if(isset($parametros['dias_credito']) && $parametros['dias_credito']!=""){
        $obj->setDiasCredito($parametros['dias_credito']);
    }
    
    //$obj->setNumeroCuenta($parametros['numero_cuenta']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setPantalla('PHP valida cliente');

    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        $obj_aux = new Cliente();
        if($obj_aux->getRegistroByRFCValidacion($obj->getRFC())){
            echo "Error: el rfc <b>".$obj->getRFC()."</b> ya se encuentra registrado en el sistema.";
            return false;
        }
        if ($obj->newRegistro()) {
            if(isset($parametros['idTicketValidar']) && !empty($parametros['idTicketValidar'])){
                $catalogo = new Catalogo();
                $result = $catalogo->obtenerLista("SELECT t.CorreoEAtenc, t.Telefono1Atenc, t.ObservacionAdicional, 
                    dt.*, tc.* 
                    FROM `c_domicilioticket` AS dt
                    left join c_ticketcliente as tc on tc.IdTicket = dt.IdTicket
                    LEFT JOIN c_ticket AS t ON t.IdTicket = tc.IdTicket
                    where dt.IdTicket = ".$parametros['idTicketValidar'].";");
                while($rs = mysql_fetch_array($result)){
                    $ccliente = new ccliente();
                    if($ccliente->getregistrobyID($obj->getClaveCliente())){
                        $ccliente->setTipoDomicilio(3);
                        $ccliente->setIdCliente($obj->getClaveCliente());
                        $ccliente->setCalleF($rs['Calle']);
                        $ccliente->setNoExtF($rs['NoExterior']);
                        $ccliente->setNoIntF($rs['NoInterior']);
                        $ccliente->setColoniaF($rs['Colonia']);
                        $ccliente->setCiudadF($rs['Ciudad']);
                        $ccliente->setEstadoF($rs['Estado']);
                        $ccliente->setDelegacionF($rs['Delegacion']);
                        $ccliente->setPais($rs['Pais']);
                        $ccliente->setCPF($rs['CodigoPostal']);
                        $ccliente->setUsuarioCreacion($obj->getUsuarioCreacion());
                        $ccliente->setUsuarioModificacion($obj->getUsuarioUltimaModificacion());
                        $ccliente->setPantalla($obj->getPantalla());

                        $ccliente->setEmail($rs['CorreoEAtenc']);
                        $ccliente->setTelefono($rs['Telefono1Atenc']);
                        $ccliente->setComentario($rs['ObservacionAdicional']);
                        $ccliente->setFacebook($rs['Facebook']);
                        $ccliente->setTwitter($rs['Twitter']);
                        //$ccliente->setRFCD($rs['RFC']);
                        $ccliente->setHorario($rs['Horario']);
                        $ccliente->setGiro($rs['IdGiro']);
                        $ccliente->setCalificacion($rs['Calificacion']);
                        $ccliente->setImagen($rs['Foto']);
                        $ccliente->setSitioweb($rs['Sitioweb']);
                        $ccliente->setEjecutivoCuenta($rs['EjecutivoCuenta']);
                        $ccliente->setEjecutivoAtencionCliente($rs['EjecutivoCuenta']);
                        $ccliente->setLatitud($rs['Latitud']);
                        $ccliente->setLongitud($rs['Longitud']);
                        $ccliente->update();
                    }
                }
            }
            echo $obj->getClaveCliente();
            /*Obtenemos la zona que se pone por default segun los parametros globales*/
            $parametro = new ParametroGlobal();
            if($parametro->getRegistroById("2")){
                $zona = $parametro->getValor();
            }else{
                $zona = "Z06";
            }
            $localidad->setClaveZona($zona);
            if($parametro->getRegistroById("6")){
                $nombre = $parametro->getValor();
            }else{
                $nombre = "Oficinas Centrales";
            }
            $localidad->setNombre($nombre);
            $localidad->setActivo(1);
            $localidad->setUsuarioCreacion($_SESSION['user']);
            $localidad->setUsuarioUltimaModificacion($_SESSION['user']);
            $localidad->setPantalla("PHP valida cliente");
            $localidad->setClaveCliente($obj->getClaveCliente());
            $localidad->newRegistro();
        } else {
            echo "Error: El cliente no se pudo registrar, intenta más tarde por favor";
        }
    } else {/* Modificar */
        $obj->setClaveCliente($parametros['id']);
        if ($obj->editRegistro()) {
            $contrato = new Contrato();
            $result = $contrato->getRegistroValidacion($obj->getClaveCliente());
            if(mysql_num_rows($result) == 1){//Si el cliente solo tiene un contrato, obtenemos ese contrato y actualizamos también la razon social del contrato
                while($rs=  mysql_fetch_array($result)){
                    if($contrato->getRegistroById($rs['NoContrato'])){
                        if($contrato->getRazonSocial() != $obj->getIdDatosFacturacionEmpresa()){
                            $contrato->setRazonSocial($obj->getIdDatosFacturacionEmpresa());
                            $contrato->editRegistro();
                        }
                    }
                }
            }
            echo $obj->getClaveCliente();
        } else {
            echo "Error: El cliente no se pudo modificar, intenta más tarde por favor";
        }
    }
}
?>