<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Empresa.class.php");
include_once("../../Classes/EmpresaProductoSAT.class.php");
include_once("../../Classes/ClaveProdServ.class.php");
include_once("../../Classes/UnidadMedidaSAT.class.php");
$obj = new Empresa();
$empresaProducto = new EmpresaProductoSAT();
$unidad = new UnidadMedidaSAT();
$claveProdServ = new ClaveProdServ();
$catalogo = new Catalogo();

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setId($_GET['id']);
    if ($obj->deletebyID()) {
        echo "La empresa se eliminó correctamente";
    } else {
        echo "La empresa no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    
    $arrayProductos = array();
    $mensaje = "";
    
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setRazonSocial($parametros['RazonSocial']);
    $obj->setCalle($parametros['CalleF']);
    $obj->setNoExterior($parametros['NoExteriorF']);
    $obj->setNoInterior($parametros['NoInteriorF']);
    $obj->setColonia($parametros['ColoniaF']);
    $obj->setEstado($parametros['EstadoF']);
    $obj->setDelegacion($parametros['DelegacionF']);
    $obj->setCP($parametros['CPF']);
    
    $resultado = $catalogo->obtenerLista("SELECT * FROM c_codigopostal WHERE ClaveCodigoPostal = ".$obj->getCP());
    if(mysql_num_rows($resultado) < 1){
        echo "Error: El código postal no se encuentra en el catálogo de códigos postales validos del SAT";
        return;
    }
    
    $obj->setRFC($parametros['RFCD']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setFechaCreacion("NOW()");
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setFechaModificacion("NOW()");
    $obj->setPantalla("PHP Controller_Empresa.php");
    $obj->setId_Cfdi($parametros['cfdi']);
    $obj->setId_pac($parametros['pac']);
    $obj->setRegimenFiscal($parametros['regimenfiscal']);
    if (isset($parametros['chkActivo']) && $parametros['chkActivo'] != "") {
        $obj->setActivo("1");
    } else {
        $obj->setActivo("0");
    }
    if(isset($parametros['tickets']) && $parametros['tickets'] == "on"){
        $obj->setFacturaTickets(1);
    }else{
        $obj->setFacturaTickets(0);
    }
    if(isset($parametros['cfdi33']) && $parametros['cfdi33'] == "on"){
        $obj->setCfdi33(1);
    }else{
        $obj->setCfdi33(0);
    }
    
    $obj->setIdSerie($parametros['serie']);
    if (isset($parametros['id']) && $parametros['id'] != "") {
        $obj->setId($parametros['id']);
        foreach ($_FILES as $key) {
            $ruta = "../../../LOGOS/";
            if ($key['error'] == UPLOAD_ERR_OK) {//Verificamos si se subio correctamente
                $nombre = $key['name']; //Obtenemos el nombre del archivo
                $nomb = explode(".", $nombre);
                $temporal = $key['tmp_name']; //Obtenemos el nombre del archivo temporal
                $nombre_concatenado = "";
                while(file_exists($ruta . $parametros['id'] . $nombre_concatenado . "_empresa_logo." . $nomb[1])){
                    $nombre_concatenado .= "(1)";
                }                                
                move_uploaded_file($temporal, $ruta . $parametros['id'] . $nombre_concatenado."_empresa_logo." . $nomb[1]); //Movemos el archivo temporal a la ruta especificada
                $obj->setArchivoLogo($parametros['id'] . $nombre_concatenado . "_empresa_logo." . $nomb[1]);
                $obj->actualizarLogo();
            } else {
                echo $key['error']; //Si no se cargo mostramos el error
            }
        }
        if ($obj->editRegistro()) {
            $mensaje.= "La empresa se actualizó correctamente";
            $empresaProducto->setIdDatosFacturacionEmpresa($obj->getId());
            $arrayProductos = $empresaProducto->getDetallesByEmpresa();
        } else {
            echo "Error: No se pudo actualizar la empresa, intente más tarde o contacte al administrador";
            return;
        }
    } else {
        if ($obj->nuevoRegistro()) {
            foreach ($_FILES as $key) {
                $ruta = "../../../LOGOS/";
                if ($key['error'] == UPLOAD_ERR_OK) {//Verificamos si se subio correctamente
                    $nombre = $key['name']; //Obtenemos el nombre del archivo
                    $nomb = explode(".", $nombre);
                    $temporal = $key['tmp_name']; //Obtenemos el nombre del archivo temporal
                    $nombre_concatenado = "";
                    while(file_exists($ruta . $obj->getId() . $nombre_concatenado . "_empresa_logo." . $nomb[1])){
                        $nombre_concatenado .= "(1)";
                    }
                    
                    move_uploaded_file($temporal, $ruta .$obj->getId(). $nombre_concatenado . "_empresa_logo." . $nomb[1]); //Movemos el archivo temporal a la ruta especificada
                    $obj->setArchivoLogo($obj->getId(). $nombre_concatenado . "_empresa_logo." . $nomb[1]);
                    $obj->actualizarLogo();
                } else {
                    echo $key['error']; //Si no se cargo mostramos el error
                    return;
                }
            }
            $mensaje.= "La empresa se registró correctamente";
        } else {
            echo "Error: No se pudo registrar la empresa, intente más tarde o contacte al administrador";
            return;
        }
    }
    
    $numProductos = (int)$_POST['contadorProductos'];
    $cont = 0;
    $aux = 0;
    $empresaProducto->setIdDatosFacturacionEmpresa($obj->getId());
    $empresaProducto->setUsuarioCreacion($obj->getUsuarioCreacion());
    $empresaProducto->setUsuarioUltimaModificacion($obj->getUsuarioModificacion());
    $empresaProducto->setPantalla($obj->getPantalla());
    
    $idEmpresa = $obj->getId()."&&&&";
    while($numProductos > $cont){
        if(!isset($parametros['producto_'.$aux])){
            $aux+=1;
            continue;
        }
        
        $unidadMedida = $parametros['unidadmedida_'.$aux];
        $clave = split(" ", $unidadMedida);
        $unidad->setClaveUnidad($clave[0]);
        if(!$unidad->getRegistroByClaveUnidad()){
            echo "$idEmpresa Error: El producto con unidad de medida $unidadMedida no se registró porque no se encontró la unidad de medida. ";
            $idEmpresa = "";
            $aux += 1;
            $cont += 1;
            continue;
        }
        $productoSAT = $parametros['producto_'.$aux];
        $clave = split(" ", $productoSAT);
        $claveProdServ->setClaveProdServ($clave[0]);
        if(!$claveProdServ->getIdByClaveProdServ()){
            echo "$idEmpresa Error: El producto $productoSAT no se registró porque no se encontró en el catálogo.";
            $idEmpresa = "";
            $mensaje = "";
            $aux += 1;
            $cont += 1;
            continue;
        }
        
        $empresaProducto->setIdUnidadMedida($unidad->getIdUnidadMedida());
        $empresaProducto->setIdClaveProdServ($claveProdServ->getIdProdServ());
        if(isset($parametros['id_'.$aux]) && !empty($parametros['id_'.$aux])){
            $empresaProducto->setIdEmpresaProductoSAT($parametros['id_'.$aux]);
            $empresaProducto->editRegistro();
            if(in_array($empresaProducto->getIdEmpresaProductoSAT(), $arrayProductos)){
                $arrayProductos = array_diff($arrayProductos, array($empresaProducto->getIdEmpresaProductoSAT()));
            }
        }else{
            $empresaProducto->newRegistro();
        }
        $aux += 1;
        $cont += 1;
    }
    
    if(count($arrayProductos) > 0){
        foreach($arrayProductos as $id){
            $empresaProducto->setIdEmpresaProductoSAT($id);
            $empresaProducto->deleteRegistro();
        }
    }
    
    echo $mensaje;
    
}
?>
