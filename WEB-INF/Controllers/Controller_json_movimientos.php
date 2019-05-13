<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Usuario.class.php");
$id_usuario = "";
$catalogo = new Catalogo();
$usuario = new Usuario();
$tipousuario = "";
if (isset($_GET['id'])) {
    $id_usuario = $_GET['id'];
} else {
    $id_usuario = $_SESSION['idUsuario'];
    if ($usuario->isUsuarioPuesto($id_usuario, 11)) {
        $tipousuario = "11";
    }
    if ($usuario->isUsuarioPuesto($id_usuario, 21)) {
        $tipousuario = "21";
    }
}
$cliente = "";
if (isset($_GET['cliente'])) {
    $query = $catalogo->obtenerLista("SELECT cc.ClaveCentroCosto FROM c_cliente AS c
        INNER JOIN c_centrocosto AS cc ON cc.ClaveCliente=c.ClaveCliente
        WHERE c.ClaveCliente='" . $_GET['cliente'] . "' AND cc.Activo=1");
    $aux = "cc.ClaveCentroCosto IN(";
    $aux2 = "ks.ClaveCentroCosto IN(";
    while ($rsl = mysql_fetch_array($query)) {
        $aux .= "'" . $rsl['ClaveCentroCosto'] . "',";
        $aux2 .= "'" . $rsl['ClaveCentroCosto'] . "',";
    }
    $aux = substr($aux, 0, -1) . ")";
    $aux2 = substr($aux2, 0, -1) . ")";
    $cliente.="(" . $aux . " OR " . $aux2 . ")";
}
if ($usuario->isUsuarioPuesto($id_usuario, 21)) {
    if ($cliente == "") {
        $cliente = " u.IdUsuario='" . $id_usuario . "'";
    } else {
        $cliente.=" AND u.IdUsuario='" . $id_usuario . "'";
    }
}
$query;
$query2;
$query3;
if (isset($_GET['like'])) {
    $tipo = " cinv.NoSerie";
    if (isset($_GET['tipo'])) {
        if ($_GET['tipo'] == 2) {
            $tipo = "c.NombreRazonSocial";
        }
    }
    if ($usuario->isUsuarioPuesto($id_usuario, 21)) {
        $query = $catalogo->obtenerLista("SELECT
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre,
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
            cinv.NoSerie AS NoSerie,
            (CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ID, 
            c_equipo.Modelo AS Modelo,
            u.IdUsuario AS Usuario,
            ks.IdKserviciogimgfa,
            csrg.Contestado AS ConSolicitud,
            cs.id_solicitud,
            bit.VentaDirecta AS VentaDirecta
            FROM `c_inventarioequipo` AS cinv
            LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
            LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
            LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
            LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
            LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
            LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
            LEFT JOIN c_bitacora AS bit ON bit.NoSerie = cinv.NoSerie
            LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora=bit.id_bitacora)
            LEFT JOIN c_solictudretirogeneral AS csrg ON csr.IdSolicitudRetiroGeneral=csrg.IdSolicitudRetiroGeneral
            LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = bit.id_solicitud AND cs.estatus IN(1,2)
            WHERE " . $tipo . " LIKE '%" . $_GET['like'] . "%' AND u.IdUsuario='" . $id_usuario . "' AND cc.Activo=1
            ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, cinv.NoSerie DESC;");
        $query3 = $catalogo->obtenerLista("SELECT
            (CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCli, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCC, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre, 
            COUNT(cinv.NoSerie) AS SUMA,
            cs.id_solicitud,
            csrg.Contestado AS ConSolicitud
            FROM `c_inventarioequipo` AS cinv
            LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
            LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
            LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
            LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
            LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
            LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
            LEFT JOIN c_bitacora AS bit ON bit.NoSerie = cinv.NoSerie
            LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora=bit.id_bitacora)
            LEFT JOIN c_solictudretirogeneral AS csrg ON csr.IdSolicitudRetiroGeneral=csrg.IdSolicitudRetiroGeneral
            LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = bit.id_solicitud AND cs.estatus IN(1,2)
            WHERE  " . $tipo . " LIKE '%" . $_GET['like'] . "%' AND u.IdUsuario='" . $id_usuario . "' AND cc.Activo=1
            GROUP BY ClaveCC, ClaveCli
            ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, cinv.NoSerie DESC;");
    } else {
        $query = $catalogo->obtenerLista("SELECT
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre, 
            cinv.NoSerie AS NoSerie,
            (CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ID, 
            c_equipo.Modelo AS Modelo,
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.EjecutivoCuenta FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.EjecutivoCuenta END) AS Usuario,
            ks.IdKserviciogimgfa,
            csrg.Contestado AS ConSolicitud,
            cs.id_solicitud,
            bit.VentaDirecta AS VentaDirecta
            FROM `c_inventarioequipo` AS cinv
            LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
            LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
            LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
            LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
            LEFT JOIN c_bitacora AS bit ON bit.NoSerie = cinv.NoSerie
            LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora=bit.id_bitacora)
            LEFT JOIN c_solictudretirogeneral AS csrg ON csr.IdSolicitudRetiroGeneral=csrg.IdSolicitudRetiroGeneral
            LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = bit.id_solicitud AND cs.estatus IN(1,2)
            WHERE " . $tipo . " LIKE '%" . $_GET['like'] . "%' AND cc.Activo=1
            ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, cinv.NoSerie DESC;");
        $query3 = $catalogo->obtenerLista("SELECT
            (CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCli, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCC, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre, 
            COUNT(cinv.NoSerie) AS SUMA,
            cs.id_solicitud,
            csrg.Contestado AS ConSolicitud
            FROM `c_inventarioequipo` AS cinv
            LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
            LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
            LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
            LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
            LEFT JOIN c_bitacora AS bit ON bit.NoSerie = cinv.NoSerie
            LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora=bit.id_bitacora)
            LEFT JOIN c_solictudretirogeneral AS csrg ON csr.IdSolicitudRetiroGeneral=csrg.IdSolicitudRetiroGeneral
            LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = bit.id_solicitud AND cs.estatus IN(1,2)
            WHERE  " . $tipo . " LIKE '%" . $_GET['like'] . "%' AND cc.Activo=1
            GROUP BY ClaveCC, ClaveCli
            ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, cinv.NoSerie DESC;");
    }
    if (isset($_GET['tipo']) && $_GET['tipo'] != 2) {
        $query2 = $catalogo->obtenerLista("SELECT 
            c_almacen.id_almacen AS ID,
            c_almacen.nombre_almacen AS NombreAlmacen,
            k_almacenequipo.NoSerie AS NoSerie,
            cs.id_solicitud,
            c_equipo.Modelo AS Modelo
            FROM c_almacen
            INNER JOIN k_almacenequipo ON k_almacenequipo.id_almacen=c_almacen.id_almacen
            LEFT JOIN c_equipo ON k_almacenequipo.NoParte = c_equipo.NoParte
            LEFT JOIN c_bitacora AS bit ON bit.NoSerie = k_almacenequipo.NoSerie
            LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = bit.id_solicitud AND cs.estatus IN(1,2)
            WHERE k_almacenequipo.NoSerie LIKE '%" . $_GET['like'] . "%' ORDER BY NombreAlmacen,NoSerie");
        $query4 = $catalogo->obtenerLista("SELECT 
            COUNT(c_almacen.nombre_almacen) as contador
            FROM c_almacen
            INNER JOIN k_almacenequipo ON k_almacenequipo.id_almacen=c_almacen.id_almacen
            LEFT JOIN c_equipo ON k_almacenequipo.NoParte = c_equipo.NoParte
            WHERE k_almacenequipo.NoSerie LIKE '%" . $_GET['like'] . "%'
            GROUP BY c_almacen.nombre_almacen
            ORDER BY c_almacen.nombre_almacen");
    } else {
        $query2 = $catalogo->obtenerLista("SELECT 
            c_almacen.id_almacen AS ID,
            c_almacen.nombre_almacen AS NombreAlmacen,
            k_almacenequipo.NoSerie AS NoSerie,
            cs.id_solicitud,
            c_equipo.Modelo AS Modelo
            FROM c_almacen
            INNER JOIN k_almacenequipo ON k_almacenequipo.id_almacen=c_almacen.id_almacen
            LEFT JOIN c_equipo ON k_almacenequipo.NoParte = c_equipo.NoParte
            LEFT JOIN c_bitacora AS bit ON bit.NoSerie = k_almacenequipo.NoSerie
            LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = bit.id_solicitud AND cs.estatus IN(1,2)
            ORDER BY NombreAlmacen,NoSerie");
        $query4 = $catalogo->obtenerLista("SELECT 
            COUNT(c_almacen.nombre_almacen) as contador
            FROM c_almacen
            INNER JOIN k_almacenequipo ON k_almacenequipo.id_almacen=c_almacen.id_almacen
            LEFT JOIN c_equipo ON k_almacenequipo.NoParte = c_equipo.NoParte
            GROUP BY c_almacen.nombre_almacen
            ORDER BY c_almacen.nombre_almacen");
    }
} else {
    if ($usuario->isUsuarioPuesto($id_usuario, 21)) {
        $query = $catalogo->obtenerLista("SELECT
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre,
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
            cinv.NoSerie AS NoSerie,
            (CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ID, 
            c_equipo.Modelo AS Modelo,
            u.IdUsuario AS Usuario,
            ks.IdKserviciogimgfa,
            csrg.Contestado AS ConSolicitud,
            cs.id_solicitud,
            bit.VentaDirecta AS VentaDirecta
            FROM `c_inventarioequipo` AS cinv
            LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
            RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
            RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
            LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
            LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
            LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
            LEFT JOIN c_bitacora AS bit ON bit.NoSerie = cinv.NoSerie
            LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora=bit.id_bitacora)
            LEFT JOIN c_solictudretirogeneral AS csrg ON csr.IdSolicitudRetiroGeneral=csrg.IdSolicitudRetiroGeneral
            LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = bit.id_solicitud AND cs.estatus IN(1,2)
            WHERE " . $cliente . "
            ORDER BY NombreCliente ASC,CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, cinv.NoSerie DESC;");
        $query3 = $catalogo->obtenerLista("SELECT
            (CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCli, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCC, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre, 
            COUNT(cinv.NoSerie) AS SUMA,
            cs.id_solicitud,
            csrg.Contestado AS ConSolicitud
            FROM `c_inventarioequipo` AS cinv
            LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
            RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
            RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
            LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
            LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
            LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
            LEFT JOIN c_bitacora AS bit ON bit.NoSerie = cinv.NoSerie
            LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora=bit.id_bitacora)
            LEFT JOIN c_solictudretirogeneral AS csrg ON csr.IdSolicitudRetiroGeneral=csrg.IdSolicitudRetiroGeneral
            LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = bit.id_solicitud AND cs.estatus IN(1,2)
            WHERE  " . $cliente . "
            GROUP BY ClaveCC, ClaveCli
            ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, cinv.NoSerie DESC;");
    } else {
        $query = $catalogo->obtenerLista("SELECT
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre, 
            cinv.NoSerie AS NoSerie,
            (CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ID, 
            c_equipo.Modelo AS Modelo,
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.EjecutivoCuenta FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.EjecutivoCuenta END) AS Usuario,
            ks.IdKserviciogimgfa,
            cs.id_solicitud,
            csrg.Contestado AS ConSolicitud,
            bit.VentaDirecta AS VentaDirecta
            FROM `c_inventarioequipo` AS cinv
            LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
            RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
            RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
            LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
            LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
            LEFT JOIN c_bitacora AS bit ON bit.NoSerie = cinv.NoSerie
            LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora=bit.id_bitacora)
            LEFT JOIN c_solictudretirogeneral AS csrg ON csr.IdSolicitudRetiroGeneral=csrg.IdSolicitudRetiroGeneral
            LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = bit.id_solicitud AND cs.estatus IN(1,2)
            WHERE " . $cliente . "
            ORDER BY NombreCliente ASC,CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, cinv.NoSerie DESC;");
        $query3 = $catalogo->obtenerLista("SELECT
            (CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCli, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCC, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre, 
            COUNT(cinv.NoSerie) AS SUMA,
            cs.id_solicitud,
            csrg.Contestado AS ConSolicitud
            FROM `c_inventarioequipo` AS cinv
            LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
            RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
            RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
            LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
            LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
            LEFT JOIN c_bitacora AS bit ON bit.NoSerie = cinv.NoSerie
            LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora=bit.id_bitacora)
            LEFT JOIN c_solictudretirogeneral AS csrg ON csr.IdSolicitudRetiroGeneral=csrg.IdSolicitudRetiroGeneral
            LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = bit.id_solicitud AND cs.estatus IN(1,2)
            WHERE  " . $cliente . "
            GROUP BY ClaveCC, ClaveCli
            ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, cinv.NoSerie DESC;");
    }
    $query2 = $catalogo->obtenerLista("SELECT 
        c_almacen.id_almacen AS ID,
	c_almacen.nombre_almacen AS NombreAlmacen,
	k_almacenequipo.NoSerie AS NoSerie,
        cs.id_solicitud,
        c_equipo.Modelo AS Modelo
        FROM c_almacen
        INNER JOIN k_almacenequipo ON k_almacenequipo.id_almacen=c_almacen.id_almacen
        LEFT JOIN c_equipo ON k_almacenequipo.NoParte = c_equipo.NoParte
        LEFT JOIN c_bitacora AS bit ON bit.NoSerie = k_almacenequipo.NoSerie
        LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = bit.id_solicitud AND cs.estatus IN(1,2)
        ORDER BY NombreAlmacen,NoSerie");
    $query4 = $catalogo->obtenerLista("SELECT 
	COUNT(c_almacen.nombre_almacen) as contador
        FROM c_almacen
        INNER JOIN k_almacenequipo ON k_almacenequipo.id_almacen=c_almacen.id_almacen
        LEFT JOIN c_equipo ON k_almacenequipo.NoParte = c_equipo.NoParte         
        GROUP BY c_almacen.nombre_almacen
        ORDER BY c_almacen.nombre_almacen");
}

$json = "";
$contador = 0;
$anterior_cliente = "";
$anterior_centro = "";
$id = 2;
$id_cliente = 0;
$id_centro = 0;

$json.="{\"id\":\"no" . $id . "\",\"text\":\"Almacenes\",\"iconCls\":\"icon-person\",\"children\":[";
$id_cliente = $id;
$id++;
while ($rs = mysql_fetch_array($query2)) {
    if ($anterior_centro == $rs['NombreAlmacen']) {  
        $pendiente = "";
        $no = "";
        if(isset($rs['id_solicitud']) && $rs['id_solicitud'] != ""){
            $pendiente = "<span style='color:red'>(En solicitud de equipo: ".$rs['id_solicitud'].")</span>";
            $no = "no";
        }
        $json.=",{\"id\":\"$no&A%" . $rs['ID'] . "\",\"text\":\"" . $rs['NoSerie'] . " - Modelo:" . $rs['Modelo'] . " $pendiente\",\"iconCls\":\"icon-impresora\"}";        
        $id++;
    } else {
        if ($id > 3) {
            $json.="]},";
        }
        $rsp = mysql_fetch_array($query4);
        $json.="{\"id\":\"no" . $id . "\",\"text\":\"" . $rs['NombreAlmacen'] . "&nbsp;<span style='color:blue'>(" . $rsp['contador'] . ")</span>\",\"iconCls\":\"icon-edificio\",\"children\":[";
        $id_centro = $id;
        $anterior_centro = $rs['NombreAlmacen'];
        $id++;
        $pendiente = "";
        $no = "";
        if(isset($rs['id_solicitud']) && $rs['id_solicitud'] != ""){
            $pendiente = "<span style='color:red'>(En solicitud de equipo: ".$rs['id_solicitud'].")</span>";
            $no = "no";
        }
        $json.="{\"id\":\"$no&A%" . $rs['ID'] . "\",\"text\":\"" . $rs['NoSerie'] . " - Modelo:" . $rs['Modelo'] . " $pendiente\",\"iconCls\":\"icon-impresora\"}";        
        $id++;
    }
}
$json .="]}]},";
if ($id == 3) {
    $json = "";
}

$sinequipos = "Sin equipos";
while ($rs = mysql_fetch_array($query)) {
    if ($anterior_cliente == $rs['NombreCliente']) {
        if ($anterior_centro == $rs['CentroCostoNombre']) {            
            if ($rs['NoSerie'] != null) {
                $pendiente = "";
                $no = "";
                if ($rs['ConSolicitud'] == "0") {
                    $pendiente = "<span style='color:red'>(Pendiente de retiro)</span>";
                    $no = "no";
                }
                if(isset($rs['id_solicitud']) && $rs['id_solicitud'] != ""){
                    $pendiente = "<span style='color:red'>(En solicitud de equipo: ".$rs['id_solicitud'].")</span>";
                    $no = "no";
                }
                if ($rs['VentaDirecta'] == "1") {
                    $pendiente.= "<span style='color:red'>(Este equipo es de Venta Directa)</span>";
                    $no = "no";
                }
                
                if (($tipousuario == "11" && $rs['Usuario'] != $_SESSION['idUsuario']) || ($tipousuario == "21" && $rs['Usuario'] != $_SESSION['idUsuario'])) {
                    $json.=",{\"id\":\"no" . $id . "\",\"text\":\"" . $rs['NoSerie'] . " - Modelo:" . $rs['Modelo'] . " <span style='color:red'>(Este equipo no pertenece a alguno de sus clientes)</span>$pendiente\",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
                } else {
                    $json.=",{\"id\":\"$no" . $rs['ID'] . "\",\"text\":\"" . $rs['NoSerie'] . " - Modelo:" . $rs['Modelo'] . " $pendiente\",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
                }
            }
            $id++;
        } else {
            $rss = mysql_fetch_array($query3);
            $json.="]},{\"id\":\"no" . $rs['ID'] . "\",\"text\":\"" . $rs['CentroCostoNombre'] . "&nbsp;<span style='color:blue'>(" . $rss['SUMA'] . ")</span>\",\"iconCls\":\"icon-edificio\",\"children\":[";            
            $id_centro = $id;
            $anterior_centro = $rs['CentroCostoNombre'];
            $id++;
            $pendiente = "";
            $no = "";
            if ($rs['ConSolicitud'] == "0") {
                $pendiente = "<span style='color:red'>(Pendiente de retiro)</span>";
                $no = "no";
            }
            if(isset($rs['id_solicitud']) && $rs['id_solicitud'] != ""){
                $pendiente = "<span style='color:red'>(En solicitud de equipo: ".$rs['id_solicitud'].")</span>";
                $no = "no";
            }
            if ($rs['VentaDirecta'] == "1") {
                $pendiente.= "<span style='color:red'>(Este equipo es de Venta Directa)</span>";
                $no = "no";
            }
            if ($rs['NoSerie'] == null) {
                $json.="{\"id\":\"no" . $id . "\",\"text\":\"" . $sinequipos . "\",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
            } elseif (($tipousuario == "11" && $rs['Usuario'] != $_SESSION['idUsuario']) || ($tipousuario == "21" && $rs['Usuario'] != $_SESSION['idUsuario'])) {
                $json.="{\"id\":\"no" . $id . "\",\"text\":\"" . $rs['NoSerie'] . " - Modelo:" . $rs['Modelo'] . " <span style='color:red'>(Este equipo no pertenece a alguno de sus clientes)</span>$pendiente\",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
            } else {
                $json.="{\"id\":\"$no" . $rs['ID'] . "\",\"text\":\"" . $rs['NoSerie'] . " - Modelo:" . $rs['Modelo'] . "$pendiente \",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
            }
            $id++;
        }
    } else {
        $rss = mysql_fetch_array($query3);
        if ($contador > 0) {
            $json.="]}]},";
        }
        $json.="{\"id\":\"no" . $id . "\",\"text\":\"" . $rs['NombreCliente'] . "\",\"iconCls\":\"icon-person\",\"children\":[";
        $anterior_cliente = $rs['NombreCliente'];
        $id_cliente = $id;
        $id++;
        $json.="{\"id\":\"no" . $rs['ID'] . "\",\"text\":\"" . $rs['CentroCostoNombre'] . "&nbsp;<span style='color:blue'>(" . $rss['SUMA'] . ")</span>\",\"iconCls\":\"icon-edificio\",\"children\":[";
        $id_centro = $id;
        $anterior_centro = $rs['CentroCostoNombre'];
        $id++;
        $pendiente = "";
        $no = "";
        if ($rs['ConSolicitud'] == "0") {
            $pendiente = "<span style='color:red'>(Pendiente de retiro)</span>";
            $no = "no";
        }
        if(isset($rs['id_solicitud']) && $rs['id_solicitud'] != ""){
            $pendiente = "<span style='color:red'>(En solicitud de equipo: ".$rs['id_solicitud'].")</span>";
            $no = "no";
        }
        if ($rs['VentaDirecta'] == "1") {
            $pendiente.= "<span style='color:red'>(Este equipo es de Venta Directa)</span>";
            $no = "no";
        }
        if ($rs['NoSerie'] == null) {
            $json.="{\"id\":\"no" . $id . "\",\"text\":\"" . $sinequipos . "\",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
        } elseif (($tipousuario == "11" && $rs['Usuario'] != $_SESSION['idUsuario']) || ($tipousuario == "21" && $rs['Usuario'] != $_SESSION['idUsuario'])) {
            $json.="{\"id\":\"no" . $id . "\",\"text\":\"" . $rs['NoSerie'] . " - Modelo:" . $rs['Modelo'] . " <span style='color:red'>(Este equipo no pertenece a alguno de sus clientes)</span>$pendiente\",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
        } else {
            $json.="{\"id\":\"$no" . $rs['ID'] . "\",\"text\":\"" . $rs['NoSerie'] . " - Modelo:" . $rs['Modelo'] . "$pendiente \",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
        }        
        $id++;
    }
    $contador++;
}
if ($contador != 0) {
    $json .="]}]";
} else {
    $json = substr_replace($json, "", -2);
}
if ($id == 3) {
    echo "[{\"id\":\"no1\",\"text\":\"<span style='color:red'>No se encontró ningún resultado</span>\"}]";
} else {
    echo "[" . preg_replace('/\t/', '', $json) . "}]";
}

?>