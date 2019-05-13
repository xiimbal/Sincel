<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
header('Content-Type: text/html; charset=utf-8');

?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/viajes/cargaListaAsistencia.js"></script>

<form id = "formCargaLista" name="formCargaLista" ENCTYPE="multipart/form-data">
    <table>
        <tr>
            <td style="width: 50px"></td>
            <td>Importar archivo: </td>
            <td style="width: 50px"></td>
            <td><input type='file' name='file' id='file' class="boton"></td>
            <td style="width: 50px"></td>
            <td><input id="upload" name ="upload" type="button" value="Cargar" class="boton" /></td>
        </tr>
    </table>
</form>
<br/>
<div id="mensajeLista"></div>
<br/>
<div id="div1"></div>
