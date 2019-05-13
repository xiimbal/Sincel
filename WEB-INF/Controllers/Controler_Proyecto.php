<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Ticket.class.php");
include_once("../Classes/Catalogo.class.php");
include_once("../Classes/Contacto.class.php");
include_once("../Classes/ccliente.class.php");
include_once("../Classes/PermisosSubMenu.class.php");

$catalogo = new Catalogo();
$obj = new Ticket();
$contacto = new Contacto();
$permisos_grid2 = new PermisosSubMenu();
$cliente = new ccliente();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$obj->setIdSubtipo($parametros['tipo']);
$obj->setFechaHora($parametros['fecha']);
$obj->setFechaFinReal($parametros['fecha_fr']);
$obj->setFechaFinPrevisto($parametros['fecha_fp']);
$obj->setPresupuesto($parametros['presupuesto']);
$obj->setPrioridad($parametros['prioridad']);
if(isset($parametros['cerrarProyecto']) && $parametros['cerrarProyecto'] == "on"){
    $obj->setProgreso("100");    
}else{
    $obj->setProgreso($parametros['amount']);    
}
$obj->setNombre($parametros['nombre']);
$obj->setUsuario($_SESSION['user']);
$obj->setTipoReporte($parametros['tipoReporte']);
$obj->setClaveCliente($parametros['cliente_ticket']);
$obj->setUsuarioOrigen($parametros['usuarioOrigen']);
if($cliente->getregistrobyID($parametros['cliente_ticket'])){
    $obj->setNombreCliente($cliente->getRazonSocial());
}
$obj->setDescripcionReporte($parametros['descripcion']);
$obj->setObservacionAdicional($parametros['observacion']);
$obj->setAreaAtencion($parametros['areaAtencionGral']);
if(isset($parametros['tipoContacto']) && $parametros['tipoContacto'] != ""){
    if($parametros['tipoContacto'] == "1" && $parametros['contacto_cliente'] != "" && $parametros['contacto_cliente'] != "null"){
        $obj->setContacto($parametros['contacto_cliente']);
        if($contacto->getRegstroByID($parametros['contacto_cliente'])){
            $obj->setNombreResp($contacto->getNombre());
            $obj->setTelefono1Resp($contacto->getTelefono());
        }
    }else if($parametros['tipoContacto'] == "2" && $parametros['contacto_nuevo'] != ""){
        $contacto->setIdTipoContacto(14);//Tipo ticket
        $contacto->setActivo(1);
        $contacto->setNombre($parametros['contacto_nuevo']);
        $contacto->setClaveEspecialContacto($parametros['cliente_ticket']);
        $contacto->setUsuarioCreacion($_SESSION['user']);
        $contacto->setUsuarioUltimaModificacion($_SESSION['user']);
        if($contacto->newRegistro()){
            $contacto->getUltimoIdContacto();
            $obj->setNombreResp($contacto->getNombre());
            $obj->setContacto($contacto->getIdContacto());
        }
    }
}
/*if($parametros['contacto_cliente'] != "" && $parametros['contacto_cliente'] != "null"){
    $obj->setContacto($parametros['contacto_cliente']);
    if($contacto->getRegstroByID($parametros['contacto_cliente'])){
        $obj->setNombreResp($contacto->getNombre());
        $obj->setTelefono1Resp($contacto->getTelefono());
    }
}else{//Poner un campo para que puedan capturar directamente el contacto (guardarlo también en el catalogo de contactos, relacionado al cliente y de tipo ticket)
    if($parametros['contacto_nuevo'] != ""){
        $contacto->setIdTipoContacto(14);//Tipo ticket
        $contacto->setActivo(1);
        $contacto->setNombre($parametros['contacto_nuevo']);
        $contacto->setClaveEspecialContacto($parametros['cliente_ticket']);
        $contacto->setUsuarioCreacion($_SESSION['user']);
        $contacto->setUsuarioUltimaModificacion($_SESSION['user']);
        if($contacto->newRegistro()){
            $contacto->getUltimoIdContacto();
            $obj->setNombreResp($contacto->getNombre());
            $obj->setContacto($contacto->getIdContacto());
        }
    }
}*/
$obj->setActivo(1);
$obj->setUsuarioCreacion($_SESSION['user']);
$obj->setUsuarioUltimaModificacion($_SESSION['user']);
$obj->setPantalla("Controler_Proyecto PHP");

if(isset($parametros['idTicket']) && empty($parametros['idTicket']) ){ 
    $obj->setEstadoDeTicket(3);
    if($obj->newRegistroProyecto()){
        if($obj->asociarTicketTecnico($parametros['tecnico'], 1)){//Ahorita es 1, a reserva de que nos diga que se parametrice o algo así.
            echo "<br>El $nombre_objeto <b>" . $obj->getIdTicket() . "</b> se registró correctamente.||".$obj->getIdTicket();
        }else{
            echo "<br>El $nombre_objeto <b>" . $obj->getIdTicket() . "</b> se registró correctamente, pero no se pudo asociar el técnico a éste.||".$obj->getIdTicket();
        }
    }else{
        echo "<br/>Error: El $nombre_objeto no se pudo registrar, intente de nuevo o notifiquelo con el administrador.";
    }    
}else{    
    $obj->setIdTicket($parametros['idTicket']);    
    if($obj->editarProyecto()){
        if($obj->eliminarAsignaciones()){
            if($obj->asociarTicketTecnico($parametros['tecnico'], 1)){//Ahorita es 1, a reserva de que nos diga que se parametrice o algo así.
                echo "<br/>El $nombre_objeto <b>".$obj->getIdTicket()."</b> se editó correctamente.||".$obj->getIdTicket();  
                //Vamos a cerrar el proyecto, si es que lo pidieron
                if(isset($parametros['cerrarProyecto']) && $parametros['cerrarProyecto'] == "on"){
                    include_once("../Classes/NotaTicket.class.php");
                    $nota = new NotaTicket();
                    $nota->setIdTicket($obj->getIdTicket());
                    $nota->setDiagnostico("$nombre_objeto cerrado.");
                    $nota->setIdEstatus(16);//El id para cerrado es 16
                    $nota->setActivo(1);
                    $nota->setUsuarioCreacion($_SESSION['user']);
                    $nota->setUsuarioModificacion($_SESSION['user']);
                    $nota->setMostrarCliente(1);
                    $nota->setProgreso(100);
                    if($nota->newRegistro()){
                        echo "||true";
                    }else{
                        echo "||false";
                    }
                }
            }else{
                echo "<br>El $nombre_objeto <b>" .$obj->getIdTicket(). "</b> se editó correctamente, pero no se pudo asociar el técnico a éste.||".$obj->getIdTicket();
            }          
        }else{
            echo "<br>El $nombre_objeto <b>" . $obj->getIdTicket() . "</b> se editó correctamente pero no se pudo modificar el técnico asignado a éste.||".$obj->getIdTicket();
        }
    }else{
        echo "<br/>Error: El $nombre_objeto <b>".$obj->getIdTicket()."</b> no se pudo registrar, intente de nuevo o notifiquelo con el adminisrador.";
    }
    
}

?>