<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/banco/lista_movimientos.js"></script>
<style>
    .ui-datepicker-calendar {
        display: none;
    }​
</style>
<form id="formlistaMovimientos">
    <table style="width: 70%;">
        <tr>
            <td>Fecha Inicio</td>
            <td><input id="fecha_inicio" name="fecha_inicio" class="fecha" style="width:196px" /></td>
            <td>Fecha Fin</td>
            <td><input id="fecha_fin" name="fecha_fin" class="fecha" style="width:196px"/></td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <td><input type="button" id="buscar" class="boton" value="Buscar"/></td>
        </tr>
    </table>
</form>
<br/><br/><br/>
<div id="divinfo">         
</div>



