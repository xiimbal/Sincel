<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Usuario.class.php");
include_once("../Classes/Conductor.class.php");
include_once("../Classes/DomicilioUsuarioTurno.class.php");
$obj = new Usuario();
$obj_aux = new Usuario();
$domicilioUsuarioTurno= new DomicilioUsuarioTurno();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setId($_GET['id']);
    $domicilioUsuarioTurno->setIdUsuario($_GET['id']);
    if ($domicilioUsuarioTurno->deleteRegistro()) {
        echo "El domicilio y el ";
        if ($obj->deleteRegistro()) {
            echo "usuario se eliminaron correctamente";
            }
        else {
            echo "El usuario no se pudo eliminar, ya que contiene datos asociados. ";
        }
    }else {
            echo "El domicilio no se pudo eliminar, ya que contiene datos asociados.";
    }
    } else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }

    //print_r($parametros);
    $obj->setProveedorFactura($parametros['proveedorF']);
    $obj->setUsuario($parametros['usuario']);
    $obj->setNombre($parametros['nombre']);
    $obj->setPaterno($parametros['paterno']);
    $obj->setMaterno($parametros['materno']);
    $obj->setPassword($parametros['pass1']);
    $obj->setTelefono($parametros['telefono']);
    $obj->setEmail($parametros['correo']);
    $obj->setPuesto($parametros['puesto']);
    $obj->setIdAlmacen($parametros['almacen']);
    $obj->setIdUsuarioMultiBD($parametros['idUsuarioMBD']);
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('PHP admin usuario');
    /************* Esto es exclusivo porque se están ocupando los empleados como rutas *************/
    $obj->setCostoFijo($parametros["costo_fijo"]);
    $obj->setIdFormaPago($parametros["forma_pago"]);
    if(isset($parametros['rfc'])){
        $obj->setRFC($parametros['rfc']);
    }
    if(isset($parametros['PorcentajeDesc'])){        
        $obj->setPorcentajeDesc($parametros['PorcentajeDesc']);
    }
    /*****************/
    
    if(isset($parametros['numero_conceptos'])){
    $slcCampanias = array();
    $slcTurnos = array();
    $numero_conceptos = $parametros['numero_conceptos'];
    for($i=1; $i<=$numero_conceptos; $i++){        
        array_push($slcCampanias, $parametros['slcCampania_'.$i]);
        array_push($slcTurnos, $parametros['slcTurno_'.$i]);      
    }
    $domicilioUsuarioTurno->setSlcCampanias($slcCampanias); 
    $domicilioUsuarioTurno->setSlcTurnos($slcTurnos);
    }else{
    if($parametros['slcTurno']>0){$slcTurno=$parametros['slcTurno'];} else{$slcTurno="NULL";} //Para almacenar como NULL si no se selecciona Turno
    if($parametros['slcCampania']>0){$slcCampania=$parametros['slcCampania'];}else{$slcCampania="NULL";}//Para almacenar como NULL si no se selecciona Campaña
    $domicilioUsuarioTurno->setTurno($slcTurno);
    $domicilioUsuarioTurno->setCampania($slcCampania);
    }
    
    if($parametros['area']>0){$area=$parametros['area'];}else{$area="NULL";}
    $domicilioUsuarioTurno->setArea($area);
    if(isset($parametros['contacto']) || !empty($parametros['contacto'])){
        if($parametros['contacto']>0){$contacto=$parametros['contacto'];}else{$contacto="NULL";}
        $domicilioUsuarioTurno->setContacto($contacto);
    }
    if(isset($parametros['codigoB']) || !empty($parametros['codigoB'])){
        $domicilioUsuarioTurno->setCodigoB($parametros['codigoB']);
    }
    if(isset($parametros['vehiculo']) || !empty($parametros['vehiculo'])){
        $domicilioUsuarioTurno->setVehiculo($parametros['vehiculo']);
    }
    $domicilioUsuarioTurno->setCalle($parametros['txtCalle']);
    $domicilioUsuarioTurno->setExterior($parametros['txtExterior']);
    $domicilioUsuarioTurno->setInterior($parametros['txtInterior']);
    $domicilioUsuarioTurno->setColonia($parametros['txtColonia']);
    $domicilioUsuarioTurno->setCiudad($parametros['txtCiudad']);
    $domicilioUsuarioTurno->setDelegacion($parametros['txtDelegacion']);
    $domicilioUsuarioTurno->setEstado($parametros['slcEstado']);
    $domicilioUsuarioTurno->setLocalidad($parametros['txtLocalidad']);
    $domicilioUsuarioTurno->setCp($parametros['txtcp']);
    $domicilioUsuarioTurno->setLatitud($parametros['Latitud']);
    $domicilioUsuarioTurno->setLongitud($parametros['Longitud']);
    $domicilioUsuarioTurno->setUsuarioCreacion($_SESSION['user']);
    $domicilioUsuarioTurno->setUsuarioUltimaCreacion($_SESSION['user']);
    $domicilioUsuarioTurno->setPantalla("PHP Usuario Turno");
    
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if($obj_aux->getUsuarioByUser($obj->getUsuario())){
            echo "Error: El usuario <b>".$obj->getUsuario()."</b> ya está registrado, intenta con otro nombre de usuario";
            return;
        }
        if ($obj->newRegistro()) {
            echo "El usuario <b>" . $obj->getUsuario() . "</b> se registro correctamente. ";
            $obj->registrarNegociosDeUsuario($parametros['negocios']);
            
            $domicilioUsuarioTurno->setIdUsuario($obj->getId());
            if ($domicilioUsuarioTurno->newRegistro()) {echo "Su domicilio se registro con exito.";}
            else{echo "<br/> El domicilio del usuario <b>" . $obj->getUsuario() . "</b> NO se registró correctamente";}
            
            $conductor = new Conductor();
            /* Verificamos si se va a guardar como mensajero */
            if (isset($parametros['mensajero']) && $parametros['mensajero'] == "Si") {
                $conductor->setNombre($obj->getNombre());
                $conductor->setApellidoPaterno($obj->getPaterno());
                $conductor->setApellidoMaterno($obj->getMaterno());
                $conductor->setIdUsuario($obj->getId());
                $conductor->setActivo($obj->getActivo());
                $conductor->setUsuarioCreacion($obj->getUsuarioCreacion());
                $conductor->setUsuarioUltimaModificacion($obj->getUsuarioModificacion());
                $conductor->setPantalla($obj->getPantalla());
                if ($conductor->newRegistro()) {
                    echo " y se registró como mensajero";
                } else {
                    echo "Error: no se pudo insertar el usuario como mensajero, intenta de nuevo o repórtalo por favor.";
                }
            }
        } else {
            echo "Error: El usuario " . $obj->getUsuario() . " ya se encuentra registrado";
        }
    } else {/* Modificar */
        $obj->setId($parametros['id']);
        if($obj_aux->getUsuarioByUser($obj->getUsuario()) && $obj_aux->getId() != $obj->getId()){
            echo "Error: El usuario <b>".$obj->getUsuario()."</b> ya está registrado, intenta con otro nombre de usuario";
            return;
        }
        
        if (isset($parametros['cambiar']) && $parametros['cambiar'] == "on") {/* Si se acciono el boton de modificar password */            
            if ($obj->editarRegistroConPassword()) {
                echo "El usuario <b>" . $obj->getUsuario() . "</b> se modificó correctamente";
                $obj->registrarNegociosDeUsuario($parametros['negocios']);
            } else {
                echo "Error: El usuario " . $obj->getUsuario() . " ya se encuentra registrado";
            }
        } else {            
            if ($obj->editRegistro()) {
                $domicilioUsuarioTurno->setIdUsuario($obj->getId());
                $domicilioUsuarioTurno->getRegistroById($parametros['id']);
                
                if(isset($parametros['numero_conceptos'])){
                    $slcCampanias = array();
                    $slcTurnos = array();
                    $numero_conceptos = $parametros['numero_conceptos'];
                    for($i=1; $i<=$numero_conceptos; $i++){        
                        array_push($slcCampanias, $parametros['slcCampania_'.$i]);
                        array_push($slcTurnos, $parametros['slcTurno_'.$i]);      
                    }
                    $domicilioUsuarioTurno->setSlcCampanias($slcCampanias); 
                    $domicilioUsuarioTurno->setSlcTurnos($slcTurnos);
                }else{
                if($parametros['slcTurno']>0){$slcTurno=$parametros['slcTurno'];} else{$slcTurno="NULL";} //Para almacenar como NULL si no se selecciona Truno
                if($parametros['slcCampania']>0){$slcCampania=$parametros['slcCampania'];}else{$slcCampania="NULL";}//Para almacenar como NULL si no se selecciona Campaña
                $domicilioUsuarioTurno->setTurno($slcTurno);
                $domicilioUsuarioTurno->setCampania($slcCampania);
                }
                
                /* Actualizamos la info del domicilio */
                if($parametros['area']>0){$area=$parametros['area'];}else{$area="NULL";}
                $domicilioUsuarioTurno->setArea($area);
                if(isset($parametros['contacto']) || !empty($parametros['contacto'])){
                    if($parametros['contacto']>0){$contacto=$parametros['contacto'];}else{$contacto="NULL";}
                }else{$contacto="NULL";}
                $domicilioUsuarioTurno->setContacto($contacto);
                if(isset($parametros['codigoB']) || !empty($parametros['codigoB'])){
                    $domicilioUsuarioTurno->setCodigoB($parametros['codigoB']);
                }
                if(isset($parametros['vehiculo']) || !empty($parametros['vehiculo'])){
                    $domicilioUsuarioTurno->setVehiculo($parametros['vehiculo']);
                }
                $domicilioUsuarioTurno->setCalle($parametros['txtCalle']);
                $domicilioUsuarioTurno->setExterior($parametros['txtExterior']);
                $domicilioUsuarioTurno->setInterior($parametros['txtInterior']);
                $domicilioUsuarioTurno->setColonia($parametros['txtColonia']);
                $domicilioUsuarioTurno->setCiudad($parametros['txtCiudad']);
                $domicilioUsuarioTurno->setDelegacion($parametros['txtDelegacion']);
                $domicilioUsuarioTurno->setEstado($parametros['slcEstado']);
                $domicilioUsuarioTurno->setCp($parametros['txtcp']);
                $domicilioUsuarioTurno->setLatitud($parametros['Latitud']);
                $domicilioUsuarioTurno->setLongitud($parametros['Longitud']);
                $domicilioUsuarioTurno->setLocalidad($parametros['txtLocalidad']);
                $domicilioUsuarioTurno->setUsuarioCreacion($_SESSION['user']);
                $domicilioUsuarioTurno->setUsuarioUltimaCreacion($_SESSION['user']);
                $domicilioUsuarioTurno->setPantalla("PHP Usuario Turno");
                $queryDomicilioUsuarioTurno = $domicilioUsuarioTurno->getIdDomicilio();

                    if ($queryDomicilioUsuarioTurno == "" || $queryDomicilioUsuarioTurno == null || $queryDomicilioUsuarioTurno == 0) {
                        $domicilioUsuarioTurno->newRegistro();
                    } else {
                        $domicilioUsuarioTurno->editRegistro();
                    }
                    
                echo "El usuario <b>" . $obj->getUsuario() . "</b> se modificó correctamente";
                $obj->registrarNegociosDeUsuario($parametros['negocios']);
            } else {
                echo "Error: El usuario " . $obj->getUsuario() . " ya se encuentra registrado";
            }
        }
        $conductor = new Conductor();
        /* Verificamos si se va a guardar como mensajero */
        if (isset($parametros['mensajero']) && $parametros['mensajero'] == "Si") {
            $conductor->setNombre($obj->getNombre());
            $conductor->setApellidoPaterno($obj->getPaterno());
            $conductor->setApellidoMaterno($obj->getMaterno());
            $conductor->setIdUsuario($obj->getId());
            $conductor->setActivo($obj->getActivo());
            $conductor->setUsuarioCreacion($obj->getUsuarioCreacion());
            $conductor->setUsuarioUltimaModificacion($obj->getUsuarioModificacion());
            $conductor->setPantalla($obj->getPantalla());
            if ($obj->isMensajeroConductor()) {
                $conductor->setIdConductor($conductor->getIdConductorByIdUsuario());
                if ($conductor->editRegistro()) {
                    echo " y se editó como mensajero";
                } else {
                    echo "Error: no se pudo editar el usuario como mensajero, intenta de nuevo o repórtalo por favor.";
                }
            } else {
                if ($conductor->newRegistro()) {
                    echo " y se registró como mensajero";
                } else {
                    echo "Error: no se pudo insertar el usuario como mensajero, intenta de nuevo o repórtalo por favor.";
                }
            }
        } else {
            if (!$conductor->deleteRegistroByIdUsuario($obj->getId())) {
                echo "<br/>Error: no se pudo eliminar al usuario como mensajero";
            } else {
                echo " y no se registró como mensajero";
            }
        }
    }
}
?>