<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Inventario.class.php");
include_once("../Classes/Configuracion.class.php");
include_once("../Classes/CentroCosto.class.php");
include_once("../Classes/Equipo.class.php");
include_once("../Classes/ReporteLectura.class.php");

$inventario = new Inventario();
$CentroCosto = new CentroCosto();
$configuracion = new Configuracion();

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
    $CentroCosto->getRegistroById($parametros['cc']);
    $NoSerie = $parametros['serie'];    
    $ids = explode("-", $parametros['servicio']);
    $idKServicio = $ids[1];
    $idServicio = $ids[0];
    $IdAnexoClienteCC = $parametros['anexo'];
    $NoParte = $parametros['modelo'];
    $Ubicacion = $parametros['ubicacion'];
    $usuario = $_SESSION['user'];
    $Pantalla = "PHP Agregar Equipo";
    $ClaveCentroCosto = $parametros['cc'];
    $ClaveCliente = $parametros['cliente'];
    /*Verificamos que la serie coincida con el prefijo del modelo*/    
    if(!$configuracion->validarSerie($NoSerie, $NoParte)){
        return false;
    }
    
    if($configuracion->getRegistroByNoSerie($NoSerie)){
        echo "<br/>Error: este equipo ya tiene bitácora en el sistema.";
        return false;
    }
    
    if(!$configuracion->existeInventario($parametros['serie'])){
        $configuracion = new Configuracion();
        $configuracion->setNoSerie($NoSerie);
        $configuracion->setClaveCentroCosto($ClaveCentroCosto);
        $configuracion->setIdKServicio($idKServicio);
        $configuracion->setIdServicio($idServicio);
        $configuracion->setIdAnexoClienteCC($IdAnexoClienteCC);
        $configuracion->setNoParte($NoParte);
        $configuracion->setUbicacion($Ubicacion);
        $configuracion->setUsuarioCreacion($usuario);
        $configuracion->setUsuarioUltimaModificacion($usuario);
        $configuracion->setPantalla($Pantalla);   
        if(isset($parametros['global'])){//Si es un servicio global            
            $configuracion->setTipoServicio("0");
        }
        /*Inserta bitacora*/
        $catalogo = new Catalogo();
        $consulta = "INSERT INTO c_bitacora(id_solicitud,NoParte,NoSerie,NoGenesis,IP,Mac_address,IdTipoInventario,ClaveCentroCosto,IdAnexoClienteCC,IdServicio,IdAlmacen,
            Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES(null,'$NoParte','$NoSerie',null,null,null,null,'$ClaveCentroCosto',null,
                null,null,1,'$usuario',now(),'$usuario',now(),'$Pantalla')";          
        $idBitacora = $catalogo->insertarRegistro($consulta);
        if($idBitacora != null && $idBitacora != 0){
            if($configuracion->registrarInventario()){            
                /*Inserta movimiento*/
                $movimiento = new Movimiento();
                $movimiento->setNoSerie($NoSerie);
                $movimiento->setClave_cliente_nuevo($ClaveCliente);
                $movimiento->setClave_centro_costo_nuevo($ClaveCentroCosto);
                $movimiento->setPantalla("PHP Inventario");
                $movimiento->nuevoMovimientoaCliente($NoSerie, $ClaveCliente, $ClaveCentroCosto, "PHP Inventario");
                echo "<br/>El equipo $NoSerie de modelo $NoParte se registró correctamente";
                return true;
            }else{
                echo "<br/>Error al insertar el equipo $NoSerie";
                return false;
            }       
        }else{
            echo "<br/>Error: no se pudo crear la bitacora del equipo, verifique que el equipo no se encuentra ya registrado.";
        }
    }else{
        $result = $inventario->getDatosDeInventario($parametros['serie']);
        if(mysql_num_rows($result) > 0){
            if($rs = mysql_fetch_array($result)){
                echo "<br/>Error: Este equipo ya se encuentra registrado con el cliente ".$rs['NombreCliente']." en la localidad ".$rs['CentroCostoNombre'];
            }else{
                echo "<br/>Error: Este equipo ya se encuentra registrado con cliente";
            }
        }else{
            echo "<br/>Error: Este equipo ya se encuentra registrado con cliente";
        }
    }
}
?>