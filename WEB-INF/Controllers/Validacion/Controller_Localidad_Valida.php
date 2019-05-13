<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}

include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Parametros.class.php");
$catalogo = new Catalogo();
$result = $catalogo->obtenerLista("SELECT cc.Nombre AS NombreCC,cc.ClaveCentroCosto,cli.NombreRazonSocial AS NombreCli,
    (CASE WHEN cli.IdEstatusCobranza = 2 THEN 1 ELSE 0 END) AS MorosoCli, cli.IdTipoMorosidad AS TipoMorosidad,
    cli.Suspendido AS Suspendido,c.nombre AS NombreCen, c.Moroso AS MorosoC,
    cc.Moroso AS MorosoCC 
    FROM c_centrocosto AS cc
    INNER JOIN c_cliente AS cli ON cli.ClaveCliente=cc.ClaveCliente
    LEFT JOIN c_cen_costo AS c ON c.id_cc=cc.id_cr
    WHERE cc.ClaveCentroCosto='" . $_POST['localidad'] . "';");
if ($rs = mysql_fetch_array($result)) {
    $parametro = new Parametros();
    $parametro->getRegistroById("14");
    if ($rs['TipoMorosidad'] == 1 && $rs['MorosoCli'] == 1 && $parametro->getValor() == 0) {
        echo "1:El cliente " . $rs['NombreCli'] . " se encuentra moroso.";
    } elseif ($rs['TipoMorosidad'] == 2 && $rs['MorosoCC'] == 1 && $parametro->getValor() == 0) {
        echo "1:La localidad " . $rs['NombreCC'] . " se encuentra morosa.";
    } elseif ($rs['TipoMorosidad'] == 3 && $rs['MorosoC'] == 1 && $parametro->getValor() == 0) {
        echo "1:El centro de costo " . $rs['NombreCen'] . " se encuentra moroso.";
    } elseif ($rs['Suspendido'] == 1) {
        echo "0:El cliente " . $rs['NombreCli'] . " se encuentra suspendido.";
    } else {
        echo "1:La localidad es valida.";
    }
} else {
    echo "0:No se encontró la localidad";
}