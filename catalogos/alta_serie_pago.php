<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Serie.class.php");
$serie = new Serie();
$prefijo = "";
$folioInicio = "";
$activo = "";
if (isset($_GET['id']) && $_GET['id']) {
    $serie->getRegistroPagoById($_GET['id']);
    $prefijo = $serie->getPrefijo();
    $folioInicio = $serie->getFolioInicio();
    $activo = $serie->getActivo();
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/catalogos/alta_serie_pago.js"></script>
<form id="formSerie">
    <table style="width: 100%;">
        <tr>
            <td>Prefijo</td>
            <td>
                <input type="text" name="prefijo" id="prefijo" value="<?php echo $prefijo ?>"/>
            </td>
            <td>Folio de Inicio</td>
            <td>
                <input type="text" name="folioInicio" id="folioInicio" value="<?php echo $folioInicio ?>"/>
            </td> 
        </tr>
        <tr>
            <td>Activo</td>
            <td><input type="checkbox" value="1" name="activo" id="activo" <?php
                if (isset($_GET['id']) && $_GET['id']) {
                    if ($activo != "" && $activo == 1) {
                        echo "checked";
                    }
                } else {
                    echo "checked";
                }
                ?>/></td>
        </tr>
    </table>
    <?php
    if (isset($_GET['id']) && $_GET['id']) {
        ?>
        <input type="hidden" name="id" id="id" value="<?php echo $_GET['id'] ?>"/>
<?php } ?>
    <br/><br/>
    <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
    <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('catalogos/lista_series_pagos.php', 'Serie');"/>
</form>
