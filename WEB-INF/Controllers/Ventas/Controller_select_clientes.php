<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$idcliente = "";
if (isset($_POST['id'])) {
    $idcliente = $_POST['id'];
}

$idusuario = "";
if (isset($_POST['cliente']) && $_POST['cliente'] != "") {
    $idusuario = "c_usuario.IdUsuario='" . $_POST['cliente'] . "' AND";
}

$modalidad = "";
if(isset($_POST['modalidad']) && $_POST['modalidad'] == "arrendamiento"){
    $modalidad = " (Modalidad = 1 OR IdTipoCliente = 7) AND ";
}

$consulta = "SELECT c_cliente.NombreRazonSocial AS Nombre,c_cliente.ClaveCliente AS ID FROM c_usuario
INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta=c_usuario.IdUsuario
WHERE $idusuario $modalidad c_cliente.Activo = 1 ORDER BY Nombre";

$query = $catalogo->obtenerLista($consulta);

echo "<option value=\"\">Selecciona cliente</option>";
while ($rs = mysql_fetch_array($query)) {
    echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
}
?>
