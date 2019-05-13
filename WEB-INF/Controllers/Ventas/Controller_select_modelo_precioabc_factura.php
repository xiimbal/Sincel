<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/Catalogo.class.php");
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $catalogo = new Catalogo();
    if ($id == 0) {
        $consulta = "SELECT DISTINCT
	e.Modelo AS Modelo,
	e.NoParte AS Parte 
        FROM c_equipo AS e
        INNER JOIN k_almacenequipo AS k ON k.NoParte=e.NoParte
        ORDER BY Modelo;";
        $query3 = $catalogo->obtenerLista($consulta);
        echo "<option value=\"\">Selecciona el modelo</option>";
        while ($rsp = mysql_fetch_array($query3)) {
            echo "<option value=\"" . $rsp['Parte'] . "\" >" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
        }
    } else {
        $consulta = "SELECT DISTINCT
	c_componente.Modelo AS Modelo,
	c_componente.NoParte AS Parte,
        c_componente.Descripcion AS Descripcion
        FROM c_componente
        INNER JOIN k_almacencomponente AS k ON k.NoParte=c_componente.NoParte
        INNER JOIN c_tipocomponente ON c_tipocomponente.IdTipoComponente=c_componente.IdTipoComponente
        WHERE c_componente.IdTipoComponente='" . $id . "' ORDER BY Modelo;";
        $query3 = $catalogo->obtenerLista($consulta);
        echo "<option value=\"\">Selecciona el modelo</option>";
        while ($rsp = mysql_fetch_array($query3)) {
            echo "<option value=\"" . $rsp['Parte'] . "\" >" . $rsp['Modelo'] . " / " . $rsp['Parte'] . " / " . $rsp['Descripcion'] . "</option>";
        }
    }
}
?>