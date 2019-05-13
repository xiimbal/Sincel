<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/Catalogo.class.php");
if (isset($_POST['tipo'])) {
    $id = $_POST['tipo'];
    $catalogo = new Catalogo();
    if ($id == 0) {
        $query3 = $catalogo->obtenerLista("SELECT pabc.Precio_A,pabc.Precio_B,pabc.Precio_C  
            FROM c_precios_abc AS pabc
            LEFT JOIN c_equipo AS e ON e.NoParte = pabc.NoParteEquipo
            WHERE e.NoParte = '".$_POST['modelo']."';");
        if ($rsp = mysql_fetch_array($query3)) {
            echo "<option value=\"\">Selecciona el precio</option>";
            echo "<option value=\"" . $rsp['Precio_A'] . "\" >Precio A: " . $rsp['Precio_A'] . "</option>";
            if ($rsp['Precio_B'] != "") {
                echo "<option value=\"" . $rsp['Precio_B'] . "\" >Precio B: " . $rsp['Precio_B'] . "</option>";
            }
            if ($rsp['Precio_C'] != "") {
                echo "<option value=\"" . $rsp['Precio_C'] . "\" >Precio C: " . $rsp['Precio_C'] . "</option>";
            }
            echo "<option value=\"none\" >Otro</option>";
        } else {
            echo "<option value=\"\">Selecciona otro</option>";
            echo "<option value=\"none\" >Otro</option>";
        }
    } else {
        $query3 = $catalogo->obtenerLista("SELECT pabc.Precio_A,pabc.Precio_B,pabc.Precio_C  
            FROM c_precios_abc AS pabc
            LEFT JOIN c_componente AS c ON c.NoParte = pabc.NoParteComponente
            WHERE c.NoParte = '".$_POST['modelo']."';");
        if ($rsp = mysql_fetch_array($query3)) {
            echo "<option value=\"\">Selecciona el precio</option>";
            echo "<option value=\"" . $rsp['Precio_A'] . "\" >Precio A: " . $rsp['Precio_A'] . "</option>";
            if ($rsp['Precio_B'] != "") {
                echo "<option value=\"" . $rsp['Precio_B'] . "\" >Precio B: " . $rsp['Precio_B'] . "</option>";
            }
            if ($rsp['Precio_C'] != "") {
                echo "<option value=\"" . $rsp['Precio_C'] . "\" >Precio C: " . $rsp['Precio_C'] . "</option>";
            }
            echo "<option value=\"none\" >Otro</option>";
        } else {
            echo "<option value=\"\">Selecciona otro</option>";
            echo "<option value=\"none\" >Otro</option>";
        }
    }
}
?>
