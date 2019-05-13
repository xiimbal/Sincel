<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../../Classes/Orden_Compra.class.php");
include_once("../../Classes/Detalle_Orden_Compra.class.php");
include_once("../../Classes/DomicilioAlmacen.class.php");
include_once("../../Classes/AutorizarEspecial.class.php");
include_once("../../Classes/DetalleEspecial.class.php");
include_once("../../Classes/Proveedor.class.php");
include_once("../../Classes/Contacto.class.php");

if (isset($_POST['formProductos'])) {   //Registrar orden de compra y su detalle
    $parametros = "";
    parse_str($_POST['formProductos'], $parametros);
    
    $ordenCompra = new Orden_Compra();
    $ordenCompra->setFacturaEmisor($parametros['proveedor']);
    $ordenCompra->setFacturaRecptor($_SESSION['idEmpresa']);
    $ordenCompra->setFechaOC(date("Y-m-d"));
    $ordenCompra->setEstatus("NULL");
    $ordenCompra->setActivo(1);
    $ordenCompra->setUsuarioCreacion($_SESSION['user']);
    $ordenCompra->setUsuarioModificacion($_SESSION['user']);
    $ordenCompra->setPantalla("Crear camión");
    if(!$ordenCompra->newRegistro()){
        echo "Error: No se pudo registrar la orden de compra";
        return;
    }
    
    $numProductos = $_POST['contadorProductos'];
    $ordenCompraDetalle = new Detalle_Orden_Compra();
    $ordenCompraDetalle->setIdOrdenCompra($ordenCompra->getIdOrdenCompra());
    $ordenCompraDetalle->setUsuarioCreacion($_SESSION['user']);
    $ordenCompraDetalle->setUsuarioModificacion($_SESSION['user']);
    $i = -1;
    $cont = 0;
    
    while($cont < $numProductos){
        $i++;
        if(!isset($parametros['cantidad_'.$i]) || empty($parametros['cantidad_'.$i])){
            continue;
        }
        $ordenCompraDetalle->setCantidad($parametros['cantidad_'.$i]);
        $ordenCompraDetalle->setEmpaque($parametros['empaque_'.$i]);
        $ordenCompraDetalle->setNoParteComponente($parametros['producto_'.$i]);
        $ordenCompraDetalle->setKg($parametros['kg_'.$i]);
        $ordenCompraDetalle->setPrecioUnitario($parametros['precio_'.$i]);
        $ordenCompraDetalle->setCostoTotal($parametros['total_'.$i]);
        if(!$ordenCompraDetalle->newRegistroCompnente()){
            echo "Error: No se pudo registrar el detalle de la orden de compra";
            return;
        }
        $cont++;
    }
    echo $ordenCompra->getIdOrdenCompra();   
}else if(isset($_POST['formCamion'])){
        
    $parametros = "";
    parse_str($_POST['formCamion'], $parametros);
    
    $contadorOc = $_POST['contadorOc'];
    $OCs = array();
    $cont = -1;
    $i = 0;
    
    $contacto = new Contacto();
    $contacto->getRegstroByID($parametros['chofer']);
    $telefono_chofer = $contacto->getTelefono();
    
    while($i < $contadorOc){
        $cont++;
        if(!isset($parametros['oc_'.$cont]) || empty($parametros['oc_'.$cont])){
            continue;
        }
        $OCs[$parametros['posicion_'.$cont]] = $parametros['oc_'.$cont];
        $i++;
    }
    ksort($OCs);
    $arrayOC = implode(",",$OCs);
    $destino = "";
    $calle_des = "";
    $exterior_des = "";
    $interior_des = "";
    $colonia_des = "";
    $ciudad_des = "";
    $delegacion_des = "";
    $cp_des = "";
    $localidad_des = "";
    $estado_des = "";
    $latitud_des = "";
    $longitud_des = "";
    $comentario_des = "";
    
    if(isset($parametros['almacen']) && !empty($parametros['almacem'])){
        $domicilioAlmacen = new DomicilioAlmacen();
        $domicilioAlmacen->setIdAlmacen($parametros['almacen']);
        $domicilioAlmacen->getRegistroByIdAlmacen();
        $destino = $domicilioAlmacen->getNombreAlmacen();
        $calle_des = $domicilioAlmacen->getCalle();
        $exterior_des = $domicilioAlmacen->getExterior();
        $interior_des = $domicilioAlmacen->getInterior();
        $colonia_des = $domicilioAlmacen->getColonia();
        $ciudad_des = $domicilioAlmacen->getCiudad();
        $delegacion_des = $domicilioAlmacen->getDelegacion();
        $cp_des = $domicilioAlmacen->getCp();
        $estado_des = $domicilioAlmacen->getEstado();
        $latitud_des = $domicilioAlmacen->getLatitud();
        $longitud_des = $domicilioAlmacen->getLongitud();
    }else{
        $destino = $parametros['destino'];
        $calle_des = $parametros['calle'];
        $exterior_des = $parametros['exterior'];
        $interior_des = $parametros['interior'];
        $colonia_des = $parametros['colonia'];
        $ciudad_des = $parametros['ciudad'];
        $delegacion_des = $parametros['delegacion'];
        $cp_des = $parametros['cp'];
        $estado_des = $parametros['estado'];
        $latitud_des = $parametros['latitud'];
        $longitud_des = $parametros['longitud'];
        $comentario_des = $parametros['comentarios'];
    }
    
    $especial = new AutorizarEspecial();
    $especial->setIdEmpleado($parametros['chofer']);
    $especial->setDestino($destino); 

    $especial->setActivo(1);
    $especial->setUsuarioCreacion($_SESSION['user']);
    $especial->setUsuarioUltimaModificacion($_SESSION['user']);
    $especial->setPantalla('WS AgregarViajesEspeciales');

    $arrayOC = split(",", $arrayOC);
    $domicilioProveedor = new Proveedor();

    //La dirección origen será la del proveedor de la primera OC
    $domicilioProveedor->getDomicilioByOc($arrayOC[0]);

    $especial->setCalle_or($domicilioProveedor->getCalle());
    $especial->setExterior_or($domicilioProveedor->getNumExterior());
    $especial->setInterior_or($domicilioProveedor->getNumInterior());
    $especial->setColonia_or($domicilioProveedor->getColonia());
    $especial->setCiudad_or($domicilioProveedor->getCiudad());
    $especial->setDelegacion_or($domicilioProveedor->getDelegacion()); 
    $especial->setCp_or($domicilioProveedor->getCp());
    $especial->setLocalidad_or("");
    $especial->setEstado_or($domicilioProveedor->getEstado());
    $especial->setLatitud_or(NULL);
    $especial->setLongitud_or(NULL); 
    $especial->setComentario_or(""); 
    if(!empty($telefono_chofer)){
        $especial->setComentario_or($especial->getComentario_or(). "Teléfono chofer: ".$telefono_chofer);
    }
    //Quitaremos del array el primer elemento ya que ya esta como dirección origen
    unset($arrayOC[0]);

    $especial->setCalle_des($calle_des);
    $especial->setExterior_des($exterior_des);
    $especial->setInterior_des($interior_des); 
    $especial->setColonia_des($colonia_des);
    $especial->setCiudad_des($ciudad_des);
    $especial->setDelegacion_des($delegacion_des);
    $especial->setCp_des($cp_des); 
    $especial->setLocalidad_des($localidad_des); 
    $especial->setEstado_des($estado_des);
    $especial->setLatitud_des($latitud_des);
    $especial->setLongitud_des($longitud_des);
    $especial->setComentario_des($comentario_des);

    //return $especial->newRegistro();
    if ($especial->newRegistro()) {
        $idEspecial = $especial->getIdEspecial();

        //Agregaremos los detalles de especial
        $detalleEspecial = new DetalleEspecial();
        $detalleEspecial->setIdEspecial($idEspecial);
        $detalleEspecial->setPesoBruto($parametros['pesoBruto']);
        $detalleEspecial->setTara($parametros['tara']);
        $detalleEspecial->setNeto($parametros['neto']);
        $detalleEspecial->setCostoTotal($parametros['costoTotal']);
        $detalleEspecial->setActivo(1);
        $detalleEspecial->setUsuarioCreacion($_SESSION['user']);
        $detalleEspecial->setUsuarioUltimaModificacion($_SESSION['user']);
        $detalleEspecial->setPantalla('WS AgregarViajesEspeciales');
        if(!$detalleEspecial->newRegistro()){
            return -13;
        }

        //Pasaremos a crear escalas por cada OC sobrante
        if(count($arrayOC) > 0){
            $escalaEspecial = new AutorizarEspecial();
            $escalaEspecial->setActivo(1);
            $escalaEspecial->setUsuarioCreacion($_SESSION['user']);
            $escalaEspecial->setUsuarioUltimaModificacion($_SESSION['user']);
            $escalaEspecial->setPantalla('WS AgregarViajesEspeciales');
            $escalaEspecial->setIdEspecial($idEspecial);
            foreach ($arrayOC as $idOC) {
                $domicilioProveedor->getDomicilioByOc($idOC);
                $escalaEspecial->setCalle_des($domicilioProveedor->getCalle());
                $escalaEspecial->setExterior_des($domicilioProveedor->getNumExterior());
                $escalaEspecial->setInterior_des($domicilioProveedor->getNumInterior());
                $escalaEspecial->setColonia_des($domicilioProveedor->getColonia());
                $escalaEspecial->setCiudad_des($domicilioProveedor->getCiudad());
                $escalaEspecial->setDelegacion_des($domicilioProveedor->getDelegacion()); 
                $escalaEspecial->setCp_des($domicilioProveedor->getCp());
                $escalaEspecial->setLocalidad_des("");
                $escalaEspecial->setEstado_des($domicilioProveedor->getEstado());
                $escalaEspecial->setLatitud_des(NULL);
                $escalaEspecial->setLongitud_des(NULL); 
                if(!$escalaEspecial->newRegistroDetalle()){
                    return "Error: No se pudo registrar la escala";
                }
            }
        }

    }else{
        echo "Error: No se ha registrado el viaje especial";
    }
    echo "Se ha registrado con éxito el camión";
}else if(isset($_POST['id'])){
    $ordenCompra = new Orden_Compra();
    $ordenCompra->setIdOrdenCompra($_POST['id']);
    $ordenCompra->deleteRegistroDetalle();
    $ordenCompra->deleteRegistroOC();
    
    echo "Se ha eliminado la OC";
}


?>

