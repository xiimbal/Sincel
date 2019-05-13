<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Cliente.class.php");
include_once("../../Classes/Parametros.class.php");

if (isset($_POST['id']) && $_POST['id']) {
    $parametros = new Parametros();
    $permitir_moroso = "0";

    if ($parametros->getRegistroById("14")) {
        $permitir_moroso = $parametros->getValor();
    }

    $catalogo = new Catalogo();
    $result = $catalogo->obtenerLista("SELECT 
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
	cinv.NoSerie AS NoSerie, c.IdEstatusCobranza, c.Suspendido,
	(CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCliente
        FROM `c_inventarioequipo` AS cinv
        LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
        LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
        LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
        WHERE TRIM(NoSerie)=TRIM('" . $_POST['id'] . "')");
    if ($rs = mysql_fetch_array($result)) {
        if ($rs['Suspendido'] == "1") {
            $cliente = new Cliente();
            $cliente->getRegistroById($rs['ClaveCliente']);
            echo "Error: El cliente <b>" . $cliente->getNombreRazonSocial() . "</b> se encuentra suspendido, no se puede levantar ticket";
            return;
        } else if ($rs['IdEstatusCobranza'] == "2") {
            $permitir = false;
            if ($permitir_moroso == "1") {
                $permitir = true;
            }

            if (!$permitir) {
                $cliente = new Cliente();
                $cliente->getRegistroById($rs['ClaveCliente']);
                echo "Error: El cliente <b>" . $cliente->getNombreRazonSocial() . "</b> se encuentra como moroso, no se puede levantar un ticket";
                return;
            }
        }

        $result2 = $catalogo->obtenerLista("SELECT * FROM c_cliente");
        echo "<option value=\"\">Selecciona el cliente</option>";
        while ($rs2 = mysql_fetch_array($result2)) {
            $s = "";
            if ($rs2['ClaveCliente'] == $rs['ClaveCliente']) {
                $s = "selected";
            }
            echo "<option value=\"" . $rs2['ClaveCliente'] . "\" $s>" . $rs2['NombreRazonSocial'] . "</option>";
        }
    } else {
        echo "Error: No se encontró el equipo";
    }
} else {
    echo "Error: No se recibió el equipo";
}
?>
