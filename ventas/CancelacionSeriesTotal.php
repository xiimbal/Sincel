<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Lectura.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/Movimiento.class.php");
include_once("../WEB-INF/Classes/AlmacenEquipo.class.php");
include_once("../WEB-INF/Classes/CentroCosto.class.php");


$permisos_grid = new PermisosSubMenu();
$lectura = new Lectura();

$idSolicitud = $_POST['id'];
$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT c_solicitud.comentario, c_cliente.NombreRazonSocial, c_solicitud.id_almacen, c_solicitud.ClaveCliente 
    FROM c_solicitud INNER JOIN c_cliente ON c_cliente.ClaveCliente = c_solicitud.ClaveCliente 
    WHERE id_solicitud = $idSolicitud;");
$comentario = "";
$cliente = "";
$ClaveCliente = "";
$IdAlmacenDestino = "";
while ($rs = mysql_fetch_array($query)) {
    $comentario = $rs['comentario'];
    $cliente = $rs['NombreRazonSocial'];
    $ClaveCliente = $rs['ClaveCliente'];
    $IdAlmacenDestino = $rs['id_almacen'];
}

$consulta = "SELECT ks.*, e.Modelo AS modelo_equipo, e.NoParte AS parte, c.ClaveCentroCosto ,c.Nombre, cli.NombreRazonSocial, ti.Nombre AS estadoEquipo, ti.idTipo,k_enviosmensajeria.NoSerie AS Serie,calm.nombre_almacen AS Almacen 
    FROM `k_solicitud` AS ks
    INNER JOIN c_equipo AS e ON ks.id_solicitud = $idSolicitud AND ks.tipo = 0 AND e.NoParte = ks.Modelo 
    LEFT JOIN c_centrocosto AS c ON c.ClaveCentroCosto = ks.ClaveCentroCosto 
    LEFT JOIN c_cliente AS cli ON cli.ClaveCliente = c.ClaveCliente 
    LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = ks.TipoInventario 
    INNER JOIN k_enviosmensajeria ON k_enviosmensajeria.IdSolicitud=$idSolicitud 
    LEFT JOIN k_almacenequipo AS kale ON kale.NoSerie=k_enviosmensajeria.NoSerie 
    LEFT JOIN c_almacen AS calm ON calm.id_almacen=kale.id_almacen
    ORDER BY ks.ClaveCentroCosto;";
$query = $catalogo->obtenerLista($consulta);
$movimiento = new Movimiento();
$usuario = $_SESSION['user'];
$pantalla = "Cancelar Solicitud Controller Serie PHP";
while ($rs = mysql_fetch_array($query)) {    
        $query2 = $catalogo->obtenerLista("
            SELECT i.NoParteEquipo,
            (CASE WHEN !ISNULL(i.ClaveEspKServicioFAIM) THEN i.ClaveEspKServicioFAIM ELSE 'NULL' END) AS IdServicio,
            (CASE WHEN !ISNULL(i.IdKServicio) THEN i.IdKServicio ELSE 'NULL' END) AS IdKServicio,
            (CASE WHEN !ISNULL(i.IdKserviciogimgfa) THEN i.IdKserviciogimgfa ELSE 'NULL' END) AS IdKserviciogimgfa,
            (CASE WHEN !ISNULL(i.IdAnexoClienteCC) THEN i.IdAnexoClienteCC ELSE 'NULL' END) AS IdAnexoClienteCC
            FROM c_inventarioequipo AS i WHERE i.NoSerie='".$rs['Serie']."';");
    if ($rss = mysql_fetch_array($query2)) {
        $id_reporte_historicos = $catalogo->insertarRegistro("INSERT INTO reportes_historicos(UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP cambie_equipo2.php');"); //Insertamos para el reporte de movimiento
        if ($id_reporte_historicos == 0 || !$id_reporte_historicos) {
            echo "<br/>Error: no se pudo generar el reporte de movimiento, intente de nuevo por favor.";
            return;
        }

        $consulta = "DELETE FROM `c_inventarioequipo` WHERE NoSerie = '" . $rs['Serie'] . "';";
        $result2 = $catalogo->obtenerLista($consulta);
        /* Insertamos en almacen */
        $obj = new AlmacenEquipo();
        $obj->setNoSerie($rs['Serie']);
        $obj->setIdAlmacen(9);
        $obj->setNoParteEquipo($rss['NoParteEquipo']);
        $obj->setUbicacion("");
        $hoy = getdate();
        $obj->setFechaIngreso($hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday']);
        $obj->setUsuarioCreacion($usuario);
        $obj->setUsuarioModificacion($usuario);
        $obj->setPantalla($pantalla);
        $localidad = new CentroCosto();
        $localidad->getRegistroById($rs['ClaveCentroCosto']);
        if ($obj->newRegistro()) {//Despues de insertar en almacen, guardamos el movimiento y lo asociamos con el reporte.
            $movimiento->nuevoMovimientoClienteAlmacen($rs['Serie'], $localidad->getClaveCliente(), $localidad->getClaveCentroCosto(), $rs['Almacen'], 
                    "", "Cancelacion de serie solicitud", $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'], 1, $usuario, $pantalla,
                    $rss['IdKServicio'],$rss['IdServicio'],$rss['IdAnexoClienteCC'],$rss['IdKserviciogimgfa']);
            $catalogo->insertarRegistro("INSERT INTO reportes_movimientos(id_reportes,id_movimientos)
                        VALUES(" . $id_reporte_historicos . "," . $movimiento->getId_movimientos() . ");");
            $catalogo->obtenerLista("UPDATE `c_bitacora` SET id_solicitud = NULL WHERE NoSerie = '".$rs['Serie']."';");
            echo "La cancelación de la serie " . $rs['Serie'] . " fue exitosa";
        } else {
            echo "No se pudo registrar el equipo " . $rs['Serie'] . " en el almacen";
        }
    } else {
        echo "<br/>Atención: el equipo se encuentra ya en almacén";
    }
}
/*Desasociamos las series de la bitacora y quitamos lo apartado*/
$catalogo->obtenerLista("UPDATE k_almacenequipo SET Apartado = 0 WHERE NoSerie IN (SELECT NoSerie FROM c_bitacora WHERE id_solicitud = $idSolicitud);");
$catalogo->obtenerLista("UPDATE `c_bitacora` SET id_solicitud = NULL WHERE id_solicitud = $idSolicitud;");
$catalogo->obtenerLista("UPDATE c_solicitud SET estatus=4 WHERE id_solicitud = " . $idSolicitud . ";");
//echo $consulta;
?>