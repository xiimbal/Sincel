<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
include_once("../../Classes/Movimiento.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/AlmacenEquipo.class.php");
include_once("../../Classes/CentroCosto.class.php");

$movimiento = new Movimiento();
$catalogo = new Catalogo();
$usuario = $_SESSION['user'];
$pantalla = "Cancelar Solicitud Controller Serie PHP";
/*$query = $catalogo->obtenerLista("SELECT * FROM c_inventarioequipo WHERE NoSerie='" . $_GET['serie'] . "'");*/
$query = $catalogo->obtenerLista("
            SELECT i.NoParteEquipo,
            (CASE WHEN !ISNULL(i.ClaveEspKServicioFAIM) THEN i.ClaveEspKServicioFAIM ELSE 'NULL' END) AS IdServicio,
            (CASE WHEN !ISNULL(i.IdKServicio) THEN i.IdKServicio ELSE 'NULL' END) AS IdKServicio,
            (CASE WHEN !ISNULL(i.IdKserviciogimgfa) THEN i.IdKserviciogimgfa ELSE 'NULL' END) AS IdKserviciogimgfa,
            (CASE WHEN !ISNULL(i.IdAnexoClienteCC) THEN i.IdAnexoClienteCC ELSE 'NULL' END) AS IdAnexoClienteCC
            FROM c_inventarioequipo AS i WHERE i.NoSerie='".$_GET['serie']."';");
if ($rs=  mysql_fetch_array($query)) {
    $id_reporte_historicos = $catalogo->insertarRegistro("INSERT INTO reportes_historicos(UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP cambie_equipo2.php');"); //Insertamos para el reporte de movimiento
    if ($id_reporte_historicos == 0 || !$id_reporte_historicos) {
        echo "<br/>Error: no se pudo generar el reporte de movimiento, intente de nuevo por favor.";
        return;
    }

    $consulta = "DELETE FROM `c_inventarioequipo` WHERE NoSerie = '" . $_GET['serie'] . "';";
    $result2 = $catalogo->obtenerLista($consulta);
    /* Insertamos en almacen */
    $obj = new AlmacenEquipo();
    $obj->setNoSerie($_GET['serie']);
    $obj->setIdAlmacen(9);
    $obj->setNoParteEquipo($rs['NoParteEquipo']);
    $obj->setUbicacion("");
    $hoy = getdate();
    $obj->setFechaIngreso($hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday']);
    $obj->setUsuarioCreacion($usuario);
    $obj->setUsuarioModificacion($usuario);
    $obj->setPantalla($pantalla);
    $localidad = new CentroCosto();
    $localidad->getRegistroById($_GET['loc']);
    if ($obj->newRegistro()) {//Despues de insertar en almacen, guardamos el movimiento y lo asociamos con el reporte.
        $movimiento->nuevoMovimientoClienteAlmacen($_GET['serie'], $localidad->getClaveCliente(), $localidad->getClaveCentroCosto(), $_GET['almacen'], "",
                "Cancelacion de serie solicitud", $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'], 1, $usuario, $pantalla,
                $rs['IdKServicio'],$rs['IdServicio'],$rs['IdAnexoClienteCC'],$rs['IdKserviciogimgfa']);
        $catalogo->insertarRegistro("INSERT INTO reportes_movimientos(id_reportes,id_movimientos)
                        VALUES(" . $id_reporte_historicos . "," . $movimiento->getId_movimientos() . ");");
        echo "La cancelación de la serie ".$_GET['serie']." fue exitosa";
    } else {
        echo "Error: No se pudo registrar el equipo en el almacen";
    }
} else {
    echo "<br/>Atención: el equipo se encuentra ya en almacén";
}