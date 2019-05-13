<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../../Classes/PeriodoSinFacturar.class.php");
include_once("../../Classes/Configuracion.class.php");
include_once("../../Classes/Cliente.class.php");
include_once("../../Classes/Catalogo.class.php");

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$periodo = new PeriodoSinFacturar();
if($periodo->marcarFacturado($parametros['periodo'], $parametros['bitacora'], $parametros['cliente'], $parametros['comentario'], $_SESSION['user'], "Controler_PeriodoSinFacturar")){
    $configuracion = new Configuracion();
    $cliente = new Cliente();
    $periodo->getRegistroById($parametros['periodo']);
    $configuracion->getRegistroById($parametros['bitacora']);
    $cliente->getRegistroById($parametros['cliente']);
    $catalogo = new Catalogo();
    echo "El registro del equipo <b>".$configuracion->getNoSerie()."</b> (".substr($catalogo->formatoFechaReportes($periodo->getPeriodo()),5).") del cliente <b>".$cliente->getNombreRazonSocial()."</b> fue marcado como facturado";
}else{
    echo "Error: El registro no pudo ser marcado como facturado";
}
?>