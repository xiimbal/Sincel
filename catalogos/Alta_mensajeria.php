<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Mensajeria.class.php");
$nombre = "";
$activo = "";
if (isset($_GET['id']) && $_GET['id']) {
    $conductor = new Mensajeria();
    $conductor->getRegistroById($_GET['id']);
    $nombre = $conductor->getNombre();
    $activo = $conductor->getActivo();
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/catalogos/Alta_mensajeria.js"></script>
<form id="formconductor">
    <table style="width: 100%;">
        <tr>
            <td>Nombre</td>
            <td>
                <input type="text" name="nombre" id="nombre" value="<?php echo $nombre ?>"/>
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
    <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('catalogos/lista_mensajeria.php', 'Mensajería');"/>
</form>