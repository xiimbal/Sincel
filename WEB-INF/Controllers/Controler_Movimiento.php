<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Movimiento.class.php");
$movimiento = new Movimiento();
include_once("../Classes/Lectura.class.php");
$lectura = new Lectura();

$lectura->setNoSerie($_POST['noSerie']);
$lectura->setContadorBNPaginas($_POST['contadorBN']);
$lectura->setContadorColorPaginas("null");
if(isset($_POST['contadorColor']))
{  
    $lectura->setContadorColorPaginas($_POST['contadorColor']);
}
$lectura->setContadorBNML("null");
$lectura->setContadorColorML("null");
$lectura->setNivelTonAmarillo("null");
$lectura->setNivelTonCian("null");
$lectura->setNivelTonMagenta("null");
$lectura->setActivo("1");
$lectura->setPantalla("Recepción de equipo");
$lectura->setLecturaCorte("0");

$movimiento->setId_movimientos($_POST['idMovimiento']);
$movimiento->setNoSerie($_POST['noSerie']);
$movimiento->setUsuarioCreacion($_SESSION['user']);
$movimiento->setUsuarioUltimaModificacion($_SESSION['user']);
$movimiento->setPantalla("Recepción de equipo");

if (isset($_POST['estatus']) && $_POST['estatus'] == "0") {
    $almacen = $_POST["almacen"];
} else {
    $almacen = "9"; //id almacen predeterminado
}
if($lectura->newRegistro())
{
	$idLectura = $lectura->getIdLectura();
    if ($movimiento->getId_movimientos() == 0 || $movimiento->editMovimientoEquipo($_POST['comentario'], $almacen, $idLectura)) {
        if ($almacen != "0") {
            if ($movimiento->editEquipoEnAlmacen($almacen, $_POST['almacenAnterio'], $_POST['noSerie']))
                echo "La recepción de equipo se realizó correctamente";
        }else {
            echo "La recepción de equipo se realizó correctamente";
        }
    } else {
        echo "Error: El Movimiento no se realizó correctamente";
    }
}else{
    echo "Error: No se ha insertado la lectura";
}
?>
