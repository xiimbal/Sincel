<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
include_once("../../Classes/Movimiento.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/CentroCosto.class.php");
include_once("../../Classes/AlmacenEquipo.class.php");
include_once("../../Classes/Inventario.class.php");
include_once("../../Classes/Configuracion.class.php");
include_once("../../Classes/Parametros.class.php");


$bitacora = new Configuracion();
$localidad = new CentroCosto();
$movimiento = new Movimiento();
$inventario = new Inventario();
$catalogo = new Catalogo();
$usuario = "Autorizacion";
$pantalla = "Acepta Retiro PHP";
$result = $catalogo->obtenerLista("SELECT IdSolicitudRetiro, sr.IdBitacora AS IdBitacora, ClaveLocalidad, sr.IdAlmacen AS IdAlmacen, srg.Contestado, Clave, FechaReporte, TipoReporte, 
    sr.UsuarioCreacion, srg.Causa_Movimiento
    FROM `c_solicitudretiro` AS sr
    INNER JOIN c_solictudretirogeneral AS srg ON srg.IdSolicitudRetiroGeneral = sr.IdSolicitudRetiroGeneral
    INNER JOIN c_bitacora AS b ON sr.IdBitacora=b.id_bitacora
    WHERE b.NoSerie ='" . $_POST['serie'] . "' AND sr.PendienteRetiro=1 AND srg.Contestado=1 AND srg.Aceptada=1;");
if (mysql_num_rows($result) > 0) {    
    $id_reporte_historicos = $catalogo->insertarRegistro("INSERT INTO reportes_historicos(UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP cambie_equipo2.php');"); //Insertamos para el reporte de movimiento
    if ($id_reporte_historicos == 0 || !$id_reporte_historicos) {
        echo "<br/>Error: no se pudo generar el reporte de movimiento, intente de nuevo por favor.";
        return;
    }
    $usuario = $_SESSION['user'];
    /* Verirficamos si la bitacora del equipo existe */
    if ($rs = mysql_fetch_array($result)) {
        if ($bitacora->getRegistroById($rs['IdBitacora'])) {
            $result2 = $inventario->getDatosDeInventario($bitacora->getNoSerie()); //Obtenemos los datos de inventario del equipo
            if (mysql_num_rows($result2) > 0) {//Si todavía esta en c_inventario
                $localidad->getRegistroById($rs['ClaveLocalidad']);
                $IdKServicio = "NULL";
                $IdServicio = "NULL";
                $IdAnexoClienteCC = "NULL";
                $idKServiciogimgfa = "NULL";
                while ($rs2 = mysql_fetch_array($result2)) {
                    if(isset($rs2['IdKServicio']) && !empty($rs2['IdKServicio'])){
                        $IdKServicio = $rs2['IdKServicio'];
                    }
                    
                    if(isset($rs2['ClaveEspKServicioFAIM']) && !empty($rs2['ClaveEspKServicioFAIM'])){
                        $IdServicio = $rs2['ClaveEspKServicioFAIM'];
                    }
                    
                    if(isset($rs2['IdAnexoClienteCC']) && !empty($rs2['IdAnexoClienteCC'])){
                        $IdAnexoClienteCC = $rs2['IdAnexoClienteCC'];
                    }
                    
                    if(isset($rs2['IdKserviciogimgfa']) && !empty($rs2['IdKserviciogimgfa'])){
                        $idKServiciogimgfa = $rs2['IdKserviciogimgfa'];
                    }
                    
                    if ($rs2['ClaveCentroCosto'] != $rs['ClaveLocalidad']) {//Si el equipo ya no esta en la misma localidad                        
                        echo "<br/>Atención: El equipo <b>" . $bitacora->getNoSerie() . "</b> ya no se encuentra en la localidad " . $localidad->getNombre() . ".";
                        continue 2;
                    }
                }
                /* Despues de las validaciones, borramos el equipo de c_inventario e insertamos ya el equipo en almacen */
                $consulta = "DELETE FROM `c_inventarioequipo` WHERE NoSerie = '" . $bitacora->getNoSerie() . "';";
                $result2 = $catalogo->obtenerLista($consulta);
                /* Insertamos en almacen */
                $obj = new AlmacenEquipo();
                $obj->setNoSerie($bitacora->getNoSerie());
                $obj->setIdAlmacen(9);
                $obj->setNoParteEquipo($bitacora->getNoParte());
                $obj->setUbicacion("");
                $hoy = getdate();
                $obj->setFechaIngreso($hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday']);
                $obj->setUsuarioCreacion($usuario);
                $obj->setUsuarioModificacion($usuario);
                $obj->setPantalla($pantalla);
                if ($obj->newRegistro()) {//Despues de insertar en almacen, guardamos el movimiento y lo asociamos con el reporte.
                    $movimiento->nuevoMovimientoClienteAlmacen($bitacora->getNoSerie(), $localidad->getClaveCliente(), $localidad->getClaveCentroCosto(), 
                            $rs['IdAlmacen'], $rs['TipoReporte'], $rs['Causa_Movimiento'], $rs['FechaReporte'], 1, $usuario, $pantalla,
                            $IdKServicio, $IdServicio, $IdAnexoClienteCC, $idKServiciogimgfa);
                    $catalogo->insertarRegistro("INSERT INTO reportes_movimientos(id_reportes,id_movimientos)
                        VALUES(" . $id_reporte_historicos . "," . $movimiento->getId_movimientos() . ");");
                } else {
                    echo "Error: No se pudo registrar el equipo en el almacen";
                }
            } else {
                echo "<br/>Atención: El equipo <b>" . $bitacora->getNoSerie() . "</b> ya no se encuentra con cliente.";
            }
        } else {
            echo "<br/>Error: no se pudo encontrar la bitacora con el folio <b>" . $rs['IdBitacora'] . "</b>";
        }
        /* Actualizamos el estatus de la solicitud de retiro */
        $query = $catalogo->obtenerLista("UPDATE c_solicitudretiro SET PendienteRetiro = 0, FechaUltimaModificacion = NOW() 
        WHERE IdSolicitudRetiro = " . $rs['IdSolicitudRetiro']);
        if ($query == "1") {//Creamos el mensaje de exito con la liga al reporte que se generó
            $parametros = new Parametros();
            $parametros->getRegistroById(8);
            $liga = $parametros->getDescripcion() . "/WEB-INF/Controllers/Ventas/Controller_Reporte_Historico.php?noSolicitud=$id_reporte_historicos";
            echo "<br/>El equipo " . $_POST['serie'] . " fue aceptado y se generó el reporte de movimiento <a href='$liga' target='_blank'>$id_reporte_historicos</a>";
        } else {
            echo "<br/>La solicitud de retiro " . $rs['IdSolicitudRetiro'] . " no pudo ser actualizada de estatus";
        }
    }
} else {
    echo "<br/>Atención: la solicitud de retiro no se encuentra registrada en la base de datos";
}
?>
