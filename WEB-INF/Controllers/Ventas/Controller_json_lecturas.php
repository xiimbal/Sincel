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
	cinv.NoSerie AS NoSerie,cinv.Demo,
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ID, 
	c_equipo.Modelo AS Modelo,
	u.IdUsuario AS Usuario,
	ks.IdKserviciogimgfa,
        bit.VentaDirecta AS VentaDirecta
        FROM `c_inventarioequipo` AS cinv
        LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
        LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
        INNER JOIN c_bitacora AS bit ON bit.NoSerie=cinv.NoSerie
        LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
        WHERE  " . $tipo . " LIKE '%" . $_GET['like'] . "%' AND u.IdUsuario='" . $id_usuario . "' AND cc.Activo=1 AND bit.Activo = 1
        ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, NoSerie DESC");
        $query2 = $catalogo->obtenerLista("SELECT
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCli, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCC, 
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre, 
        COUNT(cinv.NoSerie) AS SUMA
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
INNER JOIN c_bitacora AS bit ON bit.NoSerie=cinv.NoSerie
WHERE " . $tipo . " LIKE '%" . $_GET['like'] . "%' AND u.IdUsuario='" . $id_usuario . "' AND cc.Activo=1 AND bit.Activo = 1
GROUP BY ClaveCC, ClaveCli
ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, cinv.NoSerie DESC;");
    } else {
        $query = $catalogo->obtenerLista("SELECT
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre,
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
	cinv.NoSerie AS NoSerie,cinv.Demo,
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ID, 
	c_equipo.Modelo AS Modelo,
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.EjecutivoCuenta FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.EjecutivoCuenta END) AS Usuario,
	ks.IdKserviciogimgfa,
        bit.VentaDirecta AS VentaDirecta
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
INNER JOIN c_bitacora AS bit ON bit.NoSerie=cinv.NoSerie
WHERE  " . $tipo . " LIKE '%" . $_GET['like'] . "%' AND cc.Activo=1 AND bit.Activo = 1
ORDER BY NombreCliente ASC,CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, NoSerie DESC");
        $query2 = $catalogo->obtenerLista("SELECT
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCli, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCC, 
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre, 
        COUNT(cinv.NoSerie) AS SUMA
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
INNER JOIN c_bitacora AS bit ON bit.NoSerie=cinv.NoSerie
WHERE " . $tipo . " LIKE '%" . $_GET['like'] . "%' AND cc.Activo=1 AND bit.Activo = 1
GROUP BY ClaveCC, ClaveCli
ORDER BY NombreCliente ASC,CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, cinv.NoSerie DESC;");
    }
} else {
    if ($usuario->isUsuarioPuesto($id_usuario, 21)) {
        $query = $catalogo->obtenerLista("SELECT
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre,
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
	cinv.NoSerie AS NoSerie,cinv.Demo,
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ID, 
	c_equipo.Modelo AS Modelo,
	u.IdUsuario AS Usuario,
	ks.IdKserviciogimgfa,
        bit.VentaDirecta AS VentaDirecta
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
INNER JOIN c_bitacora AS bit ON bit.NoSerie=cinv.NoSerie
WHERE  " . $cliente . " AND bit.Activo = 1
ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, NoSerie DESC");
        $query2 = $catalogo->obtenerLista("SELECT
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCli, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCC, 
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre, 
        COUNT(cinv.NoSerie) AS SUMA
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
INNER JOIN c_bitacora AS bit ON bit.NoSerie=cinv.NoSerie
WHERE " . $cliente . " AND bit.Activo = 1
GROUP BY ClaveCC, ClaveCli
ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, cinv.NoSerie DESC;");
    } else {
        $query = $catalogo->obtenerLista("SELECT
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
        cinv.NoSerie AS NoSerie,cinv.Demo,
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ID, 
	c_equipo.Modelo AS Modelo,
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.EjecutivoCuenta FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.EjecutivoCuenta END) AS Usuario,
	ks.IdKserviciogimgfa,
        bit.VentaDirecta AS VentaDirecta
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
INNER JOIN c_bitacora AS bit ON bit.NoSerie=cinv.NoSerie
WHERE  " . $cliente . " AND bit.Activo = 1
ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre,NoSerie DESC");
        $query2 = $catalogo->obtenerLista("SELECT
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCli, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCC, 
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre, 
	COUNT(cinv.NoSerie) AS SUMA
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
INNER JOIN c_bitacora AS bit ON bit.NoSerie=cinv.NoSerie
WHERE " . $cliente . " AND bit.Activo = 1
GROUP BY ClaveCC, ClaveCli
ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, cinv.NoSerie DESC;");
    }
}
$json = "";
$contador = 0;
$anterior_cliente = "";
$anterior_centro = "";
$id = 2;
$id_cliente = 0;
$id_centro = 0;
$sinequipos = "Sin equipos";
while ($rs = mysql_fetch_array($query)) {
    $cc = str_replace("'","",str_replace("\"", "", $rs['CentroCostoNombre']));
    $nom_cliente = str_replace("'","",str_replace("\"", "", $rs['NombreCliente']));
    if ($anterior_cliente == $nom_cliente) {
        if ($anterior_centro == $cc) {
            //echo "{\"id\":".$id.",\"carpeta\":0,\"name\":\"".$rs['NoSerie']."\",\"_parentId\":2}";
            $mensaje = "";
            if ($rs['VentaDirecta'] == 1) {
                $mensaje = "<span style='color:red'>(Este equipo es de Venta Directa)</span>";
            }
            if ($rs['Demo'] == 1) {
                $mensaje = "<span style='color:red'>(Este equipo está en demo)</span>";
            }
            if ($rs['NoSerie'] != null) {
                if (($tipousuario == "11" && $rs['Usuario'] != $_SESSION['idUsuario']) || ($tipousuario == "21" && $rs['Usuario'] != $_SESSION['idUsuario'])) {
                    $json.=",{\"id\":\"no" . $id . "\",\"text\":\"" . $rs['NoSerie'] . " - Modelo:" . $rs['Modelo'] . " <span style='color:red'>(Este equipo no pertenece a alguno de sus clientes)</span> $mensaje\",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
                } else {
                    $json.=",{\"id\":" . $id . ",\"text\":\"" . $rs['NoSerie'] . " - Modelo:" . $rs['Modelo'] . " $mensaje\",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
                }
            }
            $id++;
        } else {
            $mensaje = "";
            if ($rs['VentaDirecta'] == 1) {
                $mensaje = "<span style='color:red'>(Este equipo es de Venta Directa)</span>";
            }
            if ($rs['Demo'] == 1) {
                $mensaje = "<span style='color:red'>(Este equipo está en demo)</span>";
            }
            $rss = mysql_fetch_array($query2);
            if (($tipousuario == "11" && $rs['Usuario'] != $_SESSION['idUsuario']) || ($tipousuario == "21" && $rs['Usuario'] != $_SESSION['idUsuario'])) {
                $json.="]},{\"id\":\"no%" . $rs['ClaveCentroCosto'] . "\",\"text\":\"" . $cc . "&nbsp;<span style='color:red'>(Esta localidad no pertenece a alguno de sus clientes)</span> \",\"iconCls\":\"icon-edificio\",\"children\":[";
            } else {
                $json.="]},{\"id\":\"lo%" . $rs['ClaveCentroCosto'] . "\",\"text\":\"" . $cc . "&nbsp;<span style='color:blue'>(" . $rss['SUMA'] . ")</span>\",\"iconCls\":\"icon-edificio\",\"children\":[";
            }
            $id_centro = $id;
            $anterior_centro = $cc;
            $id++;
            if ($rs['NoSerie'] == null) {
                $json.="{\"id\":\"no" . $id . "\",\"text\":\"" . $sinequipos . "\",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
            } elseif (($tipousuario == "11" && $rs['Usuario'] != $_SESSION['idUsuario']) || ($tipousuario == "21" && $rs['Usuario'] != $_SESSION['idUsuario'])) {
                $json.="{\"id\":\"no" . $id . "\",\"text\":\"" . $rs['NoSerie'] . " - Modelo:" . $rs['Modelo'] . " <span style='color:red'>(Este equipo no pertenece a alguno de sus clientes)</span> $mensaje\",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
            } else {
                $json.="{\"id\":" . $id . ",\"text\":\"" . $rs['NoSerie'] . " - Modelo:" . $rs['Modelo'] . " $mensaje\",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
            }
            $id++;
        }
    } else {
        $mensaje = "";
        if ($rs['VentaDirecta'] == 1) {
            $mensaje = "<span style='color:red'>(Este equipo es de Venta Directa)</span>";
        }
        if ($rs['Demo'] == 1) {
            $mensaje = "<span style='color:red'>(Este equipo está en demo)</span>";
        }
        $rss = mysql_fetch_array($query2);
        if ($contador > 0) {
            $json.="]}]},";
        }
        if (($tipousuario == "11" && $rs['Usuario'] != $_SESSION['idUsuario']) || ($tipousuario == "21" && $rs['Usuario'] != $_SESSION['idUsuario'])) {
            $json.="{\"id\":\"no%" . $rs['ID'] . "\",\"text\":\"" . $nom_cliente . "&nbsp;<span style='color:red'>(Usted no es ejecutivo de este cliente)</span>\",\"iconCls\":\"icon-person\",\"children\":[";
        } else {
            $json.="{\"id\":\"cli%" . $rs['ID'] . "\",\"text\":\"" . $nom_cliente . "\",\"iconCls\":\"icon-person\",\"children\":[";
        }

        $anterior_cliente = $nom_cliente;
        $id_cliente = $id;
        $id++;
        if (($tipousuario == "11" && $rs['Usuario'] != $_SESSION['idUsuario']) || ($tipousuario == "21" && $rs['Usuario'] != $_SESSION['idUsuario'])) {
            $json.="{\"id\":\"no%" . $rs['ClaveCentroCosto'] . "\",\"text\":\"" . $cc . "&nbsp;<span style='color:red'>(Esta localidad no pertenece a alguno de sus clientes)</span>\",\"iconCls\":\"icon-edificio\",\"children\":[";
        } else {
            $json.="{\"id\":\"lo%" . $rs['ClaveCentroCosto'] . "\",\"text\":\"" . $cc . "&nbsp;<span style='color:blue'>(" . $rss['SUMA'] . ")</span>\",\"iconCls\":\"icon-edificio\",\"children\":[";
        }
        $id_centro = $id;
        $anterior_centro = $cc;
        $id++;
        if ($rs['NoSerie'] == null) {
            $json.="{\"id\":\"no" . $id . "\",\"text\":\"" . $sinequipos . "\",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
        } elseif (($tipousuario == "11" && $rs['Usuario'] != $_SESSION['idUsuario']) || ($tipousuario == "21" && $rs['Usuario'] != $_SESSION['idUsuario'])) {
            $json.="{\"id\":\"no" . $id . "\",\"text\":\"" . $rs['NoSerie'] . " - Modelo:" . $rs['Modelo'] . " <span style='color:red'>(Este equipo no pertenece a alguno de sus clientes)</span> $mensaje\",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
        } else {
            $json.="{\"id\":" . $id . ",\"text\":\"" . $rs['NoSerie'] . " - Modelo:" . $rs['Modelo'] . " $mensaje\",\"_parentId\":" . $id_centro . ",\"iconCls\":\"icon-impresora\"}";
        }
        $id++;
        $contador++;
    }
}
$json .="]}]";
if ($id == 2) {
    echo "[{\"id\":\"no1\",\"text\":\"<span style='color:red'>No se encontró ningún resultado</span>\"}]";
} else {
    echo "[" . preg_replace('/\t/', '', $json) . "}]";
}
//echo "[" . substr_replace($json, "", -1) . "],\"footer\":[{\"name\":\"Total Clientes: " . $contador . "\"}]";
?>
