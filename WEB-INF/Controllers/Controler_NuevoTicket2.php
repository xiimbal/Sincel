<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/Ticket.class.php");
include_once("../Classes/Pedido.class.php");
include_once("../Classes/LecturaTicket.class.php");
include_once("../Classes/NotaTicket.class.php");
include_once("../Classes/Usuario.class.php");
include_once("../Classes/NotaRefaccion.class.php");
include_once("../Classes/Catalogo.class.php");
include_once("../Classes/Contacto.class.php");
include_once("../Classes/SolicitudToner.class.php");
include_once("../Classes/Bitacora.class.php");
include_once("../Classes/Inventario.class.php");
include_once("../Classes/Catalogo.class.php");

$obj = new Ticket();
$pedido = new Pedido();
$lecturaTicket = new LecturaTicket();
$notaTicket = new NotaTicket();
$notaRefaccion = new NotaRefaccion();
$contacto = new Contacto();
$solicitudToner = new SolicitudToner();
$bitacora = new Bitacora();
$catalogo = new Catalogo();
$Inventario = new Inventario();

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$obj->setClaveCentroCosto($parametros['slcLocalidad']);
$obj->setClaveCliente($parametros['slcCliente']);
$obj->setNombreCentroCosto($parametros['nombreCC']);
$obj->setNombreCliente($parametros['nombreCliente']);
$obj->setActualizarInfoCliente(0);
$obj->setActualizarInfoEquipo(0);
$obj->setActualizarInfoEstatCobra(0);
if($parametros['prioridad'] == 0){
    $obj->setPrioridad("NULL");
}else{
    $obj->setPrioridad($parametros['prioridad']);
}
$obj->setUsuario($_SESSION['user']);

if (isset($parametros['sltEstadoTicket'])) {
    $obj->setEstadoDeTicket($parametros['sltEstadoTicket']);
} else {
    $obj->setEstadoDeTicket(3);
}
$obj->setTipoReporte($parametros['sltTipoReporte']);

$tamanoTabla = "";
$equipo = $_POST['tabla'];
$noSerie = $parametros['txtNoSrieFallaBuscar'];
$NoParte = "";
$Modelo = "";

$bitacora->setNoSerie($noSerie);
if(!$bitacora->verficarExistencia()){//si la bitacora del equipo no existe    
    $consulta = "SELECT NoParte,Modelo FROM `c_equipo` LIMIT 0,1;";
    $result = $catalogo->obtenerLista($consulta);
    if(mysql_num_rows($result) > 0){
        while($rs = mysql_fetch_array($result)){
            $NoParte = $rs['NoParte'];
            $Modelo = $rs['Modelo'];
        }
    }else{
        echo "<br/>Error: debe de haber al menos un número de parte para poder realizar este registro";
        return;
    }
    
    
    $Inventario->setEmpresa($empresa);
    if(!$Inventario->insertarInventarioValidando($noSerie, $NoParte, $parametros['ubicacionNoDomicilio'], 
            $obj->getClaveCentroCosto(), $obj->getClaveCliente(), $Modelo, FALSE)){
        echo "<br/>Error: no se pudo crear la serie correctamente, por favor notifiquelo con el administrador del sistema";
        return;
    }
}else{    
    $result = $Inventario->getDatosDeInventario($noSerie);
    while($rs = mysql_fetch_array($result)){
        $NoParte = $rs['NoParte'];
        $Modelo = $rs['Modelo'];
    }    
}

$obj->setNoSerieEquipo($noSerie);
$obj->setModeloEquipo($Modelo);

if ($parametros['rdContacto'] == "1") {
    $contacto->setClaveEspecialContacto($parametros['slcLocalidad']);
    $contacto->setIdTipoContacto(8);
    $contacto->setNombre($parametros['txtNombre1']);
    if(isset($parametros['txtExtencion1']) && !empty($parametros['txtExtencion1'])){
        $contacto->setTelefono($parametros['txtTelefono1']." ext. ".$parametros['txtExtencion1']);
    }else{
        $contacto->setTelefono($parametros['txtTelefono1']);
    }
    $contacto->setCelular($parametros['txtCelular']);
    $contacto->setCorreoElectronico($parametros['correoElectronico']);
    $contacto->setActivo(1);
    $contacto->setUsuarioCreacion($_SESSION['user']);
    $contacto->setUsuarioUltimaModificacion($_SESSION['user']);
    $contacto->setPantalla("Alta ticket php 2");
    if (!$contacto->newRegistroCompleto()) {
        echo "<br/>Error: El contacto no se registró correctamente";
    }
    $obj->setNombreResp($parametros['txtNombre1']);
} else if ($parametros['rdContacto'] == "0") {
    $id = split(" // ", $parametros["txtNombre"]);
    $contacto->setIdContacto($id[4]);
    $contacto->setNombre($id[0]);
    $contacto->setClaveEspecialContacto($id[6]);
    $contacto->setIdTipoContacto($id[5]);
    if(isset($parametros['txtExtencion1']) && !empty($parametros['txtExtencion1'])){
        $contacto->setTelefono($parametros['txtTelefono1']." ext. ".$parametros['txtExtencion1']);
    }else{
        $contacto->setTelefono($parametros['txtTelefono1']);
    }
    $contacto->setCelular($parametros['txtCelular']);
    $contacto->setCorreoElectronico($parametros['correoElectronico']);
    $contacto->setActivo(1);
    $contacto->setUsuarioCreacion($_SESSION['user']);
    $contacto->setUsuarioUltimaModificacion($_SESSION['user']);
    $contacto->setPantalla("Alta ticket php 2");
    if (!$contacto->editRegistro()) {
        echo "<br/>Error: El contacto no se editó correctamente";
    }
    $obj->setNombreResp($id[0]);
}
$obj->setTelefono1Resp($parametros['txtTelefono1']);
$obj->setExtension1Resp($parametros['txtExtencion1']);
$obj->setTelefono2Resp($parametros['txtTelefono2']);
$obj->setExtension2Resp($parametros['txtExtencion2']);
$obj->setCelularResp($parametros['txtCelular']);
$obj->setCorreoEResp($parametros['correoElectronico']);
$obj->setHorarioAtenInicResp($parametros['lstHR'] . "," . $parametros['lstMR'] . "," . $parametros['lstTA']);
$obj->setHorarioAtenFinResp($parametros['lstFinHR'] . "," . $parametros['lstFinMR'] . "," . $parametros['lstFinTR']);

$obj->setNombreAtenc($parametros['txtNombreAtencion']);
$obj->setTelefono1Atenc($parametros['txtTelefono1Atencion']);
$obj->setExtension1Atenc($parametros['txtExtencion1Atencion']);
$obj->setTelefono2Atenc($parametros['txtTelefono2Atencion']);
$obj->setExtension2Atenc($parametros['txtExtencion2Atencion']);
$obj->setCelularAtenc($parametros['txtCelularAtencion']);
$obj->setCorreoEAtenc($parametros['txtCorreoElectronico']);
$obj->setHorarioAtenInicAtenc($parametros['lstHA'] . "," . $parametros['lstMA'] . "," . $parametros['lstTA']);
$obj->setHorarioAtenFinAtenc($parametros['lstFinHA'] . "," . $parametros['lstFinMA'] . "," . $parametros['lstFinTA']);

$obj->setNoTicketCliente($parametros['txtNoTicketClienteGral']);
$obj->setNoTicketDistribuidor($parametros['txtNoTicketDistribucionGral']);
$obj->setDescripcionReporte(str_replace("'", "´", $parametros['descripcion']));
$obj->setObservacionAdicional(str_replace("'", "´", $parametros['observacion']));
$obj->setAreaAtencion($parametros['areaAtencionGral']);
$obj->setUbicacion($parametros["sltUbicacionToner"]);
$obj->setUbicacionEmp("ubicacionEMpresa");
//datos de auditoria
$obj->setActivo(1);
$obj->setUsuarioCreacion($_SESSION['user']);
$obj->setUsuarioUltimaModificacion($_SESSION['user']);
$obj->setPantalla("Alta ticket php 2");
$obj->setPermitirTicketSinSerie(true);

if (isset($parametros['idTicket']) && $parametros['idTicket'] == "") {//nuevo registró
    if ($obj->newRegistroCompleto()) {
        if (isset($parametros['ubicacionNoDomicilio']) && $parametros['ubicacionNoDomicilio'] != "") {//cambiar la ubicacion del equipo
            if ($obj->actualizarUbicacion($parametros["txtNoSerieE_" . $fila], $parametros['ubicacionNoDomicilio'])) {
                //echo "ubicacion modificadad";
            } else {
                //echo "Ubicacion sin modificar";
            }
        }
        $lecturaTicket->setClaveEspEquipo($noSerie);
        $lecturaTicket->setModeloEquipo($Modelo);
        $lecturaTicket->setContadorBN(0);
        if (isset($parametros['txtContadorColor_' . $fila])) {
            $lecturaTicket->setContadorColor($parametros['txtContadorColor_' . $fila]);
        } else {
            $lecturaTicket->setContadorColor("");
        }
        $lecturaTicket->setNivelNegro("");
        $lecturaTicket->setNivelCia("");
        $lecturaTicket->setNivelMagenta("");
        $lecturaTicket->setNivelAmarillo("");
        $lecturaTicket->setIdTicket($obj->getIdTicket());
        $lecturaTicket->setFechaA($parametros['txtfechaAnterior_' . $fila]);
        $lecturaTicket->setContadorBNA($parametros['txtContadorNegroAnterior_' . $fila]);
        if (isset($parametros['txtContadorColorAnterior_' . $fila])) {
            $lecturaTicket->setContadorColorA($parametros['txtContadorColorAnterior_' . $fila]);
        } else {
            $lecturaTicket->setContadorColorA("");
        }
        $lecturaTicket->setNivelNegroA("");
        $lecturaTicket->setNivelCiaA("");
        $lecturaTicket->setNivelMagentaA("");
        $lecturaTicket->setNivelAmarilloA("");
        $lecturaTicket->setComentario($parametros['comentario_' . $fila]);
        $lecturaTicket->setActivo(1);
        $lecturaTicket->setUsuarioCreacion($_SESSION['user']);
        $lecturaTicket->setUsuarioUltimaModificacion($_SESSION['user']);
        $lecturaTicket->setPantalla("Alta ticket php 2");
        $idLecturaEquipo = 0;
        if ($lecturaTicket->NewRegistro()) {
            $idLecturaEquipo = $lecturaTicket->getIdLectura();
        } else {
            
        }
        echo $obj->getIdTicket();
    } else {
        echo "<br/>Error: El ticket no se registró correctamente ";
    }
} else {//editar ticket
    $idTicket = $parametros['idTicket'];
    $pantalla = "Edita ticket";

    $obj->setIdTicket($idTicket);

    $lecturaTicket->setIdTicket($idTicket);
    $lecturaTicket->deleteRegitro();
    if ($obj->EditarEquipoTicket()) {
        if (isset($parametros['ubicacionNoDomicilio']) && $parametros['ubicacionNoDomicilio'] != "") {//cambiar la ubicacion del equipo
            if ($obj->actualizarUbicacion($parametros["txtNoSerieE_" . $fila], $parametros['ubicacionNoDomicilio'])) {
                //echo "ubicacion modificadad";
            }
        }
        $lecturaTicket->setClaveEspEquipo($noSerie);
        $lecturaTicket->setModeloEquipo($Modelo);
        $lecturaTicket->setContadorBN(0);
        if (isset($parametros['txtContadorColor_' . $fila])) {
            $lecturaTicket->setContadorColor($parametros['txtContadorColor_' . $fila]);
        } else {
            $lecturaTicket->setContadorColor("");
        }
        $lecturaTicket->setNivelNegro("");
        $lecturaTicket->setNivelCia("");
        $lecturaTicket->setNivelMagenta("");
        $lecturaTicket->setNivelAmarillo("");
        $lecturaTicket->setIdTicket($idTicket);
        $lecturaTicket->setFechaA($parametros['txtfechaAnterior_' . $fila]);
        $lecturaTicket->setContadorBNA($parametros['txtContadorNegroAnterior_' . $fila]);
        if (isset($parametros['txtContadorColorAnterior_' . $fila])) {
            $lecturaTicket->setContadorColorA($parametros['txtContadorColorAnterior_' . $fila]);
        } else {
            $lecturaTicket->setContadorColorA("");
        }
        $lecturaTicket->setNivelNegroA("");
        $lecturaTicket->setNivelCiaA("");
        $lecturaTicket->setNivelMagentaA("");
        $lecturaTicket->setNivelAmarilloA("");
        $lecturaTicket->setComentario($parametros['comentario_' . $fila]);
        $lecturaTicket->setActivo(1);
        $lecturaTicket->setUsuarioCreacion($_SESSION['user']);
        $lecturaTicket->setUsuarioUltimaModificacion($_SESSION['user']);
        $lecturaTicket->setPantalla($pantalla);
        $idLecturaEquipo = 0;
        if ($lecturaTicket->NewRegistro()) {
            echo $idTicket;
        } else {
            echo "<br/>Error: La lectura del equipo no se modificó correctamente";
        }
    } else {
        echo "<br/>Error: El ticket <b>" . $idTicket . "</b> no se modificó correctamente";
    }
}
?>