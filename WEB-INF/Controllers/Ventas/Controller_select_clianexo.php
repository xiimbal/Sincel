<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$ccosto = "";
if (isset($_POST['ccosto'])) {
    $ccosto = $_POST['ccosto'];
}

if (isset($_POST['contrato'])) {
    $contrato = " AND cat.NoContrato = '" . $_POST['contrato'] . "' ";
} else {
    $contrato = "";
}

if (isset($_POST['group'])) {
    $group = "GROUP BY k_anexoclientecc.ClaveAnexoTecnico";
} else {
    $group = "";
}

$query = $catalogo->obtenerLista("SELECT DISTINCT(k_anexoclientecc.ClaveAnexoTecnico) AS Nombre,
        k_anexoclientecc.IdAnexoClienteCC AS ID
        FROM c_centrocosto
        INNER JOIN k_anexoclientecc ON k_anexoclientecc.CveEspClienteCC=c_centrocosto.ClaveCentroCosto 
        INNER JOIN c_anexotecnico AS cat ON k_anexoclientecc.ClaveAnexoTecnico = cat.ClaveAnexoTecnico $contrato 
        INNER JOIN c_contrato AS ctt ON ctt.NoContrato = cat.NoContrato AND ctt.ClaveCliente = c_centrocosto.ClaveCliente
        WHERE c_centrocosto.ClaveCentroCosto='$ccosto' AND cat.Activo = 1 $group;");
if (!isset($_POST['omite_selecciona'])) {
    echo "<option value=\"\">Selecciona anexo</option>";
}
while ($rs = mysql_fetch_array($query)) {
    echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
}
?>
