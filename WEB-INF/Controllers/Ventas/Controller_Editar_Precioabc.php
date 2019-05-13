<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/PrecioABC.class.php");
$precio = new PrecioABC();
if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
    /*if($parametros['tipo'] == "0"){
        $precio->setNoParteEquipo($parametros['modelo']);
        $precio->setNoParteComponente("");
    }else{
        $precio->setNoParteEquipo("");
        $precio->setNoParteComponente($parametros['modelo']);
    }*/
    $precio->setIdAlmacen($parametros['almacen']);
    $precio->setUsuarioCreacion($_SESSION['user']);
    $precio->setUsuarioUltimaModificacion($_SESSION['user']);
    $precio->setPantalla("PHP Controller_Nuevo_Precioabc.php");    
    $precio->setPrecio_A($parametros['precioa']);
    if (isset($parametros['preciob'])&& $parametros['preciob']!=""){
        $precio->setPrecio_B($parametros['preciob']);
    }
    if (isset($parametros['precioc'])&& $parametros['precioc']!=""){
        $precio->setPrecio_C($parametros['precioc']);
    }
    $precio->setId_precio_abc($parametros['idabc']);
    
    $almacen = " AND ISNULL(IdAlmacen) ";
    if($precio->getIdAlmacen() != NULL && $precio->getIdAlmacen()!= ""){
        $almacen = " AND IdAlmacen = ".$precio->getIdAlmacen();
    }
    
    $consulta = "SELECT Id_precio_abc FROM `c_precios_abc` "
            . "WHERE NoParteComponente = '".$precio->getNoParteComponente()."' AND NoParteEquipo = '".$precio->getNoParteEquipo()."' "
            . "$almacen AND Id_precio_abc <> ".$precio->getId_precio_abc().";";
    $catalogo = new Catalogo();
    $result = $catalogo->obtenerLista($consulta);
    if(mysql_num_rows($result) > 0){
        echo "Error: este modelo ya tiene un precio ABC asignado para este almacén";
        return false;
    }
    
    if($precio->editarabc()){
        echo "Se ha editado el precio correctamente";
    }else{
        echo "Error: No se pudo editar el precio, intente de nuevo por favor";
    }
}else{
    echo "Error: No se recibió bien la información";
}
?>
