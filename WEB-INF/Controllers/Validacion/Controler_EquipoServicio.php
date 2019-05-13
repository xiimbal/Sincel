<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../../Classes/Catalogo.class.php");

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$catalogo = new Catalogo();

$id = $parametros['tipo_servicio'];
$IdServicio = explode("..", $id);
$prefijo_mayor = $IdServicio[0];
$prefijo_menor = strtolower($prefijo_mayor);


$consulta = "SELECT IdAnexoClienteCC, IdServicio$prefijo_mayor FROM k_servicio$prefijo_menor WHERE IdKServicio$prefijo_mayor = ".$IdServicio[1].";";
//echo $consulta;
$result = $catalogo->obtenerLista($consulta);
while($rs = mysql_fetch_array($result)){
    $consulta = "UPDATE c_inventarioequipo SET IdKServicio = ".$IdServicio[1].", ClaveEspKServicioFAIM = ".$rs['IdServicio'.$prefijo_mayor]." "
            . "WHERE NoSerie = '".$parametros['NoSerie']."';";
    //echo $consulta;
    $query = $catalogo->obtenerLista($consulta);
    if($query == "1"){
        echo "<br/>El equipo fue asignado al nuevo servicio correctamente";
    }else{
        echo "<br/>Error: el equipo no pudo ser asignado al nuevo servicio";
    }
}
?>