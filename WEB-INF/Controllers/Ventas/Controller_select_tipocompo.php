<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$query3 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
echo "<option value=\"\">Selecciona el modelo</option>";
echo "<option value=\"0\">Equipo</option>";
while ($rsp = mysql_fetch_array($query3)) {
    echo "<option value=\"" . $rsp['ID'] . "\" >" . $rsp['Nombre'] . "</option>";
}
?>
