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


$query = $catalogo->obtenerLista("SELECT
	c_centrocosto.Nombre AS CentroCostoNombre,
	c_centrocosto.ClaveCentroCosto AS ID
FROM
	c_usuario
INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
INNER JOIN c_centrocosto ON c_centrocosto.ClaveCliente = c_cliente.ClaveCliente
WHERE

 c_cliente.ClaveCliente='" . $idcliente . "'
ORDER BY
	CentroCostoNombre");

echo "<option value=\"\">Selecciona localidad</option>";
while ($rs = mysql_fetch_array($query)) {
    echo "<option value=\"" . $rs['ID'] . "\">" . $rs['CentroCostoNombre'] . "</option>";
}
?>
