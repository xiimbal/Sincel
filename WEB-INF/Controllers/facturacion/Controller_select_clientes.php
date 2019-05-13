<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$id = "";

if (isset($_POST['id'])) {
    $id = $_POST['id'];
}
echo "<option value=\"\">Todos los clientes</option>";
$query = $catalogo->obtenerLista("SELECT DISTINCT
	c_cliente.NombreRazonSocial AS cliente,
	c_cliente.RFC AS ID
FROM
	c_mantenimiento
RIGHT JOIN c_centrocosto ON c_centrocosto.ClaveCentroCosto = c_mantenimiento.ClaveCentroCosto
RIGHT JOIN c_cliente ON c_cliente.ClaveCliente = c_centrocosto.ClaveCliente
WHERE c_cliente.EjecutivoCuenta='" . $id . "'
GROUP BY cliente
ORDER BY cliente");
echo "SELECT DISTINCT
	c_cliente.NombreRazonSocial AS cliente,
	c_cliente.RFC AS ID
FROM
	c_mantenimiento
RIGHT JOIN c_centrocosto ON c_centrocosto.ClaveCentroCosto = c_mantenimiento.ClaveCentroCosto
RIGHT JOIN c_cliente ON c_cliente.ClaveCliente = c_centrocosto.ClaveCliente
WHERE c_cliente.EjecutivoCuenta='" . $id . "'
GROUP BY cliente
ORDER BY cliente";
while ($rs = mysql_fetch_array($query)) {
        echo "<option value='" . $rs['ID'] . "' >" . $rs['cliente'] . " </option>";
}
?>
