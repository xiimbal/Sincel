<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/Catalogo.class.php");
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $catalogo= new Catalogo();
        $query3 = $catalogo->obtenerLista("SELECT c_centrocosto.ClaveCentroCosto AS ID,c_centrocosto.Nombre AS Nombre 
FROM c_centrocosto 
INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
WHERE c_cliente.ClaveCliente='".$id."' ORDER BY Nombre");
        echo "<option value=\"\" >Todas las localidades</option>";
        while ($rsp = mysql_fetch_array($query3)) {
            echo "<option value=\"" . $rsp['ID'] . "\" >" .$rsp['Nombre'] . "</option>";
        }
}else{
    echo "<option value=\"\" >Todos las localidades</option>";
}
?>
