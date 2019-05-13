<?php

include_once("WEB-INF/Classes/Catalogo.class.php");
include_once("WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("WEB-INF/Classes/Usuario.class.php");
include_once("WEB-INF/Classes/Cliente.class.php");

if(isset($_GET['uguid'])){
    $empresa = $_GET['uguid'];
}else{
    $empresa = "1";
}

echo "<br/>Procesando copiado de empresa $empresa";

$catalogo = new Catalogo();
$catalogo->setEmpresa($empresa);
$catalogoFacturacion = new CatalogoFacturacion();
$catalogoFacturacion->setEmpresa($empresa);

/*******************************    Clientes *******************************/
$tabla = "c_cliente";
$catalogoFacturacion->obtenerLista("DELETE FROM `$tabla`;");
$result = $catalogo->obtenerLista("SELECT * FROM `$tabla`;");
while($rs = mysql_fetch_array($result)){
    $consulta = "INSERT INTO `$tabla` VALUES(";
    for($i=0;$i<mysql_num_fields($result);$i++){
        $valor = "";
        if(!isset($rs[$i])){
            $valor = "null";
        }else{
            $valor = "'".$rs[$i]."'";
        }
        if($i == mysql_num_fields($result)-1){
            $consulta.= $valor;
            break;
        }
        $consulta.= $valor.",";
    }    
    $consulta .= ")";
    //echo "<br/>".$consulta;
    $catalogoFacturacion->insertarRegistro($consulta);    
}
echo "<br/>Se actualizó la tabla $tabla de facturación";
/*******************************    Usuario *******************************/
$tabla = "c_usuario";
$catalogoFacturacion->obtenerLista("DELETE FROM `$tabla`;");
$result = $catalogo->obtenerLista("SELECT * FROM `$tabla`;");
while($rs = mysql_fetch_array($result)){
    $consulta = "INSERT INTO `$tabla` VALUES(";
    for($i=0;$i<mysql_num_fields($result);$i++){
        $valor = "";
        if(!isset($rs[$i])){
            $valor = "null";
        }else{
            $valor = "'".$rs[$i]."'";
        }
        if($i == mysql_num_fields($result)-1){
            $consulta.= $valor;
            break;
        }
        $consulta.= $valor.",";
    }    
    $consulta .= ")";
    //echo "<br/>".$consulta;
    $catalogoFacturacion->insertarRegistro($consulta);    
}
echo "<br/>Se actualizó la tabla $tabla de facturación";
?>
