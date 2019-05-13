<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$id = "";
$client = "";
if (isset($_POST['id']) && $_POST['id'] != "") {
    $id = "WHERE c_cliente.EjecutivoCuenta='" . $_POST['id'] . "' AND c_cliente.Activo='1'";
}else{
    $id = "WHERE c_cliente.Activo='1'";
}
if(isset($_POST['client']) && $_POST['client'] != ""){
    $client = $_POST['client'];
}
echo "<option value=\"\">Todos los clientes</option>";
$query = $catalogo->obtenerLista("SELECT DISTINCT
	c_cliente.NombreRazonSocial AS cliente,
	c_cliente.ClaveCliente AS ID,
	IF(ISNULL(c_mantenimiento.IdMantenimiento),'(sin mantenimientos)','') AS mtto
FROM
	c_mantenimiento
RIGHT JOIN c_centrocosto ON c_centrocosto.ClaveCentroCosto = c_mantenimiento.ClaveCentroCosto
RIGHT JOIN c_cliente ON c_cliente.ClaveCliente = c_centrocosto.ClaveCliente
$id
GROUP BY cliente
ORDER BY cliente");
while ($rs = mysql_fetch_array($query)) {
    $s = "";
    if(!empty($client) && $client == $rs['ID']){
        $s = "selected";
    }
    if ($rs['mtto'] != '') {        
        echo "<option value='" . $rs['ID'] . "' $s style='color:blue'>" . $rs['cliente'] . " " . $rs['mtto'] . "</option>";
    } else {
        echo "<option value='" . $rs['ID'] . "' $s>" . $rs['cliente'] . " <span color='red'>" . $rs['mtto'] . "</span></option>";
    }
}
?>
