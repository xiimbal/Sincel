<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Configuracion.class.php");
include_once("../Classes/Equipo.class.php");
include_once("../Classes/Lectura.class.php");
include_once("../Classes/Movimiento.class.php");
include_once("../Classes/ReporteLectura.class.php");
$obj = new Configuracion();
$pantalla = "Configuración de equipo";
$tlectura = 0; //Para saber si se cobrarán las hojas en demo

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setId_bitacora($_GET['id']);
    if ($obj->eliminarRegistro()) {
        echo "La bitacora se eliminó correctamente";
    } else {
        echo "La bitacora no se pudo eliminar.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    if($parametros['sol_equipo']!=""){
        $obj->setId_solicitud($parametros['sol_equipo']);
    }else{
        $obj->setId_solicitud("null");
    }
    $obj->setNoParte($parametros['no_parte']);
    $obj->setNoSerie($parametros['serie']);
    $obj->setNoSerieOriginal($parametros['serie_original']);
    $obj->setNoGenesis($parametros['serie_genesis']);
    $obj->setIP($parametros['ip']);
    $obj->setMac($parametros['mac']);
    $obj->setIdTipoInventario($parametros['tipo_inventario']);
    
    /*Verificamos que la serie coincida con el prefijo del modelo*/
    if(!$obj->validarSerie($obj->getNoSerie(), $obj->getNoParte())){
        return false;
    }
    
    if(isset($parametros['equipo_demo']) && $parametros['equipo_demo'] == "1"){
        $obj->setDemo(1);
    }else{
        $obj->setDemo(0);
        //Primero registramos la nueva lectura
        $lectura = new Lectura();
        $lectura->setNoSerie($parametros['serie']);
        $lectura->setContadorBNPaginas($parametros['contadorBN']);
        $lectura->setUsuarioCreacion($_SESSION['user']);
        $lectura->setPantalla($pantalla);
        $lectura->setActivo(1);
        $lectura->setContadorBNML("null");
        if(isset($parametros['contadorColor'])){
            $lectura->setContadorColorPaginas($parametros['contadorColor']);
        }else{
            $lectura->setContadorColorPaginas("null");
        }
        $lectura->setContadorColorML("null");
        $lectura->setNivelTonAmarillo("null");
        $lectura->setNivelTonCian("null");
        $lectura->setNivelTonMagenta("null");
        $lectura->setLecturaCorte("null");
        $lectura->newRegistro();
        //Vemos que lectura se tomara en cuenta
        if(isset($parametros['cobrar_hojas']) && $parametros['cobrar_hojas'] == "1"){
            $tlectura = 1;
        }
        //Vamos a generar un nuevo movimiento de equipo
        $moviEqui = new Movimiento();
        $moviEqui->actualizarLecturaCliente($parametros['serie'], $parametros['clave_cliente'], $parametros['clave_cc'], $lectura->getIdLectura(), $tlectura);
        
    }
    
    if(isset($parametros['radio_ubicacion']) && $parametros['radio_ubicacion'] == "cliente"){
        $obj->setClaveCentroCosto($parametros['localidad']);    
        $obj->setIdAnexoClienteCC($parametros['anexo']);
        $datos_servicio = explode("-", $parametros['servicio']);
        if(isset($datos_servicio[0])){
            $obj->setIdServicio($datos_servicio[0]);
        }
        if(isset($datos_servicio[1])){
            $obj->setIdKServicio($datos_servicio[1]);
        }
        $obj->setIdAlmacen("null");
    }else{
        $obj->setClaveCentroCosto("null");    
        $obj->setIdAnexoClienteCC("null");
        $obj->setIdServicio("null");
        $obj->setIdAlmacen($parametros['almacen_equipo']);
    }
    if(isset($parametros['tipo_servicio'])){
        $obj->setTipoServicio($parametros['tipo_servicio']);
    }else{
        $obj->setTipoServicio('1');/*Sino se especifica el parametro, por default manejamos particular*/
    }
    
    $obj->setActivo("1");
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setPantalla($pantalla);
    $obj->setUbicacion($parametros['ubicacion']);
    
    if (isset($parametros['id_bitacora']) && $parametros['id_bitacora'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if($parametros['solo_serie'] == "false"){            
            if ($obj->newRegistro()) {
                $obj->nuevosDetalles($parametros);
                echo "La bitacora <b>" . $obj->getId_bitacora() . "</b> se registró correctamente";
            } else {
                echo "Error: La bitacora con No. de Serie <b>" . $obj->getNoSerie() . "</b> ya se encuentra registrada";
            }
        }else{
            if ($obj->newRegistroAlmacen()) {
                $obj->nuevosDetalles($parametros);
                echo "La bitacora <b>" . $obj->getId_bitacora() . "</b> se registró correctamente";
            } else {
                echo "Error: La bitacora con No. de Serie <b>" . $obj->getNoSerie() . "</b> ya se encuentra registrada";
            }
        }
    } else {/* Modificar */
        $obj->setId_bitacora($parametros['id_bitacora']);
        //echo "Error: editar";
        if ($obj->editarRegistro()) {
            $obj->eliminarDetalles();
            $obj->nuevosDetalles($parametros);
            echo "La bitacora <b>" . $obj->getId_bitacora() . "</b> se modificó correctamente";
        } else {
            echo "<br/>Error: No se pudo actualizar la bitacora <b>" . $obj->getId_bitacora() . "</b>";
        }
    }
}
?>
