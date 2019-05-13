<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/Catalogo.class.php");
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $catalogo= new Catalogo();
        $query3 = $catalogo->obtenerLista("SELECT c_inventarioequipo.NoSerie AS ID,c_equipo.Modelo AS Modelo FROM c_centrocosto
INNER JOIN k_anexoclientecc ON k_anexoclientecc.CveEspClienteCC=c_centrocosto.ClaveCentroCosto
INNER JOIN c_inventarioequipo ON c_inventarioequipo.IdAnexoClienteCC=k_anexoclientecc.IdAnexoClienteCC
INNER JOIN c_equipo ON c_inventarioequipo.NoParteEquipo=c_equipo.NoParte
WHERE c_centrocosto.ClaveCentroCosto='".$id."'");
        echo "<option value=\"\" >Todas los equipos</option>";
        while ($rsp = mysql_fetch_array($query3)) {
            echo "<option value=\"" . $rsp['ID'] . "\" >" .$rsp['ID'] . "/". $rsp['Modelo'] ."</option>";
        }
}else{
    echo "<option value=\"\" >Todos los equipos</option>";
}
?>
