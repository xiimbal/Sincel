<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");

if (isset($_POST['id']) && $_POST['id']) {
    $catalogo = new Catalogo();
    $result = $catalogo->obtenerLista("SELECT 
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
	cinv.NoSerie AS NoSerie,
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCliente
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
WHERE NoSerie='" . $_POST['id'] . "'");
    if ($rs = mysql_fetch_array($result)) {
        $result2 = $catalogo->obtenerLista("SELECT 
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre,
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
	cinv.NoSerie AS NoSerie,
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCliente
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
WHERE ks.ClaveCentroCosto='".$rs['ClaveCentroCosto']."' OR cc.ClaveCentroCosto='".$rs['ClaveCentroCosto']."'");

        echo "<option value=\"\">Selecciona localidad</option>";
        while ($rs2 = mysql_fetch_array($result2)) {
            $s = "";
            if ($rs['NoSerie'] == $rs2['NoSerie']) {
                $s = "selected";
            }
            echo "<option value=\"" . $rs2['NoSerie'] . "\" $s>" . $rs2['NoSerie'] . "</option>";
        }
    } else {
        echo "Error: No se encontró el equipo";
    }
}else{
    echo "Error: No se recibió el equipo";
}

?>
