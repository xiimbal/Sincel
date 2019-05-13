<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Banco.class.php");

$rfc = "";
if (isset($_GET['id']) && $_GET['id']) {
    $banco = new Banco();
    $banco->getRegistroById($_GET['id']);
    $nombre = $banco->getNombre();
    $rfc = $banco->getRFC();
    $descripcion = $banco->getDescripcion();
    $activo = $banco->getActivo();
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/catalogos/alta_banco.js"></script>
<form id="formbanco">
    <table style="width: 70%;">
        <tr>
            <td>Nombre <span class="obligatorio">*</span></td>
            <td>
                <input type="text" name="nombre" id="nombre" value="<?php echo $nombre; ?>"/>
            </td>            
            <td>Descripción</td>
            <td><input type="text" name="descripcion" id="descripcion" value="<?php echo $descripcion; ?>"/></td>
        </tr>
        <tr>
            <td>RFC <span class="obligatorio">*</span></td>
            <td>
                <input type="text" name="RFC" id="RFC" value="<?php echo $rfc; ?>"/>
            </td> 
            <td><input type="checkbox" value="1" name="activo" id="activo" <?php
                if (isset($_GET['id']) && $_GET['id']) {
                    if ($activo != "" && $activo == 1) {
                        echo "checked";
                    }
                }else{
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
    <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('Bancos/lista_bancos.php', 'Bancos');"/>
</form>
