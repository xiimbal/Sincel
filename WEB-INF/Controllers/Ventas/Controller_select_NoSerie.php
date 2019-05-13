<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$ccosto = "";
if (isset($_POST['id'])) {
    $ccosto = $_POST['id'];
}
$query = $catalogo->obtenerLista("SELECT c_inventarioequipo.NoSerie AS NoSerie,
	c_equipo.Modelo AS Modelo
FROM c_centrocosto
INNER JOIN k_anexoclientecc ON k_anexoclientecc.CveEspClienteCC=c_centrocosto.ClaveCentroCosto
INNER JOIN c_inventarioequipo ON c_inventarioequipo.IdAnexoClienteCC=k_anexoclientecc.IdAnexoClienteCC
INNER JOIN c_equipo ON c_equipo.NoParte=c_inventarioequipo.NoParteEquipo
WHERE c_centrocosto.ClaveCentroCosto='" . $ccosto . "'");
echo "<option value=\"\">Selecciona No Serie</option>";
while ($rs = mysql_fetch_array($query)) {
    echo "<option value=\"" . $rs['NoSerie'] . "\">" . $rs['NoSerie'] . " / " . $rs['Modelo'] . "</option>";
}
?>
