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
WHERE c.ClaveCliente='" . $_GET['cliente'] . "'  AND cc.Activo=1");
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
if($usuario->isUsuarioPuesto($id_usuario, 21)){
    if($cliente==""){
        $cliente=" u.IdUsuario='".$id_usuario."'";
    }else{
        $cliente.=" AND u.IdUsuario='".$id_usuario."'";
    }
}
$query;
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
        COUNT(cinv.NoSerie) AS Cuenta,
	u.IdUsuario AS Usuario,
	ks.IdKserviciogimgfa
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
WHERE " . $tipo . " LIKE '%" . $_GET['like'] . "%' AND u.IdUsuario='".$id_usuario."'  AND cc.Activo=1
    GROUP BY CentroCostoNombre, NombreCliente
ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, NoSerie DESC");
    } else {
        $query = $catalogo->obtenerLista("SELECT
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre,
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
        COUNT(cinv.NoSerie) AS Cuenta,
	cinv.NoSerie AS NoSerie,
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ID, 
	c_equipo.Modelo AS Modelo,
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.EjecutivoCuenta FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.EjecutivoCuenta END) AS Usuario,
	ks.IdKserviciogimgfa
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
WHERE " . $tipo . " LIKE '%" . $_GET['like'] . "%'  AND cc.Activo=1
    GROUP BY CentroCostoNombre, NombreCliente
ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, NoSerie DESC");
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
        COUNT(cinv.NoSerie) AS Cuenta,
	u.IdUsuario AS Usuario,
	ks.IdKserviciogimgfa
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
WHERE " . $cliente . "
    GROUP BY ClaveCentroCosto, ID
ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, NoSerie DESC");
    } else {
        $query = $catalogo->obtenerLista("SELECT
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre,
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
        COUNT(cinv.NoSerie) AS Cuenta,
	cinv.NoSerie AS NoSerie,
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ID, 
	c_equipo.Modelo AS Modelo,
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.EjecutivoCuenta FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.EjecutivoCuenta END) AS Usuario,
	ks.IdKserviciogimgfa
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
WHERE " . $cliente . "
    GROUP BY ClaveCentroCosto, ID
ORDER BY NombreCliente ASC, CAST(CentroCostoNombre AS CHAR),BINARY CentroCostoNombre, NoSerie DESC");
    }
}
$json = "";
$contador = 0;
$anterior_cliente = "";
$anterior_centro = "";
$id = 2;
$id_cliente = 0;
$id_centro = 0;

while ($rs = mysql_fetch_array($query)) {
    $cc = str_replace("'","",str_replace("\"", "", $rs['CentroCostoNombre']));
    $nom_cliente = str_replace("'","",str_replace("\"", "", $rs['NombreCliente']));
    if ($anterior_cliente == $nom_cliente) {
        if (($tipousuario == "11" && $rs['Usuario'] != $_SESSION['idUsuario']) || ($tipousuario == "21" && $rs['Usuario'] != $_SESSION['idUsuario'])) {
            $json.=",{\"id\":\"no" . $rs['ClaveCentroCosto'] . "\",\"text\":\"" . $cc . "&nbsp;<span style='color:blue'>(" . $rs['Cuenta'] . ")</span> <span style='color:red'>(Esta localidad no pertenece a alguno de sus clientes)</span>\",\"iconCls\":\"icon-edificio\"}";
        } else {
            $json.=",{\"id\":\"" . $rs['ClaveCentroCosto'] . "\",\"text\":\"" . $cc . "&nbsp;<span style='color:blue'>(" . $rs['Cuenta'] . ")</span>\",\"iconCls\":\"icon-edificio\"}";
        }
        $id_centro = $id;
        $anterior_centro = $cc;
        $id++;
    } else {
        if ($contador > 0) {
            $json.="]},";
        }
        $json.="{\"id\":\"no" . $id . "\",\"text\":\"" . $nom_cliente . "\",\"iconCls\":\"icon-person\",\"children\":[";
        $anterior_cliente = $nom_cliente;
        $id_cliente = $id;
        $id++;
        if (($tipousuario == "11" && $rs['Usuario'] != $_SESSION['idUsuario']) || ($tipousuario == "21" && $rs['Usuario'] != $_SESSION['idUsuario'])) {
            $json.="{\"id\":\"no" . $rs['ClaveCentroCosto'] . "\",\"text\":\"" . $cc . "&nbsp;<span style='color:blue'>(" . $rs['Cuenta'] . ")</span> <span style='color:red'>(Esta localidad no pertenece a alguno de sus clientes)</span>\",\"iconCls\":\"icon-edificio\"}";
        } else {
            $json.="{\"id\":\"" . $rs['ClaveCentroCosto'] . "\",\"text\":\"" . $cc . "&nbsp;<span style='color:blue'>(" . $rs['Cuenta'] . ")</span>\",\"iconCls\":\"icon-edificio\"}";
        }
        $id_centro = $id;
        $anterior_centro = $cc;
        $id++;
        $contador++;
    }
}
$json .="]";
if ($id == 2) {
    echo "[{\"id\":\"no1\",\"text\":\"<span style='color:red'>No se encontró ningún resultado</span>\"}]";
} else {
    echo "[" . preg_replace('/\t/', '', $json) . "}]";
}
?>
